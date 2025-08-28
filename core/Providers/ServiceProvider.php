<?php

declare(strict_types=1);

namespace Trees\Providers;

/**
 * ========================================
 * ****************************************
 * ========== ServiceProvider Class =======
 * ****************************************
 * ========================================
 */

abstract class ServiceProvider
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    abstract public function register();

    public function boot()
    {
        // Optional boot method
    }
}