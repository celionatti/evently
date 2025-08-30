<?php

declare(strict_types=1);

namespace App\models;

use App\models\Ticket;
use Trees\Database\Model\Model;
use Trees\Database\Relationships\HasMany;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Event extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'slug',
        'user_id',
        'tags',
        'category',
        'event_title',
        'event_image',
        'description',
        'event_link',
        'venue',
        'city',
        'event_date',
        'start_time',
        'end_date',
        'end_time',
        'phone',
        'mail',
        'social',
        'featured',
        'ticket_sales',
        'status'
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
        return [];
    }


    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(event_title LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    public static function findBySlug(string $slug): ?self
    {
        $results = static::where(['slug' => $slug]);
        return $results ? $results[0] : null;
    }

    public static function findByEventId(string $event_id): ?self
    {
        $results = static::where(['event_id' => $event_id]);
        return $results ? $results[0] : null;
    }

    /**
     * Define the relationship with tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'event_id', 'id');
    }
}
