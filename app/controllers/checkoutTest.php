<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use App\models\Event;
use App\models\Ticket;
use Trees\Http\Request;
use App\models\Attendee;
use Trees\Http\Response;
use Endroid\QrCode\QrCode;
use App\models\Transaction;
use App\services\PDFGenerator;
use Trees\Controller\Controller;
use App\models\TransactionTicket;
use Trees\Exception\TreesException;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Trees\Helper\Utils\CodeGenerator;
use Endroid\QrCode\ErrorCorrectionLevel;
use Trees\Helper\FlashMessages\FlashMessage;

class CheckoutTest extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;
    private $codeGenerator;
    private $pdfGenerator;

    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} | Check Out");

        $this->paystackSecretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? 'sk_test_your_secret_key';
        $this->paystackPublicKey = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'pk_test_your_public_key';
        $this->pdfGenerator = new PDFGenerator();

        // Initialize CodeGenerator with uniqueness checker
        $this->codeGenerator = new CodeGenerator(
            function ($code) {
                // Check if ticket code already exists in database
                return !empty(Attendee::where(['ticket_code' => $code]));
            },
            'EVT', // Prefix
            '-'    // Separator
        );
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
        if (!$reference) {
            FlashMessage::setMessage('Invalid payment reference.', 'danger');
            session()->remove('checkout_data');
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
            session()->remove('checkout_data');
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
        $view = [
            'event' => $event,
            'transaction' => $transaction,
            'selected_tickets' => $checkoutData['selected_tickets'],
            'contact' => $checkoutData['contact'],
            'attendees' => $checkoutData['attendees'],
            'total_amount' => $checkoutData['total_amount'],
            'paystackPublicKey' => $this->paystackPublicKey,
            'reference' => $reference
        ];

        return $this->render('checkout/payment', $view);
    }

    public function processPayment(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $reference = $request->input('reference');
        $email = $request->input('email');
        $amount = $request->input('amount');
        $eventId = $request->input('event_id');
        $transactionId = $request->input('transaction_id');

        if (!$reference || !$email || !$amount) {
            FlashMessage::setMessage('Invalid payment parameters.', 'danger');
            return $response->redirect('/events');
        }

        // Get checkout data from session
        $checkoutData = session()->get('checkout_data', []);
        if (empty($checkoutData) || $checkoutData['reference'] !== $reference) {
            FlashMessage::setMessage('No checkout data found. Please start the checkout process again.', 'danger');
            return $response->redirect('/events');
        }

        // Get event details for metadata
        $event = Event::find($eventId);
        if (!$event) {
            FlashMessage::setMessage('Event not found.', 'danger');
            return $response->redirect('/events');
        }

        // Build Paystack payment URL
        $paystackUrl = "https://api.paystack.co/transaction/initialize";

        // Get the base URL properly
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host;

        $fields = [
            'email' => $email,
            'amount' => $amount,
            'reference' => $reference,
            'currency' => 'NGN',
            // Fix: Ensure proper callback URL
            'callback_url' => $baseUrl . '/checkout/verify-payment?reference=' . $reference,
            'metadata' => json_encode([
                'event_id' => $eventId,
                'transaction_id' => $transactionId,
                'custom_fields' => [
                    [
                        'display_name' => "Event",
                        'variable_name' => "event",
                        'value' => $event->event_title
                    ]
                ]
            ])
        ];

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paystackUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->paystackSecretKey,
            "Cache-Control: no-cache",
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            FlashMessage::setMessage('Payment initialization failed. Please try again.', 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }

        $responseData = json_decode($result, true);

        if (!$responseData['status']) {
            FlashMessage::setMessage('Payment initialization failed: ' . $responseData['message'], 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }

        // Redirect to Paystack payment page
        return $response->redirect($responseData['data']['authorization_url']);
    }

    public function verifyPayment(Request $request, Response $response)
    {
        // Get reference from query parameters (Paystack callback) or input
        $reference = $request->input('reference') ?? $request->query('reference');

        if (!$reference) {
            FlashMessage::setMessage('Invalid payment reference.', 'danger');
            return $response->redirect('/events');
        }

        $transactions = Transaction::where(['reference_id' => $reference]);
        if (empty($transactions)) {
            FlashMessage::setMessage('Transaction not found. Please start the checkout process again.', 'danger');
            return $response->redirect('/events');
        }
        $transaction = array_shift($transactions);

        // Check if transaction is already processed
        if ($transaction->status === 'confirmed' || $transaction->status === 'success') {
            // Already processed, redirect to success page
            session()->remove('checkout_data');
            FlashMessage::setMessage('Payment already confirmed!', 'success');
            return $response->redirect("/checkout/success/{$reference}");
        }

        // Get checkout data from session (if available) or reconstruct from database
        $checkoutData = session()->get('checkout_data', []);

        // If session data is missing, reconstruct from database
        if (empty($checkoutData)) {
            // Get transaction tickets to calculate total
            $transactionTickets = TransactionTicket::where(['transaction_id' => $transaction->id]);

            if (empty($transactionTickets)) {
                FlashMessage::setMessage('Transaction details not found.', 'danger');
                return $response->redirect('/events');
            }

            // Calculate total amount from transaction tickets
            $totalAmount = 0;
            foreach ($transactionTickets as $transactionTicket) {
                $totalAmount += ($transactionTicket->price + $transactionTicket->service_charge) * $transactionTicket->quantity;
            }

            // Reconstruct minimal checkout data
            $checkoutData = [
                'transaction_id' => $transaction->id,
                'reference' => $reference,
                'total_amount' => $totalAmount,
                'event_id' => $transaction->event_id
            ];
        }

        // Verify payment with Paystack
        $paystackSecretKey = $this->paystackSecretKey;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$paystackSecretKey}",
                "Cache-Control: no-cache",
            ],
        ]);

        $responseCurl = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            FlashMessage::setMessage('Payment verification failed. Please contact support.', 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }

        $result = json_decode($responseCurl, true);
        if (!$result['status']) {
            FlashMessage::setMessage('Payment verification failed. Please contact support.', 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }

        $paymentData = $result['data'];

        if ($paymentData['status'] !== 'success' || (int)$paymentData['amount'] !== (int)($checkoutData['total_amount'] * 100)) {
            FlashMessage::setMessage('Payment verification failed or amount mismatch. Please contact support.', 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }

        try {
            // Use database transaction for atomicity
            $transactionModel = new Transaction();
            $transactionModel->transaction(function () use ($transaction) {
                $updateData = [
                    'status' => 'confirmed',
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                Transaction::updateWhere(['id' => $transaction->id], $updateData);

                // Get transaction tickets to update sold quantities
                $transactionTickets = TransactionTicket::where(['transaction_id' => $transaction->id]);

                // Update ticket quantities

                foreach ($transactionTickets as $transactionTicket) {
                    $ticketId = $transactionTicket->ticket_id;

                    // Get ticket instance and update
                    $ticket = Ticket::find($ticketId);
                    if ($ticket) {
                        $newSold = $ticket->sold + $transactionTicket->quantity;

                        // Use instance update method
                        $ticket->sold = $newSold;
                        $ticket->save();
                    }
                }

                // Update attendees status and generate ticket codes
                $attendees = Attendee::where(['transaction_id' => $transaction->id]);

                // Clear the code generator cache to ensure fresh codes for this transaction
                $this->codeGenerator->clearCache();

                foreach ($attendees as $attendee) {
                    // Generate unique ticket code using the CodeGenerator
                    $ticketCode = $this->codeGenerator->generate(
                        $transaction->event_id,
                        $attendee->id,
                        CodeGenerator::FORMAT_STANDARD,
                        [
                            'id1_length' => 4,    // Event ID length
                            'id2_length' => 6,    // Attendee ID length
                            'random_length' => 4, // Random part length
                            'prefix' => 'TKT',    // Custom prefix for tickets
                        ]
                    );

                    // Update each attendee individually with their unique ticket code
                    Attendee::updateWhere(
                        ['id' => $attendee->id],
                        [
                            'status' => 'confirmed',
                            'ticket_code' => $ticketCode,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    );
                }
            });

            // Clear checkout session data if it exists
            session()->remove('checkout_data');

            // Redirect to success page
            FlashMessage::setMessage('Payment successful! Your tickets have been confirmed.', 'success');
            return $response->redirect("/checkout/success/{$reference}");
        } catch (Exception $e) {
            // Log the error
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }

            FlashMessage::setMessage('An error occurred while processing your payment. Please contact support.', 'danger');
            return $response->redirect("/checkout/payment/{$reference}");
        }
    }

    // public function verifyPayment(Request $request, Response $response)
    // {
    //     // Get reference from query parameters (Paystack callback) or input
    //     $reference = $request->input('reference') ?? $request->query('reference');

    //     if (!$reference) {
    //         FlashMessage::setMessage('Invalid payment reference.', 'danger');
    //         return $response->redirect('/events');
    //     }

    //     $transactions = Transaction::where(['reference_id' => $reference]);
    //     if (empty($transactions)) {
    //         FlashMessage::setMessage('Transaction not found. Please start the checkout process again.', 'danger');
    //         return $response->redirect('/events');
    //     }
    //     $transaction = array_shift($transactions);

    //     // Check if transaction is already processed
    //     if ($transaction->status === 'confirmed' || $transaction->status === 'success') {
    //         // Already processed, redirect to success page
    //         session()->remove('checkout_data');
    //         FlashMessage::setMessage('Payment already confirmed!', 'success');
    //         return $response->redirect("/checkout/success/{$reference}");
    //     }

    //     // Get checkout data from session (if available) or reconstruct from database
    //     $checkoutData = session()->get('checkout_data', []);

    //     // If session data is missing, reconstruct from database
    //     if (empty($checkoutData)) {
    //         // Get transaction tickets to calculate total
    //         $transactionTickets = TransactionTicket::where(['transaction_id' => $transaction->id]);

    //         if (empty($transactionTickets)) {
    //             FlashMessage::setMessage('Transaction details not found.', 'danger');
    //             return $response->redirect('/events');
    //         }

    //         // Calculate total amount from transaction tickets
    //         $totalAmount = 0;
    //         foreach ($transactionTickets as $transactionTicket) {
    //             $totalAmount += ($transactionTicket->price + $transactionTicket->service_charge) * $transactionTicket->quantity;
    //         }

    //         // Reconstruct minimal checkout data
    //         $checkoutData = [
    //             'transaction_id' => $transaction->id,
    //             'reference' => $reference,
    //             'total_amount' => $totalAmount,
    //             'event_id' => $transaction->event_id
    //         ];
    //     }

    //     // Verify payment with Paystack
    //     $paystackSecretKey = $this->paystackSecretKey;
    //     $curl = curl_init();

    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER => [
    //             "Authorization: Bearer {$paystackSecretKey}",
    //             "Cache-Control: no-cache",
    //         ],
    //     ]);

    //     $responseCurl = curl_exec($curl);
    //     $err = curl_error($curl);
    //     curl_close($curl);

    //     if ($err) {
    //         FlashMessage::setMessage('Payment verification failed. Please contact support.', 'danger');
    //         return $response->redirect("/checkout/payment/{$reference}");
    //     }

    //     $result = json_decode($responseCurl, true);
    //     if (!$result['status']) {
    //         FlashMessage::setMessage('Payment verification failed. Please contact support.', 'danger');
    //         return $response->redirect("/checkout/payment/{$reference}");
    //     }

    //     $paymentData = $result['data'];

    //     if ($paymentData['status'] !== 'success' || (int)$paymentData['amount'] !== (int)($checkoutData['total_amount'] * 100)) {
    //         FlashMessage::setMessage('Payment verification failed or amount mismatch. Please contact support.', 'danger');
    //         return $response->redirect("/checkout/payment/{$reference}");
    //     }

    //     try {
    //         // Update transaction status
    //         $updateData = [
    //             'status' => 'confirmed',
    //             'updated_at' => date('Y-m-d H:i:s')
    //         ];

    //         Transaction::updateWhere(['id' => $transaction->id], $updateData);

    //         // Get transaction tickets to update sold quantities
    //         $transactionTickets = TransactionTicket::where(['transaction_id' => $transaction->id]);

    //         // Update ticket quantities
    //         foreach ($transactionTickets as $transactionTicket) {
    //             $ticketId = $transactionTicket->ticket_id;

    //             // Get ticket instance and update
    //             $ticket = Ticket::find($ticketId);
    //             if ($ticket) {
    //                 $newSold = $ticket->sold + $transactionTicket->quantity;

    //                 // Use instance update method
    //                 $ticket->sold = $newSold;
    //                 $ticket->save();
    //             }
    //         }

    //         // Update attendees status and generate ticket codes
    //         $attendees = Attendee::where(['transaction_id' => $transaction->id]);

    //         // Clear the code generator cache to ensure fresh codes for this transaction
    //         $this->codeGenerator->clearCache();

    //         foreach ($attendees as $attendee) {
    //             // Generate unique ticket code using the CodeGenerator
    //             $ticketCode = $this->codeGenerator->generate(
    //                 $transaction->event_id,
    //                 $attendee->id,
    //                 CodeGenerator::FORMAT_STANDARD,
    //                 [
    //                     'id1_length' => 4,    // Event ID length
    //                     'id2_length' => 6,    // Attendee ID length
    //                     'random_length' => 4, // Random part length
    //                     'prefix' => 'TKT',    // Custom prefix for tickets
    //                 ]
    //             );

    //             // Update each attendee individually with their unique ticket code
    //             Attendee::updateWhere(
    //                 ['id' => $attendee->id],
    //                 [
    //                     'status' => 'confirmed',
    //                     'ticket_code' => $ticketCode,
    //                     'updated_at' => date('Y-m-d H:i:s')
    //                 ]
    //             );
    //         }

    //         // Clear checkout session data if it exists
    //         session()->remove('checkout_data');

    //         // Redirect to success page
    //         FlashMessage::setMessage('Payment successful! Your tickets have been confirmed.', 'success');
    //         return $response->redirect("/checkout/success/{$reference}");
    //     } catch (Exception $e) {
    //         // Log the error
    //         if (class_exists('\Trees\Logger\Logger')) {
    //             \Trees\Logger\Logger::exception($e);
    //         }

    //         FlashMessage::setMessage('An error occurred while processing your payment. Please contact support.', 'danger');
    //         return $response->redirect("/checkout/payment/{$reference}");
    //     }
    // }

    public function successPage(Request $request, Response $response, $reference)
    {
        // Get transaction details
        $transaction = Transaction::where(['reference_id' => $reference]);
        if (empty($transaction)) {
            FlashMessage::setMessage('Transaction not found.', 'danger');
            return $response->redirect('/events');
        }
        $transaction = array_shift($transaction);

        // Get event details
        $event = Event::find($transaction->event_id);
        if (!$event) {
            FlashMessage::setMessage('Event not found.', 'danger');
            return $response->redirect('/events');
        }

        // Get attendees
        $attendees = Attendee::where(['transaction_id' => $transaction->id]);

        $view = [
            'event' => $event,
            'transaction' => $transaction,
            'attendees' => $attendees
        ];

        return $this->render('checkout/success', $view);
    }

    /**
     * Download individual ticket as PDF
     */
    public function downloadTicket(Request $request, Response $response, $attendeeId, $reference)
    {
        try {
            // Verify attendee exists and belongs to this transaction
            $attendee = Attendee::find($attendeeId);
            if (!$attendee) {
                FlashMessage::setMessage('Ticket not found.', 'danger');
                return $response->redirect('/events');
            }

            // Get transaction to verify reference
            $transaction = Transaction::where(['reference_id' => $reference]);
            if (empty($transaction)) {
                FlashMessage::setMessage('Invalid transaction reference.', 'danger');
                return $response->redirect('/events');
            }
            $transaction = array_shift($transaction);

            // Verify attendee belongs to this transaction
            if ($attendee->transaction_id != $transaction->id) {
                FlashMessage::setMessage('Invalid ticket access.', 'danger');
                return $response->redirect('/events');
            }

            // Get event details
            $event = Event::find($attendee->event_id);
            if (!$event) {
                FlashMessage::setMessage('Event not found.', 'danger');
                return $response->redirect('/events');
            }

            // Get ticket details
            $ticket = null;
            if ($attendee->ticket_id) {
                $ticket = Ticket::find($attendee->ticket_id);
            }

            // Prepare ticket data for PDF generation
            $ticketData = [
                'event' => $event,
                'attendee' => $attendee,
                'transaction' => $transaction,
                'ticket' => $ticket
            ];

            // Generate PDF using enhanced PdfGenerator service
            $pdf = $this->pdfGenerator->generateSimpleTicketPdf($ticketData);

            $filename = 'ticket_' . $attendee->ticket_code . '.pdf';
            $this->pdfGenerator->outputPdfForDownload($pdf, $filename);
        } catch (Exception $e) {
            FlashMessage::setMessage('Error generating ticket PDF. Please try again.', 'danger');
            return $response->redirect("/checkout/success/{$reference}");
        }
    }

    /**
     * Bulk download all tickets for a transaction as ZIP
     */
    public function downloadAllTickets(Request $request, Response $response, $reference)
    {
        try {
            // Get transaction
            $transaction = Transaction::where(['reference_id' => $reference]);
            if (empty($transaction)) {
                FlashMessage::setMessage('Transaction not found.', 'danger');
                return $response->redirect('/events');
            }
            $transaction = array_shift($transaction);

            // Get all attendees for this transaction
            $attendees = Attendee::where(['transaction_id' => $transaction->id]);
            if (empty($attendees)) {
                FlashMessage::setMessage('No tickets found.', 'danger');
                return $response->redirect('/events');
            }

            // Get event details
            $event = Event::find($transaction->event_id);
            if (!$event) {
                FlashMessage::setMessage('Event not found.', 'danger');
                return $response->redirect('/events');
            }

            // Create ZIP archive
            $zip = new \ZipArchive();
            $zipFilename = tempnam(sys_get_temp_dir(), 'tickets_') . '.zip';

            if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
                throw new Exception('Could not create ZIP file');
            }

            // Generate PDF for each attendee and add to ZIP
            foreach ($attendees as $attendee) {
                $ticket = null;
                if ($attendee->ticket_id) {
                    $ticket = Ticket::find($attendee->ticket_id);
                }

                $ticketData = [
                    'event' => $event,
                    'attendee' => $attendee,
                    'transaction' => $transaction,
                    'ticket' => $ticket
                ];

                $pdf = $this->pdfGenerator->generateSimpleTicketPdf($ticketData);
                $pdfContent = $pdf->Output('', 'S');

                $filename = 'ticket-' . $attendee->ticket_code . '.pdf';
                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            // Set headers for ZIP download
            $downloadFilename = 'tickets-' . $reference . '.zip';

            // $response->setHeader('Content-Type', 'application/zip');
            // $response->setHeader('Content-Disposition', 'attachment; filename="' . $downloadFilename . '"');
            // $response->setHeader('Content-Length', (string)filesize($zipFilename));
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
            header('Content-Length: ' . filesize($zipFilename));

            // Clear any existing buffer to avoid corruption
            if (ob_get_length()) {
                ob_end_clean();
            }

            // Output ZIP content
            readfile($zipFilename);

            // Clean up temporary file
            unlink($zipFilename);
            exit;
        } catch (Exception $e) {
            FlashMessage::setMessage('Error generating tickets. Please try again.', 'danger');
            return $response->redirect("/checkout/success/{$reference}");
        }
    }
}
