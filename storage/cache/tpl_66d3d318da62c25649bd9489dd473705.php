<?php

declare(strict_types=1);

use App\models\Ticket;
use App\models\Categories;

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<!-- HERO -->
<header class="hero">
    <div class="container position-relative">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6 order-2 order-lg-1">
                <h1 class="reveal">Discover. Book. Experience the Night.</h1>
                <p class="lead mt-3 reveal delay-1">From live concerts to tech conferences, grab verified tickets fast with a silky-smooth checkout and instant mobile passes.</p>
                <div class="d-flex gap-2 mt-3 reveal delay-2">
                    <a href="<?php echo url("/events"); ?>" class="btn btn-pulse"><i class="bi bi-lightning-charge-fill me-1"></i> Explore Events</a>
                    <a href="#how" class="btn btn-ghost"><i class="bi bi-play-circle me-1"></i> How it works</a>
                </div>
                <div class="spin-bubble" aria-hidden="true"></div>
            </div>

            <div class="col-lg-6 order-1 order-lg-2">
                <!-- Glide Hero Carousel -->
                <div class="glide reveal delay-1" id="heroGlide" aria-label="Featured slides">
                    <div class="glide__track" data-glide-el="track">
                        <ul class="glide__slides">
                            <li class="glide__slide">
                                <img class="w-100 rounded-4" src="<?= get_image("dist/img/eventlyy.png") ?>" alt="Eventlyy" style="box-shadow:var(--shadow-1);">
                            </li>
                            <li class="glide__slide">
                                <img class="w-100 rounded-4" src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?q=80&w=1400&auto=format&fit=crop" alt="Tech stage lights" style="box-shadow:var(--shadow-1);">
                            </li>
                            <li class="glide__slide">
                                <img class="w-100 rounded-4" src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1400&auto=format&fit=crop" alt="DJ performing at festival" style="box-shadow:var(--shadow-1);">
                            </li>
                        </ul>
                    </div>
                    <div class="glide__arrows" data-glide-el="controls">
                        <button class="glide__arrow glide__arrow--left btn btn-ghost px-2 py-1" data-glide-dir="<" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
                        <button class="glide__arrow glide__arrow--right btn btn-ghost px-2 py-1" data-glide-dir=">" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-3 mt-4 text-center text-lg-start">
            <div class="col-6 col-lg-3 reveal"><span class="chip"><i class="bi bi-shield-check"></i> Verified</span></div>
            <div class="col-6 col-lg-3 reveal delay-1"><span class="chip"><i class="bi bi-phone"></i> Mobile Pass</span></div>
            <div class="col-6 col-lg-3 reveal delay-2"><span class="chip"><i class="bi bi-credit-card-2-front"></i> Secure Pay</span></div>
            <div class="col-6 col-lg-3 reveal delay-3"><span class="chip"><i class="bi bi-lightning-charge"></i> Fast Entry</span></div>
        </div>
    </div>
</header>

<?php $this->partial('advert-wide', ['ads' => $advertisements]); ?>

<!-- EVENTS -->
<section id="events" class="py-5">
    <div class="container">
        <div class="row align-items-end mb-3">
            <div class="col">
                <h2 class="section-title reveal">Trending Events</h2>
                <p class="section-sub reveal delay-1">Hot picks near you—freshly updated daily.</p>
            </div>
            <div class="col-auto reveal delay-2">
                <form action="/" method="get">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-info"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" name="city" class="form-control bg-transparent border-secondary" placeholder="Filter by city (e.g., Lagos)">
                        <button type="submit" class="btn btn-ghost">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <!-- Event Card -->
                <div class="col-12 col-md-6 col-xl-4 reveal">
                    <article class="event-card h-100 d-flex flex-column">
                        <img class="event-img" src="<?php echo get_image($event->event_image); ?>" alt="<?php echo $event->event_title; ?>">
                        <div class="p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="chip">
                                    <?php
                                    $category = Categories::find($event->category);
                                    $icon = getCategoryIcon($category->name);
                                    ?>
                                    <i class="bi <?php echo $this->escape($icon); ?>"></i> <?php echo $this->escape(ucfirst($category->name)); ?>
                                </span>
                                <span class="text-info-emphasis small"><i class="bi bi-calendar-event"></i> <?php echo $this->escape(date('D • M j', strtotime($event->event_date))); ?></span>
                            </div>
                            <h5 class="mb-1"><?php echo $event->event_title; ?></h5>
                            <p class="mb-3"><?php echo getExcerpt($event->description, 150); ?></p>
                            <div class="d-flex align-items-center justify-content-between">
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
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($minPrice): ?>
                                        <span class="badge rounded-pill text-bg-info">From ₦<?= number_format((float)$minPrice) ?></span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-primary">Free</span>
                                    <?php endif; ?>
                                    <!-- <span class="badge rounded-pill text-bg-primary">VIP</span> -->
                                    <!-- <span class="badge rounded-pill text-bg-info">Early Bird</span> -->
                                </div>
                                <a href="<?php echo $this->escape(url("/events/$event->id/{$event->slug}")); ?>" class="btn btn-pulse btn-sm"><i class="bi bi-ticket-perforated me-1"></i> Get Tickets</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach ?>
        </div>

        <div class="text-center mt-4 reveal delay-3">
            <a href="<?php echo $this->escape(url("/events")); ?>" class="btn btn-ghost"><i class="bi bi-collection-play me-2"></i>View all events</a>
        </div>
    </div>
</section>

<?php $this->partial('advert', ['ads' => $advertisements]); ?>

<!-- HOW IT WORKS -->
<section id="how" class="py-5">
    <div class="container">
        <div class="row text-center mb-4">
            <h2 class="section-title reveal">How it works</h2>
            <p class="section-sub reveal delay-1">Find an event, pick a seat, pay securely—done.</p>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4 reveal">
                <div class="category-card h-100">
                    <i class="bi bi-search fs-2 text-primary mb-2"></i>
                    <h5>Browse</h5>
                    <p>Filter by city, category, and dates. Real-time availability with verified listings.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 reveal delay-1">
                <div class="category-card h-100">
                    <i class="bi bi-grid-1x2 fs-2 text-primary mb-2"></i>
                    <h5>Select</h5>
                    <p>Choose seat tiers (VIP, General, Balcony) with clear pricing and perks.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 reveal delay-2">
                <div class="category-card h-100">
                    <i class="bi bi-shield-lock fs-2 text-primary mb-2"></i>
                    <h5>Checkout</h5>
                    <p>Encrypted payments. Instantly receive wallet-ready mobile passes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section id="categories" class="py-5">
    <div class="container">
        <div class="row text-center mb-4">
            <h2 class="section-title reveal">Browse by Category</h2>
            <p class="section-sub reveal delay-1">Find events that match your interests and passions.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <?php foreach ($categories as $category): ?>
                <?php $icon = getCategoryIcon($category->name); ?>
                <div class="col-12 col-md-6 col-xl-3 reveal">
                    <div class="category-card h-100">
                        <div class="category-icon"><i class="bi <?php echo $this->escape($icon); ?>"></i></div>
                        <h5 class="mb-1"><?php echo $category->name; ?></h5>
                        <p><?php echo $category->description; ?></p>
                        <a href="<?php echo url("/events?category=$category->id"); ?>" class="btn btn-ghost w-100 mt-2">Explore</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 reveal">
                <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="People scanning tickets at the gate" class="w-100 rounded-4" style="box-shadow:var(--shadow-1);">
            </div>
            <div class="col-lg-6 reveal delay-1">
                <h2 class="section-title">Built for speed, designed for trust</h2>
                <p class="section-sub">Our ticketing engine is optimized for high-demand drops with wait-free checkout, fraud prevention, and QR-based entry that just works.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="testimonial h-100">
                            <div class="fw-bold"><i class="bi bi-shield-check text-primary"></i> Security</div>
                            <div class="small">3-D Secure & PCI-DSS ready</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="testimonial h-100">
                            <div class="fw-bold"><i class="bi bi-graph-up text-primary"></i> Scale</div>
                            <div class="small">Burst-ready infrastructure</div>
                        </div>
                    </div>
                </div>
                <a href="#get-tickets" class="btn btn-ghost mt-3">Start now</a>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS (Glide) -->
<section id="testimonials" class="py-5">
    <div class="container">
        <h2 class="section-title text-center reveal">What fans say</h2>
        <p class="section-sub text-center reveal delay-1 mb-4">Real people. Real experiences.</p>

        <div class="glide reveal delay-2" id="loveGlide">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    <li class="glide__slide">
                        <div class="testimonial">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="https://i.pravatar.cc/60?img=12" class="rounded-circle" alt="Customer A" width="48" height="48">
                                <div>
                                    <div class="fw-bold">Tomiwa A.</div>
                                    <div class="small">Lagos</div>
                                </div>
                            </div>
                            <p class="mb-0">Fastest checkout I've used. Tickets in my wallet in seconds. 🤯</p>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <div class="testimonial">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="https://i.pravatar.cc/60?img=32" class="rounded-circle" alt="Customer B" width="48" height="48">
                                <div>
                                    <div class="fw-bold">Adaeze K.</div>
                                    <div class="small">Abuja</div>
                                </div>
                            </div>
                            <p class="mb-0">Seats exactly as promised. Scanned once, we were in! 🔥</p>
                        </div>
                    </li>
                    <li class="glide__slide">
                        <div class="testimonial">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="https://i.pravatar.cc/60?img=8" class="rounded-circle" alt="Customer C" width="48" height="48">
                                <div>
                                    <div class="fw-bold">Emeka O.</div>
                                    <div class="small">Port Harcourt</div>
                                </div>
                            </div>
                            <p class="mb-0">Love the design. Smooth, fast, and trustworthy. 💙</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="glide__bullets" data-glide-el="controls[nav]">
                <button class="glide__bullet" data-glide-dir="=0" aria-label="Slide 1"></button>
                <button class="glide__bullet" data-glide-dir="=1" aria-label="Slide 2"></button>
                <button class="glide__bullet" data-glide-dir="=2" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
</section>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    // Initialize Glide sliders
    new Glide("#heroGlide", {
        type: "carousel",
        autoplay: 3500,
        hoverpause: true,
        animationDuration: 800,
        perView: 1,
        gap: 16,
        keyboard: true,
    }).mount();

    new Glide("#loveGlide", {
        type: "carousel",
        autoplay: 4200,
        animationDuration: 700,
        perView: 2,
        gap: 24,
        breakpoints: {
            992: {
                perView: 2
            },
            768: {
                perView: 1
            },
        },
    }).mount();
</script>
<script src="/dist/js/script.js"></script>
<?php $this->end(); ?>