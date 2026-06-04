<?php
// include/live-classes.php

// The main logic is in func/live-classes.php, included by bc-admin.php
// This file just handles the presentation.

if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session']) && !isset($_SESSION['teacher_session']) && !isset($_SESSION['stu_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}
?>

<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Live Classes</h2>
            <p class="color-5">Schedule and join live classes.</p>

            <?php if (isset($_SESSION['teacher_session']) || isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])): ?>
            <div class="form-container">
                <form method="post">
                    <?php if(isset($_SESSION['mod_adm_session']) || isset($_SESSION['adm_staff_session'])): ?>
                    <div class="form-group">
                        <label for="teacher_id" class="form-label">Teacher:</label>
                        <select name="teacher_id" id="teacher_id" class="form-select" required>
                            <?php
                                $sql = "SELECT id_number, firstname, lastname FROM sm_teachers WHERE school_id_number = ?";
                                $stmt = mysqli_prepare($connection_server, $sql);
                                mysqli_stmt_bind_param($stmt, 's', $get_logged_user_details['school_id_number']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                while($row = mysqli_fetch_assoc($result)):
                            ?>
                                <option value="<?php echo htmlspecialchars($row['id_number']); ?>">
                                    <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                </option>
                            <?php endwhile; mysqli_stmt_close($stmt); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="class_id" class="form-label">Class:</label>
                        <input type="text" name="class_id" id="class_id" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="title" class="form-label">Title:</label>
                        <input type="text" name="title" id="title" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="platform" class="form-label">Platform:</label>
                        <select name="platform" id="platform" class="form-select" required>
                            <option value="Zoom">Zoom</option>
                            <option value="Google Meet">Google Meet</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="meeting_link" class="form-label">Meeting Link:</label>
                        <input type="url" name="meeting_link" id="meeting_link" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="start_time" class="form-label">Start Time:</label>
                        <input type="datetime-local" name="start_time" id="start_time" class="form-input" required>
                    </div>
                    <button type="submit" name="schedule_class" class="button-box color-2 bg-4 onhover-bg-color-7">Schedule Class</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="results-container mobile-margin-top-3 system-margin-top-2">
                <h3 class="color-7">Upcoming Classes</h3>
                <?php if (empty($live_classes)): ?>
                    <p class="color-5">No live classes scheduled.</p>
                <?php else: ?>
                    <table class="table-tag mobile-font-size-12 system-font-size-14">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Platform</th>
                                <th>Start Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($live_classes as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['title']); ?></td>
                                    <td><?php echo htmlspecialchars($class['platform']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($class['start_time']))); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($class['meeting_link']); ?>" target="_blank" class="button-box color-2 bg-4 onhover-bg-color-7">Join Class</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                    if (isset($GLOBALS['total_pages']) && $GLOBALS['total_pages'] > 1) {
                        $baseUrl = '?page=smgt_live_classes';
                        echo generatePaginationLinks($GLOBALS['page'], $GLOBALS['total_pages'], $baseUrl);
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </center>
</div>
<style>
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
</style>
