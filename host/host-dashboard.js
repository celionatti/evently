// Host Dashboard
(function(){
  const eventsGrid = document.getElementById('events-grid');
  const emptyEvents = document.getElementById('empty-events');
  const eventStatusFilter = document.getElementById('event-status-filter');
  const totalEventsCount = document.getElementById('total-events-count');
  const totalRevenue = document.getElementById('total-revenue');
  const totalTicketsSold = document.getElementById('total-tickets-sold');
  const activeEventsCount = document.getElementById('active-events-count');

  // Mock data for hosted events
  const mockHostedEvents = [
    {
      id: 1,
      title: 'Summer Beats Festival',
      date: 'Aug 21, 2026',
      time: '6:00 PM',
      venue: 'Central Park',
      city: 'New York',
      image: 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&q=80&auto=format&fit=crop',
      status: 'upcoming',
      ticketsTotal: 500,
      ticketsSold: 342,
      revenue: 17100,
      description: 'An amazing summer festival featuring top artists and live performances.',
      ticketPrice: 50
    },
    {
      id: 2,
      title: 'Broadway Nights',
      date: 'Sep 12, 2026',
      time: '8:00 PM',
      venue: 'Downtown Theater',
      city: 'New York',
      image: 'https://images.unsplash.com/photo-1518972559570-6c07c1c4f9f7?w=400&q=80&auto=format&fit=crop',
      status: 'upcoming',
      ticketsTotal: 300,
      ticketsSold: 245,
      revenue: 14700,
      description: 'Experience the magic of Broadway with top theatrical productions.',
      ticketPrice: 60
    },
    {
      id: 3,
      title: 'Championship Game',
      date: 'Oct 3, 2026',
      time: '7:30 PM',
      venue: 'City Stadium',
      city: 'Los Angeles',
      image: 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=400&q=80&auto=format&fit=crop',
      status: 'upcoming',
      ticketsTotal: 8000,
      ticketsSold: 6543,
      revenue: 393180,
      description: 'Witness the most anticipated sports event of the season.',
      ticketPrice: 60
    },
    {
      id: 4,
      title: 'Indie Rock Night',
      date: 'Jul 7, 2026',
      time: '9:00 PM',
      venue: 'The Garden Stage',
      city: 'Chicago',
      image: 'https://images.unsplash.com/photo-1507878866276-a947ef722fee?w=400&q=80&auto=format&fit=crop',
      status: 'live',
      ticketsTotal: 250,
      ticketsSold: 248,
      revenue: 9920,
      description: 'Discover emerging indie bands and enjoy live rock music.',
      ticketPrice: 40
    },
    {
      id: 5,
      title: 'Comedy Gala',
      date: 'Jun 15, 2026',
      time: '8:00 PM',
      venue: 'Laugh House',
      city: 'Los Angeles',
      image: 'https://images.unsplash.com/photo-1546456073-6712f79251bb?w=400&q=80&auto=format&fit=crop',
      status: 'past',
      ticketsTotal: 150,
      ticketsSold: 145,
      revenue: 5075,
      description: 'An evening of laughter with top comedians.',
      ticketPrice: 35
    }
  ];

  // Get all events for current host
  let hostedEvents = mockHostedEvents;

  // Calculate summary stats
  function calculateStats(){
    totalEventsCount.textContent = hostedEvents.length;
    
    let totalRev = 0;
    let totalSold = 0;
    let activeCount = 0;

    hostedEvents.forEach(event => {
      totalRev += event.revenue;
      totalSold += event.ticketsSold;
      if(event.status === 'upcoming' || event.status === 'live') activeCount++;
    });

    totalRevenue.textContent = '$' + totalRev.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    totalTicketsSold.textContent = totalSold.toLocaleString();
    activeEventsCount.textContent = activeCount;
  }

  // Render events grid
  function renderEvents(){
    eventsGrid.innerHTML = '';
    
    if(hostedEvents.length === 0){
      eventsGrid.style.display = 'none';
      emptyEvents.style.display = 'block';
      return;
    }

    emptyEvents.style.display = 'none';
    eventsGrid.style.display = 'grid';

    hostedEvents.forEach(event => {
      const occupancyPercent = (event.ticketsSold / event.ticketsTotal * 100).toFixed(0);
      const statusBadge = {
        'upcoming': '📅 Upcoming',
        'live': '🔴 Live',
        'past': '✓ Past'
      }[event.status];

      const card = document.createElement('article');
      card.className = 'host-event-card';
      card.innerHTML = `
        <div class="event-card-image">
          <img src="${event.image}" alt="${event.title}" loading="lazy" />
          <span class="status-badge ${event.status}">${statusBadge}</span>
        </div>
        <div class="event-card-content">
          <h3>${event.title}</h3>
          <p class="event-card-meta">
            <span>${event.date} • ${event.time}</span>
            <span>${event.venue}, ${event.city}</span>
          </p>
          
          <div class="event-stats-mini">
            <div class="stat-mini">
              <span class="label">Sold</span>
              <span class="value">${event.ticketsSold}/${event.ticketsTotal}</span>
            </div>
            <div class="stat-mini">
              <span class="label">Revenue</span>
              <span class="value">$${event.revenue.toLocaleString()}</span>
            </div>
          </div>

          <div class="progress-bar">
            <div class="progress-fill" style="width:${occupancyPercent}%"></div>
          </div>
          <small class="progress-label">${occupancyPercent}% sold</small>

          <div class="card-actions">
            <a href="host-event-management.html?id=${event.id}" class="btn-link">Manage Event</a>
            <a href="host-ticket-verification.html" class="btn-link">Verify Tickets</a>
          </div>
        </div>
      `;
      eventsGrid.appendChild(card);
    });
  }

  // Filter events
  eventStatusFilter.addEventListener('change', (e) => {
    const filter = e.target.value;
    if(!filter){
      hostedEvents = mockHostedEvents;
    } else {
      hostedEvents = mockHostedEvents.filter(event => event.status === filter);
    }
    calculateStats();
    renderEvents();
  });

  // Initialize
  calculateStats();
  renderEvents();
})();
