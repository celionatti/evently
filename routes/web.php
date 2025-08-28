<?php

declare(strict_types=1);

use App\controllers\AuthController;
use App\controllers\SiteController;
use App\controllers\AdminController;
use App\controllers\EventController;
use App\controllers\AdminEventController;

/** @var \Trees\Router\Router $router */

/**
 * ========================================
 * ****************************************
 * =============== Web Router =============
 * ****************************************
 * ========================================
 */

$router->get('/', [SiteController::class, 'index']);
$router->get('/events', [EventController::class, 'events']);
$router->get('/events/{id}', [EventController::class, 'event']);

// Auth: Login/SignUp
$router->get('/login', [AuthController::class, 'login']);
$router->get('/sign-up', [AuthController::class, 'signup']);

// Admin: Routes
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->group(['prefix' => '/admin'], function ($router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
});

// Admin: Events Routes
$router->group(['prefix' => '/admin/events'], function ($router) {
    $router->get('/manage', [AdminEventController::class, 'manage']);
    $router->get('/create', [AdminEventController::class, 'create']);
});