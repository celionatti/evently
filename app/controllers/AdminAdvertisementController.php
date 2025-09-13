<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use App\models\Attendee;
use Trees\Http\Response;
use App\models\Advertisement;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminAdvertisementController extends Controller
{
    public function onConstruct()
    {
        requireAuth();
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/admin");
        }
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin Advertisement | Dashboard");
    }

    public function manage(Request $request, Response $response)
    {
        $categories = Advertisement::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ]);

        $pagination = new Paginator($categories['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'categories' => $categories['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/advertisement/manage', $view);
    }
}