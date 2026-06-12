// Host Event Management
(function(){
  const eventTitle = document.getElementById('event-title');
  const eventDetailsSummary = document.getElementById('event-details-summary');
  const totalRevenueDisplay = document.getElementById('total-revenue-display');
  const ticketsSoldDisplay = document.getElementById('tickets-sold-display');
  const occupancyDisplay = document.getElementById('occupancy-display');
  const avgPriceDisplay = document.getElementById('avg-price-display');
  const occupancyBar = document.getElementById('occupancy-bar');
  const occupancyPercentage = document.getElementById('occupancy-percentage');
  const capacityLabel = document.getElementById('capacity-label');
  const ticketTypesGrid = document.getElementById('ticket-types-grid');
  const ticketsTable = document.getElementById('tickets-table');
  const ticketsTbody = document.getElementById('tickets-tbody');
  const ticketsPagination = document.getElementById('tickets-pagination');
  const noTickets = document.getElementById('no-tickets');
  const searchTickets = document.getElementById('search-tickets');
  const exportReportBtn = document.getElementById('export-report-btn');
  const sendReminderBtn = document.getElementById('send-reminder-btn');

  // Mock data for events with tickets
  const mockEvents = {
    1: {
      id: 1,
      title: 'Summer Beats Festival',
      date: 'Aug 21, 2026',
      time: '6:00 PM',
      venue: 'Central Park',
      city: 'New York',
      status: 'upcoming',
      ticketsTotal: 500,
      ticketsSold: 342,
      revenue: 17100,
      ticketPrice: 50,
      description: 'An amazing summer festival featuring top artists and live performances.',
      ticketTypes: [
        { name: 'General', count: 250, price: 50 },
        { name: 'VIP', count: 92, price: 75 }
      ],
      soldTickets: [
        { id: 't001', attendee: 'John Smith', email: 'john@example.com', type: 'General', price: 50, purchaseDate: '2026-06-01', status: 'confirmed' },
        { id: 't002', attendee: 'Emma Johnson', email: 'emma@example.com', type: 'VIP', price: 75, purchaseDate: '2026-06-02', status: 'confirmed' },
        { id: 't003', attendee: 'Michael Brown', email: 'michael@example.com', type: 'General', price: 50, purchaseDate: '2026-06-03', status: 'confirmed' },
        { id: 't004', attendee: 'Sarah Davis', email: 'sarah@example.com', type: 'General', price: 50, purchaseDate: '2026-06-04', status: 'pending' },
        { id: 't005', attendee: 'James Wilson', email: 'james@example.com', type: 'VIP', price: 75, purchaseDate: '2026-06-05', status: 'confirmed' },
        { id: 't006', attendee: 'Lisa Anderson', email: 'lisa@example.com', type: 'General', price: 50, purchaseDate: '2026-06-06', status: 'confirmed' },
        { id: 't007', attendee: 'David Miller', email: 'david@example.com', type: 'General', price: 50, purchaseDate: '2026-06-07', status: 'confirmed' },
        { id: 't008', attendee: 'Rachel Garcia', email: 'rachel@example.com', type: 'VIP', price: 75, purchaseDate: '2026-06-08', status: 'confirmed' },
        { id: 't009', attendee: 'Tom Martinez', email: 'tom@example.com', type: 'General', price: 50, purchaseDate: '2026-06-09', status: 'confirmed' },
        { id: 't010', attendee: 'Jessica Lee', email: 'jessica@example.com', type: 'General', price: 50, purchaseDate: '2026-06-10', status: 'confirmed' }
      ]
    },
    2: {
      id: 2,
      title: 'Broadway Nights',
      date: 'Sep 12, 2026',
      time: '8:00 PM',
      venue: 'Downtown Theater',
      city: 'New York',
      status: 'upcoming',
      ticketsTotal: 300,
      ticketsSold: 245,
      revenue: 14700,
      ticketPrice: 60,
      description: 'Experience the magic of Broadway with top theatrical productions.',
      ticketTypes: [
        { name: 'Orchestra', count: 150, price: 60 },
        { name: 'Mezzanine', count: 95, price: 50 }
      ],
      soldTickets: [
        { id: 't021', attendee: 'Lisa Anderson', email: 'lisa@example.com', type: 'Orchestra', price: 60, purchaseDate: '2026-06-01', status: 'confirmed' },
        { id: 't022', attendee: 'David Miller', email: 'david@example.com', type: 'Mezzanine', price: 50, purchaseDate: '2026-06-02', status: 'confirmed' }
      ]
    },
    3: {
      id: 3,
      title: 'Championship Game',
      date: 'Oct 3, 2026',
      time: '7:30 PM',
      venue: 'City Stadium',
      city: 'Los Angeles',
      status: 'upcoming',
      ticketsTotal: 8000,
      ticketsSold: 6543,
      revenue: 393180,
      ticketPrice: 60,
      description: 'Witness the most anticipated sports event of the season.',
      ticketTypes: [
        { name: 'Lower Bowl', count: 3000, price: 60 },
        { name: 'Upper Bowl', count: 3000, price: 45 },
        { name: 'Club', count: 543, price: 100 }
      ],
      soldTickets: [
        { id: 't031', attendee: 'Tom Martinez', email: 'tom@example.com', type: 'Lower Bowl', price: 60, purchaseDate: '2026-05-15', status: 'confirmed' },
        { id: 't032', attendee: 'Jessica Lee', email: 'jessica@example.com', type: 'Upper Bowl', price: 45, purchaseDate: '2026-05-16', status: 'confirmed' }
      ]
    }
  };

  // Get event ID from URL
  const urlParams = new URLSearchParams(window.location.search);
  const eventId = parseInt(urlParams.get('id')) || 1;
  let currentEvent = mockEvents[eventId];
  let filteredTickets = currentEvent.soldTickets;
  let currentPage = 1;
  const pageSize = 10;

  if(!currentEvent){
    eventTitle.textContent = 'Event not found';
    return;
  }

  // Display event info
  function displayEventInfo(){
    eventTitle.textContent = currentEvent.title;
    eventDetailsSummary.textContent = `${currentEvent.date} at ${currentEvent.time} • ${currentEvent.venue}, ${currentEvent.city}`;
    
    const occupancy = (currentEvent.ticketsSold / currentEvent.ticketsTotal * 100).toFixed(1);
    const avgPrice = (currentEvent.revenue / currentEvent.ticketsSold).toFixed(2);

    totalRevenueDisplay.textContent = '$' + currentEvent.revenue.toLocaleString('en-US', {minimumFractionDigits: 2});
    ticketsSoldDisplay.textContent = `${currentEvent.ticketsSold} / ${currentEvent.ticketsTotal}`;
    occupancyDisplay.textContent = occupancy + '%';
    avgPriceDisplay.textContent = '$' + avgPrice;

    occupancyBar.style.width = occupancy + '%';
    occupancyPercentage.textContent = occupancy + '%';
    capacityLabel.textContent = `${currentEvent.ticketsSold} / ${currentEvent.ticketsTotal}`;
  }

  // Display ticket types breakdown
  function displayTicketTypes(){
    ticketTypesGrid.innerHTML = '';
    currentEvent.ticketTypes.forEach(type => {
      const card = document.createElement('article');
      card.className = 'ticket-type-card';
      const typeOccupancy = (type.count / currentEvent.ticketsTotal * 100).toFixed(0);
      card.innerHTML = `
        <h3>${type.name}</h3>
        <p class="type-price">$${type.price}</p>
        <div class="progress-bar">
          <div class="progress-fill" style="width:${typeOccupancy}%"></div>
        </div>
        <p class="type-stats">${type.count} tickets (${typeOccupancy}%)</p>
      `;
      ticketTypesGrid.appendChild(card);
    });
  }

  /* Toast & modal helpers */
  const toastContainer = document.getElementById('toast-container');
  const modalBackdrop = document.getElementById('modal-backdrop');
  const modalTitle = document.getElementById('modal-title');
  const modalBody = document.getElementById('modal-body');
  const modalActions = document.getElementById('modal-actions');
  const modalClose = document.getElementById('modal-close');

  function showToast(message, type = 'info', timeout = 4000){
    if(!toastContainer) return; // fail-safe
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.textContent = message;
    toastContainer.appendChild(el);
    setTimeout(()=>{ el.style.opacity = '0'; setTimeout(()=> el.remove(), 220); }, timeout);
  }

  function openModal(title, htmlContent, actions = []){
    if(!modalBackdrop) return;
    modalTitle.textContent = title || '';
    if(typeof htmlContent === 'string') modalBody.innerHTML = `<pre style="white-space:pre-wrap;margin:0">${htmlContent}</pre>`;
    else modalBody.innerHTML = '';
    modalActions.innerHTML = '';
    actions.forEach(a => {
      const btn = document.createElement('button');
      btn.className = 'btn' + (a.primary ? ' primary' : '');
      btn.textContent = a.label;
      btn.addEventListener('click', () => { if(a.onClick) a.onClick(); closeModal(); });
      modalActions.appendChild(btn);
    });
    modalBackdrop.classList.add('open');
    modalBackdrop.setAttribute('aria-hidden','false');
  }

  function closeModal(){
    if(!modalBackdrop) return;
    modalBackdrop.classList.remove('open');
    modalBackdrop.setAttribute('aria-hidden','true');
  }

  modalClose?.addEventListener('click', closeModal);
  modalBackdrop?.addEventListener('click', (e) => { if(e.target === modalBackdrop) closeModal(); });

  function showConfirm(message, onConfirm){
    openModal('Confirm action', message, [
      { label: 'Cancel', primary: false, onClick: ()=>{} },
      { label: 'Confirm', primary: true, onClick: onConfirm }
    ]);
  }

  // Render tickets table
  function renderTicketsTable(){
    if(filteredTickets.length === 0){
      ticketsTable.style.display = 'none';
      noTickets.style.display = 'block';
      ticketsPagination.innerHTML = '';
      return;
    }

    ticketsTable.style.display = 'table';
    noTickets.style.display = 'none';

    const start = (currentPage - 1) * pageSize;
    const pageTickets = filteredTickets.slice(start, start + pageSize);

    ticketsTbody.innerHTML = pageTickets.map(ticket => {
      const statusText = ticket.status === 'confirmed' ? '✓ Confirmed' : ticket.status === 'pending' ? '⏳ Pending' : ticket.status === 'refunded' ? '↩ Refunded' : ticket.status;
      const statusDetail = ticket.status === 'confirmed' ? 'Payment received' : ticket.status === 'pending' ? 'Awaiting confirmation' : ticket.status === 'refunded' ? 'Refund issued' : '';
      return `
      <tr>
        <td>${ticket.attendee}</td>
        <td>${ticket.email}</td>
        <td>${ticket.type}</td>
        <td>$${ticket.price.toFixed(2)}</td>
        <td>${ticket.purchaseDate}</td>
        <td>
          <div class="status-cell">
            <span class="status-badge ${ticket.status}" title="${ticket.status}">${statusText}</span>
            <div class="status-detail">${statusDetail}</div>
          </div>
        </td>
        <td>
          <div class="action-menu" data-ticket-id="${ticket.id}">
            <button class="action-menu-toggle" aria-expanded="false" aria-label="Open actions">⋮</button>
            <div class="menu" role="menu">
              <a href="#" data-action="resend">Resend ticket</a>
              <a href="#" data-action="refund">Refund</a>
              <a href="#" data-action="flag">Flag issue</a>
              <a href="#" data-action="view">View details</a>
            </div>
          </div>
        </td>
      </tr>
    `
    }).join('');

    renderPagination();
  }

  // Action dropdown handling (delegated)
  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('.action-menu-toggle');
    if(toggle){
      const wrap = toggle.closest('.action-menu');
      const isOpen = wrap.classList.toggle('open');
      // close others
      document.querySelectorAll('.action-menu.open').forEach(m => { if(m !== wrap){ m.classList.remove('open'); m.querySelector('.action-menu-toggle')?.setAttribute('aria-expanded','false'); } });
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      return;
    }

    const menuLink = e.target.closest('.action-menu .menu a');
    if(menuLink){
      e.preventDefault();
      const action = menuLink.dataset.action;
      const wrap = menuLink.closest('.action-menu');
      const ticketId = wrap.dataset.ticketId;
      handleAction(action, ticketId);
      wrap.classList.remove('open');
      wrap.querySelector('.action-menu-toggle')?.setAttribute('aria-expanded','false');
      return;
    }

    // click outside closes any open menus
    if(!e.target.closest('.action-menu')){
      document.querySelectorAll('.action-menu.open').forEach(m => { m.classList.remove('open'); m.querySelector('.action-menu-toggle')?.setAttribute('aria-expanded','false'); });
    }
  });

  function handleAction(action, ticketId){
    const ticket = currentEvent.soldTickets.find(t => t.id === ticketId);
    if(!ticket) { showToast('Ticket not found', 'error'); return; }
    switch(action){
      case 'resend':
        // simulate resend
        showToast(`Resent ticket to ${ticket.email}`, 'success');
        break;
      case 'refund':
        showConfirm(`Refund ticket ${ticket.attendee} (${ticket.id})?`, () => {
          ticket.status = 'refunded';
          renderTicketsTable();
          showToast('Ticket refunded', 'success');
        });
        break;
      case 'flag':
        showToast(`Flagged ticket ${ticket.id} for review`, 'warning');
        break;
      case 'view':
        openModal('Ticket details', JSON.stringify(ticket, null, 2), [
          { label: 'Close', primary: false, onClick: ()=>{} }
        ]);
        break;
    }
  }

  // Render pagination
  function renderPagination(){
    const totalPages = Math.ceil(filteredTickets.length / pageSize);
    ticketsPagination.innerHTML = '';

    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-button';
    prevBtn.textContent = 'Prev';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', () => { currentPage = Math.max(1, currentPage - 1); renderTicketsTable(); });
    ticketsPagination.appendChild(prevBtn);

    for(let i = 1; i <= totalPages; i++){
      const btn = document.createElement('button');
      btn.className = 'page-button';
      btn.textContent = i;
      btn.setAttribute('aria-current', currentPage === i ? 'true' : 'false');
      btn.disabled = currentPage === i;
      btn.addEventListener('click', () => { currentPage = i; renderTicketsTable(); });
      ticketsPagination.appendChild(btn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-button';
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', () => { currentPage = Math.min(totalPages, currentPage + 1); renderTicketsTable(); });
    ticketsPagination.appendChild(nextBtn);
  }

  // Search tickets
  searchTickets.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    filteredTickets = currentEvent.soldTickets.filter(ticket =>
      ticket.attendee.toLowerCase().includes(query) ||
      ticket.email.toLowerCase().includes(query)
    );
    currentPage = 1;
    renderTicketsTable();
  });

  // Export report as CSV
  exportReportBtn.addEventListener('click', () => {
    let csv = 'Event Report\n\n';
    csv += `Event: ${currentEvent.title}\n`;
    csv += `Date: ${currentEvent.date}\n`;
    csv += `Venue: ${currentEvent.venue}, ${currentEvent.city}\n\n`;
    csv += `Total Revenue: $${currentEvent.revenue.toLocaleString()}\n`;
    csv += `Tickets Sold: ${currentEvent.ticketsSold} / ${currentEvent.ticketsTotal}\n`;
    csv += `Occupancy: ${(currentEvent.ticketsSold / currentEvent.ticketsTotal * 100).toFixed(1)}%\n\n`;
    csv += 'Ticket Sales\n';
    csv += 'Attendee,Email,Ticket Type,Price,Purchase Date,Status\n';
    
    currentEvent.soldTickets.forEach(ticket => {
      csv += `"${ticket.attendee}","${ticket.email}",${ticket.type},${ticket.price},${ticket.purchaseDate},${ticket.status}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${currentEvent.title.replace(/\s+/g, '-')}-report.csv`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  });

  // Send reminder
  sendReminderBtn.addEventListener('click', () => {
    const unconfirmedCount = currentEvent.soldTickets.filter(t => t.status === 'pending').length;
    showToast(`📧 Reminder sent to ${unconfirmedCount} attendees with pending confirmations!`, 'info');
  });

  // Initialize
  displayEventInfo();
  displayTicketTypes();
  renderTicketsTable();
})();
