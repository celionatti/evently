<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

<?php $this->start('content'); ?>
<!-- Events Section -->
<div id="users-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">User Management</h1>
            <p class="text-secondary">Manage all users and their permissions</p>
        </div>
        <a href="<?= url("/admin/users/create") ?>" class="btn btn-pulse flex-end" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create User
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">1,248</div>
                <div class="text-secondary">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">842</div>
                <div class="text-secondary">Active Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">64</div>
                <div class="text-secondary">Organizers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">342</div>
                <div class="text-secondary">New This Month</div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <?php if ($users): ?>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Joined</th>
                            <th scope="col">Events</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $k => $user): ?>
                            <tr>
                                <td data-label="User" class="text-capitalize">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80"
                                            class="user-avatar" alt="User Avatar">
                                        <div><?php echo $user->name . ' ' . $user->other_name; ?></div>
                                    </div>
                                </td>
                                <td data-label="Email"><?php echo $user->email; ?></td>
                                <td data-label="Role" class="text-capitalize"><span class="badge <?php echo $this->escape($user->role === 'admin' ? 'bg-success' : ($user->role === 'organiser' ? 'bg-info' : 'bg-secondary')); ?>"><?php echo $user->role; ?></span></td>
                                <td data-label="Status">
                                    <span class="badge <?php echo $this->escape($user->is_blocked == '0' ? 'bg-success' : 'bg-danger'); ?>">
                                        <?php echo $this->escape($user->is_blocked == '0' ? 'Active' : 'Suspended'); ?>
                                    </span>
                                </td>
                                <td data-label="Joined"><?php echo TimeDateUtils::create($user->created_at)->toCustomFormat('M j, Y'); ?></td>
                                <td data-label="Events"><?php echo $user->events ?? 0; ?></td>
                                <td data-label="Actions" class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($user->role === 'admin'): ?>
                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Guest?');">
                                                <input type="hidden" name="role" value="guest">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Guest Role">
                                                    <i class="bi bi-people"></i>
                                                </button>
                                            </form>

                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Organizer?');">
                                                <input type="hidden" name="role" value="organiser">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Organizer Role">
                                                    <i class="bi bi-person-vcard"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($user->role === 'organiser'): ?>
                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Guest?');">
                                                <input type="hidden" name="role" value="guest">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Guest Role">
                                                    <i class="bi bi-people"></i>
                                                </button>
                                            </form>

                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Admin?');">
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Admin Role">
                                                    <i class="bi bi-person-workspace"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Organizer?');">
                                                <input type="hidden" name="role" value="organiser">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Organizer Role">
                                                    <i class="bi bi-person-vcard"></i>
                                                </button>
                                            </form>

                                            <form action="<?php echo $this->escape(url("/admin/users/role/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to change User Role to Admin?');">
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="Admin Role">
                                                    <i class="bi bi-person-workspace"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form action="<?php echo $this->escape(url("/admin/users/delete/{$user->user_id}")); ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <button type="submit" class="btn btn-ghost action-btn text-danger" data-bs-toggle="tooltip" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
                <h4 class="h5 mb-2">No Users Found</h4>
                <p class="text-muted">Create your first category to get started.</p>
                <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary mt-2">
                    <i class="bi bi-bookmark-plus me-1"></i> Create User
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>

<?php $this->end(); ?>