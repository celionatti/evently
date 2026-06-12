function getUsers(){ try{ return JSON.parse(localStorage.getItem('mock_users')||'{}'); }catch(e){ return {}; } }
function getEvents(){ try{ return JSON.parse(localStorage.getItem('mock_events')||'[]'); }catch(e){ return []; } }

document.addEventListener('DOMContentLoaded', ()=>{
  const totalUsers = document.getElementById('total-users');
  const readyHosts = document.getElementById('ready-hosts');
  const totalEvents = document.getElementById('total-events');
  const totalTickets = document.getElementById('total-tickets');
  const adminEvents = document.getElementById('admin-events');
  const adminUsers = document.getElementById('admin-users');

  const users = getUsers();
  const events = getEvents();
  const userEntries = Object.entries(users);
  const completeHosts = userEntries.filter(([, user])=>{
    const bank = user.organizer && user.organizer.bank;
    return bank && bank.name && bank.account && bank.routing && bank.holder;
  });
  const ticketCount = events.reduce((sum, event)=>{
    if(Array.isArray(event.tickets)){
      return sum + event.tickets.reduce((total, ticket)=> total + (Number(ticket.quantity) || 0), 0);
    }
    return sum;
  }, 0);

  totalUsers.textContent = userEntries.length;
  readyHosts.textContent = completeHosts.length;
  totalEvents.textContent = events.length;
  totalTickets.textContent = ticketCount;

  if(events.length === 0){
    adminEvents.innerHTML = '<p class="empty-state">No published events yet.</p>';
  } else {
    adminEvents.innerHTML = '';
    events.forEach(event=>{
      const item = document.createElement('article');
      item.className = 'event-item';
      item.innerHTML = `
        <div class="details">
          <div>
            <h3>${event.name || 'Untitled event'}</h3>
            <p class="meta">${event.date || 'No date'} • ${event.venue || 'No venue'} • ${event.city || 'Unknown city'}</p>
          </div>
          <p class="meta">Owner: ${event.ownerEmail || 'Unknown'}</p>
          <p class="meta">Tickets: ${Array.isArray(event.tickets) ? event.tickets.length : 0} • Guests: ${Array.isArray(event.guests) ? event.guests.length : 0}</p>
          <p class="meta">Created: ${formatDate(event.createdAt)}</p>
        </div>
      `;
      adminEvents.appendChild(item);
    });
  }

  if(userEntries.length === 0){
    adminUsers.innerHTML = '<p class="empty-state">No registered users yet.</p>';
  } else {
    adminUsers.innerHTML = '';
    userEntries.forEach(([email, user])=>{
      const item = document.createElement('article');
      item.className = 'event-item';
      const bank = (user.organizer && user.organizer.bank) || {};
      item.innerHTML = `
        <div class="details">
          <div>
            <h3>${user.name || 'Unnamed user'}</h3>
            <p class="meta">${email}</p>
          </div>
          <p class="meta">Phone: ${user.organizer && user.organizer.phone ? user.organizer.phone : 'None'}</p>
          <p class="meta">Social: ${user.organizer && user.organizer.social ? user.organizer.social : 'None'}</p>
          <p class="meta">Bank: ${bank.name ? `${bank.name} • ${bank.account} • ${bank.routing}` : 'Not configured'}</p>
          <p class="meta">Payout holder: ${bank.holder || 'Not configured'}</p>
        </div>
      `;
      adminUsers.appendChild(item);
    });
  }
});

function formatDate(value){
  if(!value) return 'Unknown';
  const date = new Date(value);
  if(Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString(undefined, {year:'numeric',month:'short',day:'numeric'});
}


