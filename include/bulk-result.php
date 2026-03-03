<?php
// include/bulk-result.php
require_once 'include/func/activation-helper.php';

$school_id = $get_logged_user_details['school_id_number'];

if (!isFeatureActivated($connection_server, $school_id, 'sm_bulk_report_card_activated_schools')) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">';
    echo '<center>';
    echo '<p class="color-4">This feature is not activated for your school.</p>';
    echo '<a href="/bc-admin.php?page=smgt_request_addon&tab=true&id=' . htmlspecialchars($school_id) . '" class="button-box color-2 bg-4 onhover-bg-color-7" style="text-decoration: none; margin-top: 15px;">Request Activation</a>';
    echo '</center>';
    echo '</div>';
    return;
}
?>

<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Bulk Report Card Printing</h2>
            <p class="color-5">Select a class, session, and term to print the report cards for all students in that class.</p>

            <form method="post" action="bc-bulk-results.php" target="_blank">
                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="numeric-class" id="find-bulk-result-class-session" class="form-select" required>
                        <option selected disabled hidden value="">Select Class</option>
                        <?php
                        $sql_classes = "SELECT * FROM sm_classes WHERE school_id_number = ? GROUP BY numeric_class_name";
                        $stmt_classes = mysqli_prepare($connection_server, $sql_classes);
                        mysqli_stmt_bind_param($stmt_classes, 's', $school_id);
                        mysqli_stmt_execute($stmt_classes);
                        $result_classes = mysqli_stmt_get_result($stmt_classes);
                        if (mysqli_num_rows($result_classes) > 0) {
                            while ($classes_details = mysqli_fetch_assoc($result_classes)) {
                                echo '<option value="' . htmlspecialchars($classes_details["numeric_class_name"]) . '">' . htmlspecialchars($classes_details["class_name"]) . ' (' . htmlspecialchars($classes_details["numeric_class_name"]) . ')</option>';
                            }
                        }
                        mysqli_stmt_close($stmt_classes);
                        ?>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Class Name*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="session" id="add-bulk-result-class-session" class="form-select" required>
                        <option disabled hidden selected value="">Select Class Session</option>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Session Name*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="term" class="form-select" required>
                        <option disabled hidden selected value="">Select Term</option>
                        <?php
                        $sql_terms = "SELECT * FROM sm_terms WHERE school_id_number = ?";
                        $stmt_terms = mysqli_prepare($connection_server, $sql_terms);
                        mysqli_stmt_bind_param($stmt_terms, 's', $school_id);
                        mysqli_stmt_execute($stmt_terms);
                        $result_terms = mysqli_stmt_get_result($stmt_terms);
                        if (mysqli_num_rows($result_terms) > 0) {
                            while ($terms_details = mysqli_fetch_assoc($result_terms)) {
                                echo '<option value="' . htmlspecialchars($terms_details["id_number"]) . '">' . htmlspecialchars($terms_details["term_name"]) . '</option>';
                            }
                        }
                        mysqli_stmt_close($stmt_terms);
                        ?>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Term</span>
                </div>

                <input hidden id="bulk-result-school-id" name="school_id" value="<?php echo htmlspecialchars($get_logged_user_details['school_id_number']); ?>" />

                <button name="view-bulk-result" style="float: left; clear: left;" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-5 system-margin-left-3 mobile-margin-right-1 system-margin-right-1">
                    PROCEED
                </button>
            </form>
        </div>
    </center>
</div>

<script>
document.getElementById('find-bulk-result-class-session').addEventListener('change', function() {
    const find_result_class_session = document.getElementById("find-bulk-result-class-session");
    const add_result_class_session = document.getElementById("add-bulk-result-class-session");
    const result_school_id_number = document.getElementById("bulk-result-school-id");

    add_result_class_session.innerHTML = "";
    const createSelectSessionOption = document.createElement("option");
    createSelectSessionOption.hidden = true;
    createSelectSessionOption.disabled = true;
    createSelectSessionOption.selected = true;
    createSelectSessionOption.text = "Select Class Session";
    createSelectSessionOption.value = "";
    add_result_class_session.add(createSelectSessionOption);

    const classSessionHttpRequest = new XMLHttpRequest();
    classSessionHttpRequest.open("POST", "./get-class-session.php");
    classSessionHttpRequest.setRequestHeader("Content-Type", "application/json");
    const classSessionHttpRequestBody = JSON.stringify({ sch_no: result_school_id_number.value, class_id_no: find_result_class_session.value });
    classSessionHttpRequest.onload = function() {
        if ((classSessionHttpRequest.readyState == 4) && (classSessionHttpRequest.status == 200)) {
            const session_list_array = JSON.parse(classSessionHttpRequest.responseText)["response"];
            for (i = 0; i < session_list_array.length; i++) {
                const createSelectOption = document.createElement("option");
                createSelectOption.text = session_list_array[i].replace("-", "/");
                createSelectOption.value = session_list_array[i];
                add_result_class_session.add(createSelectOption);
            }
        } else {
            alert(classSessionHttpRequest.status);
        }
    }
    classSessionHttpRequest.send(classSessionHttpRequestBody);
});
</script>
