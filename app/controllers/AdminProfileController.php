<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminProfileController extends Controller
{
    protected ?User $userModel;

    public function onConstruct()
    {
        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $this->view->setLayout('admin');
        $this->userModel = new User();
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Profile");
    }

    public function profile(Request $request, Response $response)
    {
        $user = auth();

        if (!$user) {
            FlashMessage::setMessage("User not found.", 'danger');
            return $response->redirect("/admin/dashboard");
        }

        $view = [
            'user' => $user,
        ];

        $this->render('admin/profile/profile', $view);
    }
}
