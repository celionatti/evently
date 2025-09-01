<?php

declare(strict_types=1);

namespace Trees\Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mail;
    private array $config;
    private bool $isConfigured = false;
    private array $mailbox = []; // For development testing

    public function __construct(array $config = [])
    {
        // Default configuration
        $this->config = array_merge([
            'environment' => env('APP_ENV', 'production'),
            'host' => env('MAIL_HOST'),
            'port' => (int) env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // tls or ssl
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME', 'Eventlyy'),
            'debug' => env('MAIL_DEBUG', false),
            'timeout' => 30,
            'development_recipients' => env('MAIL_DEV_RECIPIENTS') ? explode(',', env('MAIL_DEV_RECIPIENTS')) : [],
        ], $config);

        $this->initializePHPMailer();
        $this->setupSMTP();
    }

    /**
     * Initialize PHPMailer instance
     */
    private function initializePHPMailer(): void
    {
        $this->mail = new PHPMailer(true);
        
        // Set timeout
        $this->mail->Timeout = $this->config['timeout'];
        
        // Enable debug output if configured
        if ($this->config['debug']) {
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mail->Debugoutput = function($str, $level) {
                $this->log("PHPMailer Debug Level $level: $str");
            };
        }
    }

    /**
     * Setup SMTP configuration
     */
    private function setupSMTP(): void
    {
        try {
            // Validate required configuration
            $required = ['host', 'username', 'password', 'from_address'];
            foreach ($required as $key) {
                if (empty($this->config[$key])) {
                    throw new Exception("Missing required configuration: $key");
                }
            }

            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->Port = $this->config['port'];

            // Encryption
            if ($this->config['encryption'] === 'ssl') {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Set from address
            $this->mail->setFrom($this->config['from_address'], $this->config['from_name']);

            // SMTP options for problematic servers
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $this->isConfigured = true;
            $this->log("SMTP configuration successful");

        } catch (Exception $e) {
            $this->log("SMTP configuration error: " . $e->getMessage(), 'ERROR');
            $this->isConfigured = false;
        }
    }

    /**
     * Send email
     *
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $altBody Plain text alternative body
     * @param array $options Additional options
     * @return bool Success status
     */
    public function sendEmail($to, string $subject, string $body, ?string $altBody = null, array $options = []): bool
    {
        if (!$this->isConfigured) {
            $this->log("MailService not properly configured", 'ERROR');
            return false;
        }

        try {
            // Clear previous recipients and reset
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            $this->mail->clearBCCs();
            $this->mail->clearAttachments();

            // Handle development environment
            if ($this->config['environment'] === 'development') {
                return $this->handleDevelopmentEmail($to, $subject, $body, $altBody, $options);
            }

            // Add recipients
            $recipients = is_array($to) ? $to : [$to];
            
            // Override recipients in development if configured
            if (!empty($this->config['development_recipients'])) {
                $recipients = $this->config['development_recipients'];
                $this->log("Using development recipients: " . implode(', ', $recipients));
            }

            foreach ($recipients as $email) {
                if ($this->isValidEmail($email)) {
                    $this->mail->addAddress(trim($email));
                }
            }

            // Set content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            if ($altBody) {
                $this->mail->AltBody = $altBody;
            }

            // Handle CC
            if (isset($options['cc'])) {
                $ccEmails = is_array($options['cc']) ? $options['cc'] : [$options['cc']];
                foreach ($ccEmails as $email) {
                    if ($this->isValidEmail($email)) {
                        $this->mail->addCC(trim($email));
                    }
                }
            }

            // Handle BCC
            if (isset($options['bcc'])) {
                $bccEmails = is_array($options['bcc']) ? $options['bcc'] : [$options['bcc']];
                foreach ($bccEmails as $email) {
                    if ($this->isValidEmail($email)) {
                        $this->mail->addBCC(trim($email));
                    }
                }
            }

            // Handle Reply-To
            if (isset($options['reply_to'])) {
                $this->mail->addReplyTo($options['reply_to'], $options['reply_to_name'] ?? '');
            }

            // Handle attachments
            if (isset($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $this->mail->addAttachment($attachment);
                    }
                }
            }

            // Send the email
            $result = $this->mail->send();
            
            if ($result) {
                $this->log("Email sent successfully to: " . implode(', ', $recipients));
            } else {
                $this->log("Failed to send email to: " . implode(', ', $recipients), 'ERROR');
            }

            return $result;

        } catch (Exception $e) {
            $this->log("Email send failed: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Handle email sending in development environment
     */
    private function handleDevelopmentEmail($to, string $subject, string $body, ?string $altBody, array $options): bool
    {
        $recipients = is_array($to) ? $to : [$to];
        
        $emailData = [
            'to' => $recipients,
            'subject' => $subject,
            'body' => $body,
            'alt_body' => $altBody,
            'options' => $options,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->mailbox[] = $emailData;
        $this->log("Development email stored for: " . implode(', ', $recipients));
        
        return true;
    }

    /**
     * Send email using template
     *
     * @param string|array $to Recipients
     * @param string $subject Subject
     * @param string $template Template name
     * @param array $data Template data
     * @param array $options Email options
     * @return bool Success status
     */
    public function sendTemplate($to, string $subject, string $template, array $data = [], array $options = []): bool
    {
        $body = $this->renderTemplate($template, $data);
        return $this->sendEmail($to, $subject, $body, null, $options);
    }

    /**
     * Render email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered HTML
     */
    public function renderTemplate(string $template, array $data = []): string
    {
        // Simple template rendering - you can enhance this
        $templatePath = __DIR__ . "/templates/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \InvalidArgumentException("Template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Test SMTP connection
     *
     * @return bool Connection successful
     */
    public function testConnection(): bool
    {
        if (!$this->isConfigured) {
            return false;
        }

        try {
            return $this->mail->smtpConnect();
        } catch (Exception $e) {
            $this->log("SMTP connection test failed: " . $e->getMessage(), 'ERROR');
            return false;
        } finally {
            $this->mail->smtpClose();
        }
    }

    /**
     * Get configuration status for debugging
     *
     * @return array Configuration details
     */
    public function getConfigStatus(): array
    {
        return [
            'configured' => $this->isConfigured,
            'environment' => $this->config['environment'],
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'username' => $this->config['username'],
            'from_address' => $this->config['from_address'],
            'from_name' => $this->config['from_name'],
            'encryption' => $this->config['encryption'],
            'debug' => $this->config['debug']
        ];
    }

    /**
     * Get mailbox for development testing
     *
     * @return array Sent emails in development
     */
    public function getMailbox(): array
    {
        return $this->mailbox;
    }

    /**
     * Clear development mailbox
     *
     * @return self
     */
    public function clearMailbox(): self
    {
        $this->mailbox = [];
        return $this;
    }

    /**
     * Get the last email from mailbox (for testing)
     *
     * @return array|null Last email data
     */
    public function getLastEmail(): ?array
    {
        return end($this->mailbox) ?: null;
    }

    /**
     * Validate email address
     *
     * @param string $email Email address to validate
     * @return bool Valid email
     */
    private function isValidEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Log message
     *
     * @param string $message Message to log
     * @param string $level Log level
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] MailService: {$message}";
        
        // Log to error log
        error_log($logMessage);
        
        // You can also implement file logging here if needed
        // file_put_contents('/path/to/mail.log', $logMessage . PHP_EOL, FILE_APPEND);
    }

    /**
     * Create a quick verification email
     *
     * @param string $email Recipient email
     * @param string $name Recipient name
     * @param string $verificationUrl Verification URL
     * @return bool Success status
     */
    public function sendVerificationEmail(string $email, string $name, string $verificationUrl): bool
    {
        $subject = "Verify Your Email - Eventlyy";
        
        $body = $this->createVerificationEmailBody($name, $verificationUrl);
        
        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Create verification email HTML body
     *
     * @param string $name Recipient name
     * @param string $verificationUrl Verification URL
     * @return string HTML body
     */
    private function createVerificationEmailBody(string $name, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px 20px; }
                .content h2 { color: #333; margin-top: 0; }
                .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #007bff, #0056b3); color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { background: linear-gradient(135deg, #0056b3, #004085); }
                .url-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; word-break: break-all; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 14px; color: #666; border-top: 1px solid #dee2e6; }
                .expiry { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Eventlyy!</h1>
                </div>
                <div class='content'>
                    <h2>Hi " . htmlspecialchars($name) . ",</h2>
                    <p>Thank you for registering with Eventlyy! We're excited to have you join our community of event organizers and attendees.</p>
                    <p>To complete your registration and start using your account, please verify your email address by clicking the button below:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . htmlspecialchars($verificationUrl) . "' class='button'>Verify Email Address</a>
                    </div>
                    
                    <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
                    <div class='url-box'>
                        " . htmlspecialchars($verificationUrl) . "
                    </div>
                    
                    <div class='expiry'>
                        <strong>⏰ Important:</strong> This verification link will expire in 24 hours for security reasons.
                    </div>
                    
                    <p>Once verified, you'll be able to:</p>
                    <ul>
                        <li>Create and manage events</li>
                        <li>Connect with attendees</li>
                        <li>Access all Eventlyy features</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>If you did not create an account with Eventlyy, you can safely ignore this email.</p>
                    <p>&copy; " . date('Y') . " Eventlyy. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}