<?php

declare(strict_types=1);

namespace Trees\Database\Interface;

use JsonSerializable;
use Trees\Database\QueryBuilder\QueryBuilder;

/**
 * =======================================
 * ***************************************
 * ===== Trees ModelInterface Class ======
 * ***************************************
 * =======================================
 */

interface ModelInterface extends JsonSerializable
{
    /**
     * Apply search conditions to the query
     *
     * @param QueryBuilder $query
     * @param string $searchTerm
     * @return void
     */
    public function applySearch(QueryBuilder $query, string $searchTerm): void;

    /**
     * Get the table name for the model
     *
     * @return string
     */
    public function getTable(): string;
}