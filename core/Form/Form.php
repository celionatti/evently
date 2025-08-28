<?php

declare(strict_types=1);

namespace Trees\Form;

/**
 * =========================================
 * *****************************************
 * =========== Tress Form Class ============
 * *****************************************
 * Form builder class with basic form elements
 * and utilities.
 * =========================================
 */

class Form
{
    /**
     * Open a form tag.
     */
    public static function openForm(string $action, string $method = 'POST', ?string $enctype = null, array $attrs = []): string
    {
        $enctypeAttribute = $enctype ? ' enctype="' . htmlspecialchars($enctype) . '"' : '';
        $html = '<form action="' . htmlspecialchars($action) . '" method="' . htmlspecialchars($method) . '"' . $enctypeAttribute;
        $html .= self::processAttrs($attrs);
        $html .= '>';

        return $html;
    }

    /**
     * Close a form tag.
     */
    public static function closeForm(): string
    {
        return '</form>';
    }

    /**
     * Generate a CSRF token field.
     */
    public static function csrfField(): string
    {
        $csrf = new Csrf();
        $token = $csrf->generateToken() ?? '';

        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate a method spoofing field (for PUT, PATCH, DELETE).
     */
    public static function method(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . htmlspecialchars($method) . '">';
    }

    /**
     * Generate a hidden input field.
     */
    public static function hidden(string $name, string $value, array $attrs = []): string
    {
        $attrs['type'] = 'hidden';
        $attrs['value'] = $value;
        $attrs['id'] = $name;
        $attrs['name'] = $name;

        return '<input' . self::processAttrs($attrs) . '>';
    }

    /**
     * Generate a text input field.
     */
    public static function input(string $name, string $label, string $value = '', array $attrs = []): string
    {
        $attrs['type'] = 'text';
        $attrs['id'] = $name;
        $attrs['name'] = $name;
        $attrs['value'] = $value;

        $html = self::label($name, $label);
        $html .= '<input' . self::processAttrs($attrs) . '>';

        return $html;
    }

    /**
     * Generate a textarea field.
     */
    public static function textarea(string $name, string $label, string $value = '', array $attrs = []): string
    {
        $attrs['id'] = $name;
        $attrs['name'] = $name;

        $html = self::label($name, $label);
        $html .= '<textarea' . self::processAttrs($attrs) . '>' . htmlspecialchars($value) . '</textarea>';

        return $html;
    }

    /**
     * Generate a select dropdown field.
     */
    public static function select(string $name, string $label, array $options = [], string $selected = '', array $attrs = []): string
    {
        $attrs['id'] = $name;
        $attrs['name'] = $name;

        $html = self::label($name, $label);
        $html .= '<select' . self::processAttrs($attrs) . '>';

        foreach ($options as $value => $text) {
            $value = htmlspecialchars($value);
            $text = htmlspecialchars($text);
            $isSelected = ($value === $selected) ? ' selected' : '';
            $html .= "<option value=\"{$value}\"{$isSelected}>{$text}</option>";
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Generate a checkbox field.
     */
    public static function checkbox(string $name, string $label, bool $checked = false, array $attrs = []): string
    {
        $attrs['type'] = 'checkbox';
        $attrs['id'] = $name;
        $attrs['name'] = $name;
        $attrs['value'] = '1';

        if ($checked) {
            $attrs['checked'] = 'checked';
        }

        $html = '<input' . self::processAttrs($attrs) . '>';
        $html .= self::label($name, $label, ['class' => 'form-check-label']);

        return $html;
    }

    /**
     * Generate a radio button field.
     */
    public static function radio(string $name, string $label, string $value, bool $checked = false, array $attrs = []): string
    {
        $attrs['type'] = 'radio';
        $attrs['id'] = $name . '_' . $value;
        $attrs['name'] = $name;
        $attrs['value'] = $value;

        if ($checked) {
            $attrs['checked'] = 'checked';
        }

        $html = '<input' . self::processAttrs($attrs) . '>';
        $html .= self::label($attrs['id'], $label, ['class' => 'form-check-label']);

        return $html;
    }

    /**
     * Generate a submit button.
     */
    public static function submitBtn(string $text, array $attrs = []): string
    {
        $attrs['type'] = 'submit';
        return '<button' . self::processAttrs($attrs) . '>' . htmlspecialchars($text) . '</button>';
    }

    /**
     * Generate a label tag.
     */
    public static function label(string $for, string $text, array $attrs = []): string
    {
        return '<label for="' . htmlspecialchars($for) . '"' . self::processAttrs($attrs) . '>' . htmlspecialchars($text) . '</label>';
    }

    /**
     * Process form attributes into HTML string.
     */
    protected static function processAttrs(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $key => $value) {
            if ($value === true) {
                $html .= ' ' . htmlspecialchars($key);
            } elseif ($value !== false && $value !== null) {
                $html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars((string)$value) . '"';
            }
        }
        return $html;
    }

    /**
     * Append error classes to input attributes.
     */
    protected static function appendErrors(string $key, array $inputAttrs, array $errors): array
    {
        if (isset($errors[$key])) {
            $inputAttrs['class'] = isset($inputAttrs['class'])
                ? $inputAttrs['class'] . ' is-invalid'
                : 'is-invalid';
        }
        return $inputAttrs;
    }

    /**
     * Get the first error message for a field.
     */
    protected static function getOneError(string $id, array $errors): string
    {
        if (!isset($errors[$id])) {
            return '';
        }

        return is_array($errors[$id])
            ? ($errors[$id][0] ?? '')
            : $errors[$id];
    }
}