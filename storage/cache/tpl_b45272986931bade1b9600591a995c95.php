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
        display: none;
        margin-top: 1rem;
        text-align: center;
    }

    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- Create Article Section -->
<div id="create-article-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Create New Article</h1>
        <p class="text-secondary">Share your insights and engage with your audience through compelling content.</p>
    </div>

    <div class="dashboard-card">
        <form action="" method="post" enctype="multipart/form-data" id="createArticleForm">
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
                            placeholder="Enter a compelling title for your article" value="<?= old('title') ?>" maxlength="500">
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
                            placeholder="e.g., technology, business, lifestyle" value="<?= old('tags') ?>" maxlength="300">
                        <?php if (has_error('tags')): ?>
                            <div class="invalid-feedback"><?= get_error('tags') ?></div>
                        <?php endif; ?>
                        <small class="form-text text-secondary">Separate multiple tags with commas</small>
                    </div>

                    <div class="col-md-6">
                        <label for="contributors" class="form-label">Contributors</label>
                        <input type="text" name="contributors" id="contributors" class="form-control <?= has_error('contributors') ? 'is-invalid' : '' ?>"
                            placeholder="Co-authors or contributors" value="<?= old('contributors') ?>" maxlength="255">
                        <?php if (has_error('contributors')): ?>
                            <div class="invalid-feedback"><?= get_error('contributors') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label for="quote" class="form-label">Quote/Excerpt</label>
                        <input type="text" name="quote" id="quote" class="form-control <?= has_error('quote') ? 'is-invalid' : '' ?>"
                            placeholder="A compelling quote or excerpt from your article" value="<?= old('quote') ?>" maxlength="300">
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
                            name="content" id="content" rows="15" placeholder="Write your article content here..."><?= old('content') ?></textarea>
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
                        <label for="articleImageUpload" class="form-label">Featured Image *</label>
                        <input type="file" name="image" id="articleImageUpload" class="form-control <?= has_error('image') ? 'is-invalid' : '' ?>" accept="image/*">
                        <small class="form-text text-secondary">Upload a high-quality image (recommended: 1200x800px). Accepted formats: JPG, JPEG, PNG</small>
                        <?php if (has_error('image')): ?>
                            <div class="invalid-feedback"><?= get_error('image') ?></div>
                        <?php endif; ?>

                        <div class="image-preview-container mt-3" id="imagePreviewContainer">
                            <div class="mb-2">Image Preview:</div>
                            <img src="#" alt="Image Preview" class="image-preview" id="imagePreview">
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
                            placeholder="SEO title for search engines" value="<?= old('meta_title') ?>" maxlength="255">
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
                            placeholder="Brief description for search engines (150-160 characters recommended)" maxlength="300"><?= old('meta_description') ?></textarea>
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
                            placeholder="SEO keywords separated by commas" value="<?= old('meta_keywords') ?>" maxlength="255">
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
                            <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>Save as Draft</option>
                            <option value="publish" <?= old('status') === 'publish' ? 'selected' : '' ?>>Publish Now</option>
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
                    <i class="bi bi-check-circle me-2"></i>Create Article
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
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

        // Image preview functionality
        const articleImageUpload = document.getElementById('articleImageUpload');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');

        articleImageUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    imagePreview.src = reader.result;
                    imagePreviewContainer.style.display = 'block';
                });
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });

        // Auto-fill meta title from title
        const titleInput = document.getElementById('title');
        const metaTitleInput = document.getElementById('meta_title');
        
        titleInput.addEventListener('input', function() {
            if (!metaTitleInput.value) {
                metaTitleInput.value = this.value;
                // Trigger counter update
                metaTitleInput.dispatchEvent(new Event('input'));
            }
        });

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
        const form = document.getElementById('createArticleForm');
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