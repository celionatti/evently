/**
 * Admin Events — UI interaction script.
 * 
 * In your PHP template, the events list, stats, pagination, and filtering
 * are all rendered server-side. This script only handles:
 * 
 * 1. Delete event confirmation modal overlay.
 * 2. Client-side instant search filtering of table rows & mobile cards.
 */
(function() {
  'use strict';

  // ─── Modal Logic ────────────────────────────────────────
  var deleteModal = document.getElementById('delete-modal');
  var deleteEventName = document.getElementById('delete-event-name');
  var deleteEventId = document.getElementById('delete-event-id');
  var deleteCancelBtn = document.getElementById('delete-cancel-btn');

  // Delegate delete button clicks
  document.addEventListener('click', function(e) {
    var deleteBtn = e.target.closest('.au-delete-btn');
    if (deleteBtn) {
      e.preventDefault();
      var eventId = deleteBtn.getAttribute('data-event-id') || '';
      var eventName = deleteBtn.getAttribute('data-event-name') || 'this event';
      
      if (deleteEventName) deleteEventName.textContent = eventName;
      if (deleteEventId) deleteEventId.value = eventId;
      if (deleteModal) deleteModal.classList.remove('hidden');
    }
  });

  // Cancel button click
  if (deleteCancelBtn) {
    deleteCancelBtn.addEventListener('click', function() {
      if (deleteModal) deleteModal.classList.add('hidden');
    });
  }

  // Close modal on overlay click
  if (deleteModal) {
    deleteModal.addEventListener('click', function(e) {
      if (e.target === deleteModal) {
        deleteModal.classList.add('hidden');
      }
    });
  }

  // Close modal on Escape key press
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (deleteModal && !deleteModal.classList.contains('hidden')) {
        deleteModal.classList.add('hidden');
      }
    }
  });

  // ─── Client-Side Instant Search ─────────────────────────
  var searchInput = document.getElementById('events-search');
  var tableBody = document.getElementById('events-tbody');
  var cardsList = document.getElementById('events-cards');
  var emptyState = document.getElementById('events-empty');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      var query = (searchInput.value || '').toLowerCase().trim();

      // Filter table rows
      if (tableBody) {
        var rows = tableBody.querySelectorAll('tr');
        var visibleRows = 0;

        rows.forEach(function(row) {
          var name = (row.querySelector('.au-user-name') || {}).textContent || '';
          var email = (row.querySelector('.au-email-cell') || {}).textContent || '';
          var venue = (row.querySelector('.au-date-cell') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1 ||
                      venue.toLowerCase().indexOf(query) !== -1;

          row.style.display = match ? '' : 'none';
          if (match) visibleRows++;
        });

        // Toggle empty state
        if (emptyState) {
          emptyState.classList.toggle('hidden', visibleRows > 0 || query === '');
        }
      }

      // Filter mobile cards
      if (cardsList) {
        var cards = cardsList.querySelectorAll('.au-user-card');
        cards.forEach(function(card) {
          var name = (card.querySelector('.au-user-name') || {}).textContent || '';
          var email = (card.querySelector('.au-card-email') || {}).textContent || '';
          var venue = (card.querySelector('.au-card-meta') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1 ||
                      venue.toLowerCase().indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
        });
      }
    });
  }

})();