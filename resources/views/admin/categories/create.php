<?php

?>

@section('content')
<!-- Create Event Section -->
<div id="create-event-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Create New Category</h1>
        <p class="text-secondary">Fill in the details below to create category.</p>
    </div>

    <div class="dashboard-card">

        <form action="" method="post" id="createEventForm">
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" value="<?= old('name') ?>" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" placeholder="Enter your event title">
                    <?php if (has_error('name')): ?>
                        <div class="invalid-feedback"><?= get_error('name') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>" name="status">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <?php if (has_error('status')): ?>
                        <div class="invalid-feedback"><?= get_error('status') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" name="description" rows="4" placeholder="Describe category..."><?= old('description') ?></textarea>
                    <?php if (has_error('description')): ?>
                        <div class="invalid-feedback"><?= get_error('description') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-pulse">
                    <i class="bi bi-check-circle me-2"></i>Create Category
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')

@endsection