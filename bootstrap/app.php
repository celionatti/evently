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
    $dotenv->required(['DB_DATABASE', 'DB_USERNAME', 'DB_CONNECTION', 'APP_MAINTENANCE']);
} catch (\Exception $e) {
    http_response_code(500);
    $showDetails = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

    if ($showDetails) {
        // Start output buffering
        ob_start();

        // Extract exception data for the template
        $message = $e->getMessage();
        $code = $e->getCode();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        $time = date('Y-m-d H:i:s');
        $environment = $_ENV['APP_ENV'] ?? 'Not set';

        // Include the template
        include ROOT_PATH . '/resources/errors/error.php';

        // Output the buffered content
        ob_end_flush();
    } else {
        include ROOT_PATH . '/resources/errors/production.php';
    }

    exit;
    // die("Missing required environment variables");
}

// Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Lagos');

$trees = Trees::getInstance();

return $trees;
