<?php

declare(strict_types=1);

namespace Trees\Database;

/**
 * =======================================
 * ***************************************
 * ======== Trees Database Class =========
 * ***************************************
 * =======================================
 */

use Trees\Database\Interface\DatabaseInterface;
use Trees\Database\Factory\DatabaseFactory;
use Trees\Logger\Logger;
use Trees\Exception\TreesException;

class Database
{
    /**
     * @var DatabaseInterface The database connection
     */
    private static ?DatabaseInterface $instance = null;

    /**
     * Get the database instance (Singleton pattern)
     *
     * @return DatabaseInterface|null The database instance or null on error
     */
    public static function getInstance(): ?DatabaseInterface
    {
        return self::$instance;
    }

    /**
     * Initialize the database connection
     *
     * @param string $type Database type
     * @param array $config Connection configuration
     * @return bool True on success, false on failure
     */
    public static function init(string $type, array $config): bool
    {
        try {
            self::$instance = DatabaseFactory::create($type, $config);

            if (self::$instance === null) {
                throw new TreesException("Failed to create database instance");
            }

            return true;
        } catch (TreesException $e) {
            // Log the error if a logger is configured
            Logger::error('Database initialization error: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Close the database connection
     *
     * @return bool True on success, false on failure
     */
    public static function close(): bool
    {
        if (self::$instance !== null) {
            $result = self::$instance->close();
            self::$instance = null;
            return $result;
        }

        return true;
    }
}