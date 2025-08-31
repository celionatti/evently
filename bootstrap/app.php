<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Trees\Trees;

/**
 * =======================================
 * ***************************************
 * ======= Trees Bootstrap App ===========
 * ***************************************
 * =======================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');

require ROOT_PATH . "/vendor/autoload.php";

try {
    $dotenv = Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
    $dotenv->required(['DB_DATABASE', 'DB_USERNAME', 'DB_CONNECTION']);
} catch(\Exception $e) {
    die("Missing required environment variables");
}

// Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Lagos');

$trees = Trees::getInstance();

return $trees;