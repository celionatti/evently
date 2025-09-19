<?php

declare(strict_types=1);

namespace App\Controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use App\Models\Setting;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Database\Database;
use Trees\Database\QueryBuilder\QueryBuilder;

class AdminSettingsController extends Controller
{
    protected ?Setting $settingModel;

    public function onConstruct()
    {
        requireAuth();
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/");
        }
        
        $this->view->setLayout('admin');
        $this->settingModel = new Setting();
    }

    /**
     * Display all settings grouped by category
     */
    public function manage(Request $request, Response $response)
    {
        // Get all settings
        $allSettings = Setting::all();
        
        // Group settings by category
        $settingsByCategory = [];
        foreach ($allSettings as $setting) {
            $settingsByCategory[$setting->category][$setting->key] = [
                'id' => $setting->id,
                'value' => $this->getProcessedValue($setting),
                'raw_value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_editable' => (bool)$setting->is_editable
            ];
        }
        
        // Determine active tab (default to first category)
        $categories = array_keys($settingsByCategory);
        $activeTab = $request->query('tab', $categories[0] ?? 'general');
        
        $view = [
            'settings' => $settingsByCategory,
            'activeTab' => $activeTab,
            'categories' => $categories
        ];
        
        return $this->render('admin/settings/manage', $view);
    }

    /**
     * Process setting value based on type
     */
    private function getProcessedValue(Setting $setting)
    {
        switch ($setting->type) {
            case 'boolean':
                return (bool)$setting->value;
            case 'integer':
                return (int)$setting->value;
            case 'json':
                return json_decode($setting->value, true) ?? $setting->value;
            default:
                return $setting->value;
        }
    }

    /**
     * Show form to create a new setting
     */
    public function create(Request $request, Response $response)
    {
        // Get all existing categories for the datalist
        $db = Database::getInstance();
        $builder = new QueryBuilder($db);
        $categories = $builder->table('settings')
            ->select('DISTINCT category')
            ->orderBy('category')
            ->get();
        
        $categoryList = array_map(function($item) {
            return $item->category;
        }, $categories);
        
        $view = [
            'categories' => $categoryList
        ];
        
        return $this->render('admin/settings/create', $view);
    }

    /**
     * Store a new setting
     */
    public function store(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings");
        }

        $rules = $this->settingModel->rules();
        
        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/settings/create");
        }

        try {
            $data = $request->all();
            
            // Process boolean value
            if ($data['type'] === 'boolean') {
                $data['value'] = isset($data['value']) && $data['value'] == '1' ? '1' : '0';
            }
            
            // Check if key already exists
            $existing = Setting::where(['key' => $data['key']]);
            if (!empty($existing)) {
                FlashMessage::setMessage("Setting key already exists. Please choose a unique key.", 'danger');
                set_form_data($request->all());
                return $response->redirect("/admin/settings/create");
            }
            
            // Create the setting
            $settingId = Setting::create($data);
            
            if ($settingId) {
                FlashMessage::setMessage("Setting created successfully!");
                return $response->redirect("/admin/settings");
            } else {
                throw new \Exception("Failed to create setting");
            }
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings/create");
        }
    }

    /**
     * Show form to edit a setting
     */
    public function edit(Request $request, Response $response, $id)
    {
        $setting = Setting::find($id);
        
        if (!$setting) {
            FlashMessage::setMessage("Setting not found!", 'danger');
            return $response->redirect("/admin/settings");
        }
        
        // Get all existing categories for the datalist
        $db = Database::getInstance();
        $builder = new QueryBuilder($db);
        $categories = $builder->table('settings')
            ->select('DISTINCT category')
            ->orderBy('category')
            ->get();
        
        $categoryList = array_map(function($item) {
            return $item->category;
        }, $categories);
        
        $view = [
            'setting' => $setting,
            'categories' => $categoryList
        ];
        
        return $this->render('admin/settings/edit', $view);
    }

    /**
     * Update a setting by ID
     */
    public function update(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings");
        }

        $setting = Setting::find($id);
        
        if (!$setting) {
            FlashMessage::setMessage("Setting not found!", 'danger');
            return $response->redirect("/admin/settings");
        }

        $rules = $this->settingModel->rules();
        
        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/settings/edit/{$id}");
        }

        try {
            $data = $request->all();
            
            // Process boolean value
            if ($data['type'] === 'boolean') {
                $data['value'] = isset($data['value']) && $data['value'] == '1' ? '1' : '0';
            }
            
            // Check if key already exists (excluding current setting)
            $existing = Setting::where(['key' => $data['key']]);
            if (!empty($existing)) {
                foreach ($existing as $existingSetting) {
                    if ($existingSetting->id != $id) {
                        FlashMessage::setMessage("Setting key already exists. Please choose a unique key.", 'danger');
                        set_form_data($request->all());
                        return $response->redirect("/admin/settings/edit/{$id}");
                    }
                }
            }
            
            // Update the setting
            $updated = $setting->updateInstance($data);
            
            if ($updated) {
                FlashMessage::setMessage("Setting updated successfully!");
                return $response->redirect("/admin/settings");
            } else {
                throw new \Exception("Failed to update setting");
            }
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/settings/edit/{$id}");
        }
    }

    /**
     * Delete a setting by ID
     */
    public function delete(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/settings");
        }

        $setting = Setting::find($id);
        
        if (!$setting) {
            FlashMessage::setMessage("Setting not found!", 'danger');
            return $response->redirect("/admin/settings");
        }

        try {
            if ($setting->delete()) {
                FlashMessage::setMessage("Setting deleted successfully!");
            } else {
                throw new \Exception("Failed to delete setting");
            }
        } catch (\Exception $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
        }
        
        return $response->redirect("/admin/settings");
    }

    /**
     * AJAX endpoint to update individual setting by ID
     */
    public function updateSetting(Request $request, Response $response)
    {
        if (!$request->isAjax() || "POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $id = $request->input('id');
        $value = $request->input('value');
        dd($id);
        
        if (!$id) {
            return $response->json(['success' => false, 'message' => 'Setting ID is required'], 400);
        }

        try {
            $setting = Setting::find($id);
            
            if (!$setting) {
                return $response->json(['success' => false, 'message' => 'Setting not found'], 404);
            }
            
            // Update the setting value
            $updated = $setting->updateInstance(['value' => $value]);
            
            if ($updated) {
                return $response->json([
                    'success' => true, 
                    'message' => 'Setting updated successfully',
                    'data' => [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $value,
                        'type' => $setting->type
                    ]
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update setting'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX endpoint to update individual setting by key (backward compatibility)
     */
    public function updateSettingByKey(Request $request, Response $response)
    {
        if (!$request->isAjax() || "POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $key = $request->input('key');
        $value = $request->input('value');
        
        if (!$key) {
            return $response->json(['success' => false, 'message' => 'Setting key is required'], 400);
        }

        try {
            $setting = Setting::where(['key' => $key]);
            
            if (empty($setting)) {
                return $response->json(['success' => false, 'message' => 'Setting not found'], 404);
            }
            
            $setting = $setting[0];
            
            // Update the setting value
            $updated = $setting->updateInstance(['value' => $value]);
            
            if ($updated) {
                return $response->json([
                    'success' => true, 
                    'message' => 'Setting updated successfully',
                    'data' => [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $value,
                        'type' => $setting->type
                    ]
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update setting'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
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
            // Implement your cache clearing logic here
            // This will depend on your caching implementation
            
            // Example: Clear file-based cache
            $cachePath = ROOT_PATH . '/storage/cache/';
            if (is_dir($cachePath)) {
                $this->clearDirectory($cachePath);
            }
            
            return $response->json(['success' => true, 'message' => 'Cache cleared successfully']);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Helper method to clear a directory
     */
    private function clearDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..', '.gitkeep']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->clearDirectory($path) : unlink($path);
        }
        
        return true;
    }

    public function __destruct()
    {
        $this->settingModel = null;
    }
}