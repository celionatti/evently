<?php

declare(strict_types=1);

use App\models\Setting;

$setting = Setting::first(['`key`' => 'app_name']);
dd($setting);

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<!-- HERO SECTION -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title reveal">Our Story <?= Setting::getSetting('app_name') ?></h1>
        <p class="page-subtitle reveal delay-1">Connecting people through unforgettable experiences, one event at a time.</p>
    </div>
</section>

<!-- MISSION SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 reveal">
                <img src="https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?q=80&w=870&auto=format&fit=crop" alt="Team collaboration" class="w-100 rounded-4" style="box-shadow:var(--shadow-1);">
            </div>
            <div class="col-lg-6 reveal delay-1">
                <h2 class="section-title">Our Mission</h2>
                <p class="section-sub">Making event discovery and ticketing seamless, secure, and enjoyable.</p>
                <p>At Eventlyy, we believe that live experiences have the power to transform, connect, and inspire. Our mission is to remove the friction from event discovery and ticketing, so you can focus on what truly matters—creating memories.</p>
                <div class="row g-3 mt-4">
                    <div class="col-6">
                        <div class="testimonial h-100">
                            <div class="fw-bold"><i class="bi bi-people-fill text-primary"></i> Community</div>
                            <div class="small">Connecting people through events</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="testimonial h-100">
                            <div class="fw-bold"><i class="bi bi-lightning-charge text-primary"></i> Innovation</div>
                            <div class="small">Cutting-edge ticketing technology</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VALUES SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <h2 class="section-title reveal">Our Values</h2>
            <p class="section-sub reveal delay-1">The principles that guide everything we do</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card h-100 text-center">
                    <div class="category-icon"><i class="bi bi-heart-fill"></i></div>
                    <h5>Passion for Experiences</h5>
                    <p>We're event enthusiasts first, committed to bringing people together through memorable experiences.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-1">
                <div class="category-card h-100 text-center">
                    <div class="category-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Trust & Security</h5>
                    <p>Your safety and privacy are paramount. We implement industry-leading security measures for all transactions.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-2">
                <div class="category-card h-100 text-center">
                    <div class="category-icon"><i class="bi bi-lightbulb-fill"></i></div>
                    <h5>Innovation</h5>
                    <p>We continuously evolve our platform to deliver the best possible experience for event organizers and attendees.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-3">
                <div class="category-card h-100 text-center">
                    <div class="category-icon"><i class="bi bi-globe"></i></div>
                    <h5>Inclusivity</h5>
                    <p>We believe everyone should have access to amazing events, regardless of background or ability.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TEAM SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <h2 class="section-title reveal">Meet Our Team</h2>
            <p class="section-sub reveal delay-1">The passionate people behind Eventlyy</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card h-100 text-center">
                    <img src="https://i.pravatar.cc/120?img=32" alt="Team member" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Adaeze K.</h5>
                    <p class="text-primary">CEO & Founder</p>
                    <p class="small">Former event organizer with a vision for seamless ticketing.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-1">
                <div class="category-card h-100 text-center">
                    <img src="https://i.pravatar.cc/120?img=12" alt="Team member" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Tomiwa A.</h5>
                    <p class="text-primary">CTO</p>
                    <p class="small">Tech visionary with 10+ years in platform development.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-2">
                <div class="category-card h-100 text-center">
                    <img src="https://i.pravatar.cc/120?img=8" alt="Team member" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Emeka O.</h5>
                    <p class="text-primary">Head of Partnerships</p>
                    <p class="small">Connecting with event organizers across Africa.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal delay-3">
                <div class="category-card h-100 text-center">
                    <img src="https://i.pravatar.cc/120?img=44" alt="Team member" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Chiamaka U.</h5>
                    <p class="text-primary">Customer Experience</p>
                    <p class="small">Ensuring every user has an exceptional experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="py-5">
    <div class="container">
        <div class="category-card">
            <div class="row text-center">
                <div class="col-6 col-md-3 reveal">
                    <h2 class="display-4 fw-bold">50K+</h2>
                    <p class="text-primary">Events Hosted</p>
                </div>
                <div class="col-6 col-md-3 reveal delay-1">
                    <h2 class="display-4 fw-bold">2M+</h2>
                    <p class="text-primary">Tickets Sold</p>
                </div>
                <div class="col-6 col-md-3 reveal delay-2">
                    <h2 class="display-4 fw-bold">200+</h2>
                    <p class="text-primary">Cities</p>
                </div>
                <div class="col-6 col-md-3 reveal delay-3">
                    <h2 class="display-4 fw-bold">99.9%</h2>
                    <p class="text-primary">Uptime</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="py-5">
    <div class="container">
        <div class="category-card text-center py-5">
            <h2 class="section-title reveal">Join Our Community</h2>
            <p class="section-sub reveal delay-1">Be part of the event revolution</p>
            <div class="d-flex gap-2 mt-3 justify-content-center reveal delay-2">
                <a href="index.html#events" class="btn btn-pulse"><i class="bi bi-ticket-perforated me-1"></i> Find Events</a>
                <a href="#" class="btn btn-ghost"><i class="bi bi-question-circle me-1"></i> Contact Us</a>
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