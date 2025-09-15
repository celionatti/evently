<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

@section('content')
<!-- Events Section -->
<div id="events-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">Advertisements</h1>
            <p class="text-secondary">Manage your advertisement listings and track performance.</p>
        </div>
        <a href="<?= url("/admin/advertisement/create") ?>" class="btn btn-pulse" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Advertisement
        </a>
    </div>

    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <?php if ($advertisements): ?>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-tv me-2"></i>
                            Advertisement Listings
                        </h5>
                        <small class="text-secondary">
                            <?= count($advertisements) ?> advertisement<?= count($advertisements) !== 1 ? 's' : '' ?> total
                        </small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-wrapper">
                        <table class="table table-dark mb-0">
                            <thead>
                                <tr>
                                    <rtis scope="col">Advertisement</rtis>
                                    <th scope="col">Advertisement Link</th>
                                    <th scope="col">Start Date</th>
                                    <!-- <th scope="col">End Date</th> -->
                                    <th scope="col">Type</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advertisements as $k => $advertisement): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Event">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= get_image($advertisement->image_url, "dist/img/no_image.png") ?>"
                                                    class="rounded shadow-sm"
                                                    style="width: 60px; height: 40px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.1);" loading="lazy">
                                                <div>
                                                    <div class="fw-semibold text-white">{{{ $advertisement->title }}}</div>
                                                    <small class="text-secondary">
                                                        <?php if ($advertisement->clicks): ?>
                                                            <i class="bi bi-tags me-1"></i>{{{ $advertisement->clicks }}} •
                                                        <?php endif; ?>

                                                        <?php if ($advertisement->clicks): ?>
                                                            <i class="bi bi-geo-alt me-1"></i>{{{ $advertisement->impressions }}}
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Advertisement Link">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-break" title="{{{ $advertisement->target_url }}}" style="font-family: monospace; font-size: 0.85rem;">
                                                    {{{ getExcerpt($advertisement->target_url, 20) }}}
                                                </span>
                                                <?php if ($advertisement->target_url): ?>
                                                    <button class="btn btn-ghost action-btn" onclick="copyToClipboard('{{{ $advertisement->target_url }}}')" title="Copy link">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Advertisment Start Date">
                                            <div class="text-center">
                                                <div class="fw-medium text-white">
                                                    <?= TimeDateUtils::create($advertisement->start_date)->toCustomFormat('j M, Y') ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?= TimeDateUtils::create($advertisement->end_date)->toCustomFormat('j M, Y') ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Advertisement Type">
                                            <div class="text-center">
                                                <span class="badge bg-{{{ $advertisement->ad_type == "landscape" ? 'success' : 'secondary' }}} text-capitalize">
                                                    <i class="bi bi-{{{ $advertisement->ad_type == "landscape" ? 'tablet-landscape' : 'tablet' }}} me-1"></i>
                                                    {{{ $advertisement->ad_type == "landscape" ? 'Landscape' : 'Portrait' }}}
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <div class="text-center">
                                                <span class="badge {{ $advertisement->is_active == '1' ? 'bg-success' : 'bg-warning' }} text-capitalize">
                                                    <i class="bi bi-{{ $advertisement->is_active == '1' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                    {{{ $advertisement->is_active == '1' ? 'Active' : 'Inactive' }}}
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Actions" class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{{ url("/admin/advertisement/view/{$advertisement->target_url}") }}}" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="View Event Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{{ url("/admin/advertisement/edit/{$advertisement->id}") }}}" class="btn btn-outline-warning action-btn" data-bs-toggle="tooltip" title="Edit Advertisement">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#deleteEventModal"
                                                    data-event-slug="{{ $advertisement->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination if needed -->
                <?php if (isset($pagination) && $pagination): ?>
                    <div class="card-footer">
                        <nav aria-label="Events pagination">
                            <?= $pagination ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-tv"></i>
                    </div>
                    <h4 class="h5 mb-3">No Advertisement Found</h4>
                    <p class="text-muted mb-4">You haven't created any advertisement yet. Start by creating your first advertisement to manage bookings and track performance.</p>
                    <a href="<?= url('/admin/advertisement/create') ?>" class="btn btn-pulse">
                        <i class="bi bi-tv-fill me-2"></i>Create Your First Advertisement
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this event?</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently delete:</p>
                    <ul class="text-danger">
                        <li>The event details</li>
                        <li>All associated tickets</li>
                        <li>The event image</li>
                    </ul>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteEventForm" method="POST">
                        <button type="submit" class="btn btn-danger">Delete Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteEventModal = document.getElementById('deleteEventModal');
        const deleteEventForm = document.getElementById('deleteEventForm');

        deleteEventModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const eventSlug = button.getAttribute('data-event-slug');
            deleteEventForm.action = `/admin/events/delete/${eventSlug}`;
        });
    });

    // Delete event
    function deleteEvent(eventId, eventTitle) {
        if (confirm(`Are you sure you want to delete "${eventTitle}"? This action cannot be undone.`)) {
            fetch(`<?= url('/admin/events/delete/') ?>${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Event deleted successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Failed to delete event', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Add staggered animation on page load
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
    });
</script>
@endsection