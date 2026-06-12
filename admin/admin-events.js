function getEvents(){ try{ return JSON.parse(localStorage.getItem('mock_events')||'[]'); }catch(e){ return []; } }
function saveEvents(e){ localStorage.setItem('mock_events', JSON.stringify(e)); }

document.addEventListener('DOMContentLoaded', ()=>{
  const list = document.getElementById('events-list');
  const pager = document.getElementById('events-pagination');
  const pageSize = 6; let currentPage = 1;

  function render(){
    const events = getEvents();
    if(events.length === 0){ list.innerHTML = '<p class="empty-state">No events published.</p>'; pager.innerHTML = ''; return; }
    const totalPages = Math.max(1, Math.ceil(events.length / pageSize));
    currentPage = Math.min(Math.max(1, currentPage), totalPages);
    const start = (currentPage - 1) * pageSize;
    const pageItems = events.slice(start, start + pageSize);
    list.innerHTML = '';
    pageItems.forEach((ev, idx)=>{
      const globalIndex = start + idx;
      const item = document.createElement('article');
      item.className = 'event-item';
      item.innerHTML = `
        <div class="details">
          <div><h3>${ev.name || 'Untitled'}</h3><p class="meta">${ev.date || 'No date'} • ${ev.venue || 'No venue'}</p></div>
          <p class="meta">Owner: ${ev.ownerEmail || 'Unknown'}</p>
          <p class="meta">Tickets: ${Array.isArray(ev.tickets)? ev.tickets.length:0} • Guests: ${Array.isArray(ev.guests)? ev.guests.length:0}</p>
          <div class="admin-actions">
            <button class="admin-action-btn" data-action="export" data-idx="${globalIndex}">Export</button>
            <button class="admin-action-btn danger" data-action="delete" data-idx="${globalIndex}">Delete</button>
          </div>
        </div>
      `;
      list.appendChild(item);
    });

    // pagination
    pager.innerHTML = '';
    const prev = document.createElement('button'); prev.className = 'page-button'; prev.textContent = 'Prev'; prev.disabled = currentPage===1; prev.addEventListener('click', ()=>{ currentPage--; render(); });
    pager.appendChild(prev);
    for(let i=1;i<=totalPages;i++){ const p = document.createElement('button'); p.className='page-button'; p.textContent=String(i); if(i===currentPage) p.setAttribute('aria-current','true'); p.addEventListener('click', ()=>{ currentPage = i; render(); }); pager.appendChild(p); }
    const next = document.createElement('button'); next.className = 'page-button'; next.textContent = 'Next'; next.disabled = currentPage===totalPages; next.addEventListener('click', ()=>{ currentPage++; render(); }); pager.appendChild(next);
  }

  list.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]'); if(!btn) return;
    const action = btn.dataset.action; const idx = Number(btn.dataset.idx);
    if(action === 'delete'){
      if(!confirm('Delete this event?')) return;
      const events = getEvents(); events.splice(idx,1); saveEvents(events); render();
    }
    if(action === 'export'){
      const events = getEvents(); const ev = events[idx]; if(!ev) return;
      const blob = new Blob([JSON.stringify(ev, null, 2)], {type:'application/json'});
      const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `event-${(ev.name||'event').replace(/\s+/g,'-')}.json`; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }
  });

  render();
});