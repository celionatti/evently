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
        display: none;
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
</style>
@endsection

@section('content')
<!-- Create Event Section -->
<div id="create-event-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Create New Event</h1>
        <p class="text-secondary">Fill in the details below to create your event.</p>
    </div>

    <div class="dashboard-card">
        <form action="" method="post" enctype="multipart/form-data" id="createEventForm">
            <!-- {{ csrf_field() }} -->

            <div class="row g-4">
                <div class="col-md-8">
                    <label for="event_title" class="form-label">Event Title *</label>
                    <input type="text" name="event_title" id="event_title" class="form-control <?= has_error('event_title') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event title" value="<?= old('event_title') ?>">
                    <?php if (has_error('event_title')): ?>
                        <div class="invalid-feedback"><?= get_error('event_title') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select <?= has_error('category') ? 'is-invalid' : '' ?>">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>" <?= old('category') == $category->id ? 'selected' : '' ?>>
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
                        name="description" id="description" rows="4" placeholder="Describe your event..."><?= old('description') ?></textarea>
                    <?php if (has_error('description')): ?>
                        <div class="invalid-feedback"><?= get_error('description') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label for="event_link" class="form-label">Event Link</label>
                    <input type="url" name="event_link" id="event_link" class="form-control <?= has_error('event_link') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event link" value="<?= old('event_link') ?>">
                    <?php if (has_error('event_link')): ?>
                        <div class="invalid-feedback"><?= get_error('event_link') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="tags" class="form-label">Event Tags</label>
                    <input type="text" name="tags" id="tags" class="form-control <?= has_error('tags') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event link" value="<?= old('tags') ?>">
                    <?php if (has_error('tags')): ?>
                        <div class="invalid-feedback"><?= get_error('tags') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="venue" class="form-label">Venue *</label>
                    <input type="text" name="venue" id="venue" class="form-control <?= has_error('venue') ? 'is-invalid' : '' ?>"
                        placeholder="Event venue" value="<?= old('venue') ?>">
                    <?php if (has_error('venue')): ?>
                        <div class="invalid-feedback"><?= get_error('venue') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City *</label>
                    <select name="city" id="city" class="form-select <?= has_error('city') ? 'is-invalid' : '' ?>">
                        <option value="">Select City</option>
                        <option value="other" <?= old('city') === 'other' ? 'selected' : '' ?>>Other</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= strtolower(str_replace(" ", "_", $city['name'])) ?>" <?= old('city') == $city['name'] ? 'selected' : '' ?>>
                                <?= $city['name'] . ' - ' . $city['state'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (has_error('city')): ?>
                        <div class="invalid-feedback"><?= get_error('city') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="event_date" class="form-label">Event Date *</label>
                    <input type="date" name="event_date" id="event_date" class="form-control <?= has_error('event_date') ? 'is-invalid' : '' ?>"
                        value="<?= old('event_date') ?>">
                    <?php if (has_error('event_date')): ?>
                        <div class="invalid-feedback"><?= get_error('event_date') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="start_time" class="form-label">Start Time *</label>
                    <input type="time" id="start_time" name="start_time" class="form-control <?= has_error('start_time') ? 'is-invalid' : '' ?>"
                        value="<?= old('start_time') ?>">
                    <?php if (has_error('start_time')): ?>
                        <div class="invalid-feedback"><?= get_error('start_time') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= old('end_date') ?>">
                </div>
                <div class="col-md-6">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" value="<?= old('end_time') ?>">
                </div>
                <div class="col-12">
                    <label for="eventImageUpload" class="form-label">Event Image</label>
                    <input type="file" name="event_image" id="eventImageUpload" class="form-control <?= has_error('event_image') ? 'is-invalid' : '' ?>" accept="image/*">
                    <small class="form-text text-secondary">Upload a high-quality image (recommended: 1200x630px)</small>
                    <?php if (has_error('event_image')): ?>
                        <div class="invalid-feedback"><?= get_error('event_image') ?></div>
                    <?php endif; ?>

                    <div class="image-preview-container mt-3" id="imagePreviewContainer">
                        <div class="mb-2">Image Preview:</div>
                        <img src="#" alt="Image Preview" class="image-preview" id="imagePreview">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" id="phone" name="phone" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact phone" value="<?= old('phone') ?>">
                    <?php if (has_error('phone')): ?>
                        <div class="invalid-feedback"><?= get_error('phone') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="mail" class="form-label">Mail *</label>
                    <input type="email" name="mail" id="mail" class="form-control <?= has_error('mail') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact mail" value="<?= old('mail') ?>">
                    <?php if (has_error('mail')): ?>
                        <div class="invalid-feedback"><?= get_error('mail') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12">
                    <label for="social" class="form-label">Social *</label>
                    <input type="url" name="social" id="social" class="form-control <?= has_error('social') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event social link" value="<?= old('social') ?>">
                    <?php if (has_error('social')): ?>
                        <div class="invalid-feedback"><?= get_error('social') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="ticket_sales" class="form-label">Ticket Sales *</label>
                    <select name="ticket_sales" id="ticket_sales" class="form-select <?= has_error('ticket_sales') ? 'is-invalid' : '' ?>">
                        <option value="">Select Option</option>
                        <option value="close" <?= old('ticket_sales') === 'close' ? 'selected' : '' ?>>Close</option>
                        <option value="open" <?= old('ticket_sales') === 'open' ? 'selected' : '' ?>>Open</option>
                    </select>
                    <?php if (has_error('ticket_sales')): ?>
                        <div class="invalid-feedback"><?= get_error('ticket_sales') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Event Status *</label>
                    <select name="status" id="status" class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>">
                        <option value="">Select Option</option>
                        <option value="disable" <?= old('status') === 'disable' ? 'selected' : '' ?>>Disable</option>
                        <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
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
                        <i class="bi bi-plus-circle me-1"></i>Add Ticket
                    </button>
                </div>

                <div id="ticketTiers">
                    <?php
                    $ticketIndex = 0;
                    $oldTickets = old('tickets', []);

                    if (!empty($oldTickets)) {
                        foreach ($oldTickets as $index => $ticket) {
                            if (!empty($ticket['ticket_name'])) {
                                $hasTicketNameError = has_error("tickets.{$index}.ticket_name");
                                $hasPriceError = has_error("tickets.{$index}.price");
                                $hasQuantityError = has_error("tickets.{$index}.quantity");
                                $hasChargesError = has_error("tickets.{$index}.charges");

                                echo '<div class="ticket-tier mb-3 p-3 border rounded">';
                                echo '<div class="row g-3">';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Ticket Name *</label>';
                                echo '<input type="text" name="tickets[' . $index . '][ticket_name]" class="form-control ' . ($hasTicketNameError ? 'is-invalid' : '') . '" placeholder="e.g., General" value="' . htmlspecialchars($ticket['ticket_name']) . '">';
                                if ($hasTicketNameError) {
                                    echo '<div class="invalid-feedback">' . get_error("tickets.{$index}.ticket_name") . '</div>';
                                }
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Price (₦) *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][price]" class="form-control ' . ($hasPriceError ? 'is-invalid' : '') . '" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket['price']) . '">';
                                if ($hasPriceError) {
                                    echo '<div class="invalid-feedback">' . get_error("tickets.{$index}.price") . '</div>';
                                }
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Charges (₦)</label>';
                                echo '<input type="number" name="tickets[' . $index . '][charges]" class="form-control ' . ($hasChargesError ? 'is-invalid' : '') . '" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket['charges'] ?? '') . '">';
                                if ($hasChargesError) {
                                    echo '<div class="invalid-feedback">' . get_error("tickets.{$index}.charges") . '</div>';
                                }
                                echo '</div>';
                                echo '<div class="col-md-2">';
                                echo '<label class="form-label">Quantity *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][quantity]" class="form-control ' . ($hasQuantityError ? 'is-invalid' : '') . '" placeholder="100" min="1" value="' . htmlspecialchars($ticket['quantity']) . '">';
                                if ($hasQuantityError) {
                                    echo '<div class="invalid-feedback">' . get_error("tickets.{$index}.quantity") . '</div>';
                                }
                                echo '</div>';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Actions</label>';
                                echo '<div class="tier-controls">';
                                echo '<button type="button" class="btn btn-outline-danger btn-sm remove-tier">';
                                echo '<i class="bi bi-trash"></i>';
                                echo '</button>';
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
                    }

                    if ($ticketIndex === 0) {
                        echo '<div class="ticket-tier mb-3 p-3 border rounded">';
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
                        echo '<i class="bi bi-trash"></i>';
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
                    <i class="bi bi-check-circle me-2"></i>Create Event
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

    // Add Ticket Tier dynamically
    const addTicketTier = document.getElementById('addTicketTier');
    const ticketTiers = document.getElementById('ticketTiers');
    let ticketIndex = <?= $ticketIndex ?>;

    addTicketTier.addEventListener('click', () => {
        const tier = document.createElement('div');
        tier.classList.add('ticket-tier', 'mb-3', 'p-3', 'border', 'rounded');
        tier.innerHTML = `
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Ticket Name *</label>
                <input type="text" name="tickets[${ticketIndex}][ticket_name]" class="form-control" placeholder="e.g., VIP" >
            </div>
            <div class="col-md-2">
                <label class="form-label">Price (₦) *</label>
                <input type="number" name="tickets[${ticketIndex}][price]" class="form-control" placeholder="0" min="0" step="0.01" >
            </div>
            <div class="col-md-2">
                <label class="form-label">Charges (₦)</label>
                <input type="number" name="tickets[${ticketIndex}][charges]" class="form-control" placeholder="0" min="0" step="0.01" >
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity *</label>
                <input type="number" name="tickets[${ticketIndex}][quantity]" class="form-control" placeholder="50" min="1" >
            </div>
            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div class="tier-controls">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-tier">
                        <i class="bi bi-trash"></i>
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

        // Enable remove button for all tickets if there's more than one
        document.querySelectorAll('.remove-tier').forEach(button => {
            button.disabled = document.querySelectorAll('.ticket-tier').length <= 1;
        });

        // Add event listener to the new remove button
        tier.querySelector('.remove-tier').addEventListener('click', function() {
            if (document.querySelectorAll('.ticket-tier').length > 1) {
                tier.remove();

                // Disable remove button if only one ticket remains
                document.querySelectorAll('.remove-tier').forEach(button => {
                    button.disabled = document.querySelectorAll('.ticket-tier').length <= 1;
                });
            }
        });
    });

    // Add event listeners to existing remove buttons
    document.querySelectorAll('.remove-tier').forEach(button => {
        button.addEventListener('click', function() {
            if (document.querySelectorAll('.ticket-tier').length > 1) {
                this.closest('.ticket-tier').remove();

                // Disable remove button if only one ticket remains
                document.querySelectorAll('.remove-tier').forEach(btn => {
                    btn.disabled = document.querySelectorAll('.ticket-tier').length <= 1;
                });
            }
        });
    });
</script>
@endsection