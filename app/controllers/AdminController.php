<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminController extends Controller
{
    protected $user;
    
    public function onConstruct()
    {
        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/");
        }
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Dashboard");
        $this->user = auth();
    }

    public function dashboard()
    {
        $view = [

        ];

        return $this->render('admin/dashboard', $view);
    }
}