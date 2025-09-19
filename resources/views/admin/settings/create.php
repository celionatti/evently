<?php

declare(strict_types=1);

?>

@section('styles')
<style>
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
    }

    .btn-group .btn {
        font-size: 0.875rem;
    }

    .required {
        color: #dc3545;
    }

    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .form-section {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
    }

    .section-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
</style>
@endsection

@section('content')
<div id="settings-create-section" class="content-section">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h2 mb-1">Create New Setting</h1>
                <p class="text-secondary">Add a new configuration setting to the system.</p>
            </div>
            <a href="<?= url('/admin/settings') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Settings
            </a>
        </div>
    </div>

    <div class="dashboard-card">
        <form action="<?= url('/admin/settings/create') ?>" method="POST" class="needs-validation" novalidate>
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h4>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="key" class="form-label">
                            Setting Key <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?= has_error('key') ? 'is-invalid' : '' ?>" 
                               id="key" 
                               name="key" 
                               value="<?= old('key') ?>" 
                               placeholder="e.g., app_name, smtp_host" 
                               required>
                        <div class="help-text">
                            Unique identifier for the setting. Use lowercase letters, numbers, and underscores only.
                        </div>
                        <?php if (has_error('key')): ?>
                            <div class="invalid-feedback"><?= get_error('key') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="type" class="form-label">
                            Data Type <span class="required">*</span>
                        </label>
                        <select class="form-select <?= has_error('type') ? 'is-invalid' : '' ?>" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Select data type</option>
                            <option value="string" <?= old('type') === 'string' ? 'selected' : '' ?>>String</option>
                            <option value="text" <?= old('type') === 'text' ? 'selected' : '' ?>>Text (Long)</option>
                            <option value="integer" <?= old('type') === 'integer' ? 'selected' : '' ?>>Integer</option>
                            <option value="boolean" <?= old('type') === 'boolean' ? 'selected' : '' ?>>Boolean</option>
                            <option value="email" <?= old('type') === 'email' ? 'selected' : '' ?>>Email</option>
                            <option value="url" <?= old('type') === 'url' ? 'selected' : '' ?>>URL</option>
                            <option value="json" <?= old('type') === 'json' ? 'selected' : '' ?>>JSON</option>
                        </select>
                        <div class="help-text">
                            Choose the appropriate data type for validation and display.
                        </div>
                        <?php if (has_error('type')): ?>
                            <div class="invalid-feedback"><?= get_error('type') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Categorization Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="bi bi-folder me-2"></i>Categorization
                </h4>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">
                            Category <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?= has_error('category') ? 'is-invalid' : '' ?>" 
                               id="category" 
                               name="category" 
                               value="<?= old('category') ?>" 
                               placeholder="e.g., application, email, system" 
                               list="existing-categories"
                               required>
                        
                        <datalist id="existing-categories">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        
                        <div class="help-text">
                            Group related settings together. You can use an existing category or create a new one.
                        </div>
                        <?php if (has_error('category')): ?>
                            <div class="invalid-feedback"><?= get_error('category') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Brief description of what this setting controls..."><?= old('description') ?></textarea>
                        <div class="help-text">
                            Optional description to help administrators understand the setting's purpose.
                        </div>
                        <?php if (has_error('description')): ?>
                            <div class="invalid-feedback"><?= get_error('description') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Value & Permissions Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="bi bi-sliders me-2"></i>Value & Permissions
                </h4>
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="value" class="form-label">Default Value</label>
                        <div id="value-input-container">
                            <!-- String/Text Input -->
                            <input type="text" 
                                   class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>" 
                                   id="value-string" 
                                   name="value" 
                                   value="<?= old('value') ?>" 
                                   placeholder="Enter default value">
                            
                            <!-- Boolean Input (hidden by default) -->
                            <div class="form-check form-switch value-input" id="value-boolean" style="display: none;">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="value_boolean" 
                                       value="1" 
                                       <?= old('value') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    <span class="boolean-label">Enabled</span>
                                </label>
                            </div>
                            
                            <!-- Integer Input (hidden by default) -->
                            <input type="number" 
                                   class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>" 
                                   id="value-integer" 
                                   name="value_integer" 
                                   value="<?= old('value') ?>" 
                                   placeholder="Enter integer value"
                                   style="display: none;">
                            
                            <!-- Email Input (hidden by default) -->
                            <input type="email" 
                                   class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>" 
                                   id="value-email" 
                                   name="value_email" 
                                   value="<?= old('value') ?>" 
                                   placeholder="Enter email address"
                                   style="display: none;">
                            
                            <!-- URL Input (hidden by default) -->
                            <input type="url" 
                                   class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>" 
                                   id="value-url" 
                                   name="value_url" 
                                   value="<?= old('value') ?>" 
                                   placeholder="https://example.com"
                                   style="display: none;">
                            
                            <!-- Textarea for text and JSON (hidden by default) -->
                            <textarea class="form-control value-input <?= has_error('value') ? 'is-invalid' : '' ?>" 
                                      id="value-textarea" 
                                      name="value_textarea" 
                                      rows="4" 
                                      placeholder="Enter value..."
                                      style="display: none;"><?= old('value') ?></textarea>
                        </div>
                        
                        <div class="help-text">
                            Set the initial value for this setting. Leave empty if no default is needed.
                        </div>
                        <?php if (has_error('value')): ?>
                            <div class="invalid-feedback"><?= get_error('value') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Permissions</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_editable" 
                                   name="is_editable" 
                                   value="1" 
                                   <?= old('is_editable', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_editable">
                                <span class="editable-label">Editable</span>
                            </label>
                        </div>
                        <div class="help-text">
                            Allow administrators to modify this setting through the interface.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="<?= url('/admin/settings') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </a>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-pulse">
                        <i class="bi bi-check-circle me-2"></i>Create Setting
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const valueInputs = document.querySelectorAll('.value-input');
        const booleanLabel = document.querySelector('.boolean-label');
        const editableLabel = document.querySelector('.editable-label');
        
        // Handle type change
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide all value inputs
            valueInputs.forEach(input => {
                input.style.display = 'none';
                input.removeAttribute('name'); // Remove name attribute to prevent submission
            });
            
            // Show appropriate input based on type
            switch(selectedType) {
                case 'boolean':
                    document.getElementById('value-boolean').style.display = 'block';
                    document.querySelector('input[name="value_boolean"]').setAttribute('name', 'value');
                    break;
                    
                case 'integer':
                    document.getElementById('value-integer').style.display = 'block';
                    document.getElementById('value-integer').setAttribute('name', 'value');
                    break;
                    
                case 'email':
                    document.getElementById('value-email').style.display = 'block';
                    document.getElementById('value-email').setAttribute('name', 'value');
                    break;
                    
                case 'url':
                    document.getElementById('value-url').style.display = 'block';
                    document.getElementById('value-url').setAttribute('name', 'value');
                    break;
                    
                case 'text':
                case 'json':
                    document.getElementById('value-textarea').style.display = 'block';
                    document.getElementById('value-textarea').setAttribute('name', 'value');
                    if (selectedType === 'json') {
                        document.getElementById('value-textarea').placeholder = '{"key": "value"}';
                    } else {
                        document.getElementById('value-textarea').placeholder = 'Enter text value...';
                    }
                    break;
                    
                default: // string
                    document.getElementById('value-string').style.display = 'block';
                    document.getElementById('value-string').setAttribute('name', 'value');
                    break;
            }
        });
        
        // Handle boolean switch label updates
        const booleanSwitch = document.querySelector('input[name="value_boolean"]');
        if (booleanSwitch) {
            booleanSwitch.addEventListener('change', function() {
                booleanLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
            });
        }
        
        // Handle editable switch label updates
        const editableSwitch = document.getElementById('is_editable');
        editableSwitch.addEventListener('change', function() {
            editableLabel.textContent = this.checked ? 'Editable' : 'Read Only';
        });
        
        // Initialize display based on current type selection
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
        
        // Form validation
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
        
        // Key input formatting (auto-format to lowercase with underscores)
        const keyInput = document.getElementById('key');
        keyInput.addEventListener('input', function() {
            let value = this.value.toLowerCase();
            value = value.replace(/[^a-z0-9_]/g, ''); // Remove invalid characters
            value = value.replace(/_{2,}/g, '_'); // Replace multiple underscores with single
            this.value = value;
        });
        
        // Category input formatting
        const categoryInput = document.getElementById('category');
        categoryInput.addEventListener('input', function() {
            let value = this.value.toLowerCase();
            value = value.replace(/[^a-z0-9_\s]/g, ''); // Remove invalid characters
            value = value.replace(/\s+/g, '_'); // Replace spaces with underscores
            value = value.replace(/_{2,}/g, '_'); // Replace multiple underscores with single
            this.value = value;
        });
    });
</script>
@endsection