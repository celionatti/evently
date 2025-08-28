<?php

declare(strict_types=1);

use App\controllers\AuthController;
use App\controllers\SiteController;
use App\controllers\EventController;

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
$router->get('/auth', [AuthController::class, 'auth']);