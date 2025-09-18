<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

<?php $this->start('content'); ?>
<!-- Articles Section -->
<div id="articles-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">My Articles</h1>
            <p class="text-secondary">Manage your article content and track engagement.</p>
        </div>
        <a href="<?= url("/admin/articles/create") ?>" class="btn btn-pulse" data-section="create-article">
            <i class="bi bi-plus-circle me-2"></i>Create Article
        </a>
    </div>

    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <?php if ($articles): ?>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-newspaper me-2"></i>
                            Article Listings
                        </h5>
                        <small class="text-secondary">
                            <?= count($articles) ?> article<?= count($articles) !== 1 ? 's' : '' ?> total
                        </small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-wrapper">
                        <table class="table table-dark mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Article</th>
                                    <th scope="col">Created</th>
                                    <th scope="col">Engagement</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $k => $article): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Article">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= get_image($article->image, "dist/img/default.png") ?>"
                                                    class="rounded shadow-sm"
                                                    style="width: 60px; height: 40px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.1);" loading="lazy">
                                                <div>
                                                    <div class="fw-semibold text-white" title="<?php echo $article->title; ?>"><?php echo getExcerpt($article->title, 50); ?></div>
                                                    <small class="text-secondary">
                                                        <?php if ($article->tags): ?>
                                                            <i class="bi bi-tags me-1"></i><?php echo getExcerpt($article->tags, 30); ?> •
                                                        <?php endif; ?>
                                                        <i class="bi bi-file-text me-1"></i><?php echo getExcerpt($article->meta_title, 25); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Created">
                                            <div class="text-center">
                                                <div class="fw-medium text-white">
                                                    <?= TimeDateUtils::create($article->created_at)->toCustomFormat('j M, Y') ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?= TimeDateUtils::create($article->created_at)->toCustomFormat('H:i A') ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Engagement">
                                            <div class="d-flex justify-content-center gap-3">
                                                <div class="text-center">
                                                    <div class="fw-medium text-info">
                                                        <i class="bi bi-eye me-1"></i><?= number_format($article->views) ?>
                                                    </div>
                                                    <small class="text-secondary">Views</small>
                                                </div>
                                                <div class="text-center">
                                                    <div class="fw-medium text-danger">
                                                        <i class="bi bi-heart me-1"></i><?= number_format($article->likes) ?>
                                                    </div>
                                                    <small class="text-secondary">Likes</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <div class="text-center">
                                                <span class="badge <?php echo $this->escape($article->status == 'publish' ? 'bg-success' : 'bg-warning'); ?> text-capitalize">
                                                    <i class="bi bi-<?php echo $this->escape($article->status == 'publish' ? 'check-circle' : 'file-earmark'); ?> me-1"></i>
                                                    <?php echo $article->status === 'publish' ? 'Published' : 'Draft'; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Actions" class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="<?php echo url("/admin/articles/view/{$article->slug}"); ?>" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="View Article Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?php echo url("/admin/articles/edit/{$article->slug}"); ?>" class="btn btn-outline-warning action-btn" data-bs-toggle="tooltip" title="Edit Article">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($article->status === 'publish'): ?>
                                                    <a href="<?php echo url("/articles/{$article->slug}"); ?>" class="btn btn-outline-info action-btn" data-bs-toggle="tooltip" title="View Published Article" target="_blank">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#deleteArticleModal"
                                                    data-article-slug="<?php echo $this->escape($article->slug); ?>"
                                                    data-article-title="<?php echo $this->escape($article->title); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination if needed -->
                <?php if (isset($pagination) && $pagination): ?>
                    <div class="card-footer">
                        <nav aria-label="Articles pagination">
                            <?= $pagination ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-newspaper"></i>
                    </div>
                    <h4 class="h5 mb-3 text-white">No Articles Found</h4>
                    <p class="text-white mb-4">You haven't created any articles yet. Start by creating your first article to share your insights and engage with your audience.</p>
                    <a href="<?= url('/admin/articles/create') ?>" class="btn btn-pulse">
                        <i class="bi bi-pencil-square me-2"></i>Create Your First Article
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteArticleModal" tabindex="-1" aria-labelledby="deleteArticleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteArticleModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the article "<span id="articleTitleToDelete"></span>"?</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently delete:</p>
                    <ul class="text-danger">
                        <li>The article content and metadata</li>
                        <li>All associated images</li>
                        <li>View and like statistics</li>
                    </ul>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteArticleForm" method="POST">
                        <button type="submit" class="btn btn-danger">Delete Article</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="d-flex justify-content-between align-items-center dashboard-grid mt-4">
        <div class="stat-card">
            <div class="stat-number">
                <?= count($articles) ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-newspaper me-1"></i>
                Total Articles
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $publishedArticles = array_filter($articles, function ($article) {
                    return $article->status === 'publish';
                });
                echo count($publishedArticles);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-check-circle me-1"></i>
                Published
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $totalViews = array_sum(array_column($articles, 'views'));
                echo number_format($totalViews);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-eye me-1"></i>
                Total Views
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $totalLikes = array_sum(array_column($articles, 'likes'));
                echo number_format($totalLikes);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-heart me-1"></i>
                Total Likes
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteArticleModal = document.getElementById('deleteArticleModal');
        const deleteArticleForm = document.getElementById('deleteArticleForm');
        const articleTitleToDelete = document.getElementById('articleTitleToDelete');

        deleteArticleModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const articleSlug = button.getAttribute('data-article-slug');
            const articleTitle = button.getAttribute('data-article-title');
            
            deleteArticleForm.action = `/admin/articles/delete/${articleSlug}`;
            articleTitleToDelete.textContent = articleTitle;
        });
    });

    // Add staggered animation on page load
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
    });
</script>
<?php $this->end(); ?>