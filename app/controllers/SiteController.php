<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Advertisement;
use App\models\Categories;
use App\models\Event;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;

class SiteController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Welcome to {$name}");
    }

    public function index(Request $request, Response $response)
    {
        // Build query options
        $queryOptions = [
            'per_page' => $request->query('per_page', 6),
            'page' => $request->query('page', 1),
            'order_by' => ['event_date' => 'ASC', 'start_time' => 'ASC']
        ];

        // Only show active events to the public
        $conditions = ['status' => 'active'];

        // Add city filter
        $city = $request->query('city');
        if (!empty($city)) {
            $conditions['city'] = $city;
        }

        $queryOptions['conditions'] = $conditions;

        // Get events with pagination
        $eventsData = Event::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($eventsData['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $categories = Categories::where(['status' => 'active']);

        $advertisements = Advertisement::where(['is_active' => '1']);

        $view = [
            'events' => $eventsData['data'],
            'pagination' => $paginationLinks,
            'currentCity' => $city,
            'categories' => $categories,
            'advertisements' => $advertisements
        ];

        return $this->render('welcome', $view);
    }

    public function about()
    {
        $view = [];

        return $this->render('about', $view);
    }

    public function terms()
    {
        $view = [];

        return $this->render('pages/terms', $view);
    }

    public function policy()
    {
        $view = [];

        return $this->render('pages/policy', $view);
    }
}