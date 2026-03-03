<?php
// include/func/addon-settings.php

if (!isset($_SESSION["sup_adm_session"])) {
    return; // Silently exit if not a super admin
}

$success_msg = '';
$err_msg = '';

if (isset($_POST['save_addon_settings'])) {
    // Sanitize and prepare data
    $bank_details = trim(strip_tags($_POST['bank_details'])); // Basic sanitization for text

    $addons_costs = [
        'CBT' => filter_input(INPUT_POST, 'cbt_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'Bulk Report Card' => filter_input(INPUT_POST, 'bulk_report_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'Data Cleanup' => filter_input(INPUT_POST, 'cleanup_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'I.D. Card Generation' => filter_input(INPUT_POST, 'id_card_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'Live Classes' => filter_input(INPUT_POST, 'live_classes_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'SMS' => filter_input(INPUT_POST, 'sms_cost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
    ];

    $all_success = true;

    // Use INSERT ... ON DUPLICATE KEY UPDATE for efficiency
    $sql = "INSERT INTO sm_addon_settings (addon_name, cost, bank_details) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE cost = VALUES(cost), bank_details = VALUES(bank_details)";

    $stmt = mysqli_prepare($connection_server, $sql);

    if ($stmt) {
        foreach ($addons_costs as $name => $cost) {
            // Fallback to 0.00 if cost is not a valid float
            $cost = is_numeric($cost) ? $cost : 0.00;

            mysqli_stmt_bind_param($stmt, 'sds', $name, $cost, $bank_details);
            if (!mysqli_stmt_execute($stmt)) {
                $all_success = false;
                // You might want to log the error: mysqli_stmt_error($stmt)
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        $all_success = false;
        // Log error: mysqli_error($connection_server)
    }

    if ($all_success) {
        $success_msg = "Settings saved successfully!";
    } else {
        $err_msg = "An error occurred while saving some settings.";
    }
}

// Fetch current settings to display in the form
$current_settings = [];
$sql_fetch = "SELECT * FROM sm_addon_settings";
$result = mysqli_query($connection_server, $sql_fetch);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $current_settings[$row['addon_name']] = $row;
    }
}
// Use a single bank_details value, assuming it's the same for all.
$bank_details_value = !empty($current_settings) ? reset($current_settings)['bank_details'] : '';

?>
