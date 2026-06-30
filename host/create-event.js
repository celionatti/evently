document.addEventListener('DOMContentLoaded', ()=>{
  const ticketList = document.getElementById('ticket-list');
  const addTicketBtn = document.getElementById('add-ticket-btn');
  const guestList = document.getElementById('guest-list');
  const addGuestBtn = document.getElementById('add-guest-btn');
  const organizerPhone = document.getElementById('organizer-phone');
  const organizerSocial = document.getElementById('organizer-social');
  const form = document.getElementById('create-event-form');
  const authMessage = document.querySelector('.auth-message');

  // ── Custom URL / slug auto-generation ──────────────────
  const customUrlInput = document.getElementById('custom-url');
  let customTouched = false;

  function slugify(val){
    return (val||'').toLowerCase().trim()
      .replace(/['"`]/g,'')
      .replace(/[^a-z0-9\s-]/g,'')
      .replace(/\s+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
  }

  /** Generate a unique slug by appending a short suffix when needed */
  function generateUniqueSlug(base){
    let slug = slugify(base);
    if(!slug) slug = 'event-' + Date.now().toString(36);
    return slug;
  }

  if(customUrlInput){
    customUrlInput.addEventListener('input', ()=> customTouched = true);
  }

  const eventNameInput = document.getElementById('event-name');
  if(eventNameInput && customUrlInput){
    eventNameInput.addEventListener('input', ()=>{
      if(customTouched) return; // don't overwrite if user manually edited
      customUrlInput.value = slugify(eventNameInput.value || '');
    });
  }

  // ── Phone / social validation & masking ────────────────
  function digitsCount(val){ return (val||'').replace(/\D/g,'').length; }
  function validatePhone(val){ const d = digitsCount(val); return d === 0 || (d >= 7 && d <= 15); }
  function validateSocial(val){ if(!val) return true; val = val.trim(); if(val.startsWith('@')) return /^@[\w.]{2,30}$/.test(val); try{ const u = new URL(val); return u.protocol === 'http:' || u.protocol === 'https:'; }catch(e){ return false; } }

  function formatPhoneInput(input){
    if(!input) return;
    let v = input.value || '';
    const leadingPlus = v.trim().startsWith('+');
    let nums = v.replace(/[^\d]/g,'');
    if(nums.length === 0){ input.value = ''; return; }
    if(nums.length > 10){
      const cc = nums.slice(0, nums.length-10);
      const rest = nums.slice(-10);
      input.value = (leadingPlus?'+':'') + cc + ' ' + rest.slice(0,3) + ' ' + rest.slice(3,6) + ' ' + rest.slice(6);
    } else if(nums.length > 6){
      input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3,6) + ' ' + nums.slice(6);
    } else if(nums.length > 3){
      input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3);
    } else {
      input.value = (leadingPlus?'+':'') + nums;
    }
  }

  function attachPhoneMask(input){
    if(!input) return;
    input.addEventListener('input', ()=> formatPhoneInput(input));
    input.addEventListener('blur', ()=>{
      if(input.value && !validatePhone(input.value)) input.classList.add('invalid');
      else input.classList.remove('invalid');
    });
  }

  // ── Dynamic ticket rows ────────────────────────────────
  function createTicketRow(){
    const row = document.createElement('div');
    row.className = 'ticket-row';
    row.innerHTML = `
      <div class="ticket-row-grid">
        <label>Ticket name
          <input type="text" name="ticket-name[]" placeholder="General Admission" required>
        </label>
        <label>Price
          <input type="number" name="ticket-price[]" placeholder="45" min="0" required>
        </label>
        <label>Quantity
          <input type="number" name="ticket-qty[]" placeholder="100" min="1" required>
        </label>
        <label>Description
          <input type="text" name="ticket-desc[]" placeholder="VIP seating, early entry">
        </label>
      </div>
      <button type="button" class="ticket-remove">Remove</button>
    `;
    row.querySelector('.ticket-remove').addEventListener('click', ()=> row.remove());
    return row;
  }

  addTicketBtn.addEventListener('click', ()=> ticketList.appendChild(createTicketRow()));
  // start with one ticket row by default
  ticketList.appendChild(createTicketRow());

  // ── Dynamic guest / performer rows ─────────────────────
  function createGuestRow(){
    const row = document.createElement('div');
    row.className = 'guest-row';
    row.innerHTML = `
      <div class="guest-row-grid">
        <label>Name
          <input type="text" name="guest-name[]" placeholder="Artist Name" required>
        </label>
        <label>Role
          <input type="text" name="guest-role[]" placeholder="Performer / Speaker">
        </label>
        <label>Phone
          <input type="tel" name="guest-phone[]" placeholder="+1 555 555 5555">
        </label>
        <label>Social
          <input type="text" name="guest-social[]" placeholder="@handle or https://...">
        </label>
      </div>
      <button type="button" class="guest-remove">Remove</button>
    `;
    row.querySelector('.guest-remove').addEventListener('click', ()=> row.remove());
    const gp = row.querySelector('[name="guest-phone[]"]');
    const gs = row.querySelector('[name="guest-social[]"]');
    attachPhoneMask(gp);
    if(gs){ gs.addEventListener('blur', ()=>{ if(!validateSocial(gs.value)) gs.classList.add('invalid'); else gs.classList.remove('invalid'); }); }
    return row;
  }

  if(addGuestBtn && guestList){
    addGuestBtn.addEventListener('click', ()=> guestList.appendChild(createGuestRow()));
    // start with one guest row by default
    guestList.appendChild(createGuestRow());
  }

  // ── Organizer contact masking ──────────────────────────
  attachPhoneMask(organizerPhone);
  if(organizerSocial){
    organizerSocial.addEventListener('blur', ()=>{
      if(!validateSocial(organizerSocial.value)) organizerSocial.classList.add('invalid');
      else organizerSocial.classList.remove('invalid');
    });
  }

  // ── Client-side validation before native form submit ───
  // Lets the browser / PHP handle the actual submission.
  // We just do lightweight UX validation and slug finalization.
  if(form){
    form.addEventListener('submit', e=>{
      // Finalize the custom-url slug value before submit
      if(customUrlInput){
        const raw = customUrlInput.value.trim();
        customUrlInput.value = generateUniqueSlug(raw || (eventNameInput ? eventNameInput.value : ''));
      }

      // Quick client-side checks (PHP should re-validate)
      if(organizerPhone && !validatePhone(organizerPhone.value)){
        e.preventDefault();
        showMessage('Organizer phone appears invalid.', 'error');
        organizerPhone.focus();
        return;
      }
      if(organizerSocial && !validateSocial(organizerSocial.value)){
        e.preventDefault();
        showMessage('Organizer social/website looks invalid.', 'error');
        organizerSocial.focus();
        return;
      }

      // Validate guest contacts
      if(guestList){
        const rows = guestList.querySelectorAll('.guest-row');
        for(const row of rows){
          const phone = row.querySelector('[name="guest-phone[]"]');
          const social = row.querySelector('[name="guest-social[]"]');
          if(phone && !validatePhone(phone.value)){
            e.preventDefault();
            showMessage('One or more guest phone numbers look invalid.', 'error');
            phone.focus();
            return;
          }
          if(social && !validateSocial(social.value)){
            e.preventDefault();
            showMessage('One or more guest social links look invalid.', 'error');
            social.focus();
            return;
          }
        }
      }

      // If all checks pass, let the form submit natively to PHP
    });
  }

  // ── Helpers ────────────────────────────────────────────
  function showMessage(text, type='success'){
    if(!authMessage) return;
    authMessage.textContent = text;
    authMessage.className = 'auth-message ' + type;
  }
});
