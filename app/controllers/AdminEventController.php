<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use Trees\Helper\Cities\Cities;
use Trees\Helper\Support\Image;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Logger\Logger;

class AdminEventController extends Controller
{
    protected $uploader;
    protected ?Event $eventModel;
    protected ?Ticket $ticketModel;
    protected const MAX_UPLOAD_FILES = 1;
    protected const UPLOAD_DIR = 'uploads/events/';
    protected const IMAGE_DIR = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR;
    public function onConstruct()
    {
        $this->view->setLayout('admin');
        $imageProcessor = new Image();
        $this->eventModel = new Event();
        $this->ticketModel = new Ticket();
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
        $events = $this->eventModel::paginate([
            'per_page' => $request->query('per_page', 10),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ]);

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

        // Load tickets with the event
        $tickets = Ticket::where(['event_id' => $event->id]);

        // Ensure tickets is always an array
        $event->tickets = is_array($tickets) ? $tickets : [];

        $view = [
            'event' => $event,
            'recentAttendees' => [],
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

            // Generate slug
            $data['slug'] = str_slug($data['event_title'], "_");
            $data['user_id'] = "admin";
            // $data['user_id'] = auth()->id();

            // Handle file upload
            if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'event_image');
                if ($uploadedFile !== null) {
                    $data['event_image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            }

            // Use transaction to save event and tickets
            $this->eventModel->transaction(function () use ($data, $ticketsData) {
                $event = Event::create($data);

                if (!$event) {
                    throw new \RuntimeException('Event creation failed');
                }

                // Save tickets
                foreach ($ticketsData as $ticketData) {
                    $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");
                    $ticketData['event_id'] = $event->id;

                    $ticket = Ticket::create($ticketData);

                    if (!$ticket) {
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
        }
    }

    public function edit(Request $request, Response $response, $slug)
    {
        $event = Event::findBySlug($slug);

        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
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
            // $ticketsToDelete = $data['tickets_to_delete'] ?? [];
            $ticketsToDelete = $data['tickets_to_delete'] ?? [];
            if (is_string($ticketsToDelete) && !empty($ticketsToDelete)) {
                $ticketsToDelete = explode(',', $ticketsToDelete);
                $ticketsToDelete = array_filter($ticketsToDelete, function ($value) {
                    return !empty(trim($value));
                });
            }
            unset($data['tickets'], $data['tickets_to_delete']);

            // Update slug if title changed
            if ($data['event_title'] !== $event->event_title) {
                $data['slug'] = str_slug($data['event_title'], "_");
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
            $this->eventModel->transaction(function () use ($event, $data, $ticketsData, $ticketsToDelete) {
                // Update event
                $updated = $event->update($data);
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
                                $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");

                                if (!$ticket->update($ticketData)) {
                                    throw new \RuntimeException('Ticket update failed: ' . $ticketData['ticket_name']);
                                }
                            }
                        } else {
                            // Create new ticket
                            $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");
                            $ticketData['event_id'] = $event->id;
                            unset($ticketData['id']); // Make sure no ID is passed

                            $ticket = Ticket::create($ticketData);
                            if (!$ticket) {
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

            if (!$ticket || $ticket->event_id != $eventId) {
                return $response->json(['success' => false, 'message' => 'Ticket not found'], 404);
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

        try {
            // Store the image path BEFORE starting any operations
            $imagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $event->event_image;

            // Use transaction to ensure all database deletions succeed or fail together
            $this->eventModel->transaction(function () use ($event) {
                // Get all tickets associated with this event
                $tickets = Ticket::where(['event_id' => $event->id]);

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
            if ($imagePath) {
                if (file_exists($imagePath) && is_file($imagePath)) {
                    if (!@unlink($imagePath)) {
                        // Log the error but don't fail the entire operation
                        Logger::warning("Failed to delete event image: " . $imagePath);
                    }
                }
            }

            FlashMessage::setMessage("Event and all associated tickets deleted successfully!");
            return $response->redirect("/admin/events/manage");
        } catch (TreesException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/manage");
        } catch (\RuntimeException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/manage");
        }
    }
}
