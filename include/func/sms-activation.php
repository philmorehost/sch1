<?php
// include/func/sms-activation.php

require_once 'activation-helper.php';

// The displayActivationPage function will handle the entire logic and presentation.
// We just need to call it with the correct parameters for the SMS module.
displayActivationPage($connection_server, 'SMS', 'sm_sms_activated_schools');
