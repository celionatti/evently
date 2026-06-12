// Simple client-side mock auth for demo purposes only
(function(){
  function getUsers(){
    try{ return JSON.parse(localStorage.getItem('mock_users') || '{}'); }catch(e){ return {}; }
  }
  function saveUsers(u){ localStorage.setItem('mock_users', JSON.stringify(u)); }

  function showMessage(container, text, type='info'){
    if(!container) return;
    let msg = container.querySelector('.auth-message');
    if(!msg){ msg = document.createElement('div'); msg.className='auth-message'; container.prepend(msg); }
    msg.textContent = text;
    msg.className = 'auth-message ' + type;
  }

  // Register
  const regForm = document.getElementById('register-form');
  if(regForm){
    const authCard = regForm.closest('.auth-card');
    regForm.addEventListener('submit', e=>{
      e.preventDefault();
      const name = document.getElementById('reg-name').value.trim();
      const email = document.getElementById('reg-email').value.trim().toLowerCase();
      const pw = document.getElementById('reg-password').value;
      const users = getUsers();
      if(users[email]){ showMessage(authCard, 'An account with that email already exists.', 'error'); return; }
      users[email] = {name, password: pw, verified: false};
      saveUsers(users);
      showMessage(authCard, 'Account created. A verification email was sent (mock).', 'success');
      setTimeout(()=>{ location.href = 'verify.html?email='+encodeURIComponent(email); }, 1200);
    });
  }

  // Login
  const loginForm = document.getElementById('login-form');
  if(loginForm){
    const authCard = loginForm.closest('.auth-card');
    loginForm.addEventListener('submit', e=>{
      e.preventDefault();
      const email = document.getElementById('login-email').value.trim().toLowerCase();
      const pw = document.getElementById('login-password').value;
      const users = getUsers();
      const u = users[email];
      if(!u || u.password !== pw){ showMessage(authCard, 'Invalid credentials.', 'error'); return; }
      if(!u.verified){ showMessage(authCard, 'Please verify your email first.', 'error'); setTimeout(()=>{ location.href = 'verify.html?email='+encodeURIComponent(email); }, 1200); return; }
      showMessage(authCard, 'Signed in (mock).', 'success');
      setTimeout(()=>{ location.href = 'index.html'; }, 900);
    });
  }

  // Forgot
  const forgotForm = document.getElementById('forgot-form');
  if(forgotForm){
    const authCard = forgotForm.closest('.auth-card');
    forgotForm.addEventListener('submit', e=>{
      e.preventDefault();
      const email = document.getElementById('forgot-email').value.trim().toLowerCase();
      const users = getUsers();
      if(!users[email]){ showMessage(authCard, 'If this email exists, a reset link has been sent (mock).', 'info'); setTimeout(()=>{ location.href='login.html'; }, 1400); return; }
      // generate mock token
      const token = btoa(email+':'+Date.now()).replace(/=/g,'');
      // store token mapping
      const tokens = JSON.parse(localStorage.getItem('mock_reset')||'{}');
      tokens[token]=email;
      localStorage.setItem('mock_reset', JSON.stringify(tokens));
      showMessage(authCard, 'Reset link (mock) created.', 'success');
      setTimeout(()=>{ location.href = 'reset.html?token='+encodeURIComponent(token); }, 1200);
    });
  }

  // Reset
  const resetForm = document.getElementById('reset-form');
  if(resetForm){
    const authCard = resetForm.closest('.auth-card');
    resetForm.addEventListener('submit', e=>{
      e.preventDefault();
      const pw = document.getElementById('reset-password').value;
      const pw2 = document.getElementById('reset-password-confirm').value;
      if(pw !== pw2){ showMessage(authCard, 'Passwords do not match', 'error'); return; }
      const params = new URLSearchParams(location.search);
      const token = params.get('token');
      const tokens = JSON.parse(localStorage.getItem('mock_reset')||'{}');
      const email = tokens && tokens[token];
      if(!email){ showMessage(authCard, 'Invalid or expired token', 'error'); return; }
      const users = getUsers();
      if(users[email]){ users[email].password = pw; saveUsers(users); delete tokens[token]; localStorage.setItem('mock_reset', JSON.stringify(tokens)); showMessage(authCard, 'Password reset. Please sign in.', 'success'); setTimeout(()=>{ location.href='login.html'; }, 1200); }
    });
  }

  // Verify
  const verifyBtn = document.getElementById('verify-btn');
  if(verifyBtn){
    const authCard = verifyBtn.closest('.auth-card');
    verifyBtn.addEventListener('click', ()=>{
      const params = new URLSearchParams(location.search);
      const email = params.get('email');
      if(!email){ showMessage(authCard, 'No email specified', 'error'); return; }
      const users = getUsers();
      if(users[email]){ users[email].verified = true; saveUsers(users); showMessage(authCard, 'Email verified (mock). You may now sign in.', 'success'); setTimeout(()=>{ location.href='login.html'; }, 1200); }
      else showMessage(authCard, 'User not found', 'error');
    });
  }

})();
