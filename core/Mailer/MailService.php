<?php

declare(strict_types=1);

namespace Trees\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP()
    {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = env('MAIL_HOST');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = env('MAIL_USERNAME');
            $this->mail->Password = env('MAIL_PASSWORD');
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = env('MAIL_PORT') ?: 587;
            $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        } catch (Exception $e) {
            error_log("Mail configuration error: " . $e->getMessage());
        }
    }

    public function sendEmail($recipientEmail, $subject, $body)
    {
        try {
            $this->mail->addAddress($recipientEmail);
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            return $this->mail->send();
        } catch (Exception $e) {
            echo("Email to $recipientEmail failed: " . $e->getMessage());
            return false;
        }
    }
}