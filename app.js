// Theme toggling and small interactive behaviors
(function(){
  const root = document.documentElement;
  const themeButtons = Array.from(document.querySelectorAll('[data-theme-toggle]'));

  function setTheme(name){
    root.setAttribute('data-theme', name);
    try{ localStorage.setItem('theme', name); }catch(e){}
  }

  function updateThemeButtons(){
    const isDark = root.getAttribute('data-theme') === 'dark';
    themeButtons.forEach(btn => {
      btn.setAttribute('aria-pressed', String(isDark));
      btn.classList.toggle('is-dark', isDark);
    });
  }

  // Initialize theme from storage or system
  const saved = localStorage.getItem('theme');
  if(saved){ setTheme(saved); }
  else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){ setTheme('dark'); }
  else { setTheme('light'); }

  updateThemeButtons();

  themeButtons.forEach(button => button.addEventListener('click', ()=>{
    const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    setTheme(current);
    updateThemeButtons();
    const announcer = document.getElementById('theme-announcer');
    if(announcer) announcer.textContent = `Switched to ${current} mode`;
  }));

  // Reflect visual class when theme changes (in case of external changes)
  const obs = new MutationObserver(()=>{
    const isDark = root.getAttribute('data-theme') === 'dark';
    themeButtons.forEach(btn => btn.classList.toggle('is-dark', isDark));
  });
  obs.observe(root, {attributes:true, attributeFilter:['data-theme']});

  // NAV active-state sync between desktop links and bottom nav
  const navLinks = Array.from(document.querySelectorAll('.nav-link'));
  const bottomItems = Array.from(document.querySelectorAll('.bottom-nav .nav-item[data-nav-index]'));

  // Highlight desktop top-nav links based on current pathname
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  navLinks.forEach(a => {
    if (a.dataset.navIndex === undefined) {
      const href = a.getAttribute('href');
      if (href && (href === currentPath || href.endsWith('/' + currentPath))) {
        a.setAttribute('aria-current', 'true');
      } else {
        a.removeAttribute('aria-current');
      }
    }
  });

  function setActive(index){
    navLinks.forEach(a=>{
      if (a.dataset.navIndex !== undefined) {
        if(a.dataset.navIndex === String(index)) a.setAttribute('aria-current','true');
        else a.removeAttribute('aria-current');
      }
    });
    bottomItems.forEach(b=>{
      if(b.dataset.navIndex === String(index)) b.classList.add('active');
      else b.classList.remove('active');
      if(b.dataset.navIndex === String(index)) b.setAttribute('aria-current','true');
      else b.removeAttribute('aria-current');
    });
  }

  // init active based on existing active item or default 0
  (function initActive(){
    const cur = document.querySelector('.bottom-nav .nav-item.active');
    if(cur) setActive(cur.dataset.navIndex || 0);
    else setActive(0);
  })();

  // clicks on desktop links
  navLinks.forEach(a=>{
    a.addEventListener('click', (e)=>{
      if (a.dataset.navIndex !== undefined) {
        const idx = a.dataset.navIndex; setActive(idx);
      }
    });
    a.addEventListener('keydown', (e)=>{ if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); a.click(); } });
  });

  // clicks on bottom nav
  bottomItems.forEach(b=>{
    b.addEventListener('click', ()=> setActive(b.dataset.navIndex));
    b.addEventListener('keydown', (e)=>{ if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); b.click(); } });
  });

  // Mobile menu toggle helper
  function setupMobileMenu(btnId, bottomClass){
    const btn = document.getElementById(btnId);
    const bottom = document.querySelector(bottomClass);
    if(!btn || !bottom) return;
    btn.addEventListener('click', ()=>{
      const open = bottom.classList.toggle('visible');
      btn.setAttribute('aria-expanded', String(open));
    });
    bottom.addEventListener('click', (e)=>{
      if(e.target.closest('.nav-item') || e.target.closest('.nav-link')){
        bottom.classList.remove('visible');
        btn.setAttribute('aria-expanded','false');
      }
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        bottom.classList.remove('visible');
        btn.setAttribute('aria-expanded','false');
      }
    });
  }

  setupMobileMenu('host-mobile-menu', '.host-bottom');
  setupMobileMenu('admin-mobile-menu', '.admin-bottom');

  // Dynamic carousel: render featured events, indicators, autoplay, and interaction handling
  const carousel = document.getElementById('carousel');
  const eventListContainer = document.getElementById('event-list');
  const paginationContainer = document.getElementById('pagination');
  const listMeta = document.getElementById('list-meta');
  const searchForm = document.getElementById('search-form');
  const searchInput = document.getElementById('search-input');
  const locationSelect = document.querySelector('.search select');

  if (carousel) {
    const slides = document.getElementById('slides');
    const prev = carousel.querySelector('.carousel-prev');
    const next = carousel.querySelector('.carousel-next');
    const indicators = document.getElementById('carousel-indicators');
    const playPause = document.getElementById('play-pause');

    // Events data: try to load from external `events.json`, fallback to default list
    let fullEvents = [];
    let filteredEvents = [];
    let featuredEvents = [];
    let currentPage = 1;
    const pageSize = 6;
    const defaultEvents = [
      {title:'Summer Beats Festival', date:'Aug 21', venue:'Central Park', city:'New York', image:'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=1'},
      {title:'Broadway Nights', date:'Sep 12', venue:'Downtown Theater', city:'New York', image:'https://images.unsplash.com/photo-1518972559570-6c07c1c4f9f7?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=2'},
      {title:'Championship Game', date:'Oct 3', venue:'City Stadium', city:'Los Angeles', image:'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=3'},
      {title:'Indie Rock Night', date:'Jul 7', venue:'The Garden Stage', city:'Chicago', image:'https://images.unsplash.com/photo-1507878866276-a947ef722fee?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=4'},
      {title:'Comedy Gala', date:'Nov 5', venue:'Laugh House', city:'Los Angeles', image:'https://images.unsplash.com/photo-1546456073-6712f79251bb?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=5'},
      {title:'Street Food Fest', date:'Jun 30', venue:'Riverside', city:'Chicago', image:'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=6'},
      {title:'Summer Jazz Series', date:'Aug 5', venue:'Harbor Stage', city:'New York', image:'https://images.unsplash.com/photo-1505576391880-67f7da0f8f0a?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=7'},
      {title:'Outdoor Cinema', date:'Jul 21', venue:'Lawn Park', city:'Los Angeles', image:'https://images.unsplash.com/photo-1505685296765-3a2736de412f?w=1200&q=80&auto=format&fit=crop&ixlib=rb-4.0.3&s=8'}
    ];
    let currentIndex = 0;
    let autoplayId = null;
    let autoplayEnabled = true;
    let playing = false;
    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const AUTOPLAY_MS = 4000;

    async function loadEvents(){
      try{
        const resp = await fetch('events.json');
        if(resp.ok){
          const data = await resp.json();
          if(Array.isArray(data) && data.length) { fullEvents = data; return; }
        }
      }catch(e){/* ignore and fallback */}
      fullEvents = defaultEvents;
    }

    function renderSlides(){
      slides.innerHTML = '';
      featuredEvents.forEach(ev => {
        const link = document.createElement('a');
        link.href = 'details.html';
        link.className = 'event-card-link';
        const art = document.createElement('article');
        art.className = 'event-card';
        art.innerHTML = `<div class="image" style="background-image:linear-gradient(0deg,rgba(0,0,0,.35),rgba(0,0,0,.08)), url('${ev.image}')"></div><div class="meta"><h3>${ev.title}</h3><p>${ev.date} • ${ev.venue}</p></div>`;
        link.appendChild(art);
        slides.appendChild(link);
      });
    }

    function renderIndicators(){
      indicators.innerHTML = '';
      featuredEvents.forEach((_, i) => {
        const btn = document.createElement('button');
        btn.className = 'indicator';
        btn.setAttribute('role','tab');
        btn.setAttribute('aria-selected', i===0 ? 'true' : 'false');
        btn.addEventListener('click', ()=> scrollToIndex(i));
        btn.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); btn.click(); } });
        indicators.appendChild(btn);
      });
    }

    function renderEventList(){
      if(!eventListContainer) return;
      eventListContainer.innerHTML = '';
      const start = (currentPage - 1) * pageSize;
      const pageEvents = filteredEvents.slice(start, start + pageSize);
      if(pageEvents.length === 0){
        eventListContainer.innerHTML = '<p class="empty-state">No events match your search.</p>';
        paginationContainer.innerHTML = '';
        if(listMeta) listMeta.textContent = `Showing 0 of ${filteredEvents.length} events`;
        return;
      }
      pageEvents.forEach(ev => {
        const item = document.createElement('article');
        item.className = 'event-item';
        item.innerHTML = `<div class="image" style="background-image:url('${ev.image}')"></div><div class="details"><div><h3>${ev.title}</h3><p class="meta">${ev.date} • ${ev.venue} • ${ev.city || 'Unknown'}</p></div><a href="details.html" class="action">View Tickets</a></div>`;
        eventListContainer.appendChild(item);
      });
      renderPagination();
      if(listMeta) listMeta.textContent = `Showing ${start + 1}-${Math.min(start + pageSize, filteredEvents.length)} of ${filteredEvents.length} events`;
    }

    function renderPagination(){
      if(!paginationContainer) return;
      const totalPages = Math.max(1, Math.ceil(filteredEvents.length / pageSize));
      paginationContainer.innerHTML = '';
      const prevButton = document.createElement('button');
      prevButton.className = 'page-button';
      prevButton.textContent = 'Prev';
      prevButton.disabled = currentPage === 1;
      prevButton.addEventListener('click', ()=> changePage(currentPage - 1));
      paginationContainer.appendChild(prevButton);
      for(let i = 1; i <= totalPages; i++){
        const pageButton = document.createElement('button');
        pageButton.className = 'page-button';
        pageButton.textContent = String(i);
        pageButton.setAttribute('aria-current', String(currentPage === i));
        pageButton.addEventListener('click', ()=> changePage(i));
        paginationContainer.appendChild(pageButton);
      }
      const nextButton = document.createElement('button');
      nextButton.className = 'page-button';
      nextButton.textContent = 'Next';
      nextButton.disabled = currentPage === totalPages;
      nextButton.addEventListener('click', ()=> changePage(currentPage + 1));
      paginationContainer.appendChild(nextButton);
    }

    function changePage(page){
      const totalPages = Math.max(1, Math.ceil(filteredEvents.length / pageSize));
      currentPage = Math.min(Math.max(page, 1), totalPages);
      renderEventList();
    }

    function scrollToIndex(i){
      const item = slides.children[i];
      if(!item) return;
      const left = item.offsetLeft - (parseFloat(getComputedStyle(slides).paddingLeft) || 0);
      const behavior = (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) ? 'auto' : 'smooth';
      slides.scrollTo({left, behavior});
      updateIndicators(i);
      updateButtons();
      currentIndex = i;
    }

    function updateIndicators(active){
      Array.from(indicators.children).forEach((b, idx)=> b.setAttribute('aria-selected', String(idx===active)));
    }

    function getCurrentIndex(){
      const center = slides.scrollLeft + slides.clientWidth / 2;
      let nearest = 0; let min = Infinity;
      Array.from(slides.children).forEach((child, idx)=>{
        const c = child.offsetLeft + child.offsetWidth/2;
        const d = Math.abs(center - c);
        if(d < min){ min = d; nearest = idx; }
      });
      return nearest;
    }

    let raf = null;
    slides.addEventListener('scroll', ()=>{
      if(raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(()=>{
        const idx = getCurrentIndex();
        updateIndicators(idx);
        updateButtons();
        currentIndex = idx;
      });
    }, {passive:true});

    function updateButtons(){
      const maxIndex = slides.children.length - 1;
      if(prev) prev.disabled = currentIndex <= 0;
      if(next) next.disabled = currentIndex >= maxIndex;
    }

    prev?.addEventListener('click', ()=> scrollToIndex(Math.max(0, currentIndex - 1)));
    next?.addEventListener('click', ()=> scrollToIndex(Math.min(featuredEvents.length - 1, currentIndex + 1)));

    function updateAutoplayUI(){
      if(playPause) playPause.setAttribute('aria-pressed', String(playing));
      if(playPause) playPause.textContent = playing ? '⏸' : '▶';
    }

    function startAutoplay(){
      if(prefersReduced || !autoplayEnabled) return;
      stopAutoplay();
      playing = true;
      updateAutoplayUI();
      autoplayId = setInterval(()=>{
        if(featuredEvents.length === 0) return;
        const nextIndex = (currentIndex + 1) % featuredEvents.length;
        scrollToIndex(nextIndex);
      }, AUTOPLAY_MS);
    }
    function stopAutoplay(){ if(autoplayId) { clearInterval(autoplayId); autoplayId = null; } playing = false; updateAutoplayUI(); }

    playPause?.addEventListener('click', ()=>{
      if(playing) stopAutoplay(); else startAutoplay();
    });

    ['mouseenter','touchstart','focusin'].forEach(evt => slides.addEventListener(evt, ()=>{ if(playing) stopAutoplay(); }, {passive:true}));
    ['mouseleave','touchend','focusout'].forEach(evt => slides.addEventListener(evt, ()=>{ if(autoplayEnabled && !prefersReduced) startAutoplay(); }, {passive:true}));
    document.addEventListener('visibilitychange', ()=>{ if(document.hidden) stopAutoplay(); else startAutoplay(); });

    function applyFilter(){
      const query = searchInput?.value.trim().toLowerCase() || '';
      const locationValue = locationSelect?.value || 'Nearby';
      filteredEvents = fullEvents.filter(ev => {
        const matchesQuery = query.length === 0 || ev.title.toLowerCase().includes(query) || ev.venue.toLowerCase().includes(query) || (ev.city||'').toLowerCase().includes(query);
        const matchesLocation = locationValue === 'Nearby' || (ev.city || '').toLowerCase().includes(locationValue.toLowerCase());
        return matchesQuery && matchesLocation;
      });
      currentPage = 1;
      renderEventList();
    }

    searchForm?.addEventListener('submit', (ev)=>{ ev.preventDefault(); applyFilter(); });
    searchInput?.addEventListener('input', ()=> applyFilter());
    locationSelect?.addEventListener('change', ()=> applyFilter());

    loadEvents().then(()=>{
      filteredEvents = fullEvents.slice();
      featuredEvents = fullEvents.slice(0, 5);
      renderSlides();
      renderIndicators();
      renderEventList();
      setTimeout(()=>{ updateButtons(); startAutoplay(); }, 80);
    });
  }

  // Header background on scroll to avoid content showing through
  const header = document.querySelector('.top-nav');
  function onScroll(){
    if(!header) return;
    header.classList.toggle('scrolled', window.scrollY > 8);
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
})();
