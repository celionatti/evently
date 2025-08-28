<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;

class AuthController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('auth');
        $name = "Eventlyy";
        $this->view->setTitle("Authentication | {$name}");
    }

    public function auth()
    {
        $view = [

        ];

        return $this->render('auth/auth', $view);
    }
}