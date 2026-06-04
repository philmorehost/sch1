<?php
// include/func/bulk-messaging.php

if (isset($_POST['send_school_message']) && (isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session']))) {
    $school_id = $get_logged_user_details['school_id_number'] ?? '';
    if (!$school_id) {
        $err_msg = "Could not identify the school.";
        return;
    }

    $recipient_group = htmlspecialchars($_POST['recipient_group']);
    $message_type = htmlspecialchars($_POST['message_type']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    $class_id = htmlspecialchars($_POST['class_id']);

    $recipients = [];
    $is_sms = ($message_type === 'sms');
    $field = $is_sms ? 'phone_number' : 'email';

    if ($recipient_group === 'all_students') {
        $sql = "SELECT $field FROM sm_students WHERE school_id_number = ?";
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 's', $school_id);
    } elseif ($recipient_group === 'all_parents') {
        if ($is_sms) {
            $sql = "SELECT p.father_phone_number, p.mother_phone_number FROM sm_parents p JOIN sm_students s ON p.id_number = s.parent_id_number WHERE s.school_id_number = ? GROUP BY p.id_number";
        } else {
            $sql = "SELECT p.email FROM sm_parents p JOIN sm_students s ON p.id_number = s.parent_id_number WHERE s.school_id_number = ? GROUP BY p.email";
        }
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 's', $school_id);
    } elseif ($recipient_group === 'class' && $class_id) {
        $sql = "SELECT $field FROM sm_students WHERE school_id_number = ? AND current_class = ?";
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $school_id, $class_id);
    }

    if (isset($stmt)) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            if ($recipient_group === 'all_parents' && $is_sms) {
                if (!empty($row['father_phone_number'])) $recipients[] = $row['father_phone_number'];
                if (!empty($row['mother_phone_number'])) $recipients[] = $row['mother_phone_number'];
            } else {
                $recipients[] = $row[$field];
            }
        }
        mysqli_stmt_close($stmt);
    }

    if (!empty($recipients)) {
        if ($is_sms) {
            $school_details_query = mysqli_query($connection_server, "SELECT wallet_balance FROM sm_school_details WHERE school_id_number = '$school_id'");
            $school_details = mysqli_fetch_array($school_details_query);
            $wallet_balance = $school_details['wallet_balance'];

            if ($wallet_balance < count($recipients)) {
                $err_msg = "You have low SMS credit. Please <a href='/bc-admin.php?page=smgt_sms_dashboard'>top up your wallet</a> to send messages.";
                return;
            }

            // SMS sending logic
            $get_api_key = mysqli_query($connection_server, "SELECT sms_api_key FROM sm_sms_settings LIMIT 1");
            if (mysqli_num_rows($get_api_key) > 0) {
                $api_key = mysqli_fetch_assoc($get_api_key)['sms_api_key'];
                $sender_id = filter_input(INPUT_POST, 'sender_id', FILTER_SANITIZE_STRING) ?: 'School';
                $recipients_str = implode(',', $recipients);
                $encoded_message = urlencode($message);
                $url = "https://app.philmoresms.com/api/sms.php?token=$api_key&senderID=$sender_id&recipients=$recipients_str&message=$encoded_message";

                // Use cURL to send SMS
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $sms_result = curl_exec($ch);
                curl_close($ch);

                $response = json_decode($sms_result, true);
                if (isset($response['status']) && $response['status'] == 'success') {
                    $err_msg = "SMS sent successfully.";
                } else {
                    $err_msg = "Failed to send SMS. Error: " . ($response['error_code'] ?? 'Unknown');
                }
            } else {
                $err_msg = "SMS sending is not configured.";
            }
        } else {
            // Email sending logic
            $headers = "From: no-reply@soaschool.com";
            foreach ($recipients as $recipient) {
                customBCMailSender('no-reply@soaschool.com', $recipient, $subject, $message, $headers);
            }
            $err_msg = "Emails sent successfully.";
        }
    } else {
        $err_msg = "No recipients found for the selected criteria.";
    }
}


if (!isset($_SESSION['sup_adm_session'])) {
    return;
}

$all_schools = [];
$err_msg = '';

// Fetch all schools for the dropdown
$sql = "SELECT school_id_number, school_name FROM sm_school_details";
$result = mysqli_query($connection_server, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $all_schools[] = $row;
}

if (isset($_POST['send_message'])) {
    $recipient_type = htmlspecialchars($_POST['recipient_type']);
    $message_type = htmlspecialchars($_POST['message_type']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    if ($message_type === 'email') {
        $recipients = [];
        if ($recipient_type === 'all') {
            $sql = "SELECT m.email FROM sm_moderators m JOIN sm_school_details s ON m.school_id_number = s.school_id_number";
            $result = mysqli_query($connection_server, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $recipients[] = $row['email'];
            }
        } else {
            $school_id = htmlspecialchars($_POST['school_id']);
            $sql = "SELECT email FROM sm_moderators WHERE school_id_number = ?";
            $stmt = mysqli_prepare($connection_server, $sql);
            mysqli_stmt_bind_param($stmt, 's', $school_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $recipients[] = $row['email'];
            }
            mysqli_stmt_close($stmt);
        }

        if (!empty($recipients)) {
            $headers = "From: no-reply@soaschool.com";
            foreach ($recipients as $recipient) {
                customBCMailSender('no-reply@soaschool.com', $recipient, $subject, $message, $headers);
            }
            $err_msg = "Emails sent successfully.";
        } else {
            $err_msg = "No recipients found.";
        }
    } elseif ($message_type === 'sms') {
        $recipients = [];
        if ($recipient_type === 'all') {
            $sql = "SELECT m.phone_number FROM sm_moderators m JOIN sm_school_details s ON m.school_id_number = s.school_id_number";
            $result = mysqli_query($connection_server, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $recipients[] = $row['phone_number'];
            }
        } else {
            $school_id = htmlspecialchars($_POST['school_id']);
            $sql = "SELECT phone_number FROM sm_moderators WHERE school_id_number = ?";
            $stmt = mysqli_prepare($connection_server, $sql);
            mysqli_stmt_bind_param($stmt, 's', $school_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $recipients[] = $row['phone_number'];
            }
            mysqli_stmt_close($stmt);
        }

        if (!empty($recipients)) {
            $get_api_key = mysqli_query($connection_server, "SELECT sms_api_key FROM sm_sms_settings LIMIT 1");
            if (mysqli_num_rows($get_api_key) > 0) {
                $api_key = mysqli_fetch_assoc($get_api_key)['sms_api_key'];
                $sender_id = htmlspecialchars($_POST['sender_id']) ?: 'SuperAdmin';
                $recipients_str = implode(',', $recipients);
                $encoded_message = urlencode($message);
                $url = "https://app.philmoresms.com/api/sms.php?token=$api_key&senderID=$sender_id&recipients=$recipients_str&message=$encoded_message";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);

                $response = json_decode($result, true);
                if (isset($response['status']) && $response['status'] == 'success') {
                    $err_msg = "SMS sent successfully.";
                } else {
                    $err_msg = "Failed to send SMS. Error: " . ($response['error_code'] ?? 'Unknown');
                }
            } else {
                $err_msg = "SMS sending is not configured.";
            }
        } else {
            $err_msg = "No recipients found.";
        }
    }
}