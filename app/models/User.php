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
        'remember_token',
        'login_attempts',
        'blocked_until',
        'last_login_attempt',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'is_blocked' => 'boolean',
        'login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'blocked_until' => 'datetime',
        'last_login_attempt' => 'datetime'
    ];

    /**
     * Attributes to hide when serializing.
     */
    protected array $hidden = ['password', 'remember_token'];

    /**
     * Get the validation rules for this model.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:2|max:50',
            'other_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users.email',
            'password' => 'required|min:8',
            'role' => 'in:admin,guest,organiser'
        ];
    }

    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(name LIKE :search OR other_name LIKE :search OR email LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::first(['email' => strtolower(trim($email))]);
    }

    /**
     * Find user by user ID
     */
    public static function findByUserId(string $user_id): ?self
    {
        return static::first(['user_id' => $user_id]);
    }

    /**
     * Find user by remember token
     */
    public static function findByRememberToken(string $token): ?self
    {
        return static::first(['remember_token' => $token]);
    }

    /**
     * Verify user password.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Check if user is permanently blocked.
     */
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    /**
     * Check if user is temporarily blocked due to failed login attempts
     */
    public function isTemporarilyBlocked(): bool
    {
        if (!$this->blocked_until) {
            return false;
        }

        $blockedUntil = new \DateTime($this->blocked_until);
        $now = new \DateTime();

        // If block period has expired, auto-unblock
        if ($now >= $blockedUntil) {
            // Use static updateWhere to avoid return type issues
            static::updateWhere(['email' => $this->email], [
                'login_attempts' => 0,
                'blocked_until' => null,
                'last_login_attempt' => null
            ]);
            
            // Update current instance attributes
            $this->login_attempts = 0;
            $this->blocked_until = null;
            $this->last_login_attempt = null;
            
            return false;
        }

        return true;
    }

    /**
     * Get remaining block time in minutes
     */
    public function getRemainingBlockTime(): int
    {
        if (!$this->blocked_until) {
            return 0;
        }

        $blockedUntil = new \DateTime($this->blocked_until);
        $now = new \DateTime();

        if ($now >= $blockedUntil) {
            return 0;
        }

        return (int) $now->diff($blockedUntil)->format('%i');
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(): void
    {
        $attempts = ($this->login_attempts ?? 0) + 1;
        $now = new \DateTime();

        $updateData = [
            'login_attempts' => $attempts,
            'last_login_attempt' => $now->format('Y-m-d H:i:s')
        ];

        // Block temporarily if max attempts reached
        if ($attempts >= 3) {
            $blockedUntil = clone $now;
            $blockedUntil->add(new \DateInterval('PT30M')); // 30 minutes
            $updateData['blocked_until'] = $blockedUntil->format('Y-m-d H:i:s');
        }

        // Use static updateWhere to avoid return type issues
        static::updateWhere(['email' => $this->email], $updateData);
        
        // Update current instance attributes
        $this->login_attempts = $attempts;
        $this->last_login_attempt = $now->format('Y-m-d H:i:s');
        if (isset($updateData['blocked_until'])) {
            $this->blocked_until = $updateData['blocked_until'];
        }
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts(): void
    {
        // Use static updateWhere to avoid return type issues
        static::updateWhere(['email' => $this->email], [
            'login_attempts' => 0,
            'blocked_until' => null,
            'last_login_attempt' => null
        ]);
        
        // Update current instance attributes
        $this->login_attempts = 0;
        $this->blocked_until = null;
        $this->last_login_attempt = null;
    }

    /**
     * Block user permanently
     */
    public function blockUser(): void
    {
        $this->update(['is_blocked' => 1]);
    }

    /**
     * Unblock user
     */
    public function unblockUser(): void
    {
        $this->update([
            'is_blocked' => 0,
            'login_attempts' => 0,
            'blocked_until' => null,
            'last_login_attempt' => null
        ]);
    }

    /**
     * Generate unique user ID.
     */
    public static function generateUserId(string $firstName, string $lastName): string
    {
        $slug = str_slug($firstName . ' ' . $lastName);
        return 'USR_' . uniqid() . '_' . substr($slug, 0, 20);
    }

    /**
     * Get user's full name
     */
    public function getFullName(): string
    {
        return trim($this->name . ' ' . $this->other_name);
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has organiser role
     */
    public function isOrganiser(): bool
    {
        return $this->role === 'organiser';
    }

    /**
     * Check if user has guest role
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Update last login time
     */
    public function updateLastLogin(): void
    {
        $this->update(['updated_at' => new \DateTime()]);
    }
}