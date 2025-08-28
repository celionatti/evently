#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Trees\Container\Container;
use Trees\Config;
use Trees\Database\Database;
use Trees\Command\CommandRunner;
use Trees\Command\TermUI;
use Trees\Database\Schema\Schema;
use Trees\Exception\TreesException;

/**
 * Trees CLI Application
 *
 * A robust command-line interface for managing the Trees ecosystem
 *
 * @package Trees
 * @version 2.0.0
 * @license MIT
 */
define('ROOT_PATH', dirname(__DIR__));
define('APP_START_TIME', microtime(true));

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/core/functions.php';

// Environment validation
if (php_sapi_name() !== 'cli') {
    exit('Error: This application must be run from the command line.' . PHP_EOL);
}

// Initialize environment
try {
    $dotenv = Dotenv::createImmutable(ROOT_PATH);
    $dotenv->safeLoad();
    $dotenv->required(['APP_ENV', 'DB_HOST', 'DB_USERNAME', 'DB_DATABASE'])->notEmpty();
} catch (Throwable $e) {
    exit('Environment configuration error: ' . $e->getMessage() . PHP_EOL);
}

// Setup dependency container
$container = new Container();

// Register core services
$container->singleton(Config::class, fn() => new Config());

try {
    loadConfiguration($container);

    $container->singleton(Database::class, function () use ($container) {
        $config = $container->get(Config::class);
        $dbConfig = $config->get('database.connections.' . $config->get('database.default'));

        if (!Database::init($config->get('database.default'), $dbConfig)) {
            throw new TreesException("Database connection failed to " . $dbConfig['database']);
        }

        return new Database(
            $config->get('database.default'),
            $dbConfig
        );
    });

    Schema::setConnection($container->get(Database::class));

} catch (TreesException $e) {
    exit(TermUI::warning('Fatal error: ' . $e->getMessage()) . PHP_EOL);
}

/**
 * Load application configuration
 */
function loadConfiguration($container):void
{
    $config = $container->get(Config::class);
    $config->loadMultiple([
        config_path('database.php'),
    ]);
}

// Display application header
$header = <<<EOT

╔════════════════════════════════════════╗
║                                        ║
║         🌳  TREES CLI v2.0  🌳         ║
║    Sustainable Growth Management       ║
║                                        ║
╚════════════════════════════════════════╝

EOT;

echo TermUI::BOLD . TermUI::GREEN . $header . TermUI::RESET;

// Initialize command runner with enhanced features
try {
    $runner = new CommandRunner(name: 'Trees Ecosystem Manager', version: '2.0.0');

    return $runner;

} catch (Throwable $e) {
    exit(TermUI::error('Failed to initialize command runner') . PHP_EOL);
}