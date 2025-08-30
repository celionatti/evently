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
        'slug',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'business_name',
        'registration_number',
        'role',
        'is_blocked',
        'country',
        'remember_token',
        'reset_token',
        'reset_token_expiry'
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
            '(name LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }

    /**
     * Find a user by email
     *
     * @param string $email User email
     * @return static|null The found user or null
     */
    public static function findByName(string $email): ?self
    {
        $results = static::where(['email' => $email]);
        return $results ? $results[0] : null;
    }

    public static function findBySlug(string $slug): ?self
    {
        $results = static::where(['slug' => $slug]);
        return $results ? $results[0] : null;
    }
}
