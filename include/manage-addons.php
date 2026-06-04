<?php
// include/manage-addons.php

if (!isset($_SESSION["sup_adm_session"])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">';
    echo '<center><p class="color-4">Access Denied. You do not have permission to view this page.</p></center>';
    echo '</div>';
    return;
}
?>

<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Manage Addon Requests</h2>
            <p class="color-5">View and manage addon module requests from all schools.</p>

            <div class="scroll-box bg-2 mobile-width-96 system-width-96">
                <table class="table-tag mobile-font-size-12 system-font-size-14">
                    <thead>
                        <tr>
                            <th>School Name</th>
                            <th>Addon Module</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($addon_requests)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No addon requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($addon_requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['school_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['addon_name']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($request['status'])); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($request['request_date']))); ?></td>
                                    <td>
                                        <?php
                                            $activation_page_map = [
                                                'CBT' => 'smgt_cbt_activation',
                                                'Data Cleanup' => 'smgt_cleanup_activation',
                                                'Bulk Report Card' => 'smgt_bulk_report_card_activation',
                                                'I.D. Card Generation' => 'smgt_id_card_activation',
                                                'Live Classes' => 'smgt_live_classes_activation',
                                            ];
                                            $activation_page = $activation_page_map[$request['addon_name']] ?? '';
                                            if ($activation_page):
                                        ?>
                                            <a href="/bc-admin.php?page=<?php echo $activation_page; ?>&tab=true" style="text-decoration: underline;" class="color-5">
                                                Go to Activation Page
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </center>
</div>
