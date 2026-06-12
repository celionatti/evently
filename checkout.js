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
