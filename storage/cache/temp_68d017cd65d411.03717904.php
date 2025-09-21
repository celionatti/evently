<?php

declare(strict_types=1);

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>
<!-- BLOG CONTENT -->
<section class="container blog-container">
    <div class="blog-card">
        <div class="blog-header">
            <h1 class="page-title text-start mt-3"><?php echo $article->title; ?></h1>

            <div class="blog-meta">
                <div class="blog-meta-item">
                    <i class="bi bi-person"></i>
                    <span class="text-capitalize">By <?php echo $author->name . ' ' . $author->other_name; ?></span>
                </div>
                <div class="blog-meta-item">
                    <i class="bi bi-clock"></i>
                    <span><?php echo getReadingTime($article->content); ?> min read • <?php echo $this->escape(date('F j, Y', strtotime($article->created_at))); ?></span>
                </div>
                <div class="blog-meta-item">
                    <?php if ($article->likes > 0): ?>
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span><?php echo $article->likes ?? 0; ?> likes</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($article->tags): ?>
                <?php $tags = explode(",", $article->tags); ?>
                <div class="blog-tags">
                    <?php foreach ($tags as $tag): ?>
                        <?php $tag = trim($tag); ?>
                        <span class="blog-tag"><?php echo $tag; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <img src="<?php echo get_image($article->image, "https://images.unsplash.com/photo-1462536943532-57a629f6cc60?q=80&w=1200&auto=format&fit=crop"); ?>" alt="Eco-Friendly Event" class="blog-image">

        <div class="blog-content">
            <?php if ($article->quote): ?>
                <blockquote>
                    "<?php echo $article->quote; ?>"
                </blockquote>
            <?php endif; ?>

            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($article->content)); ?>
            </div>

            <div class="blog-action-buttons">
                <a href="#" class="blog-action-btn">
                    <i class="bi bi-hand-thumbs-up"></i> Like this article
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($article->title . ' - ' . url('/articles/' . $article->id . '/' . $article->slug)); ?>"
                    target="_blank"
                    class="blog-action-btn">
                    <i class="bi bi-whatsapp text-success"></i> Share on WhatsApp
                </a>
                <button class="blog-action-btn share-btn" data-title="<?php echo $article->title; ?>" data-url="<?php echo url('/articles/' . $article->id . '/' . $article->slug); ?>">
                    <i class="bi bi-share"></i> Share
                </button>
                <!-- <a href="#" class="blog-action-btn">
                    <i class="bi bi-bookmark"></i> Save for later
                </a> -->
            </div>

            <div class="author-card">
                <img src="<?php echo get_image($author->image, '/dist/img/avatar.png'); ?>" alt="<?php echo $author->name . ' ' . $author->other_name; ?>" class="author-avatar">
                <div class="author-info">
                    <h4><?php echo $author->name . ' ' . $author->other_name ?? "Unknown Author"; ?></h4>
                    <?php if ($user->bio): ?>
                        <p><?php echo $user->bio; ?></p>
                    <?php else: ?>
                        <p>Writer at Eventlyy</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->partial('footer'); ?>
<?php $this->end(); ?>