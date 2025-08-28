<?php

declare(strict_types=1);

namespace Trees\Controller;

use JetBrains\PhpStorm\NoReturn;
use Trees\Exception\TreesException;
use Trees\View\View;

/**
 * =======================================
 * ***************************************
 * ======= Trees Controller Class ========
 * ***************************************
 * =======================================
 */

abstract class Controller
{
    public View $view;

    public function __construct()
    {
        $this->view = new View(ROOT_PATH . '/resources/views');
        $this->onConstruct();
    }

    /**
     * @throws TreesException
     */
    protected function render(string $template, array $data = []): ?string
    {
        return $this->view->render($template, $data);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function onConstruct() {}
}
