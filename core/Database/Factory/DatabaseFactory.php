<?php

declare(strict_types=1);

namespace Trees\Database\Factory;

/**
 * =======================================
 * ***************************************
 * ====== Trees DatabaseFactory Class ====
 * ***************************************
 * =======================================
 */

use Trees\Database\Interface\DatabaseInterface;
use Trees\Database\Drivers\MySQLDatabase;
use Trees\Database\Drivers\PostgreSQLDatabase;
use Trees\Database\Drivers\SQLiteDatabase;
use Trees\Logger\Logger;
use Trees\Exception\TreesException;

class DatabaseFactory
{
    /**
     * Create a database connection based on the type
     *
     * @param string $type Database type (mysql, mariadb, pgsql, sqlite)
     * @param array $config Connection configuration
     * @return DatabaseInterface|null The database object or null on error
     */
    public static function create(string $type, array $config = []): ?DatabaseInterface
    {
        try {
            $db = match (strtolower($type)) {
                'mysql', 'mariadb' => new MySQLDatabase(),
                'pgsql', 'postgres', 'postgresql' => new PostgreSQLDatabase(),
                'sqlite' => new SQLiteDatabase(),
                default => throw new \InvalidArgumentException("Unsupported database type: $type")
            };

            if (!empty($config) && !$db->connect($config)) {
                throw new TreesException("Failed to connect to the database: " . $db->getLastError());
            }

            return $db;
        } catch (TreesException $e) {
            // Log the error if a logger is configured
            Logger::error('Database factory error: ' . $e->getMessage());

            return null;
        }
    }
}