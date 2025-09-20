<?php

declare(strict_types=1);

use App\models\User;

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<!-- HERO SECTION -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title reveal">Event Insights & Tips</h1>
        <p class="page-subtitle reveal delay-1">Discover the latest trends, planning tips, and behind-the-scenes stories from the world of events.</p>

        <!-- <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-4 reveal delay-2">
                    <input type="text" class="form-control" placeholder="Search blog posts, topics, or keywords..." style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: var(--radius-md);">
                    <button class="btn btn-pulse" type="button">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </div> -->
    </div>
</section>

<!-- BLOG GRID -->
<section class="container">
    <h2 class="mb-4 reveal">Featured Articles</h2>

    <?php if (!empty($articles) && is_array($articles)): ?>
        <div class="events-grid">
            <?php foreach ($articles as $index => $article): ?>
                <!-- Blog -->
                <div class="event-card reveal">
                    <img src="<?php echo get_image($article->image, "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=500&auto=format&fit=crop"); ?>" alt="<?php echo getExcerpt($article->title, 10); ?>" class="event-img">
                    <div class="event-content">
                        <h3 class="event-title"><?php echo $article->title; ?></h3>
                        <p class="event-description"><?php echo getExcerpt($article->content, 150); ?></p>

                        <div class="event-details">
                            <div class="event-detail">
                                <i class="bi bi-person"></i>
                                <?php 
                                    $user = User::find($article->id);
                                ?>
                                <span class="text-capitalize">By <?php echo $user->name . ' ' . $user->other_name; ?></span>
                            </div>
                            <div class="event-detail">
                                <i class="bi bi-clock"></i>
                                <span><?php echo getReadingTime($article->content); ?> min read • <?php echo $this->escape(date('M j, Y', strtotime($article->created_at))); ?></span>
                            </div>
                        </div>

                        <div class="event-footer">
                            <div class="event-price">
                                <?php if($article->likes > 0): ?>
                                <i class="bi bi-hand-thumbs-up"></i> 
                                <?php echo $article->likes ?? 0; ?>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo url("/articles/$article->id/$article->slug") ?>" class="btn btn-pulse btn-sm">Read More</a>
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
                    <h4 class="mt-3">No Articles Found</h4>
                    <p class="text-white">
                        <?php if (!empty($currentSearch)): ?>
                            No events match your search criteria. Try adjusting your search terms or filters.
                        <?php else: ?>
                            There are currently no article available. Check back soon!
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($currentSearch)): ?>
                        <a href="/articles" class="btn btn-pulse">View All Articles</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php $this->partial('advert', ['ads' => $advertisements]); ?>
<!-- NEWSLETTER SECTION -->
<!-- <section class="container">
    <div class="newsletter-section reveal">
        <h3 class="newsletter-title">Stay Updated with Eventlyy</h3>
        <p class="newsletter-text">Get the latest event planning tips, industry insights, and exclusive content delivered straight to your inbox.</p>

        <form class="newsletter-form">
            <input type="email" class="newsletter-input" placeholder="Your email address">
            <button type="submit" class="btn btn-pulse">Subscribe</button>
        </form>
    </div>
</section> -->

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="/dist/js/script.js"></script>
<?php $this->end(); ?>