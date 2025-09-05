<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class TTicket extends Model implements ModelInterface
{
    protected string $table = 'transaction_tickets';
    
    protected array $fillable = [
        'id',
        'transaction_id',
        'ticket_id',
        'quantity',
        'price',
        'service_charge',
        'created_at',
    ];
    
    protected array $hidden = [];

    protected array $casts = [];
    
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
    
    public static function getWithTicketDetails(int $transactionId): array
    {
        $query = static::query()
            ->select([
                'transaction_tickets.*',
                'tickets.ticket_name',
                'tickets.description',
                'tickets.event_id'
            ])
            ->join('tickets', 'transaction_tickets.ticket_id', '=', 'tickets.id')
            ->where('transaction_tickets.transaction_id', $transactionId)
            ->orderBy('transaction_tickets.id');
        
        $results = $query->get();
        
        // Convert to model instances with additional ticket data
        return array_map(function ($result) {
            $instance = new static();
            $instance->fill($result, false); // Don't enforce fillable for this data
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
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
    
    /**
     * Get transaction tickets by ticket ID
     *
     * @param int $ticketId The ticket ID
     * @return array Array of TransactionTicket instances
     */
    public static function getByTicketId(int $ticketId): array
    {
        return static::where(['ticket_id' => $ticketId]);
    }
    
    /**
     * Get total quantity sold for a specific ticket
     *
     * @param int $ticketId The ticket ID
     * @return int Total quantity sold
     */
    public static function getTotalSoldByTicketId(int $ticketId): int
    {
        $query = static::query()
            ->select(['SUM(quantity) as total_sold'])
            ->where('ticket_id', $ticketId);
        
        $result = $query->first();
        return (int) ($result['total_sold'] ?? 0);
    }
    
    /**
     * Get transaction summary with ticket breakdown
     *
     * @param int $transactionId The transaction ID
     * @return array Transaction summary data
     */
    public static function getTransactionSummary(int $transactionId): array
    {
        $transactionTickets = static::getWithTicketDetails($transactionId);
        $totalAmount = static::getTotalAmount($transactionId);
        $totalQuantity = array_sum(array_column($transactionTickets, 'quantity'));
        
        return [
            'tickets' => $transactionTickets,
            'total_amount' => $totalAmount,
            'total_quantity' => $totalQuantity,
            'ticket_count' => count($transactionTickets)
        ];
    }
    
    /**
     * Check if a transaction has specific ticket
     *
     * @param int $transactionId The transaction ID
     * @param int $ticketId The ticket ID
     * @return bool True if transaction has the ticket
     */
    public static function hasTicket(int $transactionId, int $ticketId): bool
    {
        return static::count([
            'transaction_id' => $transactionId,
            'ticket_id' => $ticketId
        ]) > 0;
    }
    
    /**
     * Get revenue breakdown by date range
     *
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @return array Revenue breakdown
     */
    public static function getRevenueBreakdown(string $startDate, string $endDate): array
    {
        $query = static::query()
            ->select([
                'ticket_id',
                'SUM(quantity) as total_quantity',
                'SUM((price + service_charge) * quantity) as total_revenue',
                'COUNT(*) as transaction_count'
            ])
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate])
            ->groupBy(['ticket_id'])
            ->orderBy('total_revenue', 'DESC');
        
        return $query->get();
    }
    
    /**
     * Relationship: Get the associated transaction
     *
     * @return Transaction|null
     */
    public function ticketTransaction(): ?Transaction
    {
        if (!$this->transaction_id) {
            return null;
        }
        
        return Transaction::find($this->transaction_id);
    }
    
    /**
     * Relationship: Get the associated ticket
     *
     * @return Ticket|null
     */
    public function ticket(): ?Ticket
    {
        if (!$this->ticket_id) {
            return null;
        }
        
        return Ticket::find($this->ticket_id);
    }
    
    /**
     * Calculate the subtotal for this transaction ticket line
     *
     * @return float The subtotal amount
     */
    public function getSubtotal(): float
    {
        return ($this->price + $this->service_charge) * $this->quantity;
    }
    
    /**
     * Get formatted price
     *
     * @return string Formatted price
     */
    public function getFormattedPrice(): string
    {
        return '₦' . number_format($this->price, 2);
    }
    
    /**
     * Get formatted service charge
     *
     * @return string Formatted service charge
     */
    public function getFormattedServiceCharge(): string
    {
        return '₦' . number_format($this->service_charge, 2);
    }
    
    /**
     * Get formatted subtotal
     *
     * @return string Formatted subtotal
     */
    public function getFormattedSubtotal(): string
    {
        return '₦' . number_format($this->getSubtotal(), 2);
    }
    
    /**
     * Override toArray to include calculated fields
     *
     * @return array Model attributes as an array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['subtotal'] = $this->getSubtotal();
        $array['formatted_price'] = $this->getFormattedPrice();
        $array['formatted_service_charge'] = $this->getFormattedServiceCharge();
        $array['formatted_subtotal'] = $this->getFormattedSubtotal();
        
        return $array;
    }
}