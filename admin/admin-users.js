function getUsers(){ try{ return JSON.parse(localStorage.getItem('mock_users')||'{}'); }catch(e){ return {}; } }
function saveUsers(u){ localStorage.setItem('mock_users', JSON.stringify(u)); }

document.addEventListener('DOMContentLoaded', ()=>{
  const list = document.getElementById('users-list');
  const pager = document.getElementById('users-pagination');
  const pageSize = 6;
  let currentPage = 1;

  function render(){
    const usersObj = getUsers();
    const users = Object.entries(usersObj);
    if(users.length === 0){ list.innerHTML = '<p class="empty-state">No users found.</p>'; pager.innerHTML = ''; return; }
    const totalPages = Math.max(1, Math.ceil(users.length / pageSize));
    currentPage = Math.min(Math.max(1, currentPage), totalPages);
    const start = (currentPage - 1) * pageSize;
    const pageItems = users.slice(start, start + pageSize);

    list.innerHTML = '';
    pageItems.forEach(([email, u])=>{
      const item = document.createElement('article');
      item.className = 'event-item';
      const bank = (u.organizer && u.organizer.bank) || {};
      item.innerHTML = `
        <div class="details">
          <div><h3>${u.name || 'Unnamed'}</h3><p class="meta">${email}</p></div>
          <p class="meta">Phone: ${u.organizer && u.organizer.phone ? u.organizer.phone : '—'}</p>
          <p class="meta">Social: ${u.organizer && u.organizer.social ? u.organizer.social : '—'}</p>
          <p class="meta">Bank: ${bank.name ? bank.name + ' • ' + (bank.account||'') : 'Not configured'}</p>
          <div class="admin-actions">
            <button class="admin-action-btn" data-action="export" data-email="${email}">Export</button>
            <button class="admin-action-btn danger" data-action="delete" data-email="${email}">Delete</button>
          </div>
        </div>
      `;
      list.appendChild(item);
    });

    // render pagination
    pager.innerHTML = '';
    const prev = document.createElement('button'); prev.className = 'page-button'; prev.textContent = 'Prev'; prev.disabled = currentPage===1; prev.addEventListener('click', ()=>{ currentPage--; render(); });
    pager.appendChild(prev);
    for(let i=1;i<=totalPages;i++){
      const p = document.createElement('button'); p.className = 'page-button'; p.textContent = String(i); if(i===currentPage) p.setAttribute('aria-current','true'); p.addEventListener('click', ()=>{ currentPage = i; render(); }); pager.appendChild(p);
    }
    const next = document.createElement('button'); next.className = 'page-button'; next.textContent = 'Next'; next.disabled = currentPage===totalPages; next.addEventListener('click', ()=>{ currentPage++; render(); }); pager.appendChild(next);
  }

  // actions: export, delete
  list.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]'); if(!btn) return;
    const action = btn.dataset.action; const email = btn.dataset.email;
    if(action === 'delete'){
      if(!confirm(`Delete user ${email}? This cannot be undone.`)) return;
      const users = getUsers(); delete users[email]; saveUsers(users); render();
    }
    if(action === 'export'){
      const users = getUsers(); const user = users[email]; if(!user) return;
      const blob = new Blob([JSON.stringify({email, user}, null, 2)], {type:'application/json'});
      const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `user-${email}.json`; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }
  });

  render();
});