<?php

declare(strict_types=1);

namespace Trees\Database\QueryBuilder;

/**
 * =======================================
 * ***************************************
 * ====== Trees QueryBuilder Class =======
 * ***************************************
 * =======================================
 */

use Exception;
use Trees\Database\Interface\DatabaseInterface;
use Trees\Exception\TreesException;

class oldQueryBuilder
{
    /**
     * @var DatabaseInterface The database connection
     */
    private DatabaseInterface $db;

    /**
     * @var string The table name
     */
    private string $table = '';

    /**
     * @var array Query parts
     */
    private array $parts = [
        'select' => ['*'],
        'joins' => [],
        'where' => [],
        'having' => [],
        'group' => [],
        'order' => [],
        'limit' => null,
        'offset' => null,
        'params' => [],
        'unions' => []
    ];

    /**
     * Constructor
     *
     * @param DatabaseInterface $db The database connection
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Set the table to query
     *
     * @param string $table The table name
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the columns to select
     *
     * @param string|array $columns The columns to select
     * @return self
     */
    public function select(string|array $columns): self
    {
        $this->parts['select'] = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function addSelect(string|array $columns): self
    {
        $newColumns = is_array($columns) ? $columns : [$columns];
        $this->parts['select'] = array_merge($this->parts['select'], $newColumns);
        return $this;
    }

    /**
     * Add a WHERE condition
     *
     * @param string $column The column name
     * @param mixed $value The value to compare with
     * @param string $operator The comparison operator
     * @return self
     */
    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $paramName = $this->generateParamName($column);
        $this->parts['where'][] = "$column $operator :$paramName";
        $this->parts['params'][$paramName] = $value;
        return $this;
    }

    /**
     * Add a WHERE NOT condition
     *
     * @param string $column The column name
     * @param mixed $value The value to compare with
     * @param string $operator The comparison operator (default is '=')
     * @return self
     */
    public function whereNot(string $column, mixed $value, string $operator = '='): self
    {
        $paramName = $this->generateParamName($column);
        $this->parts['where'][] = "NOT ($column $operator :$paramName)";
        $this->parts['params'][$paramName] = $value;
        return $this;
    }

    /**
     * Add a WHERE IN condition
     *
     * @param string $column The column name
     * @param array $values The values to compare against
     * @return self
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            $this->parts['where'][] = "1 = 0"; // Return no results
            return $this;
        }

        $paramNames = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName($column);
            $paramNames[] = ":$paramName";
            $this->parts['params'][$paramName] = $value;
        }

        $this->parts['where'][] = "$column IN (" . implode(', ', $paramNames) . ")";
        return $this;
    }

    /**
     * Add a WHERE NOT IN condition
     *
     * @param string $column The column name
     * @param array $values The values to compare against
     * @return self
     */
    public function whereNotIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this; // Return all results
        }

        $paramNames = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName($column);
            $paramNames[] = ":$paramName";
            $this->parts['params'][$paramName] = $value;
        }

        $this->parts['where'][] = "$column NOT IN (" . implode(', ', $paramNames) . ")";
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->parts['where'][] = "$column IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->parts['where'][] = "$column IS NOT NULL";
        return $this;
    }

    /**
     * Add a raw WHERE condition
     *
     * @param string $condition The raw condition
     * @param array $params The parameters for the condition
     * @return self
     */
    public function whereRaw(string $condition, array $params = []): self
    {
        $this->parts['where'][] = $condition;
        $this->parts['params'] = array_merge($this->parts['params'], $params);
        return $this;
    }

    public function having(string $column, mixed $value, string $operator = '='): self
    {
        $paramName = $this->generateParamName($column);
        $this->parts['having'][] = "$column $operator :$paramName";
        $this->parts['params'][$paramName] = $value;
        return $this;
    }

    public function havingRaw(string $condition, array $params = []): self
    {
        $this->parts['having'][] = $condition;
        $this->parts['params'] = array_merge($this->parts['params'], $params);
        return $this;
    }

    public function groupBy(string|array $columns): self
    {
        $this->parts['group'] = array_merge(
            $this->parts['group'],
            is_array($columns) ? $columns : [$columns]
        );
        return $this;
    }

    /**
     * Add an ORDER BY clause
     *
     * @param string $column The column to order by
     * @param string $direction The order direction (ASC or DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->parts['order'][] = "$column $direction";
        return $this;
    }

    /**
     * Set the LIMIT clause
     *
     * @param int $limit The maximum number of rows to return
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->parts['limit'] = $limit;
        return $this;
    }

    /**
     * Set the OFFSET clause
     *
     * @param int $offset The number of rows to skip
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->parts['offset'] = $offset;
        return $this;
    }

    /**
     * Add an INNER JOIN clause
     *
     * @param string $table The table to join
     * @param string $first The first column to join on
     * @param string $operator The comparison operator
     * @param string $second The second column to join on
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('INNER', $table, $first, $operator, $second);
    }

    /**
     * Add a LEFT JOIN clause
     *
     * @param string $table The table to join
     * @param string $first The first column to join on
     * @param string $operator The comparison operator
     * @param string $second The second column to join on
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('LEFT', $table, $first, $operator, $second);
    }

    /**
     * Add a RIGHT JOIN clause
     *
     * @param string $table The table to join
     * @param string $first The first column to join on
     * @param string $operator The comparison operator
     * @param string $second The second column to join on
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('RIGHT', $table, $first, $operator, $second);
    }

    /**
     * Add a raw JOIN clause
     *
     * @param string $type The join type (INNER, LEFT, RIGHT)
     * @param string $table The table to join
     * @param string $condition The raw join condition
     * @param array $params Parameters for the condition
     * @return self
     */
    public function joinRaw(string $type, string $table, string $condition, array $params = []): self
    {
        $this->parts['joins'][] = [
            'type' => strtoupper($type),
            'table' => $table,
            'condition' => $condition
        ];
        $this->parts['params'] = array_merge($this->parts['params'], $params);
        return $this;
    }

    public function union(self $query): self
    {
        $this->parts['unions'][] = $query;
        return $this;
    }

    /**
     * Count the number of records
     *
     * @return int The count of records
     * @throws Exception
     */
    public function count(): int
    {
        if (empty($this->table)) {
            throw new TreesException("Table name is required");
        }

        $originalSelect = $this->parts['select'];
        $this->parts['select'] = ['COUNT(*) as count'];

        try {
            $query = $this->buildQuery();
            $result = $this->db->query($query, $this->parts['params']);
            return (int) ($result[0]['count'] ?? 0);
        } finally {
            $this->parts['select'] = $originalSelect;
        }
    }

    /**
     * @throws Exception
     */
    public function exists(): bool
    {
        $this->limit(1);
        $query = "SELECT EXISTS(" . $this->buildQuery() . ") as exists_flag";
        $result = $this->db->query($query, $this->parts['params']);
        return (bool) ($result[0]['exists_flag'] ?? false);
    }

    /**
     * Execute the query and return the result
     *
     * @return mixed The query result
     * @throws Exception
     */
    public function get(): array
    {
        $query = $this->buildQuery();
        return $this->db->query($query, $this->parts['params']) ?: [];
    }

    /**
     * Execute the query and return the first result
     *
     * @return array|null The first result or null if no results
     * @throws Exception
     */
    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    /**
     * Insert a record into the table
     *
     * @param array $data The data to insert
     * @return mixed The query result
     * @throws Exception
     */
    public function insert(array $data): bool
    {
        if (empty($this->table)) {
            throw new TreesException("Table name is required");
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $query = "INSERT INTO " . $this->table . " (" . implode(', ', $columns) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";

        return (bool) $this->db->query($query, $data);
    }

    /**
     * Update records in the table
     *
     * @param array $data The data to update
     * @return int The number of affected rows
     * @throws Exception
     */

    //     public function update(array $data): int
    //     {
    //         if (empty($this->table)) {
    //             throw new TreesException("Table name is required");
    //         }

    //         if (empty($this->parts['where'])) {
    //             throw new TreesException("WHERE clause is required for UPDATE");
    //         }

    //         $sets = [];
    //         $params = $this->parts['params'];

    //         foreach ($data as $column => $value) {
    //             $paramName = $this->generateParamName($column);
    //             $sets[] = "$column = :$paramName";
    //             $params[$paramName] = $value;
    //         }

    //         $query = "UPDATE " . $this->table . " SET " . implode(', ', $sets);

    //         if (!empty($this->parts['where'])) {
    //             $query .= " WHERE " . implode(' AND ', $this->parts['where']);
    //         }

    //         return $this->db->query($query, $params);
    // //        return $this->db->rowCount();
    //     }

    public function update(array $data): int
    {
        if (empty($this->table)) {
            throw new TreesException("Table name is required");
        }

        if (empty($this->parts['where'])) {
            throw new TreesException("WHERE clause is required for UPDATE");
        }

        $sets = [];
        $params = $this->parts['params'];

        foreach ($data as $column => $value) {
            $paramName = $this->generateParamName($column);
            $sets[] = "$column = :$paramName";
            $params[$paramName] = $value;
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $sets);

        if (!empty($this->parts['where'])) {
            $query .= " WHERE " . implode(' AND ', $this->parts['where']);
        }

        try {
            // Execute the query - this will return true/false
            $result = $this->db->query($query, $params);

            // If query succeeded, return the number of affected rows
            if ($result !== false) {
                return $this->db->rowCount();
            }

            return 0;
        } catch (Exception $e) {
            // Log the error if logger is available
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }
            return 0;
        }
    }

    /**
     * Delete records from the table
     *
     * @return int The number of affected rows
     * @throws Exception
     */
    //     public function delete(): int
    //     {
    //         if (empty($this->table)) {
    //             throw new TreesException("Table name is required");
    //         }

    //         if (empty($this->parts['where'])) {
    //             throw new TreesException("WHERE clause is required for DELETE");
    //         }

    //         $query = "DELETE FROM " . $this->table;

    //         if (!empty($this->parts['where'])) {
    //             $query .= " WHERE " . implode(' AND ', $this->parts['where']);
    //         }

    //         return $this->db->query($query, $this->parts['params']);
    // //        return $this->db->rowCount();
    //     }

    public function delete(): int
    {
        if (empty($this->table)) {
            throw new TreesException("Table name is required");
        }

        if (empty($this->parts['where'])) {
            throw new TreesException("WHERE clause is required for DELETE");
        }

        $query = "DELETE FROM " . $this->table;

        if (!empty($this->parts['where'])) {
            $query .= " WHERE " . implode(' AND ', $this->parts['where']);
        }

        try {
            // Execute the query - this will return true/false
            $result = $this->db->query($query, $this->parts['params']);
            
            // If query succeeded, return the number of affected rows
            if ($result !== false) {
                return $this->db->rowCount();
            }
            
            return 0;
        } catch (Exception $e) {
            // Log the error if logger is available
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }
            return 0;
        }
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Internal method to add a join
     *
     * @param string $type The join type (INNER, LEFT, RIGHT)
     * @param string $table The table to join
     * @param string $first The first column to join on
     * @param string $operator The comparison operator
     * @param string $second The second column to join on
     * @return self
     */
    private function addJoin(string $type, string $table, string $first, string $operator, string $second): self
    {
        $this->parts['joins'][] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * Build the SQL query
     *
     * @return string The SQL query
     * @throws Exception
     */
    public function buildQuery(): string
    {
        if (empty($this->table)) {
            throw new TreesException("Table name is required");
        }

        $query = "SELECT " . implode(', ', $this->parts['select']) . " FROM " . $this->table;

        // Add joins
        foreach ($this->parts['joins'] as $join) {
            if (isset($join['condition'])) {
                $query .= " {$join['type']} JOIN {$join['table']} ON {$join['condition']}";
            } else {
                $query .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }

        if (!empty($this->parts['where'])) {
            $query .= " WHERE " . implode(' AND ', $this->parts['where']);
        }

        if (!empty($this->parts['group'])) {
            $query .= " GROUP BY " . implode(', ', $this->parts['group']);
        }

        if (!empty($this->parts['having'])) {
            $query .= " HAVING " . implode(' AND ', $this->parts['having']);
        }

        if (!empty($this->parts['order'])) {
            $query .= " ORDER BY " . implode(', ', $this->parts['order']);
        }

        if ($this->parts['limit'] !== null) {
            $query .= " LIMIT " . $this->parts['limit'];

            if ($this->parts['offset'] !== null) {
                $query .= " OFFSET " . $this->parts['offset'];
            }
        }

        // Handle unions
        if (!empty($this->parts['unions'])) {
            foreach ($this->parts['unions'] as $union) {
                $query .= " UNION " . $union->buildQuery();
                $this->parts['params'] = array_merge($this->parts['params'], $union->getParams());
            }
        }

        return $query;
    }

    /**
     * Get the parameters for the query
     *
     * @return array The query parameters
     */
    public function getParams(): array
    {
        return $this->parts['params'];
    }

    public function getTable(): string
    {
        return $this->table;
    }

    private function generateParamName(string $column): string
    {
        $base = str_replace(['.', '(', ')', ' '], '_', $column);
        $paramName = $base;
        $counter = 1;

        while (isset($this->parts['params'][$paramName])) {
            $paramName = $base . '_' . $counter++;
        }

        return $paramName;
    }
}
