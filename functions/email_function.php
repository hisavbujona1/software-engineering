<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| SMTP Configuration
|--------------------------------------------------------------------------
| IMPORTANT:
| Replace the two placeholder values below inside this file:
|
| SMTP_GMAIL
| SMTP_APP_PASSWORD
|
| Use a Gmail address and a Google 16-character App Password.
| Do not use your normal Gmail password.
*/

const SMTP_GMAIL = "hisavbujona1@gmail.com";
const SMTP_APP_PASSWORD = "hlmi zpax pntn ixqa";


function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;

    $mail->Username = SMTP_GMAIL;
    $mail->Password = SMTP_APP_PASSWORD;

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->CharSet = "UTF-8";
    $mail->isHTML(true);

    $mail->setFrom(
        SMTP_GMAIL,
        "Smart Expense Tracker"
    );

    /*
    |--------------------------------------------------------------------------
    | Temporary SMTP Debug
    |--------------------------------------------------------------------------
    | Keep 0 during normal use.
    | Change to 2 only when you need to see the exact SMTP error.
    */

    $mail->SMTPDebug = 0;

    return $mail;
}


/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

function sendVerificationEmail(string $email, string $code): bool
{
    try {

        $mail = createMailer();

        $mail->addAddress($email);

        $mail->Subject = "Smart Expense Tracker Email Verification";

        $safeCode = htmlspecialchars(
            $code,
            ENT_QUOTES,
            "UTF-8"
        );

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;
                    border:1px solid #e5e7eb;border-radius:14px;padding:28px;'>

            <h2 style='margin-top:0;color:#0d6efd;'>
                Smart Expense Tracker
            </h2>

            <p>Hello,</p>

            <p>
                Use the following verification code to verify your account:
            </p>

            <div style='font-size:32px;font-weight:700;letter-spacing:8px;
                        text-align:center;background:#f1f5f9;border-radius:10px;
                        padding:18px;margin:22px 0;'>
                {$safeCode}
            </div>

            <p>
                If you did not create this account, you can ignore this email.
            </p>

            <p style='color:#6b7280;font-size:13px;margin-bottom:0;'>
                Smart Expense Tracker Team
            </p>

        </div>
        ";

        $mail->AltBody =
            "Your Smart Expense Tracker verification code is: {$code}";

        $mail->send();

        return true;

    }
    catch (Exception $e) {

        error_log(
            "Verification email error: "
            . $e->getMessage()
            . " | PHPMailer: "
            . ($mail->ErrorInfo ?? "Unknown error")
        );

        return false;

    }
}


/*
|--------------------------------------------------------------------------
| Password Reset Email
|--------------------------------------------------------------------------
*/

function sendPasswordResetEmail(string $email, string $code): bool
{
    try {

        $mail = createMailer();

        $mail->addAddress($email);

        $mail->Subject = "Smart Expense Tracker Password Reset Code";

        $safeCode = htmlspecialchars(
            $code,
            ENT_QUOTES,
            "UTF-8"
        );

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;
                    border:1px solid #e5e7eb;border-radius:14px;padding:28px;'>

            <h2 style='margin-top:0;color:#dc3545;'>
                Password Reset Request
            </h2>

            <p>Hello,</p>

            <p>
                Use the following code to reset your
                Smart Expense Tracker password:
            </p>

            <div style='font-size:32px;font-weight:700;letter-spacing:8px;
                        text-align:center;background:#fff3cd;border-radius:10px;
                        padding:18px;margin:22px 0;'>
                {$safeCode}
            </div>

            <p>
                This code will expire in 15 minutes.
            </p>

            <p>
                If you did not request a password reset,
                ignore this email.
            </p>

            <p style='color:#6b7280;font-size:13px;margin-bottom:0;'>
                Smart Expense Tracker Team
            </p>

        </div>
        ";

        $mail->AltBody =
            "Your Smart Expense Tracker password reset code is: {$code}. "
            . "This code expires in 15 minutes.";

        $mail->send();

        return true;

    }
    catch (Exception $e) {

        error_log(
            "Password reset email error: "
            . $e->getMessage()
            . " | PHPMailer: "
            . ($mail->ErrorInfo ?? "Unknown error")
        );

        return false;

    }
}

?>