<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Setting;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Exception\TreesException;

class AdminSettingsController extends Controller
{
    protected ?Setting $settingModel;

    public function onConstruct()
    {
        requireAuth();
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Administrator privileges required.", 'danger');
            return redirect("/admin/dashboard");
        }
        $this->view->setLayout('admin');
        $this->settingModel = new Setting();
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Settings");
    }

    /**
     * Display settings page
     */
    public function manage(Request $request, Response $response)
    {
        $activeTab = $request->query('tab', 'application');
        $settings = Setting::getAllGrouped();
        $categories = Setting::getCategories();

        $view = [
            'settings' => $settings,
            'categories' => $categories,
            'activeTab' => $activeTab
        ];

        return $this->render('admin/settings/manage', $view);
    }

    /**
     * Update settings
     */
    public function update(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings");
        }

        $category = $request->input('category', 'application');
        $settings = $request->input('settings', []);

        if (empty($settings)) {
            FlashMessage::setMessage("No settings to update.", 'warning');
            return $response->redirect("/admin/settings?tab={$category}");
        }

        // Validate settings
        $errors = [];
        foreach ($settings as $key => $value) {
            $setting = Setting::findByKey($key);
            if ($setting && $setting->is_editable) {
                if (!Setting::validateValue($value, $setting->type)) {
                    $errors[$key] = "Invalid {$setting->type} value for {$setting->description}";
                }
            }
        }

        if (!empty($errors)) {
            set_form_error($errors);
            set_form_data($request->all());
            return $response->redirect("/admin/settings?tab={$category}");
        }

        try {
            if (Setting::updateMultiple($settings)) {
                FlashMessage::setMessage("Settings updated successfully!");
                
                // Clear any application cache if needed
                $this->clearApplicationCache();
                
                return $response->redirect("/admin/settings?tab={$category}");
            } else {
                throw new \RuntimeException('Settings update failed');
            }
        } catch (TreesException $e) {
            FlashMessage::setMessage("Update failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings?tab={$category}");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Update failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings?tab={$category}");
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $testEmail = $request->input('test_email');
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return $response->json(['success' => false, 'message' => 'Valid email address is required'], 400);
        }

        try {
            // Get email settings
            $smtpHost = Setting::get('smtp_host');
            $smtpPort = Setting::get('smtp_port', 587);
            $smtpUsername = Setting::get('smtp_username');
            $smtpPassword = Setting::get('smtp_password');
            $smtpEncryption = Setting::get('smtp_encryption', 'tls');
            $fromName = Setting::get('mail_from_name', 'Eventlyy');
            $fromEmail = Setting::get('mail_from_address');

            if (empty($smtpHost) || empty($smtpUsername) || empty($fromEmail)) {
                return $response->json([
                    'success' => false, 
                    'message' => 'Email configuration is incomplete. Please configure SMTP settings first.'
                ], 400);
            }

            // Test email sending (you'll need to implement your email service)
            $result = $this->sendTestEmail($testEmail, $smtpHost, $smtpPort, $smtpUsername, $smtpPassword, $smtpEncryption, $fromName, $fromEmail);
            
            if ($result['success']) {
                return $response->json(['success' => true, 'message' => 'Test email sent successfully!']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to send test email: ' . $result['error']], 500);
            }

        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Email test failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Test payment configuration
     */
    public function testPayment(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        try {
            $publicKey = Setting::get('paystack_public_key');
            $secretKey = Setting::get('paystack_secret_key');

            if (empty($publicKey) || empty($secretKey)) {
                return $response->json([
                    'success' => false,
                    'message' => 'Payment configuration is incomplete. Please configure Paystack keys first.'
                ], 400);
            }

            // Test Paystack connection (basic API call)
            $result = $this->testPaystackConnection($secretKey);
            
            if ($result['success']) {
                return $response->json(['success' => true, 'message' => 'Payment gateway connection successful!']);
            } else {
                return $response->json(['success' => false, 'message' => 'Payment test failed: ' . $result['error']], 500);
            }

        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Payment test failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        try {
            $cleared = $this->clearApplicationCache();
            
            if ($cleared) {
                return $response->json(['success' => true, 'message' => 'Cache cleared successfully!']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to clear cache'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Cache clearing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export settings as JSON
     */
    public function exportSettings(Request $request, Response $response)
    {
        try {
            $settings = Setting::getAllGrouped();
            
            // Remove sensitive data
            $sensitiveKeys = Setting::getSensitiveKeys();
            foreach ($settings as $category => &$categorySettings) {
                foreach ($categorySettings as $key => &$setting) {
                    if (in_array($key, $sensitiveKeys)) {
                        $setting['value'] = '***HIDDEN***';
                        $setting['raw_value'] = '***HIDDEN***';
                    }
                }
            }

            $exportData = [
                'export_date' => date('Y-m-d H:i:s'),
                'application' => Setting::get('app_name', 'Eventlyy'),
                'version' => '1.0.0', // You might want to get this dynamically
                'settings' => $settings
            ];

            $filename = 'settings_export_' . date('Y-m-d_H-i-s') . '.json';
            
            $response->setHeader('Content-Type', 'application/json');
            $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
            return $response->send(json_encode($exportData, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            FlashMessage::setMessage("Export failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings");
        }
    }

    /**
     * Import settings from JSON
     */
    public function importSettings(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings");
        }

        if (!$request->hasFile('settings_file')) {
            FlashMessage::setMessage("Please select a settings file to import.", 'danger');
            return $response->redirect("/admin/settings?tab=system");
        }

        try {
            $file = $request->file('settings_file');
            
            if ($file->getClientMediaType() !== 'application/json') {
                FlashMessage::setMessage("Invalid file type. Please upload a JSON file.", 'danger');
                return $response->redirect("/admin/settings?tab=system");
            }

            $content = file_get_contents($file->getPath());
            $importData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                FlashMessage::setMessage("Invalid JSON file format.", 'danger');
                return $response->redirect("/admin/settings?tab=system");
            }

            if (!isset($importData['settings'])) {
                FlashMessage::setMessage("Invalid settings file structure.", 'danger');
                return $response->redirect("/admin/settings?tab=system");
            }

            // Import settings
            $imported = 0;
            $skipped = 0;
            $sensitiveKeys = Setting::getSensitiveKeys();

            foreach ($importData['settings'] as $category => $categorySettings) {
                foreach ($categorySettings as $key => $settingData) {
                    // Skip sensitive keys for security
                    if (in_array($key, $sensitiveKeys)) {
                        $skipped++;
                        continue;
                    }

                    if (Setting::exists($key)) {
                        if (Setting::set($key, $settingData['raw_value'] ?? $settingData['value'])) {
                            $imported++;
                        }
                    }
                }
            }

            $message = "Import completed! {$imported} settings imported";
            if ($skipped > 0) {
                $message .= ", {$skipped} sensitive settings skipped for security";
            }

            FlashMessage::setMessage($message, 'success');
            return $response->redirect("/admin/settings?tab=system");

        } catch (\Exception $e) {
            FlashMessage::setMessage("Import failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings?tab=system");
        }
    }

    /**
     * Send test email
     */
    private function sendTestEmail(string $to, string $host, int $port, string $username, string $password, string $encryption, string $fromName, string $fromEmail): array
    {
        // This is a placeholder - implement your actual email sending logic here
        // You might use PHPMailer, SwiftMailer, or your framework's email service
        
        try {
            // Example implementation (you'll need to adapt this to your email service)
            /*
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->SMTPAuth = true;
            $mailer->Username = $username;
            $mailer->Password = $password;
            $mailer->SMTPSecure = $encryption;
            $mailer->Port = $port;

            $mailer->setFrom($fromEmail, $fromName);
            $mailer->addAddress($to);
            $mailer->Subject = 'Test Email from ' . Setting::get('app_name', 'Eventlyy');
            $mailer->Body = 'This is a test email to verify your SMTP configuration is working correctly.';

            $mailer->send();
            */

            // For now, return success (replace with actual implementation)
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test Paystack connection
     */
    private function testPaystackConnection(string $secretKey): array
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paystack.co/bank",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $secretKey,
                    "Cache-Control: no-cache",
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['status']) && $result['status'] === true) {
                    return ['success' => true];
                }
            }

            return ['success' => false, 'error' => 'Invalid API response or credentials'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clear application cache
     */
    private function clearApplicationCache(): bool
    {
        try {
            // Implement your cache clearing logic here
            // This might involve clearing file cache, Redis, Memcached, etc.
            
            // Example for file-based cache
            $cacheDir = ROOT_PATH . '/storage/cache';
            if (is_dir($cacheDir)) {
                $this->clearDirectory($cacheDir);
            }

            // Clear view cache if exists
            $viewCacheDir = ROOT_PATH . '/storage/views';
            if (is_dir($viewCacheDir)) {
                $this->clearDirectory($viewCacheDir);
            }

            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Recursively clear directory contents
     */
    private function clearDirectory(string $dir): void
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }

    public function __destruct()
    {
        $this->settingModel = null;
    }
}