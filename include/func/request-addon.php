<?php
// include/func/request-addon.php

if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    return;
}

$school_id = $get_logged_user_details['school_id_number'];
$addon_settings = [];
$bank_details_value = 'Not configured by administrator.';
$err_msg = '';
$success_msg = '';

// Check if the addon settings table exists before proceeding.
$settings_table = 'sm_addon_settings';
$check_table_sql = "SHOW TABLES LIKE '$settings_table'";
$table_result = mysqli_query($connection_server, $check_table_sql);

if (mysqli_num_rows($table_result) == 0) {
    $err_msg = "The addon system is not yet configured by the administrator. Please check back later.";
} else {
    // Fetch addon settings to display
    $sql_fetch_settings = "SELECT * FROM sm_addon_settings";
    $result_settings = mysqli_query($connection_server, $sql_fetch_settings);
    if ($result_settings) {
        while ($row = mysqli_fetch_assoc($result_settings)) {
            $addon_settings[$row['addon_name']] = $row;
        }
        if (!empty($addon_settings)) {
            $bank_details_value = reset($addon_settings)['bank_details'];
        }
    }

    if (empty($addon_settings)) {
        $err_msg = "The addon system is not yet configured by the administrator. Please check back later.";
    }


    if (isset($_POST['request_addon_module'])) {
        $addon_name = trim(strip_tags($_POST['addon_name']));

        if (!empty($addon_name)) {
            $requests_table = 'sm_addon_requests';
            $check_req_table_sql = "SHOW TABLES LIKE '$requests_table'";
            $req_table_result = mysqli_query($connection_server, $check_req_table_sql);
            if(mysqli_num_rows($req_table_result) == 0){
                $err_msg = "The addon system is not fully configured. Please contact the administrator.";
            } else {
                $sql_check = "SELECT * FROM sm_addon_requests WHERE school_id_number = ? AND addon_name = ?";
                $stmt_check = mysqli_prepare($connection_server, $sql_check);
                mysqli_stmt_bind_param($stmt_check, 'ss', $school_id, $addon_name);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);

                if (mysqli_num_rows($result_check) == 0) {
                    $sql_insert = "INSERT INTO sm_addon_requests (school_id_number, addon_name, status) VALUES (?, ?, 'pending')";
                    $stmt_insert = mysqli_prepare($connection_server, $sql_insert);
                    mysqli_stmt_bind_param($stmt_insert, 'ss', $school_id, $addon_name);

                    if (mysqli_stmt_execute($stmt_insert)) {
                        $success_msg = "Your request for the " . htmlspecialchars($addon_name) . " module has been submitted. The administrator will contact you shortly.";
                    } else {
                        $err_msg = "An error occurred. Please try again.";
                    }
                    mysqli_stmt_close($stmt_insert);
                } else {
                    $err_msg = "You have already submitted a request for the " . htmlspecialchars($addon_name) . " module.";
                }
                mysqli_stmt_close($stmt_check);
            }
        } else {
            $err_msg = "Please select a module to request.";
        }
    }

    // Fetch existing requests for this school
    $existing_requests = [];
    $requests_table_exists = mysqli_num_rows(mysqli_query($connection_server, "SHOW TABLES LIKE 'sm_addon_requests'")) > 0;
    if ($requests_table_exists) {
        $sql_fetch_requests = "SELECT addon_name, status FROM sm_addon_requests WHERE school_id_number = ?";
        $stmt_fetch_requests = mysqli_prepare($connection_server, $sql_fetch_requests);
        mysqli_stmt_bind_param($stmt_fetch_requests, 's', $school_id);
        mysqli_stmt_execute($stmt_fetch_requests);
        $result_fetch_requests = mysqli_stmt_get_result($stmt_fetch_requests);
        if ($result_fetch_requests) {
            while ($row = mysqli_fetch_assoc($result_fetch_requests)) {
                $existing_requests[$row['addon_name']] = $row['status'];
            }
        }
        mysqli_stmt_close($stmt_fetch_requests);
    }
}
?>