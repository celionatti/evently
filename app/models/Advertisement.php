<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Advertisement extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'advertisements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'title',
        'description',
        'image_url',
        'target_url',
        'ad_type',
        'is_featured',
        'start_date',
        'end_date',
        'is_active',
        'clicks',
        'impressions',
        'priority',
        'created_at',
        'updated_at'
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

    public static function findByTitle(string $title): ?self
    {
        $results = static::where(['title' => $title]);
        return $results ? $results[0] : null;
    }
}