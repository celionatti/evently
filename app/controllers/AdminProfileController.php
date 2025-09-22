<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use App\controllers\BaseController;
use Trees\Exception\TreesException;
use Trees\Helper\Countries\Countries;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminProfileController extends BaseController
{
    protected ?User $userModel;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->view->setLayout('admin');

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");

        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $this->userModel = new User();
    }

    public function profile(Request $request, Response $response)
    {
        $user = auth();
        if (!$user) {
            FlashMessage::setMessage("User not found.", 'danger');
            return $response->redirect("/admin/dashboard");
        }

        // Get user statistics - count their events and attendees
        $userStats = $this->getUserStatistics($user->id);
        $user->events = $userStats['events'];
        $user->attendees = $userStats['attendees'];
        $user->rating = $userStats['rating'];

        $countries = Countries::getCountries(['NG', 'KE', 'ZA', 'GH', 'US', 'GB', 'CA', 'FR']);
        $countriesOptions = [];
        foreach ($countries as $code => $country) {
            $countriesOptions[] = ucfirst($country['name']);
        }

        // Sort countries alphabetically
        sort($countriesOptions);

        $this->view->setTitle("Eventlyy | Profile - {$user->name} {$user->other_name}");

        $view = [
            'user' => $user,
            'countries' => $countriesOptions
        ];

        return $this->render('admin/profile/profile', $view);
    }

    public function update(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/profile");
        }

        $user = User::findByUserId(auth()->user_id);
        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/profile");
        }

        $rules = [
            'name' => 'required|min:2|max:50',
            'other_name' => 'required|min:2|max:50',
            'phone' => 'required|min:10|max:20',
            'bio' => 'max:500',
            'business_name' => 'max:100',
            'website' => 'url|max:255',
            'address' => 'required|max:100',
            'country' => 'required|max:100'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/profile");
        }

        try {
            $data = $request->only([
                'name',
                'other_name',
                'phone',
                'bio',
                'business_name',
                'website',
                'address',
                'country'
            ]);

            // Remove empty website field to avoid validation issues
            if (empty($data['website'])) {
                unset($data['website']);
            }

            if ($user->updateInstance($data)) {
                FlashMessage::setMessage("Profile Updated Successfully!");
                return $response->redirect("/admin/profile");
            }

            throw new \RuntimeException('Update operation failed');
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/profile");
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/profile");
        }
    }

    public function changePassword(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/profile");
        }

        $user = User::findByUserId(auth()->user_id);
        if (!$user) {
            FlashMessage::setMessage("User Not Found!", 'danger');
            return $response->redirect("/admin/profile");
        }

        $rules = [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6|max:50',
            'confirm_password' => 'required|same:new_password'
        ];

        if (!$request->validate($rules, false)) {
            set_form_error($request->getErrors());
            return $response->redirect("/admin/profile");
        }

        try {
            $currentPassword = $request->input('current_password');
            $newPassword = $request->input('new_password');

            // Verify current password
            if (!password_verify($currentPassword, $user->password)) {
                FlashMessage::setMessage("Current password is incorrect.", 'danger');
                return $response->redirect("/admin/profile");
            }

            // Check if new password is different from current
            if (password_verify($newPassword, $user->password)) {
                FlashMessage::setMessage("New password must be different from current password.", 'danger');
                return $response->redirect("/admin/profile");
            }

            // Hash and update new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateData = [
                'password' => $hashedPassword,
                'remember_token' => null // Clear remember token to force re-login on other devices
            ];

            if ($user->updateInstance($updateData)) {
                FlashMessage::setMessage("Password changed successfully! Please login again for security.", 'success');

                // Optional: You might want to logout the user and redirect to login
                // logout();
                // return $response->redirect("/admin/login");

                return $response->redirect("/admin/profile");
            }

            throw new \RuntimeException('Password update operation failed');
        } catch (TreesException $e) {
            FlashMessage::setMessage("Password change failed! Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/profile");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Password change failed! Please try again.", "danger");
            return $response->redirect("/admin/profile");
        }
    }

    /**
     * Get user statistics (events, attendees, rating)
     */
    private function getUserStatistics($userId): array
    {
        try {
            // You'll need to import these models or use direct queries
            // For now, using direct database queries as an example

            $eventsCount = 0;
            $attendeesCount = 0;
            $rating = 0;

            // If you have Event model available
            // $eventsCount = Event::count(['user_id' => $userId]);

            // If you have direct database access
            // $db = Database::getInstance();
            // $eventsCount = $db->query("SELECT COUNT(*) as count FROM events WHERE user_id = ?", [$userId])->fetchColumn();

            // For attendees count, you might need to join events and attendees tables
            // $attendeesQuery = "SELECT COUNT(a.id) as count FROM attendees a 
            //                   JOIN events e ON a.event_id = e.id 
            //                   WHERE e.user_id = ? AND a.status = 'confirmed'";
            // $attendeesCount = $db->query($attendeesQuery, [$userId])->fetchColumn();

            // For rating, you might calculate average from event ratings or reviews
            // $rating = 4.5; // Example rating

            return [
                'events' => $eventsCount,
                'attendees' => $attendeesCount,
                'rating' => $rating
            ];
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'events' => 0,
                'attendees' => 0,
                'rating' => 0
            ];
        }
    }

    public function __destruct()
    {
        $this->userModel = null;
    }
}
