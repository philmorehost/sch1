<?php
// school-bulk-messaging.php
if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}

$school_id = $get_logged_user_details['school_id_number'] ?? '';
$all_classes = [];
if ($school_id) {
    $result = mysqli_query($connection_server, "SELECT numeric_class_name, class_name FROM sm_classes WHERE school_id_number = '$school_id'");
    while ($row = mysqli_fetch_assoc($result)) {
        $all_classes[] = $row;
    }
}
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">School Bulk Messaging</h2>
            <p class="color-5">Send Email or SMS to students and parents.</p>

            <?php if (!empty($err_msg)): ?>
                <p class="color-4"><?php echo $err_msg; ?></p>
            <?php endif; ?>
            <?php if (!empty($success_msg)): ?>
                <p class="color-2"><?php echo $success_msg; ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form method="post">
                    <div class="form-group">
                        <label for="recipient_group" class="form-label">Recipient Group:</label>
                        <select name="recipient_group" id="recipient_group" class="form-select" onchange="toggleRecipientOptions()">
                            <option value="all_students">All Students</option>
                            <option value="all_parents">All Parents</option>
                            <option value="class">By Class</option>
                        </select>
                    </div>

                    <div id="class-selection" style="display: none;" class="form-group">
                        <label for="class_id" class="form-label">Select Class:</label>
                        <select name="class_id" id="class_id" class="form-select">
                            <?php foreach ($all_classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class['numeric_class_name']); ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message_type" class="form-label">Message Type:</label>
                        <select name="message_type" id="message_type" class="form-select" onchange="toggleSenderIdField()">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div id="sms-sender-id-group" class="form-group" style="display: none;">
                        <label for="sender_id" class="form-label">Sender ID:</label>
                        <input type="text" name="sender_id" id="sender_id" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject:</label>
                        <input type="text" name="subject" id="subject" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message:</label>
                        <textarea name="message" id="message" class="form-input" rows="10"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="send_school_message" class="button-box color-2 bg-4 onhover-bg-color-7">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </center>
</div>

<style>
.form-container {
    max-width: 800px;
    margin: auto;
}
.form-group {
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    text-align: left;
}
.form-label {
    margin-bottom: 0.5rem;
}
</style>

<script>
function toggleRecipientOptions() {
    var recipientGroup = document.getElementById('recipient_group').value;
    var classSelection = document.getElementById('class-selection');
    classSelection.style.display = (recipientGroup === 'class') ? 'block' : 'none';
}

function toggleSenderIdField() {
    var messageType = document.getElementById('message_type').value;
    var senderIdGroup = document.getElementById('sms-sender-id-group');
    senderIdGroup.style.display = (messageType === 'sms') ? 'block' : 'none';
}

// Add event listener to message type dropdown
document.getElementById('message_type').addEventListener('change', toggleSenderIdField);
// Call on page load to set initial state
toggleSenderIdField();
</script>