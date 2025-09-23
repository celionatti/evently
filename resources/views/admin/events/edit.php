<?php

?>

@section('styles')
<style>
    .content-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .image-preview-container {
        /* display: none; */
        margin-top: 1rem;
        text-align: center;
    }

    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .invalid-feedback {
        display: block;
    }

    .current-image {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .ticket-tier {
        position: relative;
        border: 2px solid #e9ecef;
        transition: border-color 0.3s ease;
    }

    .ticket-tier.existing-ticket {
        border-color: #28a745;
    }

    .ticket-tier.new-ticket {
        border-color: #007bff;
    }

    .ticket-tier.marked-for-deletion {
        border-color: #dc3545;
        opacity: 0.7;
    }

    .ticket-badge {
        position: absolute;
        top: -1px;
        right: -1px;
        padding: 2px 8px;
        border-radius: 0 6px 0 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .ticket-badge.existing {
        background-color: #28a745;
        color: white;
    }

    .ticket-badge.new {
        background-color: #007bff;
        color: white;
    }

    .ticket-badge.delete {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('content')
<!-- Edit Event Section -->
<div id="edit-event-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Edit Event: {{{ $event->event_title }}}</h1>
        <p class="text-secondary">Update the details below to modify your event.</p>
    </div>

    <div class="dashboard-card">
        <form action="" method="post" enctype="multipart/form-data" id="editEventForm">
            <!-- {{ csrf_field() }} -->

            <!-- Hidden input for tickets to delete -->
            <input type="hidden" name="tickets_to_delete" id="ticketsToDelete" value="">

            <div class="row g-4">
                <div class="col-md-8">
                    <label for="event_title" class="form-label">Event Title *</label>
                    <input type="text" name="event_title" id="event_title" class="form-control <?= has_error('event_title') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event title" value="<?= old('event_title', $event->event_title) ?>">
                    <?php if (has_error('event_title')): ?>
                        <div class="invalid-feedback"><?= get_error('event_title') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select <?= has_error('category') ? 'is-invalid' : '' ?>">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>" <?= old('category', $event->category) == $category->id ? 'selected' : '' ?>>
                                <?= $category->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (has_error('category')): ?>
                        <div class="invalid-feedback"><?= get_error('category') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>"
                        name="description" rows="4" placeholder="Describe your event..." id="description"><?= old('description', $event->description) ?></textarea>
                    <?php if (has_error('description')): ?>
                        <div class="invalid-feedback"><?= get_error('description') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label for="event_link" class="form-label">Event Link</label>
                    <input type="text" name="event_link" id="event_link" class="form-control <?= has_error('event_link') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event link" value="<?= old('event_link', $event->event_link) ?>">
                    <?php if (has_error('event_link')): ?>
                        <div class="invalid-feedback"><?= get_error('event_link') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="tags" class="form-label">Event Tags</label>
                    <input type="text" name="tags" id="tags" class="form-control <?= has_error('tags') ? 'is-invalid' : '' ?>"
                        placeholder="Enter event tags" value="<?= old('tags', $event->tags) ?>">
                    <?php if (has_error('tags')): ?>
                        <div class="invalid-feedback"><?= get_error('tags') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="venue" class="form-label">Venue *</label>
                    <input type="text" name="venue" id="venue" class="form-control <?= has_error('venue') ? 'is-invalid' : '' ?>"
                        placeholder="Event venue" value="<?= old('venue', $event->venue) ?>">
                    <?php if (has_error('venue')): ?>
                        <div class="invalid-feedback"><?= get_error('venue') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City *</label>
                    <input type="text" name="city" id="city" class="form-control <?= has_error('city') ? 'is-invalid' : '' ?>"
                        placeholder="City: ikeja, Lagos, Lekki" value="<?= old('city', $event->city) ?>">
                    <?php if (has_error('city')): ?>
                        <div class="invalid-feedback"><?= get_error('city') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="event_date" class="form-label">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" class="form-control <?= has_error('event_date') ? 'is-invalid' : '' ?>"
                        value="<?= old('event_date', $event->event_date) ?>">
                    <?php if (has_error('event_date')): ?>
                        <div class="invalid-feedback"><?= get_error('event_date') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="start_time" class="form-label">Start Time *</label>
                    <input type="time" id="start_time" name="start_time" class="form-control <?= has_error('start_time') ? 'is-invalid' : '' ?>"
                        value="<?= old('start_time', $event->start_time) ?>">
                    <?php if (has_error('start_time')): ?>
                        <div class="invalid-feedback"><?= get_error('start_time') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= old('end_date', $event->end_date) ?>">
                </div>
                <div class="col-md-6">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" value="<?= old('end_time', $event->end_time) ?>">
                </div>
                <div class="col-12">
                    <label for="eventImageUpload" class="form-label">Event Image</label>
                    <?php if (!empty($event->event_image)): ?>
                        <div class="mb-2">
                            <small class="form-text text-secondary">Current image:</small><br>
                            <img src="<?= $event->event_image ?>" alt="Current Event Image" class="current-image">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="event_image" id="eventImageUpload" class="form-control <?= has_error('event_image') ? 'is-invalid' : '' ?>" accept="image/*">
                    <small class="form-text text-secondary">Upload a new image to replace the current one (recommended: 1200x630px)</small>
                    <?php if (has_error('event_image')): ?>
                        <div class="invalid-feedback"><?= get_error('event_image') ?></div>
                    <?php endif; ?>

                    <div class="image-preview-container mt-3" id="imagePreviewContainer">
                        <div class="mb-2">Image Preview:</div>
                        <img src="<?= get_image($event->event_image) ?>" alt="Image Preview" class="image-preview" id="imagePreview" loading="lazy">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" id="phone" name="phone" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact phone" value="<?= old('phone', $event->phone) ?>">
                    <?php if (has_error('phone')): ?>
                        <div class="invalid-feedback"><?= get_error('phone') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="mail" class="form-label">Mail *</label>
                    <input type="email" id="mail" name="mail" class="form-control <?= has_error('mail') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact mail" value="<?= old('mail', $event->mail) ?>">
                    <?php if (has_error('mail')): ?>
                        <div class="invalid-feedback"><?= get_error('mail') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12">
                    <label for="social" class="form-label">Social *</label>
                    <input type="url" id="social" name="social" class="form-control <?= has_error('social') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event social link" value="<?= old('social', $event->social) ?>">
                    <?php if (has_error('social')): ?>
                        <div class="invalid-feedback"><?= get_error('social') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="ticket_sales" class="form-label">Ticket Sales *</label>
                    <select name="ticket_sales" id="ticket_sales" class="form-select <?= has_error('ticket_sales') ? 'is-invalid' : '' ?>">
                        <option value="">Select Option</option>
                        <option value="close" <?= old('ticket_sales', $event->ticket_sales) === 'close' ? 'selected' : '' ?>>Close</option>
                        <option value="open" <?= old('ticket_sales', $event->ticket_sales) === 'open' ? 'selected' : '' ?>>Open</option>
                    </select>
                    <?php if (has_error('ticket_sales')): ?>
                        <div class="invalid-feedback"><?= get_error('ticket_sales') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Event Status *</label>
                    <select name="status" id="status" class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>">
                        <option value="">Select Option</option>
                        <option value="disable" <?= old('status', $event->status) === 'disable' ? 'selected' : '' ?>>Disable</option>
                        <option value="active" <?= old('status', $event->status) === 'active' ? 'selected' : '' ?>>Active</option>
                    </select>
                    <?php if (has_error('status')): ?>
                        <div class="invalid-feedback"><?= get_error('status') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ticket Configuration -->
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Ticket Configuration</h5>
                    <button type="button" class="btn btn-ghost btn-sm" id="addTicketTier">
                        <i class="bi bi-plus-circle me-1"></i>Add New Ticket
                    </button>
                </div>

                <div id="ticketTiers">
                    <?php
                    $ticketIndex = 0;
                    $oldTickets = old('tickets', []);
                    $existingTickets = is_array($event->tickets) ? $event->tickets : [];

                    // If we have form errors, prioritize old input
                    if (!empty($oldTickets)) {
                        foreach ($oldTickets as $index => $ticket) {
                            if (!empty($ticket['ticket_name'])) {
                                $isExisting = !empty($ticket['id']);
                                $tierClass = $isExisting ? 'existing-ticket' : 'new-ticket';
                                $badgeClass = $isExisting ? 'existing' : 'new';
                                $badgeText = $isExisting ? 'Existing' : 'New';

                                echo '<div class="ticket-tier mb-3 p-3 border rounded ' . $tierClass . '" data-ticket-id="' . ($ticket['id'] ?? '') . '">';
                                echo '<div class="ticket-badge ' . $badgeClass . '">' . $badgeText . '</div>';

                                if ($isExisting) {
                                    echo '<input type="hidden" name="tickets[' . $index . '][id]" value="' . $ticket['id'] . '">';
                                }

                                echo '<div class="row g-3">';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Ticket Name *</label>';
                                echo '<input type="text" name="tickets[' . $index . '][ticket_name]" class="form-control" placeholder="e.g., General" value="' . htmlspecialchars($ticket['ticket_name']) . '">';
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Price (₦) *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][price]" class="form-control" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket['price']) . '">';
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Charges (₦)</label>';
                                echo '<input type="number" name="tickets[' . $index . '][charges]" class="form-control" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket['charges'] ?? '') . '">';
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Quantity *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][quantity]" class="form-control" placeholder="100" min="1" value="' . htmlspecialchars($ticket['quantity']) . '">';
                                echo '</div>';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Actions</label>';
                                echo '<div class="tier-controls d-flex gap-2">';
                                if ($isExisting) {
                                    echo '<button type="button" class="btn btn-outline-danger btn-sm delete-existing-ticket" data-ticket-id="' . $ticket['id'] . '">';
                                    echo '<i class="bi bi-trash"></i> Delete';
                                    echo '</button>';
                                } else {
                                    echo '<button type="button" class="btn btn-outline-danger btn-sm remove-tier">';
                                    echo '<i class="bi bi-trash"></i> Remove';
                                    echo '</button>';
                                }
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="col-12">';
                                echo '<label class="form-label">Ticket Description</label>';
                                echo '<textarea class="form-control" name="tickets[' . $index . '][description]" rows="2" placeholder="What\'s included in this ticket?">' . htmlspecialchars($ticket['description'] ?? '') . '</textarea>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                $ticketIndex = $index + 1;
                            }
                        }
                    } else {
                        // No form errors, show existing tickets from database
                        foreach ($existingTickets as $index => $ticket) {
                            echo '<div class="ticket-tier mb-3 p-3 border rounded existing-ticket" data-ticket-id="' . $ticket->id . '">';
                            echo '<div class="ticket-badge existing">Existing</div>';
                            echo '<input type="hidden" name="tickets[' . $index . '][id]" value="' . $ticket->id . '">';

                            echo '<div class="row g-3">';
                            echo '<div class="col-md-3">';
                            echo '<label class="form-label">Ticket Name *</label>';
                            echo '<input type="text" name="tickets[' . $index . '][ticket_name]" class="form-control" placeholder="e.g., General" value="' . htmlspecialchars($ticket->ticket_name) . '">';
                            echo '</div>';
                            echo '<div class="col-md-2">';
                            echo '<label class="form-label">Price (₦) *</label>';
                            echo '<input type="number" name="tickets[' . $index . '][price]" class="form-control" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket->price) . '">';
                            echo '</div>';
                            echo '<div class="col-md-2">';
                            echo '<label class="form-label">Charges (₦)</label>';
                            echo '<input type="number" name="tickets[' . $index . '][charges]" class="form-control" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket->charges ?? '') . '">';
                            echo '</div>';
                            echo '<div class="col-md-2">';
                            echo '<label class="form-label">Quantity *</label>';
                            echo '<input type="number" name="tickets[' . $index . '][quantity]" class="form-control" placeholder="100" min="1" value="' . htmlspecialchars($ticket->quantity) . '">';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<label class="form-label">Actions</label>';
                            echo '<div class="tier-controls d-flex gap-2">';
                            echo '<button type="button" class="btn btn-outline-danger btn-sm delete-existing-ticket" data-ticket-id="' . $ticket->id . '">';
                            echo '<i class="bi bi-trash"></i> Delete';
                            echo '</button>';
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="col-12">';
                            echo '<label class="form-label">Ticket Description</label>';
                            echo '<textarea class="form-control" name="tickets[' . $index . '][description]" rows="2" placeholder="What\'s included in this ticket?">' . htmlspecialchars($ticket->description ?? '') . '</textarea>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        $ticketIndex = count($existingTickets);
                    }

                    // If no tickets exist, create a default one
                    if ($ticketIndex === 0) {
                        echo '<div class="ticket-tier mb-3 p-3 border rounded new-ticket">';
                        echo '<div class="ticket-badge new">New</div>';
                        echo '<div class="row g-3">';
                        echo '<div class="col-md-3">';
                        echo '<label class="form-label">Ticket Name *</label>';
                        echo '<input type="text" name="tickets[0][ticket_name]" class="form-control" placeholder="e.g., General">';
                        echo '</div>';
                        echo '<div class="col-md-2">';
                        echo '<label class="form-label">Price (₦) *</label>';
                        echo '<input type="number" name="tickets[0][price]" class="form-control" placeholder="0" min="0" step="0.01">';
                        echo '</div>';
                        echo '<div class="col-md-2">';
                        echo '<label class="form-label">Charges (₦)</label>';
                        echo '<input type="number" name="tickets[0][charges]" class="form-control" placeholder="0" min="0" step="0.01">';
                        echo '</div>';
                        echo '<div class="col-md-2">';
                        echo '<label class="form-label">Quantity *</label>';
                        echo '<input type="number" name="tickets[0][quantity]" class="form-control" placeholder="100" min="1">';
                        echo '</div>';
                        echo '<div class="col-md-3">';
                        echo '<label class="form-label">Actions</label>';
                        echo '<div class="tier-controls">';
                        echo '<button type="button" class="btn btn-outline-danger btn-sm remove-tier" disabled>';
                        echo '<i class="bi bi-trash"></i> Remove';
                        echo '</button>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="col-12">';
                        echo '<label class="form-label">Ticket Description</label>';
                        echo '<textarea class="form-control" name="tickets[0][description]" rows="2" placeholder="What\'s included in this ticket?"></textarea>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        $ticketIndex = 1;
                    }
                    ?>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-pulse">
                    <i class="bi bi-check-circle me-2"></i>Update Event
                </button>
                <a href="/admin/events/manage" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Events
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTicketModal" tabindex="-1" aria-labelledby="deleteTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-white">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTicketModalLabel">Delete Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this ticket? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTicket">Delete Ticket</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image preview functionality
    const eventImageUpload = document.getElementById('eventImageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    eventImageUpload.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                imagePreview.src = reader.result;
                imagePreviewContainer.style.display = 'block';
            });
            reader.readAsDataURL(file);
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    });

    // Ticket management variables
    const addTicketTier = document.getElementById('addTicketTier');
    const ticketTiers = document.getElementById('ticketTiers');
    const ticketsToDeleteInput = document.getElementById('ticketsToDelete');
    let ticketIndex = <?= $ticketIndex ?>;
    let ticketsToDelete = [];

    // Add new ticket tier
    addTicketTier.addEventListener('click', () => {
        const tier = document.createElement('div');
        tier.classList.add('ticket-tier', 'mb-3', 'p-3', 'border', 'rounded', 'new-ticket');
        tier.innerHTML = `
        <div class="ticket-badge new">New</div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Ticket Name *</label>
                <input type="text" name="tickets[${ticketIndex}][ticket_name]" class="form-control" placeholder="e.g., VIP">
            </div>
            <div class="col-md-2">
                <label class="form-label">Price (₦) *</label>
                <input type="number" name="tickets[${ticketIndex}][price]" class="form-control" placeholder="0" min="0" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Charges (₦)</label>
                <input type="number" name="tickets[${ticketIndex}][charges]" class="form-control" placeholder="0" min="0" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity *</label>
                <input type="number" name="tickets[${ticketIndex}][quantity]" class="form-control" placeholder="50" min="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div class="tier-controls">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-tier">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Ticket Description</label>
                <textarea class="form-control" name="tickets[${ticketIndex}][description]" rows="2" placeholder="What's included in this ticket?"></textarea>
            </div>
        </div>
    `;
        ticketTiers.appendChild(tier);
        ticketIndex++;

        // Update remove button states
        updateRemoveButtonStates();

        // Add event listener to the new remove button
        tier.querySelector('.remove-tier').addEventListener('click', function() {
            tier.remove();
            updateRemoveButtonStates();
        });
    });

    // Handle existing ticket deletion
    let ticketToDelete = null;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-existing-ticket') || e.target.closest('.delete-existing-ticket')) {
            const button = e.target.classList.contains('delete-existing-ticket') ? e.target : e.target.closest('.delete-existing-ticket');
            ticketToDelete = {
                id: button.getAttribute('data-ticket-id'),
                element: button.closest('.ticket-tier')
            };

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('deleteTicketModal'));
            modal.show();
        }
    });

    // Confirm ticket deletion
    document.getElementById('confirmDeleteTicket').addEventListener('click', function() {
        if (ticketToDelete) {
            // Add ticket ID to deletion list
            ticketsToDelete.push(ticketToDelete.id);
            ticketsToDeleteInput.value = ticketsToDelete.join(',');

            // Mark ticket tier for deletion visually
            const ticketTier = ticketToDelete.element;
            ticketTier.classList.remove('existing-ticket');
            ticketTier.classList.add('marked-for-deletion');

            // Update badge
            const badge = ticketTier.querySelector('.ticket-badge');
            badge.textContent = 'To Delete';
            badge.classList.remove('existing');
            badge.classList.add('delete');

            // Disable all inputs in this tier
            const inputs = ticketTier.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.disabled = true;
            });

            // Replace delete button with undo button
            const deleteButton = ticketTier.querySelector('.delete-existing-ticket');
            deleteButton.outerHTML = `
                <button type="button" class="btn btn-outline-success btn-sm undo-delete-ticket" data-ticket-id="${ticketToDelete.id}">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo
                </button>
            `;

            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteTicketModal'));
            modal.hide();

            ticketToDelete = null;
        }
    });

    // Handle undo deletion
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('undo-delete-ticket') || e.target.closest('.undo-delete-ticket')) {
            const button = e.target.classList.contains('undo-delete-ticket') ? e.target : e.target.closest('.undo-delete-ticket');
            const ticketId = button.getAttribute('data-ticket-id');
            const ticketTier = button.closest('.ticket-tier');

            // Remove from deletion list
            ticketsToDelete = ticketsToDelete.filter(id => id !== ticketId);
            ticketsToDeleteInput.value = ticketsToDelete.join(',');

            // Restore visual state
            ticketTier.classList.remove('marked-for-deletion');
            ticketTier.classList.add('existing-ticket');

            // Update badge
            const badge = ticketTier.querySelector('.ticket-badge');
            badge.textContent = 'Existing';
            badge.classList.remove('delete');
            badge.classList.add('existing');

            // Re-enable inputs
            const inputs = ticketTier.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.disabled = false;
            });

            // Replace undo button with delete button
            button.outerHTML = `
                <button type="button" class="btn btn-outline-danger btn-sm delete-existing-ticket" data-ticket-id="${ticketId}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            `;
        }
    });

    // Handle remove tier for new tickets
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-tier') || e.target.closest('.remove-tier')) {
            const button = e.target.classList.contains('remove-tier') ? e.target : e.target.closest('.remove-tier');
            const ticketTier = button.closest('.ticket-tier');
            ticketTier.remove();
            updateRemoveButtonStates();
        }
    });

    // Update remove button states
    function updateRemoveButtonStates() {
        const allTiers = document.querySelectorAll('.ticket-tier:not(.marked-for-deletion)');
        const removeTierButtons = document.querySelectorAll('.remove-tier');

        // Disable remove buttons if only one active tier remains
        removeTierButtons.forEach(button => {
            button.disabled = allTiers.length <= 1;
        });
    }

    // Initial state update
    updateRemoveButtonStates();

    // Form validation
    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        const activeTiers = document.querySelectorAll('.ticket-tier:not(.marked-for-deletion)');
        if (activeTiers.length === 0) {
            e.preventDefault();
            alert('Event must have at least one ticket tier.');
        }
    });
</script>
@endsection