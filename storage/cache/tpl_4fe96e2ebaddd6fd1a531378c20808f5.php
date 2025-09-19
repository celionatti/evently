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

                <form action="<?= url('/admin/settings') ?>" method="POST" class="dashboard-card" enctype="multipart/form-data">
                    <input type="hidden" name="category" value="<?= $category ?>">

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
                            <?php if ($setting['is_editable']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?= ucwords(str_replace('_', ' ', $key)) ?>
                                        <?php if ($setting['type'] === 'boolean'): ?>
                                            <small class="text-white">(Enable/Disable)</small>
                                        <?php endif; ?>
                                    </label>

                                    <?php if ($setting['description']): ?>
                                        <small class="text-white d-block mb-2"><?= htmlspecialchars($setting['description']) ?></small>
                                    <?php endif; ?>

                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="settings[<?= $key ?>]" value="0">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="settings[<?= $key ?>]"
                                                value="1"
                                                <?= $setting['value'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                <?= $setting['value'] ? 'Enabled' : 'Disabled' ?>
                                            </label>
                                        </div>

                                    <?php elseif ($setting['type'] === 'text'): ?>
                                        <textarea class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>]"
                                            rows="4"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['raw_value']) ?></textarea>

                                    <?php elseif ($setting['type'] === 'integer'): ?>
                                        <input type="number"
                                            class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>]"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">

                                    <?php elseif ($setting['type'] === 'email'): ?>
                                        <input type="email"
                                            class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>]"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="example@domain.com">

                                    <?php elseif ($setting['type'] === 'url'): ?>
                                        <input type="url"
                                            class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>]"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="https://example.com">

                                    <?php elseif (in_array($key, ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'])): ?>
                                        <!-- Sensitive fields -->
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                                name="settings[<?= $key ?>]"
                                                value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                placeholder="Enter <?= strtolower(str_replace('_', ' ', $key)) ?>">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility(this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                    <?php else: ?>
                                        <!-- Default string input -->
                                        <input type="text"
                                            class="form-control <?= has_error("settings.{$key}") ? 'is-invalid' : '' ?>"
                                            name="settings[<?= $key ?>]"
                                            value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                            placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                    <?php endif; ?>

                                    <?php if (has_error("settings.{$key}")): ?>
                                        <div class="invalid-feedback"><?= get_error("settings.{$key}") ?></div>
                                    <?php endif; ?>
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
        // Test Email Functionality
        const testEmailBtn = document.getElementById('testEmailBtn');
        const testEmailModal = new bootstrap.Modal(document.getElementById('testEmailModal'));


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