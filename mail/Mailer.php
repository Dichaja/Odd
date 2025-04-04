<?php

namespace ZzimbaOnline\Mail;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static string $lastError = '';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    public static function sendMail(
        string $to,
        string $subject,
        string $content,
        string $fromName = 'Zzimba Online'
    ): bool {
        $smtpConfigurations = [
            [
                'SMTPSecure' => PHPMailer::ENCRYPTION_STARTTLS,
                'Port'       => 587,
            ],
            [
                'SMTPSecure' => PHPMailer::ENCRYPTION_SMTPS,
                'Port'       => 465,
            ]
        ];

        $errors = [];

        foreach ($smtpConfigurations as $config) {
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'mail.zzimbaonline.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'no-reply@zzimbaonline.com';
                $mail->Password   = 'Martie@4728##';

                $mail->SMTPSecure = $config['SMTPSecure'];
                $mail->Port       = $config['Port'];

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];

                $mail->setFrom('no-reply@zzimbaonline.com', $fromName);
                $mail->addAddress($to);

                $mail->isHTML(true);
                $mail->Subject = $subject;

                $currentYear = date('Y');

                $htmlHeader = <<<HTML
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Zzimba Online Email</title>
                    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                </head>
                <body style="margin: 0; padding: 0; font-family: 'Rubik', sans-serif; background-color: #f5f5f5; color: #1f2937;">
                    <div style="width: 100%; max-width: 600px; margin: 0 auto; padding: 20px;">
                        <div style="background-color: #D92B13; padding: 24px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <img src="https://newzzimba.zzimbaonline.com/img/logo.png" alt="Zzimba Online Logo" style="max-width: 200px; height: auto;">
                        </div>
                        <div style="background-color: #ffffff; padding: 32px 24px; color: #4b5563; font-size: 16px; line-height: 1.625; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                HTML;

                $htmlFooter = <<<HTML
                        </div>
                        <div style="padding: 24px; text-align: center; margin-top: 20px;">
                            <div style="margin-bottom: 16px;">
                                <a href="https://facebook.com/zzimbaonline" style="display: inline-block; margin: 0 8px; color: #D92B13; text-decoration: none; font-size: 20px; font-weight: bold;">
                                    &#xf39e;
                                </a>
                                <a href="https://twitter.com/zzimbaonline" style="display: inline-block; margin: 0 8px; color: #D92B13; text-decoration: none; font-size: 20px; font-weight: bold;">
                                    &#x1F426;
                                </a>
                                <a href="https://instagram.com/zzimbaonline" style="display: inline-block; margin: 0 8px; color: #D92B13; text-decoration: none; font-size: 20px; font-weight: bold;">
                                    &#x1F4F7;
                                </a>
                                <a href="https://linkedin.com/company/zzimbaonline" style="display: inline-block; margin: 0 8px; color: #D92B13; text-decoration: none; font-size: 20px; font-weight: bold;">
                                    in
                                </a>
                            </div>
                            <div style="margin-bottom: 16px; font-size: 14px; color: #6b7280;">
                                <p style="margin: 4px 0;">
                                    <a href="https://zzimbaonline.com" style="color: #D92B13; text-decoration: none; font-weight: 500;">zzimbaonline.com</a>
                                </p>
                                <p style="margin: 4px 0;">Phone: +256 392 003-406</p>
                                <p style="margin: 4px 0;">Email: info@zzimbaonline.com</p>
                            </div>
                            <div style="font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                                <p style="margin: 4px 0;">&copy; ${currentYear} Zzimba Online. All rights reserved.</p>
                                <p style="margin: 4px 0;">
                                    <a href="https://zzimbaonline.com/privacy-policy" style="color: #D92B13; text-decoration: none;">Privacy Policy</a> | 
                                    <a href="https://zzimbaonline.com/terms" style="color: #D92B13; text-decoration: none;">Terms of Service</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                HTML;

                $finalHtml = $htmlHeader . $content . $htmlFooter;
                $mail->Body = $finalHtml;

                $mail->send();

                self::$lastError = '';
                return true;
            } catch (Exception $e) {
                $errors[] = "Config (" . $config['SMTPSecure'] . ", Port " . $config['Port'] . "): " . $mail->ErrorInfo;
            }
        }

        self::$lastError = implode(" | ", $errors);
        return false;
    }
}
