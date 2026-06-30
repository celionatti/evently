document.addEventListener('DOMContentLoaded', () => {
  const ticketRows = document.querySelectorAll('.ticket-row');
  const summaryItems = document.getElementById('summary-items');
  const orderTotal = document.getElementById('order-total');
  const ticketsForm = document.getElementById('tickets-form');

  function updateSummary() {
    let total = 0;
    summaryItems.innerHTML = '';

    ticketRows.forEach(row => {
      const price = Number(row.dataset.price || 0);
      const label = row.dataset.label || '';
      const quantity = Number(row.querySelector('.qty-value').textContent.trim() || 0);
      const lineTotal = price * quantity;

      if (quantity > 0) {
        total += lineTotal;
        const item = document.createElement('div');
        item.className = 'summary-row';
        item.innerHTML = `<span>${quantity} × ${label}</span><span>$${lineTotal}</span>`;
        summaryItems.appendChild(item);
      }
    });

    if (!summaryItems.childElementCount) {
      const empty = document.createElement('p');
      empty.className = 'summary-empty';
      empty.textContent = 'No tickets selected yet.';
      summaryItems.appendChild(empty);
    }

    orderTotal.textContent = `$${total}`;
  }

  ticketRows.forEach(row => {
    row.addEventListener('click', event => {
      const btn = event.target.closest('.qty-btn');
      if (!btn) return;
      
      const valueEl = row.querySelector('.qty-value');
      const inputEl = row.querySelector('.qty-input');
      let quantity = Number(valueEl.textContent.trim() || 0);

      if (btn.dataset.action === 'increase') {
        quantity += 1;
      } else if (btn.dataset.action === 'decrease') {
        quantity = Math.max(0, quantity - 1);
      }

      valueEl.textContent = String(quantity);
      if (inputEl) {
        inputEl.value = String(quantity);
      }
      
      updateSummary();
    });
  });

  // Calculate initial summary on page load
  updateSummary();

  // Save selected tickets and event configuration to localStorage on checkout form submit
  if (ticketsForm) {
    ticketsForm.addEventListener('submit', () => {
      const selectedTickets = [];
      ticketRows.forEach(row => {
        const price = Number(row.dataset.price || 0);
        const label = row.dataset.label || '';
        const quantity = Number(row.querySelector('.qty-value').textContent.trim() || 0);
        if (quantity > 0) {
          selectedTickets.push({ name: label, price: price, quantity: quantity });
        }
      });
      
      const events = getEvents();
      const ev = events && events.length ? events[0] : null;
      
      const checkoutOrder = {
        eventName: document.querySelector('.detail-hero-copy h1') ? document.querySelector('.detail-hero-copy h1').textContent.trim() : (ev ? ev.name : 'Summer Beats Festival'),
        eventDate: ev ? ev.date : 'Aug 21',
        tickets: selectedTickets,
        passFeeToAttendee: ev ? (ev.passFeeToAttendee !== false) : true
      };
      
      localStorage.setItem('current_checkout_order', JSON.stringify(checkoutOrder));
    });
  }

  // Render guests from mock events if available
  function getEvents(){ try{ return JSON.parse(localStorage.getItem('mock_events')||'[]'); }catch(e){ return []; } }
  const guestsContainer = document.getElementById('guests-list');
  function renderGuests(){
    if(!guestsContainer) return;
    const events = getEvents();
    const ev = events && events.length ? events[0] : null;
    guestsContainer.innerHTML = '';
    const guests = ev && ev.guests && ev.guests.length ? ev.guests : [
      {name:'DJ Nova', role:'Headline DJ', phone:'', social:'@djnova'},
      {name:'Luna Rose', role:'Guest Artist', phone:'', social:''}
    ];
    guests.forEach(g => {
      const card = document.createElement('div');
      card.className = 'guest-card';
      card.innerHTML = `<h3>${g.name}</h3><p class="meta">${g.role || ''}</p><p class="meta">${g.phone || ''} ${g.social?'<br/>'+g.social:''}</p>`;
      guestsContainer.appendChild(card);
    });
  }
  renderGuests();
});
