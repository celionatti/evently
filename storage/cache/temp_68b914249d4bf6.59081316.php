<?php

use App\models\Ticket;
use App\models\Categories;

?>

<?php $this->start('styles'); ?>
<style>
    .min-vh-50 {
        min-height: 50vh;
    }
</style>
<?php $this->end(); ?>

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
<section class="container mb-5">
    <div class="filters-section reveal">
        <form action="/events" method="get" id="filterForm">
            <!-- Keep search term -->
            <input type="hidden" name="search" value="<?= $currentSearch ?? '' ?>">

            <div class="row">
                <div class="col-md-4">
                    <div class="filter-group">
                        <div class="filter-label">Category</div>
                        <select name="category" class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <?= var_dump($category); die; ?>
                                <option value="<?= $category->id ?>" <?= ($currentCategory ?? '') === $category->id ? 'selected' : '' ?>>
                                    <?= ucfirst($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="filter-group">
                        <div class="filter-label">City</div>
                        <select name="city" class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            <option value="">All Cities</option>
                            <?php foreach ($cities ?? [] as $city): ?>
                                <option value="<?= $city['name'] ?>" <?= ($currentCity ?? '') === $city['name'] ? 'selected' : '' ?>>
                                    <?= $city['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="filter-group">
                        <div class="filter-label">Featured</div>
                        <input class="form-check-input" type="checkbox" name="featured" value="true"
                            <?= ($currentFeatured ?? '') === 'true' ? 'checked' : '' ?> id="featuredFilter">
                        <label class="form-check-label" for="featuredFilter">
                            Featured Events Only
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-pulse btn-sm w-100">Apply Filters</button>

            <?php if (!empty($currentSearch) || !empty($currentCategory) || !empty($currentCity) || !empty($currentFeatured)): ?>
                <a href="/events" class="btn btn-ghost w-100 mt-2">Clear Filters</a>
            <?php endif; ?>
        </form>

        <!-- <div class="filter-group mt-3">
            <div class="filter-label">Tags</div>
            <div class="filter-options">
                <div class="filter-chip active">All events</div>
                <div class="filter-chip">Popular</div>
                <div class="filter-chip">Featured</div>
                <div class="filter-chip">Early bird</div>
                <div class="filter-chip">Sold out</div>
                <div class="filter-chip">Discount</div>
            </div>
        </div> -->
    </div>
</section>

<!-- EVENTS GRID -->
<section class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="reveal">
                <?php if (!empty($currentSearch)): ?>
                    Search Results for "<?= htmlspecialchars($currentSearch) ?>"
                <?php elseif (!empty($currentCategory)): ?>
                    <?php $category = Categories::find($currentCategory); ?>
                    <?= ucfirst($category->name) ?> Events
                <?php else: ?>
                    All Events
                <?php endif; ?>
            </h2>

            <small class="text-white"><?= $totalEvents ?? 0 ?> events found</small>
        </div>
        <div class="d-flex align-items-center">
            <label class="me-2">Per Page:</label>
            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                <option value="1" <?= ($_GET['per_page'] ?? 1) == 1 ? 'selected' : '' ?>>1</option>
                <option value="5" <?= ($_GET['per_page'] ?? 5) == 5 ? 'selected' : '' ?>>5</option>
                <option value="12" <?= ($_GET['per_page'] ?? 12) == 12 ? 'selected' : '' ?>>12</option>
                <option value="24" <?= ($_GET['per_page'] ?? 12) == 24 ? 'selected' : '' ?>>24</option>
                <option value="48" <?= ($_GET['per_page'] ?? 12) == 48 ? 'selected' : '' ?>>48</option>
            </select>
        </div>
    </div>

    <?php if (!empty($events) && is_array($events)): ?>
        <div class="events-grid">
            <?php foreach ($events as $index => $event): ?>
                <!-- Event -->
                <div class="event-card reveal <?= $index % 3 === 1 ? 'delay-1' : ($index % 3 === 2 ? 'delay-2' : '') ?>">
                    <img src="<?php echo $this->escape(get_image($event->event_image, "https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop")); ?>" alt="<?php echo $event->event_title; ?>" class="event-img">
                    <div class="event-content">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="event-category">
                                <?php
                                $category = Categories::find($event->category);
                                $icon = getCategoryIcon($category->name);
                                ?>
                                <i class="bi <?= $icon ?>"></i> <?= ucfirst($category->name) ?>
                            </span>
                            <?php if ($event->featured): ?>
                                <div class="event-category">
                                    <i class="bi bi-star-fill text-warning"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h3 class="event-title"><?php echo $event->event_title; ?></h3>
                        <p class="event-description"><?php echo getExcerpt($event->description, 150); ?></p>

                        <div class="event-details">
                            <div class="event-detail">
                                <i class="bi bi-calendar-event"></i>
                                <span><?php echo $this->escape(date('D, M j', strtotime($event->event_date))); ?> • <?php echo $this->escape(date('g:i A', strtotime($event->start_time ?? '00:00:00'))); ?></span>
                            </div>
                            <div class="event-detail">
                                <i class="bi bi-geo-alt"></i>
                                <span class="text-capitalize"><?php echo $event->venue; ?>, <?php echo $event->city; ?></span>
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
                            <a href='<?php echo $this->escape(url("/events/$event->slug")); ?>' class="btn btn-pulse btn-sm">View Event</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php echo $pagination; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="d-flex justify-content-center align-items-center min-vh-50">
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                    <h4 class="mt-3">No Events Found</h4>
                    <p class="text-white">
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
        </div>
    <?php endif; ?>
</section>

<!-- NEWSLETTER SECTION -->
<section class="container">
    <div class="newsletter-section reveal">
        <h3 class="newsletter-title">Never Miss an Event</h3>
        <p class="newsletter-text">Subscribe to our newsletter and be the first to know about new events, exclusive deals, and special promotions.</p>

        <form class="newsletter-form" action="<?php echo $this->escape(url('/newsletter/subscribe')); ?>" method="post">
            <input type="email" name="email" class="newsletter-input" placeholder="Your email address">
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