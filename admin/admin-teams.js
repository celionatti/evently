function getTeams(){
  try{
    const saved = localStorage.getItem('mock_teams');
    if(saved) return JSON.parse(saved);
  }catch(e){}
  
  // Default staff registry for demo/testing
  const defaults = [
    {id:"staff-1", name:"Alex Carter", email:"alex.carter@eventlyy.com", role:"Coordinator", assignedEvent:""},
    {id:"staff-2", name:"Sarah Connor", email:"sarah.c@eventlyy.com", role:"Security", assignedEvent:"Summer Beats Festival"},
    {id:"staff-3", name:"David Miller", email:"d.miller@eventlyy.com", role:"Support", assignedEvent:"Broadway Nights"},
    {id:"staff-4", name:"Elena Rostova", email:"elena.r@eventlyy.com", role:"Technical", assignedEvent:""}
  ];
  try{ localStorage.setItem('mock_teams', JSON.stringify(defaults)); }catch(e){}
  return defaults;
}

function saveTeams(teams){
  try{ localStorage.setItem('mock_teams', JSON.stringify(teams)); }catch(e){}
}

function getEvents(){
  try{
    const saved = localStorage.getItem('mock_events');
    if(saved){
      const parsed = JSON.parse(saved);
      if(Array.isArray(parsed) && parsed.length) return parsed;
    }
  }catch(e){}
  
  // Fallback default events list
  return [
    {name:"Summer Beats Festival"},
    {name:"Broadway Nights"},
    {name:"Championship Game"},
    {name:"Indie Rock Night"},
    {name:"Comedy Gala"},
    {name:"Street Food Fest"},
    {name:"Summer Jazz Series"},
    {name:"Outdoor Cinema"}
  ];
}

document.addEventListener('DOMContentLoaded', ()=>{
  const totalStaffEl = document.getElementById('total-staff');
  const assignedStaffEl = document.getElementById('assigned-staff');
  const unassignedStaffEl = document.getElementById('unassigned-staff');
  const activeEventsStaffEl = document.getElementById('active-events-staff');
  
  const addForm = document.getElementById('add-team-form');
  const assignForm = document.getElementById('assign-team-form');
  const addMessage = document.getElementById('add-message');
  const assignMessage = document.getElementById('assign-message');
  
  const memberNameInput = document.getElementById('member-name');
  const memberEmailInput = document.getElementById('member-email');
  const memberRoleSelect = document.getElementById('member-role');
  
  const assignMemberSelect = document.getElementById('assign-member-select');
  const assignEventSelect = document.getElementById('assign-event-select');
  
  const teamListContainer = document.getElementById('team-list');
  const teamPaginationContainer = document.getElementById('team-pagination');
  
  const pageSize = 6;
  let currentPage = 1;

  function showMessage(el, text, type="success"){
    if(!el) return;
    el.textContent = text;
    el.className = "auth-message " + type;
    el.style.display = "block";
    setTimeout(()=>{
      el.style.display = "none";
    }, 4000);
  }

  function populateDropdowns(teams, events){
    // Populate member dropdown for assignment
    const prevMemberSelection = assignMemberSelect.value;
    assignMemberSelect.innerHTML = '<option value="">-- Choose staff --</option>';
    teams.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = `${m.name} (${m.role})${m.assignedEvent ? ' - Assigned' : ''}`;
      assignMemberSelect.appendChild(opt);
    });
    assignMemberSelect.value = prevMemberSelection;

    // Populate event dropdown for assignment
    const prevEventSelection = assignEventSelect.value;
    assignEventSelect.innerHTML = '<option value="">-- Choose event --</option>';
    
    // Normalize event names (some defaults have 'title', saved events have 'name')
    const eventNames = Array.from(new Set(events.map(e => e.name || e.title).filter(Boolean)));
    eventNames.forEach(name => {
      const opt = document.createElement('option');
      opt.value = name;
      opt.textContent = name;
      assignEventSelect.appendChild(opt);
    });
    assignEventSelect.value = prevEventSelection;
  }

  function render(){
    const teams = getTeams();
    const events = getEvents();
    
    // 1. Calculate Metrics
    const totalStaff = teams.length;
    const assignedStaff = teams.filter(m => m.assignedEvent).length;
    const unassignedStaff = totalStaff - assignedStaff;
    
    const activeEventsSet = new Set();
    teams.forEach(m => {
      if(m.assignedEvent) activeEventsSet.add(m.assignedEvent);
    });
    const activeEventsStaff = activeEventsSet.size;

    totalStaffEl.textContent = totalStaff;
    assignedStaffEl.textContent = assignedStaff;
    unassignedStaffEl.textContent = unassignedStaff;
    activeEventsStaffEl.textContent = activeEventsStaff;

    // 2. Populate Select Dropdowns
    populateDropdowns(teams, events);

    // 3. Render Paginated Team List
    teamListContainer.innerHTML = '';
    if(totalStaff === 0){
      teamListContainer.innerHTML = '<p class="empty-state">No team members registered yet.</p>';
      teamPaginationContainer.innerHTML = '';
      return;
    }

    const totalPages = Math.max(1, Math.ceil(totalStaff / pageSize));
    currentPage = Math.min(Math.max(1, currentPage), totalPages);
    const start = (currentPage - 1) * pageSize;
    const pageItems = teams.slice(start, start + pageSize);

    pageItems.forEach((m) => {
      const item = document.createElement('article');
      item.className = 'event-item';
      
      const roleText = m.role === 'Technical' ? 'Technical Operations' : 
                       m.role === 'Support' ? 'Guest Support' : m.role;

      item.innerHTML = `
        <div class="details" style="display:flex; justify-content:space-between; align-items:center; width:100%; flex-wrap:wrap; gap:1rem;">
          <div>
            <h3>${m.name}</h3>
            <p class="meta">${m.email} • <span style="font-weight:600; color:rgb(var(--brand));">${roleText}</span></p>
            <p class="meta" style="margin-top:0.35rem;">
              Status: 
              ${m.assignedEvent 
                ? `<span class="panel-badge" style="background:rgba(var(--brand),0.08); color:rgb(var(--brand)); font-size:0.75rem; padding:0.2rem 0.5rem; display:inline-flex;">Coordinating: ${m.assignedEvent}</span>` 
                : '<span class="panel-badge" style="background:rgba(var(--muted),0.08); color:rgb(var(--muted)); font-size:0.75rem; padding:0.2rem 0.5rem; display:inline-flex;">Unassigned</span>'}
            </p>
          </div>
          <div class="admin-actions" style="margin-top:0;">
            ${m.assignedEvent ? `<button class="admin-action-btn" data-action="unassign" data-id="${m.id}">Unassign</button>` : ''}
            <button class="admin-action-btn danger" data-action="delete" data-id="${m.id}">Remove</button>
          </div>
        </div>
      `;
      teamListContainer.appendChild(item);
    });

    // 4. Render Pagination
    teamPaginationContainer.innerHTML = '';
    
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-button';
    prevBtn.textContent = 'Prev';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', ()=>{ currentPage--; render(); });
    teamPaginationContainer.appendChild(prevBtn);

    for(let i=1; i<=totalPages; i++){
      const pBtn = document.createElement('button');
      pBtn.className = 'page-button';
      pBtn.textContent = String(i);
      if(i === currentPage) pBtn.setAttribute('aria-current', 'true');
      pBtn.addEventListener('click', ()=>{ currentPage = i; render(); });
      teamPaginationContainer.appendChild(pBtn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-button';
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', ()=>{ currentPage++; render(); });
    teamPaginationContainer.appendChild(nextBtn);
  }

  // Handle adding a team member
  addForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const name = memberNameInput.value.trim();
    const email = memberEmailInput.value.trim();
    const role = memberRoleSelect.value;

    if(!name || !email || !role){
      showMessage(addMessage, 'Please fill in all fields.', 'error');
      return;
    }

    const teams = getTeams();
    
    // Check if email already registered in team
    if(teams.some(m => m.email.toLowerCase() === email.toLowerCase())){
      showMessage(addMessage, 'A team member with this email already exists.', 'error');
      return;
    }

    const newMember = {
      id: "staff-" + Date.now().toString(36),
      name,
      email,
      role,
      assignedEvent: ""
    };

    teams.push(newMember);
    saveTeams(teams);
    addForm.reset();
    showMessage(addMessage, 'Team member added successfully!', 'success');
    render();
  });

  // Handle assigning a team member to an event
  assignForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const memberId = assignMemberSelect.value;
    const eventName = assignEventSelect.value;

    if(!memberId || !eventName){
      showMessage(assignMessage, 'Please select both staff member and event.', 'error');
      return;
    }

    const teams = getTeams();
    const memberIdx = teams.findIndex(m => m.id === memberId);
    
    if(memberIdx === -1){
      showMessage(assignMessage, 'Staff member not found.', 'error');
      return;
    }

    teams[memberIdx].assignedEvent = eventName;
    saveTeams(teams);
    assignForm.reset();
    showMessage(assignMessage, 'Staff assigned successfully!', 'success');
    render();
  });

  // Handle actions (Unassign & Delete) via delegation
  teamListContainer.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]');
    if(!btn) return;

    const action = btn.dataset.action;
    const memberId = btn.dataset.id;
    const teams = getTeams();

    if(action === 'unassign'){
      const idx = teams.findIndex(m => m.id === memberId);
      if(idx !== -1){
        teams[idx].assignedEvent = "";
        saveTeams(teams);
        render();
      }
    } else if(action === 'delete'){
      const member = teams.find(m => m.id === memberId);
      if(!member) return;

      if(confirm(`Are you sure you want to remove ${member.name} from the team?`)){
        const filtered = teams.filter(m => m.id !== memberId);
        saveTeams(filtered);
        render();
      }
    }
  });

  render();
});
