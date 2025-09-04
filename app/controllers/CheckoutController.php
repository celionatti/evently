<?php

declare(strict_types=1);

namespace App\Controllers;

use Trees\Exception\TreesException;
use Trees\Http\Request;
use Trees\Http\Response;

class CheckoutController
{
    private $paystackSecretKey;
    private $paystackPublicKey;

    public function onConstruct()
    {
        $this->paystackSecretKey = env('PAYSTACK_SECRET_KEY') ?? 'sk_test_your_secret_key';
        $this->paystackPublicKey = env('PAYSTACK_PUBLIC_KEY') ?? 'pk_test_your_public_key';
    }

    public function process_checkout(Request $request, Response $response)
    {
        $eventId = $request->input('event_id');

        try {
            // Get form data
            $tickets = $request->input('tickets', []);
            $contact = $request->input('contact', []);
            $attendees = $request->input('attendees', []);

            dd($eventId, $tickets, $contact, $attendees);
        } catch (TreesException $e) {
            //throw $th;
        }
    }
}