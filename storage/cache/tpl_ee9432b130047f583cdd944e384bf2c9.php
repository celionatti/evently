<?php

declare(strict_types=1);

?>

<?php $this->start('content'); ?>
<div id="profile-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Profile Settings</h1>
        <p class="text-secondary">Manage your account settings and preferences.</p>
    </div>

    <div class="row g-4">
        <!-- Left Column - Profile Info -->
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="text-center">
                    <img src="<?php echo get_image("", "/dist/img/no_image.png"); ?>"
                        class="profile-avatar mb-3" alt="Profile Avatar">
                    <h3 class="text-capitalize"><?php echo $user->name . ' ' . $user->other_name; ?></h3>
                    <p class="text-secondary"><?php echo $user->role === 'admin' ? 'Administrator' : 'Event Manager'; ?></p>

                    <div class="security-badge mb-3 mx-auto">
                        <i class="bi bi-shield-check"></i>
                        <span>Account Verified</span>
                    </div>

                    <div class="profile-stats justify-content-center mb-4">
                        <div class="profile-stat">
                            <div class="profile-stat-value"><?php echo $user->events ?? 0; ?></div>
                            <div class="profile-stat-label">Events</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-value"><?php echo $user->attendees ?? '0.0k'; ?></div>
                            <div class="profile-stat-label">Attendees</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-value"><?php echo $user->rating ?? 0; ?></div>
                            <div class="profile-stat-label">Rating</div>
                        </div>
                    </div>

                    <button class="btn btn-ghost w-100 mb-2 disabled">
                        <i class="bi bi-camera me-2"></i>Change Photo
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - Forms -->
        <div class="col-lg-8">
            <div class="dashboard-card">
                <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="pill"
                            data-bs-target="#personal" type="button" role="tab">
                            Personal Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="pill"
                            data-bs-target="#security" type="button" role="tab">
                            Security
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabsContent">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <form action="<?php echo url('/admin/profile') ?>" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?php echo old('name', $user->name); ?>" placeholder="First Name" required>
                                    <?php if (has_error('name')): ?>
                                        <div class="invalid-feedback"><?= get_error('name') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Other Name <span class="text-danger">*</span></label>
                                    <input type="text" name="other_name" class="form-control <?= has_error('other_name') ? 'is-invalid' : '' ?>" value="<?php echo old('other_name', $user->other_name); ?>" placeholder="Last Name" required>
                                    <?php if (has_error('other_name')): ?>
                                        <div class="invalid-feedback"><?= get_error('other_name') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $user->email; ?>" placeholder="example@mail.com" disabled readonly>
                                    <small class="text-white">Email cannot be changed for security reasons</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>" value="<?php echo old('phone', $user->phone); ?>" placeholder="e.g., +234 812 345 6789" required>
                                    <?php if (has_error('phone')): ?>
                                        <div class="invalid-feedback"><?= get_error('phone') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bio</label>
                                    <textarea class="form-control <?= has_error('bio') ? 'is-invalid' : '' ?>" name="bio" rows="3" placeholder="Professional event organizer with 5+ years of experience creating memorable experiences." maxlength="500"><?php echo old('bio', $user->bio); ?></textarea>
                                    <small class="text-white">Maximum 500 characters</small>
                                    <?php if (has_error('bio')): ?>
                                        <div class="invalid-feedback"><?= get_error('bio') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Name (Organization)</label>
                                    <input type="text" name="business_name" class="form-control <?= has_error('business_name') ? 'is-invalid' : '' ?>" value="<?php echo old('business_name', $user->business_name); ?>" placeholder="Your Business or Organization Name" maxlength="100">
                                    <?php if (has_error('business_name')): ?>
                                        <div class="invalid-feedback"><?= get_error('business_name') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control <?= has_error('website') ? 'is-invalid' : '' ?>" value="<?php echo old('website', $user->website); ?>" placeholder="https://yourwebsite.com">
                                    <?php if (has_error('website')): ?>
                                        <div class="invalid-feedback"><?= get_error('website') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control <?= has_error('address') ? 'is-invalid' : '' ?>" value="<?php echo old('address', $user->address); ?>" placeholder="123 Event Street, Victoria Island" required maxlength="100">
                                    <?php if (has_error('address')): ?>
                                        <div class="invalid-feedback"><?= get_error('address') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select name="country" class="form-select <?= has_error('country') ? 'is-invalid' : '' ?>" required>
                                        <option value="">Select Country</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo $country; ?>" <?= old('country', $user->country) == $country ? 'selected' : '' ?>><?php echo $country; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (has_error('country')): ?>
                                        <div class="invalid-feedback"><?= get_error('country') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-pulse">
                                        <i class="bi bi-check-circle me-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form action="<?php echo url('/admin/profile/change-password') ?>" method="POST">
                            <div class="mb-4">
                                <h5 class="mb-3">Change Password</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" name="current_password" class="form-control <?= has_error('current_password') ? 'is-invalid' : '' ?>" placeholder="Enter your current password" required minlength="6">
                                        <?php if (has_error('current_password')): ?>
                                            <div class="invalid-feedback"><?= get_error('current_password') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                                        <input type="password" name="new_password" class="form-control <?= has_error('new_password') ? 'is-invalid' : '' ?>" placeholder="Enter new password" required minlength="6">
                                        <small class="text-white">Minimum 6 characters</small>
                                        <?php if (has_error('new_password')): ?>
                                            <div class="invalid-feedback"><?= get_error('new_password') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" name="confirm_password" class="form-control <?= has_error('confirm_password') ? 'is-invalid' : '' ?>" placeholder="Confirm new password" required minlength="6">
                                        <?php if (has_error('confirm_password')): ?>
                                            <div class="invalid-feedback"><?= get_error('confirm_password') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Security Notice:</strong> Changing your password will require you to login again on all devices for security purposes.
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-pulse">
                                            <i class="bi bi-key me-2"></i>Update Password
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="mb-3">Two-Factor Authentication</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Status: <span class="text-warning">Coming Soon</span></h6>
                                        <p class="text-secondary mb-0">Add an extra layer of security to your account</p>
                                    </div>
                                    <button type="button" class="btn btn-ghost disabled">
                                        <i class="bi bi-gear me-1"></i>Manage
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="mb-3">Account Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-white">Member Since</small>
                                        <div class="fw-medium"><?php echo date('F j, Y', strtotime($user->created_at)); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white">Last Updated</small>
                                        <div class="fw-medium"><?php echo date('F j, Y g:i A', strtotime($user->updated_at)); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white">User ID</small>
                                        <div class="fw-medium text-white"><?php echo $user->user_id; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white">Account Role</small>
                                        <div class="fw-medium">
                                            <span class="badge <?= $user->role === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                <?php echo ucfirst($user->role); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add character counter for bio field
document.addEventListener('DOMContentLoaded', function() {
    const bioField = document.querySelector('textarea[name="bio"]');
    if (bioField) {
        const charCount = document.createElement('small');
        charCount.className = 'text-white float-end';
        charCount.id = 'bio-char-count';
        
        function updateCharCount() {
            const remaining = 500 - bioField.value.length;
            charCount.textContent = `${bioField.value.length}/500`;
            charCount.className = remaining < 50 ? 'text-danger float-end' : 'text-white float-end';
        }
        
        bioField.addEventListener('input', updateCharCount);
        bioField.parentNode.appendChild(charCount);
        updateCharCount(); // Initialize count
    }
});

// Form validation enhancement
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
<?php $this->end(); ?>