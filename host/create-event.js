document.addEventListener('DOMContentLoaded', ()=>{
  const ticketList = document.getElementById('ticket-list');
  const addTicketBtn = document.getElementById('add-ticket-btn');
  const guestList = document.getElementById('guest-list');
  const addGuestBtn = document.getElementById('add-guest-btn');
  const organizerPhone = document.getElementById('organizer-phone');
  const organizerSocial = document.getElementById('organizer-social');
  const form = document.getElementById('create-event-form');
  const authMessage = document.querySelector('.auth-message');
  const params = new URLSearchParams(location.search);

  // Custom URL / slug handling
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
  function ensureUniqueSlug(base, existing){
    let slug = slugify(base);
    if(!slug) slug = 'event-' + Date.now().toString(36);
    const taken = new Set(existing.map(e=> (e.customUrl || e.slug || '').replace(/^\/?events\//,'')));
    if(!taken.has(slug)) return slug;
    // append short timestamp suffix until unique
    let i = 1;
    while(taken.has(slug + '-' + i)) i++;
    return slug + '-' + i;
  }

  function getUsers(){ try{ return JSON.parse(localStorage.getItem('mock_users')||'{}'); }catch(e){ return {}; } }
  function getEvents(){ try{ return JSON.parse(localStorage.getItem('mock_events')||'[]'); }catch(e){ return []; } }
  function saveEvents(events){ localStorage.setItem('mock_events', JSON.stringify(events)); }
  const ownerEmail = params.get('email') || Object.keys(getUsers())[0] || null;

  // Phone/social validation & masking helpers
  function digitsCount(val){ return (val||'').replace(/\D/g,'').length; }
  function validatePhone(val){ const d = digitsCount(val); return d === 0 || (d >= 7 && d <= 15); }
  function validateSocial(val){ if(!val) return true; val = val.trim(); if(val.startsWith('@')) return /^@[\w.]{2,30}$/.test(val); try{ const u = new URL(val); return u.protocol === 'http:' || u.protocol === 'https:'; }catch(e){ return false; } }

  function formatPhoneInput(input){ if(!input) return; let v = input.value || ''; const leadingPlus = v.trim().startsWith('+'); let nums = (v.replace(/[^\d]/g,'')); if(nums.length === 0){ input.value = ''; return; }
    // basic groups: if length>10 assume country code
    if(nums.length > 10){ const cc = nums.slice(0, nums.length-10); const rest = nums.slice(-10); input.value = (leadingPlus?'+':'') + cc + ' ' + rest.slice(0,3) + ' ' + rest.slice(3,6) + ' ' + rest.slice(6); }
    else if(nums.length > 6){ input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3,6) + ' ' + nums.slice(6); }
    else if(nums.length > 3){ input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3); }
    else { input.value = (leadingPlus?'+':'') + nums; }
  }

  function attachPhoneMask(input){ if(!input) return; input.addEventListener('input', ()=> formatPhoneInput(input)); input.addEventListener('blur', ()=>{ if(input.value && !validatePhone(input.value)){ input.classList.add('invalid'); } else input.classList.remove('invalid'); }); }

  function createTicketRow(){
    const row = document.createElement('div');
    row.className = 'ticket-row';
    row.innerHTML = `
      <div class="ticket-row-grid">
        <label>Ticket name
          <input type="text" name="ticket-name" placeholder="General Admission" required>
        </label>
        <label>Price
          <input type="number" name="ticket-price" placeholder="45" min="0" required>
        </label>
        <label>Quantity
          <input type="number" name="ticket-qty" placeholder="100" min="1" required>
        </label>
        <label>Description
          <input type="text" name="ticket-desc" placeholder="VIP seating, early entry">
        </label>
      </div>
      <button type="button" class="ticket-remove">Remove</button>
    `;
    const removeBtn = row.querySelector('.ticket-remove');
    removeBtn.addEventListener('click', ()=> row.remove());
    return row;
  }

  function createGuestRow(){
    const row = document.createElement('div');
    row.className = 'guest-row';
    row.innerHTML = `
      <div class="guest-row-grid">
        <label>Name
          <input type="text" name="guest-name" placeholder="Artist Name" required>
        </label>
        <label>Role
          <input type="text" name="guest-role" placeholder="Performer / Speaker">
        </label>
        <label>Phone
          <input type="tel" name="guest-phone" placeholder="+1 555 555 5555">
        </label>
        <label>Social
          <input type="text" name="guest-social" placeholder="@handle or https://...">
        </label>
      </div>
      <button type="button" class="guest-remove">Remove</button>
    `;
    const removeBtn = row.querySelector('.guest-remove');
    removeBtn.addEventListener('click', ()=> row.remove());
    // attach masking and basic validation to guest phone/social
    const gp = row.querySelector('[name="guest-phone"]');
    const gs = row.querySelector('[name="guest-social"]');
    attachPhoneMask(gp);
    if(gs){ gs.addEventListener('blur', ()=>{ if(!validateSocial(gs.value)) gs.classList.add('invalid'); else gs.classList.remove('invalid'); }); }
    return row;
  }

  function showMessage(text, type='success'){
    if(!authMessage) return;
    authMessage.textContent = text;
    authMessage.className = 'auth-message ' + type;
  }

  addTicketBtn.addEventListener('click', ()=>{
    ticketList.appendChild(createTicketRow());
  });

  if(addGuestBtn && guestList){
    addGuestBtn.addEventListener('click', ()=> guestList.appendChild(createGuestRow()));
    // start with one guest row by default
    guestList.appendChild(createGuestRow());
  }

  if(customUrlInput){
    customUrlInput.addEventListener('input', ()=> customTouched = true);
  }

  const eventNameInput = document.getElementById('event-name');
  if(eventNameInput && customUrlInput){
    eventNameInput.addEventListener('input', ()=>{
      if(customTouched) return; // don't overwrite if user edited
      const s = slugify(eventNameInput.value || '');
      customUrlInput.value = s;
    });
  }

  // attach mask for organizer phone
  attachPhoneMask(organizerPhone);
  if(organizerSocial){ organizerSocial.addEventListener('blur', ()=>{ if(!validateSocial(organizerSocial.value)) organizerSocial.classList.add('invalid'); else organizerSocial.classList.remove('invalid'); }); }

  ticketList.appendChild(createTicketRow());

  form.addEventListener('submit', e=>{
    e.preventDefault();
    const eventData = {
      name: document.getElementById('event-name').value.trim(),
      date: document.getElementById('event-date').value,
      time: document.getElementById('event-time').value,
      venue: document.getElementById('event-venue').value.trim(),
      city: document.getElementById('event-city').value.trim(),
      description: document.getElementById('event-description').value.trim(),
      passFeeToAttendee: document.getElementById('pass-fee') ? document.getElementById('pass-fee').checked : true,
      organizer: {
        phone: organizerPhone ? organizerPhone.value.trim() : '',
        social: organizerSocial ? organizerSocial.value.trim() : ''
      },
      tickets: Array.from(ticketList.querySelectorAll('.ticket-row')).map(row => ({
        name: row.querySelector('[name="ticket-name"]').value.trim(),
        price: row.querySelector('[name="ticket-price"]').value,
        quantity: row.querySelector('[name="ticket-qty"]').value
        ,description: row.querySelector('[name="ticket-desc"]').value.trim()
      }))
      ,guests: guestList ? Array.from(guestList.querySelectorAll('.guest-row')).map(row => ({
        name: row.querySelector('[name="guest-name"]').value.trim(),
        role: row.querySelector('[name="guest-role"]').value.trim(),
        phone: row.querySelector('[name="guest-phone"]').value.trim(),
        social: row.querySelector('[name="guest-social"]').value.trim()
      })) : []
    };

    if(!eventData.name || !eventData.date || !eventData.venue){
      showMessage('Please complete all required event fields.', 'error');
      return;
    }

    // validate organizer contact
    if(!validatePhone(eventData.organizer.phone)){ showMessage('Organizer phone appears invalid.', 'error'); organizerPhone && organizerPhone.focus(); return; }
    if(!validateSocial(eventData.organizer.social)){ showMessage('Organizer social/website looks invalid.', 'error'); organizerSocial && organizerSocial.focus(); return; }

    // validate guests
    const badGuest = eventData.guests.find(g => !validatePhone(g.phone) || !validateSocial(g.social));
    if(badGuest){ showMessage('One or more guest contacts look invalid.', 'error'); return; }

    const existingEvents = getEvents();
    // determine slug/custom url
    const slugBase = customUrlInput && customUrlInput.value ? customUrlInput.value : eventData.name;
    const slug = ensureUniqueSlug(slugBase, existingEvents);
    const fullPath = 'events/' + slug;

    existingEvents.push({
      ...eventData,
      customUrl: fullPath,
      slug: slug,
      ownerEmail: ownerEmail || null,
      createdAt: new Date().toISOString()
    });
    saveEvents(existingEvents);

    showMessage('Event published successfully! It is now saved to your profile.', 'success');
    console.log('Mock event created:', eventData);
  });
});
