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
                <body style="margin: 0; padding: 0; font-family: 'Rubik', sans-serif; background-color: #f8f9fa; color: #1f2937; line-height: 1.6;">
                    <div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                        <!-- Header -->
                        <div style="background-color: #D92B13; padding: 24px; text-align: center; border-top-left-radius: 4px; border-top-right-radius: 4px;">
                            <img src="https://newzzimba.zzimbaonline.com/img/logo.png" alt="Zzimba Online Logo" style="max-width: 180px; height: auto;">
                        </div>
                        
                        <!-- Content Area -->
                        <div style="background-color: #ffffff; padding: 40px 30px; color: #4b5563; font-size: 16px; line-height: 1.7; border-left: 1px solid #f3f4f6; border-right: 1px solid #f3f4f6;">
                HTML;

                $htmlFooter = <<<HTML
                        </div>
                        
                        <!-- Call to Action -->
                        <div style="background-color: #f9fafb; padding: 0 30px; text-align: center; border-top: 1px solid #f3f4f6;">
                            <a href="https://zzimbaonline.com" style="display: inline-block; padding: 10px 24px; background-color: #D92B13; color: #ffffff; text-decoration: none; font-weight: 500; border-radius: 4px; font-size: 15px;">Visit Our Website</a>
                        </div>
                        
                        <!-- Social Media -->
                        <div style="padding: 0 30px; text-align: center; background-color: #ffffff; border-bottom: 1px solid #f3f4f6;">
                            <p style="margin-bottom: 15px; font-size: 15px; color: #6b7280; font-weight: 500;">Connect With Us</p>
                            <div>
                                <a href="https://facebook.com/zzimbaonline" style="display: inline-block; margin: 0 10px; color: #4b5563; text-decoration: none;">
                                    <img src="https://cdn-icons-png.flaticon.com/128/733/733547.png" alt="Facebook" style="width: 24px; height: 24px;">
                                </a>
                                <a href="https://twitter.com/zzimbaonline" style="display: inline-block; margin: 0 10px; color: #4b5563; text-decoration: none;">
                                    <img src="https://cdn-icons-png.flaticon.com/128/733/733579.png" alt="Twitter" style="width: 24px; height: 24px;">
                                </a>
                                <a href="https://instagram.com/zzimbaonline" style="display: inline-block; margin: 0 10px; color: #4b5563; text-decoration: none;">
                                    <img src="https://cdn-icons-png.flaticon.com/128/733/733558.png" alt="Instagram" style="width: 24px; height: 24px;">
                                </a>
                                <a href="https://linkedin.com/company/zzimbaonline" style="display: inline-block; margin: 0 10px; color: #4b5563; text-decoration: none;">
                                    <img src="https://cdn-icons-png.flaticon.com/128/3536/3536505.png" alt="LinkedIn" style="width: 24px; height: 24px;">
                                </a>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div style="padding: 0 30px; text-align: center; background-color: #f9fafb; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                            <div style="margin-bottom: 15px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                <p style="margin: 5px 0;">Phone: +256 392 003-406</p>
                                <p style="margin: 5px 0;">Email: info@zzimbaonline.com</p>
                            </div>
                            <div style="font-size: 13px; color: #9ca3af; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 5px 0;">&copy; ${currentYear} Zzimba Online. All rights reserved.</p>
                                <p style="margin: 8px 0;">
                                    <a href="https://zzimbaonline.com/terms-and-conditions" style="color: #6b7280; text-decoration: none; margin: 0 8px;">Terms of Service</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 0 20px; font-size: 12px; color: #9ca3af;">
                        <p>If you did not request this email, please disregard it or contact our support team.</p>
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
