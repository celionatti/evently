<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use App\Models\Article;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Advertisement;
use Trees\Pagination\Paginator;
use App\controllers\BaseController;
use Trees\Helper\FlashMessages\FlashMessage;

class ArticleController extends BaseController
{
    public function onConstruct()
    {
        parent::onConstruct();
        $this->view->setLayout('default');

        // Add article-specific optimizations
        $this->addAnalytics();
        $this->addSocialShareButtons();
    }

    public function articles(Request $request, Response $response)
    {
        // Set meta tags for articles listing
        $this->view->setTitle("Articles | Eventlyy")
            ->setDescription("Discover insights, tips, and stories from our community of event organizers and attendees.")
            ->setKeywords("articles, blog, events, tips, insights, community, event planning")
            ->setCanonical($request->url());

        // Open Graph tags for social sharing
        $this->view->setOpenGraph([
            'title' => 'Eventlyy Articles - Event Planning Insights',
            'description' => 'Discover insights, tips, and stories from our community of event organizers and attendees.',
            'image' => $request->getBaseUrl() . '/dist/img/og-articles.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website',
            'site_name' => 'Eventlyy'
        ]);

        // Twitter Card
        $this->view->setTwitterCard([
            'card' => 'summary_large_image',
            'title' => 'Eventlyy Articles',
            'description' => 'Event planning insights and community stories',
            'image' => $request->getBaseUrl() . '/dist/img/twitter-articles.jpg',
            'site' => '@eventlyy'
        ]);

        // Build query options
        $queryOptions = [
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        // Only show published articles to the public
        $conditions = ['status' => 'publish'];

        // Add search functionality
        $search = $request->query('search');
        if (!empty($search)) {
            // Use the search method from the Article model
            $searchResults = Article::search($search, ['conditions' => $conditions]);

            // For search results, we'll handle pagination manually
            $totalResults = count($searchResults);
            $perPage = $queryOptions['per_page'];
            $currentPage = $queryOptions['page'];
            $offset = ($currentPage - 1) * $perPage;

            $paginatedResults = array_slice($searchResults, $offset, $perPage);

            $articlesData = [
                'data' => $paginatedResults,
                'meta' => [
                    'total' => $totalResults,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => ceil($totalResults / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $totalResults)
                ]
            ];
        } else {
            $queryOptions['conditions'] = $conditions;
            // Get articles with pagination
            $articlesData = Article::paginate($queryOptions);
        }

        // Create pagination instance
        $pagination = new Paginator($articlesData['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        // Get advertisements
        $advertisements = Advertisement::where(['is_active' => '1']);

        $view = [
            'articles' => $articlesData['data'],
            'pagination' => $paginationLinks,
            'currentSearch' => $search,
            'totalArticles' => $articlesData['meta']['total'] ?? 0,
            'advertisements' => $advertisements
        ];

        return $this->render('articles', $view);
    }

    public function article(Request $request, Response $response, $id, $slug)
    {
        // Find article by slug or ID
        $article = null;

        // Try to find by slug first, then by ID
        if (is_numeric($id)) {
            $article = Article::find($id);
        } else {
            // Find by slug
            $articles = Article::where(['slug' => $slug]);
            $article = !empty($articles) ? $articles[0] : null;
        }

        if (!$article) {
            FlashMessage::setMessage("Article not found!", 'danger');
            return $response->redirect("/articles");
        }

        // Only show active articles to the public (unless user is admin/organizer)
        if ($article->status !== 'publish') {
            // Check if user has permission to view inactive events
            if (!auth() || (!isAdminOrOrganiser() && $article->user_id !== auth()->id)) {
                FlashMessage::setMessage("Article not available!", 'danger');
                return $response->redirect("/articles");
            }
        }

        // Increment view count
        $article->incrementViews();

        // Get article author
        $author = User::find($article->user_id);

        // Set comprehensive SEO for the article
        $this->view->setSEOForArticle([
            'main_title' => $article->title ?: $article->meta_title,
            'title' => $article->meta_title ?: $article->title,
            'description' => $article->meta_description ?: getExcerpt($article->content, 160),
            'keywords' => $article->meta_keywords ?: $article->tags,
            'author' => $author ? $author->name . ' ' . $author->other_name : 'Eventlyy Team',
            'url' => $request->fullUrl(),
            'image' => $this->getFullImageUrl($request, $article->image, '/dist/img/default-article.jpg'),
            'site_name' => 'Eventlyy',
            'published_time' => $article->created_at,
            'modified_time' => $article->updated_at,
            'section' => 'Articles',
            'tags' => $article->tags,
            'canonical' => $request->url(),
            'twitter_creator' => '@eventlyy'
        ]);

        // Add article-specific CSS
        $this->view->addStyle('
                       .article-progress {
                           position: fixed;
                           top: 0;
                           left: 0;
                           height: 3px;
                           background: #007bff;
                           z-index: 9999;
                           transition: width 0.3s ease;
                       }
                   ');

        // Add article-specific JavaScript
        $this->view->addInlineScript('
                       // Track article reading
                       document.addEventListener("DOMContentLoaded", function() {
                           // Reading progress bar
                           const progressBar = document.createElement("div");
                           progressBar.className = "article-progress";
                           document.body.appendChild(progressBar);
                           
                           // Track scroll progress
                           window.addEventListener("scroll", function() {
                               const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                               progressBar.style.width = scrolled + "%";
                           });
                           
                           // Analytics tracking
                           if (typeof gtag !== "undefined") {
                               gtag("event", "page_view", {
                                   page_title: "' . addslashes($article->title) . '",
                                   page_location: window.location.href,
                                   content_group1: "Articles"
                               });
                           }
                       });
                   ');

        // Add structured data for rich snippets
        $this->view->addInlineScript($this->generateArticleStructuredData($article, $author, $request), [
            'type' => 'application/ld+json'
        ]);

        // Add performance optimizations for single article
        $this->optimizeForArticle($article, $request);

        $view = [
            'article' => $article,
            'author' => $author
        ];

        return $this->render("article", $view);
    }

    /**
     * Handle article like/unlike via AJAX
     */
    public function likeArticle(Request $request, Response $response, $id)
    {
        // This should be a POST request
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $article = Article::find($id);
        if (!$article) {
            return $response->json(['success' => false, 'message' => 'Article not found'], 404);
        }

        $action = $request->input('action', 'like'); // 'like' or 'unlike'

        if ($action === 'like') {
            $success = $article->incrementLikes();
        } else {
            $success = $article->decrementLikes();
        }

        if ($success) {
            return $response->json([
                'success' => true,
                'likes' => $article->likes,
                'message' => $action === 'like' ? 'Article liked!' : 'Article unliked!'
            ]);
        } else {
            return $response->json(['success' => false, 'message' => 'Failed to update likes'], 500);
        }
    }

    /**
     * Search articles API endpoint
     */
    public function searchArticles(Request $request, Response $response)
    {
        $query = $request->query('q', '');
        $limit = $request->query('limit', 10);

        if (empty($query)) {
            return $response->json(['articles' => []]);
        }

        $articles = Article::search($query);
        $articles = array_slice($articles, 0, $limit);

        // Format articles for JSON response
        $formattedArticles = array_map(function ($article) use ($request) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => substr(strip_tags($article->content), 0, 150) . '...',
                'image' => $this->getFullImageUrl($request, $article->image),
                'created_at' => $article->created_at,
                'reading_time' => $article->getReadingTime(),
                'author' => $this->getArticleAuthor($article->user_id, $request)
            ];
        }, $articles);

        return $response->json(['articles' => $formattedArticles]);
    }

    /**
     * RSS Feed with proper headers
     */
    public function rss(Request $request, Response $response)
    {
        $articles = Article::getRecent(50);

        // Set cache headers for RSS (cache for 1 hour)
        $response->setHeader('Cache-Control', 'public, max-age=3600')
            ->setHeader('ETag', md5(serialize($articles)));

        $rssContent = $this->generateRSSFeed($articles, $request);

        return $response->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->output($rssContent);
    }

    /**
     * Article sitemap for SEO
     */
    public function sitemap(Request $request, Response $response)
    {
        $articles = Article::getPublished();

        $xml = $this->generateSitemap($articles, $request);

        return $response->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->output($xml);
    }

    /**
     * Get article author details
     */
    private function getArticleAuthor(int $userId, Request $request): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'name' => 'Unknown Author',
                'avatar' => $this->getFullImageUrl($request, '', '/dist/img/avatar.png')
            ];
        }

        return [
            'name' => $user->name . ' ' . $user->other_name,
            'avatar' => $this->getFullImageUrl($request, $user->avatar ?? '', '/dist/img/avatar.png')
        ];
    }

    /**
     * Generate RSS feed content
     */
    private function generateRSSFeed(array $articles, Request $request): string
    {
        $baseUrl = $request->getBaseUrl();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>Eventlyy Articles</title>' . "\n";
        $rss .= '<link>' . $baseUrl . '/articles</link>' . "\n";
        $rss .= '<description>Latest articles from Eventlyy</description>' . "\n";
        $rss .= '<language>en-us</language>' . "\n";

        foreach ($articles as $article) {
            $articleUrl = $baseUrl . '/articles/' . $article->id . '/' . $article->slug;
            $author = $this->getArticleAuthor($article->user_id, $request);

            $rss .= '<item>' . "\n";
            $rss .= '<title><![CDATA[' . $article->title . ']]></title>' . "\n";
            $rss .= '<link>' . $articleUrl . '</link>' . "\n";
            $rss .= '<description><![CDATA[' . substr(strip_tags($article->content), 0, 300) . '...]]></description>' . "\n";
            $rss .= '<author>' . $author['name'] . '</author>' . "\n";
            $rss .= '<pubDate>' . date('r', strtotime($article->created_at)) . '</pubDate>' . "\n";
            $rss .= '<guid>' . $articleUrl . '</guid>' . "\n";
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return $rss;
    }

    /**
     * Generate sitemap XML
     */
    private function generateSitemap(array $articles, Request $request): string
    {
        $baseUrl = $request->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($articles as $article) {
            $xml .= '<url>' . "\n";
            $xml .= '<loc>' . $baseUrl . '/articles/' . $article->id . '/' . $article->slug . '</loc>' . "\n";
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($article->updated_at)) . '</lastmod>' . "\n";
            $xml .= '<changefreq>weekly</changefreq>' . "\n";
            $xml .= '<priority>0.8</priority>' . "\n";
            $xml .= '</url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return $xml;
    }

    /**
     * Generate comprehensive structured data
     */
    private function generateArticleStructuredData(Article $article, ?User $author, Request $request): string
    {
        $data = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $article->title,
            "description" => $article->meta_description ?: getExcerpt($article->content, 160),
            "image" => [
                "@type" => "ImageObject",
                "url" => $this->getFullImageUrl($request, $article->image),
                "width" => 1200,
                "height" => 630
            ],
            "datePublished" => date('c', strtotime($article->created_at)),
            "dateModified" => date('c', strtotime($article->updated_at)),
            "wordCount" => str_word_count(strip_tags($article->content)),
            "timeRequired" => "PT" . $article->getReadingTime() . "M",
            "url" => $request->fullUrl(),
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $request->fullUrl()
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Eventlyy",
                "url" => $request->getBaseUrl(),
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => $request->getBaseUrl() . "/dist/img/logo.png",
                    "width" => 200,
                    "height" => 60
                ],
                "sameAs" => [
                    "https://facebook.com/eventlyy",
                    "https://twitter.com/eventlyy",
                    "https://instagram.com/eventlyy"
                ]
            ]
        ];

        if ($author) {
            $data["author"] = [
                "@type" => "Person",
                "name" => $author->name . ' ' . $author->other_name,
                "url" => $request->getBaseUrl() . "/authors/" . $author->id,
                "image" => [
                    "@type" => "ImageObject",
                    "url" => $this->getFullImageUrl($request, $author->avatar ?? '', '/dist/img/avatar.png')
                ]
            ];
        }

        if ($article->tags) {
            $tags = array_map('trim', explode(',', $article->tags));
            $data["keywords"] = implode(', ', $tags);
            $data["articleSection"] = $tags[0] ?? 'General';
        }

        // Add breadcrumb structured data
        $breadcrumbData = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => $request->getBaseUrl()
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Articles",
                    "item" => $request->getBaseUrl() . "/articles"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $article->title,
                    "item" => $request->fullUrl()
                ]
            ]
        ];

        return json_encode([$data, $breadcrumbData], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Get full image URL with fallback
     */
    private function getFullImageUrl(Request $request, string $image = '', string $fallback = ''): string
    {
        if (empty($image)) {
            if (empty($fallback)) {
                return '';
            }
            $image = $fallback;
        }

        // If it's already a full URL, return as is
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        // Use the enhanced Request methods
        $baseUrl = $request->getBaseUrl();
        return $baseUrl . '/' . ltrim($image, '/');
    }

    private function optimizeForArticle(Article $article, Request $request)
    {
        // Preload related resources
        // $this->view->addLink('preload', '/dist/css/article.css', ['as' => 'style'])
        //            ->addLink('preload', '/dist/js/article-interactions.js', ['as' => 'script']);

        // Add lazy loading script for images
        $this->view->addInlineScript('
            // Lazy loading for images
            document.addEventListener("DOMContentLoaded", function() {
                const images = document.querySelectorAll("img[data-src]");
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.removeAttribute("data-src");
                            observer.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            });
        ');

        // Add service worker for caching
        $this->view->addInlineScript('
            // Service Worker registration
            if ("serviceWorker" in navigator) {
                window.addEventListener("load", function() {
                    navigator.serviceWorker.register("/sw.js").then(function(registration) {
                        console.log("SW registered: ", registration);
                    }).catch(function(registrationError) {
                        console.log("SW registration failed: ", registrationError);
                    });
                });
            }
        ');
    }
}
