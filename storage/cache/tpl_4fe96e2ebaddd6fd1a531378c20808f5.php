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
        position: relative;
    }

    .setting-item:hover {
        background-color: inherit;
        border-color: var(--blue-3);
        border: 2px dashed var(--blue-1);
    }

    .setting-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .setting-item:hover .setting-actions {
        opacity: 1;
    }

    .setting-edit-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .setting-save-btn {
        display: none;
        margin-top: 0.5rem;
    }

    .setting-item.edit-mode {
        border: 2px solid var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }

    .setting-item.edit-mode .setting-view-mode {
        display: none;
    }

    .setting-item.edit-mode .setting-edit-mode {
        display: block;
    }

    .setting-view-mode {
        display: block;
    }

    .setting-edit-mode {
        display: none;
    }

    .setting-value-display {
        min-height: 2.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        background-color: inherit;
        border: 1px solid var(--blue-1);
    }

    @media (max-width: 768px) {
        .nav-pills {
            flex-direction: column;
        }

        .nav-pills .nav-link {
            margin-right: 0;
            margin-bottom: 0.25rem;
        }

        .setting-actions {
            position: relative;
            top: 0;
            right: 0;
            opacity: 1;
            margin-bottom: 0.5rem;
            text-align: right;
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

                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">
                            <i class="<?= $tabLabels[$category][1] ?> me-2"></i>
                            <?= $tabLabels[$category][0] ?> Settings
                        </h4>

                        <div class="btn-group">
                            <?php if ($category === 'cache'): ?>
                                <button type="button" class="btn btn-outline-warning" id="clearCacheBtn">
                                    <i class="bi bi-trash me-1"></i>Clear Cache
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($categorySettings as $key => $setting): ?>
                            <div class="col-md-6 setting-item" id="setting-<?= $key ?>">
                                <div class="setting-actions">
                                    <?php if ($setting['is_editable']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary setting-edit-btn"
                                            data-key="<?= $key ?>"
                                            data-setting='<?= json_encode($setting) ?>'>
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <label class="form-label fw-bold">
                                    <?= ucwords(str_replace('_', ' ', $key)) ?>
                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <small class="text-muted">(Enable/Disable)</small>
                                    <?php endif; ?>
                                </label>

                                <?php if ($setting['description']): ?>
                                    <small class="text-muted d-block mb-2"><?= htmlspecialchars($setting['description']) ?></small>
                                <?php endif; ?>

                                <!-- View Mode (Display only) -->
                                <div class="setting-view-mode">
                                    <div class="setting-value-display">
                                        <?php if ($setting['type'] === 'boolean'): ?>
                                            <span class="badge bg-<?= $setting['value'] ? 'success' : 'secondary' ?>">
                                                <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                            </span>
                                        <?php elseif ($setting['type'] === 'text'): ?>
                                            <div class="text-truncate" style="max-height: 3rem; overflow: hidden;">
                                                <?= nl2br(htmlspecialchars($setting['raw_value'] ?? 'Not set')) ?>
                                            </div>
                                        <?php elseif (in_array($key, ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'])): ?>
                                            ••••••••
                                        <?php else: ?>
                                            <?= htmlspecialchars($setting['raw_value'] ?? 'Not set') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Edit Mode (Form inputs) -->
                                <?php if ($setting['is_editable']): ?>
                                    <div class="setting-edit-mode">
                                        <form class="setting-form" data-key="<?= $key ?>">
                                            <input type="hidden" name="type" value="<?= $setting['type'] ?>">

                                            <?php if ($setting['type'] === 'boolean'): ?>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="value" value="0">
                                                    <input class="form-check-input setting-input"
                                                        type="checkbox"
                                                        name="value"
                                                        value="1"
                                                        data-type="boolean"
                                                        <?= $setting['value'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">
                                                        <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                                    </label>
                                                </div>

                                            <?php elseif ($setting['type'] === 'text'): ?>
                                                <textarea class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                    name="value"
                                                    data-type="text"
                                                    rows="4"
                                                    placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['raw_value']) ?></textarea>

                                            <?php elseif ($setting['type'] === 'integer'): ?>
                                                <input type="number"
                                                    class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                    name="value"
                                                    data-type="integer"
                                                    value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                    placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">

                                            <?php elseif ($setting['type'] === 'email'): ?>
                                                <input type="email"
                                                    class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                    name="value"
                                                    data-type="email"
                                                    value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                    placeholder="example@domain.com">

                                            <?php elseif ($setting['type'] === 'url'): ?>
                                                <input type="url"
                                                    class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                    name="value"
                                                    data-type="url"
                                                    value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                    placeholder="https://example.com">

                                            <?php elseif (in_array($key, ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'])): ?>
                                                <!-- Sensitive fields -->
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control setting-input <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                        name="value"
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
                                                    name="value"
                                                    data-type="string"
                                                    value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                    placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                            <?php endif; ?>

                                            <?php if (has_error("settings.{$key}")): ?>
                                                <div class="invalid-feedback"><?= get_error("settings.{$key}") ?></div>
                                            <?php endif; ?>

                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check me-1"></i>Save
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary setting-cancel-btn">
                                                    <i class="bi bi-x me-1"></i>Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-lock"></i> This setting is read-only
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button functionality
        document.querySelectorAll('.setting-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const key = this.dataset.key;
                const settingItem = this.closest('.setting-item');

                // Enter edit mode
                settingItem.classList.add('edit-mode');

                // Focus on the first input
                const firstInput = settingItem.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });

        // Cancel button functionality
        document.querySelectorAll('.setting-cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const settingItem = this.closest('.setting-item');
                settingItem.classList.remove('edit-mode');
            });
        });

        // Form submission
        document.querySelectorAll('.setting-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const key = this.dataset.key;
                const formData = new FormData(this);
                const data = {
                    key: key,
                    value: formData.get('value'),
                    type: formData.get('type')
                };

                // For checkboxes, get the checked value
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    data.value = checkbox.checked ? '1' : '0';
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

                saveIndividualSetting(key, data.value, submitBtn, this);
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
    function saveIndividualSetting(id, value, button, form) {
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

        fetch('<?= url('/admin/settings/update-setting') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: id, // Send ID instead of key
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const settingItem = form.closest('.setting-item');
                    settingItem.classList.remove('edit-mode');

                    // Update the display value
                    const displayElement = settingItem.querySelector('.setting-value-display');
                    if (displayElement) {
                        // For boolean values
                        if (value === '1' || value === '0') {
                            const isEnabled = value === '1';
                            displayElement.innerHTML = `<span class="badge bg-${isEnabled ? 'success' : 'secondary'}">${isEnabled ? 'Enabled' : 'Disabled'}</span>`;
                        }
                        // For sensitive fields (mask with dots)
                        else if (data.data.type === 'password' || ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'].includes(data.data.key)) {
                            displayElement.textContent = '••••••••';
                        }
                        // For text areas
                        else if (form.querySelector('textarea')) {
                            displayElement.innerHTML = `<div class="text-truncate" style="max-height: 3rem; overflow: hidden;">${escapeHtml(value)}</div>`;
                        }
                        // For other values
                        else {
                            displayElement.textContent = value || 'Not set';
                        }
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

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
</script>
<?php $this->end(); ?>