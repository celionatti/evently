<?php

?>

<?php $this->start('styles'); ?>
<style>
    .content-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .image-preview-container {
        display: block;
        margin-top: 1rem;
        text-align: center;
    }

    .current-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .new-image-preview {
        display: none;
        margin-top: 1rem;
        text-align: center;
    }

    .invalid-feedback {
        display: block;
    }

    .content-editor {
        min-height: 300px;
    }

    .character-counter {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .counter-warning {
        color: #ffc107;
    }

    .counter-danger {
        color: #dc3545;
    }

    .form-section {
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        background: rgba(255, 255, 255, 0.02);
    }

    .section-title {
        color: #fff;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .article-stats {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #fff;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- Edit Article Section -->
<div id="edit-article-section" class="content-section">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h2 mb-1">Edit Article</h1>
                <p class="text-secondary">Update your article content and settings.</p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($article->status === 'publish'): ?>
                    <a href="<?= url("/articles/{$article->slug}") ?>" class="btn btn-outline-info btn-sm" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View Live
                    </a>
                <?php endif; ?>
                <a href="<?= url("/admin/articles/manage") ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to Articles
                </a>
            </div>
        </div>
    </div>

    <!-- Article Stats -->
    <div class="article-stats">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($article->views) ?></div>
                    <div class="stat-label">
                        <i class="bi bi-eye me-1"></i>Views
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($article->likes) ?></div>
                    <div class="stat-label">
                        <i class="bi bi-heart me-1"></i>Likes
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= date('j M, Y', strtotime($article->created_at)) ?></div>
                    <div class="stat-label">
                        <i class="bi bi-calendar me-1"></i>Created
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= date('j M, Y', strtotime($article->updated_at)) ?></div>
                    <div class="stat-label">
                        <i class="bi bi-clock me-1"></i>Last Updated
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <form action="" method="post" enctype="multipart/form-data" id="editArticleForm">
            <!-- <?php echo $this->escape(csrf_field()); ?> -->

            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-info-circle"></i>
                    Basic Information
                </div>
                
                <div class="row g-4">
                    <div class="col-12">
                        <label for="title" class="form-label">Article Title *</label>
                        <input type="text" name="title" id="title" class="form-control <?= has_error('title') ? 'is-invalid' : '' ?>"
                            placeholder="Enter a compelling title for your article" value="<?= old('title', $article->title) ?>" maxlength="500">
                        <?php if (has_error('title')): ?>
                            <div class="invalid-feedback"><?= get_error('title') ?></div>
                        <?php endif; ?>
                        <div class="character-counter mt-1">
                            <span id="titleCounter">0</span>/500 characters
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" id="tags" class="form-control <?= has_error('tags') ? 'is-invalid' : '' ?>"
                            placeholder="e.g., technology, business, lifestyle" value="<?= old('tags', $article->tags) ?>" maxlength="300">
                        <?php if (has_error('tags')): ?>
                            <div class="invalid-feedback"><?= get_error('tags') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-secondary">Separate multiple tags with commas</small>
                    </div>

                    <div class="col-md-6">
                        <label for="contributors" class="form-label">Contributors</label>
                        <input type="text" name="contributors" id="contributors" class="form-control <?= has_error('contributors') ? 'is-invalid' : '' ?>"
                            placeholder="Co-authors or contributors" value="<?= old('contributors', $article->contributors) ?>" maxlength="255">
                        <?php if (has_error('contributors')): ?>
                            <div class="invalid-feedback"><?= get_error('contributors') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label for="quote" class="form-label">Quote/Excerpt</label>
                        <input type="text" name="quote" id="quote" class="form-control <?= has_error('quote') ? 'is-invalid' : '' ?>"
                            placeholder="A compelling quote or excerpt from your article" value="<?= old('quote', $article->quote) ?>" maxlength="300">
                        <?php if (has_error('quote')): ?>
                            <div class="invalid-feedback"><?= get_error('quote') ?></div>
                        <?php endif; ?>
                        <div class="character-counter mt-1">
                            <span id="quoteCounter">0</span>/300 characters
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-file-text"></i>
                    Content
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label for="content" class="form-label">Article Content *</label>
                        <textarea class="form-control content-editor <?= has_error('content') ? 'is-invalid' : '' ?>"
                            name="content" id="content" rows="15" placeholder="Write your article content here..."><?= old('content', $article->content) ?></textarea>
                        <?php if (has_error('content')): ?>
                            <div class="invalid-feedback"><?= get_error('content') ?></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="form-text text-secondary">Minimum 50 characters required</small>
                            <div class="character-counter">
                                <span id="contentCounter">0</span> characters
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="articleImageUpload" class="form-label">Featured Image</label>
                        
                        <!-- Current Image -->
                        <?php if ($article->image): ?>
                            <div class="image-preview-container">
                                <div class="mb-2">Current Image:</div>
                                <img src="<?= get_image($article->image) ?>" alt="Current Article Image" class="current-image" id="currentImage">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="removeCurrentImage">
                                        <i class="bi bi-trash me-1"></i>Remove Current Image
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="image" id="articleImageUpload" class="form-control <?= has_error('image') ? 'is-invalid' : '' ?>" accept="image/*">
                        <small class="form-text text-secondary">
                            <?php if ($article->image): ?>
                                Upload a new image to replace the current one. Leave empty to keep current image.
                            <?php else: ?>
                                Upload a high-quality image (recommended: 1200x800px). Accepted formats: JPG, JPEG, PNG
                            <?php endif; ?>
                        </small>
                        <?php if (has_error('image')): ?>
                            <div class="invalid-feedback"><?= get_error('image') ?></div>
                        <?php endif; ?>

                        <!-- New Image Preview -->
                        <div class="new-image-preview" id="newImagePreviewContainer">
                            <div class="mb-2">New Image Preview:</div>
                            <img src="#" alt="New Image Preview" class="image-preview" id="newImagePreview">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-search"></i>
                    SEO & Metadata
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label for="meta_title" class="form-label">Meta Title *</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control <?= has_error('meta_title') ? 'is-invalid' : '' ?>"
                            placeholder="SEO title for search engines" value="<?= old('meta_title', $article->meta_title) ?>" maxlength="255">
                        <?php if (has_error('meta_title')): ?>
                            <div class="invalid-feedback"><?= get_error('meta_title') ?></div>
                        <?php endif; ?>
                        <div class="character-counter mt-1">
                            <span id="metaTitleCounter">0</span>/255 characters
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="meta_description" class="form-label">Meta Description *</label>
                        <textarea class="form-control <?= has_error('meta_description') ? 'is-invalid' : '' ?>"
                            name="meta_description" id="meta_description" rows="3" 
                            placeholder="Brief description for search engines (150-160 characters recommended)" maxlength="300"><?= old('meta_description', $article->meta_description) ?></textarea>
                        <?php if (has_error('meta_description')): ?>
                            <div class="invalid-feedback"><?= get_error('meta_description') ?></div>
                        <?php endif; ?>
                        <div class="character-counter mt-1">
                            <span id="metaDescCounter">0</span>/300 characters
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="meta_keywords" class="form-label">Meta Keywords *</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" class="form-control <?= has_error('meta_keywords') ? 'is-invalid' : '' ?>"
                            placeholder="SEO keywords separated by commas" value="<?= old('meta_keywords', $article->meta_keywords) ?>" maxlength="255">
                        <?php if (has_error('meta_keywords')): ?>
                            <div class="invalid-feedback"><?= get_error('meta_keywords') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-secondary">Enter relevant keywords for better search engine visibility</small>
                    </div>
                </div>
            </div>

            <!-- Publishing Options -->
            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-gear"></i>
                    Publishing Options
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Publication Status *</label>
                        <select name="status" id="status" class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>">
                            <option value="">Select Status</option>
                            <option value="draft" <?= old('status', $article->status) === 'draft' ? 'selected' : '' ?>>Save as Draft</option>
                            <option value="publish" <?= old('status', $article->status) === 'publish' ? 'selected' : '' ?>>Publish</option>
                        </select>
                        <?php if (has_error('status')): ?>
                            <div class="invalid-feedback"><?= get_error('status') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-secondary">
                            <strong>Draft:</strong> Save privately for later editing<br>
                            <strong>Publish:</strong> Make article publicly visible
                        </small>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-pulse">
                    <i class="bi bi-check-circle me-2"></i>Update Article
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-ghost" id="previewBtn">
                    <i class="bi bi-eye me-2"></i>Preview
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-white">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Article Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="articlePreview">
                    <!-- Preview content will be injected here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counters
        const counters = [
            { input: 'title', counter: 'titleCounter', max: 500 },
            { input: 'quote', counter: 'quoteCounter', max: 300 },
            { input: 'content', counter: 'contentCounter' },
            { input: 'meta_title', counter: 'metaTitleCounter', max: 255 },
            { input: 'meta_description', counter: 'metaDescCounter', max: 300 }
        ];

        counters.forEach(item => {
            const input = document.getElementById(item.input);
            const counter = document.getElementById(item.counter);
            
            if (input && counter) {
                // Initialize counter
                counter.textContent = input.value.length;
                updateCounterColor(counter, input.value.length, item.max);
                
                input.addEventListener('input', function() {
                    const length = this.value.length;
                    counter.textContent = length;
                    updateCounterColor(counter, length, item.max);
                });
            }
        });

        function updateCounterColor(counter, length, max) {
            if (!max) return;
            
            counter.classList.remove('counter-warning', 'counter-danger');
            
            if (length > max * 0.9) {
                counter.classList.add('counter-danger');
            } else if (length > max * 0.8) {
                counter.classList.add('counter-warning');
            }
        }

        // Image upload and preview functionality
        const articleImageUpload = document.getElementById('articleImageUpload');
        const newImagePreview = document.getElementById('newImagePreview');
        const newImagePreviewContainer = document.getElementById('newImagePreviewContainer');
        const currentImage = document.getElementById('currentImage');
        const removeCurrentImageBtn = document.getElementById('removeCurrentImage');

        articleImageUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    newImagePreview.src = reader.result;
                    newImagePreviewContainer.style.display = 'block';
                });
                reader.readAsDataURL(file);
            } else {
                newImagePreviewContainer.style.display = 'none';
            }
        });

        // Remove current image functionality
        if (removeCurrentImageBtn) {
            removeCurrentImageBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove the current image? This action cannot be undone.')) {
                    if (currentImage) {
                        currentImage.style.opacity = '0.3';
                    }
                    this.textContent = 'Image will be removed on save';
                    this.disabled = true;
                    this.classList.remove('btn-outline-danger');
                    this.classList.add('btn-outline-warning');
                    
                    // Add a hidden input to indicate image removal
                    const removeImageInput = document.createElement('input');
                    removeImageInput.type = 'hidden';
                    removeImageInput.name = 'remove_image';
                    removeImageInput.value = '1';
                    document.getElementById('editArticleForm').appendChild(removeImageInput);
                }
            });
        }

        // Preview functionality
        const previewBtn = document.getElementById('previewBtn');
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        const articlePreview = document.getElementById('articlePreview');

        previewBtn.addEventListener('click', function() {
            const title = document.getElementById('title').value;
            const content = document.getElementById('content').value;
            const quote = document.getElementById('quote').value;
            const tags = document.getElementById('tags').value;
            const contributors = document.getElementById('contributors').value;
            
            let previewHTML = `
                <article class="preview-article">
                    <header class="mb-4">
                        <h1 class="display-6 fw-bold text-dark mb-3">${title || 'Article Title'}</h1>
                        ${quote ? `<blockquote class="blockquote text-muted border-start border-primary ps-3 mb-3"><p class="mb-0">${quote}</p></blockquote>` : ''}
                        <div class="article-meta text-muted small">
                            <span><i class="bi bi-person me-1"></i>By ${contributors || 'Author'}</span>
                            <span class="mx-2">•</span>
                            <span><i class="bi bi-calendar me-1"></i>${new Date().toLocaleDateString()}</span>
                            ${tags ? `<div class="mt-2"><i class="bi bi-tags me-1"></i>${tags.split(',').map(tag => `<span class="badge bg-light text-dark me-1">${tag.trim()}</span>`).join('')}</div>` : ''}
                        </div>
                    </header>
                    <div class="article-content">
                        ${content ? content.replace(/\n/g, '</p><p>').replace(/^<p>/, '<p>').replace(/<p>$/, '</p>') : '<p>Article content will appear here...</p>'}
                    </div>
                </article>
            `;
            
            articlePreview.innerHTML = previewHTML;
            previewModal.show();
        });

        // Form validation enhancement
        const form = document.getElementById('editArticleForm');
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const metaTitle = document.getElementById('meta_title').value.trim();
            const metaDescription = document.getElementById('meta_description').value.trim();
            const metaKeywords = document.getElementById('meta_keywords').value.trim();
            const status = document.getElementById('status').value;
            
            if (!title || !content || !metaTitle || !metaDescription || !metaKeywords || !status) {
                e.preventDefault();
                alert('Please fill in all required fields before submitting.');
                return false;
            }
            
            if (content.length < 50) {
                e.preventDefault();
                alert('Article content must be at least 50 characters long.');
                return false;
            }
        });
    });
</script>
<?php $this->end(); ?>