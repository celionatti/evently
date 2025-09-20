<?php

declare(strict_types=1);

use App\models\User;

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
                    <?php
                    $user = User::find($article->id);
                    ?>
                    <span class="text-capitalize">By <?php echo $user->name . ' ' . $user->other_name; ?></span>
                </div>
                <div class="blog-meta-item">
                    <i class="bi bi-clock"></i>
                    <span><?php echo getReadingTime($article->content); ?> min read • <?php echo $this->escape(date('F j, Y', strtotime($article->created_at))); ?></span>
                </div>
                <div class="blog-meta-item">
                    <?php if($article->likes > 0): ?>
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

            <p><?php echo $article->content; ?></p>

            <div class="blog-action-buttons">
                <a href="#" class="blog-action-btn">
                    <i class="bi bi-hand-thumbs-up"></i> Like this article
                </a>
                <a href="#" class="blog-action-btn">
                    <i class="bi bi-whatsapp text-success"></i> Share
                </a>
                <!-- <a href="#" class="blog-action-btn">
                    <i class="bi bi-bookmark"></i> Save for later
                </a> -->
            </div>

            <div class="author-card">
                <img src="<?php echo get_image('', '/dist/img/avatar.png'); ?>" alt="<?php echo $user->name . ' ' . $user->other_name; ?>" class="author-avatar">
                <div class="author-info">
                    <h4><?php echo $user->name . ' ' . $user->other_name; ?></h4>
                    <p><?php echo $user->bio; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->partial('footer'); ?>
<?php $this->end(); ?>