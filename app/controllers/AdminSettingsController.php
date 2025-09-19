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

            // Test Paystack connection
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
     * Clear application cache
     */
    private function clearApplicationCache(): bool
    {
        try {
            $cleared = true;
            
            // Clear file-based cache
            if (defined('ROOT_PATH')) {
                $cacheDir = ROOT_PATH . '/storage/cache';
                if (is_dir($cacheDir)) {
                    $cleared = $cleared && $this->clearDirectory($cacheDir, false);
                }

                // Clear view cache if exists
                $viewCacheDir = ROOT_PATH . '/storage/views';
                if (is_dir($viewCacheDir)) {
                    $cleared = $cleared && $this->clearDirectory($viewCacheDir, false);
                }
            }

            // Add other cache clearing logic here (Redis, Memcached, etc.)
            
            return $cleared;
            
        } catch (\Exception $e) {
            // Log error if logger is available
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }
            return false;
        }
    }

    /**
     * Recursively clear directory contents
     */
    private function clearDirectory(string $dir, bool $removeDir = false): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        try {
            $files = scandir($dir);
            if ($files === false) {
                return false;
            }

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                
                if (is_file($filePath)) {
                    if (!unlink($filePath)) {
                        return false;
                    }
                } elseif (is_dir($filePath)) {
                    if (!$this->clearDirectory($filePath, true)) {
                        return false;
                    }
                }
            }

            // Remove the directory itself if requested
            if ($removeDir && !rmdir($dir)) {
                return false;
            }

            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __destruct()
    {
        $this->settingModel = null;
    }
}