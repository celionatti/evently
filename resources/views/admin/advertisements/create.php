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
<!-- Create Advertisement Section -->
<div id="create-advertisement-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">Create Advertisement</h1>
            <p class="text-secondary">Create a new advertisement campaign to promote your business.</p>
        </div>
        <a href="<?= url("/admin/advertisements") ?>" class="btn btn-outline-secondary">
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
                    <form id="createAdvertisementForm" action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Advertisement Title -->
                            <div class="col-12 mb-3">
                                <label for="title" class="form-label">Advertisement Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required maxlength="255" 
                                       placeholder="Enter advertisement title">
                                <div class="form-text text-white">A catchy title for your advertisement</div>
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required 
                                          placeholder="Describe your advertisement..."></textarea>
                                <div class="form-text text-white">Brief description of what this advertisement is about</div>
                            </div>

                            <!-- Advertisement Image -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Advertisement Image <span class="text-danger">*</span></label>
                                
                                <!-- Upload Type Toggle -->
                                <div class="mb-3">
                                    <div class="btn-group" role="group" aria-label="Upload type">
                                        <input type="radio" class="btn-check" name="upload_type" id="upload_file" value="file" checked>
                                        <label class="btn btn-outline-primary" for="upload_file">
                                            <i class="bi bi-cloud-upload me-2"></i>Upload File
                                        </label>

                                        <input type="radio" class="btn-check" name="upload_type" id="upload_url" value="url">
                                        <label class="btn btn-outline-primary" for="upload_url">
                                            <i class="bi bi-link-45deg me-2"></i>Image URL
                                        </label>
                                    </div>
                                </div>

                                <!-- File Upload Input -->
                                <div id="file_upload_section" class="upload-section">
                                    <input type="file" class="form-control" id="image_file" name="image_file" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text text-white">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB. Recommended: 1200x630px for landscape, 600x900px for portrait.
                                    </div>
                                </div>

                                <!-- URL Input -->
                                <div id="url_upload_section" class="upload-section" style="display: none;">
                                    <input type="url" class="form-control" id="image_url" name="image_url" 
                                           placeholder="https://example.com/image.jpg" onchange="previewImageFromUrl(this)">
                                    <div class="form-text text-white">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Enter a direct link to your image. Make sure the URL is publicly accessible.
                                    </div>
                                </div>

                                <!-- Image Preview -->
                                <div id="image_preview" class="mt-3" style="display: none;">
                                    <div class="preview-container">
                                        <img id="preview_img" src="" alt="Preview" class="img-preview">
                                        <button type="button" class="btn btn-sm btn-outline-danger preview-remove" onclick="removePreview()">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Target URL -->
                            <div class="col-12 mb-3">
                                <label for="target_url" class="form-label">Target URL</label>
                                <input type="url" class="form-control" id="target_url" name="target_url" 
                                       placeholder="https://example.com/landing-page">
                                <div class="form-text text-white">Where users will be redirected when they click the advertisement (optional)</div>
                            </div>

                            <!-- Ad Type and Featured Row -->
                            <div class="col-md-6 mb-3">
                                <label for="ad_type" class="form-label">Advertisement Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="ad_type" name="ad_type" required>
                                    <option value="">Select advertisement type</option>
                                    <option value="landscape">Landscape (Horizontal)</option>
                                    <option value="portrait">Portrait (Vertical)</option>
                                </select>
                                <div class="form-text text-white">Choose the orientation of your advertisement</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <input type="number" class="form-control" id="priority" name="priority" value="0" min="0" max="100">
                                <div class="form-text text-white">Higher priority ads are shown more frequently (0-100)</div>
                            </div>

                            <!-- Campaign Period -->
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                <div class="form-text text-white">When the advertisement campaign should start</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                <div class="form-text text-white">When the advertisement campaign should end</div>
                            </div>

                            <!-- Status Checkboxes -->
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                                    <label class="form-check-label" for="is_featured">
                                        <i class="bi bi-star me-1"></i>
                                        Featured Advertisement
                                    </label>
                                    <div class="form-text text-white">Featured ads get premium placement and higher visibility</div>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
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
                            <a href="<?= url('/admin/advertisements') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="previewAdvertisement()">
                                <i class="bi bi-eye me-2"></i>Preview
                            </button>
                            <button type="submit" class="btn btn-pulse">
                                <i class="bi bi-check-circle me-2"></i>Create Advertisement
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
                        <div class="preview-placeholder">
                            <i class="bi bi-image"></i>
                            <p>Advertisement preview will appear here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="dashboard-card mt-3 slide-up" style="animation-delay: 0.2s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Tips for Better Ads
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Use high-quality, eye-catching images
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Keep titles short and compelling
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Include a clear call-to-action
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Test different ad types and priorities
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Monitor performance and adjust accordingly
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default start date to now
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        // Format dates for datetime-local input
        startDateInput.value = now.toISOString().slice(0, 16);
        endDateInput.value = tomorrow.toISOString().slice(0, 16);

        // Handle upload type toggle
        const uploadTypeRadios = document.querySelectorAll('input[name="upload_type"]');
        const fileSection = document.getElementById('file_upload_section');
        const urlSection = document.getElementById('url_upload_section');

        uploadTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'file') {
                    fileSection.style.display = 'block';
                    urlSection.style.display = 'none';
                    document.getElementById('image_url').removeAttribute('required');
                    document.getElementById('image_file').setAttribute('required', 'required');
                } else {
                    fileSection.style.display = 'none';
                    urlSection.style.display = 'block';
                    document.getElementById('image_file').removeAttribute('required');
                    document.getElementById('image_url').setAttribute('required', 'required');
                }
                // Clear preview when switching
                removePreview();
            });
        });

        // Form validation
        const form = document.getElementById('createAdvertisementForm');
        form.addEventListener('submit', function(e) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (endDate <= startDate) {
                e.preventDefault();
                showToast('End date must be after start date', 'error');
                return;
            }
        });

        // Live preview updates
        const previewFields = ['title', 'description', 'ad_type', 'is_featured'];
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
        } else if (!url) {
            removePreview();
        }
    }

    function showImagePreview(src) {
        const previewContainer = document.getElementById('image_preview');
        const previewImg = document.getElementById('preview_img');
        
        previewImg.src = src;
        previewContainer.style.display = 'block';
        updatePreview();
    }

    function removePreview() {
        const previewContainer = document.getElementById('image_preview');
        const previewImg = document.getElementById('preview_img');
        
        previewImg.src = '';
        previewContainer.style.display = 'none';
        
        // Clear file inputs
        document.getElementById('image_file').value = '';
        document.getElementById('image_url').value = '';
        
        updatePreview();
    }

    function updatePreview() {
        const previewContainer = document.getElementById('ad_preview');
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const adType = document.getElementById('ad_type').value;
        const isFeatured = document.getElementById('is_featured').checked;
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
                </div>
            </div>
        `;
    }

    function previewAdvertisement() {
        const form = document.getElementById('createAdvertisementForm');
        const formData = new FormData(form);
        
        // Basic validation
        if (!formData.get('title') || !formData.get('description')) {
            showToast('Please fill in the title and description first', 'warning');
            return;
        }
        
        // Update the preview
        updatePreview();
        
        // Scroll to preview
        document.querySelector('.col-lg-4').scrollIntoView({ behavior: 'smooth' });
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

    // Toast notification function (assuming it exists globally)
    function showToast(message, type = 'info') {
        // Implement your toast notification logic here
        console.log(`${type.toUpperCase()}: ${message}`);
    }
</script>
@endsection