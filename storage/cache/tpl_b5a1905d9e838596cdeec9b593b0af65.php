<?php

?>

<?php $this->start('content'); ?>
<!-- Events Section -->
<div id="events-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Manage Categories</h1>
            <p class="text-secondary">Manage all categories.</p>
        </div>
        <a href="<?= url("/admin/categories/create") ?>" class="btn btn-pulse flex-end" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Category
        </a>
    </div>

    <div class="dashboard-card">
        <?php if ($categories): ?>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $k => $category): ?>
                            <tr>
                                <td><?php echo $k + 1; ?></td>
                                <td class="text-capitalize"><?php echo $category->name; ?></td>
                                <td><?php echo $category->description; ?></td>
                                <td class="text-capitalize"><span class="badge <?php echo $this->escape($category->status === 'active' ? 'bg-success' : 'bg-warning'); ?>"><?php echo $category->status; ?></span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-ghost btn-sm dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="<?= url("/admin/categories/edit/{$category->slug}") ?>"><i
                                                        class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item text-danger" href="<?= url("/admin/categories/delete/{$category->slug}") ?>"><i
                                                        class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $pagination; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h4 class="h5 mb-2">No Categories Found</h4>
                <p class="text-muted">Create your first category to get started.</p>
                <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary mt-2">
                    <i class="bi bi-bookmark-plus me-1"></i> Create Category
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>

<?php $this->end(); ?>