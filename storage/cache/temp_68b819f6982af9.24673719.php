<?php

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
                <form action="/events" method="GET" class="input-group mb-4 reveal delay-2">
                    <input type="text" name="search" class="form-control" placeholder="Search events, artists, or categories..." value="<?= htmlspecialchars($searchTerm ?? '') ?>" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: var(--radius-md);">
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
    <form action="/events" method="GET">
        <div class="filters-section reveal">
            <div class="row">
                <div class="col-md-3">
                    <div class="filter-group">
                        <div class="filter-label">Date</div>
                        <select name="date" class="form-select">
                            <option value="">Any date</option>
                            <option value="today" <?= ($dateFilter ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="tomorrow" <?= ($dateFilter ?? '') == 'tomorrow' ? 'selected' : '' ?>>Tomorrow</option>
                            <option value="this_week" <?= ($dateFilter ?? '') == 'this_week' ? 'selected' : '' ?>>This week</option>
                            <option value="this_weekend" <?= ($dateFilter ?? '') == 'this_weekend' ? 'selected' : '' ?>>This weekend</option>
                            <option value="next_week" <?= ($dateFilter ?? '') == 'next_week' ? 'selected' : '' ?>>Next week</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <div class="filter-label">Category</div>
                        <select name="category" class="form-select">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= ($categoryFilter ?? '') == $category ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <div class="filter-label">Location</div>
                        <select name="city" class="form-select">
                            <option value="">Any location</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city ?>" <?= ($cityFilter ?? '') == $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <div class="filter-label">Status</div>
                        <select name="status" class="form-select">
                            <option value="">Any status</option>
                            <option value="featured" <?= ($statusFilter ?? '') == 'featured' ? 'selected' : '' ?>>Featured</option>
                            <option value="active" <?= ($statusFilter ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="filter-group mt-3">
                <div class="filter-label">Tags</div>
                <div class="filter-options">
                    <div class="filter-chip <?= empty($tagFilter) ? 'active' : '' ?>" data-tag="">All events</div>
                    <?php foreach ($popularTags as $tag): ?>
                        <div class="filter-chip <?= ($tagFilter ?? '') == $tag ? 'active' : '' ?>" data-tag="<?= $tag ?>"><?= htmlspecialchars($tag) ?></div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="tag" id="tagInput" value="<?= $tagFilter ?? '' ?>">
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-pulse">Apply Filters</button>
                <a href="/events" class="btn btn-outline-secondary ms-2">Clear</a>
            </div>
        </div>
    </form>
</section>

<!-- EVENTS GRID -->
<section class="container">
    <h2 class="mb-4 reveal"><?= $eventsTitle ?? 'Upcoming Events' ?></h2>

    <?php if (!empty($events)): ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card reveal">
                    <img src="<?= htmlspecialchars($event->event_image) ?>" alt="<?= htmlspecialchars($event->event_title) ?>" class="event-img">
                    <div class="event-content">
                        <span class="event-category"><i class="bi bi-tag"></i> <?= htmlspecialchars($event->category) ?></span>
                        <h3 class="event-title"><?= htmlspecialchars($event->event_title) ?></h3>
                        <p class="event-description"><?= htmlspecialchars(substr($event->description, 0, 120)) ?>...</p>

                        <div class="event-details">
                            <div class="event-detail">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    <?= date('D, M j', strtotime($event->event_date)) ?>
                                    <?php if ($event->start_time): ?>
                                        • <?= date('g:i A', strtotime($event->start_time)) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($event->venue || $event->city): ?>
                                <div class="event-detail">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>
                                        <?php if ($event->venue) echo htmlspecialchars($event->venue); ?>
                                        <?php if ($event->venue && $event->city) echo ', '; ?>
                                        <?php if ($event->city) echo htmlspecialchars($event->city); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="event-footer">
                            <div class="event-price">
                                <?php if (isset($event->min_price)): ?>
                                    From ₦<?= number_format($event->min_price) ?>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </div>
                            <a href="/event/<?= $event->slug ?>" class="btn btn-pulse btn-sm">View Event</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination): ?>
            <nav aria-label="Event pagination">
                <?= $pagination ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5 reveal">
            <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
            <h3 class="mt-3">No events found</h3>
            <p class="text-muted">Try adjusting your filters or search terms</p>
            <a href="/events" class="btn btn-pulse mt-3">Clear Filters</a>
        </div>
    <?php endif; ?>
</section>

<!-- NEWSLETTER SECTION -->
<section class="container">
    <div class="newsletter-section reveal">
        <h3 class="newsletter-title">Never Miss an Event</h3>
        <p class="newsletter-text">Subscribe to our newsletter and be the first to know about new events, exclusive deals, and special promotions.</p>

        <form class="newsletter-form">
            <input type="email" class="newsletter-input" placeholder="Your email address">
            <button type="submit" class="btn btn-pulse">Subscribe</button>
        </form>
    </div>
</section>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="/dist/js/script.js"></script>
<?php $this->end(); ?>