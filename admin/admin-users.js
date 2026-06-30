/**
 * Admin Users — UI interaction script.
 *
 * In your PHP template, the user list, stats, pagination, and filtering
 * are all rendered server-side. This script only handles:
 *
 * 1. Suspend / Reactivate confirmation modals.
 * 2. Client-side instant search filtering of table rows & mobile cards.
 */
(function () {
  'use strict';

  // ─── Modal Logic ────────────────────────────────────────

  var suspendModal = document.getElementById('suspend-modal');
  var suspendUserName = document.getElementById('suspend-user-name');
  var suspendUserId = document.getElementById('suspend-user-id');
  var suspendCancelBtn = document.getElementById('suspend-cancel-btn');

  var reactivateModal = document.getElementById('reactivate-modal');
  var reactivateUserName = document.getElementById('reactivate-user-name');
  var reactivateUserId = document.getElementById('reactivate-user-id');
  var reactivateCancelBtn = document.getElementById('reactivate-cancel-btn');

  // Delegate suspend button clicks
  document.addEventListener('click', function (e) {
    var suspendBtn = e.target.closest('.au-suspend-btn');
    if (suspendBtn) {
      e.preventDefault();
      var userId = suspendBtn.getAttribute('data-user-id') || '';
      var userName = suspendBtn.getAttribute('data-user-name') || 'this user';
      if (suspendUserName) suspendUserName.textContent = userName;
      if (suspendUserId) suspendUserId.value = userId;
      if (suspendModal) suspendModal.classList.remove('hidden');
      return;
    }

    var reactivateBtn = e.target.closest('.au-reactivate-btn');
    if (reactivateBtn) {
      e.preventDefault();
      var rUserId = reactivateBtn.getAttribute('data-user-id') || '';
      var rUserName = reactivateBtn.getAttribute('data-user-name') || 'this user';
      if (reactivateUserName) reactivateUserName.textContent = rUserName;
      if (reactivateUserId) reactivateUserId.value = rUserId;
      if (reactivateModal) reactivateModal.classList.remove('hidden');
      return;
    }
  });

  // Cancel buttons
  if (suspendCancelBtn) {
    suspendCancelBtn.addEventListener('click', function () {
      if (suspendModal) suspendModal.classList.add('hidden');
    });
  }
  if (reactivateCancelBtn) {
    reactivateCancelBtn.addEventListener('click', function () {
      if (reactivateModal) reactivateModal.classList.add('hidden');
    });
  }

  // Close modals on overlay click
  [suspendModal, reactivateModal].forEach(function (modal) {
    if (!modal) return;
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (suspendModal && !suspendModal.classList.contains('hidden')) {
        suspendModal.classList.add('hidden');
      }
      if (reactivateModal && !reactivateModal.classList.contains('hidden')) {
        reactivateModal.classList.add('hidden');
      }
    }
  });

  // ─── Client-Side Instant Search ─────────────────────────
  // This provides instant filtering while the user types.
  // The form also submits to the server via GET for full server-side search.

  var searchInput = document.getElementById('users-search');
  var tableBody = document.getElementById('users-tbody');
  var cardsList = document.getElementById('users-cards');
  var emptyState = document.getElementById('users-empty');

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var query = (searchInput.value || '').toLowerCase().trim();

      // Filter table rows
      if (tableBody) {
        var rows = tableBody.querySelectorAll('tr');
        var visibleRows = 0;

        rows.forEach(function (row) {
          var name = (row.querySelector('.au-user-name') || {}).textContent || '';
          var email = (row.querySelector('.au-email-cell') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1;

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
        cards.forEach(function (card) {
          var name = (card.querySelector('.au-user-name') || {}).textContent || '';
          var email = (card.querySelector('.au-card-email') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
        });
      }
    });
  }

})();