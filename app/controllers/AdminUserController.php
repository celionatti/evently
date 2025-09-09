<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminUserController extends Controller
{
    protected $user;

    public function onConstruct()
    {
        requireAuth();
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return redirect("/admin");
        }
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin User | Dashboard");
        $this->user = auth();
    }

    public function manage(Request $request, Response $response)
    {
        $users = User::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ]);

        $pagination = new Paginator($users['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'users' => $users['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/users/manage', $view);
    }

    public function create(Request $request, Response $response)
    {
        $view = [
            
        ];

        return $this->render('admin/users/create', $view);
    }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        // Check if user is admin
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $password = $request->input('password');
        $role = $request->input('role', 'guest');

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            FlashMessage::setMessage("All fields are required.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            FlashMessage::setMessage("Invalid email format.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        if (User::findByEmail($email)) {
            FlashMessage::setMessage("Email already exists.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        // Validate role
        $validRoles = ['admin', 'organiser', 'guest'];
        if (!in_array($role, $validRoles)) {
            FlashMessage::setMessage("Invalid role specified.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->role = $role;

            if ($user->save()) {
                FlashMessage::setMessage("User Created Successfully!");
                return $response->redirect("/admin/users/manage");
            }

            throw new \RuntimeException('User creation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("User Creation Failed! Please try again.", "danger");
            return $response->redirect("/admin/users/manage");
        }
    }

    public function delete(Request $request, Response $response, $user_id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        // Check if user is admin
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        $user = User::findByUserId($user_id);

        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        // Prevent user from deleting their own account
        if (isCurrentUser($user)) {
            FlashMessage::setMessage("You cannot delete your own account!", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        try {
            if ($user->delete()) {
                FlashMessage::setMessage("User Deleted!");
                $response->redirect("/admin/users/manage");
                return;
            }

            throw new \RuntimeException('Delete operation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("Delete Failed! Please try again.", "danger");
            return $response->redirect("/admin/users/manage");
        }
    }

    public function role(Request $request, Response $response, $user_id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        // Check if user is admin
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Admin privileges required.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        $user = User::findByUserId($user_id);

        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        // Prevent user from changing their own role
        if (isCurrentUser($user)) {
            FlashMessage::setMessage("You cannot change your own role!", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        $newRole = $request->input('role');

        // Validate new role
        $validRoles = ['admin', 'organiser', 'guest'];
        if (!in_array($newRole, $validRoles)) {
            FlashMessage::setMessage("Invalid role specified.", 'danger');
            return $response->redirect("/admin/users/manage");
        }

        try {
            $user->role = $newRole;
            if ($user->save()) {
                FlashMessage::setMessage("User Role Updated to " . ucfirst($newRole) . "!");
                $response->redirect("/admin/users/manage");
                return;
            }

            throw new \RuntimeException('Role update operation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("Role Update Failed! Please try again.", "danger");
            return $response->redirect("/admin/users/manage");
        }
    }
}