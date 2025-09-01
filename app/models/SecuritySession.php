<?php

declare(strict_types=1);

namespace App\models;

use Trees\Database\Model\Model;


class SecuritySession extends Model
{
    protected string $table = 'security_sessions';
   
    protected array $fillable = [
        'user_id',
        'session_token',
        'expires_at',
        'completed',
        'completed_at',
        'created_at'
    ];

    public static function findByToken(string $token): ?self
    {
        return static::first(['session_token' => $token]);
    }

    public static function updateByToken(string $token, array $data): bool
    {
        return static::updateWhere(['session_token' => $token], $data);
    }
}