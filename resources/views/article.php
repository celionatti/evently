<?php

declare(strict_types=1);

?>

@section('content')
@include('nav')
<!-- BLOG CONTENT -->
<section class="container blog-container">
    <div class="blog-card">
        <div class="blog-header">
            <h1 class="page-title text-start mt-3">{{{ $article->title }}}</h1>

            <div class="blog-meta">
                <div class="blog-meta-item">
                    <i class="bi bi-person"></i>
                    <span class="text-capitalize">By {{{ $author->name . ' ' . $author->other_name }}}</span>
                </div>
                <div class="blog-meta-item">
                    <i class="bi bi-clock"></i>
                    <span>{{{ getReadingTime($article->content) }}} min read • {{ date('F j, Y', strtotime($article->created_at)) }}</span>
                </div>
                <div class="blog-meta-item">
                    <?php if ($article->likes > 0): ?>
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span>{{{ $article->likes ?? 0 }}} likes</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($article->tags): ?>
                <?php $tags = explode(",", $article->tags); ?>
                <div class="blog-tags">
                    <?php foreach ($tags as $tag): ?>
                        <?php $tag = trim($tag); ?>
                        <span class="blog-tag">{{{ $tag }}}</span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <img src="{{{ get_image($article->image, "https://images.unsplash.com/photo-1462536943532-57a629f6cc60?q=80&w=1200&auto=format&fit=crop") }}}" alt="Eco-Friendly Event" class="blog-image">

        <div class="blog-content">
            <?php if ($article->quote): ?>
                <blockquote>
                    "{{{ $article->quote }}}"
                </blockquote>
            <?php endif; ?>

            <div class="article-content">
                {{{ nl2br(htmlspecialchars($article->content)) }}}
            </div>

            <div class="blog-action-buttons">
                <button class="blog-action-btn like-btn" data-article-id="{{{ $article->id }}}">
                    <i class="bi bi-hand-thumbs-up"></i>
                    <span class="like-text">Like this article</span>
                    <span class="like-count">({{{ $article->likes ?? 0 }}})</span>
                </button>
                <!-- <a href="#" class="blog-action-btn">
                    <i class="bi bi-hand-thumbs-up"></i> Like this article
                </a> -->
                <a href="https://wa.me/?text={{{ urlencode($article->title . ' - ' . url('/articles/' . $article->id . '/' . $article->slug)) }}}"
                    target="_blank"
                    class="blog-action-btn">
                    <i class="bi bi-whatsapp text-success"></i> Share on WhatsApp
                </a>
                <button class="blog-action-btn share-btn" data-title="{{{ $article->title }}}" data-url="{{{ url('/articles/' . $article->id . '/' . $article->slug) }}}">
                    <i class="bi bi-share"></i> Share
                </button>
                <!-- <a href="#" class="blog-action-btn">
                    <i class="bi bi-bookmark"></i> Save for later
                </a> -->
            </div>

            <div class="author-card">
                <img src="{{{ get_image($author->image, '/dist/img/avatar.png') }}}" alt="{{{ $author->name . ' ' . $author->other_name }}}" class="author-avatar">
                <div class="author-info">
                    <h4>{{{ $author->name . ' ' . $author->other_name ?? "Unknown Author" }}}</h4>
                    <?php if ($author->bio): ?>
                        <p>{{{ $author->bio }}}</p>
                    <?php else: ?>
                        <p>Writer at Eventlyy</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
@include('footer')
@endsection

@section('scripts')
<script>
    // Like functionality
    document.addEventListener('DOMContentLoaded', function() {
        const likeBtn = document.querySelector('.like-btn');
        if (likeBtn) {
            likeBtn.addEventListener('click', function() {
                const articleId = this.dataset.articleId;
                const likeCount = this.querySelector('.like-count');
                const likeText = this.querySelector('.like-text');

                fetch(`/api/articles/${articleId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            action: 'like'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            likeCount.textContent = `(${data.likes})`;
                            likeText.textContent = 'Liked!';
                            this.classList.add('liked');
                            setTimeout(() => {
                                likeText.textContent = 'Like this article';
                                this.classList.remove('liked');
                            }, 2000);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        }

        // Share functionality
        const shareBtn = document.querySelector('.share-btn');
        if (shareBtn) {
            shareBtn.addEventListener('click', function() {
                copyToClipboard(this.dataset.url);
            });
        }
    });
</script>
@endsection