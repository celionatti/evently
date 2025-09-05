<?php

declare(strict_types=1);

namespace App\Controllers;

use App\models\Event;
use App\models\Ticket;
use App\models\Transaction;
use App\models\Attendee;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Controller\Controller;
use Exception;

class eventCheckController extends Controller
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

            // Calculate total and validate tickets - FIXED: Use ticket ID instead of slug
            $totalAmount = 0;
            $totalQuantity = 0;
            $selectedTickets = [];

            foreach ($tickets as $ticketId => $quantity) {
                if ($quantity > 0) {
                    // Get ticket details by ID, not slug
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

                    $serviceCharge = $ticket->service_charges ?? 0;
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
            $transactionId = $transactionModel->transaction(function() use ($reference, $eventId, $contact, $totalAmount, $attendees, $selectedTickets) {
                // Create pending transaction
                $transactionData = [
                    'reference_id' => $reference,
                    'user_id' => auth() ? auth()->id : null,
                    'event_id' => $eventId,
                    'email' => $contact['email'],
                    'amount' => $totalAmount,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $transactionId = Transaction::create($transactionData);
                if (!$transactionId) {
                    throw new Exception('Failed to create transaction');
                }

                // Store transaction ticket details (if using the transaction_tickets table)
                foreach ($selectedTickets as $selectedTicket) {
                    // You'll need to create a TransactionTicket model for this
                    // or add this data to session for now
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
                        'ticket_id' => $attendeeData['ticket_id'] ?? null, // Will be updated after payment
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $attendeeId = Attendee::create($attendeeRecord);
                    if (!$attendeeId) {
                        throw new Exception('Failed to create attendee');
                    }
                }

                return $transactionId;
            });

            // Store checkout data in session
            $_SESSION['checkout_data'] = [
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'selected_tickets' => $selectedTickets,
                'contact' => $contact,
                'attendees' => $attendees,
                'total_amount' => $totalAmount
            ];

            return $response->redirect("/checkout/payment/{$reference}");

        } catch (Exception $e) {
            error_log("Checkout Error: " . $e->getMessage());
            FlashMessage::setMessage('An error occurred during checkout. Please try again.', 'danger');
            return $response->redirect("/events/{$eventId}");
        }
    }

    public function paymentPage(Request $request, Response $response, $reference)
    {
        $checkoutData = $_SESSION['checkout_data'] ?? null;

        if (!$checkoutData || $checkoutData['reference'] !== $reference) {
            FlashMessage::setMessage('Invalid checkout session.', 'danger');
            return $response->redirect('/events');
        }

        $transaction = Transaction::where(['reference_id' => $reference]);
        if (empty($transaction)) {
            FlashMessage::setMessage('Transaction not found.', 'danger');
            return $response->redirect('/events');
        }

        $transaction = $transaction[0];
        $event = Event::find($transaction->event_id);

        $view = [
            'transaction' => $transaction,
            'event' => $event,
            'checkoutData' => $checkoutData,
            'paystackPublicKey' => $this->paystackPublicKey,
            'reference' => $reference,
            'amount' => $checkoutData['total_amount'] * 100, // Paystack expects kobo
            'email' => $checkoutData['contact']['email']
        ];

        $this->view->setLayout('payment');
        $this->view->setTitle('Complete Payment | Eventlyy');
        
        return $this->render('payment', $view);
    }

    public function verifyPayment(Request $request, Response $response)
    {
        $reference = $request->input('reference');
        
        if (!$reference) {
            return $response->json(['success' => false, 'message' => 'Reference is required']);
        }

        try {
            // Verify payment with Paystack
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer {$this->paystackSecretKey}",
                    "Cache-Control: no-cache",
                ),
            ));
            
            $paystackResponse = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                throw new Exception("Curl Error: " . $err);
            }

            $result = json_decode($paystackResponse, true);

            if ($result['status'] && $result['data']['status'] === 'success') {
                $this->completeTransaction($reference, $result['data']);
                
                return $response->json([
                    'success' => true, 
                    'message' => 'Payment successful',
                    'redirect_url' => "/checkout/success/{$reference}"
                ]);
            } else {
                return $response->json([
                    'success' => false, 
                    'message' => 'Payment verification failed'
                ]);
            }

        } catch (Exception $e) {
            error_log("Payment Verification Error: " . $e->getMessage());
            return $response->json([
                'success' => false, 
                'message' => 'Payment verification failed'
            ]);
        }
    }

    private function completeTransaction($reference, $paystackData)
    {
        $checkoutData = $_SESSION['checkout_data'] ?? null;
        if (!$checkoutData) {
            throw new Exception('Checkout data not found');
        }

        $transactionModel = new Transaction();
        $transactionModel->transaction(function() use ($reference, $paystackData, $checkoutData) {
            // Find transaction
            $transactions = Transaction::where(['reference_id' => $reference]);
            if (empty($transactions)) {
                throw new Exception('Transaction not found');
            }

            $transaction = $transactions[0];
            
            // Update transaction
            $updateResult = Transaction::update($transaction->id, [
                'status' => 'confirmed', // Fixed typo
                'transaction_id' => $paystackData['id'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$updateResult) {
                throw new Exception('Failed to update transaction');
            }

            // Update ticket quantities and assign tickets to attendees
            $attendees = Attendee::where(['transaction_id' => $transaction->id]);
            $attendeeIndex = 0;

            foreach ($checkoutData['selected_tickets'] as $selectedTicket) {
                $ticket = $selectedTicket['ticket'];
                $quantity = $selectedTicket['quantity'];

                // Update sold count
                $newSoldCount = ($ticket->sold ?? 0) + $quantity;
                $updateTicketResult = Ticket::update($ticket->id, ['sold' => $newSoldCount]);
                
                if (!$updateTicketResult) {
                    throw new Exception('Failed to update ticket quantity');
                }

                // Assign ticket_id to attendees and generate ticket codes
                for ($i = 0; $i < $quantity; $i++) {
                    if (isset($attendees[$attendeeIndex])) {
                        $ticketCode = $this->generateTicketCode($transaction->id, $ticket->id);
                        
                        $updateAttendeeResult = Attendee::update($attendees[$attendeeIndex]->id, [
                            'ticket_id' => $ticket->id,
                            'ticket_code' => $ticketCode,
                            'status' => 'confirmed'
                        ]);
                        
                        if (!$updateAttendeeResult) {
                            throw new Exception('Failed to update attendee');
                        }
                        $attendeeIndex++;
                    }
                }
            }

            return true;
        });

        // Send confirmation email
        $this->sendConfirmationEmail($reference, $checkoutData);

        // Clear checkout data
        unset($_SESSION['checkout_data']);
    }

    private function generateTicketCode($transactionId, $ticketId)
    {
        return 'TKT-' . strtoupper(substr(uniqid(), -6)) . '-' . $transactionId;
    }

    public function successPage(Request $request, Response $response, $reference)
    {
        $transactions = Transaction::where(['reference_id' => $reference]);
        if (empty($transactions) || $transactions[0]->status !== 'confirmed') {
            FlashMessage::setMessage('Transaction not found or not completed.', 'danger');
            return $response->redirect('/events');
        }

        $transaction = $transactions[0];
        $event = Event::find($transaction->event_id);
        $attendees = Attendee::where(['transaction_id' => $transaction->id]);

        $view = [
            'transaction' => $transaction,
            'event' => $event,
            'attendees' => $attendees
        ];

        $this->view->setTitle('Payment Successful | Eventlyy');
        
        return $this->render('checkout-success', $view);
    }

    private function sendConfirmationEmail($reference, $checkoutData)
    {
        try {
            $transactions = Transaction::where(['reference_id' => $reference]);
            if (empty($transactions)) return;
            
            $transaction = $transactions[0];
            $event = Event::find($transaction->event_id);
            $attendees = Attendee::where(['transaction_id' => $transaction->id]);
            
            $subject = "Ticket Confirmation - {$event->event_title}";
            $to = $transaction->email;
            
            // Implement your email logic here
            // You might want to use a proper email template and service
            
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
        }
    }
}