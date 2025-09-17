<?php

use Trees\Helper\Utils\TimeDateUtils;
?>

@section('styles')
<style>
    .upload-section {
        transition: all 0.3s ease;
    }

    .img-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .preview-container {
        position: relative;
        display: inline-block;
    }

    .preview-remove {
        position: absolute;
        top: -8px;
        right: -8px;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .advertisement-preview {
        min-height: 200px;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
    }

    .preview-placeholder {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
    }

    .preview-placeholder i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .advertisement-preview.has-content {
        border-style: solid;
        border-color: rgba(var(--bs-primary-rgb), 0.5);
        background: rgba(var(--bs-primary-rgb), 0.1);
        padding: 1rem;
    }

    .ad-preview-content {
        width: 100%;
        max-width: 300px;
    }

    .ad-preview-image {
        width: 100%;
        height: auto;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }

    .ad-preview-title {
        font-weight: 600;
        color: white;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .ad-preview-description {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.8rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .ad-preview-meta {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .ad-preview-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
</style>
@endsection

@section('content')
<!-- Edit Advertisement Section -->
<div id="edit-advertisement-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">Edit Advertisement</h1>
            <p class="text-secondary">Update <?= htmlspecialchars($advertisement->title) ?> advertisement campaign.</p>
        </div>
        <a href="<?= url("/admin/advertisements/manage") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Advertisements
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card slide-up">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-megaphone me-2"></i>
                        Advertisement Details
                    </h5>
                </div>
                <div class="card-body">
                    <form id="editAdvertisementForm" action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PUT">

                        <div class="row">
                            <!-- Advertisement Title -->
                            <div class="col-12 mb-3">
                                <label for="title" class="form-label">Advertisement Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= has_error('title') ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old('title', $advertisement->title) ?>" required maxlength="255"
                                    placeholder="Enter advertisement title">
                                <?php if (has_error('title')): ?>
                                    <div class="invalid-feedback"><?= get_error('title') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">A catchy title for your advertisement</div>
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" id="description" name="description" rows="4" required
                                    placeholder="Describe your advertisement..."><?= old('description', $advertisement->description) ?></textarea>
                                <?php if (has_error('description')): ?>
                                    <div class="invalid-feedback"><?= get_error('description') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">Brief description of what this advertisement is about</div>
                            </div>

                            <!-- Target URL -->
                            <div class="col-12 mb-3">
                                <label for="target_url" class="form-label">Target URL</label>
                                <input type="url" class="form-control <?= has_error('target_url') ? 'is-invalid' : '' ?>" id="target_url" name="target_url" value="<?= old('target_url', $advertisement->target_url) ?>"
                                    placeholder="https://example.com/landing-page">
                                <?php if (has_error('target_url')): ?>
                                    <div class="invalid-feedback"><?= get_error('target_url') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">Where users will be redirected when they click the advertisement (optional)</div>
                            </div>

                            <!-- Ad Type and Featured Row -->
                            <div class="col-md-6 mb-3">
                                <label for="ad_type" class="form-label">Advertisement Type <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('ad_type') ? 'is-invalid' : '' ?>" id="ad_type" name="ad_type" required>
                                    <option value="">Select advertisement type</option>
                                    <option value="landscape" <?= old('ad_type', $advertisement->ad_type) === 'landscape' ? 'selected' : '' ?>>Landscape (Horizontal)</option>
                                    <option value="portrait" <?= old('ad_type', $advertisement->ad_type) === 'portrait' ? 'selected' : '' ?>>Portrait (Vertical)</option>
                                </select>
                                <?php if (has_error('ad_type')): ?>
                                    <div class="invalid-feedback"><?= get_error('ad_type') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">Choose the orientation of your advertisement</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <input type="number" class="form-control <?= has_error('priority') ? 'is-invalid' : '' ?>" id="priority" name="priority" value="<?= old('priority', $advertisement->priority) ?>" min="0" max="100">
                                <?php if (has_error('priority')): ?>
                                    <div class="invalid-feedback"><?= get_error('priority') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">Higher priority ads are shown more frequently (0-100)</div>
                            </div>

                            <!-- Campaign Period -->
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control <?= has_error('start_date') ? 'is-invalid' : '' ?>" id="start_date" name="start_date" value="<?= old('start_date', date('Y-m-d\TH:i', strtotime($advertisement->start_date))) ?>" required>
                                <?php if (has_error('start_date')): ?>
                                    <div class="invalid-feedback"><?= get_error('start_date') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">When the advertisement campaign should start</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control <?= has_error('end_date') ? 'is-invalid' : '' ?>" id="end_date" name="end_date" value="<?= old('end_date', date('Y-m-d\TH:i', strtotime($advertisement->end_date))) ?>" required>
                                <?php if (has_error('end_date')): ?>
                                    <div class="invalid-feedback"><?= get_error('end_date') ?></div>
                                <?php endif; ?>
                                <div class="form-text text-white">When the advertisement campaign should end</div>
                            </div>

                            <!-- Status Checkboxes -->
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= $advertisement->is_featured ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <i class="bi bi-star me-1"></i>
                                        Featured Advertisement
                                    </label>
                                    <div class="form-text text-white">Featured ads get premium placement and higher visibility</div>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $advertisement->is_active ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Active Advertisement
                                    </label>
                                    <div class="form-text text-white">Only active advertisements will be displayed to users</div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="<?= url('/admin/advertisements/manage') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="previewAdvertisement()">
                                <i class="bi bi-eye me-2"></i>Preview
                            </button>
                            <button type="submit" class="btn btn-pulse">
                                <i class="bi bi-check-circle me-2"></i>Update Advertisement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <div class="dashboard-card slide-up" style="animation-delay: 0.1s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-eye me-2"></i>
                        Live Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="ad_preview" class="advertisement-preview">
                        <img id="preview_img" src="<?= $advertisement->image_url ? get_image($advertisement->image_url) : '' ?>" alt="Ad Preview" class="ad-preview-image" style="<?= $advertisement->image_url ? '' : 'display:none;' ?>">
                        <!-- <div class="preview-placeholder">
                            <i class="bi bi-image"></i>
                            <p>Advertisement preview will appear here</p>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the preview with current data
        updatePreview();;

        // Form validation
        const form = document.getElementById('editAdvertisementForm');
        form.addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);

            if (endDate <= startDate) {
                e.preventDefault();
                showToast('End date must be after start date', 'error');
                return;
            }
        });

        // Live preview updates
        const previewFields = ['title', 'description', 'ad_type', 'is_featured', 'is_active'];
        previewFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', updatePreview);
                field.addEventListener('change', updatePreview);
            }
        });
    });

    // Image preview functions
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                showImagePreview(e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewImageFromUrl(input) {
        const url = input.value.trim();
        if (url && isValidUrl(url)) {
            const img = new Image();
            img.onload = function() {
                showImagePreview(url);
            };
            img.onerror = function() {
                showToast('Unable to load image from URL', 'error');
                removePreview();
            };
            img.src = url;
        }
    }

    function showImagePreview(src) {
        const previewContainer = document.getElementById('image_preview');
        const previewImg = document.getElementById('preview_img');

        previewImg.src = src;
        previewContainer.style.display = 'block';
        updatePreview();
    }

    function updatePreview() {
        const previewContainer = document.getElementById('ad_preview');
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const adType = document.getElementById('ad_type').value;
        const isFeatured = document.getElementById('is_featured').checked;
        const isActive = document.getElementById('is_active').checked;
        const previewImg = document.getElementById('preview_img');
        const hasImage = previewImg.src && previewImg.src !== '';

        if (!title && !description && !hasImage) {
            previewContainer.innerHTML = `
                <div class="preview-placeholder">
                    <i class="bi bi-image"></i>
                    <p>Advertisement preview will appear here</p>
                </div>
            `;
            previewContainer.classList.remove('has-content');
            return;
        }

        previewContainer.classList.add('has-content');
        previewContainer.innerHTML = `
            <div class="ad-preview-content">
                ${hasImage ? `<img src="${previewImg.src}" alt="Ad Preview" class="ad-preview-image">` : ''}
                ${title ? `<div class="ad-preview-title">${escapeHtml(title)}</div>` : ''}
                ${description ? `<div class="ad-preview-description">${escapeHtml(description)}</div>` : ''}
                <div class="ad-preview-meta">
                    ${adType ? `<span class="badge bg-info ad-preview-badge">${adType.charAt(0).toUpperCase() + adType.slice(1)}</span>` : ''}
                    ${isFeatured ? `<span class="badge bg-warning ad-preview-badge"><i class="bi bi-star-fill"></i> Featured</span>` : ''}
                    ${isActive ? `<span class="badge bg-success ad-preview-badge"><i class="bi bi-check-circle"></i> Active</span>` : '<span class="badge bg-danger ad-preview-badge"><i class="bi bi-x-circle"></i> Inactive</span>'}
                </div>
            </div>
        `;
    }

    function previewAdvertisement() {
        const form = document.getElementById('editAdvertisementForm');
        const formData = new FormData(form);

        // Basic validation
        if (!formData.get('title') || !formData.get('description')) {
            showToast('Please fill in the title and description first', 'warning');
            return;
        }

        // Update the preview
        updatePreview();

        // Scroll to preview
        document.querySelector('.col-lg-4').scrollIntoView({
            behavior: 'smooth'
        });
        showToast('Preview updated!', 'success');
    }

    // Utility functions
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endsection