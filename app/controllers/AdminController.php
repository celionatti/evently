<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use App\controllers\BaseController;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminController extends BaseController
{
    protected $user;
    
    public function onConstruct()
    {
        parent::onConstruct();

        $this->view->setLayout('admin');

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");

        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/");
        }
        
        $this->view->setTitle("Eventlyy Admin | Dashboard");
        $this->user = auth();
    }

    public function dashboard()
    {
        $view = [

        ];

        return $this->render('admin/dashboard', $view);
    }
}