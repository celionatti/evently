<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class User extends Model implements ModelInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'id',
        'user_id',
        'name',
        'other_name',
        'email',
        'password',
        'role',
        'business_name',
        'is_blocked',
        // 'remember_token',
        // 'token',
        // 'token_expire',
        'security_pin',
        'recovery_phrase',
        'security_setup_completed',
        'created_at',
        'updated_at'
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
        'password',
        'security_pin',
        'recovery_phrase',
        'remember_token',
        'token'
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
            '(name LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    public static function findByEmail(string $email): ?self
    {
        return static::first(['email' => $email]);
    }

    public static function findByUserId(string $user_id): ?self
    {
        return static::first(['user_id' => $user_id]);
    }

    public static function updateSecurityData(string $userId, array $data): bool
    {
        return static::updateWhere(['user_id' => $userId], $data);
    }

    public static function getSecurityData(string $userId): ?array
    {
        $user = static::first(['user_id' => $userId]);
        if (!$user) {
            return null;
        }

        return [
            'security_pin' => $user->security_pin ?? null,
            'recovery_phrase' => $user->recovery_phrase ?? null,
            'security_setup_completed' => (bool)($user->security_setup_completed ?? 0)
        ];
    }
}