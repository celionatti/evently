<?php

declare(strict_types=1);

?>

@section('content')
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
                    <img src="{{{ get_image("", "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80") }}}"
                        class="profile-avatar mb-3" alt="Profile Avatar">
                    <h3>John Organizer</h3>
                    <p class="text-secondary">Event Manager</p>

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

                    <button class="btn btn-ghost w-100 mb-2">
                        <i class="bi bi-camera me-2"></i>Change Photo
                    </button>
                    <button class="btn btn-outline-secondary w-100">
                        <i class="bi bi-download me-2"></i>Export Data
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
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" value="John">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" value="Organizer">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="john@organizer.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" value="+234 812 345 6789">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bio</label>
                                    <textarea class="form-control" rows="3">Professional event organizer with 5+ years of experience creating memorable experiences.</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Organization</label>
                                    <input type="text" class="form-control" value="Event Masters NG">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control" value="https://eventmasters.ng">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" value="123 Event Street, Victoria Island">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <select class="form-select">
                                        <option selected>Lagos</option>
                                        <option>Abuja</option>
                                        <option>Port Harcourt</option>
                                        <option>Ibadan</option>
                                        <option>Kano</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <select class="form-select">
                                        <option selected>Lagos</option>
                                        <option>Abuja</option>
                                        <option>Rivers</option>
                                        <option>Oyo</option>
                                        <option>Kano</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" value="101241">
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
@endsection