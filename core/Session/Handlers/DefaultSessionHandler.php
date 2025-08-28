<?php

declare(strict_types=1);

namespace Trees\Session\Handlers;

use Trees\Session\SessionHandler;

/**
 * ========================================
 * ****************************************
 * ======= DefaultSessionHandler Class ====
 * ****************************************
 * ========================================
 */

class DefaultSessionHandler extends SessionHandler
{
    public function __construct()
    {
        $this->start();
    }
}