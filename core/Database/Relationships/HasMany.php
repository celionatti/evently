<?php

declare(strict_types=1);

namespace Trees\Database\Relationships;

use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Database\Model\Model;

class HasMany
{
    protected QueryBuilder $query;
    protected Model $parent;
    protected string $foreignKey;
    protected string $localKey;

    public function __construct(
        QueryBuilder $query,
        Model $parent,
        string $foreignKey,
        string $localKey
    ) {
        $this->query = $query;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        $this->addConstraints();
    }

    /**
     * Add constraints to the relationship query
     */
    protected function addConstraints(): void
    {
        $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
    }

    /**
     * Add a where clause to the relationship query
     */
    public function where(string $column, $value, string $operator = '='): self
    {
        $this->query->where($column, $value, $operator);
        return $this;
    }

    /**
     * Get the results of the relationship
     */
    public function getResults(): array
    {
        $results = $this->query->get();

        if (empty($results)) {
            return [];
        }

        $modelClass = get_class($this->parent);
        $models = [];

        foreach ($results as $result) {
            $model = new $modelClass();
            $model->fill($result);
            $model->exists = true;
            $model->original = $result;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Get the first result of the relationship
     */
    public function first(): ?Model
    {
        $result = $this->query->first();

        if (!$result) {
            return null;
        }

        $modelClass = get_class($this->parent);
        $model = new $modelClass();
        $model->fill($result);
        $model->exists = true;
        $model->original = $result;

        return $model;
    }

    /**
     * Get the query builder instance
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
}