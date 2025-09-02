<?php

?>

@section('content')
<!-- Events Section -->
<div id="events-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Manage Users</h1>
            <p class="text-secondary">Manage all users.</p>
        </div>
        <a href="<?= url("/admin/users/create") ?>" class="btn btn-pulse flex-end" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create User
        </a>
    </div>

    <div class="dashboard-card">
        <?php if ($users): ?>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Business Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $k => $user): ?>
                            <tr>
                                <td data-label="#">{{{ $k + 1 }}}</td>
                                <td data-label="First Name" class="text-capitalize">{{{ $user->name }}}</td>
                                <td data-label="Last Name" class="text-capitalize">{{{ $user->other_name }}}</td>
                                <td data-label="Email">{{{ $user->email }}}</td>
                                <td data-label="Phone">{{{ $user->phone ?? "+234X-XXX" }}}</td>
                                <td data-label="Role" class="text-capitalize"><span class="badge {{ $user->role === 'admin' ? 'bg-success' : 'bg-info' }}">{{{ $user->role }}}</span></td>
                                <td data-label="Business Name">{{{ $user->business_name ?? "None" }}}</td>
                                <td data-label="Actions">
                                    <div class="dropdown">
                                        <button class="btn btn-ghost btn-sm dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <hr class="dropdown-divider">
                                            </li>
                                            <form action="{{ url("/admin/users/delete/{$user->user_id}") }}" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                {{{ $pagination }}}
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
@endsection

@section('scripts')

@endsection