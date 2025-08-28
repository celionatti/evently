<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;

class AdminController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Dashboard");
    }

    public function dashboard()
    {
        $view = [

        ];

        return $this->render('admin/dashboard', $view);
    }
}