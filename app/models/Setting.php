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
        return [];
    }

    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(key LIKE :search OR description LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
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
                'value' => $setting->value,
                'raw_value' => $setting->value,
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
        return $results[0] ?? null;
    }

    /**
     * Get setting value by key with optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::findByKey($key);
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, mixed $value): bool
    {
        $setting = static::findByKey($key);
        
        if (!$setting) {
            return false;
        }

        return $setting->updateInstance(['value' => $value]);
    }

    /**
     * Update multiple settings at once
     */
    public static function updateMultiple(array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                $setting = static::findByKey($key);
                if ($setting && $setting->is_editable) {
                    $setting->updateInstance(['value' => $value]);
                }
            }
            return true;
        } catch (\Exception $e) {
            throw new TreesException("Failed to update settings: " . $e->getMessage());
        }
    }

    /**
     * Validate value based on type
     */
    public static function validateValue(mixed $value, string $type): bool
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_numeric($value) && (int)$value == $value;
            case 'float':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value) || in_array($value, ['0', '1', 0, 1, 'true', 'false', true, false], true);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'json':
                if (!is_string($value)) {
                    return false;
                }
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;
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
            'jwt_secret'
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
        if (static::exists($data['key'])) {
            throw new TreesException("Setting key '{$data['key']}' already exists");
        }

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
     * Get settings as key-value pairs
     */
    public static function getKeyValuePairs(): array
    {
        $settings = static::all();
        $pairs = [];

        foreach ($settings as $setting) {
            $pairs[$setting->key] = $setting->value;
        }

        return $pairs;
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
     * Convert value to appropriate type
     */
    public function getValueAttribute(): mixed
    {
        $value = $this->attributes['value'] ?? null;
        
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'array':
                return explode(',', $value);
            default:
                return $value;
        }
    }

    /**
     * Set value with proper formatting
     */
    public function setValueAttribute(mixed $value): void
    {
        switch ($this->type) {
            case 'boolean':
                $this->attributes['value'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                break;
            case 'json':
                $this->attributes['value'] = is_string($value) ? $value : json_encode($value);
                break;
            case 'array':
                $this->attributes['value'] = is_array($value) ? implode(',', $value) : $value;
                break;
            default:
                $this->attributes['value'] = (string)$value;
        }
    }
}