// Host Ticket Verification System
(function(){
  const eventSelect = document.getElementById('event-select');
  const ticketCodeInput = document.getElementById('ticket-code-input');
  const verificationSection = document.getElementById('verification-section');
  const ticketsLogSection = document.getElementById('tickets-log-section');
  const eventStats = document.getElementById('event-stats');
  const verificationResult = document.getElementById('verification-result');
  const resultContent = document.getElementById('result-content');
  const clearResultBtn = document.getElementById('clear-result-btn');
  const noEventMessage = document.getElementById('no-event-message');
  const verifiedTicketsList = document.getElementById('verified-tickets-list');
  const emptyLog = document.getElementById('empty-log');
  const exportLogBtn = document.getElementById('export-log-btn');
  const searchVerifiedTickets = document.getElementById('search-verified-tickets');
  
  // Scanner elements
  const toggleScannerBtn = document.getElementById('toggle-scanner-btn');
  const toggleManualInputBtn = document.getElementById('toggle-manual-input-btn');
  const scannerView = document.getElementById('scanner-view');
  const manualInputView = document.getElementById('manual-input-view');
  const qrScannerElement = document.getElementById('qr-scanner');
  const scannerStatus = document.getElementById('scanner-status');

  let html5QrcodeScanner = null;
  let isScannerActive = false;

  // Mock data for events and tickets
  const mockEvents = [
    {
      id: 1,
      title: 'Summer Beats Festival',
      date: 'Aug 21',
      venue: 'Central Park',
      city: 'New York',
      totalTickets: 500,
      tickets: [
        { code: 'SBF001', attendee: 'John Smith', type: 'General' },
        { code: 'SBF002', attendee: 'Emma Johnson', type: 'VIP' },
        { code: 'SBF003', attendee: 'Michael Brown', type: 'General' },
        { code: 'SBF004', attendee: 'Sarah Davis', type: 'General' },
        { code: 'SBF005', attendee: 'James Wilson', type: 'VIP' }
      ]
    },
    {
      id: 2,
      title: 'Broadway Nights',
      date: 'Sep 12',
      venue: 'Downtown Theater',
      city: 'New York',
      totalTickets: 300,
      tickets: [
        { code: 'BRW001', attendee: 'Lisa Anderson', type: 'Orchestra' },
        { code: 'BRW002', attendee: 'David Miller', type: 'Mezzanine' },
        { code: 'BRW003', attendee: 'Rachel Garcia', type: 'Orchestra' }
      ]
    },
    {
      id: 3,
      title: 'Championship Game',
      date: 'Oct 3',
      venue: 'City Stadium',
      city: 'Los Angeles',
      totalTickets: 8000,
      tickets: [
        { code: 'CHG001', attendee: 'Tom Martinez', type: 'Lower Bowl' },
        { code: 'CHG002', attendee: 'Jessica Lee', type: 'Upper Bowl' },
        { code: 'CHG003', attendee: 'Chris Taylor', type: 'Club' }
      ]
    }
  ];

  let currentEvent = null;
  let verifiedTickets = [];

  // Load events into select
  function loadEvents(){
    mockEvents.forEach(event => {
      const option = document.createElement('option');
      option.value = event.id;
      option.textContent = `${event.title} (${event.date})`;
      eventSelect.appendChild(option);
    });
  }

  // Initialize QR code scanner
  function initializeScanner(){
    if(html5QrcodeScanner) return;
    
    html5QrcodeScanner = new Html5Qrcode('qr-scanner');
    
    const qrCodeSuccessCallback = (decodedText) => {
      if(currentEvent){
        processTicketCode(decodedText.trim().toUpperCase());
      }
    };
    
    const qrCodeErrorCallback = () => {
      // Suppress error logs during scanning
    };

    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
      aspectRatio: 1.0
    };

    html5QrcodeScanner.start(
      { facingMode: 'environment' },
      config,
      qrCodeSuccessCallback,
      qrCodeErrorCallback
    ).catch(err => {
      updateScannerStatus(`❌ Camera access denied or unavailable: ${err.message}`, 'error');
    });
  }

  // Stop scanner
  function stopScanner(){
    if(html5QrcodeScanner){
      html5QrcodeScanner.stop().catch(err => console.log('Error stopping scanner:', err));
    }
  }

  // Update scanner status
  function updateScannerStatus(message, type = 'info'){
    scannerStatus.textContent = message;
    scannerStatus.className = 'scanner-status scanner-' + type;
    scannerStatus.style.display = 'block';
  }

  // Toggle scanner view
  toggleScannerBtn.addEventListener('click', () => {
    if(!currentEvent){
      alert('Please select an event first');
      return;
    }
    
    isScannerActive = true;
    scannerView.style.display = 'block';
    manualInputView.style.display = 'none';
    toggleScannerBtn.style.display = 'none';
    toggleManualInputBtn.style.display = 'inline-block';
    updateScannerStatus('🟢 Scanner ready - point camera at barcode', 'info');
    
    setTimeout(() => initializeScanner(), 100);
  });

  // Toggle manual input view
  toggleManualInputBtn.addEventListener('click', () => {
    isScannerActive = false;
    stopScanner();
    scannerView.style.display = 'none';
    manualInputView.style.display = 'block';
    toggleScannerBtn.style.display = 'inline-block';
    toggleManualInputBtn.style.display = 'none';
    ticketCodeInput.focus();
  });

  // Process ticket code (extracted for reuse)
  function processTicketCode(code){
    if(!code){
      showResult('Please enter a ticket code', 'error');
      return;
    }

    // Check if already verified
    if(verifiedTickets.some(t => t.code === code)){
      showResult(`❌ Ticket ${code} already verified at ${verifiedTickets.find(t => t.code === code).verifiedAt}`, 'error');
      if(isScannerActive) ticketCodeInput.value = '';
      return;
    }

    // Find ticket in current event
    const ticket = currentEvent.tickets.find(t => t.code === code);
    if(!ticket){
      showResult(`❌ Ticket code "${code}" not found`, 'error');
      if(isScannerActive) ticketCodeInput.value = '';
      return;
    }

    // Verify the ticket
    const now = new Date();
    const verifiedTicket = {
      ...ticket,
      verifiedAt: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      verifiedDate: now.toLocaleDateString()
    };

    verifiedTickets.push(verifiedTicket);
    localStorage.setItem(`verified-tickets-${currentEvent.id}`, JSON.stringify(verifiedTickets));

    showResult(`✅ Welcome ${ticket.attendee}! Ticket ${code} verified. (${ticket.type})`, 'success');
    updateEventStats();
    updateVerifiedTicketsList();
    ticketCodeInput.value = '';
    ticketCodeInput.focus();
  }

  // Handle event selection
  eventSelect.addEventListener('change', (e) => {
    const eventId = parseInt(e.target.value);
    if(!eventId){
      currentEvent = null;
      stopScanner();
      isScannerActive = false;
      verificationSection.style.display = 'none';
      ticketsLogSection.style.display = 'none';
      eventStats.style.display = 'none';
      ticketCodeInput.value = '';
      verificationResult.style.display = 'none';
      scannerView.style.display = 'none';
      manualInputView.style.display = 'block';
      toggleScannerBtn.style.display = 'inline-block';
      toggleManualInputBtn.style.display = 'none';
      return;
    }

    currentEvent = mockEvents.find(ev => ev.id === eventId);
    if(currentEvent){
      verifiedTickets = JSON.parse(localStorage.getItem(`verified-tickets-${eventId}`)) || [];
      updateEventStats();
      updateVerifiedTicketsList();
      verificationSection.style.display = 'block';
      ticketsLogSection.style.display = 'block';
      eventStats.style.display = 'grid';
      scannerView.style.display = 'none';
      manualInputView.style.display = 'block';
      toggleScannerBtn.style.display = 'inline-block';
      toggleManualInputBtn.style.display = 'none';
      ticketCodeInput.focus();
    }
  });

  // Update event statistics
  function updateEventStats(){
    if(!currentEvent) return;
    document.getElementById('selected-event-name').textContent = currentEvent.title;
    document.getElementById('total-tickets-count').textContent = currentEvent.totalTickets;
    document.getElementById('verified-tickets-count').textContent = verifiedTickets.length;
    document.getElementById('remaining-tickets-count').textContent = currentEvent.totalTickets - verifiedTickets.length;
  }

  // Handle ticket code verification
  ticketCodeInput.addEventListener('keypress', (e) => {
    if(e.key !== 'Enter' || !currentEvent) return;
    e.preventDefault();
    
    const code = ticketCodeInput.value.trim().toUpperCase();
    processTicketCode(code);
  });

  // Show verification result
  function showResult(message, type){
    resultContent.innerHTML = `<div class="result-${type}">${message}</div>`;
    verificationResult.style.display = 'block';
    verificationResult.className = 'verification-result result-' + type;
  }

  // Clear result
  clearResultBtn.addEventListener('click', () => {
    verificationResult.style.display = 'none';
    ticketCodeInput.focus();
  });

  // Update verified tickets list
  function updateVerifiedTicketsList(){
    if(verifiedTickets.length === 0){
      verifiedTicketsList.innerHTML = '';
      emptyLog.style.display = 'block';
      return;
    }

    emptyLog.style.display = 'none';
    renderVerifiedTickets(verifiedTickets);
  }

  // Render verified tickets
  function renderVerifiedTickets(tickets){
    verifiedTicketsList.innerHTML = tickets.map(ticket => `
      <article class="verified-ticket-item">
        <div class="ticket-info">
          <h4>${ticket.attendee}</h4>
          <p class="ticket-meta">Code: <strong>${ticket.code}</strong> | ${ticket.type}</p>
          <p class="ticket-time">Verified at ${ticket.verifiedAt}</p>
        </div>
        <button class="btn-icon" onclick="removeVerifiedTicket('${ticket.code}')" aria-label="Remove entry">✕</button>
      </article>
    `).join('');
  }

  // Remove verified ticket (exposed globally)
  window.removeVerifiedTicket = function(code){
    verifiedTickets = verifiedTickets.filter(t => t.code !== code);
    localStorage.setItem(`verified-tickets-${currentEvent.id}`, JSON.stringify(verifiedTickets));
    updateEventStats();
    updateVerifiedTicketsList();
  };

  // Search verified tickets
  searchVerifiedTickets.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    const filtered = verifiedTickets.filter(t => 
      t.attendee.toLowerCase().includes(query) || 
      t.code.toLowerCase().includes(query)
    );
    renderVerifiedTickets(filtered);
  });

  // Export log
  exportLogBtn.addEventListener('click', () => {
    if(verifiedTickets.length === 0){
      alert('No tickets to export');
      return;
    }

    let csv = 'Ticket Code,Attendee Name,Ticket Type,Verified Time\n';
    verifiedTickets.forEach(ticket => {
      csv += `${ticket.code},"${ticket.attendee}",${ticket.type},${ticket.verifiedAt}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `verified-tickets-${currentEvent.title}-${new Date().toLocaleDateString()}.csv`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  });

  // Initialize
  loadEvents();
})();
