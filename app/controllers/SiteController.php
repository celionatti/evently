<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Controller\Controller;

class SiteController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Welcome to {$name}");
    }

    public function index()
    {
        $view = [

        ];

        return $this->render('welcome', $view);
    }
}