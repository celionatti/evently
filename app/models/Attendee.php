<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Attendee extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'attendees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'user_id',
        'ticket_id',
        'event_id',
        'transaction_id',
        'name',
        'email',
        'phone',
        'ticket_code',
        'status'
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
            '(email LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    public static function findByEmail(string $email): ?self
    {
        $results = static::where(['email' => $email]);
        return $results ? $results[0] : null;
    }
}