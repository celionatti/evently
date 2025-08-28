<?php

declare(strict_types=1);

namespace Trees\Validation;

use Trees\Database\Database;
use DateTime;
use DateTimeInterface;
use Trees\Exception\TreesException;

/**
 * =======================================
 * ***************************************
 * ========== Trees Validator Class ======
 * ***************************************
 * =======================================
 */

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];
    protected array $customMessages = [];
    protected static array $customValidators = [];
    protected static array $defaultMessages = [
        'required' => ':field is required.',
        'email' => ':field must be a valid email address.',
        'string' => ':field must be a string.',
        'min' => ':field must be at least :value characters.',
        'max' => ':field must not exceed :value characters.',
        'numeric' => ':field must be a number.',
        'integer' => ':field must be an integer.',
        'boolean' => ':field must be true or false.',
        'array' => ':field must be an array.',
        'datetime' => ':field must be a valid datetime.',
        'confirmed' => ':field confirmation does not match.',
        'unique' => ':field must be unique.',
        'in' => ':field must be one of :values.',
        'same' => ':field must match :other.',
        'different' => ':field must be different from :other.',
        'regex' => ':field format is invalid.',
        'url' => ':field must be a valid URL.',
        'ip' => ':field must be a valid IP address.',
        'file' => ':field must be a file.',
        'image' => ':field must be an image.',
        'mimes' => ':field must be a file of type: :values.',
        'max_size' => ':field must not be larger than :value kilobytes.',
        'min_size' => ':field must be at least :value kilobytes.',
    ];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
    }

    public function passes(): bool
    {
        foreach ($this->rules as $field => $rules) {
            if (!is_string($rules)) {
                throw new TreesException("Validation rules for field '{$field}' must be a string.");
            }

            $rules = explode('|', $rules);

            foreach ($rules as $rule) {
                $parameters = explode(':', $rule, 2);
                $ruleName = $parameters[0];
                $ruleValue = $parameters[1] ?? null;

                // Skip validation if field is nullable and empty
                if ($ruleName === 'nullable' &&
                    (!isset($this->data[$field]) || $this->data[$field] === null || $this->data[$field] === '')) {
                    continue;
                }

                $method = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $method)) {
                    $this->$method($field, $ruleValue);
                } elseif (isset(self::$customValidators[$ruleName])) {
                    call_user_func(self::$customValidators[$ruleName], $this, $field, $ruleValue);
                } else {
                    throw new TreesException("Validation rule '{$ruleName}' does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public static function addCustomValidator(string $name, callable $callback): void
    {
        self::$customValidators[$name] = $callback;
    }

    protected function getMessage(string $rule, string $field, $value = null): string
    {
        $customKey = "{$field}.{$rule}";

        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$rule])) {
            $message = $this->customMessages[$rule];
        } else {
            $message = self::$defaultMessages[$rule] ?? ":field validation failed for rule {$rule}.";
        }

        $message = str_replace(':field', $this->formatFieldName($field), $message);

        if ($value !== null) {
            $message = str_replace(':value', $value, $message);
        }

        return $message;
    }

    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $field));
    }

    protected function validateRequired(string $field): void
    {
        if (!isset($this->data[$field])) {
            $this->addError($field, $this->getMessage('required', $field));
            return;
        }

        $value = $this->data[$field];

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, $this->getMessage('required', $field));
        }
    }

    protected function validateEmail(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $this->getMessage('email', $field));
        }
    }

    protected function validateString(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!is_string($this->data[$field])) {
            $this->addError($field, $this->getMessage('string', $field));
        }
    }

    protected function validateMin(string $field, string $value): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $val = $this->data[$field];

        if (is_string($val)) {
            if (mb_strlen($val) < $value) {
                $this->addError($field, $this->getMessage('min', $field, $value));
            }
        } elseif (is_array($val)) {
            if (count($val) < $value) {
                $this->addError($field, $this->getMessage('min', $field, $value));
            }
        } elseif (is_numeric($val)) {
            if ($val < $value) {
                $this->addError($field, $this->getMessage('min', $field, $value));
            }
        }
    }

    protected function validateMax(string $field, string $value): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $val = $this->data[$field];

        if (is_string($val)) {
            if (mb_strlen($val) > $value) {
                $this->addError($field, $this->getMessage('max', $field, $value));
            }
        } elseif (is_array($val)) {
            if (count($val) > $value) {
                $this->addError($field, $this->getMessage('max', $field, $value));
            }
        } elseif (is_numeric($val)) {
            if ($val > $value) {
                $this->addError($field, $this->getMessage('max', $field, $value));
            }
        }
    }

    protected function validateNumeric(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!is_numeric($this->data[$field])) {
            $this->addError($field, $this->getMessage('numeric', $field));
        }
    }

    protected function validateInteger(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->addError($field, $this->getMessage('integer', $field));
        }
    }

    protected function validateBoolean(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $val = $this->data[$field];

        if (!filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null && $val !== false) {
            $this->addError($field, $this->getMessage('boolean', $field));
        }
    }

    protected function validateArray(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!is_array($this->data[$field])) {
            $this->addError($field, $this->getMessage('array', $field));
        }
    }

    protected function validateDatetime(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $val = $this->data[$field];

        if ($val instanceof DateTimeInterface) {
            return;
        }

        if (!strtotime($val)) {
            $this->addError($field, $this->getMessage('datetime', $field));
            return;
        }

        try {
            new DateTime($val);
        } catch (\Exception $e) {
            $this->addError($field, $this->getMessage('datetime', $field));
        }
    }

    protected function validateConfirmed(string $field): void
    {
        $confirmationField = "{$field}_confirmation";

        if (!isset($this->data[$confirmationField])) {
            $this->addError($field, $this->getMessage('confirmed', $field));
            return;
        }

        if ($this->data[$field] !== $this->data[$confirmationField]) {
            $this->addError($field, $this->getMessage('confirmed', $field));
        }
    }

    protected function validateUnique(string $field, ?string $tableColumnCondition = null): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        // Check if the required parameter is provided
        if ($tableColumnCondition === null || empty($tableColumnCondition)) {
            throw new TreesException("The 'unique' validation rule for field '{$field}' requires a table.column parameter.");
        }

        $parts = explode(',', $tableColumnCondition);
        $tableColumn = array_shift($parts);

        // Validate table.column format
        if (substr_count($tableColumn, '.') !== 1) {
            throw new TreesException("Invalid format for unique validation. Expected 'table.column'.");
        }

        list($table, $column) = explode('.', $tableColumn);
        $value = $this->data[$field];

        // Use your database implementation instead of direct PDO
        $db = Database::getInstance();
        if (!$db) {
            throw new TreesException("Database connection not established");
        }

        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        // Handle additional conditions if provided
        if (!empty($parts)) {
            $additionalCondition = trim($parts[0]);

            if (preg_match('/(\w+)\s*(=|!=|<|>|<=|>=)\s*(.+)/', $additionalCondition, $matches)) {
                $conditionField = $matches[1];
                $conditionOperator = $matches[2];
                $conditionValue = $matches[3];

                $query .= " AND {$conditionField} {$conditionOperator} ?";
                $params[] = is_numeric($conditionValue) ? $conditionValue : trim($conditionValue, "'");
            } else {
                throw new TreesException("Invalid condition format in unique validation.");
            }
        }

        try {
            // Use your database's query method
            $result = $db->query($query, $params);

            if ($result === false) {
                throw new TreesException("Database query failed: " . $db->getLastError());
            }

            // Check if any records were found
            if (!empty($result) && $result[0]['count'] > 0) {
                $this->addError($field, $this->getMessage('unique', $field));
            }
        } catch (\Exception $e) {
            throw new TreesException("Database error during unique validation: " . $e->getMessage());
        }
    }

    protected function validateIn(string $field, string $values): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $allowedValues = explode(',', $values);

        if (!in_array($this->data[$field], $allowedValues)) {
            $this->addError($field, $this->getMessage('in', $field, $values));
        }
    }

    protected function validateSame(string $field, string $otherField): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!isset($this->data[$otherField])) {
            $this->addError($field, str_replace(':other', $this->formatFieldName($otherField),
                $this->getMessage('same', $field)));
            return;
        }

        if ($this->data[$field] !== $this->data[$otherField]) {
            $this->addError($field, str_replace(':other', $this->formatFieldName($otherField),
                $this->getMessage('same', $field)));
        }
    }

    protected function validateDifferent(string $field, string $otherField): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!isset($this->data[$otherField])) {
            return;
        }

        if ($this->data[$field] === $this->data[$otherField]) {
            $this->addError($field, str_replace(':other', $this->formatFieldName($otherField),
                $this->getMessage('different', $field)));
        }
    }

    protected function validateRegex(string $field, string $pattern): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!preg_match($pattern, $this->data[$field])) {
            $this->addError($field, $this->getMessage('regex', $field));
        }
    }

    protected function validateUrl(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->addError($field, $this->getMessage('url', $field));
        }
    }

    protected function validateIp(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        if (!filter_var($this->data[$field], FILTER_VALIDATE_IP)) {
            $this->addError($field, $this->getMessage('ip', $field));
        }
    }

    protected function validateFile(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $file = $this->data[$field];

        // Check if it's a valid uploaded file
        if (!is_array($file) || !isset($file['tmp_name']) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, $this->getMessage('file', $field));
            return;
        }

        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->addError($field, $this->getMessage('file', $field));
        }
    }

    protected function validateImage(string $field): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $file = $this->data[$field];

        // First validate it's a file
        if (!is_array($file) || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return; // Let the 'file' rule handle this case
        }

        // Check if the file is an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->addError($field, $this->getMessage('image', $field));
        }
    }

    protected function validateMimes(string $field, string $allowedTypes): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $file = $this->data[$field];

        // First validate it's a file
        if (!is_array($file) || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return; // Let the 'file' rule handle this case
        }

        $allowedMimes = explode(',', $allowedTypes);
        $fileMime = mime_content_type($file['tmp_name']);

        if (!in_array($fileMime, $allowedMimes)) {
            $this->addError($field, str_replace(':values', $allowedTypes, $this->getMessage('mimes', $field)));
        }
    }

    protected function validateMaxSize(string $field, string $maxSizeKB): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $file = $this->data[$field];

        // First validate it's a file
        if (!is_array($file) || !isset($file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
            return; // Let the 'file' rule handle this case
        }

        $maxSizeBytes = $maxSizeKB * 1024;
        if ($file['size'] > $maxSizeBytes) {
            $this->addError($field, str_replace(':value', $maxSizeKB, $this->getMessage('max_size', $field)));
        }
    }

    protected function validateMinSize(string $field, string $minSizeKB): void
    {
        if (!isset($this->data[$field])) {
            return;
        }

        $file = $this->data[$field];

        // First validate it's a file
        if (!is_array($file) || !isset($file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
            return; // Let the 'file' rule handle this case
        }

        $minSizeBytes = $minSizeKB * 1024;
        if ($file['size'] < $minSizeBytes) {
            $this->addError($field, str_replace(':value', $minSizeKB, $this->getMessage('min_size', $field)));
        }
    }

    protected function validateNullable(string $field): void
    {
        // Handled in the passes() method
    }
}