<?php

declare(strict_types=1);

namespace Trees\Providers\Services;

use Trees\Providers\ServiceProvider;
use Trees\Router\Router;

/**
 * ========================================
 * ****************************************
 * ===== RouteServiceProvider Class =======
 * ****************************************
 * ========================================
 */

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        // No need to register anything here
    }

    public function boot()
    {
        /** @var Router $router */
        $router = $this->app->get(Router::class);

        $this->loadWebRoutes($router);
        // $this->loadApiRoutes($router);
    }

    protected function loadWebRoutes(Router $router)
    {
        require ROOT_PATH . '/routes/web.php';
    }

    protected function loadApiRoutes(Router $router)
    {
        require ROOT_PATH . '/routes/api.php';
    }
}