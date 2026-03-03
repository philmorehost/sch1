<?php
// include/bulk-messaging.php
if (!isset($_SESSION['sup_adm_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Bulk Messaging</h2>
            <p class="color-5">Send Email or SMS to school admins.</p>

            <div class="form-container">
                <form method="post">
                    <div class="form-group">
                        <label for="recipient_type" class="form-label">Recipient:</label>
                        <select name="recipient_type" id="recipient_type" class="form-select" onchange="toggleSchoolSelection()">
                            <option value="all">All Schools</option>
                            <option value="single">Single School</option>
                        </select>
                    </div>

                    <div id="school-selection" style="display: none;" class="form-group">
                        <label for="school_id" class="form-label">Select School:</label>
                        <select name="school_id" id="school_id" class="form-select">
                            <?php foreach ($all_schools as $school): ?>
                                <option value="<?php echo htmlspecialchars($school['school_id_number']); ?>">
                                    <?php echo htmlspecialchars($school['school_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message_type" class="form-label">Message Type:</label>
                        <select name="message_type" id="message_type" class="form-select">
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
                        <button type="submit" name="send_message" class="button-box color-2 bg-4 onhover-bg-color-7">Send Message</button>
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
function toggleSchoolSelection() {
    var recipientType = document.getElementById('recipient_type').value;
    var schoolSelection = document.getElementById('school-selection');
    schoolSelection.style.display = (recipientType === 'single') ? 'block' : 'none';
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
