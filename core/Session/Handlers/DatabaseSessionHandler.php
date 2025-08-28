<?php

declare(strict_types=1);

namespace Trees\Session;

use PDO;
use PDOException;
use RuntimeException;
use Trees\Session\SessionHandler;

/**
 * ========================================
 * ****************************************
 * ======= DatabaseSessionHandler Class ===
 * Handles session storage in database with
 * PDO
 * ****************************************
 * ========================================
 */

class DatabaseSessionHandler extends SessionHandler
{
    private PDO $pdo;
    private string $tableName = 'sessions';
    private int $gcProbability = 1;
    private int $gcDivisor = 100;

    public function __construct(
        PDO $pdo,
        string $tableName = 'sessions',
        int $gcProbability = 1,
        int $gcDivisor = 100
    ) {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->gcProbability = $gcProbability;
        $this->gcDivisor = $gcDivisor;

        // Validate GC probability
        if ($gcProbability < 0 || $gcDivisor <= 0 || $gcProbability > $gcDivisor) {
            throw new RuntimeException('Invalid garbage collection probability configuration');
        }

        // Set more secure session settings
        ini_set('session.sid_length', '128');
        ini_set('session.sid_bits_per_character', '6');
        ini_set('session.gc_probability', $this->gcProbability);
        ini_set('session.gc_divisor', $this->gcDivisor);

        $this->createTableIfNotExists();

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );

        // Prevent session fixation
        register_shutdown_function('session_write_close');
        $this->start();
    }

    /**
     * Create sessions table if it doesn't exist
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            data LONGTEXT NOT NULL,
            timestamp INT NOT NULL,
            INDEX (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT data FROM {$this->tableName} WHERE id = :id"
            );
            $stmt->bindValue(':id', $sessionId, PDO::PARAM_STR);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['data'] ? base64_decode($row['data']) : '';
            }

            return '';
        } catch (PDOException $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }

    public function write(string $sessionId, string $data): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "REPLACE INTO {$this->tableName} (id, data, timestamp)
                VALUES (:id, :data, :timestamp)"
            );

            return $stmt->execute([
                ':id' => $sessionId,
                ':data' => base64_encode($data),
                ':timestamp' => time(),
            ]);
        } catch (PDOException $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $sessionId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->tableName} WHERE id = :id"
            );
            return $stmt->execute([':id' => $sessionId]);
        } catch (PDOException $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    public function gc(int $maxLifetime): int|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->tableName} WHERE timestamp < :timestamp"
            );
            $stmt->execute([':timestamp' => time() - $maxLifetime]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Session garbage collection error: " . $e->getMessage());
            return false;
        }
    }

    public function updateTimestamp(string $sessionId, string $data): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->tableName} SET timestamp = :timestamp WHERE id = :id"
            );
            return $stmt->execute([
                ':id' => $sessionId,
                ':timestamp' => time(),
            ]);
        } catch (PDOException $e) {
            error_log("Session timestamp update error: " . $e->getMessage());
            return false;
        }
    }
}