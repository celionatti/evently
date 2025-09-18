<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use Trees\Helper\Countries\Countries;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminProfileController extends Controller
{
    protected ?User $userModel;

    public function onConstruct()
    {
        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $this->view->setLayout('admin');
        $this->userModel = new User();
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Profile");
    }

    public function profile(Request $request, Response $response)
    {
        $user = auth();

        if (!$user) {
            FlashMessage::setMessage("User not found.", 'danger');
            return $response->redirect("/admin/dashboard");
        }

        $countries = Countries::getCountries(['NG', 'KE', 'ZA', 'GH']);
        $countriesOptions = [];
        foreach ($countries as $code => $country) {
            $countriesOptions[$code] = ucfirst($country['name']);
        }

        $view = [
            'user' => $user,
            'countries' => $countriesOptions
        ];

        $this->render('admin/profile/profile', $view);
    }

    public function update(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $user = User::findByUserId(auth()->user_id);

        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/profile");
        }

        $rules = [
            'name' => 'required|min:2|max:50',
            'other_name' => 'required|min:2|max:50',
            // 'email' => "required|email|unique:users.email, email!={$user->email}",
            'phone' => 'required',
            'country' => 'required',
            'address' => 'required',
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/profile");
        }
        try {
            $data = $request->all();
            if ($user->updateInstance($data)) {
                FlashMessage::setMessage("Profile Updated!");
                return $response->redirect("/admin/profile");
            }
            throw new \RuntimeException('Update operation failed');
        } catch(\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/profile");
        }
    }
}
