/**
 * Admin Teams — UI interaction script.
 * 
 * In your PHP template, staff additions, event assignments, metrics, 
 * and the registry list are all rendered server-side.
 * 
 * This script handles:
 * 1. Remove Staff confirmation modal overlay.
 * 2. Unassign Staff confirmation modal overlay.
 * 3. Light validation of forms before allowing server POST.
 */
(function() {
  'use strict';

  // ─── Modal Elements ──────────────────────────────────────
  var removeModal = document.getElementById('remove-modal');
  var removeMemberName = document.getElementById('remove-member-name');
  var removeMemberId = document.getElementById('remove-member-id');
  var removeCancelBtn = document.getElementById('remove-cancel-btn');

  var unassignModal = document.getElementById('unassign-modal');
  var unassignMemberName = document.getElementById('unassign-member-name');
  var unassignEventName = document.getElementById('unassign-event-name');
  var unassignMemberId = document.getElementById('unassign-member-id');
  var unassignCancelBtn = document.getElementById('unassign-cancel-btn');

  // Delegate action button clicks (Remove & Unassign)
  document.addEventListener('click', function(e) {
    var removeBtn = e.target.closest('.au-remove-staff-btn');
    if (removeBtn) {
      e.preventDefault();
      var memberId = removeBtn.getAttribute('data-member-id') || '';
      var memberName = removeBtn.getAttribute('data-member-name') || 'this staff member';
      
      if (removeMemberName) removeMemberName.textContent = memberName;
      if (removeMemberId) removeMemberId.value = memberId;
      if (removeModal) removeModal.classList.remove('hidden');
      return;
    }

    var unassignBtn = e.target.closest('.au-unassign-staff-btn');
    if (unassignBtn) {
      e.preventDefault();
      var uMemberId = unassignBtn.getAttribute('data-member-id') || '';
      var uMemberName = unassignBtn.getAttribute('data-member-name') || 'this staff member';
      var uEventName = unassignBtn.getAttribute('data-event-name') || 'the event';

      if (unassignMemberName) unassignMemberName.textContent = uMemberName;
      if (unassignEventName) unassignEventName.textContent = uEventName;
      if (unassignMemberId) unassignMemberId.value = uMemberId;
      if (unassignModal) unassignModal.classList.remove('hidden');
      return;
    }
  });

  // Cancel buttons
  if (removeCancelBtn) {
    removeCancelBtn.addEventListener('click', function() {
      if (removeModal) removeModal.classList.add('hidden');
    });
  }
  if (unassignCancelBtn) {
    unassignCancelBtn.addEventListener('click', function() {
      if (unassignModal) unassignModal.classList.add('hidden');
    });
  }

  // Close modals on overlay clicks
  [removeModal, unassignModal].forEach(function(modal) {
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
      if (removeModal && !removeModal.classList.contains('hidden')) {
        removeModal.classList.add('hidden');
      }
      if (unassignModal && !unassignModal.classList.contains('hidden')) {
        unassignModal.classList.add('hidden');
      }
    }
  });

  // ─── Form Input Validation ──────────────────────────────
  var addForm = document.getElementById('add-team-form');
  var assignForm = document.getElementById('assign-team-form');
  var addMessage = document.getElementById('add-message');
  var assignMessage = document.getElementById('assign-message');

  function showStatus(el, text, type) {
    if (!el) return;
    el.textContent = text;
    el.className = 'auth-message ' + type;
    el.style.display = 'block';
    setTimeout(function() {
      el.style.display = 'none';
    }, 4000);
  }

  if (addForm) {
    addForm.addEventListener('submit', function(e) {
      var nameVal = (document.getElementById('member-name') || {}).value || '';
      var emailVal = (document.getElementById('member-email') || {}).value || '';
      
      if (!nameVal.trim() || !emailVal.trim()) {
        e.preventDefault();
        showStatus(addMessage, 'Please fill in all staff details.', 'error');
      }
    });
  }

  if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
      var staffSelect = document.getElementById('assign-member-select');
      var eventSelect = document.getElementById('assign-event-select');

      if (!staffSelect.value || !eventSelect.value) {
        e.preventDefault();
        showStatus(assignMessage, 'Please select both staff member and event.', 'error');
      }
    });
  }

})();
