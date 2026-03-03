<?php
// include/id-card.php

// The main logic is in func/id-card.php, included by bc-admin.php
// This file just handles the presentation.

// Ensure user is logged in and has permission
if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}
?>

<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">I.D. Card Generation</h2>
            <p class="color-5">Generate and print I.D. cards for students and staff.</p>

            <div class="form-container">
                <form method="get" id="id-card-form">
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 'smgt_id_card'); ?>">
                    <div class="form-group">
                        <label for="generation_type" class="form-label">Select Generation Type:</label>
                        <select name="generation_type" id="generation_type" class="form-select" onchange="toggleInputFields()">
                            <option value="none" selected>-- Select --</option>
                            <option value="single_student">Single Student</option>
                            <option value="all_students">All Students</option>
                            <option value="single_staff">Single Staff</option>
                            <option value="all_staff">All Staff</option>
                        </select>
                    </div>

                    <div id="single-student-fields" style="display: none;">
                        <div class="form-group">
                            <label for="student_id" class="form-label">Student Admission Number:</label>
                            <input type="text" name="student_id" id="student_id" class="form-input" placeholder="Enter Admission Number">
                        </div>
                    </div>

                    <div id="single-staff-fields" style="display: none;">
                        <div class="form-group">
                            <label for="staff_id" class="form-label">Staff ID Number:</label>
                            <input type="text" name="staff_id" id="staff_id" class="form-input" placeholder="Enter Staff ID">
                        </div>
                    </div>

                    <button type="submit" name="generate_cards" class="button-box color-2 bg-4 onhover-bg-color-7">Generate I.D. Cards</button>
                </form>
            </div>

            <div class="results-container mobile-margin-top-3 system-margin-top-2">
                <?php
                // The $output variable is generated in func/id-card.php
                if (!empty($output)) {
                    echo $output;
                }
                ?>
            </div>
        </div>
    </center>
</div>

<style>
.id-card-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: center;
    margin-top: 20px;
}
.id-card {
    border: 1px solid #ccc;
    border-radius: 10px;
    width: 320px;
    height: 200px;
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-family: sans-serif;
    display: flex;
    flex-direction: column;
}
.id-card-header {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
.school-logo {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}
.school-name {
    font-weight: bold;
    font-size: 14px;
    color: #333;
}
.id-card-body {
    display: flex;
    margin-top: 10px;
    flex-grow: 1;
}
.profile-photo {
    width: 80px;
    height: 80px;
    border: 2px solid #ddd;
    border-radius: 5px;
    margin-right: 15px;
}
.user-details {
    font-size: 14px;
}
.user-name {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 5px;
}
.user-id, .user-class {
    margin-bottom: 3px;
    color: #555;
}
.id-card-footer {
    font-size: 10px;
    text-align: center;
}
.pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 20px;
}
.page-link {
    padding: 8px 12px;
    margin: 4px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
}
.page-link.active {
    background-color: #4CAF50;
    color: white;
    border-color: #4CAF50;
}
.page-link-dots {
    padding: 8px 12px;
    margin: 4px;
    border: 1px solid transparent;
    color: #333;
}
@media print {
    body * {
        visibility: hidden;
    }
    .id-card-container, .id-card-container * {
        visibility: visible;
    }
    .id-card-container {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>

<script>
function toggleInputFields() {
    var selection = document.getElementById('generation_type').value;
    var studentFields = document.getElementById('single-student-fields');
    var staffFields = document.getElementById('single-staff-fields');

    // Hide all conditional fields first
    studentFields.style.display = 'none';
    staffFields.style.display = 'none';

    if (selection === 'single_student') {
        studentFields.style.display = 'block';
    } else if (selection === 'single_staff') {
        staffFields.style.display = 'block';
    }
}
</script>