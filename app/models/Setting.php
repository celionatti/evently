<?php

declare(strict_types=1);

namespace App\Models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Exception\TreesException;

class Setting extends Model implements ModelInterface
{
    protected string $table = 'settings';

    protected array $fillable = [
        'id',
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_editable'
    ];

    protected array $casts = [
        'is_editable' => 'boolean'
    ];

    protected array $hidden = [];

    public function rules()
    {
        return [
            'key' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|string|in:string,integer,float,boolean,email,url,json,text,array',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_editable' => 'boolean'
        ];
    }

    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(key LIKE :search OR description LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Add value accessor to handle type casting
     */
    public function getValueAttribute()
    {
        $value = $this->attributes['value'] ?? null;
        
        if ($value === null) {
            return null;
        }

        // Cast based on type
        switch ($this->type) {
            case 'boolean':
                return (bool)$value || $value === '1' || $value === 'true';
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'array':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return $decoded !== null ? $decoded : explode(',', $value);
                }
                return is_array($value) ? $value : [$value];
            default:
                return $value;
        }
    }

    /**
     * Set value attribute with proper type handling
     */
    public function setValueAttribute($value)
    {
        // Handle different types for storage
        switch ($this->type ?? 'string') {
            case 'boolean':
                $this->attributes['value'] = $value ? '1' : '0';
                break;
            case 'json':
            case 'array':
                $this->attributes['value'] = is_string($value) ? $value : json_encode($value);
                break;
            default:
                $this->attributes['value'] = (string)$value;
        }
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        $settings = static::all();
        $grouped = [];

        foreach ($settings as $setting) {
            $grouped[$setting->category][$setting->key] = [
                'value' => $setting->getValueAttribute(),
                'raw_value' => $setting->attributes['value'] ?? null,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_editable' => (bool)$setting->is_editable
            ];
        }

        return $grouped;
    }

    /**
     * Get all unique categories
     */
    public static function getCategories(): array
    {
        $settings = static::all();
        $categories = [];

        foreach ($settings as $setting) {
            if (!in_array($setting->category, $categories)) {
                $categories[] = $setting->category;
            }
        }

        return $categories;
    }

    /**
     * Find setting by key
     */
    public static function findByKey(string $key): ?self
    {
        $results = static::where(['key' => $key]);
        return $results ? $results[0] : null;
    }

    /**
     * Get setting value by key with optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::findByKey($key);
        return $setting ? $setting->getValueAttribute() : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, mixed $value): bool
    {
        $setting = static::findByKey($key);
        if (!$setting || !$setting->is_editable) {
            return false;
        }

        // Validate the value
        if (!static::validateValue($value, $setting->type)) {
            return false;
        }

        $setting->setValueAttribute($value);
        return $setting->updateInstance(['value' => $setting->attributes['value']]);
    }

    /**
     * Update multiple settings at once - FIXED VERSION
     */
    public static function updateMultiple(array $settings): bool
    {
        try {
            $updated = 0;
            $errors = [];

            foreach ($settings as $key => $value) {
                $setting = static::findByKey($key);
                
                if ($setting && $setting->is_editable) {
                    // Validate the value first
                    if (!static::validateValue($value, $setting->type)) {
                        $errors[] = "Invalid {$setting->type} value for setting '{$key}'";
                        continue;
                    }

                    // Set the value using the proper method
                    $setting->setValueAttribute($value);
                    
                    // Update the database record
                    if ($setting->updateInstance(['value' => $setting->attributes['value']])) {
                        $updated++;
                    } else {
                        $errors[] = "Failed to update setting '{$key}'";
                    }
                }
            }

            if (!empty($errors)) {
                throw new TreesException(implode(', ', $errors));
            }

            return $updated > 0;
        } catch (\Exception $e) {
            throw new TreesException("Failed to update settings: " . $e->getMessage());
        }
    }

    /**
     * Validate value based on type
     */
    public static function validateValue(mixed $value, string $type): bool
    {
        // Handle null values
        if ($value === null || $value === '') {
            return true; // Allow empty values for optional settings
        }

        switch (strtolower($type)) {
            case 'string':
            case 'text':
                return is_string($value) || is_numeric($value);

            case 'integer':
                return is_numeric($value) && (int)$value == $value;

            case 'float':
                return is_numeric($value);

            case 'boolean':
                return is_bool($value) || in_array($value, ['0', '1', 0, 1, 'true', 'false', true, false], true);

            case 'email':
                return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'url':
                return empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'json':
                if (!is_string($value)) {
                    return is_array($value) || is_object($value);
                }
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;

            case 'array':
                return is_array($value) || is_string($value);

            default:
                return true;
        }
    }

    /**
     * Check if setting exists
     */
    public static function existing(string $key): bool
    {
        return static::findByKey($key) !== null;
    }

    /**
     * Get list of sensitive keys that should be hidden
     */
    public static function getSensitiveKeys(): array
    {
        return [
            'smtp_password',
            'paystack_secret_key',
            'paystack_public_key',
            'database_password',
            'api_secret',
            'encryption_key',
            'jwt_secret',
            'google_maps_api_key',
            'stripe_secret_key',
            'aws_secret_access_key'
        ];
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): array
    {
        return static::where(['category' => $category]);
    }

    /**
     * Create a new setting
     */
    public static function createSetting(array $data): int|bool
    {
        // Ensure key is unique
        if (static::existing($data['key'])) {
            throw new TreesException("Setting key '{$data['key']}' already exists");
        }

        // Validate required fields
        if (empty($data['key']) || empty($data['type']) || empty($data['category'])) {
            throw new TreesException("Key, type, and category are required fields");
        }

        // Set defaults
        $data['is_editable'] = $data['is_editable'] ?? true;
        $data['description'] = $data['description'] ?? '';

        return static::create($data);
    }

    /**
     * Delete setting by key
     */
    public static function deleteByKey(string $key): bool
    {
        $setting = static::findByKey($key);
        if (!$setting) {
            return false;
        }

        return $setting->delete();
    }

    /**
     * Get editable settings only
     */
    public static function getEditable(): array
    {
        return static::where(['is_editable' => true]);
    }

    /**
     * Get non-editable settings
     */
    public static function getNonEditable(): array
    {
        return static::where(['is_editable' => false]);
    }

    /**
     * Create default application settings
     */
    public static function createDefaults(): bool
    {
        $defaults = [
            // Application settings
            ['key' => 'app_name', 'value' => 'Eventlyy', 'type' => 'string', 'category' => 'application', 'description' => 'Application name'],
            ['key' => 'app_url', 'value' => '', 'type' => 'url', 'category' => 'application', 'description' => 'Application URL'],
            ['key' => 'app_timezone', 'value' => 'UTC', 'type' => 'string', 'category' => 'application', 'description' => 'Application timezone'],
            ['key' => 'app_debug', 'value' => '0', 'type' => 'boolean', 'category' => 'application', 'description' => 'Debug mode'],

            // Email settings
            ['key' => 'smtp_host', 'value' => '', 'type' => 'string', 'category' => 'email', 'description' => 'SMTP host'],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'category' => 'email', 'description' => 'SMTP port'],
            ['key' => 'smtp_username', 'value' => '', 'type' => 'string', 'category' => 'email', 'description' => 'SMTP username'],
            ['key' => 'smtp_password', 'value' => '', 'type' => 'string', 'category' => 'email', 'description' => 'SMTP password', 'is_editable' => true],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'category' => 'email', 'description' => 'SMTP encryption (tls/ssl)'],
            ['key' => 'mail_from_name', 'value' => 'Eventlyy', 'type' => 'string', 'category' => 'email', 'description' => 'Mail from name'],
            ['key' => 'mail_from_address', 'value' => '', 'type' => 'email', 'category' => 'email', 'description' => 'Mail from address'],

            // Payment settings
            ['key' => 'paystack_public_key', 'value' => '', 'type' => 'string', 'category' => 'payment', 'description' => 'Paystack public key'],
            ['key' => 'paystack_secret_key', 'value' => '', 'type' => 'string', 'category' => 'payment', 'description' => 'Paystack secret key'],

            // System settings
            ['key' => 'cache_enabled', 'value' => '1', 'type' => 'boolean', 'category' => 'system', 'description' => 'Enable caching'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'category' => 'system', 'description' => 'Maintenance mode'],

            // SEO settings
            ['key' => 'meta_title', 'value' => 'Eventlyy', 'type' => 'string', 'category' => 'seo', 'description' => 'Default page title'],
            ['key' => 'meta_description', 'value' => '', 'type' => 'text', 'category' => 'seo', 'description' => 'Default meta description'],
            ['key' => 'meta_keywords', 'value' => '', 'type' => 'string', 'category' => 'seo', 'description' => 'Default meta keywords'],

            // Contact settings
            ['key' => 'contact_email', 'value' => '', 'type' => 'email', 'category' => 'contact', 'description' => 'Contact email address'],
            ['key' => 'contact_phone', 'value' => '', 'type' => 'string', 'category' => 'contact', 'description' => 'Contact phone number'],
            ['key' => 'contact_address', 'value' => '', 'type' => 'text', 'category' => 'contact', 'description' => 'Contact address'],

            // Social media settings
            ['key' => 'facebook_url', 'value' => '', 'type' => 'url', 'category' => 'social', 'description' => 'Facebook page URL'],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'url', 'category' => 'social', 'description' => 'Twitter profile URL'],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'url', 'category' => 'social', 'description' => 'Instagram profile URL'],
            ['key' => 'linkedin_url', 'value' => '', 'type' => 'url', 'category' => 'social', 'description' => 'LinkedIn profile URL']
        ];

        try {
            foreach ($defaults as $setting) {
                // Skip empty settings
                if (empty($setting['key'])) {
                    continue;
                }
                
                if (!static::existing($setting['key'])) {
                    static::createSetting($setting);
                }
            }
            return true;
        } catch (\Exception $e) {
            // Log error if logger is available
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }
            return false;
        }
    }

    /**
     * Bulk update settings from form data
     */
    public static function bulkUpdate(array $formData): bool
    {
        try {
            $updated = 0;
            $errors = [];

            foreach ($formData as $key => $value) {
                $setting = static::findByKey($key);
                
                if (!$setting) {
                    continue; // Skip non-existent settings
                }

                if (!$setting->is_editable) {
                    continue; // Skip non-editable settings
                }

                // Validate the value
                if (!static::validateValue($value, $setting->type)) {
                    $errors[] = "Invalid value for {$key}";
                    continue;
                }

                // Update the setting
                if (static::set($key, $value)) {
                    $updated++;
                }
            }

            if (!empty($errors)) {
                throw new TreesException(implode(', ', $errors));
            }

            return $updated > 0;
        } catch (\Exception $e) {
            throw new TreesException("Bulk update failed: " . $e->getMessage());
        }
    }

    /**
     * Get setting with type casting
     */
    public static function getTyped(string $key, mixed $default = null): mixed
    {
        $setting = static::findByKey($key);
        
        if (!$setting) {
            return $default;
        }

        return $setting->getValueAttribute();
    }

    /**
     * Reset setting to default value
     */
    public static function resetToDefault(string $key): bool
    {
        // This would require a defaults table or hardcoded defaults
        // For now, just return false
        return false;
    }

    /**
     * Get settings as key-value pairs for a category
     */
    public static function getCategoryAsArray(string $category): array
    {
        $settings = static::getByCategory($category);
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getValueAttribute();
        }

        return $result;
    }

    /**
     * Cache frequently accessed settings
     */
    private static ?array $cache = null;

    public static function getCached(string $key, mixed $default = null): mixed
    {
        if (static::$cache === null) {
            static::$cache = [];
            $all = static::all();
            
            foreach ($all as $setting) {
                static::$cache[$setting->key] = $setting->getValueAttribute();
            }
        }

        return static::$cache[$key] ?? $default;
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache(): void
    {
        static::$cache = null;
    }
}