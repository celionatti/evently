function getUsers(){ try{ return JSON.parse(localStorage.getItem('mock_users')||'{}'); }catch(e){ return {}; } }
function saveUsers(u){ localStorage.setItem('mock_users', JSON.stringify(u)); }

document.addEventListener('DOMContentLoaded', ()=>{
  const list = document.getElementById('payouts-list');
  const pager = document.getElementById('payouts-pagination');
  const pageSize = 6; let currentPage = 1;

  function render(){
    const usersObj = getUsers();
    const users = Object.entries(usersObj);
    if(users.length === 0){ list.innerHTML = '<p class="empty-state">No users available.</p>'; pager.innerHTML = ''; return; }
    const totalPages = Math.max(1, Math.ceil(users.length / pageSize));
    currentPage = Math.min(Math.max(1, currentPage), totalPages);
    const start = (currentPage - 1) * pageSize;
    const pageItems = users.slice(start, start + pageSize);
    list.innerHTML = '';
    pageItems.forEach(([email, u])=>{
      const bank = (u.organizer && u.organizer.bank) || {};
      const item = document.createElement('article');
      item.className = 'event-item';
      item.innerHTML = `
        <div class="details">
          <div><h3>${u.name || 'Unnamed'}</h3><p class="meta">${email}</p></div>
          <p class="meta">Bank: ${bank.name || 'Not configured'}</p>
          <p class="meta">Account: ${bank.account || '—'}</p>
          <p class="meta">Routing: ${bank.routing || '—'}</p>
          <p class="meta">Holder: ${bank.holder || '—'}</p>
          <div class="admin-actions">
            <button class="admin-action-btn" data-action="export" data-email="${email}">Export</button>
            <button class="admin-action-btn danger" data-action="delete" data-email="${email}">Delete</button>
          </div>
        </div>
      `;
      list.appendChild(item);
    });

    pager.innerHTML = '';
    const prev = document.createElement('button'); prev.className = 'page-button'; prev.textContent = 'Prev'; prev.disabled = currentPage===1; prev.addEventListener('click', ()=>{ currentPage--; render(); });
    pager.appendChild(prev);
    for(let i=1;i<=totalPages;i++){ const p = document.createElement('button'); p.className='page-button'; p.textContent=String(i); if(i===currentPage) p.setAttribute('aria-current','true'); p.addEventListener('click', ()=>{ currentPage = i; render(); }); pager.appendChild(p); }
    const next = document.createElement('button'); next.className = 'page-button'; next.textContent = 'Next'; next.disabled = currentPage===totalPages; next.addEventListener('click', ()=>{ currentPage++; render(); }); pager.appendChild(next);
  }

  list.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]'); if(!btn) return;
    const action = btn.dataset.action; const email = btn.dataset.email;
    if(action === 'delete'){
      if(!confirm(`Delete payout info for ${email}?`)) return;
      const users = getUsers(); delete users[email]; saveUsers(users); render();
    }
    if(action === 'export'){
      const users = getUsers(); const user = users[email]; if(!user) return;
      const blob = new Blob([JSON.stringify({email, bank: user.organizer?.bank||{}}, null, 2)], {type:'application/json'});
      const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `payout-${email}.json`; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }
  });

  render();
});