<?php

declare(strict_types=1);

?>

@section('content')
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<div id="settings-section" class="content-section">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center page-header">
            <div>
                <h1 class="h2 mb-1">System Settings</h1>
                <p class="text-secondary">Configure application settings and system preferences.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= url('/admin/settings/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Add Setting
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($settings)): ?>
        <div class="dashboard-card text-center py-5">
            <i class="bi bi-gear-wide-connected display-1 text-white mb-3"></i>
            <h3>No Settings Found</h3>
            <p class="text-white mb-4">Get started by creating your first system setting.</p>
            <a href="<?= url('/admin/settings/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus me-2"></i>Create First Setting
            </a>
        </div>
    <?php else: ?>
        <!-- Settings Navigation -->
        <div class="dashboard-card mb-4">
            <ul class="nav nav-pills" id="settingsTabs" role="tablist">
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
                                <span class="badge bg-light text-dark ms-1"><?= count($settings[$key]) ?></span>
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
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
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

                        <div class="settings-grid">
                            <?php foreach ($categorySettings as $key => $setting): ?>
                                <div class="setting-item fade-in" id="setting-<?= $setting['id'] ?>">
                                    <div class="setting-actions">
                                        <?php if ($setting['is_editable']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary setting-edit-btn"
                                                data-id="<?= $setting['id'] ?>"
                                                data-key="<?= $key ?>"
                                                data-setting='<?= json_encode($setting) ?>'>
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        <?php endif; ?>
                                        <div class="btn-group ms-1">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="<?= url("/admin/settings/edit/{$setting['id']}") ?>">
                                                        <i class="bi bi-pencil me-2"></i>Full Edit
                                                    </a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item text-danger" href="#"
                                                        onclick="deleteSetting(<?= $setting['id'] ?>, '<?= $key ?>')">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <label class="form-label fw-bold">
                                        <?= ucwords(str_replace('_', ' ', $key)) ?>
                                        <?php if ($setting['type'] === 'boolean'): ?>
                                            <small class="text-white">(Enable/Disable)</small>
                                        <?php endif; ?>
                                    </label>

                                    <?php if ($setting['description']): ?>
                                        <small class="text-secondary d-block mb-2"><?= htmlspecialchars($setting['description']) ?></small>
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
                                            <form class="setting-form" data-id="<?= $setting['id'] ?>" data-key="<?= $key ?>">
                                                <input type="hidden" name="type" value="<?= $setting['type'] ?>" class="form-control setting-input">

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
                                                    <textarea class="form-control setting-input"
                                                        name="value"
                                                        data-type="text"
                                                        rows="4"
                                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['raw_value']) ?></textarea>

                                                <?php elseif ($setting['type'] === 'integer'): ?>
                                                    <input type="number"
                                                        class="form-control setting-input"
                                                        name="value"
                                                        data-type="integer"
                                                        value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">

                                                <?php elseif ($setting['type'] === 'email'): ?>
                                                    <input type="email"
                                                        class="form-control setting-input"
                                                        name="value"
                                                        data-type="email"
                                                        value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                        placeholder="example@domain.com">

                                                <?php elseif ($setting['type'] === 'url'): ?>
                                                    <input type="url"
                                                        class="form-control setting-input"
                                                        name="value"
                                                        data-type="url"
                                                        value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                        placeholder="https://example.com">

                                                <?php elseif (in_array($key, ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'])): ?>
                                                    <!-- Sensitive fields -->
                                                    <div class="input-group">
                                                        <input type="password"
                                                            class="form-control setting-input"
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
                                                        class="form-control setting-input"
                                                        name="value"
                                                        data-type="string"
                                                        value="<?= htmlspecialchars($setting['raw_value']) ?>"
                                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
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
                                        <div class="text-secondary small mt-1">
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
    <?php endif; ?>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/admin/settings/import') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Import Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="settings_file" class="form-label">Settings File (JSON)</label>
                        <input type="file" class="form-control" id="settings_file" name="settings_file" accept=".json" required>
                        <div class="form-text">Upload a JSON file exported from this system.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the setting "<span id="settingKeyName"></span>"?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete Setting</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button functionality
        document.querySelectorAll('.setting-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const settingId = this.dataset.id;
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

                const settingId = this.dataset.id;
                const formData = new FormData(this);
                let value = formData.get('value');

                // For checkboxes, get the checked value
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    value = checkbox.checked ? '1' : '0';
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

                saveIndividualSetting(settingId, value, submitBtn, this);
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
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Success', 'Cache cleared successfully!', 'success');
                        } else {
                            showToast('Error', 'Failed to clear cache: ' + data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error', 'Error: ' + error.message, 'danger');
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

        // Convert boolean values properly for JSON
        let formattedValue = value;
        if (value === '1' || value === '0') {
            formattedValue = value === '1';
        }

        // Create form data instead of JSON for better compatibility
        const formData = new FormData();
        formData.append('id', id);
        formData.append('value', value); // Use the original value, not the boolean

        fetch('<?= url('/admin/settings/update-setting') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug log

                if (data.success) {
                    // Update UI
                    const settingItem = form.closest('.setting-item');
                    settingItem.classList.remove('edit-mode');

                    // Update the display value
                    const displayElement = settingItem.querySelector('.setting-value-display');
                    if (displayElement) {
                        updateDisplayValue(displayElement, value, data.data || {});
                    }

                    // Show success message
                    showToast('Success', data.message || 'Setting updated successfully', 'success');
                } else {
                    showToast('Error', data.message || 'Failed to update setting', 'danger');
                }
                button.disabled = false;
                button.innerHTML = originalHtml;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('Error', 'Network error: ' + error.message, 'danger');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalHtml;
            });
    }

    // Update display value helper
    function updateDisplayValue(displayElement, value, settingData) {
        try {
            if (settingData && settingData.type === 'boolean') {
                const isEnabled = value === '1' || value === 1 || value === true;
                displayElement.innerHTML = `<span class="badge bg-${isEnabled ? 'success' : 'secondary'}">${isEnabled ? 'Enabled' : 'Disabled'}</span>`;
            } else if (settingData && ['smtp_password', 'paystack_secret_key', 'google_maps_api_key'].includes(settingData.key)) {
                displayElement.textContent = '••••••••';
            } else if (settingData && settingData.type === 'text') {
                displayElement.innerHTML = `<div class="text-truncate" style="max-height: 3rem; overflow: hidden;">${escapeHtml(value)}</div>`;
            } else {
                displayElement.textContent = value || 'Not set';
            }
        } catch (error) {
            console.error('Error updating display:', error);
            displayElement.textContent = value || 'Not set';
        }
    }

    // Delete setting function
    function deleteSetting(id, key) {
        document.getElementById('settingKeyName').textContent = key.replace(/_/g, ' ');
        document.getElementById('deleteForm').action = `<?= url('/admin/settings/delete') ?>/${id}`;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Show toast notification
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
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
@endsection