<?php
// bulk-import-student.php
if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Bulk Student Import</h2>
            <p class="color-5">Import multiple student records from a CSV file.</p>

            <div class="import-instructions">
                <h3 class="color-7">Instructions:</h3>
                <p class="color-5">Please prepare a CSV file with the following columns in order:</p>
                <ul class="color-5">
                    <li>Surname</li>
                    <li>First Name</li>
                    <li>Other Names</li>
                    <li>Gender</li>
                    <li>Date of Birth (YYYY-MM-DD)</li>
                    <li>Blood Group</li>
                    <li>Residence Address</li>
                    <li>City</li>
                    <li>Local Council/LGA</li>
                    <li>State of Residence</li>
                    <li>State of Origin</li>
                    <li>Country of Residence</li>
                    <li>Nationality</li>
                    <li>Phone Number</li>
                    <li>Email</li>
                    <li>Admission Year</li>
                    <li>Parent ID</li>
                    <li>Class ID</li>
                    <li>Session</li>
                    <li>Previous School</li>
                    <li>Previous Class</li>
                    <li>Bus ID</li>
                    <li>Class Category ID</li>
                    <li>Password</li>
                </ul>
                <a href="/include/student_import_template.csv" class="button-box color-2 bg-4 onhover-bg-color-7">Download CSV Template</a>
            </div>

            <div class="form-container">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="student_csv" class="form-label">Upload CSV File:</label>
                        <input type="file" name="student_csv" id="student_csv" class="form-file-chooser" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="import_students" class="button-box color-2 bg-4 onhover-bg-color-7">Import Students</button>
                    </div>
                </form>
            </div>
             <?php if (!empty($err_msg)): ?>
                <p class="color-4"><?php echo $err_msg; ?></p>
            <?php endif; ?>
        </div>
    </center>
</div>
<style>
.import-instructions {
    text-align: left;
    margin-bottom: 2rem;
}
.import-instructions ul {
    list-style-type: disc;
    margin-left: 20px;
}
</style>
