<?php

?>

<?php $this->start('content'); ?>
<div id="users-section" class="content-section">
    <div class="dashboard-card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-bell"></i>
            </div>
            <div><span class="text-warning">******</span> Notice <span class="text-warning">******</span></div>
            <h4 class="h5 mb-2">Creating User not Available</h4>
            <p class="text-white text-center">Please Note: Creating of User is not available currently, User can manually use the sign up form, then later the admin user can change the user role, based on the user role.</p>
            <span class="text-warning">***********</span>
            <a href="<?= url('/admin/users/manage') ?>" class="btn btn-primary mt-2">
                <i class="bi bi-people me-1"></i> Manage Users
            </a>
        </div>
    </div>
</div>
<?php $this->end(); ?>