<?php
// bc-bulk-results.php
session_start();
require_once 'include/config-file.php';
require_once 'include/func/result-helpers.php';

// Define the user identifier variable before including result.php
if (isset($_SESSION["mod_adm_session"])) {
    $user_identifier_auth_id = "mod_adm";
} elseif (isset($_SESSION["adm_staff_session"])) {
    $user_identifier_auth_id = "adm_staff";
} elseif (isset($_SESSION["teacher_session"])) {
    $user_identifier_auth_id = "teacher";
} else {
    // Default or fallback identifier if needed
    $user_identifier_auth_id = "unknown";
}


if (!isset($_POST['view-bulk-result'])) {
    die("Invalid access.");
}

$school_id = trim(strip_tags($_POST['school_id']));
$numeric_class_name = trim(strip_tags($_POST['numeric-class']));
$session = trim(strip_tags($_POST['session']));
$term_id_number = trim(strip_tags($_POST['term']));

// Fetch all students in the class at once
$sql_students = "SELECT s.* FROM sm_students s JOIN sm_class_list cl ON s.admission_number = cl.admission_number WHERE cl.school_id_number = ? AND cl.numeric_class_name = ? AND cl.session = ?";
$stmt_students = mysqli_prepare($connection_server, $sql_students);
mysqli_stmt_bind_param($stmt_students, 'sss', $school_id, $numeric_class_name, $session);
mysqli_stmt_execute($stmt_students);
$result_students = mysqli_stmt_get_result($stmt_students);
$students = [];
while ($row = mysqli_fetch_assoc($result_students)) {
    $students[$row['admission_number']] = $row;
}
mysqli_stmt_close($stmt_students);

$student_admission_numbers = array_keys($students);
if (!empty($student_admission_numbers)) {
    $placeholders = implode(',', array_fill(0, count($student_admission_numbers), '?'));

    // Fetch all results for the class at once
    $sql_results = "SELECT * FROM sm_results WHERE school_id_number = ? AND numeric_class_name = ? AND session = ? AND term_id_number = ? AND admission_number IN ($placeholders)";
    $stmt_results = mysqli_prepare($connection_server, $sql_results);
    $types = 'ssss' . str_repeat('s', count($student_admission_numbers));
    $params = array_merge([$school_id, $numeric_class_name, $session, $term_id_number], $student_admission_numbers);
    mysqli_stmt_bind_param($stmt_results, $types, ...$params);
    mysqli_stmt_execute($stmt_results);
    $result_results = mysqli_stmt_get_result($stmt_results);
    $results_by_student = [];
    while ($row = mysqli_fetch_assoc($result_results)) {
        $results_by_student[$row['admission_number']][] = $row;
    }
    mysqli_stmt_close($stmt_results);

    // Fetch all remarks for the class at once
    $sql_remarks = "SELECT * FROM sm_result_remarks WHERE school_id_number = ? AND numeric_class_name = ? AND session = ? AND term_id_number = ? AND admission_number IN ($placeholders)";
    $stmt_remarks = mysqli_prepare($connection_server, $sql_remarks);
    mysqli_stmt_bind_param($stmt_remarks, $types, ...$params);
    mysqli_stmt_execute($stmt_remarks);
    $result_remarks = mysqli_stmt_get_result($stmt_remarks);
    $remarks_by_student = [];
    while ($row = mysqli_fetch_assoc($result_remarks)) {
        $remarks_by_student[$row['admission_number']] = $row;
    }
    mysqli_stmt_close($stmt_remarks);
}


// Fetch school details
$sql_school_details = "SELECT * FROM sm_school_details WHERE school_id_number = ?";
$stmt_school_details = mysqli_prepare($connection_server, $sql_school_details);
mysqli_stmt_bind_param($stmt_school_details, 's', $school_id);
mysqli_stmt_execute($stmt_school_details);
$school_details = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_school_details));
mysqli_stmt_close($stmt_school_details);

// Fetch term details
$sql_term = "SELECT term_name, next_term_begins, school_open_days FROM sm_terms WHERE school_id_number = ? AND id_number = ?";
$stmt_term = mysqli_prepare($connection_server, $sql_term);
mysqli_stmt_bind_param($stmt_term, 'ss', $school_id, $term_id_number);
mysqli_stmt_execute($stmt_term);
$term_details = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_term));
mysqli_stmt_close($stmt_term);

// Fetch class name
$sql_class = "SELECT class_name FROM sm_classes WHERE numeric_class_name = ?";
$stmt_class = mysqli_prepare($connection_server, $sql_class);
mysqli_stmt_bind_param($stmt_class, 's', $numeric_class_name);
mysqli_stmt_execute($stmt_class);
$class_details = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_class));
mysqli_stmt_close($stmt_class);

// Fetch all subject names
$sql_subjects = "SELECT subject_code, subject_name FROM sm_subjects WHERE school_id_number = ?";
$stmt_subjects = mysqli_prepare($connection_server, $sql_subjects);
mysqli_stmt_bind_param($stmt_subjects, 's', $school_id);
mysqli_stmt_execute($stmt_subjects);
$result_subjects = mysqli_stmt_get_result($stmt_subjects);
$subjects = [];
while ($row = mysqli_fetch_assoc($result_subjects)) {
    $subjects[$row['subject_code']] = $row['subject_name'];
}
mysqli_stmt_close($stmt_subjects);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Report Cards</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="cssfile/font-family.css">
    <link rel="stylesheet" href="cssfile/portal.css">
    <style>
        body {
            background-color: #f0f0f0; /* Light gray background for the page */
        }
        .report-card-container {
            width: 21cm; /* A4 width */
            min-height: 29.7cm; /* A4 height */
            padding: 1cm;
            margin: 1cm auto;
            border: 1px solid #ccc;
            background: white;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        @media print {
            body {
                background-color: white;
            }
            .report-card-container {
                margin: 0;
                border: none;
                width: auto;
                min-height: auto;
                box-shadow: none;
                page-break-after: always;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 20px;">
    <button onclick="window.print()" class="button-box color-2 bg-4 onhover-bg-color-7">Print All Report Cards</button>
</div>

<?php
foreach ($students as $admission_number => $student_details) {
    $results = $results_by_student[$admission_number] ?? [];
    $remarks = $remarks_by_student[$admission_number] ?? [];
    ?>

    <div class="report-card-container">
        <div style="text-align:center;">
            <?php if(file_exists("dataimg/school_".$school_id.".png")): ?>
                <img src="dataimg/school_<?php echo $school_id; ?>.png" style="max-width: 100px; max-height: 100px;" />
            <?php else: ?>
                <img src="imgfile/logo.png" style="max-width: 100px; max-height: 100px;" />
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($school_details['school_name']); ?></h2>
            <p><?php echo htmlspecialchars($school_details['school_address']); ?></p>
            <h3><?php echo htmlspecialchars($term_details['term_name']); ?> Report Card</h3>
        </div>

        <div style="margin-top: 20px; overflow: auto; padding-bottom: 15px;">
            <?php if(file_exists("dataimg/student_".$school_id."_".$admission_number.".png")): ?>
                <img src="dataimg/student_<?php echo $school_id."_".$admission_number; ?>.png" style="width: 100px; height: 100px; border-radius: 5px; float: right; border: 1px solid #ccc;" />
            <?php endif; ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student_details['firstname'] . ' ' . $student_details['lastname']); ?></p>
            <p><strong>Admission No:</strong> <?php echo htmlspecialchars($admission_number); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($class_details['class_name']); ?></p>
            <p><strong>Session:</strong> <?php echo htmlspecialchars(str_replace('-', '/', $session)); ?></p>
        </div>

        <table class="table-tag" style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>1st C.A</th>
                    <th>2nd C.A</th>
                    <th>3rd C.A</th>
                    <th>Exam</th>
                    <th>Total</th>
                    <th>Grade</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $total_marks_obtained = 0;
            $total_subjects = 0;
            $total_marks_obtainable = 0;
            foreach ($results as $row) {
                $total = (int)$row['first_ca'] + (int)$row['second_ca'] + (int)$row['third_ca'] + (int)$row['exam'];
                $total_marks_obtained += $total;
                $total_subjects++;
                $total_marks_obtainable += 100;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($subjects[$row['subject_code']] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['first_ca']); ?></td>
                    <td><?php echo htmlspecialchars($row['second_ca']); ?></td>
                    <td><?php echo htmlspecialchars($row['third_ca']); ?></td>
                    <td><?php echo htmlspecialchars($row['exam']); ?></td>
                    <td><?php echo htmlspecialchars($total); ?></td>
                    <td><?php echo getScoreGrade($total, 'grade', $school_id); ?></td>
                    <td><?php echo getScoreGrade($total, 'remark', $school_id); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <p><strong>Total Marks Obtained:</strong> <?php echo $total_marks_obtained; ?> out of <?php echo $total_marks_obtainable; ?></p>
            <p><strong>Average Mark:</strong> <?php echo ($total_subjects > 0) ? round(($total_marks_obtained / $total_marks_obtainable) * 100, 2) : 'N/A'; ?>%</p>
            <p><strong>Principal's Remark:</strong> <?php echo htmlspecialchars($remarks['principal_remark'] ?? ''); ?></p>
            <p><strong>Teacher's Remark:</strong> <?php echo htmlspecialchars($remarks['teacher_remark'] ?? ''); ?></p>
            <p><strong>Next Term Begins:</strong> <?php echo htmlspecialchars($term_details['next_term_begins'] ?? 'N/A'); ?></p>
            <p><strong>No of Days School Open:</strong> <?php echo htmlspecialchars($term_details['school_open_days'] ?? 'N/A'); ?></p>
        </div>
    </div>
<?php } ?>

</body>
</html>
