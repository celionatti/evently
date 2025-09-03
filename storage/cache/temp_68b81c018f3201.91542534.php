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
            <!-- <div class="row">
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
            </div> -->

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

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="/dist/js/script.js"></script>
<?php $this->end(); ?>