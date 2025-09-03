<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use Trees\Http\Response;
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
        // Get filter parameters
        $searchTerm = $request->query('search', '');
        $categoryFilter = $request->query('category', '');
        $cityFilter = $request->query('city', '');
        $dateFilter = $request->query('date', '');
        $statusFilter = $request->query('status', '');
        $tagFilter = $request->query('tag', '');

        // Build query options
        $queryOptions = [
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page', 1),
            'order_by' => ['event_date' => 'ASC', 'start_time' => 'ASC']
        ];

        // Only show active events to the public
        $conditions = ['status' => 'active'];

        // Apply filters
        if (!empty($searchTerm)) {
            $conditions['event_title LIKE'] = "%{$searchTerm}%";
        }

        if (!empty($categoryFilter)) {
            $conditions['category'] = $categoryFilter;
        }

        if (!empty($cityFilter)) {
            $conditions['city'] = $cityFilter;
        }

        if (!empty($tagFilter)) {
            $conditions['tags LIKE'] = "%{$tagFilter}%";
        }

        // Date filtering
        if (!empty($dateFilter)) {
            $today = date('Y-m-d');

            switch ($dateFilter) {
                case 'today':
                    $conditions['event_date'] = $today;
                    break;
                case 'tomorrow':
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $conditions['event_date'] = $tomorrow;
                    break;
                case 'this_week':
                    $startOfWeek = date('Y-m-d', strtotime('this week'));
                    $endOfWeek = date('Y-m-d', strtotime('this week +6 days'));
                    $conditions['event_date BETWEEN'] = [$startOfWeek, $endOfWeek];
                    break;
                case 'this_weekend':
                    $saturday = date('Y-m-d', strtotime('this saturday'));
                    $sunday = date('Y-m-d', strtotime('this sunday'));
                    $conditions['event_date BETWEEN'] = [$saturday, $sunday];
                    break;
                case 'next_week':
                    $startOfNextWeek = date('Y-m-d', strtotime('next week'));
                    $endOfNextWeek = date('Y-m-d', strtotime('next week +6 days'));
                    $conditions['event_date BETWEEN'] = [$startOfNextWeek, $endOfNextWeek];
                    break;
            }
        }

        // Status filtering (featured events)
        if ($statusFilter === 'featured') {
            $conditions['featured'] = 1;
        }
        
        $queryOptions['conditions'] = $conditions;

        // Get events with pagination
        $eventsData = Event::paginate($queryOptions);

        foreach($eventsData['data'] as &$event) {
            $tickets = Ticket::where(['event_id' => $event->id]);
        }

        $view = [];

        return $this->render('events', $view);
    }

    public function event(Request $request, Response $response, $id)
    {
        $view = [];

        return $this->render('event', $view);
    }
}
