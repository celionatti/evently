<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class TransactionTicket extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'transaction_tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'transaction_id',
        'ticket_id',
        'quantity',
        'price',
        'service_charge',
        'created_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [];

    /**
     * Attributes to hide when serializing.
     */
    protected array $hidden = [];

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
            '(name LIKE :search OR other_name LIKE :search OR email LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Find user by transaction_id
     */
    public static function findByTransactionId(string $transaction_id): ?self
    {
        return static::first(['transaction_id' => $transaction_id]);
    }
}