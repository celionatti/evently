<?php

?>

@section('content')
@include('nav')

<!-- HERO SECTION -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title reveal">Discover Amazing Events</h1>
        <p class="page-subtitle reveal delay-1">Find and book tickets for the best concerts, conferences, and experiences in your city and beyond.</p>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-4 reveal delay-2">
                    <input type="text" class="form-control" placeholder="Search events, artists, or categories..." style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: var(--radius-md);">
                    <button class="btn btn-pulse" type="button">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FILTERS SECTION -->
<section class="container mb-5">
    <div class="filters-section reveal">
        <div class="row">
            <div class="col-md-3">
                <div class="filter-group">
                    <div class="filter-label">Date</div>
                    <select class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                        <option>Any date</option>
                        <option>Today</option>
                        <option>Tomorrow</option>
                        <option>This week</option>
                        <option>This weekend</option>
                        <option>Next week</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="filter-group">
                    <div class="filter-label">Category</div>
                    <select class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                        <option>All categories</option>
                        <option>Music</option>
                        <option>Conference</option>
                        <option>Workshop</option>
                        <option>Sports</option>
                        <option>Comedy</option>
                        <option>Art & Culture</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="filter-group">
                    <div class="filter-label">Location</div>
                    <select class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                        <option>Any location</option>
                        <option>Lagos</option>
                        <option>Abuja</option>
                        <option>Port Harcourt</option>
                        <option>Ibadan</option>
                        <option>Online</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="filter-group">
                    <div class="filter-label">Price</div>
                    <select class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                        <option>Any price</option>
                        <option>Free</option>
                        <option>Under ₦5,000</option>
                        <option>₦5,000 - ₦10,000</option>
                        <option>₦10,000 - ₦20,000</option>
                        <option>Over ₦20,000</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="filter-group mt-3">
            <div class="filter-label">Tags</div>
            <div class="filter-options">
                <div class="filter-chip active">All events</div>
                <div class="filter-chip">Popular</div>
                <div class="filter-chip">Featured</div>
                <div class="filter-chip">Early bird</div>
                <div class="filter-chip">Sold out</div>
                <div class="filter-chip">Discount</div>
            </div>
        </div>
    </div>
</section>

<!-- EVENTS GRID -->
<section class="container">
    <h2 class="mb-4 reveal">Featured Events</h2>

    <div class="events-grid">
        <!-- Event 1 -->
        <div class="event-card reveal">
            <img src="https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop" alt="Afrobeats Concert" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-music-note-beamed"></i> Music</span>
                <h3 class="event-title">Afrobeats Live: Midnight Wave</h3>
                <p class="event-description">Experience the biggest names in African music for a 5-hour extravaganza that will keep you dancing until the early hours.</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Fri, Oct 10 • 8:00 PM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>Eko Convention Center, Lagos</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">From ₦15,000</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>

        <!-- Event 2 -->
        <div class="event-card reveal delay-1">
            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=500&auto=format&fit=crop" alt="Tech Conference" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-laptop"></i> Technology</span>
                <h3 class="event-title">Tech Summit Africa 2025</h3>
                <p class="event-description">Join industry leaders for a 2-day conference on the future of technology, innovation, and digital transformation in Africa.</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Nov 15-16 • 9:00 AM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>Landmark Centre, Lagos</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">From ₦25,000</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>

        <!-- Event 3 -->
        <div class="event-card reveal delay-2">
            <img src="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?q=80&w=500&auto=format&fit=crop" alt="Art Exhibition" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-palette"></i> Art</span>
                <h3 class="event-title">Contemporary Art Exhibition</h3>
                <p class="event-description">Discover works from emerging African artists in this month-long exhibition showcasing contemporary art forms.</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Sep 1-30 • 10:00 AM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>National Gallery, Abuja</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">₦5,000</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>

        <!-- Event 4 -->
        <div class="event-card reveal">
            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=500&auto=format&fit=crop" alt="Food Festival" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-egg-fried"></i> Food & Drink</span>
                <h3 class="event-title">Lagos Food Festival</h3>
                <p class="event-description">Taste your way through the best of Nigerian cuisine with over 50 food vendors, live cooking demos, and competitions.</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Sun, Dec 8 • 12:00 PM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>Muritala Park, Lagos</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">₦3,500</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>

        <!-- Event 5 -->
        <div class="event-card reveal delay-1">
            <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=500&auto=format&fit=crop" alt="Comedy Show" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-mic"></i> Comedy</span>
                <h3 class="event-title">Night of Laughter</h3>
                <p class="event-description">An evening of non-stop laughter featuring Nigeria's top comedians. Prepare for sore cheeks from laughing too much!</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Sat, Nov 23 • 7:00 PM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>Muson Centre, Lagos</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">From ₦7,000</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>

        <!-- Event 6 -->
        <div class="event-card reveal delay-2">
            <img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=500&auto=format&fit=crop" alt="Marathon" class="event-img">
            <div class="event-content">
                <span class="event-category"><i class="bi bi-person-running"></i> Sports</span>
                <h3 class="event-title">Lagos City Marathon</h3>
                <p class="event-description">Join thousands of runners in this annual marathon through the heart of Lagos. Choose from 5K, 10K, or full marathon.</p>

                <div class="event-details">
                    <div class="event-detail">
                        <i class="bi bi-calendar-event"></i>
                        <span>Sun, Feb 9 • 6:00 AM</span>
                    </div>
                    <div class="event-detail">
                        <i class="bi bi-geo-alt"></i>
                        <span>National Stadium, Lagos</span>
                    </div>
                </div>

                <div class="event-footer">
                    <div class="event-price">₦8,000</div>
                    <a href="#" class="btn btn-pulse btn-sm">View Event</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav aria-label="Event pagination">
        <ul class="pagination">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
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

@include('footer')
@endsection

@section('scripts')
<script src="/dist/js/script.js"></script>
@endsection