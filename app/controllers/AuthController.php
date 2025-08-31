<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;

class AuthController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('auth');
        $name = "Eventlyy";
        $this->view->setTitle("Authentication | {$name}");
    }

    public function login()
    {
        $view = [

        ];

        return $this->render('auth/login', $view);
    }

    public function signup()
    {
        $view = [

        ];

        return $this->render('auth/signup', $view);
    }

    public function create_user(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'name' => 'required|min:3',
            'other_name' => 'required|min:3',
            'email' => 'required|email|unique:users.email',
            'password' => 'required|password.secure',
            'password_confirmation' => 'required',
            'terms' => 'required'
        ];
    }
}