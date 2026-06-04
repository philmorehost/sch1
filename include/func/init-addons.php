<?php
// include/func/init-addons.php

// This script ensures all addon-related tables exist and are populated with default values.
// It should be included in a central location that runs on every page load.

function create_table_if_not_exists($connection, $tableName, $sql) {
    $check_table_sql = "SHOW TABLES LIKE '$tableName'";
    $table_result = mysqli_query($connection, $check_table_sql);
    if ($table_result && mysqli_num_rows($table_result) == 0) {
        if (!mysqli_query($connection, $sql)) {
            // Handle table creation error if needed
        }
    }
}

// 1. Addon Settings Table
$settings_table = 'sm_addon_settings';
$create_settings_sql = "CREATE TABLE `{$settings_table}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `addon_name` varchar(255) NOT NULL,
    `cost` decimal(10,2) NOT NULL,
    `bank_details` text NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `addon_name` (`addon_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
create_table_if_not_exists($connection_server, $settings_table, $create_settings_sql);

// Self-healing: Add the activation_table column if it doesn't exist
$check_column_sql = "SHOW COLUMNS FROM `{$settings_table}` LIKE 'activation_table'";
$column_result = mysqli_query($connection_server, $check_column_sql);
if ($column_result && mysqli_num_rows($column_result) == 0) {
    $alter_table_sql = "ALTER TABLE `{$settings_table}` ADD COLUMN `activation_table` VARCHAR(255) NOT NULL AFTER `bank_details`";
    mysqli_query($connection_server, $alter_table_sql);
}

// 2. Populate Addon Settings with default addons if they don't exist
$default_addons = [
    ['name' => 'CBT', 'table' => 'sm_cbt_activated_schools'],
    ['name' => 'Bulk Report Card', 'table' => 'sm_bulk_report_card_activated_schools'],
    ['name' => 'Data Cleanup', 'table' => 'sm_cleanup_activated_schools'],
    ['name' => 'I.D. Card Generation', 'table' => 'sm_id_card_activated_schools'],
    ['name' => 'Live Classes', 'table' => 'sm_live_classes_activated_schools'],
    ['name' => 'SMS', 'table' => 'sm_sms_activated_schools']
];

$check_sql = "SELECT addon_name FROM `{$settings_table}` WHERE addon_name = ?";
$stmt_check = mysqli_prepare($connection_server, $check_sql);

$insert_sql = "INSERT INTO `{$settings_table}` (addon_name, cost, bank_details, activation_table) VALUES (?, 0.00, '', ?)";
$stmt_insert = mysqli_prepare($connection_server, $insert_sql);

if ($stmt_check && $stmt_insert) {
    foreach ($default_addons as $addon) {
        mysqli_stmt_bind_param($stmt_check, 's', $addon['name']);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) == 0) {
            mysqli_stmt_bind_param($stmt_insert, 'ss', $addon['name'], $addon['table']);
            mysqli_stmt_execute($stmt_insert);
        }
    }
    mysqli_stmt_close($stmt_check);
    mysqli_stmt_close($stmt_insert);
}

// 3. Addon Requests Table
$requests_table = 'sm_addon_requests';
$create_requests_sql = "CREATE TABLE `{$requests_table}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `school_id_number` varchar(255) NOT NULL,
    `addon_name` varchar(255) NOT NULL,
    `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `school_id_number` (`school_id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
create_table_if_not_exists($connection_server, $requests_table, $create_requests_sql);

// 4. Activation Tables for each Addon
$activation_tables = [
    'sm_cbt_activated_schools' => "CREATE TABLE `sm_cbt_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'sm_bulk_report_card_activated_schools' => "CREATE TABLE `sm_bulk_report_card_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'sm_cleanup_activated_schools' => "CREATE TABLE `sm_cleanup_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'sm_id_card_activated_schools' => "CREATE TABLE `sm_id_card_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'sm_live_classes_activated_schools' => "CREATE TABLE `sm_live_classes_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'sm_sms_activated_schools' => "CREATE TABLE `sm_sms_activated_schools` (`id` int(11) NOT NULL AUTO_INCREMENT, `school_id_number` varchar(255) NOT NULL, `date_of_activation` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];

foreach($activation_tables as $table => $sql){
    create_table_if_not_exists($connection_server, $table, $sql);
}

// 5. Live Classes Table (specific to the Live Classes addon)
$live_classes_table = 'sm_live_classes';
$create_live_classes_sql = "CREATE TABLE `{$live_classes_table}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `school_id_number` varchar(255) NOT NULL,
    `teacher_id` varchar(255) NOT NULL,
    `class_id` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `platform` varchar(50) NOT NULL,
    `meeting_link` text NOT NULL,
    `start_time` datetime NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
create_table_if_not_exists($connection_server, $live_classes_table, $create_live_classes_sql);

?>
