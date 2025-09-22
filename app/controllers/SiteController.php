<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use App\models\Advertisement;
use Trees\Pagination\Paginator;
use App\controllers\BaseController;

class SiteController extends BaseController
{
    public function onConstruct()
    {
        parent::onConstruct();
        $this->view->setLayout('default');

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");
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

        $this->view->setTitle("Welcome To Eventlyy | Home Page")
            ->setDescription("Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets.")
            ->setKeywords("bookings, events, tickets, concerts events, meetsup events, beach party events, art or culture, night partys")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "Welcome To Eventlyy | Home Page",
            'description' => "Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets",
            'image' => $request->getBaseUrl() . '/dist/img/og-eventlyy.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);

        $view = [
            'events' => $eventsData['data'],
            'pagination' => $paginationLinks,
            'currentCity' => $city,
            'categories' => $categories,
            'advertisements' => $advertisements
        ];

        return $this->render('welcome', $view);
    }

    public function about(Request $request, Response $response)
    {
        $this->view->setTitle("Welcome To Eventlyy | About Us")
            ->setDescription("Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets.")
            ->setKeywords("bookings, events, tickets, concerts events, meetsup events, beach party events, art or culture, night partys")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "Welcome To Eventlyy | About Us",
            'description' => "Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets",
            'image' => $request->getBaseUrl() . '/dist/img/og-eventlyy.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);

        $view = [];

        return $this->render('about', $view);
    }

    public function terms(Request $request, Response $response)
    {
        $this->view->setTitle("Welcome To Eventlyy | Terms and Conditions")
            ->setDescription("Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets.")
            ->setKeywords("bookings, events, tickets, concerts events, meetsup events, beach party events, art or culture, night partys")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "Welcome To Eventlyy | Terms and Conditions",
            'description' => "Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets",
            'image' => $request->getBaseUrl() . '/dist/img/og-eventlyy.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);
        
        $view = [];

        return $this->render('pages/terms', $view);
    }

    public function policy(Request $request, Response $response)
    {
        $this->view->setTitle("Welcome To Eventlyy | Privacy Policy")
            ->setDescription("Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets.")
            ->setKeywords("bookings, events, tickets, concerts events, meetsup events, beach party events, art or culture, night partys")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "Welcome To Eventlyy | Privacy Policy",
            'description' => "Welcome to Eventlyy Discover events on Eventlyy. Browse events - upcoming events and book your tickets",
            'image' => $request->getBaseUrl() . '/dist/img/og-eventlyy.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);

        $view = [];

        return $this->render('pages/policy', $view);
    }
}