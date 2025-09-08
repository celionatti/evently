<?php

declare(strict_types=1);

/**
 * App Configuration info
 */

return [
    // Application settings
    'name' => 'Trees MVC Framework',
    'version' => '1.0.0',
    'debug' => true, // Set to false in production
    'timezone' => 'UTC',
    'locale' => 'en',

    // Database configuration
    'database' => [
        'driver' => 'mysql', // mysql, sqlite, pgsql
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'your_database',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'prefix' => ''
    ],

    // Session configuration
    'session' => [
        'name' => 'eventlyy_session',
        'lifetime' => 120, // minutes
        'secure' => false, // Set to true for HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // Global middleware
    'middleware' => [
        // 'App\\Middleware\\CorsMiddleware',
        // 'App\\Middleware\\SecurityMiddleware'
    ],

    // Paths
    'paths' => [
        'views' => ROOT_PATH . '/Views/',
        'storage' => STORAGE_PATH,
        'logs' => STORAGE_PATH . '/logs/',
        'cache' => STORAGE_PATH . '/cache/',
        'uploads' => STORAGE_PATH . '/uploads/'
    ]
];