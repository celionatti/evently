<?php

declare(strict_types=1);

namespace Trees\Form;

use Trees\Form\Form;

/**
 * =========================================
 * *****************************************
 * ======== Tress BootstrapForm Class ======
 * *****************************************
 * Bootstrap-styled form builder extending
 * the base Form class.
 * =========================================
 */

class BootstrapForm extends Form
{
    /**
     * Generate a Bootstrap-styled submit button.
     */
    public static function submitButton(string $label = 'Submit', string $class = 'btn-primary', array $attrs = []): string
    {
        $attrs['class'] = 'btn ' . $class . ' ' . ($attrs['class'] ?? '');
        return parent::submitBtn($label, $attrs);
    }

    /**
     * Generate a Bootstrap-styled input field.
     */
    public static function inputField(string $label, string $id, string $value, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'mb-3 ' . ($wrapperAttrs['class'] ?? '');
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputAttrs['placeholder'] = $inputAttrs['placeholder'] ?? $label;

        $errorMsg = self::getOneError($id, $errors);
        $errorHtml = $errorMsg ? '<div class="invalid-feedback">' . htmlspecialchars($errorMsg) . '</div>' : '';

        $html = '<div' . self::processAttrs($wrapperAttrs) . '>';
        $html .= self::label($id, $label, ['class' => 'form-label']);
        $html .= '<input id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($id) . '" value="' . htmlspecialchars($value) . '"' . self::processAttrs($inputAttrs) . '>';
        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a Bootstrap-styled select field.
     */
    public static function selectField(string $label, string $id, ?string $value, array $options, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'mb-3 ' . ($wrapperAttrs['class'] ?? '');
        $inputAttrs['class'] = 'form-select ' . ($inputAttrs['class'] ?? '');

        $errorMsg = self::getOneError($id, $errors);
        $errorHtml = $errorMsg ? '<div class="invalid-feedback">' . htmlspecialchars($errorMsg) . '</div>' : '';

        $html = '<div' . self::processAttrs($wrapperAttrs) . '>';
        $html .= self::label($id, $label, ['class' => 'form-label']);
        $html .= '<select id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($id) . '"' . self::processAttrs($inputAttrs) . '>';
        $html .= '<option value="">Please select...</option>';

        foreach ($options as $val => $display) {
            $val = htmlspecialchars($val);
            $display = htmlspecialchars($display);
            $selected = ($val === $value) ? ' selected' : '';
            $html .= "<option value=\"{$val}\"{$selected}>{$display}</option>";
        }

        $html .= '</select>';
        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a Bootstrap-styled checkbox field.
     */
    public static function checkField(string $label, string $id, bool $checked = false, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'mb-3 form-check ' . ($wrapperAttrs['class'] ?? '');
        $inputAttrs['class'] = 'form-check-input ' . ($inputAttrs['class'] ?? '');

        $errorMsg = self::getOneError($id, $errors);
        $errorHtml = $errorMsg ? '<div class="invalid-feedback">' . htmlspecialchars($errorMsg) . '</div>' : '';

        $html = '<div' . self::processAttrs($wrapperAttrs) . '>';
        $html .= '<input type="checkbox" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($id) . '"' . ($checked ? ' checked' : '') . self::processAttrs($inputAttrs) . '>';
        $html .= self::label($id, $label, ['class' => 'form-check-label']);
        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a Bootstrap-styled textarea field.
     */
    public static function textareaField(string $label, string $id, string $value, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'mb-3 ' . ($wrapperAttrs['class'] ?? '');
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputAttrs['placeholder'] = $inputAttrs['placeholder'] ?? $label;

        $errorMsg = self::getOneError($id, $errors);
        $errorHtml = $errorMsg ? '<div class="invalid-feedback">' . htmlspecialchars($errorMsg) . '</div>' : '';

        $html = '<div' . self::processAttrs($wrapperAttrs) . '>';
        $html .= self::label($id, $label, ['class' => 'form-label']);
        $html .= '<textarea id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($id) . '"' . self::processAttrs($inputAttrs) . '>' . htmlspecialchars($value) . '</textarea>';
        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a Bootstrap-styled file input field.
     */
    public static function fileField(string $label, string $id, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'mb-3 ' . ($wrapperAttrs['class'] ?? '');
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');

        $errorMsg = self::getOneError($id, $errors);
        $errorHtml = $errorMsg ? '<div class="invalid-feedback">' . htmlspecialchars($errorMsg) . '</div>' : '';

        $html = '<div' . self::processAttrs($wrapperAttrs) . '>';
        $html .= self::label($id, $label, ['class' => 'form-label']);
        $html .= '<input type="file" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($id) . '"' . self::processAttrs($inputAttrs) . '>';
        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a Bootstrap-styled multiple file input field.
     */
    public static function fileFieldMultiple(string $label, string $id, array $inputAttrs = [], array $wrapperAttrs = [], array $errors = []): string
    {
        $inputAttrs['multiple'] = true;
        return self::fileField($label, $id, $inputAttrs, $wrapperAttrs, $errors);
    }
}