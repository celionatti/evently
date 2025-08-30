<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use App\models\Ticket;
use Trees\Helper\Cities\Cities;
use Trees\Helper\Support\Image;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminEventController extends Controller
{
    protected $uploader;
    protected ?Event $eventModel;
    protected ?Ticket $ticketModel;
    protected const MAX_UPLOAD_FILES = 1;
    protected const UPLOAD_DIR = 'uploads/events/';
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

    public function manage()
    {
        $view = [];

        return $this->render('admin/events/manage', $view);
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
            $this->eventModel->transaction(function() use ($data, $ticketsData) {
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
}
