<?php
// include/func/automated-fees-reminder.php

function sendAutomatedFeesReminders($connection_server) {
    // Check if reminders were sent today already to prevent spam
    $today = date('Y-m-d');
    $sql = "SELECT last_sent_date FROM sm_cron_jobs WHERE job_name = 'fees_reminder'";
    $result = mysqli_query($connection_server, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['last_sent_date'] == $today) {
            return; // Reminders already sent today
        }
    }

    // Get all parents with outstanding fees
    $sql = "SELECT p.email, p.father_first_name, s.firstname, s.lastname, f.fees_type, f.amount
            FROM sm_fees_payment f
            JOIN sm_students s ON f.admission_number = s.admission_number
            JOIN sm_parents p ON s.parent_id_number = p.id_number
            WHERE f.status = 'unpaid'";
    $result = mysqli_query($connection_server, $sql);

    $reminders_to_send = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reminders_to_send[$row['email']][] = $row;
    }

    if (!empty($reminders_to_send)) {
        $headers = "From: no-reply@soaschool.com";
        foreach ($reminders_to_send as $email => $fees) {
            $parent_name = $fees[0]['father_first_name'];
            $subject = "School Fees Reminder";
            $message = "Dear Mr/Mrs $parent_name,\n\nThis is a reminder that the following school fees are due:\n\n";
            foreach ($fees as $fee) {
                $message .= "- {$fee['firstname']} {$fee['lastname']}: {$fee['fees_type']} - {$fee['amount']}\n";
            }
            $message .= "\nPlease make the payment at your earliest convenience.\n\nSincerely,\nSchool Administration";

            customBCMailSender('no-reply@soaschool.com', $email, $subject, $message, $headers);
        }
    }

    // Update the cron job table
    $sql = "INSERT INTO sm_cron_jobs (job_name, last_sent_date) VALUES ('fees_reminder', '$today')
            ON DUPLICATE KEY UPDATE last_sent_date = '$today'";
    mysqli_query($connection_server, $sql);
}

// Create cron job table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS sm_cron_jobs (
    job_name VARCHAR(255) PRIMARY KEY,
    last_sent_date DATE
)";
mysqli_query($connection_server, $sql);

// Create fees payment table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS sm_fees_payment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_number VARCHAR(255),
    fees_type VARCHAR(255),
    amount DECIMAL(10, 2),
    status VARCHAR(255)
)";
mysqli_query($connection_server, $sql);

// Call the function
sendAutomatedFeesReminders($connection_server);
