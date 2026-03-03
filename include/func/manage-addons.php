<?php
// include/func/manage-addons.php

if (!isset($_SESSION["sup_adm_session"])) {
    return;
}

// Ensure the addon requests table exists
$table_name = 'sm_addon_requests';
$check_table_sql = "SHOW TABLES LIKE '$table_name'";
$table_result = mysqli_query($connection_server, $check_table_sql);
if (mysqli_num_rows($table_result) == 0) {
    $create_table_sql = "CREATE TABLE `{$table_name}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `school_id_number` varchar(255) NOT NULL,
        `addon_name` varchar(255) NOT NULL,
        `status` varchar(50) NOT NULL DEFAULT 'pending',
        `request_date` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($connection_server, $create_table_sql);
}


// Fetch all addon requests
$addon_requests = [];
$sql = "SELECT ar.*, sd.school_name
        FROM sm_addon_requests ar
        JOIN sm_school_details sd ON ar.school_id_number = sd.school_id_number
        ORDER BY ar.request_date DESC";
$result = mysqli_query($connection_server, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $addon_requests[] = $row;
    }
}
?>