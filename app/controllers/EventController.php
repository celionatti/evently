<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use Trees\Helper\Cities\Cities;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;

class EventController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Events | {$name}");
    }

    public function events(Request $request, Response $response)
    {
        // Build query options
        $queryOptions = [
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page', 1),
            'order_by' => ['event_date' => 'ASC', 'start_time' => 'ASC']
        ];

        // Only show active events to the public
        $conditions = ['status' => 'active'];

        // Add search functionality
        $search = $request->query('search');
        if (!empty($search)) {
            // Use the applySearch method from the Event model
            $queryOptions['search'] = $search;
        }

        // Add category filter
        $category = $request->query('category');
        if (!empty($category)) {
            $conditions['category'] = $category;
        }
        
        // Add city filter
        $city = $request->query('city');
        if (!empty($city)) {
            $conditions['city'] = $city;
        }
        
        // Add featured filter
        $featured = $request->query('featured');
        if ($featured === 'true') {
            $conditions['featured'] = 1;
        }

        // Add date filter - only show future events
        $queryOptions['where_raw'] = ['event_date >= CURDATE()'];
        
        $queryOptions['conditions'] = $conditions;

        // Get events with pagination
        $eventsData = Event::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($eventsData['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        // Get categories and cities for filters
        $categories = Categories::all();
        $cities = Cities::getAll('NG');
        
        // Get featured events for sidebar or hero section
        $featuredEvents = Event::where([
            'status' => 'active', 
            'featured' => 1
        ]);

        // Limit to 3 featured events
        if (is_array($featuredEvents) && count($featuredEvents) > 3) {
            $featuredEvents = array_slice($featuredEvents, 0, 3);
        }


        $view = [
            'events' => $eventsData['data'],
            'pagination' => $paginationLinks,
            'categories' => $categories,
            'cities' => $cities,
            'featuredEvents' => $featuredEvents ?? [],
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentCity' => $city,
            'currentFeatured' => $featured,
            'totalEvents' => $eventsData['meta']['total'] ?? 0
        ];

        return $this->render('events', $view);
    }

    public function event(Request $request, Response $response, $id)
    {
        $view = [];

        return $this->render('event', $view);
    }
}
