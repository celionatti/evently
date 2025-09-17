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

    protected array $casts = [];

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
}