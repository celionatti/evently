<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminUserController extends Controller
{
    public function onConstruct()
    {
        requireAuth();
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin User | Dashboard");
    }

    public function manage(Request $request, Response $response)
    {
        $users = User::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ]);

        $pagination = new Paginator($users['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'users' => $users['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/users/manage', $view);
    }

    public function delete(Request $request, Response $response, $user_id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $user = User::findByUserId($user_id);

        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        try {
            if ($user->delete()) {
                FlashMessage::setMessage("User Deleted!");
                $response->redirect("/admin/users/manage");
                return;
            }

            throw new \RuntimeException('Delete operation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("Delete Failed! Please try again.", "danger");
            return $response->redirect("/admin/users/manage");
        }
    }
}