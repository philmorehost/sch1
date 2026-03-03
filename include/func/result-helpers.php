<?php
// include/func/result-helpers.php

if (!function_exists('studentName')) {
    function studentName($student_info, $school_id)
    {
        global $connection_server;
        $student_name = "N/A";
        $sql = "SELECT lastname, firstname, othername FROM sm_students WHERE school_id_number=? AND admission_number=? LIMIT 1";
        $stmt = mysqli_prepare($connection_server, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $school_id, $student_info);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($student_name_array = mysqli_fetch_assoc($result)) {
                $student_name = $student_name_array["lastname"] . " " . $student_name_array["firstname"] . " " . $student_name_array["othername"];
            }
            mysqli_stmt_close($stmt);
        }
        return $student_name;
    }
}

if (!function_exists('getScoreGrade')) {
    function getScoreGrade($score_info, $type_info, $school_id)
    {
        global $connection_server;
        $grade_name = "N/A";
        $sql = "SELECT grade_name, grade_comment, mark_from, mark_upto FROM sm_grades WHERE school_id_number=?";
        $stmt = mysqli_prepare($connection_server, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $school_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($grade_name_array = mysqli_fetch_assoc($result)) {
                if ($score_info >= (int)$grade_name_array["mark_from"] && $score_info <= (int)$grade_name_array["mark_upto"]) {
                    if ($type_info == "grade") {
                        $grade_name = $grade_name_array["grade_name"];
                    } elseif ($type_info == "remark") {
                        $grade_name = $grade_name_array["grade_comment"];
                    }
                    break;
                }
            }
            mysqli_stmt_close($stmt);
        }
        return $grade_name;
    }
}

if (!function_exists('subjectName')) {
    function subjectName($subjects_info, $school_id)
    {
        global $connection_server;
        $subject_name = "N/A";
        $sql = "SELECT subject_name, subject_code FROM sm_subjects WHERE school_id_number=? AND subject_code=? LIMIT 1";
        $stmt = mysqli_prepare($connection_server, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $school_id, $subjects_info);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($subject_name_array = mysqli_fetch_assoc($result)) {
                $subject_name = $subject_name_array["subject_name"] . " (" . $subject_name_array["subject_code"] . ")";
            }
            mysqli_stmt_close($stmt);
        }
        return $subject_name;
    }
}
?>