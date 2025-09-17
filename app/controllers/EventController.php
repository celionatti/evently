<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use App\models\Advertisement;
use Trees\Helper\Cities\Cities;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

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

        $advertisements = Advertisement::where(['is_active' => '1']);

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
            'totalEvents' => $eventsData['meta']['total'] ?? 0,
            'advertisements' => $advertisements
        ];

        return $this->render('events', $view);
    }

    public function event(Request $request, Response $response, $id, $slug)
    {
        // Find event by slug or ID
        $event = null;

        // Try to find by slug first, then by ID
        if (is_numeric($id)) {
            $event = Event::find($id);
        } else {
            // Find by slug
            $events = Event::where(['slug' => $slug]);
            $event = !empty($events) ? $events[0] : null;
        }

        if (!$event) {
            FlashMessage::setMessage("Event not found!", 'danger');
            return $response->redirect("/events");
        }

        // Only show active events to the public (unless user is admin/organizer)
        if ($event->status !== 'active') {
            // Check if user has permission to view inactive events
            if (!auth() || (!isAdminOrOrganiser() && $event->user_id !== auth()->id)) {
                FlashMessage::setMessage("Event not available!", 'danger');
                return $response->redirect("/events");
            }
        }

        // Check if event has passed (optional - you might want to show past events)
        $eventDateTime = strtotime($event->event_date . ' ' . ($event->start_time ?? '00:00:00'));
        $isPastEvent = $eventDateTime < time();

        // Load tickets for this event
        $tickets = Ticket::where(['event_id' => $event->id]);
        $event->tickets = is_array($tickets) ? $tickets : [];

        // Organize tickets by type/tier and check availability
        $ticketTiers = [];
        foreach ($tickets as $ticket) {
            $available = $ticket->quantity - ($ticket->sold ?? 0);
            $ticketTiers[] = [
                'id' => $ticket->id,
                'slug' => $ticket->slug,
                'name' => $ticket->ticket_name,
                'description' => $ticket->description ?? '',
                'price' => $ticket->price,
                'original_price' => $ticket->original_price ?? $ticket->price,
                'quantity' => $ticket->quantity,
                'available' => max(0, $available),
                'sold_out' => $available <= 0,
                'early_bird' => $ticket->early_bird ?? false,
                'service_charge' => $ticket->charges ?? ($ticket->price * 0.05), // 5% default service charge
                'max_per_person' => $ticket->max_per_person ?? 10
            ];
        }

        // Sort tickets by price (cheapest first)
        usort($ticketTiers, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        // Calculate time until event
        $timeUntilEvent = $eventDateTime - time();
        $daysUntilEvent = max(0, floor($timeUntilEvent / (60 * 60 * 24)));

        // Get minimum ticket price for display
        $minPrice = null;
        foreach ($ticketTiers as $ticket) {
            if ($ticket['price'] > 0 && ($minPrice === null || $ticket['price'] < $minPrice)) {
                $minPrice = $ticket['price'];
            }
        }

        $advertisements = Advertisement::where(['is_active' => '1']);

        // Update page title
        $this->view->setTitle($event->event_title . " | Eventlyy");

        $view = [
            'event' => $event,
            'tickets' => $ticketTiers,
            'isPastEvent' => $isPastEvent,
            'daysUntilEvent' => $daysUntilEvent,
            'timeUntilEvent' => $timeUntilEvent,
            'minPrice' => $minPrice,
            'eventDateTime' => $eventDateTime,
            'advertisements' => $advertisements
        ];

        return $this->render('event', $view);
    }
}
