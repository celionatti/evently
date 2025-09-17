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
}

// Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Lagos');

// Check if maintenance mode is enabled and environment is production
$isMaintenance = ($_ENV['APP_MAINTENANCE'] ?? 'false') === 'true';
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

if ($isMaintenance && $isProduction) {
    // Serve maintenance page
    http_response_code(503); // Service Unavailable
    header('Retry-After: 3600'); // Retry after 1 hour
    
    // Check if upgrade.html exists, otherwise show a default message
    $maintenancePage = ROOT_PATH . '/resources/views/upgrade.html';
    if (file_exists($maintenancePage)) {
        readfile($maintenancePage);
    } else {
        // Fallback maintenance message
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Maintenance Mode</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #333; }
                p { color: #666; }
            </style>
        </head>
        <body>
            <h1>Maintenance Mode</h1>
            <p>The application is currently undergoing maintenance. Please check back later.</p>
        </body>
        </html>';
    }
    exit;
}

$trees = Trees::getInstance();

return $trees;