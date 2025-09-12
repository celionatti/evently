<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== PdfGenerator
 * ===============        ===============
 * ======================================
 */

namespace App\services;

use TCPDF;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Trees\Helper\Utils\TimeDateUtils;

class old
{
    /**
     * Generate a visually appealing event ticket PDF
     *
     * @param array $ticketData
     * @return TCPDF
     */
    public function generateEventTicketPdf(array $ticketData): TCPDF
    {
        $pdf = $this->createPdfInstance('Ticket for ' . $ticketData['event']->event_title);

        // Generate the HTML content for the ticket
        $html = $this->generateEventTicketHtml($ticketData);
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf;
    }

    /**
     * Generate an attendees report PDF for an event.
     *
     * @param object $event
     * @param array $attendees
     * @param float $totalRevenue
     * @return TCPDF
     */
    public function generateAttendeesPdf(object $event, array $attendees, float $totalRevenue): TCPDF
    {
        $pdf = $this->createPdfInstance('Event Attendees - ' . $event->event_title);

        // Set UTF-8 compatible font
        $pdf->SetFont('helvetica', 'B', 16);

        // Title
        $pdf->Cell(0, 10, 'Event Attendees Report', 0, 1, 'C');
        $pdf->Ln(5);

        // Event Details Section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Event Information', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 10);

        // Event details table
        $eventDetails = [
            ['Event Title:', htmlspecialchars($event->event_title, ENT_QUOTES, 'UTF-8')],
            ['Date:', date('j M, Y', strtotime($event->event_date))],
            ['Time:', date('g:i A', strtotime($event->start_time))],
            ['Venue:', htmlspecialchars($event->venue, ENT_QUOTES, 'UTF-8')],
            ['City:', htmlspecialchars($event->city, ENT_QUOTES, 'UTF-8')],
            ['Status:', ucfirst($event->status)],
            ['Ticket Sales:', ucfirst($event->ticket_sales)],
        ];

        foreach ($eventDetails as $detail) {
            $pdf->Cell(40, 6, $detail[0], 0, 0, 'L');
            $pdf->Cell(0, 6, $detail[1], 0, 1, 'L');
        }

        $pdf->Ln(10);

        // Summary Statistics
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Summary Statistics', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 10);

        $confirmedCount = count(array_filter($attendees, fn($a) => $a->status === 'confirmed'));
        $pendingCount = count(array_filter($attendees, fn($a) => $a->status === 'pending'));
        $checkedInCount = count(array_filter($attendees, fn($a) => $a->status === 'checked'));

        // Format currency properly
        $formattedRevenue = '₦' . number_format((float)$totalRevenue, 2);

        $summaryStats = [
            ['Total Registrations:', (string)count($attendees)],
            ['Confirmed Attendees:', (string)$confirmedCount],
            ['Pending Confirmations:', (string)$pendingCount],
            ['Checked In:', (string)$checkedInCount],
            ['Total Revenue:', $formattedRevenue],
            ['Export Date:', date('j M, Y g:i A')],
        ];

        foreach ($summaryStats as $stat) {
            $pdf->Cell(40, 6, $stat[0], 0, 0, 'L');
            $pdf->Cell(0, 6, $stat[1], 0, 1, 'L');
        }

        $pdf->Ln(10);

        // Attendees Table
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Attendee Details', 0, 1, 'L');
        $pdf->Ln(5);

        if (!empty($attendees)) {
            // Table header
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);

            $pdf->Cell(8, 8, '#', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Name', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Email', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ticket Type', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Amount', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Status', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Purchase Date', 1, 1, 'C', true);

            // Table content
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetFillColor(245, 245, 245);

            foreach ($attendees as $index => $attendee) {
                $fill = ($index % 2 == 0) ? true : false;

                // Handle long text by truncating
                $name = mb_strlen($attendee->name) > 20 ? mb_substr($attendee->name, 0, 17) . '...' : $attendee->name;
                $email = mb_strlen($attendee->email) > 25 ? mb_substr($attendee->email, 0, 22) . '...' : $attendee->email;
                $ticketName = mb_strlen($attendee->ticket_name) > 15 ? mb_substr($attendee->ticket_name, 0, 12) . '...' : $attendee->ticket_name;

                // Format amount properly
                $formattedAmount = '₦' . number_format((float)$attendee->amount, 2);

                $pdf->Cell(8, 7, (string)($index + 1), 1, 0, 'C', $fill);
                $pdf->Cell(35, 7, htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell(40, 7, htmlspecialchars($email, ENT_QUOTES, 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell(25, 7, htmlspecialchars($ticketName, ENT_QUOTES, 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell(20, 7, $formattedAmount, 1, 0, 'R', $fill);
                $pdf->Cell(20, 7, ucfirst($attendee->status), 1, 0, 'C', $fill);
                $pdf->Cell(25, 7, date('j M, Y', strtotime($attendee->created_at)), 1, 1, 'C', $fill);

                // Check if we need a new page
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                    // Repeat header on new page
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->SetFillColor(230, 230, 230);

                    $pdf->Cell(8, 8, '#', 1, 0, 'C', true);
                    $pdf->Cell(35, 8, 'Name', 1, 0, 'C', true);
                    $pdf->Cell(40, 8, 'Email', 1, 0, 'C', true);
                    $pdf->Cell(25, 8, 'Ticket Type', 1, 0, 'C', true);
                    $pdf->Cell(20, 8, 'Amount', 1, 0, 'C', true);
                    $pdf->Cell(20, 8, 'Status', 1, 0, 'C', true);
                    $pdf->Cell(25, 8, 'Purchase Date', 1, 1, 'C', true);

                    $pdf->SetFont('helvetica', '', 8);
                }
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'No attendees found for this event.', 0, 1, 'C');
        }

        // Footer note
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Generated by Eventlyy Admin System on ' . date('j M, Y \a\t g:i A'), 0, 1, 'C');

        return $pdf;
    }

    // private function createPdfInstance(string $title, bool $isFreeTicket = false): TCPDF
    // {
    //     $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    //     if ($isFreeTicket) {
    //         // Apply document security for free tickets
    //         $pdf->SetProtection(['print'], '', 'eventlyy_pass');
    //     }

    //     $pdf->SetCreator('EVENTLYY');
    //     $pdf->SetAuthor('EVENTLYY');
    //     $pdf->SetTitle($title);

    //     // Set default header data
    //     $pdf->SetHeaderData('', 0, 'EVENTLYY', 'Event Management System');

    //     // Set header and footer fonts
    //     $pdf->setHeaderFont(array('helvetica', '', 10));
    //     $pdf->setFooterFont(array('helvetica', '', 8));

    //     // Set margins
    //     $pdf->SetMargins(15, 25, 15);
    //     $pdf->SetHeaderMargin(10);
    //     $pdf->SetFooterMargin(10);

    //     // Set auto page breaks
    //     $pdf->SetAutoPageBreak(TRUE, 15);

    //     // Add a page
    //     $pdf->AddPage();

    //     return $pdf;
    // }

    /**
     * Create a new TCPDF instance with custom settings
     *
     * @param string $title
     * @return TCPDF
     */
    private function createPdfInstance(string $title): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('EVENTLYY');
        $pdf->SetAuthor('EVENTLYY');
        $pdf->SetTitle($title);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        return $pdf;
    }

    /**
     * Generate HTML for the event ticket with cool design
     *
     * @param array $ticketData
     * @return string
     */
    private function generateEventTicketHtml(array $ticketData): string
    {
        $event = $ticketData['event'];
        $attendee = $ticketData['attendee'];
        $transaction = $ticketData['transaction'];
        $ticket = $ticketData['ticket'] ?? null;

        // Format dates and times
        $eventDate = date('F j, Y', strtotime($event->event_date));
        $startTime = date('g:i A', strtotime($event->start_time));
        $endTime = $event->end_time ? date('g:i A', strtotime($event->end_time)) : '';

        // Generate QR code
        $qrCodeData = json_encode([
            'ticket_code' => $attendee->ticket_code,
            'event_id' => $event->id,
            'attendee_id' => $attendee->id
        ]);

        $qrCodeBase64 = $this->generateQrCodeBase64($qrCodeData);

        // Get logo path (adjust based on your filesystem)
        $logoPath = ROOT_PATH . '/public/img/logo.png';
        $logoBase64 = '';

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        // Ticket design with cool styling
        return <<<HTML
        <style>
            .ticket-container {
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                border: 2px solid #4a7856;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .ticket-header {
                background: linear-gradient(135deg, #4a7856 0%, #2c5535 100%);
                padding: 20px;
                text-align: center;
                color: white;
            }
            .ticket-logo {
                max-height: 60px;
                margin-bottom: 10px;
            }
            .ticket-title {
                font-size: 24px;
                font-weight: bold;
                margin: 10px 0 5px;
            }
            .ticket-subtitle {
                font-size: 16px;
                opacity: 0.9;
            }
            .ticket-body {
                padding: 25px;
                background: #f9f9f9;
            }
            .ticket-info {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 20px;
            }
            .info-section {
                flex: 1;
                min-width: 250px;
                margin-bottom: 15px;
            }
            .info-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
                margin-bottom: 5px;
            }
            .info-value {
                font-size: 16px;
                font-weight: 500;
                margin-bottom: 10px;
            }
            .qr-section {
                text-align: center;
                margin: 20px 0;
                padding: 15px;
                background: white;
                border-radius: 8px;
                border: 1px dashed #4a7856;
            }
            .qr-code {
                width: 150px;
                height: 150px;
                margin: 0 auto;
            }
            .ticket-code {
                font-size: 18px;
                font-weight: bold;
                color: #4a7856;
                letter-spacing: 1px;
                margin-top: 10px;
            }
            .ticket-footer {
                background: #f0f0f0;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
            }
            .divider {
                border-top: 1px dashed #ccc;
                margin: 20px 0;
            }
        </style>
        
        <div class="ticket-container">
            <div class="ticket-header">
                <img src="$logoBase64" class="ticket-logo" alt="Eventlyy Logo">
                <div class="ticket-title">EVENT TICKET</div>
                <div class="ticket-subtitle">{$event->event_title}</div>
            </div>
            
            <div class="ticket-body">
                <div class="ticket-info">
                    <div class="info-section">
                        <div class="info-label">Attendee Name</div>
                        <div class="info-value">{$attendee->name}</div>
                        
                        <div class="info-label">Email</div>
                        <div class="info-value">{$attendee->email}</div>
                        
                        <div class="info-label">Phone</div>
                        <div class="info-value">{$attendee->phone}</div>
                    </div>

                    <div class="info-section">
                        <div class="info-label">Event Date & Time</div>
                        <div class="info-value">{$eventDate} at {$startTime}</div>
                        
                        <div class="info-label">Venue</div>
                        <div class="info-value">{$event->venue}</div>
                        
                        <div class="info-label">City</div>
                        <div class="info-value">{$event->city}</div>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="ticket-info">
                    <div class="info-section">
                        <div class="info-label">Ticket Type</div>
                        <div class="info-value">{$ticket->ticket_name}</div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-label">Transaction Reference</div>
                        <div class="info-value">{$transaction->reference_id}</div>
                    </div>
                </div>
                
                <div class="qr-section">
                    <img src="{$qrCodeBase64}" class="qr-code" alt="QR Code">
                    <div class="ticket-code">{$attendee->ticket_code}</div>
                    <div style="font-size: 12px; margin-top: 5px; color: #666;">
                        Scan this code for entry
                    </div>
                </div>
                
                <div style="font-size: 12px; text-align: center; color: #888; margin-top: 20px;">
                    <p>Please present this ticket at the event entrance. This ticket is non-transferable.</p>
                    <p>For assistance, contact: {$event->mail}</p>
                </div>
            </div>
            
            <div class="ticket-footer">
                Generated by Eventlyy • {$eventDate}
            </div>
        </div>
        HTML;
    }

    /**
     * Render rotated text on the provided PDF instance.
     *
     * @param TCPDF $pdf
     * @param float $x
     * @param float $y
     * @param string $txt
     * @param float $angle
     * @return void
     */
    private function rotatedText(TCPDF $pdf, float $x, float $y, string $txt, float $angle): void
    {
        $pdf->StartTransform();
        $pdf->Rotate($angle, $x, $y);
        $pdf->Text($x, $y, $txt);
        $pdf->StopTransform();
    }

    /**
     * Generate a QR code image as a Base64-encoded string
     *
     * @param string $text The data to encode in the QR code
     * @param int $size The size of the QR code (default is 200)
     * @return string Base64 encoded PNG image string
     */
    private function generateQrCodeBase64(string $text, int $size = 200): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $text,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();
        return base64_encode($result->getString());
    }

    /**
     * Output PDF for download with proper headers.
     *
     * @param TCPDF $pdf
     * @param string $filename
     * @return void
     */
    public function outputPdfForDownload(TCPDF $pdf, string $filename): void
    {
        // Clean any output buffer
        if (ob_get_contents()) ob_end_clean();

        // Output PDF
        $pdf->Output($filename, 'D'); // 'D' for download
        exit;
    }
}
