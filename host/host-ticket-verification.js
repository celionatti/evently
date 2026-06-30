/**
 * Host Ticket Verification — UI & Scanner interaction script.
 * 
 * In your PHP template, the verification logic, checked-in log list, 
 * and metrics stats are rendered server-side.
 * 
 * This script handles:
 * 1. Initializing and managing the device camera scanner (via html5-qrcode library).
 * 2. Autocomplete scan input values and submitting the verification form.
 * 3. Client-side search filtering of the checked-in log list.
 */
(function() {
  'use strict';

  // DOM Elements
  var toggleScannerBtn = document.getElementById('toggle-scanner-btn');
  var toggleManualInputBtn = document.getElementById('toggle-manual-input-btn');
  var scannerView = document.getElementById('scanner-view');
  var manualInputView = document.getElementById('manual-input-view');
  var ticketCodeInput = document.getElementById('ticket-code-input');
  var verifyForm = document.getElementById('manual-verify-form');
  var scannerStatus = document.getElementById('scanner-status');
  var searchInput = document.getElementById('search-verified-tickets');
  var logList = document.getElementById('verified-tickets-list');
  var emptyLogMsg = document.getElementById('empty-log');
  var clearResultBtn = document.getElementById('clear-result-btn');
  var verificationResult = document.getElementById('verification-result');

  // Scanner state variables
  var html5QrcodeScanner = null;
  var isScannerActive = false;

  // ─── Camera Scanner Logic ───────────────────────────────
  
  function updateScannerStatus(message, type) {
    type = type || 'info';
    if (!scannerStatus) return;
    scannerStatus.textContent = message;
    scannerStatus.className = 'scanner-status scanner-' + type;
    scannerStatus.style.display = 'block';
  }

  function initializeScanner() {
    if (html5QrcodeScanner) return;

    // Initialize scanner class (Html5Qrcode uses the raw video stream)
    html5QrcodeScanner = new Html5Qrcode('qr-scanner');

    var onScanSuccess = function(decodedText) {
      var code = (decodedText || '').trim();
      if (code) {
        // Set scanned value in code input field
        if (ticketCodeInput) {
          ticketCodeInput.value = code;
        }
        updateScannerStatus('🟢 Code Scanned: ' + code + '. Verifying...', 'success');

        // Automatically submit the form to the PHP backend
        if (verifyForm) {
          setTimeout(function() {
            verifyForm.submit();
          }, 600);
        }
      }
    };

    var onScanError = function() {
      // Suppress console spam during real-time scan frames
    };

    var config = {
      fps: 12,
      qrbox: { width: 250, height: 250 },
      aspectRatio: 1.0
    };

    html5QrcodeScanner.start(
      { facingMode: 'environment' },
      config,
      onScanSuccess,
      onScanError
    ).catch(function(err) {
      updateScannerStatus('❌ Camera access error: ' + err.message, 'error');
    });
  }

  function stopScanner() {
    if (html5QrcodeScanner) {
      html5QrcodeScanner.stop().then(function() {
        html5QrcodeScanner = null;
      }).catch(function(err) {
        console.error('Error stopping scanner:', err);
      });
    }
  }

  // Bind scanner toggles
  if (toggleScannerBtn) {
    toggleScannerBtn.addEventListener('click', function() {
      isScannerActive = true;
      if (scannerView) scannerView.classList.remove('hidden');
      if (manualInputView) manualInputView.classList.add('hidden');
      toggleScannerBtn.classList.add('hidden');
      if (toggleManualInputBtn) toggleManualInputBtn.classList.remove('hidden');
      
      updateScannerStatus('📷 Scanner starting, please grant camera permissions...', 'info');
      // Give DOM time to update display state before camera init
      setTimeout(initializeScanner, 100);
    });
  }

  if (toggleManualInputBtn) {
    toggleManualInputBtn.addEventListener('click', function() {
      isScannerActive = false;
      stopScanner();
      if (scannerView) scannerView.classList.add('hidden');
      if (manualInputView) manualInputView.classList.remove('hidden');
      if (toggleScannerBtn) toggleScannerBtn.classList.remove('hidden');
      toggleManualInputBtn.classList.add('hidden');
      if (ticketCodeInput) ticketCodeInput.focus();
    });
  }

  // Clear Result display block
  if (clearResultBtn) {
    clearResultBtn.addEventListener('click', function() {
      if (verificationResult) {
        verificationResult.classList.add('hidden');
      }
      if (ticketCodeInput) {
        ticketCodeInput.focus();
      }
    });
  }

  // ─── Checked In Log Filter ─────────────────────────────
  
  if (searchInput && logList) {
    searchInput.addEventListener('input', function(e) {
      var query = (e.target.value || '').toLowerCase().trim();
      var items = logList.querySelectorAll('.verified-ticket-item');
      var visibleCount = 0;

      items.forEach(function(item) {
        var attendeeName = (item.querySelector('h4') ? item.querySelector('h4').textContent : '').toLowerCase();
        var ticketCode = (item.querySelector('strong') ? item.querySelector('strong').textContent : '').toLowerCase();
        
        if (attendeeName.indexOf(query) !== -1 || ticketCode.indexOf(query) !== -1) {
          item.style.display = '';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });

      if (emptyLogMsg) {
        if (items.length > 0 && visibleCount === 0) {
          emptyLogMsg.classList.remove('hidden');
        } else {
          emptyLogMsg.classList.add('hidden');
        }
      }
    });
  }

  // ─── Checked In Ticket Removal ──────────────────────────
  // Note: For production backend database sync, wrap this in a form post
  // or AJAX request to delete the check-in status on the server.
  window.removeVerifiedTicket = function(code) {
    if (!code) return;
    
    // UI removal placeholder
    var itemToRemove = logList ? logList.querySelector('[data-code="' + code + '"]') : null;
    if (itemToRemove) {
      itemToRemove.remove();
      
      // Update check-in counts in DOM header metrics
      var checkinCountEl = document.getElementById('verified-tickets-count');
      var remainingCountEl = document.getElementById('remaining-tickets-count');
      if (checkinCountEl && remainingCountEl) {
        var currentChecked = parseInt(checkinCountEl.textContent) || 0;
        var currentRemaining = parseInt(remainingCountEl.textContent) || 0;
        
        if (currentChecked > 0) {
          checkinCountEl.textContent = currentChecked - 1;
          remainingCountEl.textContent = currentRemaining + 1;
        }
      }
      
      // If list is now empty, toggle empty log label
      var remainingItems = logList ? logList.querySelectorAll('.verified-ticket-item') : [];
      if (remainingItems.length === 0 && emptyLogMsg) {
        emptyLogMsg.classList.remove('hidden');
      }
    }
  };

})();
