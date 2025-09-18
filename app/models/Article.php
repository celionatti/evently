<?php

declare(strict_types=1);

namespace App\Models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;


class Article extends Model implements ModelInterface
{
    protected string $table = 'articles';

    protected array $fillable = [
        'id',
        'slug',
        'user_id',
        'views',
        'likes',
        'tags',
        'title',
        'content',
        'quote',
        'contributors',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'image',
        'status',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'user_id' => 'integer',
        'views' => 'integer',
        'likes' => 'integer',
    ];

    protected array $hidden = [];

    public function rules()
    {
        return [];
    }

    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(title LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Find an article by its slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where(['slug' => $slug])[0] ?? null;
    }

    /**
     * Get published articles only
     */
    public static function getPublished(array $options = []): array
    {
        $conditions = array_merge($options['conditions'] ?? [], ['status' => 'publish']);
        $options['conditions'] = $conditions;
        
        return static::where($options);
    }

    /**
     * Get articles by user
     */
    public static function getByUser(int $userId, array $options = []): array
    {
        $conditions = array_merge($options['conditions'] ?? [], ['user_id' => $userId]);
        $options['conditions'] = $conditions;
        
        return static::where($options);
    }

    /**
     * Get popular articles (by views)
     */
    public static function getPopular(int $limit = 10): array
    {
        return static::where([
            'conditions' => ['status' => 'publish'],
            'order_by' => ['views' => 'DESC'],
            'limit' => $limit
        ]);
    }

    /**
     * Get recent articles
     */
    public static function getRecent(int $limit = 10): array
    {
        return static::where([
            'conditions' => ['status' => 'publish'],
            'order_by' => ['created_at' => 'DESC'],
            'limit' => $limit
        ]);
    }

    /**
     * Search articles by title or content
     */
    public static function search(string $query, array $options = []): array
    {
        // This is a basic implementation - you might want to use a more sophisticated search
        $conditions = array_merge($options['conditions'] ?? [], ['status' => 'publish']);
        $articles = static::where(['conditions' => $conditions]);
        
        return array_filter($articles, function($article) use ($query) {
            return stripos($article->title, $query) !== false || 
                   stripos($article->content, $query) !== false ||
                   stripos($article->tags, $query) !== false;
        });
    }

    /**
     * Increment view count
     */
    public function incrementViews(): bool
    {
        $this->views = ($this->views ?? 0) + 1;
        return $this->updateInstance(['views' => $this->views]);
    }

    /**
     * Increment like count
     */
    public function incrementLikes(): bool
    {
        $this->likes = ($this->likes ?? 0) + 1;
        return $this->updateInstance(['likes' => $this->likes]);
    }

    /**
     * Decrement like count
     */
    public function decrementLikes(): bool
    {
        $this->likes = max(0, ($this->likes ?? 0) - 1);
        return $this->updateInstance(['likes' => $this->likes]);
    }

    /**
     * Get the article's reading time estimate
     */
    public function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        // Average reading speed is 200-250 words per minute
        return max(1, ceil($wordCount / 225));
    }

    /**
     * Get the article's tags as an array
     */
    public function getTagsArray(): array
    {
        if (!$this->tags) return [];
        
        return array_map('trim', explode(',', $this->tags));
    }

    /**
     * Check if article is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'publish';
    }

    /**
     * Check if article is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Scope for published articles
     */
    public static function published(): array
    {
        return static::where(['status' => 'publish']);
    }

    /**
     * Scope for draft articles
     */
    public static function drafts(): array
    {
        return static::where(['status' => 'draft']);
    }

    /**
     * Get related articles based on tags
     */
    public function getRelatedArticles(int $limit = 5): array
    {
        if (!$this->tags) return [];
        
        $tags = $this->getTagsArray();
        $relatedArticles = [];
        
        foreach ($tags as $tag) {
            $articles = static::getByTag($tag);
            foreach ($articles as $article) {
                if ($article->id !== $this->id && !in_array($article, $relatedArticles, true)) {
                    $relatedArticles[] = $article;
                }
            }
        }
        
        // Sort by created_at desc and limit
        usort($relatedArticles, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return array_slice($relatedArticles, 0, $limit);
    }

    /**
     * Get articles by tag
     */
    public static function getByTag(string $tag, array $options = []): array
    {
        $conditions = array_merge($options['conditions'] ?? [], ['status' => 'publish']);
        $articles = static::where(['conditions' => $conditions]);
        
        return array_filter($articles, function($article) use ($tag) {
            if (!$article->tags) return false;
            
            $tags = array_map('trim', explode(',', $article->tags));
            return in_array($tag, $tags, true);
        });
    }

    /**
     * Generate SEO-friendly slug from title
     */
    public static function generateSlug(string $title): string
    {
        return str_slug($title, '-');
    }

    /**
     * Check if slug is unique
     */
    public static function isSlugUnique(string $slug, ?int $excludeId = null): bool
    {
        $conditions = ['slug' => $slug];
        if ($excludeId) {
            $conditions['id !='] = $excludeId;
        }
        
        $existing = static::where($conditions);
        return empty($existing);
    }

    /**
     * Generate unique slug
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = static::generateSlug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (!static::isSlugUnique($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Before creating, ensure slug is unique
     */
    public static function create(array $data): int|bool
    {
        if (!empty($data['title']) && empty($data['slug'])) {
            $data['slug'] = static::generateUniqueSlug($data['title']);
        }
        
        return parent::create($data);
    }

    /**
     * Before updating, ensure slug is unique if title changed
     */
    public function updateArticleInstance($data): bool
    {
        if (!empty($data['title']) && $data['title'] !== $this->title) {
            if (empty($data['slug'])) {
                $data['slug'] = static::generateUniqueSlug($data['title'], $this->id);
            } elseif (!static::isSlugUnique($data['slug'], $this->id)) {
                $data['slug'] = static::generateUniqueSlug($data['title'], $this->id);
            }
        }
        
        return parent::updateInstance($data);
    }
}