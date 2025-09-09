<?php

?>

<?php $this->start('content'); ?>
<!-- Categories Section -->
<div id="categories-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 page-header">
        <div>
            <h1 class="h2 mb-1">Manage Categories</h1>
            <p class="text-secondary">Organize and manage all event categories.</p>
        </div>
        <a href="<?= url("/admin/categories/create") ?>" class="btn btn-pulse" data-section="create-category">
            <i class="bi bi-plus-circle me-2"></i>Create Category
        </a>
    </div>

    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <?php if ($categories): ?>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-tags me-2"></i>
                            Category Management
                        </h5>
                        <small class="text-secondary">
                            <?= count($categories) ?> categor<?= count($categories) !== 1 ? 'ies' : 'y' ?> total
                        </small>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-wrapper">
                        <table class="table table-dark mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $k => $category): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="#" class="text-center">
                                            <span class="fw-semibold text-blue-1" style="color: var(--blue-1);">
                                                <?php echo $k + 1; ?>
                                            </span>
                                        </td>
                                        <td data-label="Category Name">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="category-icon bg-gradient d-flex align-items-center justify-content-center" 
                                                     style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--blue-2), var(--blue-3)); border-radius: 8px; font-size: 0.8rem;">
                                                    <i class="bi bi-tag text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-white text-capitalize"><?php echo $category->name; ?></div>
                                                    <small class="text-secondary">
                                                        <i class="bi bi-link-45deg me-1"></i>
                                                        Slug: <?php echo $category->slug; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Category Description">
                                            <div class="description-cell">
                                                <?php if($category->description): ?>
                                                    <span class="text-white" title="<?php echo $category->description; ?>">
                                                        <?php echo getExcerpt($category->description, 50); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <small class="text-secondary fst-italic">
                                                        <i class="bi bi-dash me-1"></i>No description
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Category Status">
                                            <div class="text-center">
                                                <span class="badge <?php echo $this->escape($category->status === 'active' ? 'bg-success' : 'bg-warning'); ?> text-capitalize">
                                                    <i class="bi bi-<?php echo $this->escape($category->status === 'active' ? 'check-circle' : 'clock'); ?> me-1"></i>
                                                    <?php echo $category->status; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle" 
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical me-1"></i>
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= url("/admin/categories/edit/{$category->slug}") ?>">
                                                            <i class="bi bi-pencil me-2"></i>Edit Category
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="<?php echo $this->escape(url("/admin/categories/delete/{$category->slug}")); ?>" 
                                                              method="post" 
                                                              onsubmit="return confirm('Are you sure you want to delete \'<?php echo $category->name; ?>\'? This action cannot be undone.');"
                                                              class="m-0">
                                                            <button type="submit" class="dropdown-item text-danger w-100 text-start border-0 bg-transparent">
                                                                <i class="bi bi-trash me-2"></i>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if(isset($pagination) && $pagination): ?>
                    <div class="card-footer">
                        <nav aria-label="Categories pagination">
                            <?php echo $pagination; ?>
                        </nav>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-tags"></i>
                    </div>
                    <h4 class="h5 mb-3">No Categories Found</h4>
                    <p class="text-muted mb-4">You haven't created any categories yet. Start by creating your first category to organize your events.</p>
                    <a href="<?= url('/admin/categories/create') ?>" class="btn btn-pulse">
                        <i class="bi bi-bookmark-plus me-2"></i>Create Your First Category
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
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