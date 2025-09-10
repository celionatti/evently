<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use App\models\Attendee;
use Trees\Http\Response;
use Trees\Logger\Logger;
use App\models\Categories;
use App\models\Transaction;
use Trees\Helper\Cities\Cities;
use Trees\Helper\Support\Image;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use App\models\TransactionTicket;
use Trees\Exception\TreesException;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminEventController extends Controller
{
    protected $uploader;
    protected ?Event $eventModel;
    protected ?Ticket $ticketModel;
    protected ?Attendee $attendeesModel;
    protected const MAX_UPLOAD_FILES = 1;
    protected const UPLOAD_DIR = 'uploads/events/';

    public function onConstruct()
    {
        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $this->view->setLayout('admin');
        $imageProcessor = new Image();
        $this->eventModel = new Event();
        $this->ticketModel = new Ticket();
        $this->attendeesModel = new Attendee();
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Dashboard");
        $this->uploader = new FileUploader(
            uploadDir: self::UPLOAD_DIR,
            maxFileSize: 5 * 1024 * 1024,
            allowedMimeTypes: ['image/jpg', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            overwriteExisting: false,
            imageProcessor: $imageProcessor,
            maxImageWidth: 1200,
            maxImageHeight: 1080,
            imageQuality: 85
        );

        $this->uploader->setQualitySettings(
            75, // JPEG quality
            85, // WebP quality
            6,  // PNG compression
            true // Convert to WebP
        );
    }

    public function manage(Request $request, Response $response)
    {
        // For organiser, only show their own events
        // For admin, show all events
        $queryOptions = [
            'per_page' => $request->query('per_page', 10),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        if (isOrganiser()) {
            // Organiser can only see their own events
            $queryOptions['conditions'] = ['user_id' => auth()->id];
        }

        $events = $this->eventModel::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($events['meta']);

        // Render the pagination links
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'events' => $events['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/events/manage', $view);
    }

    public function view(Request $request, Response $response, $slug)
    {
        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Check if organiser is trying to view someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only view your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Load tickets with the event
        $tickets = Ticket::where(['event_id' => $event->id]);
        $event->tickets = is_array($tickets) ? $tickets : [];

        // Calculate ticket statistics
        $totalTickets = 0;
        $soldTickets = 0;
        $totalRevenue = 0;

        foreach ($event->tickets as $ticket) {
            $totalTickets += $ticket->quantity;

            // Get sold count for this ticket
            $soldCount = Attendee::count(['ticket_id' => $ticket->id, 'status' => 'confirmed']);
            $ticket->sold = $soldCount; // Add sold count to ticket object

            $soldTickets += $soldCount;
            $totalRevenue += ($soldCount * $ticket->price);
        }

        // Calculate sales rate
        $salesRate = $totalTickets > 0 ? round(($soldTickets / $totalTickets) * 100) : 0;

        // Get total attendees count for this event
        $totalAttendees = Attendee::count(['event_id' => $event->id, 'status' => 'confirmed']);

        // Build query options for recent attendees
        $queryOptions = [
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC', 'status' => 'ASC'] // Show recent first, pending last
        ];

        // Get confirmed attendees for this event
        // $conditions = ['status' => 'confirmed', 'event_id' => $event->id];
        $conditions = ['event_id' => $event->id];
        $queryOptions['conditions'] = $conditions;

        $attendees = $this->attendeesModel::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($attendees['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        // Get attendee details with ticket information
        $attendeesWithTickets = [];
        foreach ($attendees['data'] as $attendee) {
            // Get ticket details for this attendee
            $ticket = Ticket::find($attendee->ticket_id);
            $attendee->ticket_name = $ticket ? $ticket->ticket_name : 'Unknown';
            $attendee->ticket_price = $ticket ? $ticket->price : 0;
            $attendee->amount = $attendee->ticket_price; // Set amount based on ticket price
            $attendeesWithTickets[] = $attendee;
        }

        $view = [
            'event' => $event,
            'recentAttendees' => $attendeesWithTickets,
            'pagination' => $paginationLinks,
            'ticketStats' => [
                'total_tickets' => $totalTickets,
                'sold_tickets' => $soldTickets,
                'total_revenue' => $totalRevenue,
                'sales_rate' => $salesRate,
                'total_attendees' => $totalAttendees
            ]
        ];

        return $this->render('admin/events/view', $view);
    }

    public function create()
    {
        $view = [
            'categories' => Categories::all(),
            'cities' => Cities::getAll('NG')
        ];

        return $this->render('admin/events/create', $view);
    }

    // public function insert(Request $request, Response $response)
    // {
    //     if ("POST" !== $request->getMethod()) {
    //         return;
    //     }
    //     $rules = [
    //         'event_title' => 'required|min:3',
    //         'category' => 'required',
    //         'description' => 'required|min:10',
    //         'event_link' => 'unique:events.event_link',
    //         'event_image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|min:1|max:' . self::MAX_UPLOAD_FILES,
    //         'venue' => 'required',
    //         'city' => 'required',
    //         'event_date' => 'required',
    //         'start_time' => 'required',
    //         'phone' => 'required',
    //         'mail' => 'required|email',
    //         'social' => 'required|url',
    //         'ticket_sales' => 'required',
    //         'status' => 'required'
    //     ];

    //     if (!$request->validate($rules, false)) {
    //         set_form_data($request->all());
    //         set_form_error($request->getErrors());
    //         return $response->redirect("/admin/events/create");
    //     }

    //     try {
    //         $data = $request->all();
    //         $ticketsData = $data['tickets'] ?? [];
    //         unset($data['tickets']);

    //         // Generate slug
    //         $data['slug'] = str_slug($data['event_title'], "_");
    //         $data['user_id'] = auth()->id;

    //         // Handle file upload
    //         if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
    //             $uploadedFile = $this->uploader->uploadFromRequest($request, 'event_image');
    //             if ($uploadedFile !== null) {
    //                 $data['event_image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
    //             }
    //         }

    //         // Use transaction to save event and tickets
    //         $this->eventModel->transaction(function () use ($data, $ticketsData) {
    //             $event = Event::create($data);

    //             if (!$event) {
    //                 throw new \RuntimeException('Event creation failed');
    //             }

    //             // Save tickets
    //             foreach ($ticketsData as $ticketData) {
    //                 $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");
    //                 $ticketData['event_id'] = $event->id;

    //                 $ticket = Ticket::create($ticketData);

    //                 if (!$ticket) {
    //                     throw new \RuntimeException('Ticket creation failed: ' . $ticketData['ticket_name']);
    //                 }
    //             }
    //         });
    //         FlashMessage::setMessage("New Event Created!");
    //         return $response->redirect("/admin/events/manage");
    //     } catch (TreesException $e) {
    //         set_form_data($request->all());
    //         FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
    //         return $response->redirect("/admin/events/create");
    //     }
    // }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'event_title' => 'required|min:3',
            'category' => 'required',
            'description' => 'required|min:10',
            'event_link' => 'unique:events.event_link',
            'event_image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|min:1|max:' . self::MAX_UPLOAD_FILES,
            'venue' => 'required',
            'city' => 'required',
            'event_date' => 'required',
            'start_time' => 'required',
            'phone' => 'required',
            'mail' => 'required|email',
            'social' => 'required|url',
            'ticket_sales' => 'required',
            'status' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/events/create");
        }

        try {
            $data = $request->all();
            $ticketsData = $data['tickets'] ?? [];
            unset($data['tickets']);

            // Generate slug FIRST
            $eventSlug = str_slug($data['event_title'], "_");
            $data['slug'] = $eventSlug;
            $data['user_id'] = auth()->id;

            // Handle file upload
            if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'event_image');
                if ($uploadedFile !== null) {
                    $data['event_image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            }

            // Use transaction to save event and tickets
            $eventId = null;
            $this->eventModel->transaction(function () use ($data, $ticketsData, $eventSlug, &$eventId) {
                // Create event and get the ID
                $eventId = Event::create($data);

                if (!$eventId || $eventId === false) {
                    throw new \RuntimeException('Event creation failed');
                }

                // Validate tickets data exists
                if (empty($ticketsData)) {
                    throw new \RuntimeException('At least one ticket must be provided');
                }

                // Save tickets - use the returned event ID
                foreach ($ticketsData as $ticketData) {
                    // Validate required ticket fields
                    if (empty($ticketData['ticket_name'])) {
                        throw new \RuntimeException('Ticket name is required for all tickets');
                    }
                    if (!isset($ticketData['price']) || $ticketData['price'] < 0) {
                        throw new \RuntimeException('Valid ticket price is required');
                    }
                    if (!isset($ticketData['quantity']) || $ticketData['quantity'] < 1) {
                        throw new \RuntimeException('Ticket quantity must be at least 1');
                    }

                    $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $eventSlug, "_");
                    $ticketData['event_id'] = $eventId; // Use the returned ID

                    $ticketId = Ticket::create($ticketData);

                    if (!$ticketId || $ticketId === false) {
                        throw new \RuntimeException('Ticket creation failed: ' . $ticketData['ticket_name']);
                    }
                }
            });

            FlashMessage::setMessage("New Event Created!");
            return $response->redirect("/admin/events/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/create");
        } catch (\RuntimeException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/create");
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Unexpected error occurred.", 'danger');
            return $response->redirect("/admin/events/create");
        }
    }

    public function edit(Request $request, Response $response, $slug)
    {
        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Check if organiser is trying to edit someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only edit your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Load tickets with the event
        $tickets = Ticket::where(['event_id' => $event->id]);

        // Ensure tickets is always an array
        $event->tickets = is_array($tickets) ? $tickets : [];

        $view = [
            'event' => $event,
            'categories' => Categories::all(),
            'cities' => Cities::getAll('NG')
        ];

        return $this->render('admin/events/edit', $view);
    }

    // public function update(Request $request, Response $response, $slug)
    // {
    //     if ("POST" !== $request->getMethod()) {
    //         return;
    //     }

    //     $event = Event::findBySlug($slug);
    //     if (!$event) {
    //         FlashMessage::setMessage("Event Not Found!", 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     }

    //     // Check if organiser is trying to update someone else's event
    //     if (isOrganiser() && $event->user_id !== auth()->id) {
    //         FlashMessage::setMessage("Access denied. You can only update your own events.", 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     }

    //     $rules = [
    //         'event_title' => 'required|min:3',
    //         'category' => 'required',
    //         'description' => 'required|min:10',
    //         'event_link' => "unique:events.event_link,event_link!={$event->event_link}",
    //         'event_image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|max:' . self::MAX_UPLOAD_FILES,
    //         'venue' => 'required',
    //         'city' => 'required',
    //         'event_date' => 'required',
    //         'start_time' => 'required',
    //         'phone' => 'required',
    //         'mail' => 'required|email',
    //         'social' => 'required|url',
    //         'ticket_sales' => 'required',
    //         'status' => 'required'
    //     ];

    //     if (!$request->validate($rules, false)) {
    //         set_form_data($request->all());
    //         set_form_error($request->getErrors());
    //         return $response->redirect("/admin/events/edit/{$slug}");
    //     }

    //     try {
    //         $data = $request->all();
    //         $ticketsData = $data['tickets'] ?? [];
    //         $ticketsToDelete = $data['tickets_to_delete'] ?? [];
    //         if (is_string($ticketsToDelete) && !empty($ticketsToDelete)) {
    //             $ticketsToDelete = explode(',', $ticketsToDelete);
    //             $ticketsToDelete = array_filter($ticketsToDelete, function ($value) {
    //                 return !empty(trim($value));
    //             });
    //         }
    //         unset($data['tickets'], $data['tickets_to_delete']);

    //         // Update slug if title changed
    //         if ($data['event_title'] !== $event->event_title) {
    //             $data['slug'] = str_slug($data['event_title'], "_");
    //         }

    //         // Handle file upload
    //         if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
    //             $uploadedFile = $this->uploader->uploadFromRequest($request, 'event_image');
    //             if ($uploadedFile !== null) {
    //                 // Delete old image if exists
    //                 if ($event->event_image && file_exists(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image)) {
    //                     @unlink(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image);
    //                 }
    //                 $data['event_image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
    //             }
    //         }

    //         // Use transaction to update event and tickets
    //         $this->eventModel->transaction(function () use ($event, $data, $ticketsData, $ticketsToDelete) {
    //             // Update event
    //             $updated = $event->update($data);
    //             if (!$updated) {
    //                 throw new \RuntimeException('Event update failed');
    //             }

    //             // Delete marked tickets
    //             if (!empty($ticketsToDelete)) {
    //                 foreach ($ticketsToDelete as $ticketId) {
    //                     $ticketId = (int) $ticketId; // Ensure it's an integer
    //                     if ($ticketId > 0) {
    //                         $ticket = Ticket::find($ticketId);
    //                         if ($ticket && $ticket->event_id == $event->id) {
    //                             $ticket->delete();
    //                         }
    //                     }
    //                 }
    //             }

    //             // Process tickets (update existing, create new)
    //             foreach ($ticketsData as $ticketData) {
    //                 if (!empty($ticketData['ticket_name'])) {
    //                     if (isset($ticketData['id']) && !empty($ticketData['id'])) {
    //                         // Update existing ticket
    //                         $ticket = Ticket::find($ticketData['id']);
    //                         if ($ticket && $ticket->event_id == $event->id) {
    //                             unset($ticketData['id']); // Remove ID from update data
    //                             $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");

    //                             if (!$ticket->update($ticketData)) {
    //                                 throw new \RuntimeException('Ticket update failed: ' . $ticketData['ticket_name']);
    //                             }
    //                         }
    //                     } else {
    //                         // Create new ticket
    //                         $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");
    //                         $ticketData['event_id'] = $event->id;
    //                         unset($ticketData['id']); // Make sure no ID is passed

    //                         $ticket = Ticket::create($ticketData);
    //                         if (!$ticket) {
    //                             throw new \RuntimeException('New ticket creation failed: ' . $ticketData['ticket_name']);
    //                         }
    //                     }
    //                 }
    //             }
    //         });

    //         FlashMessage::setMessage("Event Updated Successfully!");
    //         return $response->redirect("/admin/events/manage");
    //     } catch (TreesException $e) {
    //         set_form_data($request->all());
    //         FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
    //         return $response->redirect("/admin/events/edit/{$slug}");
    //     }
    // }

    public function update(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $event = Event::findBySlug($slug);
        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Check if organiser is trying to update someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only update your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        $rules = [
            'event_title' => 'required|min:3',
            'category' => 'required',
            'description' => 'required|min:10',
            'event_link' => "unique:events.event_link,event_link!={$event->event_link}",
            'event_image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|max:' . self::MAX_UPLOAD_FILES,
            'venue' => 'required',
            'city' => 'required',
            'event_date' => 'required',
            'start_time' => 'required',
            'phone' => 'required',
            'mail' => 'required|email',
            'social' => 'required|url',
            'ticket_sales' => 'required',
            'status' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/events/edit/{$slug}");
        }

        try {
            $data = $request->all();
            $ticketsData = $data['tickets'] ?? [];
            $ticketsToDelete = $data['tickets_to_delete'] ?? [];
            if (is_string($ticketsToDelete) && !empty($ticketsToDelete)) {
                $ticketsToDelete = explode(',', $ticketsToDelete);
                $ticketsToDelete = array_filter($ticketsToDelete, function ($value) {
                    return !empty(trim($value));
                });
            }
            unset($data['tickets'], $data['tickets_to_delete']);

            // Generate event slug FIRST before using it for tickets
            $eventSlug = $event->slug; // Use current slug by default
            if ($data['event_title'] !== $event->event_title) {
                $eventSlug = str_slug($data['event_title'], "_");
                $data['slug'] = $eventSlug;
            }

            // Handle file upload
            if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'event_image');
                if ($uploadedFile !== null) {
                    // Delete old image if exists
                    if ($event->event_image && file_exists(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image)) {
                        @unlink(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image);
                    }
                    $data['event_image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            }

            // Use transaction to update event and tickets
            $this->eventModel->transaction(function () use ($event, $data, $ticketsData, $ticketsToDelete, $eventSlug) {
                // Update event
                $updated = $event->updateInstance($data);
                if (!$updated) {
                    throw new \RuntimeException('Event update failed');
                }

                // Delete marked tickets
                if (!empty($ticketsToDelete)) {
                    foreach ($ticketsToDelete as $ticketId) {
                        $ticketId = (int) $ticketId; // Ensure it's an integer
                        if ($ticketId > 0) {
                            $ticket = Ticket::find($ticketId);
                            if ($ticket && $ticket->event_id == $event->id) {
                                $ticket->delete();
                            }
                        }
                    }
                }

                // Process tickets (update existing, create new)
                foreach ($ticketsData as $ticketData) {
                    if (!empty($ticketData['ticket_name'])) {
                        if (isset($ticketData['id']) && !empty($ticketData['id'])) {
                            // Update existing ticket
                            $ticket = Ticket::find($ticketData['id']);
                            if ($ticket && $ticket->event_id == $event->id) {
                                unset($ticketData['id']); // Remove ID from update data
                                $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $eventSlug, "_");

                                if (!$ticket->updateInstance($ticketData)) {
                                    throw new \RuntimeException('Ticket update failed: ' . $ticketData['ticket_name']);
                                }
                            }
                        } else {
                            // Create new ticket
                            $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $eventSlug, "_");
                            $ticketData['event_id'] = $event->id;
                            unset($ticketData['id']); // Make sure no ID is passed

                            $ticketId = Ticket::create($ticketData);
                            if ($ticketId === false) { // Check explicitly for false
                                throw new \RuntimeException('New ticket creation failed: ' . $ticketData['ticket_name']);
                            }
                        }
                    }
                }
            });

            FlashMessage::setMessage("Event Updated Successfully!");
            return $response->redirect("/admin/events/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/edit/{$slug}");
        }
    }

    public function deleteTicket(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $ticketId = $request->input('ticket_id');
        $eventId = $request->input('event_id');

        if (!$ticketId || !$eventId) {
            return $response->json(['success' => false, 'message' => 'Missing required parameters'], 400);
        }

        try {
            $ticket = Ticket::find($ticketId);
            $event = Event::find($eventId);

            if (!$ticket || !$event || $ticket->event_id != $event->id) {
                return $response->json(['success' => false, 'message' => 'Ticket not found'], 404);
            }

            // Check if organiser is trying to delete a ticket from someone else's event
            if (isOrganiser() && $event->user_id !== auth()->id) {
                return $response->json(['success' => false, 'message' => 'Access denied. You can only delete tickets from your own events.'], 403);
            }

            // Check if this is the last ticket for the event
            $ticketCount = Ticket::where(['event_id' => $eventId]);
            if (count($ticketCount) <= 1) {
                return $response->json(['success' => false, 'message' => 'Cannot delete the last ticket. Event must have at least one ticket.'], 400);
            }

            if ($ticket->delete()) {
                return $response->json(['success' => true, 'message' => 'Ticket deleted successfully']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to delete ticket'], 500);
            }
        } catch (TreesException $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // public function delete(Request $request, Response $response, $slug)
    // {
    //     if ("POST" !== $request->getMethod()) {
    //         return;
    //     }

    //     $event = Event::findBySlug($slug);

    //     if (!$event) {
    //         FlashMessage::setMessage("Event Not Found!", 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     }

    //     // Check if organiser is trying to delete someone else's event
    //     if (isOrganiser() && $event->user_id !== auth()->id) {
    //         FlashMessage::setMessage("Access denied. You can only delete your own events.", 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     }

    //     try {
    //         // Store the image path BEFORE starting any operations
    //         $imagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image;

    //         // Use transaction to ensure all database deletions succeed or fail together
    //         $this->eventModel->transaction(function () use ($event) {
    //             // Get all tickets associated with this event
    //             $tickets = Ticket::where(['event_id' => $event->id]);

    //             // Delete all tickets
    //             if (!empty($tickets)) {
    //                 foreach ($tickets as $ticket) {
    //                     $transactiontickets = TransactionTicket::where(['ticket_id' => $ticket->id]);
    //                     if (!empty($transactiontickets)) {
    //                         foreach ($transactiontickets as $transTicket) {
    //                             if (!$transTicket->delete()) {
    //                                 throw new \RuntimeException('Failed to delete transaction ticket: ' . $transTicket->id);
    //                             }
    //                         }
    //                     }

    //                     if (!$ticket->delete()) {
    //                         throw new \RuntimeException('Failed to delete ticket: ' . $ticket->id);
    //                     }
    //                 }
    //             }

    //             // Delete the event itself
    //             if (!$event->delete()) {
    //                 throw new \RuntimeException('Failed to delete event');
    //             }
    //         });

    //         // Delete event image file AFTER successful database operations
    //         if ($imagePath) {
    //             if (file_exists($imagePath) && is_file($imagePath)) {
    //                 if (!@unlink($imagePath)) {
    //                     // Log the error but don't fail the entire operation
    //                     Logger::warning("Failed to delete event image: " . $imagePath);
    //                 }
    //             }
    //         }

    //         FlashMessage::setMessage("Event and all associated tickets deleted successfully!");
    //         return $response->redirect("/admin/events/manage");
    //     } catch (TreesException $e) {
    //         FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     } catch (\RuntimeException $e) {
    //         FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
    //         return $response->redirect("/admin/events/manage");
    //     }
    // }

    public function delete(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Check if organiser is trying to delete someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only delete your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        try {
            // Store the image path BEFORE starting any operations
            $imagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image;

            // Use transaction to ensure all database deletions succeed or fail together
            $this->eventModel->transaction(function () use ($event) {
                // Get all tickets associated with this event
                $tickets = Ticket::where(['event_id' => $event->id]);

                // Get all transactions associated with this event
                $transactions = Transaction::where(['event_id' => $event->id]);

                // Get all attendees associated with this event
                $attendees = Attendee::where(['event_id' => $event->id]);

                // Delete all attendees first (they reference tickets and transactions)
                if (!empty($attendees)) {
                    foreach ($attendees as $attendee) {
                        if (!$attendee->delete()) {
                            throw new \RuntimeException('Failed to delete attendee: ' . $attendee->id);
                        }
                    }
                }

                // Delete all transaction tickets (they reference tickets and transactions)
                if (!empty($tickets)) {
                    foreach ($tickets as $ticket) {
                        $transactionTickets = TransactionTicket::where(['ticket_id' => $ticket->id]);
                        if (!empty($transactionTickets)) {
                            foreach ($transactionTickets as $transTicket) {
                                if (!$transTicket->delete()) {
                                    throw new \RuntimeException('Failed to delete transaction ticket: ' . $transTicket->id);
                                }
                            }
                        }
                    }
                }

                // Delete all transactions
                if (!empty($transactions)) {
                    foreach ($transactions as $transaction) {
                        if (!$transaction->delete()) {
                            throw new \RuntimeException('Failed to delete transaction: ' . $transaction->id);
                        }
                    }
                }

                // Delete all tickets
                if (!empty($tickets)) {
                    foreach ($tickets as $ticket) {
                        if (!$ticket->delete()) {
                            throw new \RuntimeException('Failed to delete ticket: ' . $ticket->id);
                        }
                    }
                }

                // Delete the event itself
                if (!$event->delete()) {
                    throw new \RuntimeException('Failed to delete event');
                }
            });

            // Delete event image file AFTER successful database operations
            if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                if (!@unlink($imagePath)) {
                    // Log the error but don't fail the entire operation
                    Logger::warning("Failed to delete event image: " . $imagePath);
                }
            }

            FlashMessage::setMessage("Event and all associated data deleted successfully!");
            return $response->redirect("/admin/events/manage");
        } catch (TreesException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/manage");
        } catch (\RuntimeException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/manage");
        }
    }

    public function eventStatus(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $slug = $request->input('event_slug');

        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/view/{$slug}");
        }

        // Check if organiser is trying to delete someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only delete your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        try {
            $updateData = [
                'status' => $request->input('status', $event->status)
            ];
            $updated = Event::updateWhere(['id' => $event->id], $updateData);
            if ($updated) {
                FlashMessage::setMessage("Event status updated successfully!", 'success');
            } else {
                FlashMessage::setMessage("No changes made to event status.", 'info');
            }
            return $response->redirect("/admin/events/view/{$slug}");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Error updating event status: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/view/{$slug}");
        }
    }

    public function ticketStatus(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $slug = $request->input('event_slug');

        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/view/{$slug}");
        }

        // Check if organiser is trying to delete someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only delete your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        try {
            $updateData = [
                'ticket_sales' => $request->input('ticket_sales', $event->ticket_sales)
            ];
            $updated = Event::updateWhere(['id' => $event->id], $updateData);
            if ($updated) {
                FlashMessage::setMessage("Ticket sales status updated successfully!", 'success');
            } else {
                FlashMessage::setMessage("No changes made to ticket sales status.", 'info');
            }
            return $response->redirect("/admin/events/view/{$slug}");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Error updating ticket status: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/view/{$slug}");
        }
    }

    public function exportAttendees(Request $request, Response $response, $slug)
    {
        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        // Check if organiser is trying to export someone else's event attendees
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only export attendees from your own events.", 'danger');
            return $response->redirect("/admin/events/manage");
        }

        try {
            // Get all attendees for this event
            $attendees = Attendee::where(['event_id' => $event->id]);

            // Get attendee details with ticket information
            $attendeesWithTickets = [];
            $totalRevenue = 0;

            foreach ($attendees as $attendee) {
                // Get ticket details for this attendee
                $ticket = Ticket::find($attendee->ticket_id);
                $attendee->ticket_name = $ticket ? $ticket->ticket_name : 'Unknown';
                $attendee->ticket_price = $ticket ? $ticket->price : 0;
                $attendee->amount = $attendee->ticket_price;
                $totalRevenue += $attendee->amount;
                $attendeesWithTickets[] = $attendee;
            }

            // Generate PDF
            $this->generateAttendeesPDF($event, $attendeesWithTickets, $totalRevenue);
        } catch (\Exception $e) {
            FlashMessage::setMessage("Error exporting attendees: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/view/{$slug}");
        }
    }

    private function generateAttendeesPDF($event, $attendees, $totalRevenue)
    {
        // Include TCPDF library
        require_once(ROOT_PATH . '/vendor/tecnickcom/tcpdf/tcpdf.php');

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Eventlyy Admin');
        $pdf->SetAuthor('Eventlyy');
        $pdf->SetTitle('Event Attendees - ' . $event->event_title);
        $pdf->SetSubject('Attendee Export');

        // Set default header data
        $pdf->SetHeaderData('', 0, 'EVENTLYY', 'Event Management System');

        // Set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);

        // Title
        $pdf->Cell(0, 10, 'Event Attendees Report', 0, 1, 'C');
        $pdf->Ln(5);

        // Event Details Section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Event Information', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 10);

        // Event details table
        $eventDetails = [
            ['Event Title:', htmlspecialchars($event->event_title)],
            ['Date:', date('j M, Y', strtotime($event->event_date))],
            ['Time:', date('g:i A', strtotime($event->start_time))],
            ['Venue:', htmlspecialchars($event->venue)],
            ['City:', htmlspecialchars($event->city)],
            ['Status:', ucfirst($event->status)],
            ['Ticket Sales:', ucfirst($event->ticket_sales)],
        ];

        foreach ($eventDetails as $detail) {
            $pdf->Cell(40, 6, $detail[0], 0, 0, 'L');
            $pdf->Cell(0, 6, $detail[1], 0, 1, 'L');
        }

        $pdf->Ln(10);

        // Summary Statistics
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Summary Statistics', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 10);

        $confirmedCount = count(array_filter($attendees, fn($a) => $a->status === 'confirmed'));
        $pendingCount = count(array_filter($attendees, fn($a) => $a->status === 'pending'));
        $checkedInCount = count(array_filter($attendees, fn($a) => $a->status === 'checked'));

        $summaryStats = [
            ['Total Registrations:', count($attendees)],
            ['Confirmed Attendees:', $confirmedCount],
            ['Pending Confirmations:', $pendingCount],
            ['Checked In:', $checkedInCount],
            ['Total Revenue:', '₦' . number_format($totalRevenue)],
            ['Export Date:', date('j M, Y g:i A')],
        ];

        foreach ($summaryStats as $stat) {
            $pdf->Cell(40, 6, $stat[0], 0, 0, 'L');
            $pdf->Cell(0, 6, $stat[1], 0, 1, 'L');
        }

        $pdf->Ln(10);

        // Attendees Table
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Attendee Details', 0, 1, 'L');
        $pdf->Ln(5);

        if (!empty($attendees)) {
            // Table header
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);

            $pdf->Cell(8, 8, '#', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Name', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Email', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ticket Type', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Amount', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Status', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Purchase Date', 1, 1, 'C', true);

            // Table content
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetFillColor(245, 245, 245);

            foreach ($attendees as $index => $attendee) {
                $fill = ($index % 2 == 0) ? true : false;

                // Handle long text by truncating
                $name = strlen($attendee->name) > 20 ? substr($attendee->name, 0, 17) . '...' : $attendee->name;
                $email = strlen($attendee->email) > 25 ? substr($attendee->email, 0, 22) . '...' : $attendee->email;
                $ticketName = strlen($attendee->ticket_name) > 15 ? substr($attendee->ticket_name, 0, 12) . '...' : $attendee->ticket_name;

                $pdf->Cell(8, 7, htmlspecialchars((string)$index . '1'), 1, 0, 'C', $fill);
                $pdf->Cell(35, 7, htmlspecialchars($name), 1, 0, 'L', $fill);
                $pdf->Cell(40, 7, htmlspecialchars($email), 1, 0, 'L', $fill);
                $pdf->Cell(25, 7, htmlspecialchars($ticketName), 1, 0, 'L', $fill);
                $pdf->Cell(20, 7, '₦' . number_format($attendee->amount), 1, 0, 'R', $fill);
                $pdf->Cell(20, 7, ucfirst($attendee->status), 1, 0, 'C', $fill);
                $pdf->Cell(25, 7, date('j M, Y', strtotime($attendee->created_at)), 1, 1, 'C', $fill);

                // Check if we need a new page
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                    // Repeat header on new page
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->SetFillColor(230, 230, 230);

                    $pdf->Cell(8, 8, '#', 1, 0, 'C', true);
                    $pdf->Cell(35, 8, 'Name', 1, 0, 'C', true);
                    $pdf->Cell(40, 8, 'Email', 1, 0, 'C', true);
                    $pdf->Cell(25, 8, 'Ticket Type', 1, 0, 'C', true);
                    $pdf->Cell(20, 8, 'Amount', 1, 0, 'C', true);
                    $pdf->Cell(20, 8, 'Status', 1, 0, 'C', true);
                    $pdf->Cell(25, 8, 'Purchase Date', 1, 1, 'C', true);

                    $pdf->SetFont('helvetica', '', 8);
                }
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'No attendees found for this event.', 0, 1, 'C');
        }

        // Footer note
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Generated by Eventlyy Admin System on ' . date('j M, Y \a\t g:i A'), 0, 1, 'C');

        // Clean any output buffer
        if (ob_get_contents()) ob_end_clean();

        // Output PDF
        $filename = 'attendees_' . str_slug($event->event_title) . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D'); // 'D' for download
        exit;
    }
}
