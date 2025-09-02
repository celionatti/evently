<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Controller\Controller;
use Trees\Http\Request;
use Trees\Http\Response;

class AdminController extends Controller
{
    protected $user;
    
    public function onConstruct()
    {
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Dashboard");
        requireAuth();
        $this->user = auth();
    }

    public function dashboard()
    {
        $view = [

        ];

        return $this->render('admin/dashboard', $view);
    }
}