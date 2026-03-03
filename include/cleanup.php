<?php
// include/cleanup.php
require_once 'include/func/activation-helper.php';

$school_id = $get_logged_user_details['school_id_number'];

if (!isFeatureActivated($connection_server, $school_id, 'sm_cleanup_activated_schools')) {
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
            <h2 class="color-7">Data Cleanup Tool</h2>
            <p class="color-5">This tool helps to resolve data integrity issues in the system.</p>

            <div id="cleanup-message" style="display:none;" class="container-box color-4 bg-10 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px mobile-width-80 system-width-92 mobile-padding-top-2 system-padding-top-1 mobile-padding-left-2 system-padding-left-2 mobile-padding-right-2 system-padding-right-2 mobile-padding-bottom-2 system-padding-bottom-1 mobile-margin-bottom-1 system-margin-bottom-1">
            </div>

            <div class="container-box color-4 bg-10 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px mobile-width-80 system-width-92 mobile-padding-top-2 system-padding-top-1 mobile-padding-left-2 system-padding-left-2 mobile-padding-right-2 system-padding-right-2 mobile-padding-bottom-2 system-padding-bottom-1 mobile-margin-bottom-1 system-margin-bottom-1">
                <strong>Warning:</strong> This action will permanently delete score records for subjects that no longer exist in the system. This action cannot be undone.
            </div>

            <button id="cleanup-btn" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2">
                Clean Orphaned Subject Records
            </button>
        </div>
    </center>
</div>

<script>
document.getElementById('cleanup-btn').addEventListener('click', function() {
    if (confirm("Are you sure you want to permanently delete score records for subjects that no longer exist? This action cannot be undone.")) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'include/func/cleanup.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.status == 200) {
                try {
                    var response = JSON.parse(this.responseText);
                    var messageDiv = document.getElementById('cleanup-message');
                    messageDiv.style.display = 'inline-block';
                    messageDiv.className = 'container-box text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px mobile-width-80 system-width-92 mobile-padding-top-2 system-padding-top-1 mobile-padding-left-2 system-padding-left-2 mobile-padding-right-2 system-padding-right-2 mobile-padding-bottom-2 system-padding-bottom-1 mobile-margin-bottom-1 system-margin-bottom-1';

                    if(response.status === 'success'){
                        messageDiv.classList.add('color-2', 'bg-4');
                        messageDiv.innerText = response.message;
                    // Reload the page after 2 seconds to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    } else {
                        messageDiv.classList.add('color-4', 'bg-10');
                        messageDiv.innerText = 'Error: ' + response.message;
                    }
                } catch (e) {
                    console.error("Error parsing JSON response: ", this.responseText);
                }
            }
        };

        xhr.onerror = function() {
            var messageDiv = document.getElementById('cleanup-message');
            messageDiv.style.display = 'inline-block';
            messageDiv.className = 'container-box color-4 bg-10 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px mobile-width-80 system-width-92 mobile-padding-top-2 system-padding-top-1 mobile-padding-left-2 system-padding-left-2 mobile-padding-right-2 system-padding-right-2 mobile-padding-bottom-2 system-padding-bottom-1 mobile-margin-bottom-1 system-margin-bottom-1';
            messageDiv.innerText = 'An error occurred with the request.';
        };

        xhr.send('cleanup_orphaned_subjects=1');
    }
});
</script>
