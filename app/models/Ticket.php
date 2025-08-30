<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Relationships\BelongsTo;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Ticket extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'slug',
        'event_id',
        'ticket_name',
        'description',
        'price',
        'quantity'
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
            '(ticket_name LIKE :search)',
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
     * Define the relationship with event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
