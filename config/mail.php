<?php
// Start output buffer and disable error display
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// SMTP configs
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'mdraza7477@gmail.com');
// define('SMTP_PASS', 'lkabvvtlyvunmtfo');
define('SMTP_PASS', 'bwcxqblwiaemhthm');
define('SMTP_PORT', 587);

// Composer autoload
require '../vendor/autoload.php';

// PHPMailer class
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = 'tls';
$mail->SMTPDebug = 0; // disable debug output
$mail->isHTML(true);
$mail->Port = SMTP_PORT;

class Mail {
    public function sendMail($to, $subject, $message) {
        global $mail;

        $mail->clearAllRecipients(); // Important: clear previous recipients
        $mail->setFrom(SMTP_USER, 'Az Furniture');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;

        if (!$mail->send()) {
            
            // Optional: log error with $mail->ErrorInfo
            return false;
        }
        return true;
    }
}

$mailer = new Mail;
