<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Transaction extends Model implements ModelInterface
{
    protected string $table = 'transactions';

    protected array $fillable = [
        'id',
        'transaction_id',
        'user_id',
        'event_id',
        'email',
        'amount',
        'reference_id',
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