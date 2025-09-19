<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Setting;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminSettingsController extends Controller
{
    public function onConstruct()
    {
        requireAuth();
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/admin");
        }
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin Settings | Dashboard");
    }

    public function manage(Request $request, Response $response)
    {
        // Get all settings grouped by category
        $settings = Setting::getAllGrouped();
        
        // Determine active tab from query parameter or default to first category
        $activeTab = $request->query('tab', '');
        if (empty($activeTab) && !empty($settings)) {
            $activeTab = array_key_first($settings);
        }
        
        // Seed default settings if none exist
        if (empty($settings)) {
            Setting::seedDefaults();
            $settings = Setting::getAllGrouped();
            if (!empty($settings)) {
                $activeTab = array_key_first($settings);
            }
        }

        $view = [
            'settings' => $settings,
            'activeTab' => $activeTab,
            'totalSettings' => array_sum(array_map('count', $settings))
        ];

        return $this->render('admin/settings/manage', $view);
    }

    public function create(Request $request, Response $response)
    {
        // Get available categories
        $categories = [
            'application' => 'Application',
            'contact' => 'Contact',
            'social' => 'Social Media',
            'email' => 'Email',
            'payment' => 'Payment',
            'system' => 'System',
            'seo' => 'SEO',
            'security' => 'Security',
            'notifications' => 'Notifications',
            'legal' => 'Legal',
            'api' => 'API',
            'cache' => 'Cache'
        ];

        // Get available types
        $types = [
            'string' => 'String',
            'integer' => 'Integer',
            'boolean' => 'Boolean',
            'json' => 'JSON',
            'text' => 'Text',
            'email' => 'Email',
            'url' => 'URL'
        ];

        $view = [
            'categories' => $categories,
            'types' => $types
        ];

        return $this->render('admin/settings/create', $view);
    }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'key' => 'required|min:2|max:100|unique:settings.key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text,email,url',
            'category' => 'required|min:2|max:50',
            'description' => 'nullable|max:255',
            'is_editable' => 'boolean'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/settings/create");
        }

        try {
            $data = $request->all();
            
            // Convert boolean value for is_editable
            $data['is_editable'] = isset($data['is_editable']) ? 1 : 0;
            
            // Format value based on type
            if ($data['type'] === 'boolean') {
                $data['value'] = isset($data['value']) && $data['value'] ? '1' : '0';
            } elseif ($data['type'] === 'integer') {
                $data['value'] = (string) intval($data['value'] ?? 0);
            }

            $setting = Setting::create($data);

            if (!$setting) {
                throw new \RuntimeException('Setting creation failed');
            }

            FlashMessage::setMessage("New Setting Created Successfully!");
            return $response->redirect("/admin/settings/manage?tab=" . $data['category']);
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings/create");
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            FlashMessage::setMessage("Setting Not Found!", 'danger');
            return $response->redirect("/admin/settings/manage");
        }

        // Get available categories
        $categories = [
            'application' => 'Application',
            'contact' => 'Contact',
            'social' => 'Social Media',
            'email' => 'Email',
            'payment' => 'Payment',
            'system' => 'System',
            'seo' => 'SEO',
            'security' => 'Security',
            'notifications' => 'Notifications',
            'legal' => 'Legal',
            'api' => 'API',
            'cache' => 'Cache'
        ];

        // Get available types
        $types = [
            'string' => 'String',
            'integer' => 'Integer',
            'boolean' => 'Boolean',
            'json' => 'JSON',
            'text' => 'Text',
            'email' => 'Email',
            'url' => 'URL'
        ];

        $view = [
            'setting' => $setting,
            'categories' => $categories,
            'types' => $types
        ];

        return $this->render('admin/settings/edit', $view);
    }

    public function update(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $setting = Setting::find($id);

        if (!$setting) {
            FlashMessage::setMessage("Setting Not Found!", 'danger');
            return $response->redirect("/admin/settings/manage");
        }

        $rules = [
            'key' => "required|min:2|max:100|unique:settings.key,key!={$setting->key}",
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text,email,url',
            'category' => 'required|min:2|max:50',
            'description' => 'nullable|max:255',
            'is_editable' => 'boolean'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/settings/edit/{$id}");
        }

        try {
            $data = $request->all();
            
            // Convert boolean value for is_editable
            $data['is_editable'] = isset($data['is_editable']) ? 1 : 0;
            
            // Format value based on type
            if ($data['type'] === 'boolean') {
                $data['value'] = isset($data['value']) && $data['value'] ? '1' : '0';
            } elseif ($data['type'] === 'integer') {
                $data['value'] = (string) intval($data['value'] ?? 0);
            }

            if ($setting->updateInstance($data)) {
                FlashMessage::setMessage("Setting Updated Successfully!");
                return $response->redirect("/admin/settings/manage?tab=" . $data['category']);
            }
            throw new \RuntimeException('Update operation failed');
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/settings/edit/{$id}");
        }
    }

    public function delete(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $setting = Setting::find($id);

        if (!$setting) {
            FlashMessage::setMessage("Setting Not Found!", 'danger');
            return $response->redirect("/admin/settings/manage");
        }

        try {
            if ($setting->delete()) {
                FlashMessage::setMessage("Setting Deleted Successfully!");
                return $response->redirect("/admin/settings/manage");
            }

            throw new \RuntimeException('Delete operation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("Delete Failed! Please try again.", "danger");
            return $response->redirect("/admin/settings/manage");
        }
    }

    public function updateSetting(Request $request, Response $response)
    {
        // Handle AJAX requests for individual setting updates
        if (!$request->isAjax()) {
            return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        try {
            $input = json_decode($request->getBody(), true);
            $id = $input['id'] ?? null;
            $value = $input['value'] ?? '';

            if (!$id) {
                return $response->json(['success' => false, 'message' => 'Setting ID is required'], 400);
            }

            $setting = Setting::find($id);
            if (!$setting) {
                return $response->json(['success' => false, 'message' => 'Setting not found'], 404);
            }

            if (!$setting->is_editable) {
                return $response->json(['success' => false, 'message' => 'This setting is not editable'], 403);
            }

            // Format value based on type
            $formattedValue = $value;
            if ($setting->type === 'boolean') {
                $formattedValue = $value ? '1' : '0';
            } elseif ($setting->type === 'integer') {
                $formattedValue = (string) intval($value);
            }

            // Basic validation based on type
            if ($setting->type === 'email' && !empty($formattedValue) && !filter_var($formattedValue, FILTER_VALIDATE_EMAIL)) {
                return $response->json(['success' => false, 'message' => 'Invalid email format'], 400);
            }

            if ($setting->type === 'url' && !empty($formattedValue) && !filter_var($formattedValue, FILTER_VALIDATE_URL)) {
                return $response->json(['success' => false, 'message' => 'Invalid URL format'], 400);
            }

            if ($setting->updateInstance(['value' => $formattedValue])) {
                return $response->json([
                    'success' => true,
                    'message' => 'Setting updated successfully',
                    'data' => [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $formattedValue,
                        'type' => $setting->type
                    ]
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update setting'], 500);
            }

        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearCache(Request $request, Response $response)
    {
        // Handle AJAX requests for cache clearing
        if (!$request->isAjax()) {
            return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        try {
            // Clear different types of cache
            $cleared = [];
            
            // Clear PHP OPCache if available
            // if (function_exists('opcache_reset')) {
            //     opcache_reset();
            //     $cleared[] = 'OPCache';
            // }

            // Clear APCu cache if available
            // if (function_exists('apcu_clear_cache')) {
            //     apcu_clear_cache();
            //     $cleared[] = 'APCu';
            // }

            // Clear custom application cache (if you have a cache directory)
            $cacheDir = dirname(__DIR__, 2) . '/cache';
            if (is_dir($cacheDir)) {
                $this->clearDirectory($cacheDir);
                $cleared[] = 'File Cache';
            }

            // Clear session cache files if they exist
            $sessionDir = dirname(__DIR__, 2) . '/storage/sessions';
            if (is_dir($sessionDir)) {
                $this->clearDirectory($sessionDir);
                $cleared[] = 'Session Files';
            }

            $message = empty($cleared) 
                ? 'No cache types were available to clear' 
                : 'Cleared: ' . implode(', ', $cleared);

            return $response->json([
                'success' => true,
                'message' => $message,
                'cleared' => $cleared
            ]);

        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportSettings(Request $request, Response $response)
    {
        try {
            $settings = Setting::all();
            $exportData = [];

            foreach ($settings as $setting) {
                $exportData[] = [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'category' => $setting->category,
                    'description' => $setting->description,
                    'is_editable' => $setting->is_editable
                ];
            }

            $filename = 'settings_export_' . date('Y-m-d_H-i-s') . '.json';
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen(json_encode($exportData)));
            
            echo json_encode($exportData, JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            FlashMessage::setMessage("Export failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings/manage");
        }
    }

    public function importSettings(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings/manage");
        }

        try {
            $uploadedFile = $request->file('settings_file');
            
            if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('No file uploaded or upload error occurred');
            }

            $content = file_get_contents($uploadedFile['tmp_name']);
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON file');
            }

            $imported = 0;
            $skipped = 0;

            foreach ($settings as $settingData) {
                if (!isset($settingData['key']) || !isset($settingData['value'])) {
                    $skipped++;
                    continue;
                }

                $existing = Setting::findByKey($settingData['key']);
                if ($existing) {
                    // Update existing
                    $existing->updateInstance([
                        'value' => $settingData['value'],
                        'type' => $settingData['type'] ?? 'string',
                        'category' => $settingData['category'] ?? 'general',
                        'description' => $settingData['description'] ?? null,
                        'is_editable' => $settingData['is_editable'] ?? 1
                    ]);
                } else {
                    // Create new
                    Setting::create([
                        'key' => $settingData['key'],
                        'value' => $settingData['value'],
                        'type' => $settingData['type'] ?? 'string',
                        'category' => $settingData['category'] ?? 'general',
                        'description' => $settingData['description'] ?? null,
                        'is_editable' => $settingData['is_editable'] ?? 1
                    ]);
                }
                $imported++;
            }

            FlashMessage::setMessage("Settings imported successfully! Imported: {$imported}, Skipped: {$skipped}");
            return $response->redirect("/admin/settings/manage");

        } catch (\Exception $e) {
            FlashMessage::setMessage("Import failed: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings/manage");
        }
    }

    /**
     * Recursively clear a directory
     *
     * @param string $dir Directory path
     */
    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                $this->clearDirectory($filePath);
                rmdir($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
}