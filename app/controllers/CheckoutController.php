<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use App\models\Attendee;
use Trees\Http\Response;
use App\models\Transaction;
use Trees\Controller\Controller;
use App\models\TransactionTicket;
use Trees\Exception\TreesException;
use Trees\Helper\FlashMessages\FlashMessage;

class CheckoutController extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;

    public function __construct()
    {
        $this->paystackSecretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? 'sk_test_your_secret_key';
        $this->paystackPublicKey = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'pk_test_your_public_key';
    }

    public function processCheckout(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $eventId = $request->input('event_id');
        $eventSlug = $request->input('event_slug');

        try {
            $tickets = $request->input('tickets', []);
            $contact = $request->input('contact', []);
            $attendees = $request->input('attendees', []);

            // Validate required fields
            if (!$eventId || empty($contact['name']) || empty($contact['email'])) {
                FlashMessage::setMessage('Please fill in all required fields.', 'danger');
                return $response->redirect("/events/{$eventId}/{$eventSlug}");
            }

            // Get event details
            $event = Event::find($eventId);
            if (!$event) {
                FlashMessage::setMessage('Event not found.', 'danger');
                return $response->redirect('/events');
            }

            // Calculate total and validate tickets
            $totalAmount = 0;
            $totalQuantity = 0;
            $selectedTickets = [];

            foreach ($tickets as $ticketId => $quantity) {
                if ($quantity > 0) {
                    // Get ticket details by ID
                    $ticket = Ticket::find($ticketId);
                    if (!$ticket || $ticket->event_id != $eventId) {
                        FlashMessage::setMessage('Invalid ticket selected.', 'danger');
                        return $response->redirect("/events/{$eventId}/{$eventSlug}");
                    }

                    // Check availability
                    $available = $ticket->quantity - ($ticket->sold ?? 0);
                    if ($quantity > $available) {
                        FlashMessage::setMessage("Only {$available} tickets available for {$ticket->ticket_name}.", 'danger');
                        return $response->redirect("/events/{$eventId}/{$eventSlug}");
                    }

                    // Check max per person limit
                    $maxPerPerson = $ticket->max_per_person ?? 10;
                    if ($quantity > $maxPerPerson) {
                        FlashMessage::setMessage("Maximum {$maxPerPerson} tickets allowed per person for {$ticket->ticket_name}.", 'danger');
                        return $response->redirect("/events/{$eventId}/{$eventSlug}");
                    }

                    $serviceCharge = $ticket->charges ?? 0;
                    $ticketAmount = ($ticket->price + $serviceCharge) * $quantity;
                    $totalAmount += $ticketAmount;
                    $totalQuantity += $quantity;

                    $selectedTickets[] = [
                        'ticket' => $ticket,
                        'quantity' => $quantity,
                        'amount' => $ticketAmount,
                        'service_charge' => $serviceCharge
                    ];
                }
            }

            if ($totalQuantity === 0) {
                FlashMessage::setMessage('Please select at least one ticket.', 'danger');
                return $response->redirect("/events/{$eventId}/{$eventSlug}");
            }

            // Validate attendee count matches ticket quantity
            if (count($attendees) !== $totalQuantity) {
                FlashMessage::setMessage('Attendee information does not match ticket quantity.', 'danger');
                return $response->redirect("/events/{$eventId}/{$eventSlug}");
            }

            // Generate unique reference
            $reference = 'EVT_' . time() . '_' . uniqid();

            // Create transaction and attendees
            $transactionModel = new Transaction();

            $transactionId = $transactionModel->transaction(function () use ($reference, $eventId, $contact, $totalAmount, $attendees, $selectedTickets) {
                // Create pending transaction
                $transactionData = [
                    'transaction_id' => 'TRAN_' . uniqid(),
                    'reference_id' => $reference,
                    'user_id' => auth() ? auth()->id : null,
                    'event_id' => $eventId,
                    'email' => $contact['email'],
                    'amount' => $totalAmount,
                    'status' => 'pending'
                ];

                $transactionId = Transaction::create($transactionData);
                if (!$transactionId) {
                    throw new Exception('Failed to create transaction');
                }

                // Store transaction ticket details
                foreach ($selectedTickets as $selectedTicket) {
                    $ticket = $selectedTicket['ticket'];
                    $transactionTicketData = [
                        'transaction_id' => $transactionId,
                        'ticket_id' => (string)$ticket->id,
                        'quantity' => $selectedTicket['quantity'],
                        'price' => $ticket->price,
                        'service_charge' => $selectedTicket['service_charge']
                    ];

                    // $transactionTicketId = TransactionTicket::create($transactionTicketData);
                    // if (!$transactionTicketId) {
                    //     throw new Exception('Failed to create transaction ticket record');
                    // }
                    $transactionTicket = new TransactionTicket();
                    $transactionTicket->fill($transactionTicketData);
                    $success = $transactionTicket->save();

                    if (!$success) {
                        throw new Exception('Failed to create transaction ticket record');
                    }
                }

                // Store attendee information
                foreach ($attendees as $attendeeData) {
                    $attendeeRecord = [
                        'user_id' => auth() ? auth()->id : null,
                        'event_id' => $eventId,
                        'transaction_id' => $transactionId,
                        'name' => $attendeeData['name'],
                        'email' => $attendeeData['email'],
                        'phone' => $contact['phone'] ?? null,
                        'ticket_id' => $attendeeData['ticket_id'] ?? null,
                        'ticket_code' => null,
                        'status' => 'pending'
                    ];

                    $attendeeId = Attendee::create($attendeeRecord);
                    if (!$attendeeId) {
                        throw new Exception('Failed to create attendee');
                    }
                }
                return $transactionId;
            });
            // Store checkout data in session
            session()->setArray([
                'checkout_data' => [
                    'transaction_id' => $transactionId,
                    'reference' => $reference,
                    'selected_tickets' => $selectedTickets,
                    'contact' => $contact,
                    'attendees' => $attendees,
                    'total_amount' => $totalAmount,
                    'event_id' => $eventId
                ]
            ]);
            return $response->redirect("/checkout/payment/{$reference}");
        } catch (TreesException $e) {
            FlashMessage::setMessage('An error occurred during checkout. Please try again.', 'danger');
            return $response->redirect("/events/{$eventId}/{$eventSlug}");
        }
    }

    public function paymentPage(Request $request, Response $response, $reference)
    {
        dd($reference);
        $reference = $reference['reference'] ?? null;
        if (!$reference) {
            FlashMessage::setMessage('Invalid payment reference.', 'danger');
            return $response->redirect('/events');
        }

        $checkoutData = session()->get('checkout_data', []);
        if (empty($checkoutData) || $checkoutData['reference'] !== $reference) {
            FlashMessage::setMessage('No checkout data found. Please start the checkout process again.', 'danger');
            return $response->redirect('/events');
        }

        // Get transaction details
        $transaction = Transaction::where(['id' => $checkoutData['transaction_id'], 'reference_id' => $reference]);
        if (empty($transaction)) {
            FlashMessage::setMessage('Transaction not found. Please start the checkout process again.', 'danger');
            return $response->redirect('/events');
        }
        $transaction = array_shift($transaction);

        // Get event details
        $event = Event::find($checkoutData['event_id']);
        if (!$event) {
            FlashMessage::setMessage('Event not found.', 'danger');
            return $response->redirect('/events');
        }

        // Prepare data for the payment page
        $data = [
            'event' => $event,
            'transaction' => $transaction,
            'selected_tickets' => $checkoutData['selected_tickets'],
            'contact' => $checkoutData['contact'],
            'attendees' => $checkoutData['attendees'],
            'total_amount' => $checkoutData['total_amount'],
            'paystack_public_key' => $this->paystackPublicKey,
            'reference' => $reference
        ];

        return $this->render('checkout/payment', $data);
    }
}
