<?php
// include/func/class.php

$err_msg = "";
if (isset($_GET["err"])) {
    $error_code = filter_input(INPUT_GET, 'err', FILTER_SANITIZE_STRING);
    switch ($error_code) {
        case "1": $err_msg = "Error: Empty Fields"; break;
        case "2": $err_msg = "Error: Class with the same Numeric Class Name / Session already exists."; break;
        case "3": $err_msg = "Error: Another Class with the same Numeric Class Name already exists."; break;
    }
}

$school_id_from_get = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$logged_in_school_id = $get_logged_user_details['school_id_number'] ?? $school_id_from_get;

if (empty($logged_in_school_id)) {
    $err_msg = "Error: School ID is missing. Please log in again.";
    return;
}

$header_add_button = "add_class";
$additional_add_tag = "&id=" . urlencode($logged_in_school_id);
$additional_back_tag = "&id=" . urlencode($logged_in_school_id);

$current_page_no = filter_input(INPUT_GET, 'prevnext', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$page_pnum = filter_input(INPUT_GET, 'pnum', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1]]);
$search_text = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? "";

$offset = ($current_page_no - 1) * $page_pnum;

// --- Query building ---
$base_query = "FROM sm_classes WHERE school_id_number = ?";
$param_types = 's';
$params = [$logged_in_school_id];

if (!empty($search_text)) {
    $search_conditions = [];
    $search_terms = array_filter(explode(" ", $search_text));
    foreach ($search_terms as $term) {
        $term_like = "%" . $term . "%";
        $search_conditions[] = "(class_name LIKE ? OR numeric_class_name LIKE ? OR student_capacity LIKE ? OR session LIKE ?)";
        $param_types .= 'ssss';
        array_push($params, $term_like, $term_like, $term_like, $term_like);
    }

    if (!empty($search_conditions)) {
        $base_query .= " AND (" . implode(" OR ", $search_conditions) . ")";
    }
}

// --- Count query ---
$query_count = "SELECT COUNT(*) as total " . $base_query;
$stmt_count = mysqli_prepare($connection_server, $query_count);
if ($stmt_count && !empty($param_types)) {
    mysqli_stmt_bind_param($stmt_count, $param_types, ...$params);
}
if ($stmt_count) {
    mysqli_stmt_execute($stmt_count);
    $count_result = mysqli_stmt_get_result($stmt_count);
    $total_records = mysqli_fetch_assoc($count_result)['total'] ?? 0;
    mysqli_stmt_close($stmt_count);
} else {
    $total_records = 0;
}


// --- Select query (with pagination) ---
$query_select = "SELECT * " . $base_query . " ORDER BY numeric_class_name ASC, session DESC LIMIT ? OFFSET ?";
$select_param_types = $param_types . 'ii';
$select_params = $params;
$select_params[] = $page_pnum;
$select_params[] = $offset;

$stmt_select = mysqli_prepare($connection_server, $query_select);
if ($stmt_select) {
    mysqli_stmt_bind_param($stmt_select, $select_param_types, ...$select_params);
    mysqli_stmt_execute($stmt_select);
    $select_class_table_lists = mysqli_stmt_get_result($stmt_select);
    mysqli_stmt_close($stmt_select);
} else {
    $select_class_table_lists = null;
}

if (isset($_POST["add-class"])) {
    $class_name = filter_input(INPUT_POST, 'class-name', FILTER_SANITIZE_STRING);
    $student_capacity = filter_input(INPUT_POST, 'stu-capacity', FILTER_SANITIZE_NUMBER_INT);
    $session = filter_input(INPUT_POST, 'session', FILTER_SANITIZE_STRING);
    $school_id = filter_input(INPUT_POST, 'school-id', FILTER_SANITIZE_STRING);

    if (!empty($class_name) && !empty($student_capacity) && !empty($session) && !empty($school_id)) {
        $max_numeric_sql = "SELECT MAX(numeric_class_name) as max_num FROM sm_classes WHERE school_id_number = ?";
        $stmt_max = mysqli_prepare($connection_server, $max_numeric_sql);
        mysqli_stmt_bind_param($stmt_max, 's', $school_id);
        mysqli_stmt_execute($stmt_max);
        $max_result = mysqli_stmt_get_result($stmt_max);
        $max_row = mysqli_fetch_assoc($max_result);
        $numeric_class_name = ($max_row['max_num'] ?? 0) + 1;
        mysqli_stmt_close($stmt_max);

        $check_sql = "SELECT * FROM sm_classes WHERE school_id_number = ? AND numeric_class_name = ? AND session = ?";
        $stmt_check = mysqli_prepare($connection_server, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'sis', $school_id, $numeric_class_name, $session);
        mysqli_stmt_execute($stmt_check);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) == 0) {
            $insert_sql = "INSERT INTO sm_classes (school_id_number, class_name, numeric_class_name, student_capacity, session) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($connection_server, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'ssiss', $school_id, $class_name, $numeric_class_name, $student_capacity, $session);
            if (mysqli_stmt_execute($stmt_insert)) {
                header("Location: /bc-admin.php?page=" . strip_tags($_GET['page']) . "&tab=true" . $additional_back_tag);
                exit();
            }
        } else {
            header("Location: " . $_SERVER["REQUEST_URI"] . "&err=2");
            exit();
        }
    } else {
        header("Location: " . $_SERVER["REQUEST_URI"] . "&err=1");
        exit();
    }
}

if(isset($_POST["update-class"])){
    $class_name = filter_input(INPUT_POST, 'class-name', FILTER_SANITIZE_STRING);
    $numeric_class_name = filter_input(INPUT_POST, 'num-class-name', FILTER_SANITIZE_NUMBER_INT);
    $student_capacity = filter_input(INPUT_POST, 'stu-capacity', FILTER_SANITIZE_NUMBER_INT);
    $session = filter_input(INPUT_POST, 'session', FILTER_SANITIZE_STRING);
    $school_id = filter_input(INPUT_POST, 'school-id', FILTER_SANITIZE_STRING);

    $current_session = filter_input(INPUT_GET, 'session', FILTER_SANITIZE_STRING);
    $current_numeric_class_name = filter_input(INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT);

    if (!empty($class_name) && !empty($numeric_class_name) && !empty($student_capacity) && !empty($school_id) && !empty($session)) {
        $check_sql = "SELECT * FROM sm_classes WHERE school_id_number = ? AND numeric_class_name = ? AND session = ? AND NOT (numeric_class_name = ? AND session = ?)";
        $stmt_check = mysqli_prepare($connection_server, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'sisis', $school_id, $numeric_class_name, $session, $current_numeric_class_name, $current_session);
        mysqli_stmt_execute($stmt_check);
        if(mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) == 0){
            $update_sql = "UPDATE sm_classes SET class_name = ?, numeric_class_name = ?, student_capacity = ?, session = ? WHERE school_id_number = ? AND numeric_class_name = ? AND session = ?";
            $stmt_update = mysqli_prepare($connection_server, $update_sql);
            mysqli_stmt_bind_param($stmt_update, 'siissis', $class_name, $numeric_class_name, $student_capacity, $session, $school_id, $current_numeric_class_name, $current_session);
            if(mysqli_stmt_execute($stmt_update)){
                header("Location: /bc-admin.php?page=".strip_tags($_GET['page'])."&tab=true".$additional_back_tag);
                exit();
            }
        } else {
            header("Location: " . $_SERVER["REQUEST_URI"]."&err=3");
            exit();
        }
    } else {
        header("Location: " . $_SERVER["REQUEST_URI"]."&err=1");
        exit();
    }
}

if(isset($_POST["delete-class"])){
    $class_ids = $_POST["class_id"] ?? [];
    $school_ids = $_POST["school_id"] ?? [];
    $session_ids = $_POST["session_id"] ?? [];

    if(!empty($class_ids)){
        $delete_sql = "DELETE FROM sm_classes WHERE school_id_number = ? AND numeric_class_name = ? AND session = ?";
        $stmt_delete = mysqli_prepare($connection_server, $delete_sql);

        foreach($class_ids as $index => $class_id_no){
            $sch_id_number = $school_ids[$index] ?? null;
            $session = $session_ids[$index] ?? null;
            if($sch_id_number && $session){
                mysqli_stmt_bind_param($stmt_delete, 'sss', $sch_id_number, $class_id_no, $session);
                mysqli_stmt_execute($stmt_delete);
            }
        }
        mysqli_stmt_close($stmt_delete);
    }

    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}

if(isset($_POST["search-item"])){
    $search_item_text = filter_input(INPUT_POST, 'search-item', FILTER_SANITIZE_STRING);
    $page_to_go_link = "/bc-admin.php?page=".trim(strip_tags($_GET["page"]))."&tab=true". $additional_add_tag ."&search=".urlencode($search_item_text)."&pnum=".$page_pnum;
    header("Location: ".$page_to_go_link);
    exit();
}
?>
