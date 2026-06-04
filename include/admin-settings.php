<?php
// include/admin-settings.php
if (!isset($_SESSION['sup_adm_session'])) {
    echo '<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1"><center><p class="color-4">Access Denied.</p></center></div>';
    return;
}
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
            <h2 class="color-7">Admin Settings</h2>
            <p class="color-5">Configure system-wide settings.</p>

            <div class="form-container">
                <form method="post">
                    <div style="text-align: left;" class="container-box color-5 bg-3 text-bold-500 mobile-width-90 system-width-95 mobile-margin-top-3 system-margin-top-2">
                        SMTP SETTINGS
                    </div>
                    <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                        <input name="smtp_host" value="<?php echo htmlspecialchars($admin_settings['smtp_host'] ?? ''); ?>" type="text" placeholder="e.g., smtp.example.com" class="form-input"/>
                        <span class="form-span">SMTP Host</span>
                    </div>
                    <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                        <input name="smtp_username" value="<?php echo htmlspecialchars($admin_settings['smtp_username'] ?? ''); ?>" type="text" placeholder="e.g., user@example.com" class="form-input"/>
                        <span class="form-span">SMTP Username</span>
                    </div>
                    <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                        <input name="smtp_password" value="<?php echo htmlspecialchars($admin_settings['smtp_password'] ?? ''); ?>" type="password" class="form-input"/>
                        <span class="form-span">SMTP Password</span>
                    </div>
                    <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2">
                        <input name="smtp_port" value="<?php echo htmlspecialchars($admin_settings['smtp_port'] ?? ''); ?>" type="number" placeholder="e.g., 587" class="form-input"/>
                        <span class="form-span">SMTP Port</span>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="save_admin_settings" class="button-box color-2 bg-4 onhover-bg-color-7">Save Settings</button>
                    </div>
                </form>
            </div>
             <?php if (!empty($err_msg)): ?>
                <p class="color-4"><?php echo $err_msg; ?></p>
            <?php endif; ?>
        </div>
    </center>
</div>
