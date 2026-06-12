// Ticket detail page script
(function(){
  function qs(name){
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
  }

  function loadEventFromStorage(eventId){
    try{
      const raw = localStorage.getItem('event_' + eventId);
      if(!raw) return null;
      return JSON.parse(raw);
    }catch(e){
      return null;
    }
  }

  function saveEventToStorage(ev){
    try{ localStorage.setItem('event_' + ev.id, JSON.stringify(ev)); }catch(e){ console.error(e); }
  }

  function showToast(message, type='info', timeout=3000){
    const tc = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = message;
    tc.appendChild(t);
    setTimeout(()=>{ t.classList.add('visible'); }, 10);
    setTimeout(()=>{ t.classList.remove('visible'); setTimeout(()=>t.remove(),300); }, timeout);
  }

  function openConfirm(message, onConfirm){
    const backdrop = document.getElementById('modal-backdrop');
    backdrop.innerHTML = `
      <div class="modal">
        <h3>Confirm</h3>
        <p>${message}</p>
        <div class="modal-actions">
          <button id="confirm-yes" class="admin-action-btn">Yes</button>
          <button id="confirm-no" class="admin-action-btn">No</button>
        </div>
      </div>
    `;
    backdrop.classList.remove('hidden');
    document.getElementById('confirm-yes').onclick = () => { closeModal(); onConfirm(); };
    document.getElementById('confirm-no').onclick = closeModal;
  }

  function closeModal(){
    const backdrop = document.getElementById('modal-backdrop');
    backdrop.classList.add('hidden');
    backdrop.innerHTML = '';
  }

  function renderTicket(ticket, event){
    document.getElementById('ticket-id').textContent = ticket.id || '—';
    const evLink = document.getElementById('event-link');
    evLink.textContent = event.title || 'Event';
    evLink.href = `host-event-management.html?eventId=${event.id}`;
    document.getElementById('holder-name').textContent = ticket.holderName || ticket.name || '—';
    const emailEl = document.getElementById('holder-email');
    emailEl.textContent = ticket.email || '—';
    emailEl.href = 'mailto:' + (ticket.email || '');
    document.getElementById('ticket-type').textContent = ticket.type || '—';
    document.getElementById('ticket-price').textContent = ticket.price ? '$' + (ticket.price/100).toFixed(2) : '—';

    const statusEl = document.getElementById('ticket-status');
    statusEl.className = 'status-badge ' + (ticket.status || 'pending');
    statusEl.textContent = (ticket.status || 'pending').toUpperCase();

    const sd = document.getElementById('status-detail');
    sd.textContent = ticket.statusDetail || (ticket.status==='refunded'? 'Refunded to original payment method' : ticket.status==='cancelled'? 'Order cancelled' : ticket.status==='confirmed' ? 'Payment confirmed' : 'Awaiting payment confirmation');

    // QR placeholder: generate a data URI with basic text
    const qrImg = document.getElementById('qr-img');
    const qrText = `T:${ticket.id}|E:${event.id}|H:${ticket.holderName||''}`;
    qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(qrText);
  }

  function findTicket(eventObj, ticketId){
    if(!eventObj) return null;
    return (eventObj.soldTickets || []).find(t=>String(t.id) === String(ticketId));
  }

  // Wire up actions
  function setupActions(ticket, eventObj){
    document.getElementById('action-resend').addEventListener('click', (e)=>{
      e.preventDefault();
      showToast('Resent ticket to ' + (ticket.email || ticket.holderName));
    });
    document.getElementById('action-flag').addEventListener('click', (e)=>{
      e.preventDefault();
      ticket.flagged = true; saveEventToStorage(eventObj); showToast('Ticket flagged');
    });
    document.getElementById('action-refund').addEventListener('click', (e)=>{
      e.preventDefault();
      openConfirm('Refund this ticket? This will mark it as refunded.', ()=>{
        ticket.status = 'refunded';
        ticket.statusDetail = 'Refund issued by host';
        saveEventToStorage(eventObj);
        renderTicket(ticket, eventObj);
        showToast('Ticket refunded');
      });
    });
  }

  // Init
  document.addEventListener('DOMContentLoaded', ()=>{
    const ticketId = qs('ticketId');
    const eventId = qs('eventId');
    const pageBack = document.getElementById('back-link');
    if(eventId) pageBack.href = `host-event-management.html?eventId=${eventId}`;

    let eventObj = null;
    if(eventId) eventObj = loadEventFromStorage(eventId);

    // fallback sample event if none in storage
    if(!eventObj){
      eventObj = {
        id: eventId || '1',
        title: 'Sample Event',
        soldTickets: [
          { id: 'A1', holderName: 'Jane Doe', email: 'jane@example.com', type: 'General', price: 2500, status: 'confirmed' },
          { id: 'B2', holderName: 'John Smith', email: 'john@example.com', type: 'VIP', price: 5000, status: 'pending' }
        ]
      };
    }

    const ticket = ticketId ? findTicket(eventObj, ticketId) : (eventObj.soldTickets && eventObj.soldTickets[0]);
    if(!ticket){
      document.getElementById('ticket-card').innerHTML = '<p>Ticket not found.</p>';
      return;
    }

    renderTicket(ticket, eventObj);
    setupActions(ticket, eventObj);
  });
})();