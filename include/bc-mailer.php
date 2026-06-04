<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

function customBCMailSender($from, $to, $subject, $message, $headers, $smtp_settings = []) {
    if (!empty($smtp_settings['smtp_host']) && !empty($smtp_settings['smtp_username'])) {
        $smtpMAIL = new PHPMailer(true);
        try {
            // Server settings
            $smtpMAIL->isSMTP();
            $smtpMAIL->Host = $smtp_settings['smtp_host'];
            $smtpMAIL->SMTPAuth = true;
            $smtpMAIL->Username = $smtp_settings['smtp_username'];
            $smtpMAIL->Password = $smtp_settings['smtp_password'];
            $smtpMAIL->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $smtpMAIL->Port = $smtp_settings['smtp_port'];

            // Sender and recipient settings
            $smtpMAIL->setFrom($from, $from);
            $smtpMAIL->addAddress($to, $to);
            $smtpMAIL->addReplyTo($from, $from);

            // Setting the email content
            $smtpMAIL->IsHTML(true);
            $smtpMAIL->Subject = $subject;
            $smtpMAIL->Body = $message;
            $smtpMAIL->AltBody = $message;
            $smtpMAIL->send();
        } catch (Exception $e) {
            // Log error, but don't show to user
        }
    } else {
        // Inbuilt Mail Functions
        mail($to, $subject, $message, $headers);
    }
}

?>