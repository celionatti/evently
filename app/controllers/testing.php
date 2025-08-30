<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Event;
use App\models\Ticket;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminEventController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin Events | Dashboard");
    }

    public function manage(Request $request, Response $response)
    {
        $events = Event::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['id' => 'DESC']
        ]);

        $pagination = new Paginator($events['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'events' => $events['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/events/manage', $view);
    }

    public function create()
    {
        $view = [];
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
            'venue' => 'required',
            'city' => 'required',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'phone' => 'required',
            'mail' => 'required|email',
            'social' => 'required|url',
            'ticket_sales' => 'required',
            'status' => 'required',
            'tickets.*.ticket_name' => 'required',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1'
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
            $data['user_id'] = auth()->id(); // Assuming you have authentication
            
            // Handle file upload
            if ($request->hasFile('event_image')) {
                $uploadResult = $this->handleImageUpload($request->file('event_image'));
                if ($uploadResult['success']) {
                    $data['event_image'] = $uploadResult['filename'];
                } else {
                    throw new \RuntimeException($uploadResult['error']);
                }
            }
            
            // Use transaction to save event and tickets
            Event::transaction(function() use ($data, $ticketsData) {
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
            
        } catch (\Exception $e) {
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
        $event->tickets = Ticket::where(['event_id' => $event->id]);
        
        $view = [
            'event' => $event
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
            'venue' => 'required',
            'city' => 'required',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'phone' => 'required',
            'mail' => 'required|email',
            'social' => 'required|url',
            'ticket_sales' => 'required',
            'status' => 'required',
            'tickets.*.ticket_name' => 'required',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1'
        ];
        
        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/events/edit/{$slug}");
        }
        
        try {
            $data = $request->all();
            $ticketsData = $data['tickets'] ?? [];
            unset($data['tickets']);
            
            // Handle file upload if a new image is provided
            if ($request->hasFile('event_image')) {
                $uploadResult = $this->handleImageUpload($request->file('event_image'));
                if ($uploadResult['success']) {
                    $data['event_image'] = $uploadResult['filename'];
                    
                    // Optionally delete the old image
                    if (!empty($event->event_image)) {
                        $this->deleteImage($event->event_image);
                    }
                } else {
                    throw new \RuntimeException($uploadResult['error']);
                }
            }
            
            // Use transaction to update event and tickets
            Event::transaction(function() use ($event, $data, $ticketsData) {
                // Update event
                if (!$event->update($data)) {
                    throw new \RuntimeException('Event update failed');
                }
                
                // Delete existing tickets
                $existingTickets = Ticket::where(['event_id' => $event->id]);
                foreach ($existingTickets as $existingTicket) {
                    if (!$existingTicket->delete()) {
                        throw new \RuntimeException('Failed to delete existing tickets');
                    }
                }
                
                // Save new tickets
                foreach ($ticketsData as $ticketData) {
                    $ticketData['slug'] = str_slug($ticketData['ticket_name'] . '-' . $event->slug, "_");
                    $ticketData['event_id'] = $event->id;
                    
                    $ticket = Ticket::create($ticketData);
                    
                    if (!$ticket) {
                        throw new \RuntimeException('Ticket creation failed: ' . $ticketData['ticket_name']);
                    }
                }
            });
            
            FlashMessage::setMessage("Event Updated!");
            return $response->redirect("/admin/events/manage");
            
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/edit/{$slug}");
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
            // Use transaction to delete event and tickets
            Event::transaction(function() use ($event) {
                // Delete tickets first
                $tickets = Ticket::where(['event_id' => $event->id]);
                foreach ($tickets as $ticket) {
                    if (!$ticket->delete()) {
                        throw new \RuntimeException('Failed to delete tickets');
                    }
                }
                
                // Delete event
                if (!$event->delete()) {
                    throw new \RuntimeException('Event deletion failed');
                }
                
                // Delete event image if exists
                if (!empty($event->event_image)) {
                    $this->deleteImage($event->event_image);
                }
            });
            
            FlashMessage::setMessage("Event Deleted!");
            return $response->redirect("/admin/events/manage");
            
        } catch (\Exception $e) {
            FlashMessage::setMessage("Delete Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/manage");
        }
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file)
    {
        $targetDir = "uploads/events/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $filename = uniqid() . '-' . basename($file["name"]);
        $targetFile = $targetDir . $filename;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'error' => 'File is not an image.'];
        }
        
        // Check file size (5MB max)
        if ($file["size"] > 5000000) {
            return ['success' => false, 'error' => 'Sorry, your file is too large.'];
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return ['success' => false, 'error' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.'];
        }
        
        // Try to upload file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'error' => 'Sorry, there was an error uploading your file.'];
        }
    }
    
    /**
     * Delete image file
     */
    private function deleteImage($filename)
    {
        $filePath = "uploads/events/" . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}