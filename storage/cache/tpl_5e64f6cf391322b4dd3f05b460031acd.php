<?php

use App\models\Ticket;

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<!-- HERO SECTION -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title reveal">Discover Amazing Events</h1>
        <p class="page-subtitle reveal delay-1">Find and book tickets for the best concerts, conferences, and experiences in your city and beyond.</p>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <form method="GET" action="/events" class="input-group mb-4 reveal delay-2">
                    <input type="text" name="search" class="form-control" placeholder="Search events, artists, or categories..." 
                           value="<?= old('search', $currentSearch ?? '') ?>"
                           style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: var(--radius-md);">
                    <button class="btn btn-pulse" type="submit">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FILTERS SECTION -->
<section class="container">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="filter-card">
                <h5><i class="bi bi-funnel"></i> Filters</h5>
                
                <form method="GET" action="/events" id="filterForm">
                    <!-- Keep search term -->
                    <input type="hidden" name="search" value="<?= $currentSearch ?? '' ?>">
                    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->name ?>" <?= ($currentCategory ?? '') === $category->name ? 'selected' : '' ?>>
                                    <?= ucfirst($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- City Filter -->
                    <div class="filter-group">
                        <label>City</label>
                        <select name="city" class="form-select form-select-sm">
                            <option value="">All Cities</option>
                            <?php foreach ($cities ?? [] as $city): ?>
                                <option value="<?= $city['name'] ?>" <?= ($currentCity ?? '') === $city['name'] ? 'selected' : '' ?>>
                                    <?= $city['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Featured Filter -->
                    <div class="filter-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="featured" value="true" 
                                   <?= ($currentFeatured ?? '') === 'true' ? 'checked' : '' ?> id="featuredFilter">
                            <label class="form-check-label" for="featuredFilter">
                                Featured Events Only
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-pulse btn-sm w-100">Apply Filters</button>
                    
                    <?php if (!empty($currentSearch) || !empty($currentCategory) || !empty($currentCity) || !empty($currentFeatured)): ?>
                        <a href="/events" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4>
                        <?php if (!empty($currentSearch)): ?>
                            Search Results for "<?= htmlspecialchars($currentSearch) ?>"
                        <?php elseif (!empty($currentCategory)): ?>
                            <?= ucfirst($currentCategory) ?> Events
                        <?php else: ?>
                            All Events
                        <?php endif; ?>
                    </h4>
                    <small class="text-muted"><?= $totalEvents ?? 0 ?> events found</small>
                </div>
                
                <div class="d-flex align-items-center">
                    <label class="me-2">Per Page:</label>
                    <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="12" <?= ($_GET['per_page'] ?? 12) == 12 ? 'selected' : '' ?>>12</option>
                        <option value="24" <?= ($_GET['per_page'] ?? 12) == 24 ? 'selected' : '' ?>>24</option>
                        <option value="48" <?= ($_GET['per_page'] ?? 12) == 48 ? 'selected' : '' ?>>48</option>
                    </select>
                </div>
            </div>

            <!-- EVENTS GRID -->
            <div class="events-grid">
                <?php if (!empty($events) && is_array($events)): ?>
                    <?php foreach ($events as $index => $event): ?>
                        <div class="event-card reveal <?= $index % 3 === 1 ? 'delay-1' : ($index % 3 === 2 ? 'delay-2' : '') ?>">
                            <img src="<?= $event->event_image ? $event->event_image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop' ?>" 
                                 alt="<?= htmlspecialchars($event->event_title) ?>" class="event-img">
                            
                            <div class="event-content">
                                <span class="event-category">
                                    <?php
                                    // Map categories to icons
                                    $categoryIcons = [
                                        'music' => 'bi-music-note-beamed',
                                        'technology' => 'bi-laptop',
                                        'art' => 'bi-palette',
                                        'food' => 'bi-egg-fried',
                                        'comedy' => 'bi-mic',
                                        'sports' => 'bi-person-running',
                                        'business' => 'bi-briefcase',
                                        'education' => 'bi-book'
                                    ];
                                    $icon = $categoryIcons[strtolower($event->category)] ?? 'bi-calendar-event';
                                    ?>
                                    <i class="<?= $icon ?>"></i> <?= ucfirst($event->category) ?>
                                </span>
                                
                                <h3 class="event-title">
                                    <a href="/events/<?= $event->slug ?>"><?= htmlspecialchars($event->event_title) ?></a>
                                </h3>
                                
                                <p class="event-description">
                                    <?= htmlspecialchars(substr($event->description, 0, 150)) ?><?= strlen($event->description) > 150 ? '...' : '' ?>
                                </p>

                                <div class="event-details">
                                    <div class="event-detail">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>
                                            <?= date('D, M j', strtotime($event->event_date)) ?> • 
                                            <?= date('g:i A', strtotime($event->start_time ?? '00:00:00')) ?>
                                        </span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?= htmlspecialchars($event->venue) ?>, <?= htmlspecialchars($event->city) ?></span>
                                    </div>
                                </div>

                                <div class="event-footer">
                                    <?php
                                    // Get minimum price from tickets
                                    $minPrice = null;
                                    $tickets = Ticket::where(['event_id' => $event->id]) ?? [];
                                    
                                    foreach ($tickets as $ticket) {
                                        if ($ticket->price > 0 && ($minPrice === null || $ticket->price < $minPrice)) {
                                            $minPrice = $ticket->price;
                                        }
                                    }
                                    ?>
                                    
                                    <div class="event-price">
                                        <?php if ($minPrice): ?>
                                            From ₦<?= number_format($minPrice) ?>
                                        <?php else: ?>
                                            Free
                                        <?php endif; ?>
                                    </div>
                                    
                                    <a href="/events/<?= $event->slug ?>" class="btn btn-pulse btn-sm">View Event</a>
                                </div>
                                
                                <?php if ($event->featured): ?>
                                    <div class="featured-badge">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="no-events">
                            <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                            <h4 class="mt-3">No Events Found</h4>
                            <p class="text-muted">
                                <?php if (!empty($currentSearch)): ?>
                                    No events match your search criteria. Try adjusting your search terms or filters.
                                <?php else: ?>
                                    There are currently no events available. Check back soon!
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($currentSearch) || !empty($currentCategory) || !empty($currentCity)): ?>
                                <a href="/events" class="btn btn-pulse">View All Events</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (!empty($pagination)): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?= $pagination ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FEATURED EVENTS SECTION (if we have featured events and not filtering) -->
<?php if (!empty($featuredEvents) && empty($currentSearch) && empty($currentCategory)): ?>
<section class="container mt-5">
    <h2 class="mb-4 reveal">Featured Events</h2>
    
    <div class="row">
        <?php foreach ($featuredEvents as $index => $event): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="event-card featured-card reveal <?= $index === 1 ? 'delay-1' : ($index === 2 ? 'delay-2' : '') ?>">
                    <img src="<?= $event->event_image ? $event->event_image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop' ?>" 
                         alt="<?= htmlspecialchars($event->event_title) ?>" class="event-img">
                    
                    <div class="event-content">
                        <span class="event-category">
                            <?php
                            $categoryIcons = [
                                'music' => 'bi-music-note-beamed',
                                'technology' => 'bi-laptop',
                                'art' => 'bi-palette',
                                'food' => 'bi-egg-fried',
                                'comedy' => 'bi-mic',
                                'sports' => 'bi-person-running'
                            ];
                            $icon = $categoryIcons[strtolower($event->category)] ?? 'bi-calendar-event';
                            ?>
                            <i class="<?= $icon ?>"></i> <?= ucfirst($event->category) ?>
                        </span>
                        
                        <h3 class="event-title">
                            <a href="/events/<?= $event->slug ?>"><?= htmlspecialchars($event->event_title) ?></a>
                        </h3>
                        
                        <div class="event-details">
                            <div class="event-detail">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    <?= date('D, M j', strtotime($event->event_date)) ?> • 
                                    <?= date('g:i A', strtotime($event->start_time ?? '00:00:00')) ?>
                                </span>
                            </div>
                            <div class="event-detail">
                                <i class="bi bi-geo-alt"></i>
                                <span><?= htmlspecialchars($event->venue) ?>, <?= htmlspecialchars($event->city) ?></span>
                            </div>
                        </div>

                        <div class="event-footer">
                            <?php
                            $minPrice = null;
                            $tickets = Ticket::where(['event_id' => $event->id]) ?? [];
                            
                            foreach ($tickets as $ticket) {
                                if ($ticket->price > 0 && ($minPrice === null || $ticket->price < $minPrice)) {
                                    $minPrice = $ticket->price;
                                }
                            }
                            ?>
                            
                            <div class="event-price">
                                <?php if ($minPrice): ?>
                                    From ₦<?= number_format($minPrice) ?>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </div>
                            
                            <a href="/events/<?= $event->slug ?>" class="btn btn-pulse btn-sm">View Event</a>
                        </div>
                        
                        <div class="featured-badge">
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- NEWSLETTER SECTION -->
<section class="container">
    <div class="newsletter-section reveal">
        <h3 class="newsletter-title">Never Miss an Event</h3>
        <p class="newsletter-text">Subscribe to our newsletter and be the first to know about new events, exclusive deals, and special promotions.</p>

        <form class="newsletter-form" action="/newsletter/subscribe" method="POST">
            <input type="email" name="email" class="newsletter-input" placeholder="Your email address" required>
            <button type="submit" class="btn btn-pulse">Subscribe</button>
        </form>
    </div>
</section>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="/dist/js/script.js"></script>
<script>
// Change per page function
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1); // Reset to first page
    window.location.href = url.toString();
}

// Auto-submit filters when changed
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#filterForm select');
    const filterCheckbox = document.querySelector('#featuredFilter');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    if (filterCheckbox) {
        filterCheckbox.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    // Search form enhancement
    const searchForm = document.querySelector('form[action="/events"]');
    if (searchForm && !searchForm.id) { // Make sure it's not the filter form
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput && searchInput.value.trim().length === 0) {
                e.preventDefault();
                window.location.href = '/events';
            }
        });
    }
});
</script>
<?php $this->end(); ?>