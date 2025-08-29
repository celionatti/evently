<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Categories extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'slug',
        'name',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        // Add your casts here
    ];

    /**
     * @var array Hidden attributes
     */
    protected array $hidden = [
        // Add your hidden here
    ];

    /**
     * Get the validation rules for this model.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|unique:categories.name',
            'description' => 'required|min:15|string',
        ];
    }


    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(name LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Find a category by name
     *
     * @param string $name Categories name
     * @return static|null The found category or null
     */
    public static function findByName(string $name): ?self
    {
        $results = static::where(['name' => $name]);
        return $results ? $results[0] : null;
    }

    public static function findBySlug(string $slug): ?self
    {
        $results = static::where(['slug' => $slug]);
        return $results ? $results[0] : null;
    }
}