document.addEventListener('DOMContentLoaded', () => {
  const pmBtns = document.querySelectorAll('.pm-btn');
  const checkoutNote = document.querySelector('.checkout-note');
  if(!pmBtns || pmBtns.length === 0) return;

  pmBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      pmBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const label = btn.textContent.trim();
      if(checkoutNote) checkoutNote.textContent = `Pay with ${label} — you'll be redirected to complete payment.`;
    });
  });

  // default selection
  const defaultBtn = document.querySelector('.pm-btn[data-method="stripe"]');
  if(defaultBtn) defaultBtn.classList.add('active');
});

// Toggle card fields visibility when third-party payment methods are selected
document.addEventListener('DOMContentLoaded', () => {
  const cardFields = document.querySelector('.card-fields');
  const pmBtns = document.querySelectorAll('.pm-btn');
  if(!cardFields || !pmBtns) return;

  function setCardFieldsVisible(visible){
    if(visible){
      cardFields.setAttribute('aria-hidden','false');
      cardFields.classList.remove('hidden');
      Array.from(cardFields.querySelectorAll('input')).forEach(i=>{ i.disabled = false; i.required = true; });
    } else {
      cardFields.setAttribute('aria-hidden','true');
      cardFields.classList.add('hidden');
      Array.from(cardFields.querySelectorAll('input')).forEach(i=>{ i.disabled = true; i.required = false; });
    }
  }

  // For the listed providers we use redirect flows — hide card inputs
  pmBtns.forEach(btn => btn.addEventListener('click', () => {
    const method = btn.dataset.method;
    // If method is a redirect provider, hide card fields
    const redirectProviders = ['stripe','paystack','flutterwave'];
    setCardFieldsVisible(!redirectProviders.includes(method));
  }));

  // initialize visibility based on default active
  const active = document.querySelector('.pm-btn.active');
  setCardFieldsVisible(active ? !['stripe','paystack','flutterwave'].includes(active.dataset.method) : false);
});

// Handle checkout action: redirect to selected provider mock page for redirect providers
function checkoutMessage(container, text, type='info'){
  if(!container) return;
  let msg = container.querySelector('.checkout-message');
  if(!msg){ msg = document.createElement('div'); msg.className='checkout-message'; container.prepend(msg); }
  msg.textContent = text;
  msg.className = 'checkout-message ' + type;
}

document.addEventListener('DOMContentLoaded', ()=>{
  const checkoutBtn = document.querySelector('.checkout-btn');
  const checkoutForm = document.querySelector('.form-card');
  if(!checkoutBtn) return;
  checkoutBtn.addEventListener('click', (e)=>{
    // if link anchor, let it navigate normally
    if(checkoutBtn.tagName.toLowerCase() === 'a') return;
    e.preventDefault();
    const active = document.querySelector('.pm-btn.active');
    const method = active ? active.dataset.method : 'stripe';
    const redirectProviders = ['stripe','paystack','flutterwave'];
    if(redirectProviders.includes(method)){
      // in real app we'd send order to server and get a provider url
      window.location.href = `${method}-redirect.html?order=12345`;
    } else {
      checkoutMessage(checkoutForm, 'Proceeding with local payment flow (mock)', 'info');
    }
  });
});

// Render dynamic order summary based on selected tickets and fee settings
document.addEventListener('DOMContentLoaded', () => {
  const orderDataStr = localStorage.getItem('current_checkout_order');
  if (!orderDataStr) return;
  
  try {
    const orderData = JSON.parse(orderDataStr);
    const summaryCard = document.querySelector('.summary-card');
    if (!summaryCard) return;
    
    let html = `<div class="summary-row" style="font-weight: 700; font-size: 1.1rem; color: rgb(var(--text));"><span>${orderData.eventName}</span><span>${orderData.eventDate}</span></div>`;
    let subtotal = 0;
    
    // List tickets
    if (orderData.tickets && orderData.tickets.length > 0) {
      orderData.tickets.forEach(t => {
        const lineTotal = t.price * t.quantity;
        subtotal += lineTotal;
        html += `<div class="summary-row"><span>${t.name} × ${t.quantity}</span><span>$${lineTotal.toFixed(2)}</span></div>`;
      });
    } else {
      // Default fallback if no tickets selected (static fallback matching HTML default)
      subtotal = 45.00;
      html += `<div class="summary-row"><span>General Admission × 1</span><span>$45.00</span></div>`;
    }
    
    // Check fee configuration: default is to pass fee to attendee (checked)
    const passFee = orderData.passFeeToAttendee !== false;
    const fee = passFee ? (subtotal * 0.05) : 0;
    const total = subtotal + fee;
    
    html += `<div class="summary-divider"></div>`;
    
    if (passFee) {
      html += `<div class="summary-row"><span>Subtotal</span><span>$${subtotal.toFixed(2)}</span></div>`;
      html += `<div class="summary-row" style="color: rgb(var(--brand)); font-weight: 600;"><span>Ticketing Fee (5%)</span><span>$${fee.toFixed(2)}</span></div>`;
      html += `<div class="summary-divider"></div>`;
    } else {
      html += `<div class="summary-row" style="color: rgb(var(--muted)); font-style: italic;"><span>Ticketing Fee</span><span>Absorbed by Host</span></div>`;
      html += `<div class="summary-divider"></div>`;
    }
    
    html += `<div class="summary-total">
      <span>Total</span>
      <strong>$${total.toFixed(2)}</strong>
    </div>`;
    
    summaryCard.innerHTML = html;
  } catch (e) {
    console.error('Error rendering checkout order summary:', e);
  }
});
