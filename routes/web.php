<?php

declare(strict_types=1);

use App\controllers\AuthController;
use App\controllers\SiteController;
use App\controllers\AdminController;
use App\controllers\EventController;
use App\Controllers\CheckoutController;
use App\controllers\AdminUserController;
use App\controllers\AdminEventController;
use App\controllers\AdminAttendeeController;
use App\controllers\AdminCategoryController;
use App\controllers\AdminProfileController;

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
$router->get('/events/{id}/{slug}', [EventController::class, 'event']);

// Checkout Ticket Transaction
$router->post('/checkout/tickets', [CheckoutController::class, 'processCheckout']);
$router->get('/checkout/payment/{reference}', [CheckoutController::class, 'paymentPage']);
$router->post('/checkout/process-payment', [CheckoutController::class, 'processPayment']);
$router->get('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment']);
$router->get('/checkout/success/{reference}', [CheckoutController::class, 'successPage']);

// Auth: Login/SignUp
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login_user']);
$router->get('/sign-up', [AuthController::class, 'signup']);
$router->post('/sign-up', [AuthController::class, 'create_user']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin: Routes
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->group(['prefix' => '/admin'], function ($router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
    $router->get('/profile', [AdminProfileController::class, 'profile']);
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

    $router->post('/status', [AdminEventController::class, 'eventStatus']);
    $router->post('/ticket-status', [AdminEventController::class, 'ticketStatus']);
    $router->post('/delete-ticket', [AdminEventController::class, 'deleteTicket']);
    $router->get('/{slug}/export-attendees', [AdminEventController::class, 'exportAttendees']);
    $router->get('/cleanup', [AdminEventController::class, 'cleanupPage']);
    $router->post('/execute-cleanup', [AdminEventController::class, 'executeCleanup']);
});

// Admin: Events Attendees Routes
$router->group(['prefix' => '/admin/attendees'], function ($router) {
    $router->post('/check-in/{id}/{event_slug}', [AdminAttendeeController::class, 'checkInAttendee']);
});

// Admin: Users Routes
$router->group(['prefix' => '/admin/users'], function ($router) {
    $router->get('/manage', [AdminUserController::class, 'manage']);
    $router->get('/create', [AdminUserController::class, 'create']);
    $router->post('/delete/{user_id}', [AdminUserController::class, 'delete']);
    $router->post('/role/{user_id}', [AdminUserController::class, 'role']);
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
