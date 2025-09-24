<?php

declare(strict_types=1);

use App\controllers\AuthController;
use App\controllers\SiteController;
use App\controllers\AdminController;
use App\controllers\EventController;
use App\controllers\ArticleController;
use App\controllers\CheckoutController;
use App\controllers\AdminUserController;
use App\controllers\AdminEventController;
use App\controllers\AdminArticleController;
use App\controllers\AdminProfileController;
use App\controllers\AdminAttendeeController;
use App\controllers\AdminCategoryController;
use App\controllers\AdminSettingsController;
use App\controllers\AdminAdvertisementController;

/** @var \Trees\Router\Router $router */

/**
 * ========================================
 * ****************************************
 * =============== Web Router =============
 * ****************************************
 * ========================================
 */

$router->get('/', [SiteController::class, 'index']);
$router->get('/about-us', [SiteController::class, 'about']);
$router->get('/events', [EventController::class, 'events']);
$router->get('/events/{id}/{slug}', [EventController::class, 'event']);
$router->get('/e/{eventLink}', [EventController::class, 'showByCustomLink']);

// SEO and Feed routes
$router->get('/events/rss', [EventController::class, 'rss']);
$router->get('/events/sitemap.xml', [EventController::class, 'sitemap']);

$router->get('/articles', [ArticleController::class, 'articles']);
$router->get('/articles/{id}/{slug}', [ArticleController::class, 'article']);

// API Routes for AJAX functionality
$router->post('/api/articles/{id}/like', [ArticleController::class, 'likeArticle']);

// SEO and Feed routes
$router->get('/articles/rss', [ArticleController::class, 'rss']);
$router->get('/articles/sitemap.xml', [ArticleController::class, 'sitemap']);

$router->get('/terms-and-conditions', [SiteController::class, 'terms']);
$router->get('/privacy-policy', [SiteController::class, 'policy']);

// Checkout Ticket Transaction
$router->post('/checkout/tickets', [CheckoutController::class, 'processCheckout']);
$router->get('/checkout/payment/{reference}', [CheckoutController::class, 'paymentPage']);
$router->post('/checkout/process-payment', [CheckoutController::class, 'processPayment']);
$router->get('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment']);
$router->get('/checkout/success/{reference}', [CheckoutController::class, 'successPage']);
$router->get('/checkout/download-ticket/{attendeeId}/{reference}', [CheckoutController::class, 'downloadTicket']);
$router->get('/checkout/download-all-tickets/{reference}', [CheckoutController::class, 'downloadAllTickets']);

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
    $router->post('/profile', [AdminProfileController::class, 'update']);
    $router->post('/profile/change-password', [AdminProfileController::class, 'change_password']);
});

// Admin: Articles Routes
$router->group(['prefix' => '/admin/articles'], function ($router) {
    $router->get('/manage', [AdminArticleController::class, 'manage']);
    $router->get('/view/{slug}', [AdminArticleController::class, 'view']);
    $router->get('/create', [AdminArticleController::class, 'create']);
    $router->post('/create', [AdminArticleController::class, 'insert']);
    $router->get('/edit/{slug}', [AdminArticleController::class, 'edit']);
    $router->post('/edit/{slug}', [AdminArticleController::class, 'update']);
    $router->post('/delete/{slug}', [AdminArticleController::class, 'delete']);
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

// Admin: Advertisement Routes
$router->group(['prefix' => '/admin/advertisements'], function ($router) {
    $router->get('/manage', [AdminAdvertisementController::class, 'manage']);
    $router->get('/create', [AdminAdvertisementController::class, 'create']);
    $router->post('/create', [AdminAdvertisementController::class, 'insert']);
    $router->get('/edit/{id}', [AdminAdvertisementController::class, 'edit']);
    $router->post('/edit/{id}', [AdminAdvertisementController::class, 'update']);
    $router->post('/delete/{id}', [AdminAdvertisementController::class, 'delete']);
});

// Admin: Setting Routes
$router->group(['prefix' => '/admin/settings'], function ($router) {
    $router->get('/manage', [AdminSettingsController::class, 'manage']);
    $router->get('/create', [AdminSettingsController::class, 'create']);
    $router->post('/create', [AdminSettingsController::class, 'insert']);
    $router->get('/edit/{id}', [AdminSettingsController::class, 'edit']);
    $router->post('/edit/{id}', [AdminSettingsController::class, 'update']);
    $router->post('/delete/{id}', [AdminSettingsController::class, 'delete']);
    $router->post('/update-setting', [AdminSettingsController::class, 'updateSetting']);
    $router->post('/clear-cache', [AdminSettingsController::class, 'clearCache']);
});
// $router->post('/admin/settings', [AdminSettingsController::class, 'index']);
