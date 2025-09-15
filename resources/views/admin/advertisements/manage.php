<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

@section('content')
<!-- Advertisements Section -->
<div id="advertisements-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">My Advertisements</h1>
            <p class="text-secondary">Manage your advertisement campaigns and track performance.</p>
        </div>
        <a href="<?= url("/admin/advertisements/create") ?>" class="btn btn-pulse" data-section="create-advertisement">
            <i class="bi bi-plus-circle me-2"></i>Create Advertisement
        </a>
    </div>

    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <?php if ($advertisements): ?>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-megaphone me-2"></i>
                            Advertisement Campaigns
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
                                    <th scope="col">Advertisement</th>
                                    <th scope="col">Campaign Period</th>
                                    <th scope="col">Sta's</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advertisements as $k => $ad): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Advertisement">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= get_image($ad->image_url, "dist/img/no_image.png") ?>"
                                                    class="rounded shadow-sm"
                                                    style="width: 80px; height: 45px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.1);" loading="lazy">
                                                <div>
                                                    <div class="fw-semibold text-white">{{{ $ad->title }}}</div>
                                                    <small class="text-secondary d-flex align-items-center gap-2">
                                                        <span class="badge bg-<?= $ad->ad_type == 'landscape' ? 'info' : 'purple' ?> px-2 py-1">
                                                            <i class="bi bi-aspect-ratio me-1"></i>{{{ ucfirst($ad->ad_type) }}}
                                                        </span>
                                                        <?php if ($ad->is_featured): ?>
                                                            <span class="badge bg-warning px-2 py-1">
                                                                <i class="bi bi-star-fill me-1"></i>Featured
                                                            </span>
                                                        <?php endif; ?>
                                                        <span class="text-warning">
                                                            Priority: {{{ $ad->priority }}}
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Campaign Period">
                                            <div class="text-center">
                                                <div class="fw-medium text-white">
                                                    <?= TimeDateUtils::create($ad->start_date)->toCustomFormat('j M, Y') ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <i class="bi bi-arrow-down me-1"></i>
                                                    <?= TimeDateUtils::create($ad->end_date)->toCustomFormat('j M, Y') ?>
                                                </small>
                                                <?php 
                                                $now = new DateTime();
                                                $startDate = new DateTime($ad->start_date);
                                                $endDate = new DateTime($ad->end_date);
                                                $isActive = $now >= $startDate && $now <= $endDate;
                                                $isExpired = $now > $endDate;
                                                $isPending = $now < $startDate;
                                                ?>
                                                <div class="mt-1">
                                                    <?php if ($isExpired): ?>
                                                        <span class="badge bg-danger">Expired</span>
                                                    <?php elseif ($isPending): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif ($isActive): ?>
                                                        <span class="badge bg-success">Running</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Performance">
                                            <div class="text-center">
                                                <div class="fw-medium text-info">
                                                    <i class="bi bi-eye me-1"></i>{{{ number_format($ad->impressions) }}}
                                                </div>
                                                <div class="fw-medium text-success">
                                                    <i class="bi bi-cursor me-1"></i>{{{ number_format($ad->clicks) }}}
                                                </div>
                                                <?php if ($ad->impressions > 0): ?>
                                                    <small class="text-secondary">
                                                        CTR: <?= number_format(($ad->clicks / $ad->impressions) * 100, 2) ?>%
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-secondary">CTR: 0.00%</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <div class="text-center">
                                                <span class="badge {{ $ad->is_active ? 'bg-success' : 'bg-secondary' }} text-capitalize">
                                                    <i class="bi bi-{{ $ad->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                                    {{{ $ad->is_active ? 'Active' : 'Inactive' }}}
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Actions" class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{{ url("/admin/advertisements/view/{$ad->id}") }}}" class="btn btn-ghost action-btn" data-bs-toggle="tooltip" title="View Advertisement Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info action-btn"
                                                    onclick="toggleAdStatus()"
                                                    data-bs-toggle="tooltip" title="{{ $ad->is_active ? 'Deactivate' : 'Activate' }} Advertisement">
                                                    <i class="bi bi-{{ $ad->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <a href="{{{ url("/admin/advertisements/edit/{$ad->id}") }}}" class="btn btn-outline-warning action-btn" data-bs-toggle="tooltip" title="Edit Advertisement">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#deleteAdModal"
                                                    data-ad-id="{{ $ad->id }}"
                                                    data-ad-title="{{ $ad->title }}">
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
                        <nav aria-label="Advertisements pagination">
                            <?= $pagination ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <h4 class="h5 mb-3">No Advertisements Found</h4>
                    <p class="text-white mb-4">You haven't created any advertisements yet. Start by creating your first advertisement campaign to reach more customers.</p>
                    <a href="<?= url('/admin/advertisements/create') ?>" class="btn btn-pulse">
                        <i class="bi bi-megaphone me-2"></i>Create Your First Advertisement
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteAdModal" tabindex="-1" aria-labelledby="deleteAdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAdModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this advertisement?</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently delete:</p>
                    <ul class="text-danger">
                        <li>The advertisement details</li>
                        <li>All performance data (clicks, impressions)</li>
                        <li>The advertisement image</li>
                    </ul>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteAdForm" method="POST">
                        <button type="submit" class="btn btn-danger">Delete Advertisement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="dashboard-grid mt-4">
        <div class="stat-card">
            <div class="stat-number">
                <?= count($advertisements) ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-megaphone me-1"></i>
                Total Ads
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $activeAds = array_filter($advertisements ?? [], function ($ad) {
                    return $ad->is_active == 1;
                });
                echo count($activeAds);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-check-circle me-1"></i>
                Active Ads
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $totalImpressions = array_sum(array_map(function ($ad) {
                    return $ad->impressions;
                }, $advertisements ?? []));
                echo number_format($totalImpressions);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-eye me-1"></i>
                Total Impressions
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $totalClicks = array_sum(array_map(function ($ad) {
                    return $ad->clicks;
                }, $advertisements ?? []));
                echo number_format($totalClicks);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-cursor me-1"></i>
                Total Clicks
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteAdModal = document.getElementById('deleteAdModal');
        const deleteAdForm = document.getElementById('deleteAdForm');

        deleteAdModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const adId = button.getAttribute('data-ad-id');
            deleteAdForm.action = `/admin/advertisements/delete/${adId}`;
        });
    });

    // Toggle advertisement status
    function toggleAdStatus(adId, newStatus) {
        const action = newStatus === 'true' ? 'activate' : 'deactivate';
        const confirmMessage = `Are you sure you want to ${action} this advertisement?`;
        
        if (confirm(confirmMessage)) {
            fetch(`<?= url('/admin/advertisements/toggle-status/') ?>${adId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ is_active: newStatus === 'true' ? 1 : 0 })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`Advertisement ${action}d successfully!`, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(`Failed to ${action} advertisement`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Delete advertisement
    function deleteAdvertisement(adId, adTitle) {
        if (confirm(`Are you sure you want to delete "${adTitle}"? This action cannot be undone.`)) {
            fetch(`<?= url('/admin/advertisements/delete/') ?>${adId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Advertisement deleted successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Failed to delete advertisement', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Copy to clipboard functionality
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('URL copied to clipboard!', 'success');
        }, function(err) {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy URL', 'error');
        });
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