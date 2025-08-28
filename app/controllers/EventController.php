<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;

class EventController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Events | {$name}");
    }

    public function events()
    {
        $view = [

        ];

        return $this->render('events', $view);
    }

    public function event(Request $request, Response $response, $id)
    {
        $view = [

        ];

        return $this->render('event', $view);
    }
}