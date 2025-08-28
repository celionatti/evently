<?php

declare(strict_types=1);

namespace Trees\Database\Relationships;

use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Database\Model\Model;

/**
 * =======================================
 * ***************************************
 * ======== Trees BelongsTo Class ========
 * ***************************************
 * =======================================
 */
class BelongsTo
{
    protected QueryBuilder $query;
    protected Model $child;
    protected string $foreignKey;
    protected string $ownerKey;

    public function __construct(
        QueryBuilder $query,
        Model $child,
        string $foreignKey,
        string $ownerKey
    ) {
        $this->query = $query;
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * Get the results of the relationship.
     *
     * @return Model|null
     */
    public function getResults(): ?Model
    {
        return $this->query
            ->where($this->ownerKey, '=', $this->child->{$this->foreignKey})
            ->first();
    }

    /**
     * Associate the model with its parent.
     *
     * @param Model $model
     * @return Model
     */
    public function associate(Model $model): Model
    {
        $this->child->{$this->foreignKey} = $model->{$this->ownerKey};
        return $this->child;
    }

    /**
     * Dissociate the model from its parent.
     *
     * @return Model
     */
    public function dissociate(): Model
    {
        $this->child->{$this->foreignKey} = null;
        return $this->child;
    }
}