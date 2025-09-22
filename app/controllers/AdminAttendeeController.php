<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Event;
use Trees\Http\Request;
use App\models\Attendee;
use Trees\Http\Response;
use App\controllers\BaseController;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminAttendeeController extends BaseController
{
    public function checkInAttendee(Request $request, Response $response, $id, $event_slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $attendee = Attendee::find($id);
        if (!$attendee) {
            FlashMessage::setMessage("Attendee Not Found!", 'danger');
            return $response->redirect("/admin/events/view/{$event_slug}");
        }

        $event = Event::findBySlug($event_slug);
        if (!$event) {
            FlashMessage::setMessage("Event Not Found!", 'danger');
            return $response->redirect("/admin/events/view/{$event_slug}");
        }

        // Check if organiser is trying to update someone else's event
        if (isOrganiser() && $event->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only update your own events.", 'danger');
            return $response->redirect("/admin/events/view/{$event_slug}");
        }
        try {
            // Check if already checked in
            if ($attendee->status === 'checked') {
                FlashMessage::setMessage("Attendee {$attendee->name} is already checked in.", 'info');
                return $response->redirect("/admin/events/view/{$event_slug}");
            }

            // Use static update method with proper syntax
            $updateData = [
                'status' => 'checked'
            ];

            // This assumes your static update method works with conditions
            $updated = Attendee::updateWhere(['id' => $id], $updateData);

            if ($updated) {
                FlashMessage::setMessage("Attendee {$attendee->name} checked in successfully!", 'success');
            } else {
                FlashMessage::setMessage("Failed to check in attendee.", 'danger');
            }
            return $response->redirect("/admin/events/view/{$event_slug}");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Error checking in attendee: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/events/view/{$event_slug}");
        }
    }
}
