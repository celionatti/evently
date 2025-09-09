<?php

declare(strict_types=1);

namespace Trees\Database\Drivers;

/**
 * =======================================
 * ***************************************
 * ==== Trees PostgreSQLDatabase Class ===
 * ***************************************
 * =======================================
 */

use PDOStatement;
use Trees\Database\AbstractDatabase;

class PostgreSQLDatabase extends AbstractDatabase
{
    /**
     * @var PDOStatement|null The last executed statement
     */
    protected ?PDOStatement $lastStatement = null;

    /**
     * @var array Default configuration values
     */
    protected array $defaultConfig = [
        'host' => 'localhost',
        'port' => 5432,
        'database' => '',
        'username' => '',
        'password' => '',
        'options' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]
    ];

    /**
     * Connect to the database
     *
     * @param array $config Connection configuration parameters
     * @return bool True on success, false on failure
     */
    public function connect(array $config): bool
    {
        try {
            $config = $this->mergeConfig($config);

            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $config['host'],
                $config['port'],
                $config['database']
            );

            $this->connection = new \PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            return true;
        } catch (\PDOException $e) {
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Execute a query and return the result
     *
     * @param string $query The SQL query to execute
     * @param array $params Parameters to bind to the query
     * @return mixed The query result or false on failure
     */
    public function query(string $query, array $params = []): mixed
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            $stmt = $this->connection->prepare($query);

            if (!$stmt) {
                throw new \Exception("Failed to prepare statement: " . implode(' ', $this->connection->errorInfo()));
            }

            if (!$stmt->execute($params)) {
                throw new \Exception("Failed to execute query: " . implode(' ', $stmt->errorInfo()));
            }

            // Store the statement for rowCount() calls
            $this->lastStatement = $stmt;

            // Determine if this is a SELECT query
            if (stripos(trim($query), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }

            // For non-SELECT queries, return true to indicate success
            return true;
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            $this->lastStatement = null;
            return false;
        }
    }

    public function prepare(string $query): mixed
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            $stmt = $this->connection->prepare($query);

            if (!$stmt) {
                throw new \Exception("Failed to prepare statement: " . implode(' ', $this->connection->errorInfo()));
            }

            // Store the statement for rowCount() calls
            $this->lastStatement = $stmt;

            // Determine if this is a SELECT query
            if (stripos(trim($query), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }

            // For non-SELECT queries, return true to indicate success
            return $stmt;
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            $this->lastStatement = null;
            return false;
        }
    }

    /**
     * Get the number of rows affected by the last DELETE, INSERT, or UPDATE statement
     *
     * @return int The number of affected rows
     */
    public function rowCount(): int
    {
        if ($this->lastStatement === null) {
            return 0;
        }

        try {
            return $this->lastStatement->rowCount();
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            return 0;
        }
    }

    /**
     * Begin a transaction
     *
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            return $this->connection->beginTransaction();
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Commit a transaction
     *
     * @return bool True on success, false on failure
     */
    public function commit(): bool
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            return $this->connection->commit();
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Rollback a transaction
     *
     * @return bool True on success, false on failure
     */
    public function rollback(): bool
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            return $this->connection->rollBack();
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    /**
     * Get the last inserted ID (with sequence name for PostgreSQL)
     *
     * @param string $sequenceName The name of the sequence (optional)
     * @return int|string The last inserted ID
     */
    public function lastInsertId(string $sequenceName = ''): int|string
    {
        try {
            if (!$this->connection) {
                throw new \Exception("Database connection not established");
            }

            return $this->connection->lastInsertId($sequenceName);
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            return 0;
        }
    }

    /**
     * Get the driver name
     *
     * @return string The driver name
     */
    protected function getDriverName(): string
    {
        return 'postgresql';
    }

    /**
     * Close the database connection
     *
     * @return bool True on success, false on failure
     */
    public function close(): bool
    {
        $this->connection = null;
        $this->lastStatement = null;
        return true;
    }
}