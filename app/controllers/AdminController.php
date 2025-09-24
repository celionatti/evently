<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use Trees\Http\Request;
use Trees\Http\Response;
use App\controllers\BaseController;
use App\models\Attendee;
use App\models\Ticket;
use App\models\Categories;
use Trees\Database\Database;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminController extends BaseController
{
    protected $user;
    protected ?Event $eventModel;
    protected ?Attendee $attendeeModel;
    protected ?Ticket $ticketModel;
    protected $db;

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
        $this->db = Database::getInstance();
    }

    public function dashboard(Request $request, Response $response)
    {
        // Get dashboard analytics data
        $analytics = $this->getDashboardAnalytics();

        $view = [
            'user' => $this->user,
            'analytics' => $analytics,
            'recentEvents' => $this->getRecentEvents(5),
            'upcomingEvents' => $this->getUpcomingEvents(5),
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
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        // Total Events
        $totalEvents = $isOrganiser 
            ? Event::count(['user_id' => $userId]) 
            : Event::count();

        // Active Events
        $activeEvents = $isOrganiser 
            ? Event::count(['user_id' => $userId, 'status' => 'active']) 
            : Event::count(['status' => 'active']);

        // Total Attendees
        if ($isOrganiser) {
            $organizerEvents = Event::where(['user_id' => $userId]);
            $eventIds = array_map(fn($event) => $event->id, $organizerEvents);
            $totalAttendees = 0;
            $confirmedAttendees = 0;
            
            if (!empty($eventIds)) {
                foreach ($eventIds as $eventId) {
                    $totalAttendees += Attendee::count(['event_id' => $eventId]);
                    $confirmedAttendees += Attendee::count(['event_id' => $eventId, 'status' => 'confirmed']);
                }
            }
        } else {
            $totalAttendees = Attendee::count();
            $confirmedAttendees = Attendee::count(['status' => 'confirmed']);
        }

        // Calculate revenue
        $totalRevenue = $this->calculateTotalRevenue($isOrganiser ? $userId : null);
        $monthlyRevenue = $this->calculateMonthlyRevenue($isOrganiser ? $userId : null);

        // Tickets sold this month
        $currentMonth = date('Y-m');
        $monthlyTicketsSold = $this->getMonthlyTicketsSold($currentMonth, $isOrganiser ? $userId : null);
        $lastMonthTicketsSold = $this->getMonthlyTicketsSold(date('Y-m', strtotime('-1 month')), $isOrganiser ? $userId : null);
        
        // Calculate growth percentage
        $ticketGrowth = $lastMonthTicketsSold > 0 
            ? round((($monthlyTicketsSold - $lastMonthTicketsSold) / $lastMonthTicketsSold) * 100, 1)
            : ($monthlyTicketsSold > 0 ? 100 : 0);

        // Recent activity count (events created in last 30 days)
        $recentActivityCount = $this->getRecentActivityCount($isOrganiser ? $userId : null);

        return [
            'total_events' => $totalEvents,
            'active_events' => $activeEvents,
            'total_attendees' => $totalAttendees,
            'confirmed_attendees' => $confirmedAttendees,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_tickets_sold' => $monthlyTicketsSold,
            'ticket_growth_percentage' => $ticketGrowth,
            'recent_activity_count' => $recentActivityCount,
            'average_attendees_per_event' => $totalEvents > 0 ? round($confirmedAttendees / $totalEvents, 1) : 0,
            'event_completion_rate' => $totalEvents > 0 ? round(($this->getCompletedEventsCount($isOrganiser ? $userId : null) / $totalEvents) * 100, 1) : 0
        ];
    }

    private function getRecentEvents(int $limit = 5): array
    {
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        if ($isOrganiser) {
            $events = Event::where(['user_id' => $userId]);
            // Sort by created_at DESC manually since we can't use ORDER BY with where()
            usort($events, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));
            $events = array_slice($events, 0, $limit);
        } else {
            // For admin, we need to use raw query for ordering
            $sql = "SELECT * FROM events ORDER BY created_at DESC LIMIT ?";
            $eventData = $this->db->query($sql, [$limit]);
            
            // Check if query returned false (error) or empty array
            if ($eventData === false) {
                return [];
            }
            
            $events = [];
            foreach ($eventData as $data) {
                $event = new Event();
                $event->fill($data);
                $event->exists = true;
                $event->original = $data;
                $events[] = $event;
            }
        }

        // Add attendee counts and revenue to each event
        foreach ($events as $event) {
            $attendeeCount = Attendee::count(['event_id' => $event->id, 'status' => 'confirmed']);
            $event->attendee_count = $attendeeCount;
            
            // Calculate revenue for this event
            $eventRevenue = 0;
            $attendees = Attendee::where(['event_id' => $event->id, 'status' => 'confirmed']);
            foreach ($attendees as $attendee) {
                $ticket = Ticket::find($attendee->ticket_id);
                if ($ticket) {
                    $eventRevenue += (float)$ticket->price;
                }
            }
            $event->revenue = $eventRevenue;
        }

        return $events;
    }

    private function getUpcomingEvents(int $limit = 5): array
    {
        $today = date('Y-m-d');
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        if ($isOrganiser) {
            $sql = "SELECT * FROM events WHERE user_id = ? AND event_date >= ? ORDER BY event_date ASC LIMIT ?";
            $eventData = $this->db->query($sql, [$userId, $today, $limit]);
        } else {
            $sql = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC LIMIT ?";
            $eventData = $this->db->query($sql, [$today, $limit]);
        }

        // Check if query returned false (error)
        if ($eventData === false) {
            return [];
        }

        $events = [];
        foreach ($eventData as $data) {
            $event = new Event();
            $event->fill($data);
            $event->exists = true;
            $event->original = $data;
            $events[] = $event;
        }

        // Add days until event and ticket sales info
        foreach ($events as $event) {
            $eventDate = new \DateTime($event->event_date);
            $currentDate = new \DateTime($today);
            $interval = $currentDate->diff($eventDate);
            $event->days_until = $interval->days;
            
            // Add ticket sales info
            $attendeeCount = Attendee::count(['event_id' => $event->id, 'status' => 'confirmed']);
            $totalTickets = $this->getTotalTicketsForEvent($event->id);
            $event->tickets_sold = $attendeeCount;
            $event->total_tickets = $totalTickets;
            $event->sales_percentage = $totalTickets > 0 ? round(($attendeeCount / $totalTickets) * 100, 1) : 0;
        }

        return $events;
    }

    private function getTopSellingEvents(int $limit = 5): array
    {
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        if ($isOrganiser) {
            $sql = "
                SELECT e.*, COUNT(a.id) as tickets_sold,
                       COALESCE(SUM(t.price), 0) as revenue
                FROM events e 
                LEFT JOIN attendees a ON e.id = a.event_id AND a.status = 'confirmed'
                LEFT JOIN tickets t ON a.ticket_id = t.id
                WHERE e.user_id = ?
                GROUP BY e.id 
                ORDER BY tickets_sold DESC, revenue DESC 
                LIMIT ?";
            $eventData = $this->db->query($sql, [$userId, $limit]);
        } else {
            $sql = "
                SELECT e.*, COUNT(a.id) as tickets_sold,
                       COALESCE(SUM(t.price), 0) as revenue
                FROM events e 
                LEFT JOIN attendees a ON e.id = a.event_id AND a.status = 'confirmed'
                LEFT JOIN tickets t ON a.ticket_id = t.id
                GROUP BY e.id 
                ORDER BY tickets_sold DESC, revenue DESC 
                LIMIT ?";
            $eventData = $this->db->query($sql, [$limit]);
        }

        // Check if query returned false (error)
        if ($eventData === false) {
            return [];
        }

        $events = [];
        foreach ($eventData as $data) {
            $event = new Event();
            $event->fill($data);
            $event->exists = true;
            $event->original = $data;
            $events[] = $event;
        }

        return $events;
    }

    private function getMonthlyStats(): array
    {
        $months = [];
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthName = date('M Y', strtotime("-{$i} months"));

            if ($isOrganiser) {
                // Events created
                $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?";
                $eventsResult = $this->db->query($sql, [$userId, $month]);
                $eventsCount = ($eventsResult !== false) ? ($eventsResult[0]['count'] ?? 0) : 0;

                // Tickets sold and revenue
                $sql = "SELECT COUNT(a.id) as tickets_sold, COALESCE(SUM(t.price), 0) as revenue
                        FROM events e 
                        LEFT JOIN attendees a ON e.id = a.event_id AND a.status = 'confirmed' 
                        LEFT JOIN tickets t ON a.ticket_id = t.id 
                        WHERE e.user_id = ? AND DATE_FORMAT(a.created_at, '%Y-%m') = ?";
                $salesResult = $this->db->query($sql, [$userId, $month]);
                $ticketsSold = ($salesResult !== false) ? ($salesResult[0]['tickets_sold'] ?? 0) : 0;
                $revenue = ($salesResult !== false) ? ($salesResult[0]['revenue'] ?? 0) : 0;
            } else {
                // For admin: all data
                $sql = "SELECT COUNT(*) as count FROM events WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
                $eventsResult = $this->db->query($sql, [$month]);
                $eventsCount = ($eventsResult !== false) ? ($eventsResult[0]['count'] ?? 0) : 0;

                $sql = "SELECT COUNT(a.id) as tickets_sold, COALESCE(SUM(t.price), 0) as revenue
                        FROM attendees a 
                        LEFT JOIN tickets t ON a.ticket_id = t.id 
                        WHERE a.status = 'confirmed' AND DATE_FORMAT(a.created_at, '%Y-%m') = ?";
                $salesResult = $this->db->query($sql, [$month]);
                $ticketsSold = ($salesResult !== false) ? ($salesResult[0]['tickets_sold'] ?? 0) : 0;
                $revenue = ($salesResult !== false) ? ($salesResult[0]['revenue'] ?? 0) : 0;
            }

            $months[] = [
                'month' => $monthName,
                'events_created' => (int)$eventsCount,
                'tickets_sold' => (int)$ticketsSold,
                'revenue' => (float)$revenue
            ];
        }

        return $months;
    }

    private function getCategoryStats(): array
    {
        $isOrganiser = isOrganiser();
        $userId = auth()->id;

        if ($isOrganiser) {
            $sql = "
                SELECT e.category, COUNT(e.id) as event_count, 
                       COUNT(a.id) as tickets_sold,
                       COALESCE(SUM(t.price), 0) as revenue
                FROM events e 
                LEFT JOIN attendees a ON e.id = a.event_id AND a.status = 'confirmed'
                LEFT JOIN tickets t ON a.ticket_id = t.id
                WHERE e.user_id = ?
                GROUP BY e.category 
                ORDER BY event_count DESC";
            $results = $this->db->query($sql, [$userId]);
        } else {
            $sql = "
                SELECT e.category, COUNT(e.id) as event_count, 
                       COUNT(a.id) as tickets_sold,
                       COALESCE(SUM(t.price), 0) as revenue
                FROM events e 
                LEFT JOIN attendees a ON e.id = a.event_id AND a.status = 'confirmed'
                LEFT JOIN tickets t ON a.ticket_id = t.id
                GROUP BY e.category 
                ORDER BY event_count DESC";
            $results = $this->db->query($sql, []);
        }

        // Check if query returned false (error)
        if ($results === false) {
            return [];
        }

        // Get category names
        foreach ($results as &$result) {
            $category = Categories::find($result['category']);
            $result['category_name'] = $category ? ucfirst($category->name) : 'Unknown';
        }

        return $results;
    }

    private function getTicketSalesChartData(): array
    {
        $monthlyStats = $this->getMonthlyStats();
        
        return [
            'labels' => array_column($monthlyStats, 'month'),
            'data' => array_column($monthlyStats, 'tickets_sold')
        ];
    }

    private function getRevenueChartData(): array
    {
        $monthlyStats = $this->getMonthlyStats();
        
        return [
            'labels' => array_column($monthlyStats, 'month'),
            'data' => array_column($monthlyStats, 'revenue')
        ];
    }

    private function calculateTotalRevenue(int|string|null $userId = null): float
    {
        if ($userId) {
            // For organiser: get revenue from their events only
            $events = Event::where(['user_id' => $userId]);
            $totalRevenue = 0;
            foreach ($events as $event) {
                $attendees = Attendee::where(['event_id' => $event->id, 'status' => 'confirmed']);
                foreach ($attendees as $attendee) {
                    $ticket = Ticket::find($attendee->ticket_id);
                    if ($ticket) {
                        $totalRevenue += (float)$ticket->price;
                    }
                }
            }
            return $totalRevenue;
        } else {
            // For admin: get all revenue
            $attendees = Attendee::where(['status' => 'confirmed']);
            $totalRevenue = 0;
            foreach ($attendees as $attendee) {
                $ticket = Ticket::find($attendee->ticket_id);
                if ($ticket) {
                    $totalRevenue += (float)$ticket->price;
                }
            }
            return $totalRevenue;
        }
    }

    private function calculateMonthlyRevenue(int|string|null $userId = null): float
    {
        $currentMonth = date('Y-m');
        
        if ($userId) {
            $sql = "SELECT COALESCE(SUM(t.price), 0) as revenue
                    FROM events e 
                    JOIN attendees a ON e.id = a.event_id 
                    JOIN tickets t ON a.ticket_id = t.id 
                    WHERE e.user_id = ? AND a.status = 'confirmed' 
                    AND DATE_FORMAT(a.created_at, '%Y-%m') = ?";
            $result = $this->db->query($sql, [$userId, $currentMonth]);
            return ($result !== false) ? (float)($result[0]['revenue'] ?? 0) : 0.0;
        } else {
            $sql = "SELECT COALESCE(SUM(t.price), 0) as revenue
                    FROM attendees a 
                    JOIN tickets t ON a.ticket_id = t.id 
                    WHERE a.status = 'confirmed' 
                    AND DATE_FORMAT(a.created_at, '%Y-%m') = ?";
            $result = $this->db->query($sql, [$currentMonth]);
            return ($result !== false) ? (float)($result[0]['revenue'] ?? 0) : 0.0;
        }
    }

    private function getMonthlyTicketsSold(string $month, int|string|null $userId = null): int
    {
        if ($userId) {
            $sql = "SELECT COUNT(a.id) as count
                    FROM events e 
                    JOIN attendees a ON e.id = a.event_id 
                    WHERE e.user_id = ? AND a.status = 'confirmed' 
                    AND DATE_FORMAT(a.created_at, '%Y-%m') = ?";
            $result = $this->db->query($sql, [$userId, $month]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        } else {
            $sql = "SELECT COUNT(*) as count FROM attendees 
                    WHERE status = 'confirmed' 
                    AND DATE_FORMAT(created_at, '%Y-%m') = ?";
            $result = $this->db->query($sql, [$month]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        }
    }

    private function getRecentActivityCount(int|string|null $userId = null): int
    {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        if ($userId) {
            $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = ? AND created_at >= ?";
            $result = $this->db->query($sql, [$userId, $thirtyDaysAgo]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        } else {
            $sql = "SELECT COUNT(*) as count FROM events WHERE created_at >= ?";
            $result = $this->db->query($sql, [$thirtyDaysAgo]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        }
    }

    private function getCompletedEventsCount(int|string|null $userId = null): int
    {
        $today = date('Y-m-d');
        
        if ($userId) {
            $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = ? AND event_date < ?";
            $result = $this->db->query($sql, [$userId, $today]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        } else {
            $sql = "SELECT COUNT(*) as count FROM events WHERE event_date < ?";
            $result = $this->db->query($sql, [$today]);
            return ($result !== false) ? (int)($result[0]['count'] ?? 0) : 0;
        }
    }

    private function getTotalTicketsForEvent(int|string $eventId): int
    {
        $tickets = Ticket::where(['event_id' => $eventId]);
        $total = 0;
        foreach ($tickets as $ticket) {
            $total += (int)$ticket->quantity;
        }
        return $total;
    }
}