<?php
// include/bulk-report-card-activation.php
require_once 'include/func/activation-helper.php';

if (isset($_SESSION["sup_adm_session"])) {
    displayActivationPage($connection_server, "Bulk Report Card", "sm_bulk_report_card_activated_schools");
} else {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">';
    echo '<center><p class="color-4">Access Denied. You do not have permission to view this page.</p></center>';
    echo '</div>';
}
