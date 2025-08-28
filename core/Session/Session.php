<?php

declare(strict_types=1);

namespace Trees\Session;

use Trees\Session\SessionHandler;

/**
 * ========================================
 * ****************************************
 * =========== Session Class ==============
 * ****************************************
 * ========================================
 */

class Session extends SessionHandler {
    private static $instance;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->start();
        }
        return self::$instance;
    }
}
