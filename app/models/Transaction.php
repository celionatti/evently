<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Transaction extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'transaction_id',
        'user_id',
        'ticket_id',
        'event_id',
        'quantity',
        'email',
        'amount',
        'reference_id',
        'status',
        'created_at',
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