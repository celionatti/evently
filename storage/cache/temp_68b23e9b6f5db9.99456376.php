<?php
foreach($cities as $city) {
    var_dump($city);
}
die;
?>

<?php $this->start('content'); ?>
<!-- Create Event Section -->
<div id="create-event-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Create New Event</h1>
        <p class="text-secondary">Fill in the details below to create your event.</p>
    </div>

    <div class="dashboard-card">
        <form action="/admin/events/insert" method="post" enctype="multipart/form-data" id="createEventForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="event_title" class="form-control <?= has_error('event_title') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event title" value="<?= old('event_title') ?>" required>
                    <?php if (has_error('event_title')): ?>
                        <div class="invalid-feedback"><?= get_error('event_title') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select <?= has_error('category') ? 'is-invalid' : '' ?>" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>" <?= old('category_id') == $category->id ? 'selected' : '' ?>>
                                <?= $category->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (has_error('category')): ?>
                        <div class="invalid-feedback"><?= get_error('category') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>"
                        name="description" rows="4" placeholder="Describe your event..." required><?= old('description') ?></textarea>
                    <?php if (has_error('description')): ?>
                        <div class="invalid-feedback"><?= get_error('description') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Venue *</label>
                    <input type="text" name="venue" class="form-control <?= has_error('venue') ? 'is-invalid' : '' ?>"
                        placeholder="Event venue" value="<?= old('venue') ?>" required>
                    <?php if (has_error('venue')): ?>
                        <div class="invalid-feedback"><?= get_error('venue') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">City *</label>
                    <select name="city" class="form-select <?= has_error('city') ? 'is-invalid' : '' ?>" required>
                        <option value="">Select City</option>
                        <option value="other" <?= old('city') === 'other' ? 'selected' : '' ?>>Other</option>
                        <?php foreach ($cities as $code => $city): ?>
                            <option value="<?= $city[$code] ?>" <?= old('city') == $city['name'] ? 'selected' : '' ?>>
                                <?= $city['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (has_error('city')): ?>
                        <div class="invalid-feedback"><?= get_error('city') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Event Date *</label>
                    <input type="date" name="event_date" class="form-control <?= has_error('event_date') ? 'is-invalid' : '' ?>"
                        value="<?= old('event_date') ?>" required>
                    <?php if (has_error('event_date')): ?>
                        <div class="invalid-feedback"><?= get_error('event_date') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control <?= has_error('start_time') ? 'is-invalid' : '' ?>"
                        value="<?= old('start_time') ?>" required>
                    <?php if (has_error('start_time')): ?>
                        <div class="invalid-feedback"><?= get_error('start_time') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= old('end_date') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="<?= old('end_time') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Event Image</label>
                    <input type="file" name="event_image" class="form-control" accept="image/*">
                    <small class="form-text text-secondary">Upload a high-quality image (recommended: 1200x630px)</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact phone" value="<?= old('phone') ?>" required>
                    <?php if (has_error('phone')): ?>
                        <div class="invalid-feedback"><?= get_error('phone') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mail *</label>
                    <input type="email" name="mail" class="form-control <?= has_error('mail') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your contact mail" value="<?= old('mail') ?>" required>
                    <?php if (has_error('mail')): ?>
                        <div class="invalid-feedback"><?= get_error('mail') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Social *</label>
                    <input type="url" name="social" class="form-control <?= has_error('social') ? 'is-invalid' : '' ?>"
                        placeholder="Enter your event social link" value="<?= old('social') ?>" required>
                    <?php if (has_error('social')): ?>
                        <div class="invalid-feedback"><?= get_error('social') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ticket Sales *</label>
                    <select name="ticket_sales" class="form-select <?= has_error('ticket_sales') ? 'is-invalid' : '' ?>" required>
                        <option value="">Select Option</option>
                        <option value="close" <?= old('ticket_sales') === 'close' ? 'selected' : '' ?>>Close</option>
                        <option value="open" <?= old('ticket_sales') === 'open' ? 'selected' : '' ?>>Open</option>
                    </select>
                    <?php if (has_error('ticket_sales')): ?>
                        <div class="invalid-feedback"><?= get_error('ticket_sales') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Event Status *</label>
                    <select name="status" class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>" required>
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
                                echo '<div class="ticket-tier mb-3 p-3 border rounded">';
                                echo '<div class="row g-3">';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Ticket Name *</label>';
                                echo '<input type="text" name="tickets[' . $index . '][ticket_name]" class="form-control" placeholder="e.g., General" value="' . htmlspecialchars($ticket['ticket_name']) . '" required>';
                                echo '</div>';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Price (₦) *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][price]" class="form-control" placeholder="0" min="0" step="0.01" value="' . htmlspecialchars($ticket['price']) . '" required>';
                                echo '</div>';
                                echo '<div class="col-md-3">';
                                echo '<label class="form-label">Quantity *</label>';
                                echo '<input type="number" name="tickets[' . $index . '][quantity]" class="form-control" placeholder="100" min="1" value="' . htmlspecialchars($ticket['quantity']) . '" required>';
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
                        echo '<input type="text" name="tickets[0][ticket_name]" class="form-control" placeholder="e.g., General" required>';
                        echo '</div>';
                        echo '<div class="col-md-3">';
                        echo '<label class="form-label">Price (₦) *</label>';
                        echo '<input type="number" name="tickets[0][price]" class="form-control" placeholder="0" min="0" step="0.01" required>';
                        echo '</div>';
                        echo '<div class="col-md-3">';
                        echo '<label class="form-label">Quantity *</label>';
                        echo '<input type="number" name="tickets[0][quantity]" class="form-control" placeholder="100" min="1" required>';
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
                <button type="submit" class="btn btn-pulse" name="action" value="publish">
                    <i class="bi bi-check-circle me-2"></i>Create Event
                </button>
                <button type="submit" class="btn btn-ghost" name="action" value="draft">
                    <i class="bi bi-save me-2"></i>Save as Draft
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </button>
            </div>
        </form>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
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
                    <input type="text" name="tickets[${ticketIndex}][ticket_name]" class="form-control" placeholder="e.g., VIP" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price (₦) *</label>
                    <input type="number" name="tickets[${ticketIndex}][price]" class="form-control" placeholder="0" min="0" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="tickets[${ticketIndex}][quantity]" class="form-control" placeholder="50" min="1" required>
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
<?php $this->end(); ?>