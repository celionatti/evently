// Host Ticket Verification System
(function(){
  const eventSelect = document.getElementById('event-select');
  const ticketCodeInput = document.getElementById('ticket-code-input');
  const verificationSection = document.getElementById('verification-section');
  const ticketsLogSection = document.getElementById('tickets-log-section');
  const eventStats = document.getElementById('event-stats');
  const verificationResult = document.getElementById('verification-result');
  const ticketSuccessCard = document.getElementById('ticket-success-card');
  const ticketErrorCard = document.getElementById('ticket-error-card');
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
      const prev = verifiedTickets.find(t => t.code === code);
      showResult(`Ticket ${code} was already verified at ${prev.verifiedAt}`, 'error');
      if(isScannerActive) ticketCodeInput.value = '';
      return;
    }

    // Find ticket in current event
    const ticket = currentEvent.tickets.find(t => t.code === code);
    if(!ticket){
      showResult(`Ticket code "${code}" was not found for this event`, 'error');
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

    showResult(`Welcome ${ticket.attendee}!`, 'success', ticket);
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

  // Generate a decorative barcode visual from a code string
  function generateBarcodeHTML(code){
    const bars = [];
    // Create a deterministic barcode pattern from the ticket code
    for(let i = 0; i < code.length; i++){
      const charCode = code.charCodeAt(i);
      for(let j = 0; j < 3; j++){
        const height = 20 + ((charCode * (j + 1)) % 20);
        const width = (charCode + j) % 2 === 0 ? 2 : 3;
        bars.push(`<span style="height:${height}px;width:${width}px"></span>`);
      }
      // Gap between character groups
      bars.push(`<span style="height:0;width:2px"></span>`);
    }
    return bars.join('');
  }

  // Get initials from a name
  function getInitials(name){
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
  }

  // Show verification result with a rich ticket card
  function showResult(message, type, ticketData){
    if(type === 'success' && ticketData){
      const event = currentEvent;
      const now = new Date();
      const verifyTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      const verifyDate = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });

      // Populate Success Card Fields
      document.getElementById('ticket-success-event-title').textContent = event.title;
      document.getElementById('ticket-success-event-location').textContent = `${event.venue} · ${event.city}`;
      document.getElementById('ticket-success-avatar').textContent = getInitials(ticketData.attendee);
      document.getElementById('ticket-success-attendee-name').textContent = ticketData.attendee;
      document.getElementById('ticket-success-ticket-type').textContent = `${ticketData.type} Ticket`;
      document.getElementById('ticket-success-code').textContent = ticketData.code;
      document.getElementById('ticket-success-date').textContent = event.date;
      document.getElementById('ticket-success-time').textContent = verifyTime;
      document.getElementById('ticket-success-checkin-date').textContent = verifyDate;
      document.getElementById('ticket-success-barcode').innerHTML = generateBarcodeHTML(ticketData.code);
      document.getElementById('ticket-success-barcode-code').textContent = ticketData.code;

      ticketSuccessCard.style.display = 'block';
      ticketErrorCard.style.display = 'none';
    } else if(type === 'error'){
      // Determine the error subtype
      let icon = '❌';
      let title = 'Invalid Ticket';
      let subtitle = message;
      let badgeClass = 'badge-invalid';
      let badgeText = 'Invalid';

      if(message.includes('already verified')){
        icon = '⚠️';
        title = 'Duplicate Scan';
        badgeClass = 'badge-duplicate';
        badgeText = 'Already Used';
      } else if(message.includes('not found')){
        icon = '🔍';
        title = 'Ticket Not Found';
      }

      // Populate Error Card Fields
      document.getElementById('ticket-error-event-title').textContent = currentEvent ? currentEvent.title : 'Verification Error';
      document.getElementById('ticket-error-event-location').textContent = currentEvent ? `${currentEvent.venue} · ${currentEvent.city}` : '';
      
      const badge = document.getElementById('ticket-error-badge');
      badge.textContent = badgeText;
      badge.className = `ticket-status-badge ${badgeClass}`;

      document.getElementById('ticket-error-icon').textContent = icon;
      document.getElementById('ticket-error-title').textContent = title;
      document.getElementById('ticket-error-message').textContent = subtitle;

      ticketSuccessCard.style.display = 'none';
      ticketErrorCard.style.display = 'block';
    }

    verificationResult.style.display = 'flex';
  }

  // Clear result
  clearResultBtn.addEventListener('click', () => {
    verificationResult.style.display = 'none';
    ticketSuccessCard.style.display = 'none';
    ticketErrorCard.style.display = 'none';
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
