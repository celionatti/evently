// Host Dashboard — DOM-based pagination & filtering
(function(){
  const tbody = document.getElementById('events-tbody');
  const paginationEl = document.getElementById('events-pagination');
  const filterSelect = document.getElementById('event-status-filter');
  const tableContainer = document.getElementById('events-table-container');
  const emptyEvents = document.getElementById('empty-events');

  const allRows = Array.from(tbody.querySelectorAll('tr'));
  const PAGE_SIZE = 5;
  let currentPage = 1;
  let filteredRows = allRows;

  // Filter rows by data-status attribute
  function applyFilter(){
    const status = filterSelect.value;
    filteredRows = status
      ? allRows.filter(r => r.dataset.status === status)
      : allRows;
    currentPage = 1;
    render();
  }

  // Show/hide rows for current page
  function render(){
    // Hide all rows first
    allRows.forEach(r => r.style.display = 'none');

    if(filteredRows.length === 0){
      tableContainer.style.display = 'none';
      paginationEl.style.display = 'none';
      emptyEvents.style.display = 'block';
      return;
    }

    tableContainer.style.display = '';
    emptyEvents.style.display = 'none';

    const totalPages = Math.ceil(filteredRows.length / PAGE_SIZE);
    const start = (currentPage - 1) * PAGE_SIZE;
    const end = start + PAGE_SIZE;

    // Show only current page rows
    filteredRows.slice(start, end).forEach(r => r.style.display = '');

    renderPagination(totalPages);
  }

  // Build pagination controls
  function renderPagination(totalPages){
    paginationEl.innerHTML = '';

    if(totalPages <= 1){
      paginationEl.style.display = 'none';
      return;
    }

    paginationEl.style.display = 'flex';

    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-button';
    prevBtn.textContent = '← Prev';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', () => { currentPage--; render(); });
    paginationEl.appendChild(prevBtn);

    // Page number buttons
    for(let i = 1; i <= totalPages; i++){
      const btn = document.createElement('button');
      btn.className = 'page-button';
      btn.textContent = i;
      btn.disabled = currentPage === i;
      btn.setAttribute('aria-current', currentPage === i ? 'true' : 'false');
      btn.addEventListener('click', () => { currentPage = i; render(); });
      paginationEl.appendChild(btn);
    }

    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-button';
    nextBtn.textContent = 'Next →';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', () => { currentPage++; render(); });
    paginationEl.appendChild(nextBtn);
  }

  // Bind filter
  filterSelect.addEventListener('change', applyFilter);

  // Initial render
  render();
})();
