<?php

declare(strict_types=1);

namespace Trees\Database\Relationships\Traits;

use Trees\Database\Relationships\BelongsTo;
use Trees\Database\Relationships\HasMany;
use Trees\Database\Relationships\HasOne;
use Trees\Database\QueryBuilder\QueryBuilder;

/**
 * =======================================
 * ***************************************
 * ===== Trees HasRelationships Trait ====
 * ***************************************
 * =======================================
 */
trait HasRelationships
{
    /**
     * Define a one-to-one relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey The foreign key of the parent model
     * @param string|null $localKey The local key of the parent model
     * @return HasOne
     */
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        $instance = new $related();

        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        return new HasOne(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey
        );
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey The foreign key of the parent model
     * @param string|null $localKey The local key of the parent model
     * @return HasMany
     */
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        $instance = new $related();

        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        return new HasMany(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey
        );
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param string $related The related model class
     * @param string|null $foreignKey The foreign key of the child model
     * @param string|null $ownerKey The primary key of the related model
     * @return BelongsTo
     */
    public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        $instance = new $related();

        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new BelongsTo(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey
        );
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get a new query builder for the model.
     *
     * @return QueryBuilder
     */
    public function newQuery(): QueryBuilder
    {
        return static::query();
    }
}