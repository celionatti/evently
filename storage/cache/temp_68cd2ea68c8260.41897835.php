<!-- admin/settings/manage.php -->
<?php

declare(strict_types=1);

?>

<?php $this->start('styles'); ?>
<style>
    .nav-pills .nav-link {
        margin-bottom: 0.5rem;
        margin-right: 0.5rem;
        border: 1px solid #dee2e6;
        color: #6c757d;
    }

    .nav-pills .nav-link.active {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }

    .form-switch .form-check-input:checked {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }

    .dashboard-card .border-top {
        border-top: 1px solid #dee2e6 !important;
    }

    .btn-group .btn {
        font-size: 0.875rem;
    }

    .setting-item {
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-radius: 0.375rem;
        border: 1px solid #e9ecef;
    }

    .setting-item:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .setting-save-btn {
        display: none;
        margin-top: 0.5rem;
    }

    .setting-item.changed .setting-save-btn {
        display: inline-block;
    }

    @media (max-width: 768px) {
        .nav-pills {
            flex-direction: column;
        }

        .nav-pills .nav-link {
            margin-right: 0;
            margin-bottom: 0.25rem;
        }
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<div id="settings-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">System Settings</h1>
        <p class="text-secondary">Configure application settings and system preferences.</p>
    </div>

    <!-- Settings Navigation -->
    <div class="dashboard-card mb-4">
        <ul class="nav nav-pills flex-wrap" id="settingsTabs" role="tablist">
            <?php
            $tabLabels = [
                'application' => ['Application', 'bi-app'],
                'contact' => ['Contact', 'bi-telephone'],
                'social' => ['Social Media', 'bi-share'],
                'email' => ['Email', 'bi-envelope'],
                'payment' => ['Payment', 'bi-credit-card'],
                'system' => ['System', 'bi-gear'],
                'seo' => ['SEO', 'bi-search'],
                'security' => ['Security', 'bi-shield'],
                'notifications' => ['Notifications', 'bi-bell'],
                'legal' => ['Legal', 'bi-file-text'],
                'api' => ['API', 'bi-code'],
                'cache' => ['Cache', 'bi-lightning']
            ];
            ?>
            <?php foreach ($tabLabels as $key => $label): ?>
                <?php if (isset($settings[$key]) && !empty($settings[$key])): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === $key ? 'active' : '' ?>"
                            id="<?= $key ?>-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#<?= $key ?>"
                            type="button"
                            role="tab">
                            <i class="<?= $label[1] ?> me-2"></i><?= $label[0] ?>
                        </button>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Settings Content -->
    <div class="tab-content" id="settingsTabsContent">
        <?php foreach ($settings as $category => $categorySettings): ?>
            <div class="tab-pane fade <?= $activeTab === $category ? 'show active' : '' ?>"
                id="<?= $category ?>"
                role="tabpanel"
                aria-labelledby="<?= $category ?>-tab">

                <form action="<?= url('/admin/settings/update') ?>" method="POST" class="dashboard-card" enctype="multipart/form-data">
                    <input type="hidden" name="category" value="<?= $category ?>">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">
                            <i class="<?= $tabLabels[$category][1] ?> me-2"></i>
                            <?= $tabLabels[$category][0] ?> Settings
                        </h4>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-pulse">
                                <i class="bi bi-check-circle me-2"></i>Save All Changes
                            </button>
                            <?php if ($category === 'cache'): ?>
                                <button type="button" class="btn btn-outline-warning" id="clearCacheBtn">
                                    <i class="bi bi-trash me-1"></i>Clear Cache
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($categorySettings as $key => $setting): ?>
                            <?php if ($setting['is_editable']): ?>
                                <div class="col-md-6 setting-item" id="setting-<?= $key ?>">
                                    <label class="form-label fw-bold">
                                        <?= ucwords(str_replace('_', ' ', $key)) ?>
                                        <?php if ($setting['type'] === 'boolean'): ?>
                                            <small class="text-muted">(Enable/Disable)</small>
                                        <?php endif; ?>
                                    </label>

                                    <?php if ($setting['description']): ?>
                                        <small class="text-muted d-block mb-2"><?= htmlspecialchars($setting['description']) ?></small>
                                    <?php endif; ?>

                                    <input type="hidden" name="settings[<?= $key ?>][type]" value="<?= $setting['type'] ?>">

                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="settings[<?= $key ?>][value]" value="0">
                                            <input class="form-check-input setting-input"
                                                type="checkbox"
                                                name="settings[<?= $key ?>][value]"
                                                value="1"
                                                data-key="<?= $key ?>"
                                                data-type="boolean"
                                                <?= $setting['value'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                            </label>
                                        </div>

                                    <?php elseif ($setting['type'] === 'text'): ?>
                                        <textarea class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>][value]"
                                            data-key="<?= $key ?>"
                                            data-type="text"
                                            rows="4"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['raw_value']) ?></textarea>

                                    <?php elseif ($setting['type'] === 'integer'): ?>
                                        <input type="number"
                                            class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>][value]"
                                            data-key="<?= $key ?>"
                                            data-type="integer"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">

                                    <?php elseif ($setting['type'] === 'email'): ?>
                                        <input type="email"
                                            class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>][value]"
                                            data-key="<?= $key ?>"
                                            data-type="email"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="example@domain.com">

                                    <?php elseif ($setting['type'] === 'url'): ?>
                                        <input type="url"
                                            class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>][value]"
                                            data-key="<?= $key ?>"
                                            data-type="url"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="https://example.com">

                                    <?php elseif (in_array($key, ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'])): ?>
                                        <!-- Sensitive fields -->
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                name="settings[<?= $key ?>][value]"
                                                data-key="<?= $key ?>"
                                                data-type="password"
                                                value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                placeholder="Enter <?= strtolower(str_replace('_', ' ', $key)) ?>">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                    <?php else: ?>
                                        <!-- Default string input -->
                                        <input type="text"
                                            class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>][value]"
                                            data-key="<?= $key ?>"
                                            data-type="string"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                    <?php endif; ?>

                                    <?php if (has_error("settings.{$key}")): ?>
                                        <div class="invalid-feedback"><?= get_error("settings.{$key}") ?></div>
                                    <?php endif; ?>

                                    <!-- Individual save button -->
                                    <button type="button" class="btn btn-sm btn-success setting-save-btn" data-key="<?= $key ?>">
                                        <i class="bi bi-check me-1"></i>Save
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Non-editable settings display -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted">
                                        <?= ucwords(str_replace('_', ' ', $key)) ?>
                                        <small class="text-muted">(Read-only)</small>
                                    </label>
                                    
                                    <?php if ($setting['description']): ?>
                                        <small class="text-muted d-block mb-2"><?= htmlspecialchars($setting['description']) ?></small>
                                    <?php endif; ?>
                                    
                                    <div class="form-control-plaintext">
                                        <?php if ($setting['type'] === 'boolean'): ?>
                                            <span class="badge bg-<?= $setting['value'] ? 'success' : 'secondary' ?>">
                                                <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                            </span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($setting['raw_value'] ?? 'Not set') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-pulse">
                            <i class="bi bi-check-circle me-2"></i>Save <?= ucfirst($category) ?> Settings
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">Test Email Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="testEmailForm">
                    <div class="mb-3">
                        <label for="testEmail" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="testEmail" required>
                        <div class="form-text">Enter an email address to receive the test email</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendTestEmailBtn">
                    <i class="bi bi-send me-1"></i>Send Test Email
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Track changes for individual settings
        const originalValues = {};
        const settingInputs = document.querySelectorAll('.setting-input');
        
        settingInputs.forEach(input => {
            const key = input.dataset.key;
            const type = input.dataset.type;
            
            // Store original value
            if (type === 'checkbox') {
                originalValues[key] = input.checked;
            } else {
                originalValues[key] = input.value;
            }
            
            // Add change listener
            input.addEventListener('change', function() {
                const currentValue = type === 'checkbox' ? this.checked : this.value;
                const settingItem = this.closest('.setting-item');
                
                if (currentValue != originalValues[key]) {
                    settingItem.classList.add('changed');
                } else {
                    settingItem.classList.remove('changed');
                }
            });
        });

        // Individual setting save
        document.querySelectorAll('.setting-save-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const key = this.dataset.key;
                const input = document.querySelector(`.setting-input[data-key="${key}"]`);
                const type = input.dataset.type;
                let value;
                
                if (type === 'checkbox') {
                    value = input.checked ? '1' : '0';
                } else {
                    value = input.value;
                }
                
                saveIndividualSetting(key, value, this);
            });
        });

        // Clear Cache Functionality
        const clearCacheBtn = document.getElementById('clearCacheBtn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to clear the application cache?')) {
                    return;
                }

                clearCacheBtn.disabled = true;
                clearCacheBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Clearing...';

                fetch('<?= url('/admin/settings/clear-cache') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Cache cleared successfully!');
                        } else {
                            alert('Failed to clear cache: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    })
                    .finally(() => {
                        clearCacheBtn.disabled = false;
                        clearCacheBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Clear Cache';
                    });
            });
        }

        // Switch label updates
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (label && label.classList.contains('form-check-label')) {
                    label.textContent = this.checked ? 'Enabled' : 'Disabled';
                }
            });
        });
    });

    // Save individual setting via AJAX
    function saveIndividualSetting(key, value, button) {
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

        fetch('<?= url('/admin/settings/update-setting') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ key: key, value: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const settingItem = button.closest('.setting-item');
                settingItem.classList.remove('changed');
                
                // Update original value
                const input = document.querySelector(`.setting-input[data-key="${key}"]`);
                if (input.dataset.type === 'checkbox') {
                    originalValues[key] = input.checked;
                } else {
                    originalValues[key] = input.value;
                }
                
                // Show success message
                showToast('Success', data.message, 'success');
            } else {
                showToast('Error', data.message, 'danger');
            }
        })
        .catch(error => {
            showToast('Error', 'Network error: ' + error.message, 'danger');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }

    // Show toast notification
    function showToast(title, message, type = 'info') {
        // You can implement your own toast notification system here
        // For now using alert
        alert(`${title}: ${message}`);
    }

    // Toggle password visibility
    function togglePasswordVisibility(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByTagName('form');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
<?php $this->end(); ?>