<?php

?>

@section('content')
<!-- Create Event Section -->
<div id="create-event-section" class="content-section">
    <div class="mb-4">
        <h1 class="h2 mb-1">Create New Event</h1>
        <p class="text-secondary">Fill in the details below to create your event.</p>
    </div>

    <div class="dashboard-card">
        <form action="" method="post" enctype="multipart/form-data" id="createEventForm">
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="event_title" class="form-control" placeholder="Enter your event title" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="music">Music</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Describe your event..."
                        required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Venue *</label>
                    <input type="text" name="venue" class="form-control" placeholder="Event venue" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">City *</label>
                    <select name="city" class="form-select" required>
                        <option value="">Select City</option>
                        <option value="lagos">Lagos</option>
                        <option value="abuja">Abuja</option>
                        <option value="port-harcourt">Port Harcourt</option>
                        <option value="kano">Kano</option>
                        <option value="ibadan">Ibadan</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Event Date *</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Event Image</label>
                    <input type="file" name="event_image" class="form-control" accept="image/*">
                    <small class="form-text text-secondary">Upload a high-quality image (recommended:
                        1200x630px)</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter your contact phone" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mail *</label>
                    <input type="text" name="mail" class="form-control" placeholder="Enter your contact mail" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Social *</label>
                    <input type="text" name="social" class="form-control" placeholder="Enter your event social link" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ticket Sales *</label>
                    <select class="form-select" required>
                        <option value="">Select Option</option>
                        <option value="close">Close</option>
                        <option value="open">Open</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Event Status *</label>
                    <select class="form-select" required>
                        <option value="">Select Option</option>
                        <option value="disable">Disable</option>
                        <option value="active">Active</option>
                    </select>
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
                    <div class="ticket-tier">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Ticket Name *</label>
                                <input type="text" class="form-control" placeholder="e.g., General"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price (₦) *</label>
                                <input type="number" class="form-control" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Quantity *</label>
                                <input type="number" class="form-control" placeholder="100" min="1"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Actions</label>
                                <div class="tier-controls">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-tier"
                                        disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ticket Description</label>
                                <textarea class="form-control" rows="2"
                                    placeholder="What's included in this ticket?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-pulse">
                    <i class="bi bi-check-circle me-2"></i>Create Event
                </button>
                <button type="button" class="btn btn-ghost" id="saveDraft">
                    <i class="bi bi-save me-2"></i>Save as Draft
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
    // Add Ticket Tier dynamically
    const addTicketTier = document.getElementById('addTicketTier');
    const ticketTiers = document.getElementById('ticketTiers');

    addTicketTier.addEventListener('click', () => {
        const tier = document.createElement('div');
        tier.classList.add('ticket-tier');
        tier.innerHTML = `
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Ticket Name *</label>
            <input type="text" class="form-control" placeholder="e.g., VIP" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Price (₦) *</label>
            <input type="number" class="form-control" placeholder="0" min="0" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Quantity *</label>
            <input type="number" class="form-control" placeholder="50" min="1" required>
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
            <textarea class="form-control" rows="2" placeholder="What's included in this ticket?"></textarea>
          </div>
        </div>
      `;
        ticketTiers.appendChild(tier);

        tier.querySelector('.remove-tier').addEventListener('click', () => {
            tier.remove();
        });
    });
</script>
@endsection