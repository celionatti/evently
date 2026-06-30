/**
 * Admin Payouts — UI interaction script.
 * 
 * In your PHP template, host bank accounts, pending balances, metrics, 
 * and the payouts queue table are all rendered server-side.
 * 
 * This script handles:
 * 1. Process Payout confirmation modal overlay.
 * 2. Flag Account confirmation modal overlay.
 * 3. Resolve Hold (Unflag) confirmation modal overlay.
 * 4. Client-side instant search filtering of table rows & mobile cards.
 */
(function() {
  'use strict';

  // ─── Modal Elements ──────────────────────────────────────
  var processModal = document.getElementById('process-modal');
  var processPayoutAmount = document.getElementById('process-payout-amount');
  var processHostName = document.getElementById('process-host-name');
  var processPayoutId = document.getElementById('process-payout-id');
  var processCancelBtn = document.getElementById('process-cancel-btn');

  var flagModal = document.getElementById('flag-modal');
  var flagHostName = document.getElementById('flag-host-name');
  var flagPayoutId = document.getElementById('flag-payout-id');
  var flagCancelBtn = document.getElementById('flag-cancel-btn');

  var unflagModal = document.getElementById('unflag-modal');
  var unflagHostName = document.getElementById('unflag-host-name');
  var unflagPayoutId = document.getElementById('unflag-payout-id');
  var unflagCancelBtn = document.getElementById('unflag-cancel-btn');

  // Delegate action button clicks
  document.addEventListener('click', function(e) {
    var processBtn = e.target.closest('.au-process-btn');
    if (processBtn) {
      e.preventDefault();
      var payoutId = processBtn.getAttribute('data-payout-id') || '';
      var hostName = processBtn.getAttribute('data-host-name') || 'this host';
      var amountVal = processBtn.getAttribute('data-amount') || '$0.00';
      
      if (processPayoutAmount) processPayoutAmount.textContent = amountVal;
      if (processHostName) processHostName.textContent = hostName;
      if (processPayoutId) processPayoutId.value = payoutId;
      if (processModal) processModal.classList.remove('hidden');
      return;
    }

    var flagBtn = e.target.closest('.au-flag-btn');
    if (flagBtn) {
      e.preventDefault();
      var fPayoutId = flagBtn.getAttribute('data-payout-id') || '';
      var fHostName = flagBtn.getAttribute('data-host-name') || 'this host';
      
      if (flagHostName) flagHostName.textContent = fHostName;
      if (flagPayoutId) flagPayoutId.value = fPayoutId;
      if (flagModal) flagModal.classList.remove('hidden');
      return;
    }

    var unflagBtn = e.target.closest('.au-unflag-btn');
    if (unflagBtn) {
      e.preventDefault();
      var uPayoutId = unflagBtn.getAttribute('data-payout-id') || '';
      var uHostName = unflagBtn.getAttribute('data-host-name') || 'this host';

      if (unflagHostName) unflagHostName.textContent = uHostName;
      if (unflagPayoutId) unflagPayoutId.value = uPayoutId;
      if (unflagModal) unflagModal.classList.remove('hidden');
      return;
    }
  });

  // Cancel buttons
  if (processCancelBtn) {
    processCancelBtn.addEventListener('click', function() {
      if (processModal) processModal.classList.add('hidden');
    });
  }
  if (flagCancelBtn) {
    flagCancelBtn.addEventListener('click', function() {
      if (flagModal) flagModal.classList.add('hidden');
    });
  }
  if (unflagCancelBtn) {
    unflagCancelBtn.addEventListener('click', function() {
      if (unflagModal) unflagModal.classList.add('hidden');
    });
  }

  // Close modals on overlay clicks
  [processModal, flagModal, unflagModal].forEach(function(modal) {
    if (!modal) return;
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      [processModal, flagModal, unflagModal].forEach(function(modal) {
        if (modal && !modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
        }
      });
    }
  });

  // ─── Client-Side Instant Search ─────────────────────────
  var searchInput = document.getElementById('payouts-search');
  var tableBody = document.getElementById('payouts-tbody');
  var cardsList = document.getElementById('payouts-cards');
  var emptyState = document.getElementById('payouts-empty');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      var query = (searchInput.value || '').toLowerCase().trim();

      // Filter table rows
      if (tableBody) {
        var rows = tableBody.querySelectorAll('tr');
        var visibleRows = 0;

        rows.forEach(function(row) {
          var name = (row.querySelector('.au-user-name') || {}).textContent || '';
          var email = (row.querySelector('.au-card-email') || {}).textContent || '';
          var bankDetails = (row.querySelector('.au-date-cell') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1 ||
                      bankDetails.toLowerCase().indexOf(query) !== -1;

          row.style.display = match ? '' : 'none';
          if (match) visibleRows++;
        });

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
          var bank = (card.querySelector('.au-card-meta') || {}).textContent || '';
          var match = name.toLowerCase().indexOf(query) !== -1 ||
                      email.toLowerCase().indexOf(query) !== -1 ||
                      bank.toLowerCase().indexOf(query) !== -1;
          card.style.display = match ? '' : 'none';
        });
      }
    });
  }

})();