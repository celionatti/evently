/**
 * Host Ticket Detail — UI interactions only.
 * All ticket data is rendered server-side by PHP.
 * This script handles: toast notifications, confirmation modals,
 * and wiring the action buttons (resend / refund / flag).
 *
 * In your PHP integration, update the action URLs in the
 * data-action-url attributes or the fetch endpoints below.
 */
(function(){
  'use strict';

  // ── Toast notifications ──────────────────────────────
  function showToast(message, type, timeout){
    type = type || 'info';
    timeout = timeout || 3000;
    var tc = document.getElementById('toast-container');
    if(!tc) return;
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = message;
    tc.appendChild(t);
    setTimeout(function(){ t.classList.add('visible'); }, 10);
    setTimeout(function(){
      t.classList.remove('visible');
      setTimeout(function(){ t.remove(); }, 300);
    }, timeout);
  }

  // ── Confirmation modal ───────────────────────────────
  var backdrop   = document.getElementById('modal-backdrop');
  var confirmMsg = document.getElementById('confirm-message');
  var confirmYes = document.getElementById('confirm-yes');
  var confirmNo  = document.getElementById('confirm-no');
  var pendingAction = null;

  function openConfirm(message, onConfirm){
    if(!backdrop) return onConfirm();
    confirmMsg.textContent = message;
    pendingAction = onConfirm;
    backdrop.classList.remove('hidden');
  }

  function closeModal(){
    if(backdrop) backdrop.classList.add('hidden');
    pendingAction = null;
  }

  if(confirmYes){
    confirmYes.addEventListener('click', function(){
      closeModal();
      if(typeof pendingAction === 'function') pendingAction();
    });
  }
  if(confirmNo){
    confirmNo.addEventListener('click', closeModal);
  }
  // Close modal on backdrop click
  if(backdrop){
    backdrop.addEventListener('click', function(e){
      if(e.target === backdrop) closeModal();
    });
  }

  // ── Action buttons ───────────────────────────────────
  // These buttons trigger lightweight UI feedback.
  // For actual server operations, either:
  //   a) Use PHP form submissions (wrap each in a <form>), or
  //   b) Use fetch() calls below pointed at your PHP endpoints.

  var resendBtn = document.getElementById('action-resend');
  var refundBtn = document.getElementById('action-refund');
  var flagBtn   = document.getElementById('action-flag');

  if(resendBtn){
    resendBtn.addEventListener('click', function(e){
      e.preventDefault();
      // PHP integration: POST to your resend endpoint
      // Example: fetch('actions/resend-ticket.php', { method:'POST', body: formData })
      showToast('Ticket resent successfully.', 'success');
    });
  }

  if(flagBtn){
    flagBtn.addEventListener('click', function(e){
      e.preventDefault();
      // PHP integration: POST to your flag endpoint
      showToast('Ticket has been flagged for review.', 'info');
    });
  }

  if(refundBtn){
    refundBtn.addEventListener('click', function(e){
      e.preventDefault();
      openConfirm('Refund this ticket? This action cannot be undone.', function(){
        // PHP integration: POST to your refund endpoint
        // Example:
        // fetch('actions/refund-ticket.php', {
        //   method: 'POST',
        //   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        //   body: 'ticket_id=' + encodeURIComponent(ticketId)
        // })
        // .then(res => res.json())
        // .then(data => { ... update UI or redirect ... });

        // Update status badge visually after refund
        var statusEl = document.getElementById('ticket-status');
        if(statusEl){
          statusEl.className = 'status-badge refunded';
          statusEl.textContent = 'REFUNDED';
        }
        var detailEl = document.getElementById('status-detail');
        if(detailEl) detailEl.textContent = 'Refund issued by host';

        showToast('Ticket refunded successfully.', 'success');
      });
    });
  }
})();