<?php
// include/addon-settings.php

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
            <h2 class="color-7">Addon Module Settings</h2>
            <p class="color-5">Configure the cost for each addon module and provide payment details for school admins.</p>

            <?php if (!empty($success_msg)): ?>
                <div class="container-box color-2 bg-4 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php elseif (!empty($err_msg)): ?>
                <div class="container-box color-4 bg-10 text-bold-800 mobile-font-size-14 system-font-size-16 border-radius-5px">
                    <?php echo htmlspecialchars($err_msg); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div style="text-align: left;" class="container-box color-5 bg-3 text-bold-500 mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
                    MODULE COSTS
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="cbt_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['CBT']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">CBT Module Cost*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="bulk_report_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['Bulk Report Card']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">Bulk Report Card Module Cost*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="cleanup_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['Data Cleanup']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">Data Cleanup Module Cost*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="id_card_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['I.D. Card Generation']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">I.D. Card Generation Module Cost*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="live_classes_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['Live Classes']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">Live Classes Module Cost*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                    <input name="sms_cost" type="number" step="0.01" placeholder="Enter cost" class="form-input" value="<?php echo htmlspecialchars($current_settings['SMS']['cost'] ?? '0.00'); ?>" required/>
                    <span class="form-span">SMS Module Cost*</span>
                </div>

                <div style="text-align: left;" class="container-box color-5 bg-3 text-bold-500 mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
                    PAYMENT INFORMATION
                </div>

                <div class="form-group mobile-width-90 system-width-95 mobile-margin-top-2 system-margin-top-2">
                    <textarea name="bank_details" placeholder="Enter bank account details and payment instructions" class="form-input" style="height: 150px;" required><?php echo htmlspecialchars($bank_details_value); ?></textarea>
                    <span class="form-span">Bank Account Details*</span>
                </div>

                <button name="save_addon_settings" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2">
                    SAVE SETTINGS
                </button>
            </form>
        </div>
    </center>
</div>
