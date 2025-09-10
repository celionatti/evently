<?php $this->start('styles'); ?>
<style>
    .cleanup-stats {
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: 10px;
    }
    
    .danger-zone {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.3);
        border-radius: 10px;
    }
    
    .event-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .event-item:hover {
        background: rgba(255, 255, 255, 0.08);
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- Event Cleanup Section -->
<div id="cleanup-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">Event Cleanup</h1>
            <p class="text-secondary">Manage and clean up old events to maintain database performance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url("/admin/events/manage") ?>" class="btn btn-ghost btn-sm">
                <i class="bi bi-arrow-left me-2"></i>Back to Events
            </a>
        </div>
    </div>

    <!-- Cleanup Configuration -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card slide-up">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Cleanup Settings
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= url('/admin/events/cleanup') ?>" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label for="months" class="form-label">Delete events older than:</label>
                            <select class="form-select" id="months" name="months" onchange="this.form.submit()">
                                <option value="1" <?= $months_old == 1 ? 'selected' : '' ?>>1 month</option>
                                <option value="2" <?= $months_old == 2 ? 'selected' : '' ?>>2 months</option>
                                <option value="3" <?= $months_old == 3 ? 'selected' : '' ?>>3 months</option>
                                <option value="6" <?= $months_old == 6 ? 'selected' : '' ?>>6 months</option>
                                <option value="12" <?= $months_old == 12 ? 'selected' : '' ?>>1 year</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <div class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Events with dates before <strong><?= date('F j, Y', strtotime($stats['cutoff_date'])) ?></strong> will be affected.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <?php if ($stats['count'] > 0): ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-number text-warning"><?= $stats['count'] ?></div>
                <div class="stat-label">
                    <i class="bi bi-calendar-x me-1"></i>
                    Old Events
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-number text-info"><?= number_format($stats['total_attendees']) ?></div>
                <div class="stat-label">
                    <i class="bi bi-people me-1"></i>
                    Total Attendees
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-number text-success"><?= number_format($stats['total_tickets']) ?></div>
                <div class="stat-label">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    Total Tickets
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-number text-primary"><?= $stats['estimated_freed_space'] ?></div>
                <div class="stat-label">
                    <i class="bi bi-hdd me-1"></i>
                    Space to Free
                </div>
            </div>
        </div>

        <!-- Cleanup Summary -->
        <div class="dashboard-grid-full">
            <div class="dashboard-card table-card slide-up">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            Events to be Deleted
                        </h5>
                        <span class="badge bg-warning text-dark"><?= $stats['count'] ?> events</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="cleanup-stats p-4 mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            What will be deleted:
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check text-warning me-2"></i>
                                        <?= $stats['count'] ?> event records
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check text-warning me-2"></i>
                                        <?= number_format($stats['total_attendees']) ?> attendee records
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check text-warning me-2"></i>
                                        <?= number_format($stats['total_tickets']) ?> ticket records
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check text-warning me-2"></i>
                                        <?= number_format($stats['total_transactions']) ?> transaction records
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Events List -->
                    <div class="row">
                        <?php foreach ($stats['events'] as $index => $event): ?>
                            <?php if ($index < 6): // Show only first 6 events ?>
                                <div class="col-md-6 mb-3">
                                    <div class="event-item p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 text-truncate"><?= htmlspecialchars($event['title']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?= date('M j, Y', strtotime($event['date'])) ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-secondary">Old</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (count($stats['events']) > 6): ?>
                            <div class="col-12 text-center">
                                <div class="text-muted">
                                    <i class="bi bi-three-dots me-1"></i>
                                    And <?= count($stats['events']) - 6 ?> more events...
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone - Cleanup Actions -->
        <div class="dashboard-grid-full">
            <div class="dashboard-card slide-up">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Danger Zone
                    </h5>
                </div>
                <div class="card-body">
                    <div class="danger-zone p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-danger mb-2">Delete Old Events</h6>
                                <p class="text-muted mb-0">
                                    This action will permanently delete all events older than <?= $months_old ?> months 
                                    along with their attendees, tickets, transactions, and associated files. 
                                    <strong>This action cannot be undone.</strong>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="d-grid gap-2">
                                    <!-- Dry Run Button -->
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="performCleanup(true)">
                                        <i class="bi bi-eye me-2"></i>
                                        Preview Cleanup
                                    </button>
                                    
                                    <!-- Actual Cleanup Button -->
                                    <button type="button" class="btn btn-danger" 
                                            onclick="confirmCleanup()">
                                        <i class="bi bi-trash me-2"></i>
                                        Delete <?= $stats['count'] ?> Events
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Old Events -->
        <div class="dashboard-grid-full">
            <div class="dashboard-card text-center slide-up">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success mb-3">All Clean!</h4>
                    <p class="text-muted mb-4">
                        No events older than <?= $months_old ?> months found. Your database is clean and optimized.
                    </p>
                    <div class="text-muted small">
                        Events with dates before <strong><?= date('F j, Y', strtotime($stats['cutoff_date'])) ?></strong> would be considered for cleanup.
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Cleanup Form (Hidden) -->
    <form id="cleanupForm" action="<?= url('/admin/events/execute-cleanup') ?>" method="POST" style="display: none;">
        <input type="hidden" name="months_old" value="<?= $months_old ?>">
        <input type="hidden" name="dry_run" id="dry_run_input" value="0">
    </form>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmCleanupModal" tabindex="-1" aria-labelledby="confirmCleanupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-white text-dark">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmCleanupModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirm Cleanup Operation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>You are about to permanently delete:</p>
                <ul class="text-danger fw-bold">
                    <li><?= $stats['count'] ?? 0 ?> events</li>
                    <li><?= number_format($stats['total_attendees'] ?? 0) ?> attendee records</li>
                    <li><?= number_format($stats['total_tickets'] ?? 0) ?> ticket records</li>
                    <li><?= number_format($stats['total_transactions'] ?? 0) ?> transaction records</li>
                    <li>Associated event images (~<?= $stats['estimated_freed_space'] ?? '0 B' ?>)</li>
                </ul>
                
                <p>Please type <strong>DELETE</strong> to confirm:</p>
                <input type="text" class="form-control" id="confirmationInput" placeholder="Type DELETE to confirm">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmCleanupBtn" disabled onclick="executeCleanup()">
                    <i class="bi bi-trash me-2"></i>Delete Events
                </button>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    // Confirmation input handler
    document.getElementById('confirmationInput').addEventListener('input', function() {
        const confirmBtn = document.getElementById('confirmCleanupBtn');
        if (this.value === 'DELETE') {
            confirmBtn.disabled = false;
        } else {
            confirmBtn.disabled = true;
        }
    });

    function confirmCleanup() {
        const modal = new bootstrap.Modal(document.getElementById('confirmCleanupModal'));
        document.getElementById('confirmationInput').value = '';
        document.getElementById('confirmCleanupBtn').disabled = true;
        modal.show();
    }

    function performCleanup(dryRun = false) {
        document.getElementById('dry_run_input').value = dryRun ? '1' : '0';
        
        if (dryRun) {
            // For preview, submit directly
            document.getElementById('cleanupForm').submit();
        } else {
            // For actual cleanup, show confirmation modal
            confirmCleanup();
        }
    }

    function executeCleanup() {
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('confirmCleanupModal')).hide();
        
        // Show loading state
        showToast('Starting cleanup process...', 'info');
        
        // Submit form
        document.getElementById('cleanupForm').submit();
    }

    // Add loading overlay for cleanup operations
    document.getElementById('cleanupForm').addEventListener('submit', function() {
        const overlay = document.createElement('div');
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.8)';
        overlay.style.zIndex = '9999';
        overlay.innerHTML = `
            <div class="text-center text-white">
                <div class="spinner-border mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>Processing cleanup operation...</div>
                <div class="small text-muted mt-2">This may take a few moments</div>
            </div>
        `;
        document.body.appendChild(overlay);
    });
</script>
<?php $this->end(); ?>