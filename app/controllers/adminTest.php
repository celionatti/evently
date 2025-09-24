<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use Trees\Http\Request;
use Trees\Http\Response;
use App\controllers\BaseController;
use App\models\Attendee;
use App\models\Ticket;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminTest extends BaseController
{
    protected $user;
    protected ?Event $eventModel;
    protected ?Attendee $attendeeModel;
    protected ?Ticket $ticketModel;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->view->setLayout('admin');

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");

        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/");
        }

        $this->view->setTitle("Eventlyy Admin | Dashboard");
        $this->user = auth();
        $this->eventModel = new Event();
        $this->attendeeModel = new Attendee();
        $this->ticketModel = new Ticket();
    }

    public function dashboard(Request $request, Response $response)
    {
        // Get dashboard analytics data
        $analytics = $this->getDashboardAnalytics();

        $view = [
            'user' => $this->user,
            'analytics' => $analytics,
            'recentEvents' => $this->getRecentEvents($request, $response),
            'upcomingEvents' => $this->getUpcomingEvents($request, $response),
            'topSellingEvents' => $this->getTopSellingEvents(5),
            'monthlyStats' => $this->getMonthlyStats(),
            'categoryStats' => $this->getCategoryStats(),
            'ticketSalesChart' => $this->getTicketSalesChartData(),
            'revenueChart' => $this->getRevenueChartData()
        ];

        return $this->render('admin/dashboard', $view);
    }

    private function getDashboardAnalytics(): array
    {
        return [];
    }

    private function getRecentEvents(Request $request, Response $response)
    {
        $queryOptions = [
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        if (isOrganiser()) {
            // Organiser can only see their own events
            $queryOptions['conditions'] = ['user_id' => auth()->id];
        }

        $events = $this->eventModel::paginate($queryOptions);

        foreach ($events['data'] as $event) {
            $attendeeCount = $this->attendeeModel->count(['event_id' => $event->id, 'status' => 'confirmed']);

            $event->attendee_count = $attendeeCount;

            // Calculate revenue for this event
            $eventRevenue = 0;
            $attendees = $this->attendeeModel->where(['event_id' => $event->id, 'status' => 'confirmed']);
            foreach ($attendees as $attendee) {
                $ticket = $this->ticketModel::find($attendee->ticket_id);
                if ($ticket) {
                    $eventRevenue += (float)$ticket->price;
                }
            }
            $event->revenue = $eventRevenue;
        }
    }

    private function getUpcomingEvents(Request $request, Response $response)
    {
        $today = date('Y-m-d');

        $queryOptions = [
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        if (isOrganiser()) {
            // Organiser can only see their own events
            $queryOptions['conditions'] = ['user_id' => auth()->id];

            // Add date filter - only show future events
            $queryOptions['where_raw'] = ['event_date >= CURDATE()'];
        }

        $events = $this->eventModel::paginate($queryOptions);

        // Add days until event
        foreach ($events['data'] as $event) {
            $eventDate = new \DateTime($event->event_date);
            $currentDate = new \DateTime($today);
            $interval = $currentDate->diff($eventDate);
            $event->days_until = $interval->days;

            // Add ticket sales info
            $attendeeCount = $this->attendeeModel->count(['event_id' => $event->id, 'status' => 'confirmed']);
            $totalTickets = $this->getTotalTicketsForEvent($event->id);
            $event->tickets_sold = $attendeeCount;
            $event->total_tickets = $totalTickets;
            $event->sales_percentage = $totalTickets > 0 ? round(($attendeeCount / $totalTickets) * 100, 1) : 0;
        }
        return $events;
    }

    private function getTopSellingEvents(int $limit = 5): array
    {
        return [];
    }

    private function getMonthlyStats(): array
    {
        return [];
    }

    private function getCategoryStats(): array
    {
        return [];
    }

    private function getTicketSalesChartData(): array
    {
        return [];
    }

    private function getRevenueChartData(): array
    {
        return [];
    }

    // Others Here.

    private function calculateTotalRevenue(?int $userId = null): float
    {
        return 0.0;
    }

    private function calculateMonthlyRevenue(?int $userId = null): float
    {
        return 0.0;
    }

    private function getMonthlyTicketsSold(string $month, ?int $userId = null): int
    {
        return 0;
    }

    private function getRecentActivityCount(?int $userId = null): int
    {
        return 0;
    }

    private function getCompletedEventsCount(?int $userId = null): int
    {
        return 0;
    }

    private function getTotalTicketsForEvent(int|string $eventId): int
    {
        $tickets = Ticket::where(['event_id' => $eventId]);
        $total = 0;
        foreach ($tickets as $ticket) {
            $total += $ticket->quantity;
        }
        return $total;
    }
}
