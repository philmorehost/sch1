<?php
// include/cbt-activation.php

// This file is the UI part for the CBT Activation page.
// The main logic is in func/cbt-activation.php, which should be included before this file.
// Since bc-admin.php now handles the inclusion, this file can focus on presentation.

// No direct logic here, everything is prepared by bc-admin.php and func/cbt-activation.php
// The required variables like $select_all_school_table_lists are expected to be set.
require_once 'include/func/activation-helper.php';

displayActivationPage($connection_server, 'CBT', 'sm_cbt_activated_schools');
