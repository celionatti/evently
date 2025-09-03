<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use App\models\Categories;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\Cities\Cities;
use Trees\Helper\FlashMessages\FlashMessage;

class EventController extends Controller
{
    protected ?Event $eventModel;
    protected ?Ticket $ticketModel;
    
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Events | {$name}");
        $this->eventModel = new Event();
        $this->ticketModel = new Ticket();
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

    public function event(Request $request, Response $response, $slug)
    {
        // Find event by slug
        $event = Event::findBySlug($slug);
        
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
        
        // Load tickets for this event
        $tickets = Ticket::where(['event_id' => $event->id]);
        $event->tickets = is_array($tickets) ? $tickets : [];
        
        // Calculate total available tickets
        $totalAvailable = 0;
        $totalSold = 0;
        $minPrice = null;
        $maxPrice = null;
        
        foreach ($event->tickets as $ticket) {
            $available = $ticket->quantity - ($ticket->sold ?? 0);
            $totalAvailable += $available;
            $totalSold += $ticket->sold ?? 0;
            
            // Calculate price range
            if ($ticket->price > 0) {
                if ($minPrice === null || $ticket->price < $minPrice) {
                    $minPrice = $ticket->price;
                }
                if ($maxPrice === null || $ticket->price > $maxPrice) {
                    $maxPrice = $ticket->price;
                }
            }
        }
        
        // Get related events (same category, different event)
        $relatedEvents = Event::where([
            'category' => $event->category,
            'status' => 'active'
        ]);
        
        // Remove current event from related events and limit to 3
        if (is_array($relatedEvents)) {
            $relatedEvents = array_filter($relatedEvents, function($relatedEvent) use ($event) {
                return $relatedEvent->id !== $event->id;
            });
            $relatedEvents = array_slice($relatedEvents, 0, 3);
        } else {
            $relatedEvents = [];
        }
        
        // Check if event date has passed
        $eventDateTime = strtotime($event->event_date . ' ' . ($event->start_time ?? '00:00:00'));
        $isEventPassed = $eventDateTime < time();
        
        // Update page title to include event name
        $this->view->setTitle($event->event_title . " | Eventlyy");
        
        $view = [
            'event' => $event,
            'tickets' => $event->tickets,
            'totalAvailable' => $totalAvailable,
            'totalSold' => $totalSold,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'relatedEvents' => $relatedEvents,
            'isEventPassed' => $isEventPassed,
            'canEdit' => auth() && (isAdminOrOrganiser() || $event->user_id === auth()->id)
        ];

        return $this->render('event', $view);
    }
    
    /**
     * Get events by category (AJAX endpoint)
     */
    public function getEventsByCategory(Request $request, Response $response, $category)
    {
        if ($request->isAjax()) {
            $events = Event::where([
                'category' => $category,
                'status' => 'active'
            ]);
            
            // Filter future events only
            if (is_array($events)) {
                $events = array_filter($events, function($event) {
                    $eventDateTime = strtotime($event->event_date . ' ' . ($event->start_time ?? '00:00:00'));
                    return $eventDateTime >= time();
                });
                
                // Limit to 6 events
                $events = array_slice($events, 0, 6);
            }
            
            return $response->json([
                'success' => true,
                'events' => $events ?? [],
                'count' => count($events ?? [])
            ]);
        }
        
        return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
    }
    
    /**
     * Search events (AJAX endpoint)
     */
    public function searchEvents(Request $request, Response $response)
    {
        if ($request->isAjax()) {
            $searchTerm = $request->input('search', '');
            
            if (strlen($searchTerm) < 2) {
                return $response->json([
                    'success' => false,
                    'message' => 'Search term must be at least 2 characters'
                ]);
            }
            
            $queryOptions = [
                'conditions' => ['status' => 'active'],
                'search' => $searchTerm,
                'order_by' => ['event_date' => 'ASC'],
                'per_page' => 10,
                'page' => 1
            ];
            
            // Add future events filter
            $queryOptions['where_raw'] = ['event_date >= CURDATE()'];
            
            $eventsData = Event::paginate($queryOptions);
            
            return $response->json([
                'success' => true,
                'events' => $eventsData['data'] ?? [],
                'total' => $eventsData['meta']['total'] ?? 0
            ]);
        }
        
        return $response->json(['success' => false, 'message' => 'Invalid request'], 400);
    }
}