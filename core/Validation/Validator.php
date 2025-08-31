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
        'not_in' => ':field must not be one of :values.',
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
        'date' => ':field must be a valid date.',
        'before' => ':field must be before :date.',
        'after' => ':field must be after :date.',
        'accepted' => ':field must be accepted.',
        'alpha' => ':field must contain only letters.',
        'alpha_num' => ':field must contain only letters and numbers.',
        'alpha_dash' => ':field must contain only letters, numbers, dashes and underscores.',
        'between' => ':field must be between :min and :max.',
        'digits' => ':field must be :value digits.',
        'digits_between' => ':field must be between :min and :max digits.',
        'distinct' => ':field has duplicate values.',
        'filled' => ':field must not be empty when present.',
        'present' => ':field must be present.',
        'required_if' => ':field is required when :other is :value.',
        'required_unless' => ':field is required unless :other is in :values.',
        'required_with' => ':field is required when :values is present.',
        'required_with_all' => ':field is required when :values are present.',
        'required_without' => ':field is required when :values is not present.',
        'required_without_all' => ':field is required when none of :values are present.',
        'size' => ':field must be :size.',
        'password.uppercase' => ':field must contain at least one uppercase letter.',
        'password.lowercase' => ':field must contain at least one lowercase letter.',
        'password.number' => ':field must contain at least one number.',
        'password.special' => ':field must contain at least one special character.',
        'password.secure' => ':field must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'password.common' => ':field is too common and easily guessable.',
        'password.pwned' => ':field has been compromised in a data breach. Please choose a different password.',
        'password.history' => ':field has been used recently. Please choose a different password.',
    ];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
    }

    public function passes(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            if (!is_string($rules)) {
                throw new TreesException("Validation rules for field '{$field}' must be a string.");
            }

            $rules = explode('|', $rules);

            // Check if field is an array with indexes (e.g., tickets.0.name)
            $isIndexedArray = preg_match('/\.\d+\./', $field) || preg_match('/\.\d+$/', $field);
            $isWildcardArray = strpos($field, '.*.') !== false;

            if ($isWildcardArray) {
                $this->validateWildcardArrayField($field, $rules);
                continue;
            }

            if ($isIndexedArray) {
                $this->validateIndexedArrayField($field, $rules);
                continue;
            }

            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    protected function validateWildcardArrayField(string $field, array $rules): void
    {
        // Convert wildcard to regex pattern
        $pattern = str_replace('.*.', '\.\d+\.', $field);
        $pattern = str_replace('.*', '\.\d+', $pattern);
        $pattern = '/^' . $pattern . '$/';

        // Find all matching fields in data
        $matchingFields = [];
        foreach (array_keys($this->data) as $dataField) {
            if (preg_match($pattern, $dataField)) {
                $matchingFields[] = $dataField;
            }
        }

        // Apply rules to each matching field
        foreach ($matchingFields as $matchingField) {
            foreach ($rules as $rule) {
                $this->applyRule($matchingField, $rule);
            }
        }
    }

    protected function validateIndexedArrayField(string $field, array $rules): void
    {
        // Check if the field exists in data
        if (!array_key_exists($field, $this->data)) {
            // Field doesn't exist, check if it's required
            foreach ($rules as $rule) {
                if (strpos($rule, 'required') === 0) {
                    $this->addError($field, $this->getMessage('required', $field));
                    break;
                }
            }
            return;
        }

        foreach ($rules as $rule) {
            $this->applyRule($field, $rule);
        }
    }

    protected function applyRule(string $field, string $rule): void
    {
        $parameters = explode(':', $rule, 2);
        $ruleName = $parameters[0];
        $ruleValue = $parameters[1] ?? null;

        // Skip validation if field is nullable and empty
        if (
            $ruleName === 'nullable' &&
            (!isset($this->data[$field]) || $this->data[$field] === null || $this->data[$field] === '')
        ) {
            return;
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

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function has(string $field): bool
    {
        return isset($this->errors[$field]);
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
            if (is_array($value)) {
                if (isset($value['min']) && isset($value['max'])) {
                    $message = str_replace(':min', $value['min'], $message);
                    $message = str_replace(':max', $value['max'], $message);
                } else {
                    $message = str_replace(':values', implode(', ', $value), $message);
                }
            } else {
                $message = str_replace(':value', $value, $message);
            }
        }

        return $message;
    }

    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace(['_', '-', '.'], ' ', $field));
    }

    protected function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    protected function hasValue(string $field): bool
    {
        return isset($this->data[$field]);
    }

    protected function isEmptyValue($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return false;
    }

    protected function validateRequired(string $field): void
    {
        if (!$this->hasValue($field)) {
            $this->addError($field, $this->getMessage('required', $field));
            return;
        }

        $value = $this->getValue($field);

        if ($this->isEmptyValue($value)) {
            $this->addError($field, $this->getMessage('required', $field));
        }
    }

    protected function validateFilled(string $field): void
    {
        if ($this->hasValue($field) && $this->isEmptyValue($this->getValue($field))) {
            $this->addError($field, $this->getMessage('filled', $field));
        }
    }

    protected function validatePresent(string $field): void
    {
        if (!$this->hasValue($field)) {
            $this->addError($field, $this->getMessage('present', $field));
        }
    }

    protected function validateAccepted(string $field): void
    {
        if (!$this->hasValue($field)) {
            return;
        }

        $value = $this->getValue($field);
        $accepted = ['yes', 'on', '1', 1, true, 'true'];

        if (!in_array($value, $accepted, true)) {
            $this->addError($field, $this->getMessage('accepted', $field));
        }
    }

    protected function validateEmail(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!filter_var($this->getValue($field), FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $this->getMessage('email', $field));
        }
    }

    protected function validateString(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!is_string($this->getValue($field))) {
            $this->addError($field, $this->getMessage('string', $field));
        }
    }

    protected function validateAlpha(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!is_string($value) || !preg_match('/^[\pL\pM]+$/u', $value)) {
            $this->addError($field, $this->getMessage('alpha', $field));
        }
    }

    protected function validateAlphaNum(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!is_string($value) || !preg_match('/^[\pL\pM\pN]+$/u', $value)) {
            $this->addError($field, $this->getMessage('alpha_num', $field));
        }
    }

    protected function validateAlphaDash(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!is_string($value) || !preg_match('/^[\pL\pM\pN_-]+$/u', $value)) {
            $this->addError($field, $this->getMessage('alpha_dash', $field));
        }
    }

    protected function validateMin(string $field, string $value): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

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
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

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

    protected function validateBetween(string $field, string $values): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);
        $values = explode(',', $values);

        if (count($values) !== 2) {
            throw new TreesException("The 'between' rule requires exactly 2 values.");
        }

        $min = trim($values[0]);
        $max = trim($values[1]);

        if (is_string($value)) {
            $length = mb_strlen($value);
            if ($length < $min || $length > $max) {
                $this->addError($field, $this->getMessage('between', $field, ['min' => $min, 'max' => $max]));
            }
        } elseif (is_array($value)) {
            $count = count($value);
            if ($count < $min || $count > $max) {
                $this->addError($field, $this->getMessage('between', $field, ['min' => $min, 'max' => $max]));
            }
        } elseif (is_numeric($value)) {
            if ($value < $min || $value > $max) {
                $this->addError($field, $this->getMessage('between', $field, ['min' => $min, 'max' => $max]));
            }
        }
    }

    protected function validateSize(string $field, string $size): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (is_string($value)) {
            if (mb_strlen($value) != $size) {
                $this->addError($field, $this->getMessage('size', $field, $size));
            }
        } elseif (is_array($value)) {
            if (count($value) != $size) {
                $this->addError($field, $this->getMessage('size', $field, $size));
            }
        } elseif (is_numeric($value)) {
            if ($value != $size) {
                $this->addError($field, $this->getMessage('size', $field, $size));
            }
        }
    }

    protected function validateNumeric(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!is_numeric($this->getValue($field))) {
            $this->addError($field, $this->getMessage('numeric', $field));
        }
    }

    protected function validateDigits(string $field, string $value): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

        if (!ctype_digit((string) $val) || strlen((string) $val) != $value) {
            $this->addError($field, $this->getMessage('digits', $field, $value));
        }
    }

    protected function validateDigitsBetween(string $field, string $values): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);
        $values = explode(',', $values);

        if (count($values) !== 2) {
            throw new TreesException("The 'digits_between' rule requires exactly 2 values.");
        }

        $min = trim($values[0]);
        $max = trim($values[1]);

        if (!ctype_digit((string) $val)) {
            $this->addError($field, $this->getMessage('digits_between', $field, ['min' => $min, 'max' => $max]));
            return;
        }

        $length = strlen((string) $val);

        if ($length < $min || $length > $max) {
            $this->addError($field, $this->getMessage('digits_between', $field, ['min' => $min, 'max' => $max]));
        }
    }

    protected function validateInteger(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!filter_var($this->getValue($field), FILTER_VALIDATE_INT)) {
            $this->addError($field, $this->getMessage('integer', $field));
        }
    }

    protected function validateBoolean(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

        if (!filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null && $val !== false) {
            $this->addError($field, $this->getMessage('boolean', $field));
        }
    }

    protected function validateArray(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!is_array($this->getValue($field))) {
            $this->addError($field, $this->getMessage('array', $field));
        }
    }

    protected function validateDistinct(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!is_array($value)) {
            return;
        }

        if (count($value) !== count(array_unique($value))) {
            $this->addError($field, $this->getMessage('distinct', $field));
        }
    }

    protected function validateDatetime(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

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

    protected function validateDate(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $val = $this->getValue($field);

        if (!strtotime($val)) {
            $this->addError($field, $this->getMessage('date', $field));
            return;
        }

        try {
            $date = date_parse($val);
            if (!$date || $date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
                $this->addError($field, $this->getMessage('date', $field));
            }
        } catch (\Exception $e) {
            $this->addError($field, $this->getMessage('date', $field));
        }
    }

    protected function validateBefore(string $field, string $dateField): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $fieldValue = $this->getValue($field);
        $dateValue = $this->hasValue($dateField) ? $this->getValue($dateField) : $dateField;

        if (!strtotime($fieldValue) || !strtotime($dateValue)) {
            return; // Let other rules handle invalid dates
        }

        if (strtotime($fieldValue) >= strtotime($dateValue)) {
            $this->addError($field, $this->getMessage('before', $field, $dateField));
        }
    }

    protected function validateAfter(string $field, string $dateField): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $fieldValue = $this->getValue($field);
        $dateValue = $this->hasValue($dateField) ? $this->getValue($dateField) : $dateField;

        if (!strtotime($fieldValue) || !strtotime($dateValue)) {
            return; // Let other rules handle invalid dates
        }

        if (strtotime($fieldValue) <= strtotime($dateValue)) {
            $this->addError($field, $this->getMessage('after', $field, $dateField));
        }
    }

    protected function validateConfirmed(string $field): void
    {
        $confirmationField = "{$field}_confirmation";

        if (!$this->hasValue($confirmationField)) {
            $this->addError($field, $this->getMessage('confirmed', $field));
            return;
        }

        if ($this->getValue($field) !== $this->getValue($confirmationField)) {
            $this->addError($field, $this->getMessage('confirmed', $field));
        }
    }

    protected function validateUnique(string $field, ?string $tableColumnCondition = null): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
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
        $value = $this->getValue($field);

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
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $allowedValues = explode(',', $values);

        if (!in_array($this->getValue($field), $allowedValues)) {
            $this->addError($field, $this->getMessage('in', $field, $values));
        }
    }

    protected function validateNotIn(string $field, string $values): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $disallowedValues = explode(',', $values);

        if (in_array($this->getValue($field), $disallowedValues)) {
            $this->addError($field, $this->getMessage('not_in', $field, $values));
        }
    }

    protected function validateSame(string $field, string $otherField): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!$this->hasValue($otherField)) {
            $this->addError($field, str_replace(
                ':other',
                $this->formatFieldName($otherField),
                $this->getMessage('same', $field)
            ));
            return;
        }

        if ($this->getValue($field) !== $this->getValue($otherField)) {
            $this->addError($field, str_replace(
                ':other',
                $this->formatFieldName($otherField),
                $this->getMessage('same', $field)
            ));
        }
    }

    protected function validateDifferent(string $field, string $otherField): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!$this->hasValue($otherField)) {
            return;
        }

        if ($this->getValue($field) === $this->getValue($otherField)) {
            $this->addError($field, str_replace(
                ':other',
                $this->formatFieldName($otherField),
                $this->getMessage('different', $field)
            ));
        }
    }

    protected function validateRequiredIf(string $field, string $condition): void
    {
        $parts = explode(',', $condition);

        if (count($parts) < 2) {
            throw new TreesException("The 'required_if' rule requires at least 2 parameters.");
        }

        $otherField = trim($parts[0]);
        $requiredValue = trim($parts[1]);

        if (!$this->hasValue($otherField)) {
            return;
        }

        $otherValue = $this->getValue($otherField);

        if (
            $otherValue == $requiredValue &&
            (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))
        ) {
            $this->addError($field, str_replace(
                [':other', ':value'],
                [$this->formatFieldName($otherField), $requiredValue],
                $this->getMessage('required_if', $field)
            ));
        }
    }

    protected function validateRequiredUnless(string $field, string $condition): void
    {
        $parts = explode(',', $condition);

        if (count($parts) < 2) {
            throw new TreesException("The 'required_unless' rule requires at least 2 parameters.");
        }

        $otherField = trim($parts[0]);
        $excludedValues = array_slice($parts, 1);
        $excludedValues = array_map('trim', $excludedValues);

        if (!$this->hasValue($otherField)) {
            // Other field is not present, so field is required
            if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
                $this->addError($field, str_replace(
                    [':other', ':values'],
                    [$this->formatFieldName($otherField), implode(', ', $excludedValues)],
                    $this->getMessage('required_unless', $field)
                ));
            }
            return;
        }

        $otherValue = $this->getValue($otherField);

        if (
            !in_array($otherValue, $excludedValues) &&
            (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))
        ) {
            $this->addError($field, str_replace(
                [':other', ':values'],
                [$this->formatFieldName($otherField), implode(', ', $excludedValues)],
                $this->getMessage('required_unless', $field)
            ));
        }
    }

    protected function validateRequiredWith(string $field, string $otherFields): void
    {
        $otherFields = explode(',', $otherFields);
        $otherFields = array_map('trim', $otherFields);

        $hasOtherField = false;
        foreach ($otherFields as $otherField) {
            if ($this->hasValue($otherField) && !$this->isEmptyValue($this->getValue($otherField))) {
                $hasOtherField = true;
                break;
            }
        }

        if ($hasOtherField && (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))) {
            $this->addError($field, str_replace(
                ':values',
                implode(', ', $otherFields),
                $this->getMessage('required_with', $field)
            ));
        }
    }

    protected function validateRequiredWithAll(string $field, string $otherFields): void
    {
        $otherFields = explode(',', $otherFields);
        $otherFields = array_map('trim', $otherFields);

        $hasAllOtherFields = true;
        foreach ($otherFields as $otherField) {
            if (!$this->hasValue($otherField) || $this->isEmptyValue($this->getValue($otherField))) {
                $hasAllOtherFields = false;
                break;
            }
        }

        if ($hasAllOtherFields && (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))) {
            $this->addError($field, str_replace(
                ':values',
                implode(', ', $otherFields),
                $this->getMessage('required_with_all', $field)
            ));
        }
    }

    protected function validateRequiredWithout(string $field, string $otherFields): void
    {
        $otherFields = explode(',', $otherFields);
        $otherFields = array_map('trim', $otherFields);

        $missingOtherField = false;
        foreach ($otherFields as $otherField) {
            if (!$this->hasValue($otherField) || $this->isEmptyValue($this->getValue($otherField))) {
                $missingOtherField = true;
                break;
            }
        }

        if ($missingOtherField && (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))) {
            $this->addError($field, str_replace(
                ':values',
                implode(', ', $otherFields),
                $this->getMessage('required_without', $field)
            ));
        }
    }

    protected function validateRequiredWithoutAll(string $field, string $otherFields): void
    {
        $otherFields = explode(',', $otherFields);
        $otherFields = array_map('trim', $otherFields);

        $allOtherFieldsMissing = true;
        foreach ($otherFields as $otherField) {
            if ($this->hasValue($otherField) && !$this->isEmptyValue($this->getValue($otherField))) {
                $allOtherFieldsMissing = false;
                break;
            }
        }

        if ($allOtherFieldsMissing && (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field)))) {
            $this->addError($field, str_replace(
                ':values',
                implode(', ', $otherFields),
                $this->getMessage('required_without_all', $field)
            ));
        }
    }

    protected function validateRegex(string $field, string $pattern): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!preg_match($pattern, $this->getValue($field))) {
            $this->addError($field, $this->getMessage('regex', $field));
        }
    }

    protected function validateUrl(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!filter_var($this->getValue($field), FILTER_VALIDATE_URL)) {
            $this->addError($field, $this->getMessage('url', $field));
        }
    }

    protected function validateIp(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        if (!filter_var($this->getValue($field), FILTER_VALIDATE_IP)) {
            $this->addError($field, $this->getMessage('ip', $field));
        }
    }

    protected function validateFile(string $field): void
    {
        if (!$this->hasValue($field)) {
            return;
        }

        $file = $this->getValue($field);

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
        if (!$this->hasValue($field)) {
            return;
        }

        $file = $this->getValue($field);

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
        if (!$this->hasValue($field)) {
            return;
        }

        $file = $this->getValue($field);

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
        if (!$this->hasValue($field)) {
            return;
        }

        $file = $this->getValue($field);

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
        if (!$this->hasValue($field)) {
            return;
        }

        $file = $this->getValue($field);

        // First validate it's a file
        if (!is_array($file) || !isset($file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
            return; // Let the 'file' rule handle this case
        }

        $minSizeBytes = $minSizeKB * 1024;
        if ($file['size'] < $minSizeBytes) {
            $this->addError($field, str_replace(':value', $minSizeKB, $this->getMessage('min_size', $field)));
        }
    }

    /**
     * Validate password contains at least one uppercase letter
     */
    protected function validatePasswordUppercase(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!preg_match('/[A-Z]/', $value)) {
            $this->addError($field, $this->getMessage('password.uppercase', $field));
        }
    }

    /**
     * Validate password contains at least one lowercase letter
     */
    protected function validatePasswordLowercase(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!preg_match('/[a-z]/', $value)) {
            $this->addError($field, $this->getMessage('password.lowercase', $field));
        }
    }

    /**
     * Validate password contains at least one number
     */
    protected function validatePasswordNumber(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (!preg_match('/[0-9]/', $value)) {
            $this->addError($field, $this->getMessage('password.number', $field));
        }
    }

    /**
     * Validate password contains at least one special character
     */
    protected function validatePasswordSpecial(string $field, ?string $specialChars = null): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);
        $specialChars = $specialChars ?: '!@#$%^&*()\-_=+{};:,<.>';

        if (!preg_match('/[' . preg_quote($specialChars, '/') . ']/', $value)) {
            $this->addError($field, $this->getMessage('password.special', $field));
        }
    }

    /**
     * Validate password meets common security requirements
     * (min 8 chars, uppercase, lowercase, number, special char)
     */
    protected function validatePasswordSecure(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);

        if (strlen($value) < 8) {
            $this->addError($field, $this->getMessage('min', $field, '8'));
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->addError($field, $this->getMessage('password.uppercase', $field));
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->addError($field, $this->getMessage('password.lowercase', $field));
        }

        if (!preg_match('/[0-9]/', $value)) {
            $this->addError($field, $this->getMessage('password.number', $field));
        }

        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $value)) {
            $this->addError($field, $this->getMessage('password.special', $field));
        }
    }

    /**
     * Validate password is not a common password
     */
    protected function validatePasswordCommon(string $field): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);
        $commonPasswords = [
            'password',
            '123456',
            '12345678',
            '123456789',
            'qwerty',
            'abc123',
            'password1',
            '12345',
            '1234567',
            '1234567890',
            'admin',
            'welcome',
            'monkey',
            'letmein',
            'password123'
        ];

        if (in_array(strtolower($value), $commonPasswords)) {
            $this->addError($field, $this->getMessage('password.common', $field));
        }
    }

    /**
     * Validate password against Have I Been Pwned API (optional)
     * Note: This requires an internet connection and API call
     */
    protected function validatePasswordPwned(string $field, ?string $minBreaches = '1'): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);
        $minBreaches = (int)($minBreaches ?? '1');

        try {
            $sha1 = sha1($value);
            $prefix = substr($sha1, 0, 5);
            $suffix = substr($sha1, 5);

            $url = "https://api.pwnedpasswords.com/range/" . $prefix;
            $response = @file_get_contents($url);

            if ($response !== false) {
                $hashes = explode("\n", $response);
                foreach ($hashes as $hash) {
                    list($hashSuffix, $count) = explode(':', trim($hash));
                    if (strtoupper($hashSuffix) === strtoupper($suffix) && (int)$count >= $minBreaches) {
                        $this->addError($field, $this->getMessage('password.pwned', $field));
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if the API is unavailable
            // You might want to log this error in production
        }
    }

    /**
     * Validate password against user's password history
     * This requires integration with your user system
     */
    protected function validatePasswordHistory(string $field, string $parameters): void
    {
        if (!$this->hasValue($field) || $this->isEmptyValue($this->getValue($field))) {
            return;
        }

        $value = $this->getValue($field);
        $params = explode(',', $parameters);
        $userId = $params[0] ?? null;
        $historyCount = $params[1] ?? 5;

        if (!$userId) {
            throw new TreesException("The 'password.history' rule requires a user ID parameter.");
        }

        // This is a placeholder - you'll need to implement your own password history check
        // based on your application's user storage system

        // Example implementation (pseudo-code):
        /*
    $db = Database::getInstance();
    $previousPasswords = $db->query(
        "SELECT password FROM user_password_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
        [$userId, $historyCount]
    );
    
    foreach ($previousPasswords as $previous) {
        if (password_verify($value, $previous['password'])) {
            $this->addError($field, $this->getMessage('password.history', $field));
            break;
        }
    }
    */

        // For now, we'll just throw an exception since this requires custom implementation
        throw new TreesException("Password history validation requires custom implementation for your user system.");
    }

    protected function validateNullable(string $field): void
    {
        // Handled in the applyRule() method
    }
}
