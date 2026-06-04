<?php
// include/func/activation-helper.php

/**
 * Checks if a feature is activated for a given school.
 *
 * @param mysqli $connection_server The database connection.
 * @param string $school_id The school's ID number.
 * @param string $table_name The name of the activation table.
 * @return bool True if the feature is activated, false otherwise.
 */
function isFeatureActivated($connection_server, $school_id, $table_name) {
    if (empty($school_id) || empty($table_name)) {
        return false;
    }

    $safe_table_name = mysqli_real_escape_string($connection_server, $table_name);

    $check_table_sql = "SHOW TABLES LIKE '$safe_table_name'";
    $table_result = mysqli_query($connection_server, $check_table_sql);
    if (mysqli_num_rows($table_result) == 0) {
        return false;
    }

    $sql = "SELECT * FROM `{$safe_table_name}` WHERE school_id_number = ?";
    $stmt = mysqli_prepare($connection_server, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $school_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $is_activated = (mysqli_num_rows($result) > 0);
    mysqli_stmt_close($stmt);

    return $is_activated;
}


/**
 * Displays the feature activation management page for super admins.
 *
 * @param mysqli $connection_server The database connection.
 * @param string $feature_name The display name of the feature (e.g., "Cleanup").
 * @param string $table_name The database table for activation (e.g., "sm_cleanup_activated_schools").
 */
function displayActivationPage($connection_server, $feature_name, $table_name) {

    if (isset($_POST['activation-btn-' . $table_name])) {
        $school_id = trim(strip_tags($_POST['school-id']));
        $activation_id = (int)trim(strip_tags($_POST['activation-id']));

        if ($activation_id === 1) { // Enable
            $sql = "INSERT INTO `{$table_name}` (school_id_number) VALUES (?)";
            $status_to_set = 'approved';
        } else { // Disable
            $sql = "DELETE FROM `{$table_name}` WHERE school_id_number = ?";
            $status_to_set = 'rejected';
        }

        $stmt = mysqli_prepare($connection_server, $sql);
        mysqli_stmt_bind_param($stmt, 's', $school_id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if($success) {
            $update_sql = "UPDATE sm_addon_requests SET status = ? WHERE school_id_number = ? AND addon_name = ?";
            $update_stmt = mysqli_prepare($connection_server, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'sss', $status_to_set, $school_id, $feature_name);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $current_page_no = filter_input(INPUT_GET, 'prevnext', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
    $page_pnum = filter_input(INPUT_GET, 'pnum', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1]]);
    $search_text = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? "";
    $offset = ($current_page_no - 1) * $page_pnum;

    $base_query = "FROM sm_school_details";
    $params = [];
    $param_types = '';

    if (!empty($search_text)) {
        $search_conditions = [];
        $search_terms = array_filter(explode(" ", $search_text));
        foreach ($search_terms as $term) {
            $term_like = "%" . $term . "%";
            $search_conditions[] = "(email LIKE ? OR school_name LIKE ? OR school_id_number LIKE ?)";
            $param_types .= 'sss';
            array_push($params, $term_like, $term_like, $term_like);
        }
        if (!empty($search_conditions)) {
            $base_query .= " WHERE " . implode(" OR ", $search_conditions);
        }
    }

    $query_select = "SELECT * " . $base_query . " LIMIT ? OFFSET ?";
    $param_types .= 'ii';
    $params[] = $page_pnum;
    $params[] = $offset;

    $stmt_select = mysqli_prepare($connection_server, $query_select);
    mysqli_stmt_bind_param($stmt_select, $param_types, ...$params);
    mysqli_stmt_execute($stmt_select);
    $select_all_school_table_lists = mysqli_stmt_get_result($stmt_select);
    mysqli_stmt_close($stmt_select);

    $query_count = "SELECT COUNT(*) as total " . $base_query;
    $count_params = array_slice($params, 0, count($params) - 2);
    $count_param_types = substr($param_types, 0, strlen($param_types) - 2);

    $stmt_count = mysqli_prepare($connection_server, $query_count);
    if (!empty($count_param_types)) {
        mysqli_stmt_bind_param($stmt_count, $count_param_types, ...$count_params);
    }
    mysqli_stmt_execute($stmt_count);
    $count_result = mysqli_stmt_get_result($stmt_count);
    $total_records = mysqli_fetch_assoc($count_result)['total'] ?? 0;
    mysqli_stmt_close($stmt_count);

    ?>
    <div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
        <center>
            <div style="text-align: left;" class="scroll-box bg-2 mobile-width-96 system-width-96">
                <form method="post">
                    <table style="width: 100%;" class="table-tag-borderless mobile-font-size-12 system-font-size-14">
                        <thead>
                            <tr>
                                <th class="mobile-width-10 system-width-10">School</th>
                                <th class="mobile-width-20 system-width-auto"></th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (mysqli_num_rows($select_all_school_table_lists) > 0) {
                            while ($school_details = mysqli_fetch_assoc($select_all_school_table_lists)) {
                                $is_activated = isFeatureActivated($connection_server, $school_details["school_id_number"], $table_name);

                                if ($is_activated) {
                                    $status = "Activated";
                                    $button = '<span style="cursor: pointer; text-decoration: underline;" onclick="handleActivation(\'' . htmlspecialchars($school_details["school_name"]) . '\', \'' . $school_details["school_id_number"] . '\', 2, \'' . $table_name . '\');">Disable</span>';
                                } else {
                                    $status = "Not Activated";
                                    $button = '<span style="cursor: pointer; text-decoration: underline;" onclick="handleActivation(\'' . htmlspecialchars($school_details["school_name"]) . '\', \'' . $school_details["school_id_number"] . '\', 1, \'' . $table_name . '\');">Enable</span>';
                                }

                                echo '<tr>
                                    <td><img style="position: relative; margin: -1.5% 0 0 -2%;" src="dataimg/school_' . $school_details["school_id_number"] . '.png" class="mobile-width-100 system-width-50 avatar_icon_height" /></td>
                                    <td>' . htmlspecialchars($school_details["school_name"]) . '<br><span class="color-5">' . htmlspecialchars($school_details["email"]) . '</span></td>
                                    <td>' . $status . '</td>
                                    <td>' . $button . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4" style="text-align:center;">No schools found.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                    <input type="hidden" id="school-id-<?php echo $table_name; ?>" name="school-id" />
                    <input type="hidden" id="activation-id-<?php echo $table_name; ?>" name="activation-id" />
                    <button type="submit" id="activation-btn-<?php echo $table_name; ?>" name="activation-btn-<?php echo $table_name; ?>" hidden><?php echo htmlspecialchars($feature_name); ?> Activation</button>
                </form>
                <div style="float: right;" class="container-box bg-3 mobile-width-100 system-width-22">
                    <a style="text-decoration: none;" href="<?php echo strtok($_SERVER["REQUEST_URI"], '?') . '?page=' . htmlspecialchars($_GET['page']) . '&tab=true&prevnext=' . ($current_page_no - 1); ?>">
                        <button type="button" class="button-box color-7 bg-6">Previous</button>
                    </a>
                    <button type="button" class="button-box color-2 bg-4 onhover-bg-color-7"><?php echo $current_page_no; ?></button>
                    <a style="text-decoration: none;" href="<?php echo strtok($_SERVER["REQUEST_URI"], '?') . '?page=' . htmlspecialchars($_GET['page']) . '&tab=true&prevnext=' . ($current_page_no + 1); ?>">
                        <button type="button" class="button-box color-7 bg-6">Next</button>
                    </a>
                </div>
            </div>
        </center>
        <script>
            function handleActivation(schoolName, schoolId, activationType, tableName) {
                const schoolIdInput = document.getElementById("school-id-" + tableName);
                const activationIdInput = document.getElementById("activation-id-" + tableName);
                const activationBtn = document.getElementById("activation-btn-" + tableName);
                const activationArr = { 1: "Enable", 2: "Disable" };
                if (confirm("Are You Sure You Want To " + activationArr[activationType] + " " + schoolName + " <?php echo htmlspecialchars($feature_name); ?> Portal?")) {
                    schoolIdInput.value = schoolId;
                    activationIdInput.value = activationType;
                    activationBtn.click();
                } else {
                    alert("Operation cancelled");
                }
            }
        </script>
    </div>
    <?php
}
?>
