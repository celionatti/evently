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
use App\controllers\BaseController;
use Trees\Helper\FlashMessages\FlashMessage;

class EventController extends BaseController
{
    public function onConstruct()
    {
        parent::onConstruct();
        $this->view->setLayout('default');

        // Add article-specific optimizations
        $this->addAnalytics();
        $this->addSocialShareButtons();

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");
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

        // Set SEO meta tags for events listing
        $this->setupEventsListingSEO($request, $search, $category, $city, $eventsData['meta']['total'] ?? 0);

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

        // Set comprehensive SEO for the event
        $this->setupEventSEO($event, $request, $ticketTiers, (float)$minPrice, $isPastEvent);

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

    /**
     * Events sitemap for SEO
     */
    public function sitemap(Request $request, Response $response)
    {
        $events = Event::where(['status' => 'active']);

        $xml = $this->generateEventsSitemap($events, $request);

        return $response->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->output($xml);
    }

    /**
     * Events RSS feed
     */
    public function rss(Request $request, Response $response)
    {
        $events = Event::where([
            'conditions' => ['status' => 'active'],
            'where_raw' => ['event_date >= CURDATE()'],
            'order_by' => ['created_at' => 'DESC'],
            'limit' => 50
        ]);

        $rssContent = $this->generateEventsRSSFeed($events, $request);

        return $response->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->output($rssContent);
    }

    /**
     * Set up SEO for events listing page
     */
    private function setupEventsListingSEO(Request $request, ?string $search, ?int $category, ?string $city, int $totalEvents)
    {
        $title = "Events";
        $description = "Discover amazing events on Eventlyy. ";
        $keywords = ["events", "tickets", "conferences", "workshops", "meetups"];

        // Customize based on filters
        if ($search) {
            $title = "Search: {$search} - Events";
            $description .= "Search results for '{$search}'. ";
            $keywords[] = $search;
        }

        if ($category) {
            $categoryName = $this->getCategoryName($category);
            $title = "{$categoryName} Events";
            $description .= "Browse {$categoryName} events. ";
            $keywords[] = strtolower($categoryName);
        }

        if ($city) {
            $title .= " in {$city}";
            $description .= "Find events in {$city}. ";
            $keywords[] = strtolower($city);
        }

        $description .= "Book tickets for {$totalEvents}+ upcoming events.";

        $this->view->setTitle("{$title} | Eventlyy")
            ->setDescription($description)
            ->setKeywords(implode(', ', $keywords))
            ->setCanonical($request->url());

        // Open Graph tags
        $this->view->setOpenGraph([
            'title' =>  "{$title} | Eventlyy",
            'description' => $description,
            'image' => $request->getBaseUrl() . '/dist/img/og-events.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website',
            'site_name' => 'Eventlyy'
        ]);

        // Twitter Card
        $this->view->setTwitterCard([
            'card' => 'summary_large_image',
            'title' => $title,
            'description' => $description,
            'image' => $request->getBaseUrl() . '/dist/img/twitter-events.jpg'
        ]);
    }

    /**
     * Set up comprehensive SEO for single event page
     */
    private function setupEventSEO(Event $event, Request $request, array $tickets, ?float $minPrice, bool $isPastEvent)
    {
        $title = $event->event_title;
        $description = $event->meta_description ?: getExcerpt($event->description ?? '', 160);

        // Build keywords from event data
        $keywords = [];
        if ($event->tags) $keywords[] = $event->tags;
        if ($event->city) $keywords[] = $event->city;
        $categoryName = $this->getCategoryName((int)$event->category);
        if ($categoryName) $keywords[] = $categoryName;

        $keywordString = implode(', ', array_filter($keywords));

        $this->view->setTitle($title . " | Eventlyy")
            ->setDescription($description)
            ->setKeywords($keywordString)
            ->setCanonical($request->url());

        // Open Graph for events
        $this->view->setOpenGraph([
            'title' => $title,
            'description' => $description,
            'type' => 'website',
            'url' => $request->fullUrl(),
            'image' => $this->getFullImageUrl($request, $event->image),
            'site_name' => 'Eventlyy'
        ]);

        // Twitter Card
        $this->view->setTwitterCard([
            'card' => 'summary_large_image',
            'title' => $title,
            'description' => $description,
            'image' => $this->getFullImageUrl($request, $event->image)
        ]);

        // Add event-specific JavaScript for interactions
        $this->view->addInlineScript($this->generateEventInteractionScript($event, $isPastEvent));

        // Add structured data for the event
        $this->view->addInlineScript($this->generateEventStructuredData($event, $tickets, $request), [
            'type' => 'application/ld+json'
        ]);
    }

    /**
     * Set up SEO for category pages
     */
    private function setupCategorySEO(Categories $category, Request $request, int $totalEvents)
    {
        $this->view->setTitle("{$category->category_name} Events | Eventlyy")
            ->setDescription("Discover {$category->category_name} events on Eventlyy. Browse {$totalEvents}+ upcoming {$category->category_name} events and book your tickets.")
            ->setKeywords("{$category->category_name}, events, tickets, {$category->category_name} events")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "{$category->category_name} Events | Eventlyy",
            'description' => "Browse {$category->category_name} events on Eventlyy",
            'image' => $request->getBaseUrl() . '/dist/img/og-category.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);
    }

    /**
     * Set up SEO for city pages
     */
    private function setupCitySEO(string $city, Request $request, int $totalEvents)
    {
        $this->view->setTitle("Events in {$city} | Eventlyy")
            ->setDescription("Find events in {$city}. Browse {$totalEvents}+ upcoming events in {$city} and book your tickets on Eventlyy.")
            ->setKeywords("events in {$city}, {$city} events, tickets {$city}")
            ->setCanonical($request->url());

        $this->view->setOpenGraph([
            'title' => "Events in {$city} | Eventlyy",
            'description' => "Find the best events in {$city}",
            'image' => $request->getBaseUrl() . '/dist/img/og-city.jpg',
            'url' => $request->fullUrl(),
            'type' => 'website'
        ]);
    }

    /**
     * Generate structured data for events
     */
    private function generateEventStructuredData(Event $event, array $tickets, Request $request): string
    {
        $data = [
            "@context" => "https://schema.org",
            "@type" => "Event",
            "name" => $event->event_title,
            "description" => $event->description ?? '',
            "startDate" => $event->event_date . 'T' . ($event->start_time ?? '00:00:00'),
            "endDate" => $event->end_date ? ($event->end_date . 'T' . ($event->end_time ?? '23:59:59')) : null,
            "eventStatus" => "https://schema.org/EventScheduled",
            "eventAttendanceMode" => "https://schema.org/OfflineEventAttendanceMode",
            "location" => [
                "@type" => "Place",
                "name" => $event->venue_name ?? '',
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => $event->venue_address ?? '',
                    "addressLocality" => $event->city ?? '',
                    "addressRegion" => $event->state ?? '',
                    "addressCountry" => "NG"
                ]
            ],
            "image" => [
                $this->getFullImageUrl($request, $event->featured_image)
            ],
            "organizer" => [
                "@type" => "Organization",
                "name" => $event->organizer_name ?? "Eventlyy",
                "url" => $request->getBaseUrl()
            ],
            "url" => $request->fullUrl()
        ];

        // Add offers (tickets)
        if (!empty($tickets)) {
            $offers = [];
            foreach ($tickets as $ticket) {
                $offers[] = [
                    "@type" => "Offer",
                    "name" => $ticket['name'],
                    "price" => $ticket['price'],
                    "priceCurrency" => "NGN",
                    "availability" => $ticket['sold_out'] ? "https://schema.org/SoldOut" : "https://schema.org/InStock",
                    "validFrom" => date('c'),
                    "url" => $request->fullUrl() . "#ticket-" . $ticket['id']
                ];
            }
            $data["offers"] = $offers;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate event interaction JavaScript
     */
    private function generateEventInteractionScript(Event $event, bool $isPastEvent): string
    {
        return "
            document.addEventListener('DOMContentLoaded', function() {
                // Event countdown timer
                " . ($isPastEvent ? "" : $this->generateCountdownScript($event)) . "
                
                // Analytics tracking
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'view_event', {
                        event_category: 'Events',
                        event_label: '{$event->event_title}',
                        custom_parameter: '{$event->id}'
                    });
                }
                
                // Ticket selection tracking
                document.querySelectorAll('.ticket-select').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const ticketName = this.dataset.ticketName;
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'select_ticket', {
                                event_category: 'Tickets',
                                event_label: ticketName
                            });
                        }
                    });
                });
            });
        ";
    }

    /**
     * Generate countdown timer script
     */
    private function generateCountdownScript(Event $event): string
    {
        $dateString = $event->event_date . ' ' . ($event->start_time ?? '00:00:00');
        $eventTimestamp = strtotime($dateString);

        if ($eventTimestamp === false) {
            return "console.error('Invalid event date');";
        }

        return sprintf(
            "const eventDate=new Date(%d);" .
                "const el=document.getElementById('event-countdown');" .
                "if(el){function u(){const n=Date.now(),d=eventDate-n;" .
                "if(d>0){const da=Math.floor(d/864e5),h=Math.floor(d%%864e5/36e5),m=Math.floor(d%%36e5/6e4);" .
                "el.textContent=da+'d '+h+'h '+m+'m'}else{el.textContent='Event Started'}}" .
                "u();setInterval(u,6e4)}",
            $eventTimestamp * 1000
        );
    }

    /**
     * Generate RSS feed for events
     */
    private function generateEventsRSSFeed(array $events, Request $request): string
    {
        $baseUrl = $request->getBaseUrl();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>Eventlyy Events</title>' . "\n";
        $rss .= '<link>' . $baseUrl . '/events</link>' . "\n";
        $rss .= '<description>Latest events from Eventlyy</description>' . "\n";
        $rss .= '<language>en-us</language>' . "\n";

        foreach ($events as $event) {
            $eventUrl = $baseUrl . '/events/' . $event->id . '/' . $event->slug;

            $rss .= '<item>' . "\n";
            $rss .= '<title><![CDATA[' . $event->event_title . ']]></title>' . "\n";
            $rss .= '<link>' . $eventUrl . '</link>' . "\n";
            $rss .= '<description><![CDATA[' . getExcerpt($event->description ?? '', 300) . ']]></description>' . "\n";
            $rss .= '<pubDate>' . date('r', strtotime($event->created_at)) . '</pubDate>' . "\n";
            $rss .= '<guid>' . $eventUrl . '</guid>' . "\n";
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return $rss;
    }

    /**
     * Generate sitemap for events
     */
    private function generateEventsSitemap(array $events, Request $request): string
    {
        $baseUrl = $request->getBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($events as $event) {
            $xml .= '<url>' . "\n";
            $xml .= '<loc>' . $baseUrl . '/events/' . $event->id . '/' . $event->slug . '</loc>' . "\n";
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($event->updated_at ?? $event->created_at)) . '</lastmod>' . "\n";
            $xml .= '<changefreq>weekly</changefreq>' . "\n";
            $xml .= '<priority>0.8</priority>' . "\n";
            $xml .= '</url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return $xml;
    }

    /**
     * Helper methods
     */
    private function getFullImageUrl(Request $request, ?string $image = '', string $fallback = ''): string
    {
        if (empty($image)) {
            if (empty($fallback)) {
                return $request->getBaseUrl() . '/dist/img/default-event.jpg';
            }
            $image = $fallback;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return $request->getBaseUrl() . '/' . ltrim($image, '/');
    }

    private function getCategoryName(?int $categoryId): ?string
    {
        if (!$categoryId) return null;

        $category = Categories::find($categoryId);
        return $category ? $category->name : null;
    }

    private function getEventMinPrice(int $eventId): ?float
    {
        $tickets = Ticket::where(['event_id' => $eventId]);
        $minPrice = null;

        foreach ($tickets as $ticket) {
            if ($ticket->price > 0 && ($minPrice === null || $ticket->price < $minPrice)) {
                $minPrice = $ticket->price;
            }
        }

        return $minPrice;
    }
}
