<?php

declare(strict_types=1);

namespace Trees\Providers\Services;

use Trees\Providers\ServiceProvider;
use Trees\Session\Handlers\DefaultSessionHandler;

/**
 * ========================================
 * ****************************************
 * ===== SessionServiceProvider Class =====
 * ****************************************
 * ========================================
 */

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('sessions', function($app) {
            return new DefaultSessionHandler();
        });
    }

    public function boot()
    {
        $this->app->make('sessions');
    }
}