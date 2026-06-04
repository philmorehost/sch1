<?php
// include/func/cleanup.php
session_start();

if (isset($_POST['cleanup_orphaned_subjects'])) {
    // Include the main config file to setup the environment which includes the database connection
    require_once dirname(__FILE__) . '/../config-file.php';

    // Check for user session and permissions
    if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not authorized to perform this action.']);
        exit;
    }

    $school_id = $get_logged_user_details['school_id_number'];

    if (empty($school_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Could not determine school ID.']);
        exit;
    }

    // Find orphaned subject records
    $sql = "DELETE FROM sm_results WHERE school_id_number = ? AND subject_code NOT IN (SELECT subject_code FROM sm_subjects WHERE school_id_number = ?)";
    $stmt = mysqli_prepare($connection_server, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $school_id, $school_id);

    if (mysqli_stmt_execute($stmt)) {
        $deleted_rows = mysqli_stmt_affected_rows($stmt);
        echo json_encode(['status' => 'success', 'message' => "Successfully deleted {$deleted_rows} orphaned subject records."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while cleaning up the data.']);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($connection_server);
}
