<?php

declare(strict_types=1);

namespace App\traits;

use App\models\Event;
use App\models\Ticket;
use App\models\Attendee;
use Trees\Logger\Logger;
use App\models\Transaction;
use Trees\Database\Database;
use Trees\Database\QueryBuilder\QueryBuilder;

trait EventMaintenanceTrait
{
    /**
     * Close expired events (past event date)
     * Updates ticket_sales to 'close' and status to 'disable' for events that have passed
     * 
     * @param bool $dryRun - If true, only returns count without making changes
     * @return array - Results of the operation
     */
    public function closeExpiredEvents(bool $dryRun = false): array
    {
        try {
            $currentDateTime = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            
            // Get events that have passed their event date
            $db = Database::getInstance();
            $builder = new QueryBuilder($db);
            
            // Find events that are still active/open but have passed their event date
            $expiredEvents = $builder->table('events')
                ->where('event_date', $today, '<')
                ->where(function($query) {
                    $query->where('status', 'active')
                          ->orWhere('ticket_sales', 'open');
                })
                ->get();
            
            if (empty($expiredEvents)) {
                return [
                    'success' => true,
                    'message' => 'No expired events found to close.',
                    'affected_count' => 0,
                    'events' => []
                ];
            }
            
            $affectedCount = 0;
            $processedEvents = [];
            
            if (!$dryRun) {
                // Update expired events
                foreach ($expiredEvents as $eventData) {
                    $updateResult = $builder->table('events')
                        ->where('id', $eventData['id'])
                        ->update([
                            'ticket_sales' => 'close',
                            'status' => 'disable',
                            'updated_at' => $currentDateTime
                        ]);
                    
                    if ($updateResult) {
                        $affectedCount++;
                        $processedEvents[] = [
                            'id' => $eventData['id'],
                            'title' => $eventData['event_title'],
                            'event_date' => $eventData['event_date'],
                            'previous_status' => $eventData['status'],
                            'previous_ticket_sales' => $eventData['ticket_sales']
                        ];
                        
                        // Log the closure
                        Logger::info("Event closed automatically: ID {$eventData['id']}, Title: {$eventData['event_title']}, Date: {$eventData['event_date']}");
                    }
                }
            } else {
                // Dry run - just count
                $affectedCount = count($expiredEvents);
                foreach ($expiredEvents as $eventData) {
                    $processedEvents[] = [
                        'id' => $eventData['id'],
                        'title' => $eventData['event_title'],
                        'event_date' => $eventData['event_date'],
                        'current_status' => $eventData['status'],
                        'current_ticket_sales' => $eventData['ticket_sales']
                    ];
                }
            }
            
            $message = $dryRun 
                ? "Dry Run: Found {$affectedCount} expired events that would be closed."
                : "Successfully closed {$affectedCount} expired events.";
            
            return [
                'success' => true,
                'message' => $message,
                'affected_count' => $affectedCount,
                'events' => $processedEvents
            ];
            
        } catch (\Exception $e) {
            Logger::error("Error in closeExpiredEvents: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to close expired events: ' . $e->getMessage(),
                'affected_count' => 0,
                'events' => []
            ];
        }
    }
    
    /**
     * Delete old events (3 months after event date)
     * Completely removes events and all associated data that are 3+ months old
     * 
     * @param int $monthsOld - How many months old events should be deleted (default: 3)
     * @param bool $dryRun - If true, only returns count without making changes
     * @return array - Results of the operation
     */
    public function deleteOldEvents(int $monthsOld = 3, bool $dryRun = false): array
    {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$monthsOld} months"));
            
            $db = Database::getInstance();
            $builder = new QueryBuilder($db);
            
            // Get events older than cutoff date
            $oldEventsData = $builder->table('events')
                ->where('event_date', $cutoffDate, '<')
                ->get();
            
            if (empty($oldEventsData)) {
                return [
                    'success' => true,
                    'message' => "No events older than {$monthsOld} months found for deletion.",
                    'stats' => [
                        'deleted_events' => 0,
                        'deleted_attendees' => 0,
                        'deleted_tickets' => 0,
                        'deleted_transactions' => 0,
                        'deleted_transaction_tickets' => 0,
                        'freed_space' => '0 B'
                    ],
                    'events' => []
                ];
            }
            
            // Convert to Event objects for easier handling
            $oldEvents = [];
            foreach ($oldEventsData as $eventData) {
                $event = new Event();
                $event->fill($eventData);
                $event->exists = true;
                $event->original = $eventData;
                $oldEvents[] = $event;
            }
            
            $stats = [
                'deleted_events' => 0,
                'deleted_attendees' => 0,
                'deleted_tickets' => 0,
                'deleted_transactions' => 0,
                'deleted_transaction_tickets' => 0,
                'freed_space' => 0
            ];
            
            $processedEvents = [];
            
            if (!$dryRun) {
                // Actually delete the events
                foreach ($oldEvents as $event) {
                    try {
                        $eventStats = $this->deleteEventWithDependencies($event);
                        $stats['deleted_events']++;
                        $stats['deleted_attendees'] += $eventStats['attendees'];
                        $stats['deleted_tickets'] += $eventStats['tickets'];
                        $stats['deleted_transactions'] += $eventStats['transactions'];
                        $stats['deleted_transaction_tickets'] += $eventStats['transaction_tickets'];
                        $stats['freed_space'] += $eventStats['image_size'];
                        
                        $processedEvents[] = [
                            'id' => $event->id,
                            'title' => $event->event_title,
                            'event_date' => $event->event_date,
                            'deleted_attendees' => $eventStats['attendees'],
                            'deleted_tickets' => $eventStats['tickets']
                        ];
                        
                        Logger::info("Event auto-deleted: ID {$event->id}, Title: {$event->event_title}, Date: {$event->event_date}");
                        
                    } catch (\Exception $e) {
                        Logger::error("Failed to delete event ID {$event->id}: " . $e->getMessage());
                        continue;
                    }
                }
            } else {
                // Dry run - calculate what would be deleted
                foreach ($oldEvents as $event) {
                    $eventStats = $this->calculateEventDeletionStats($event);
                    $stats['deleted_events']++;
                    $stats['deleted_attendees'] += $eventStats['attendees'];
                    $stats['deleted_tickets'] += $eventStats['tickets'];
                    $stats['deleted_transactions'] += $eventStats['transactions'];
                    $stats['deleted_transaction_tickets'] += $eventStats['transaction_tickets'];
                    $stats['freed_space'] += $eventStats['image_size'];
                    
                    $processedEvents[] = [
                        'id' => $event->id,
                        'title' => $event->event_title,
                        'event_date' => $event->event_date,
                        'attendees_count' => $eventStats['attendees'],
                        'tickets_count' => $eventStats['tickets']
                    ];
                }
            }
            
            $stats['freed_space'] = $this->formatBytes($stats['freed_space']);
            
            $message = $dryRun
                ? "Dry Run: Found {$stats['deleted_events']} events older than {$monthsOld} months that would be deleted with {$stats['deleted_attendees']} attendees and {$stats['deleted_tickets']} tickets."
                : "Successfully deleted {$stats['deleted_events']} old events with {$stats['deleted_attendees']} attendees, {$stats['deleted_tickets']} tickets, and {$stats['deleted_transactions']} transactions. Freed space: {$stats['freed_space']}.";
            
            return [
                'success' => true,
                'message' => $message,
                'stats' => $stats,
                'events' => $processedEvents
            ];
            
        } catch (\Exception $e) {
            Logger::error("Error in deleteOldEvents: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete old events: ' . $e->getMessage(),
                'stats' => [
                    'deleted_events' => 0,
                    'deleted_attendees' => 0,
                    'deleted_tickets' => 0,
                    'deleted_transactions' => 0,
                    'deleted_transaction_tickets' => 0,
                    'freed_space' => '0 B'
                ],
                'events' => []
            ];
        }
    }
    
    /**
     * Delete an event and all its dependencies in a transaction
     */
    private function deleteEventWithDependencies(Event $event): array
    {
        $stats = [
            'attendees' => 0,
            'tickets' => 0,
            'transactions' => 0,
            'transaction_tickets' => 0,
            'image_size' => 0
        ];
        
        // Store image path and size before deletion
        $imagePath = null;
        if (!empty($event->event_image)) {
            $imagePath = ROOT_PATH . '/public' . $event->event_image;
            if (file_exists($imagePath)) {
                $stats['image_size'] = filesize($imagePath);
            }
        }
        
        $eventModel = new Event();
        $eventModel->transaction(function () use ($event, &$stats, $imagePath) {
            // Get all related data
            $attendees = Attendee::where(['event_id' => $event->id]);
            $tickets = Ticket::where(['event_id' => $event->id]);
            $transactions = Transaction::where(['event_id' => $event->id]);
            
            // Delete attendees
            if (!empty($attendees)) {
                foreach ($attendees as $attendee) {
                    if ($attendee->delete()) {
                        $stats['attendees']++;
                    }
                }
            }
            
            // Delete transaction tickets
            if (!empty($tickets)) {
                $db = Database::getInstance();
                $builder = new QueryBuilder($db);
                
                foreach ($tickets as $ticket) {
                    $deletedCount = $builder->table('transaction_tickets')
                        ->where('ticket_id', $ticket->id)
                        ->delete();
                    $stats['transaction_tickets'] += $deletedCount;
                }
            }
            
            // Delete transactions
            if (!empty($transactions)) {
                foreach ($transactions as $transaction) {
                    if ($transaction->delete()) {
                        $stats['transactions']++;
                    }
                }
            }
            
            // Delete tickets
            if (!empty($tickets)) {
                foreach ($tickets as $ticket) {
                    if ($ticket->delete()) {
                        $stats['tickets']++;
                    }
                }
            }
            
            // Delete the event
            if (!$event->delete()) {
                throw new \RuntimeException('Failed to delete event');
            }
            
            // Delete image file after successful database deletion
            if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                if (!@unlink($imagePath)) {
                    Logger::warning("Failed to delete event image: " . $imagePath);
                }
            }
        });
        
        return $stats;
    }
    
    /**
     * Calculate stats for what would be deleted (dry run)
     */
    private function calculateEventDeletionStats(Event $event): array
    {
        $stats = [
            'attendees' => 0,
            'tickets' => 0,
            'transactions' => 0,
            'transaction_tickets' => 0,
            'image_size' => 0
        ];
        
        // Count attendees
        $attendees = Attendee::where(['event_id' => $event->id]);
        $stats['attendees'] = count($attendees);
        
        // Count tickets
        $tickets = Ticket::where(['event_id' => $event->id]);
        $stats['tickets'] = count($tickets);
        
        // Count transactions
        $transactions = Transaction::where(['event_id' => $event->id]);
        $stats['transactions'] = count($transactions);
        
        // Count transaction tickets
        if (!empty($tickets)) {
            $db = Database::getInstance();
            $builder = new QueryBuilder($db);
            
            foreach ($tickets as $ticket) {
                $count = $builder->table('transaction_tickets')
                    ->where('ticket_id', $ticket->id)
                    ->count();
                $stats['transaction_tickets'] += $count;
            }
        }
        
        // Get image size
        if (!empty($event->event_image)) {
            $imagePath = ROOT_PATH . '/public' . $event->event_image;
            if (file_exists($imagePath)) {
                $stats['image_size'] = filesize($imagePath);
            }
        }
        
        return $stats;
    }
    
    /**
     * Format bytes into human readable format
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        if ($size == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
    
    /**
     * Run both maintenance operations
     * Call this method from a cron job or scheduled task
     */
    public function runEventMaintenance(bool $dryRun = false): array
    {
        $results = [];
        
        // First, close expired events
        $results['close_expired'] = $this->closeExpiredEvents($dryRun);
        
        // Then, delete old events (3 months old)
        $results['delete_old'] = $this->deleteOldEvents(3, $dryRun);
        
        return $results;
    }
}