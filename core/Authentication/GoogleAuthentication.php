<?php

declare(strict_types=1);

namespace Trees\Authentication;

use DateTime;
use PDO;
use PDOException;
use Exception;
use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;
use Trees\Session\Handlers\DefaultSessionHandler;

/**
 * =======================================
 * ***************************************
 * === Trees GoogleAuthentication Class ==
 * ***************************************
 * =======================================
 */
class GoogleAuthentication extends Authentication
{
    protected Google_Client $client;
    protected ?string $error = null;
    protected array $googleConfig;

    public function __construct(?PDO $pdo = null, array $config = [], array $googleConfig = [])
    {
        parent::__construct($pdo, $config);

        $this->googleConfig = array_merge([
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => '',
            'scopes' => ['email', 'profile'],
            'access_type' => 'offline',
            'prompt' => 'select_account consent'
        ], $googleConfig);

        $this->initializeGoogleClient();
        $this->initializeDatabase();
    }

    protected function initializeGoogleClient(): void
    {
        $this->client = new Google_Client();
        $this->client->setClientId($this->googleConfig['client_id']);
        $this->client->setClientSecret($this->googleConfig['client_secret']);
        $this->client->setRedirectUri($this->googleConfig['redirect_uri']);
        $this->client->setAccessType($this->googleConfig['access_type']);
        $this->client->setPrompt($this->googleConfig['prompt']);
        $this->client->setIncludeGrantedScopes(true);

        foreach ($this->googleConfig['scopes'] as $scope) {
            $this->client->addScope($scope);
        }
    }

    protected function initializeDatabase(): void
    {
        parent::initializeDatabase();

        // Add Google-specific columns if they don't exist
        try {
            $stmt = $this->pdo->query("PRAGMA table_info(users)");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

            if (!in_array('google_id', $columns)) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN google_id TEXT");
                $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_google_id ON users(google_id)");
            }

            if (!in_array('picture', $columns)) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN picture TEXT");
            }

            if (!in_array('google_refresh_token', $columns)) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN google_refresh_token TEXT");
            }
        } catch (PDOException $e) {
            error_log("Google Auth database initialization error: " . $e->getMessage());
        }
    }

    public function getAuthUrl(?string $state = null): string
    {
        if ($state) {
            $this->client->setState($state);
        }
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code, ?string $state = null): array
    {
        try {
            // Validate state (CSRF protection)
            if ($state && $state !== $this->client->getState()) {
                throw new Exception('Invalid state parameter');
            }

            // Exchange code for tokens
            $tokens = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($tokens['error'])) {
                throw new Exception($tokens['error_description'] ?? 'Invalid token exchange');
            }

            $this->client->setAccessToken($tokens);

            // Get user profile
            $oauth = new Google_Service_Oauth2($this->client);
            $userInfo = $oauth->userinfo->get();

            $userData = [
                'google_id' => $userInfo->getId(),
                'email' => $userInfo->getEmail(),
                'verified_email' => $userInfo->getVerifiedEmail(),
                'name' => $userInfo->getName(),
                'given_name' => $userInfo->getGivenName(),
                'family_name' => $userInfo->getFamilyName(),
                'picture' => $userInfo->getPicture(),
                'locale' => $userInfo->getLocale(),
                'google_access_token' => $tokens['access_token'],
                'google_refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_in' => $tokens['expires_in'] ?? null
            ];

            // Create or update user in database
            $userId = $this->createOrUpdateUser($userData);

            // Log the user in
            return $this->loginWithGoogle($userId, $userData);

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log('Google Auth Error: ' . $e->getMessage());
            return $this->response(false, $e->getMessage(), 'error');
        }
    }

    protected function createOrUpdateUser(array $userData): int
    {
        // Check if user exists by google_id or email
        $user = $this->getUserByGoogleId($userData['google_id']) ??
                $this->getUserByEmail($userData['email']);

        if ($user) {
            // Update existing user
            $updateData = [
                'google_id' => $userData['google_id'],
                'picture' => $userData['picture'],
                'google_refresh_token' => $userData['google_refresh_token']
            ];

            // Only update name if it's not already set
            if (empty($user['name'])) {
                $updateData['name'] = $userData['name'];
            }

            $this->updateUser($user['user_id'], $updateData);
            return $user['user_id'];
        } else {
            // Create new user
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("
                INSERT INTO users
                (email, password, name, picture, google_id, google_refresh_token, created_at)
                VALUES (:email, :password, :name, :picture, :google_id, :refresh_token, CURRENT_TIMESTAMP)
            ");

            $stmt->execute([
                'email' => $userData['email'],
                'password' => $password,
                'name' => $userData['name'],
                'picture' => $userData['picture'],
                'google_id' => $userData['google_id'],
                'refresh_token' => $userData['google_refresh_token']
            ]);

            return (int)$this->pdo->lastInsertId();
        }
    }

    protected function loginWithGoogle(int $userId, array $userData): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);

        $sessionToken = bin2hex(random_bytes($this->config['session_token_length']));
        $this->updateUser($userId, [
            'session_token' => $sessionToken,
            'last_login' => (new DateTime())->format('Y-m-d H:i:s')
        ]);

        $this->session->set("user_id", $userId);
        $this->session->set("session_token", $sessionToken);

        return $this->response(true, "Google login successful.", "success", $this->config['url_root'] . "dashboard");
    }

    public function getUserByGoogleId(string $googleId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE google_id = :google_id LIMIT 1");
        $stmt->execute(['google_id' => $googleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function revokeToken(): bool
    {
        try {
            return $this->client->revokeToken();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log('Google token revocation error: ' . $e->getMessage());
            return false;
        }
    }
}