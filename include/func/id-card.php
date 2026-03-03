<?php
// include/func/id-card.php

if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    return;
}

require_once 'activation-helper.php';

function generateIdCardHtml($items, $type, $school_details, $connection_server) {
    $html = '<div class="id-card-container">';

    $school_info_sql = "SELECT school_name, school_address FROM sm_school_details WHERE school_id_number = ?";
    $stmt_school = mysqli_prepare($connection_server, $school_info_sql);
    mysqli_stmt_bind_param($stmt_school, 's', $school_details['school_id_number']);
    mysqli_stmt_execute($stmt_school);
    $school_result = mysqli_stmt_get_result($stmt_school);
    $school_info = mysqli_fetch_assoc($school_result);
    mysqli_stmt_close($stmt_school);

    foreach ($items as $item) {
        $photo_path = '';
        if ($type === 'student') {
            $user_photo_filename = "student_" . $school_details["school_id_number"] . "_" . $item["admission_number"] . ".png";
            if (file_exists('dataimg/' . $user_photo_filename)) {
                $photo_path = 'dataimg/' . $user_photo_filename;
            } else {
                $photo_path = 'imgfile/student.png';
            }
        } else { // Staff
            $user_photo_filename = "teacher_" . $school_details["school_id_number"] . "_" . $item["id_number"] . ".png";
            if (file_exists('dataimg/' . $user_photo_filename)) {
                $photo_path = 'dataimg/' . $user_photo_filename;
            } else {
                $photo_path = 'imgfile/teacher.png';
            }
        }

        $html .= '
        <div class="id-card">
            <div class="id-card-header">
                <img src="dataimg/school_' . htmlspecialchars($school_details['school_id_number']) . '.png" class="school-logo" alt="School Logo">
                <div class="school-name">' . htmlspecialchars($school_info['school_name']) . '</div>
            </div>
            <div class="id-card-body">
                <img src="' . $photo_path . '" class="profile-photo" alt="Profile Photo">
                <div class="user-details">
                    <div class="user-name">' . htmlspecialchars($item['firstname'] . ' ' . ($item['lastname'] ?? '')) . '</div>
                    <div class="user-id">ID: ' . htmlspecialchars(($type === 'student') ? $item['admission_number'] : $item['id_number']) . '</div>';

        if ($type === 'student') {
            $class_name = $item['current_class']; // Default to class ID
            $class_name_sql = "SELECT class_name FROM sm_classes WHERE school_id_number = ? AND numeric_class_name = ?";
            $stmt_class = mysqli_prepare($connection_server, $class_name_sql);
            if ($stmt_class) {
                mysqli_stmt_bind_param($stmt_class, 'ss', $school_details['school_id_number'], $item['current_class']);
                mysqli_stmt_execute($stmt_class);
                $class_result = mysqli_stmt_get_result($stmt_class);
                if ($class_info = mysqli_fetch_assoc($class_result)) {
                    $class_name = $class_info['class_name'];
                }
                mysqli_stmt_close($stmt_class);
            }
            $html .= '<div class="user-class">Class: ' . htmlspecialchars($class_name) . '</div>';
        } else {
            $html .= '<div class="user-role">Role: Teacher</div>';
        }

        $html .= '
                </div>
            </div>
            <div class="id-card-footer">
                <p>' . htmlspecialchars($school_info['school_address']) . '</p>
            </div>
        </div>';
    }

    $html .= '</div>';

    // Add pagination links
    if (isset($GLOBALS['total_pages']) && $GLOBALS['total_pages'] > 1) {
        $queryParams = $_GET;
        unset($queryParams['p']); // Remove old page number
        $baseUrl = '?' . http_build_query($queryParams);
        $html .= generatePaginationLinks($GLOBALS['page'], $GLOBALS['total_pages'], $baseUrl);
    }

    $html .= '<br><button onclick="window.print()" class="button-box color-2 bg-4 onhover-bg-color-7">Print I.D. Cards</button>';
    return $html;
}

$school_id = $get_logged_user_details['school_id_number'] ?? '';
$output = '';

if (isFeatureActivated($connection_server, $school_id, 'sm_id_card_activated_schools')) {
    if (isset($_GET['generate_cards'])) {
        $generation_type = $_GET['generation_type'] ?? 'none';
        $items = [];
        $template_type = '';

        switch ($generation_type) {
            case 'single_student':
                $student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
                if ($student_id) {
                    $sql = "SELECT * FROM sm_students WHERE school_id_number = ? AND admission_number = ?";
                    $stmt = mysqli_prepare($connection_server, $sql);
                    mysqli_stmt_bind_param($stmt, 'ss', $school_id, $student_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        $items[] = $row;
                    }
                    mysqli_stmt_close($stmt);
                }
                $template_type = 'student';
                break;

            case 'all_students':
                $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                $limit = 30;
                $offset = ($page - 1) * $limit;

                $count_sql = "SELECT COUNT(*) FROM sm_students WHERE school_id_number = ?";
                $stmt_count = mysqli_prepare($connection_server, $count_sql);
                mysqli_stmt_bind_param($stmt_count, 's', $school_id);
                mysqli_stmt_execute($stmt_count);
                $count_result = mysqli_stmt_get_result($stmt_count);
                $total_records = mysqli_fetch_array($count_result)[0];
                $total_pages = ceil($total_records / $limit);
                mysqli_stmt_close($stmt_count);

                $sql = "SELECT * FROM sm_students WHERE school_id_number = ? LIMIT ? OFFSET ?";
                $stmt = mysqli_prepare($connection_server, $sql);
                mysqli_stmt_bind_param($stmt, 'sii', $school_id, $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $items[] = $row;
                }
                mysqli_stmt_close($stmt);
                $template_type = 'student';
                break;
            case 'single_staff':
                $staff_id = isset($_GET['staff_id']) ? trim($_GET['staff_id']) : '';
                if ($staff_id) {
                    $sql = "SELECT * FROM sm_teachers WHERE school_id_number = ? AND id_number = ?";
                    $stmt = mysqli_prepare($connection_server, $sql);
                    mysqli_stmt_bind_param($stmt, 'ss', $school_id, $staff_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        $items[] = $row;
                    }
                    mysqli_stmt_close($stmt);
                }
                $template_type = 'staff';
                break;

            case 'all_staff':
                $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                $limit = 30;
                $offset = ($page - 1) * $limit;

                $count_sql = "SELECT COUNT(*) FROM sm_teachers WHERE school_id_number = ?";
                $stmt_count = mysqli_prepare($connection_server, $count_sql);
                mysqli_stmt_bind_param($stmt_count, 's', $school_id);
                mysqli_stmt_execute($stmt_count);
                $count_result = mysqli_stmt_get_result($stmt_count);
                $total_records = mysqli_fetch_array($count_result)[0];
                $total_pages = ceil($total_records / $limit);
                mysqli_stmt_close($stmt_count);

                $sql = "SELECT * FROM sm_teachers WHERE school_id_number = ? LIMIT ? OFFSET ?";
                $stmt = mysqli_prepare($connection_server, $sql);
                mysqli_stmt_bind_param($stmt, 'sii', $school_id, $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $items[] = $row;
                }
                mysqli_stmt_close($stmt);
                $template_type = 'staff';
                break;
        }

        if (!empty($items)) {
            $output = generateIdCardHtml($items, $template_type, $get_logged_user_details, $connection_server);
        } else {
            $output = "<p class='color-5'>No individuals found for the selected criteria.</p>";
        }
    }
} else {
    // Redirect or show a message if the feature is not activated
    // For example, you could set output to an informative message.
    $output = "<p class='color-5'>The I.D. Card Generation feature is not activated for your school. Please contact support.</p>";
    // Or redirect:
    // header("Location: /bc-admin.php?page=request-addon");
    // exit();
}
?>