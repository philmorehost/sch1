<?php
// include/func/admin-settings.php

if (!isset($_SESSION['sup_adm_session'])) {
    return;
}

$err_msg = '';

// Create admin settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS sm_admin_settings (
    setting_key VARCHAR(255) PRIMARY KEY,
    setting_value TEXT
)";
mysqli_query($connection_server, $sql);

if (isset($_POST['save_admin_settings'])) {
    $smtp_host = filter_input(INPUT_POST, 'smtp_host', FILTER_SANITIZE_STRING);
    $smtp_username = filter_input(INPUT_POST, 'smtp_username', FILTER_SANITIZE_STRING);
    $smtp_password = filter_input(INPUT_POST, 'smtp_password', FILTER_SANITIZE_STRING);
    $smtp_port = filter_input(INPUT_POST, 'smtp_port', FILTER_SANITIZE_NUMBER_INT);

    $settings = [
        'smtp_host' => $smtp_host,
        'smtp_username' => $smtp_username,
        'smtp_password' => $smtp_password,
        'smtp_port' => $smtp_port,
    ];

    foreach ($settings as $key => $value) {
        $sql = "INSERT INTO sm_admin_settings (setting_key, setting_value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    $err_msg = "Settings saved successfully.";
}

// Fetch current settings
$admin_settings = [];
$sql = "SELECT * FROM sm_admin_settings";
$result = mysqli_query($connection_server, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $admin_settings[$row['setting_key']] = $row['setting_value'];
}
