<?php
// include/func/live-classes.php

if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session']) && !isset($_SESSION['teacher_session']) && !isset($_SESSION['stu_session'])) {
    return;
}

require_once 'activation-helper.php';

$school_id = $get_logged_user_details['school_id_number'] ?? '';

// Redirect if the feature is not activated for this school
if (!isFeatureActivated($connection_server, $school_id, 'sm_live_classes_activated_schools')) {
    header("Location: /bc-admin.php?page=smgt_dashboard");
    exit();
}

$live_classes = [];

if (isset($_SESSION['teacher_session']) || isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])) {
    // Logic for teachers, admins, and staff
    $teacher_id = $_SESSION['teacher_session'] ?? filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_STRING);

    if (isset($_POST['schedule_class'])) {
        if (isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])) {
            $teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_STRING);
        }
        $class_id = filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_STRING);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $platform = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_STRING);
        $meeting_link = filter_input(INPUT_POST, 'meeting_link', FILTER_SANITIZE_URL);
        $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);

        if ($class_id && $title && $platform && $meeting_link && $start_time) {
            $sql = "INSERT INTO sm_live_classes (school_id_number, teacher_id, class_id, title, platform, meeting_link, start_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection_server, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssss', $school_id, $teacher_id, $class_id, $title, $platform, $meeting_link, $start_time);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    if (isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])) {
        $sql = "SELECT * FROM sm_live_classes WHERE school_id_number = ? ORDER BY start_time DESC";
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 's', $school_id);
    } else { // Teacher
        $sql = "SELECT * FROM sm_live_classes WHERE school_id_number = ? AND teacher_id = ? ORDER BY start_time DESC";
        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $school_id, $teacher_id);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $live_classes[] = $row;
    }
    mysqli_stmt_close($stmt);

} elseif (isset($_SESSION['stu_session']) && !empty($get_logged_user_details['current_class'])) {
    // Logic for students
    $student_class_id = $get_logged_user_details['current_class'];
    $sql = "SELECT * FROM sm_live_classes WHERE school_id_number = ? AND class_id = ? AND start_time >= NOW() ORDER BY start_time ASC";
    $stmt = mysqli_prepare($connection_server, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $school_id, $student_class_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $live_classes[] = $row;
    }
    mysqli_stmt_close($stmt);
} elseif (isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])) {
    // Logic for admins/staff to view all classes
    $sql = "SELECT * FROM sm_live_classes WHERE school_id_number = ? ORDER BY start_time DESC";
    $stmt = mysqli_prepare($connection_server, $sql);
    mysqli_stmt_bind_param($stmt, 's', $school_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $live_classes[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
