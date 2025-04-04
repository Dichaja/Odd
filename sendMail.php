<?php
require_once __DIR__ . '/mail/Mailer.php';

use ZzimbaOnline\Mail\Mailer;

$recipientEmail = 'zziwa4728@gmail.com';
$subject        = 'Test Email from Zzimba';
$bodyContent    = '<p>This is a test email body for Zzimba Online.</p>';

if (Mailer::sendMail($recipientEmail, $subject, $bodyContent)) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email: <br>" . Mailer::getLastError();
}
