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
                            <div class="profile-stat-value">12</div>
                            <div class="profile-stat-label">Events</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-value">2.4K</div>
                            <div class="profile-stat-label">Attendees</div>
                        </div>
                        <div class="profile-stat">
                            <div class="profile-stat-value">4.2</div>
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
                        <form action="" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo old('name', $user->name); ?>" placeholder="First Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Other Name</label>
                                    <input type="text" name="other_name" class="form-control" value="<?php echo old('other_name', $user->other_name); ?>" placeholder="Other Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo old('email', $user->email); ?>" placeholder="example@mail.com" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo old('phone', $user->phone); ?>" placeholder="e.g., +234 812 345 6789">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bio</label>
                                    <textarea class="form-control" name="bio" rows="3" placeholder="Professional event organizer with 5+ years of experience creating memorable experiences."><?php echo old('bio', $user->bio); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Name(Organization)</label>
                                    <input type="text" name="business_name" class="form-control" value="<?php echo old('business_name', $user->business_name); ?>" placeholder="Your Business or Organization Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control" value="<?php echo old('website', $user->website); ?>" placeholder="https://yourwebsite.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" value="<?php echo old('address', $user->address); ?>" placeholder="123 Event Street, Victoria Island">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Country</label>
                                    <select name="country" class="form-select" aria-placeholder="Select Country">
                                        <option selected>Nigeria</option>
                                        <option>UK</option>
                                        <option>Kenya</option>
                                    </select>
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
                        <form>
                            <div class="mb-4">
                                <h5 class="mb-3">Change Password</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control">
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
                                        <h6 class="mb-1">Status: <span class="text-secondary">Pending</span></h6>
                                        <p class="text-secondary mb-0">Add an extra layer of security to your account</p>
                                    </div>
                                    <button type="button" class="btn btn-ghost disabled">
                                        <i class="bi bi-gear me-1"></i>Manage
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>