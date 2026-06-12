// Simple profile editor that reads/writes mock_users and lists mock_events
(function(){
  function getUsers(){ try{ return JSON.parse(localStorage.getItem('mock_users')||'{}'); }catch(e){ return {}; } }
  function saveUsers(u){ localStorage.setItem('mock_users', JSON.stringify(u)); }
  function getEvents(){ try{ return JSON.parse(localStorage.getItem('mock_events')||'[]'); }catch(e){ return []; } }

  function showMessage(text, type='info'){
    const m = document.querySelector('.auth-message'); if(!m) return; m.textContent = text; m.className = 'auth-message ' + type;
  }

  function digitsCount(val){ return (val||'').replace(/\D/g,'').length; }
  function validatePhone(val){ const d = digitsCount(val); return d === 0 || (d >= 7 && d <= 15); }
  function validateSocial(val){ if(!val) return true; val = val.trim(); if(val.startsWith('@')) return /^@[\w.]{2,30}$/.test(val); try{ const u = new URL(val); return u.protocol === 'http:' || u.protocol === 'https:'; }catch(e){ return false; } }

  const params = new URLSearchParams(location.search);
  const emailParam = params.get('email');
  const users = getUsers();
  const email = emailParam || Object.keys(users)[0];
  const nameInput = document.getElementById('profile-name');
  const emailInput = document.getElementById('profile-email');
  const phoneInput = document.getElementById('profile-phone');
  const socialInput = document.getElementById('profile-social');
  const bankNameInput = document.getElementById('profile-bank-name');
  const bankAccountInput = document.getElementById('profile-bank-account');
  const bankRoutingInput = document.getElementById('profile-bank-routing');
  const bankHolderInput = document.getElementById('profile-bank-holder');
  const form = document.getElementById('profile-form');
  const hostEvents = document.getElementById('host-events');

  if(!email){ showMessage('No account available. Create one at register.', 'info'); return; }
  const u = users[email];
  if(!u){ showMessage('User not found. Use an existing email or register.', 'error'); return; }

  // populate
  nameInput.value = u.name || '';
  emailInput.value = email;
  phoneInput.value = (u.organizer && u.organizer.phone) || u.phone || '';
  socialInput.value = (u.organizer && u.organizer.social) || u.social || '';
  bankNameInput.value = (u.organizer && u.organizer.bank && u.organizer.bank.name) || '';
  bankAccountInput.value = (u.organizer && u.organizer.bank && u.organizer.bank.account) || '';
  bankRoutingInput.value = (u.organizer && u.organizer.bank && u.organizer.bank.routing) || '';
  bankHolderInput.value = (u.organizer && u.organizer.bank && u.organizer.bank.holder) || '';

  // simple formatter for phone
  function formatPhoneInput(input){ if(!input) return; let v = input.value || ''; const leadingPlus = v.trim().startsWith('+'); let nums = (v.replace(/[^\d]/g,'')); if(nums.length === 0){ input.value = ''; return; }
    if(nums.length > 10){ const cc = nums.slice(0, nums.length-10); const rest = nums.slice(-10); input.value = (leadingPlus?'+':'') + cc + ' ' + rest.slice(0,3) + ' ' + rest.slice(3,6) + ' ' + rest.slice(6); }
    else if(nums.length > 6){ input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3,6) + ' ' + nums.slice(6); }
    else if(nums.length > 3){ input.value = (leadingPlus?'+':'') + nums.slice(0,3) + ' ' + nums.slice(3); }
    else { input.value = (leadingPlus?'+':'') + nums; }
  }
  phoneInput.addEventListener('input', ()=> formatPhoneInput(phoneInput));
  socialInput.addEventListener('blur', ()=>{ if(!validateSocial(socialInput.value)) socialInput.classList.add('invalid'); else socialInput.classList.remove('invalid'); });

  form.addEventListener('submit', e=>{
    e.preventDefault();
    const newName = nameInput.value.trim();
    const newPhone = phoneInput.value.trim();
    const newSocial = socialInput.value.trim();
    if(!validatePhone(newPhone)){ showMessage('Phone looks invalid.', 'error'); phoneInput.focus(); return; }
    if(!validateSocial(newSocial)){ showMessage('Social/website looks invalid.', 'error'); socialInput.focus(); return; }
    users[email] = users[email] || {};
    users[email].name = newName;
    users[email].organizer = users[email].organizer || {};
    users[email].organizer.phone = newPhone;
    users[email].organizer.social = newSocial;
    users[email].organizer.bank = {
      name: bankNameInput.value.trim(),
      account: bankAccountInput.value.trim(),
      routing: bankRoutingInput.value.trim(),
      holder: bankHolderInput.value.trim()
    };
    saveUsers(users);
    showMessage('Profile updated.', 'success');
  });

  // render events (mock)
  const events = getEvents().filter(ev => ev.ownerEmail === email);
  hostEvents.innerHTML = '';
  if(!events || events.length === 0){ hostEvents.innerHTML = '<p class="muted">No events yet.</p>'; }
  else{
    events.forEach(ev => {
      const card = document.createElement('article');
      card.className = 'event-item';
      card.innerHTML = `<div class="image" style="background-image:url('${ev.image||'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=800&q=60&auto=format&fit=crop'}')"></div><div class="details"><div><h3>${ev.name||ev.title||'Untitled'}</h3><p class="meta">${ev.date||''} • ${ev.venue||''}</p></div><a href="details.html" class="action">View</a></div>`;
      hostEvents.appendChild(card);
    });
  }
})();
