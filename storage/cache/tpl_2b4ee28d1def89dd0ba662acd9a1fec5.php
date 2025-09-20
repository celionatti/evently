<?php

declare(strict_types=1);

?>

<?php $this->start('styles'); ?>
<style>
    .dashboard-card {
        background: inherit;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<div class="content-section">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-1">Create New Setting</h1>
                <p class="text-secondary">Add a new system setting to configure your application.</p>
            </div>
            <a href="<?= url('/admin/settings/manage') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Settings
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <form action="" method="POST">
                    <div class="row g-3">
                        <!-- Setting Key -->
                        <div class="col-md-6">
                            <label for="key" class="form-label">Setting Key <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control <?= has_error('key') ? 'is-invalid' : '' ?>"
                                id="key"
                                name="key"
                                value="<?= get_form_data('key') ?>"
                                placeholder="e.g., app_name, smtp_host"
                                required>
                            <?php if (has_error('key')): ?>
                                <div class="invalid-feedback"><?= get_error('key') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-white">Unique identifier for this setting (use underscores for spaces)</small>
                        </div>

                        <!-- Setting Type -->
                        <div class="col-md-6">
                            <label for="type" class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('type') ? 'is-invalid' : '' ?>"
                                id="type"
                                name="type"
                                required
                                onchange="updateValueField()">
                                <option value="">Select type...</option>
                                <?php foreach ($types as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= get_form_data('type') === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (has_error('type')): ?>
                                <div class="invalid-feedback"><?= get_error('type') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Setting Category -->
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('category') ? 'is-invalid' : '' ?>"
                                id="category"
                                name="category"
                                required>
                                <option value="">Select category...</option>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= get_form_data('category') === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (has_error('category')): ?>
                                <div class="invalid-feedback"><?= get_error('category') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Is Editable -->
                        <div class="col-md-6">
                            <label class="form-label">Permissions</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="is_editable"
                                    name="is_editable"
                                    value="1"
                                    <?= get_form_data('is_editable') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_editable">
                                    Allow editing via admin panel
                                </label>
                            </div>
                            <small class="form-text text-white">If disabled, setting can only be changed by developers</small>
                        </div>

                        <!-- Setting Value -->
                        <div class="col-12">
                            <label for="value" class="form-label">Value</label>

                            <!-- String/Default Input -->
                            <input type="text"
                                class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-string"
                                name="value"
                                value="<?= get_form_data('value') ?>"
                                placeholder="Enter setting value">

                            <!-- Integer Input -->
                            <input type="number"
                                class="form-control value-input d-none <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-integer"
                                name="value"
                                value="<?= get_form_data('value') ?>"
                                placeholder="Enter numeric value">

                            <!-- Boolean Input -->
                            <div class="value-input d-none" id="value-boolean">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="value-bool-checkbox"
                                        name="value"
                                        value="1"
                                        <?= get_form_data('value') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="value-bool-checkbox">
                                        <span id="bool-label">Enabled</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Text Area -->
                            <textarea class="form-control value-input d-none <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-text"
                                name="value"
                                rows="4"
                                placeholder="Enter text content"><?= get_form_data('value') ?></textarea>

                            <!-- Email Input -->
                            <input type="email"
                                class="form-control value-input d-none <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-email"
                                name="value"
                                value="<?= get_form_data('value') ?>"
                                placeholder="Enter email address">

                            <!-- URL Input -->
                            <input type="url"
                                class="form-control value-input d-none <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-url"
                                name="value"
                                value="<?= get_form_data('value') ?>"
                                placeholder="Enter URL (https://example.com)">

                            <!-- JSON Input -->
                            <textarea class="form-control value-input d-none <?= has_error('value') ? 'is-invalid' : '' ?>"
                                id="value-json"
                                name="value"
                                rows="6"
                                placeholder="Enter valid JSON"><?= get_form_data('value') ?></textarea>

                            <?php if (has_error('value')): ?>
                                <div class="invalid-feedback"><?= get_error('value') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-white" id="value-help">Enter the default value for this setting</small>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>"
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Brief description of what this setting controls"><?= get_form_data('description') ?></textarea>
                            <?php if (has_error('description')): ?>
                                <div class="invalid-feedback"><?= get_error('description') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-white">Help text that will be shown to administrators</small>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-4">
                        <div class="d-flex justify-content-between">
                            <a href="<?= url('/admin/settings/manage') ?>" class="btn btn-secondary">
                                <i class="bi bi-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>Create Setting
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    function updateValueField() {
        const type = document.getElementById('type').value;
        const inputs = document.querySelectorAll('.value-input');
        const helpText = document.getElementById('value-help');

        // Hide all inputs
        inputs.forEach(input => {
            input.classList.add('d-none');
            input.removeAttribute('name');
        });

        // Show appropriate input and set name attribute
        let activeInput;
        let helpMessage = 'Enter the default value for this setting';

        switch (type) {
            case 'integer':
                activeInput = document.getElementById('value-integer');
                helpMessage = 'Enter a numeric value (whole numbers only)';
                break;
            case 'boolean':
                activeInput = document.getElementById('value-boolean');
                helpMessage = 'Toggle to set the default state';
                break;
            case 'text':
                activeInput = document.getElementById('value-text');
                helpMessage = 'Enter multi-line text content';
                break;
            case 'email':
                activeInput = document.getElementById('value-email');
                helpMessage = 'Enter a valid email address';
                break;
            case 'url':
                activeInput = document.getElementById('value-url');
                helpMessage = 'Enter a complete URL including https://';
                break;
            case 'json':
                activeInput = document.getElementById('value-json');
                helpMessage = 'Enter valid JSON format (e.g., {"key": "value"})';
                break;
            default:
                activeInput = document.getElementById('value-string');
                break;
        }

        if (activeInput) {
            activeInput.classList.remove('d-none');
            if (type === 'boolean') {
                activeInput.querySelector('input').setAttribute('name', 'value');
            } else {
                activeInput.setAttribute('name', 'value');
            }
        }

        helpText.textContent = helpMessage;
    }

    // Boolean toggle label update
    document.getElementById('value-bool-checkbox').addEventListener('change', function() {
        const label = document.getElementById('bool-label');
        label.textContent = this.checked ? 'Enabled' : 'Disabled';
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateValueField();
    });
</script>
<?php $this->end(); ?>