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

    public static function getByTransactionId(int $transaction_id): array
    {
        return static::where(['transaction_id' => $transaction_id]);
    }

    public static function getByTicketId(int $ticketId): array
    {
        return static::where(['ticket_id' => $ticketId]);
    }

    /**
     * Get total amount for a transaction
     *
     * @param int $transactionId The transaction ID
     * @return float The total amount
     */
    public static function getTotalAmount(int $transactionId): float
    {
        $query = static::query()
            ->select(['SUM((price + service_charge) * quantity) as total'])
            ->where('transaction_id', $transactionId);
        
        $result = $query->first();
        return (float) ($result['total'] ?? 0.0);
    }

    public static function getTotalSoldByTicketId(int $ticketId): int
    {
        $query = static::query()
            ->select(['SUM(quantity) as total_sold'])
            ->where('ticket_id', $ticketId);
        
        $result = $query->first();
        return (int) ($result['total_sold'] ?? 0);
    }
}