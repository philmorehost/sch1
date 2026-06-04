<?php
// include/request-addon.php

if (!isset($_SESSION['mod_adm_session']) && !isset($_SESSION['adm_staff_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">';
    echo '<center><p class="color-4">Access Denied. You do not have permission to view this page.</p></center>';
    echo '</div>';
    return;
}
?>

<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Request Addon Module</h2>
            <p class="color-5">Request activation for paid addon modules for your school.</p>

            <?php if (!empty($success_msg)): ?>
                <div class="container-box color-2 bg-4 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php elseif (!empty($err_msg)): ?>
                <div class="container-box color-4 bg-10 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px">
                    <?php echo htmlspecialchars($err_msg); ?>
                </div>
            <?php endif; ?>

            <div style="text-align: left;" class="container-box color-5 bg-3 text-bold-500 mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
                AVAILABLE MODULES
            </div>

            <?php if (empty($addon_settings)): ?>
            <div class="container-box bg-3 mobile-width-90 system-width-95 mobile-margin-top-2 system-margin-top-2" style="text-align: center; padding: 15px;">
                <p class="color-6">No addon modules are available at the moment, or they have not been configured by the administrator. Please check back later.</p>
            </div>
            <?php else: ?>
            <form method="post">
                <div class="form-group mobile-width-90 system-width-95 mobile-margin-top-2 system-margin-top-2">
                    <select name="addon_name" class="form-select" required>
                        <option value="" disabled selected>Select a module...</option>
                        <?php foreach($addon_settings as $name => $details): ?>
                            <?php
                                $is_requested = isset($existing_requests[$name]);
                                $is_activated = isFeatureActivated($connection_server, $school_id, $details['activation_table']);
                                $disabled = $is_requested || $is_activated;
                                $status_text = '';
                                if ($is_activated) {
                                    $status_text = ' (Already Activated)';
                                } elseif ($is_requested) {
                                    $status_text = ' (Request Pending)';
                                }
                                $cost = $details['cost'] ?? '0.00';
                            ?>
                            <option value="<?php echo htmlspecialchars($name); ?>" <?php if($disabled) echo 'disabled'; ?>>
                                <?php echo htmlspecialchars($name) . ' - Cost: ' . htmlspecialchars($cost) . $status_text; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-span">Select Module*</span>
                </div>

                <div style="text-align: left;" class="container-box color-5 bg-3 text-bold-500 mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
                    PAYMENT INSTRUCTIONS
                </div>
                <div class="container-box bg-3 mobile-width-90 system-width-95 mobile-margin-top-2 system-margin-top-2" style="text-align: left; padding: 15px;">
                    <p class="color-7" style="white-space: pre-wrap;"><?php echo htmlspecialchars($bank_details_value ?? 'Not configured by administrator.'); ?></p>
                </div>

                <button name="request_addon_module" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2">
                    SUBMIT REQUEST
                </button>
            </form>
            <?php endif; ?>
        </div>
    </center>
</div>
