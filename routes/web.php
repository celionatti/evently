<?php

declare(strict_types=1);

use App\controllers\AuthController;
use App\controllers\SiteController;
use App\controllers\AdminController;
use App\controllers\EventController;
use App\controllers\AdminUserController;
use App\controllers\AdminEventController;
use App\controllers\AdminCategoryController;

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
    $router->get('/view/{slug}', [AdminEventController::class, 'view']);
    $router->get('/create', [AdminEventController::class, 'create']);
    $router->post('/create', [AdminEventController::class, 'insert']);
    $router->get('/edit/{slug}', [AdminEventController::class, 'edit']);
    $router->post('/edit/{slug}', [AdminEventController::class, 'update']);
    $router->post('/delete/{slug}', [AdminEventController::class, 'delete']);

    $router->post('/delete-ticket', [AdminEventController::class, 'deleteTicket']);
});

// Admin: Users Routes
$router->group(['prefix' => '/admin/users'], function ($router) {
    $router->get('/manage', [AdminUserController::class, 'manage']);
    $router->post('/delete/{slug}', [AdminUserController::class, 'delete']);
});

// Admin: Categories Routes
$router->group(['prefix' => '/admin/categories'], function ($router) {
    $router->get('/manage', [AdminCategoryController::class, 'manage']);
    $router->get('/create', [AdminCategoryController::class, 'create']);
    $router->post('/create', [AdminCategoryController::class, 'insert']);
    $router->get('/edit/{slug}', [AdminCategoryController::class, 'edit']);
    $router->post('/edit/{slug}', [AdminCategoryController::class, 'update']);
    $router->post('/delete/{slug}', [AdminCategoryController::class, 'delete']);
});
