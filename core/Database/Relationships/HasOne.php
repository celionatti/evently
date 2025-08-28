<?php

declare(strict_types=1);

namespace Trees\Database\Relationships;

use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Database\Model\Model;

class HasOne extends HasMany
{
    /**
     * Get the results of the relationship.
     * Overrides HasMany's getResults() to return a single model.
     *
     * @return Model|null
     */
    public function getResults(): ?Model
    {
        return $this->first();
    }

    /**
     * Add constraints to the relationship query.
     * Overrides HasMany's constraints to limit to 1 result.
     */
    protected function addConstraints(): void
    {
        parent::addConstraints();
        $this->query->limit(1);
    }
}