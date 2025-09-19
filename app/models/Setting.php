<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;

class Setting extends Model
{
    /**
     * @var string The table name associated with the model
     */
    protected string $table = 'settings';

    /**
     * @var array Fillable attributes that can be mass-assigned
     */
    protected array $fillable = [
        'id',
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_editable'
    ];

    /**
     * @var array Hidden attributes that should be excluded from serialization
     */
    protected array $hidden = [];

    /**
     * @var array Validation rules for model attributes
     */
    protected array $rules = [
        'key' => 'required|min:2|max:100|unique:settings.`key`',
        'value' => 'nullable',
        'type' => 'required|in:string,integer,boolean,json,text,email,url',
        'category' => 'required|min:2|max:50',
        'description' => 'nullable|max:255',
        'is_editable' => 'boolean'
    ];

    /**
     * Find a setting by its key
     *
     * @param string $key The setting key
     * @return static|null The found setting or null
     */
    public static function findByKey(string $key): ?self
    {
        return static::first(['key' => $key]);
    }

    /**
     * Get settings by category
     *
     * @param string $category The category name
     * @return array Array of settings in the category
     */
    public static function getByCategory(string $category): array
    {
        return static::where(['category' => $category]);
    }

    /**
     * Get all editable settings
     *
     * @return array Array of editable settings
     */
    public static function getEditable(): array
    {
        return static::where(['is_editable' => 1]);
    }

    /**
     * Get all categories with their settings count
     *
     * @return array Array of categories with count
     */
    public static function getCategoriesWithCount(): array
    {
        $db = \Trees\Database\Database::getInstance();
        
        try {
            $query = "SELECT category, COUNT(*) as count 
                     FROM settings 
                     GROUP BY category 
                     ORDER BY category ASC";
            
            return $db->query($query) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set a setting value by key
     *
     * @param string $key The setting key
     * @param mixed $value The setting value
     * @param string $type The value type
     * @param string $category The setting category
     * @param string|null $description The setting description
     * @param bool $isEditable Whether the setting is editable
     * @return bool True if successful, false otherwise
     */
    public static function setSetting(
        string $key, 
        mixed $value, 
        string $type = 'string',
        string $category = 'general',
        ?string $description = null,
        bool $isEditable = true
    ): bool {
        // Convert value based on type
        $formattedValue = static::formatValueByType($value, $type);
        
        $existing = static::findByKey($key);
        
        if ($existing) {
            // Update existing setting
            return $existing->updateInstance([
                'value' => $formattedValue,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_editable' => $isEditable ? 1 : 0
            ]);
        } else {
            // Create new setting
            $result = static::create([
                'key' => $key,
                'value' => $formattedValue,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_editable' => $isEditable ? 1 : 0
            ]);
            
            return $result !== false;
        }
    }

    /**
     * Get a setting value by key
     *
     * @param string $key The setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed The setting value
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = static::findByKey($key);
        
        if (!$setting) {
            return $default;
        }
        
        return static::parseValueByType($setting->value, $setting->type);
    }

    /**
     * Format value according to its type for storage
     *
     * @param mixed $value The value to format
     * @param string $type The value type
     * @return string The formatted value for storage
     */
    private static function formatValueByType(mixed $value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string) intval($value);
            case 'json':
                return is_string($value) ? $value : json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Parse value according to its type for retrieval
     *
     * @param string $value The stored value
     * @param string $type The value type
     * @return mixed The parsed value
     */
    private static function parseValueByType(string $value, string $type): mixed
    {
        switch ($type) {
            case 'boolean':
                return $value === '1' || strtolower($value) === 'true';
            case 'integer':
                return intval($value);
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }

    /**
     * Get all settings grouped by category for display
     *
     * @return array Settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        $allSettings = static::all();
        $grouped = [];

        foreach ($allSettings as $setting) {
            $grouped[$setting->category][$setting->key] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => static::parseValueByType($setting->value, $setting->type),
                'raw_value' => $setting->value,
                'type' => $setting->type,
                'category' => $setting->category,
                'description' => $setting->description,
                'is_editable' => (bool) $setting->is_editable,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at
            ];
        }

        return $grouped;
    }

    /**
     * Seed default settings if they don't exist
     *
     * @return bool True if successful
     */
    public static function seedDefaults(): bool
    {
        $defaults = [
            // Application Settings
            'app_name' => ['Eventlyy', 'string', 'application', 'Application name'],
            'app_description' => ['Event Management System', 'text', 'application', 'Application description'],
            'app_url' => ['https://eventlyy.com', 'url', 'application', 'Application URL'],
            'app_timezone' => ['UTC', 'string', 'application', 'Application timezone'],
            'app_locale' => ['en', 'string', 'application', 'Application locale'],
            
            // Contact Settings
            'contact_email' => ['contact@eventlyy.com', 'email', 'contact', 'Primary contact email'],
            'contact_phone' => ['+1234567890', 'string', 'contact', 'Contact phone number'],
            'contact_address' => ['123 Main St, City, State', 'text', 'contact', 'Physical address'],
            
            // Email Settings
            'smtp_host' => ['smtp.gmail.com', 'string', 'email', 'SMTP server host'],
            'smtp_port' => ['587', 'integer', 'email', 'SMTP server port'],
            'smtp_username' => ['', 'email', 'email', 'SMTP username'],
            'smtp_password' => ['', 'string', 'email', 'SMTP password'],
            'mail_from_address' => ['noreply@eventlyy.com', 'email', 'email', 'From email address'],
            'mail_from_name' => ['Eventlyy', 'string', 'email', 'From name'],
            
            // Social Media
            'facebook_url' => ['', 'url', 'social', 'Facebook page URL'],
            'twitter_url' => ['', 'url', 'social', 'Twitter profile URL'],
            'instagram_url' => ['', 'url', 'social', 'Instagram profile URL'],
            'linkedin_url' => ['', 'url', 'social', 'LinkedIn profile URL'],
            
            // System Settings
            'maintenance_mode' => ['0', 'boolean', 'system', 'Enable maintenance mode'],
            'registration_enabled' => ['1', 'boolean', 'system', 'Allow user registration'],
            'debug_mode' => ['0', 'boolean', 'system', 'Enable debug mode'],
            
            // SEO Settings
            'meta_keywords' => ['events, management, booking', 'text', 'seo', 'Meta keywords'],
            'meta_description' => ['Professional event management platform', 'text', 'seo', 'Meta description'],
            
            // Security Settings
            'session_timeout' => ['1440', 'integer', 'security', 'Session timeout in minutes'],
            'max_login_attempts' => ['5', 'integer', 'security', 'Maximum login attempts'],
            
            // Cache Settings
            'cache_enabled' => ['1', 'boolean', 'cache', 'Enable application cache'],
            'cache_ttl' => ['3600', 'integer', 'cache', 'Cache TTL in seconds'],
        ];

        try {
            foreach ($defaults as $key => $data) {
                $existing = static::findByKey($key);
                if (!$existing) {
                    static::create([
                        'key' => $key,
                        'value' => $data[0],
                        'type' => $data[1],
                        'category' => $data[2],
                        'description' => $data[3],
                        'is_editable' => 1
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}