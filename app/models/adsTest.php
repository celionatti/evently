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

class adsTest extends Controller
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
        $advertisements = Advertisement::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ]);

        $pagination = new Paginator($advertisements['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'advertisements' => $advertisements['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/advertisement/manage', $view);
    }

    public function create()
    {
        $view = [];

        return $this->render('admin/advertisement/create', $view);
    }
}