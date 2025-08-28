<?php

declare(strict_types=1);

namespace Trees\Authentication;

use DateTime;
use PDO;
use PDOException;
use Exception;
use Trees\Session\Handlers\DefaultSessionHandler;

/**
 * =======================================
 * ***************************************
 * ====== Trees Authentication Class =====
 * ***************************************
 * =======================================
 */

class Authentication
{
    protected DefaultSessionHandler $session;
    protected ?array $loggedInUser = null;
    protected PDO $pdo;
    protected array $config;

    public function __construct(?PDO $pdo = null, array $config = [])
    {
        $this->config = array_merge([
            'remember_me_name' => 'tre_remember_me',
            'url_root' => '/',
            'max_login_attempts' => 5,
            'block_duration_base' => 5, // minutes
            'session_token_length' => 32,
            'remember_token_length' => 32,
            'remember_token_expiry' => 30 * 24 * 60 * 60, // 30 days in seconds
            'cookie_params' => [
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        ], $config);

        $this->session = new DefaultSessionHandler();

        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = new PDO('sqlite:' . ROOT_PATH . '/storage/auth.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeDatabase();
        }
    }

    protected function initializeDatabase(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                user_id VARCHAR(255) NOT NULL,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                is_blocked TINYINT(1) DEFAULT 0,
                session_token VARCHAR(255),
                remember_tokens TEXT,
                last_login DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS failed_logins (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                attempts INTEGER DEFAULT 1,
                blocked_until DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_failed_logins_email ON failed_logins(email)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    }

    public function user(): ?array
    {
        if ($this->loggedInUser !== null) {
            return $this->loggedInUser;
        }

        $userId = $this->session->get("user_id");
        $sessionToken = $this->session->get("session_token");

        if ($userId && $sessionToken) {
            try {
                $user = $this->getUserById((int)$userId);
                if ($user && hash_equals($user['session_token'] ?? '', $sessionToken)) {
                    $this->loggedInUser = $user;
                    return $this->loggedInUser;
                }
                $this->logout();
            } catch (PDOException $e) {
                error_log("Authentication error: " . $e->getMessage());
                $this->logout();
            }
        }

        return $this->autoLogin();
    }

    public function login(string $email, string $password, bool $rememberMe = false, ?string $redirect = null): array
    {
        try {
            $email = trim($email);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
                return $this->response(false, "Invalid or empty credentials.", "error", $redirect);
            }

            $user = $this->getUserByEmail($email);
            if (!$user) {
                return $this->handleFailedLogin($email, "Invalid credentials.");
            }

            if ($user['is_blocked'] && $this->isBlocked($email)) {
                return $this->response(false, "Account is currently blocked.", "warning", $redirect);
            }

            if (!password_verify($password, $user['password'])) {
                return $this->handleFailedLogin($email, "Invalid credentials.");
            }

            $this->resetFailedLogins($email);

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            session_regenerate_id(true);

            $sessionToken = bin2hex(random_bytes($this->config['session_token_length']));
            $this->updateUser($user['user_id'], [
                'session_token' => $sessionToken,
                'last_login' => (new DateTime())->format('Y-m-d H:i:s')
            ]);

            $this->session->set("user_id", $user['user_id']);
            $this->session->set("session_token", $sessionToken);

            if ($rememberMe) {
                $this->setRememberMeToken($user['user_id']);
            }

            return $this->response(true, "Login successful.", "success", $redirect ?? $this->config['url_root'] . "dashboard");
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return $this->response(false, "An error occurred during login.", "error", $redirect);
        }
    }

    public function autoLogin(): ?array
    {
        if (!isset($_COOKIE[$this->config['remember_me_name']])) {
            return null;
        }

        try {
            $token = $_COOKIE[$this->config['remember_me_name']];
            $hashedToken = hash('sha256', $token);

            $user = $this->getUserByRememberToken($hashedToken);
            if ($user) {
                $sessionToken = bin2hex(random_bytes($this->config['session_token_length']));
                $this->updateUser($user['user_id'], ['session_token' => $sessionToken]);

                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                session_regenerate_id(true);

                $this->session->set("user_id", $user['user_id']);
                $this->session->set("session_token", $sessionToken);
                $this->loggedInUser = $user;
                return $this->loggedInUser;
            }

            $this->clearRememberMeCookie();
        } catch (Exception $e) {
            error_log("Auto login error: " . $e->getMessage());
            $this->clearRememberMeCookie();
        }

        return null;
    }

    public function logout(): void
    {
        $userId = $this->session->get("user_id");

        try {
            if ($userId) {
                $this->updateUser((int)$userId, ['session_token' => null]);
            }
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
        }

        $this->session->remove("user_id");
        $this->session->remove("session_token");
        $this->clearRememberMeCookie();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            session_destroy();
        }

        $this->loggedInUser = null;
    }

    protected function handleFailedLogin(string $email, string $reason): array
    {
        try {
            $record = $this->getFailedLogin($email);
            if (!$record) {
                $this->createFailedLogin($email);
            } else {
                $this->updateFailedLogin($record, $email);
            }

            if ($this->isBlocked($email)) {
                $record = $this->getFailedLogin($email);
                $blockedUntil = new DateTime($record['blocked_until']);
                $currentTime = new DateTime();
                $remainingTime = $currentTime->diff($blockedUntil);

                return $this->response(false,
                    "Account temporarily locked. Try again in " . $remainingTime->format('%i minutes'),
                    "warning"
                );
            }

            $attemptsLeft = max($this->config['max_login_attempts'] - ($record['attempts'] ?? 0), 0);
            $message = $attemptsLeft > 0
                ? "$reason ($attemptsLeft attempts remaining)"
                : "Account locked due to too many failed attempts.";

            return $this->response(false, $message, "warning");
        } catch (Exception $e) {
            error_log("Failed login handling error: " . $e->getMessage());
            return $this->response(false, "An error occurred during login.", "error");
        }
    }

    protected function resetFailedLogins(string $email): void
    {
        try {
            $this->deleteFailedLogin($email);
            $this->updateUserByEmail($email, ['is_blocked' => 0]);
        } catch (Exception $e) {
            error_log("Failed to reset failed logins: " . $e->getMessage());
        }
    }

    protected function isBlocked(string $email): bool
    {
        try {
            $record = $this->getFailedLogin($email);
            if (!$record || empty($record['blocked_until'])) {
                return false;
            }
            return (new DateTime($record['blocked_until'])) > new DateTime();
        } catch (Exception $e) {
            error_log("Block check error: " . $e->getMessage());
            return false;
        }
    }

    protected function setRememberMeToken(int $userId): void
    {
        try {
            $token = bin2hex(random_bytes($this->config['remember_token_length']));
            $hashedToken = hash('sha256', $token);

            $user = $this->getUserById($userId);
            $rememberTokens = [];
            if (!empty($user['remember_tokens'])) {
                $rememberTokens = json_decode($user['remember_tokens'], true) ?: [];
            }

            $rememberTokens[] = [
                'token' => $hashedToken,
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];

            $this->updateUser($userId, ['remember_tokens' => json_encode($rememberTokens)]);

            $cookieParams = $this->config['cookie_params'];
            $cookieParams['expires'] = time() + $this->config['remember_token_expiry'];
            $cookieParams['domain'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

            if (!$this->isProductionEnvironment() && ($_SERVER['HTTP_HOST'] ?? '') === 'localhost') {
                $cookieParams['secure'] = false;
                $cookieParams['domain'] = false;
            }

            setcookie($this->config['remember_me_name'], $token, $cookieParams);
        } catch (Exception $e) {
            error_log("Remember me token error: " . $e->getMessage());
        }
    }

    protected function clearRememberMeCookie(): void
    {
        $params = $this->config['cookie_params'];
        $params['expires'] = time() - 3600;
        setcookie($this->config['remember_me_name'], '', $params);
    }

    protected function response(bool $success, string $message, string $type, ?string $redirect = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'type' => $type,
            'redirect' => $redirect
        ];
    }

    protected function isProductionEnvironment(): bool
    {
        return ($_SERVER['APP_ENV'] ?? '') === 'production';
    }

    // Database Methods

    protected function getUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    protected function getUserById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    protected function getUserByRememberToken(string $hashedToken): ?array
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($user['remember_tokens'])) {
                $tokens = json_decode($user['remember_tokens'], true);
                if (is_array($tokens)) {
                    foreach ($tokens as $tokenData) {
                        if (isset($tokenData['token']) && hash_equals($tokenData['token'], $hashedToken)) {
                            return $user;
                        }
                    }
                }
            }
        }
        return null;
    }

    protected function updateUser(int $userId, array $data): void
    {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        $params['user_id'] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    protected function updateUserByEmail(string $email, array $data): void
    {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        $params['email'] = $email;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    protected function getFailedLogin(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM failed_logins WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    protected function createFailedLogin(string $email): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO failed_logins
            (email, ip_address, user_agent, attempts)
            VALUES (:email, :ip, :ua, 1)
        ");
        $stmt->execute([
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    protected function updateFailedLogin(array $record, string $email): void
    {
        $attempts = $record['attempts'] + 1;
        $blockDuration = min(max(($attempts - $this->config['max_login_attempts']) * $this->config['block_duration_base'], 60));
        $blockedUntil = $attempts > $this->config['max_login_attempts']
            ? (new DateTime())->modify("+$blockDuration minutes")->format('Y-m-d H:i:s')
            : null;

        $stmt = $this->pdo->prepare("
            UPDATE failed_logins
            SET attempts = :attempts,
                blocked_until = :blocked_until,
                updated_at = CURRENT_TIMESTAMP
            WHERE email = :email
        ");
        $stmt->execute([
            'attempts' => $attempts,
            'blocked_until' => $blockedUntil,
            'email' => $email
        ]);

        if ($attempts > $this->config['max_login_attempts']) {
            $this->updateUserByEmail($email, ['is_blocked' => 1]);
        }
    }

    protected function deleteFailedLogin(string $email): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM failed_logins WHERE email = :email");
        $stmt->execute(['email' => $email]);
    }
}