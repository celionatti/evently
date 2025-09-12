<?php

declare(strict_types=1);

namespace App\services;

use TCPDF;
use Exception;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel;

class PDFGenerator
{
    /**
     * Generate a professional ticket PDF using the event ticket data structure
     *
     * @param array $ticketData
     * @return TCPDF
     */
    public function generateTicketPdf(array $ticketData): TCPDF
    {
        // Extract data from the ticket data array
        $event = $ticketData['event'];
        $attendee = $ticketData['attendee'];
        $transaction = $ticketData['transaction'];
        $ticket = $ticketData['ticket'] ?? null;

        // Create PDF instance
        $pdf = $this->createProfessionalPdfInstance('Event Ticket - ' . $event->event_title);

        // Generate the enhanced ticket design
        $this->generateEnhancedTicketDesign($pdf, $event, $attendee, $transaction, $ticket);

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
        $pdf = $this->createProfessionalPdfInstance('Event Attendees Report - ' . $event->event_title);
        
        // Enhanced attendees report design
        $this->generateAttendeesReportContent($pdf, $event, $attendees, $totalRevenue);

        return $pdf;
    }

    /**
     * Create a professional PDF instance with enhanced settings
     */
    private function createProfessionalPdfInstance(string $title): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Eventlyy Event Management System');
        $pdf->SetAuthor('Eventlyy');
        $pdf->SetTitle($title);
        $pdf->SetSubject('Event Management Document');
        $pdf->SetKeywords('event, ticket, management, eventlyy');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        // Add a page
        $pdf->AddPage();

        return $pdf;
    }

    /**
     * Generate enhanced ticket design
     */
    private function generateEnhancedTicketDesign(TCPDF $pdf, $event, $attendee, $transaction, $ticket = null): void
    {
        // Define clean color scheme with black text and white background
        $colors = [
            'black' => [0, 0, 0],
            'white' => [255, 255, 255],
            'light_gray' => [240, 240, 240],
            'border' => [200, 200, 200]
        ];

        // White background
        $pdf->SetFillColor($colors['white'][0], $colors['white'][1], $colors['white'][2]);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Header section
        $this->drawProfessionalHeader($pdf, $colors, $event, $attendee->ticket_code);

        // Main ticket body
        $this->drawTicketBody($pdf, $colors, $event, $attendee, $transaction, $ticket);

        // QR Code section
        $this->drawQRCodeSection($pdf, $colors, $event, $attendee);

        // Footer section
        $this->drawTicketFooter($pdf, $colors, $transaction);
    }

    /**
     * Draw professional header section
     */
    private function drawProfessionalHeader(TCPDF $pdf, array $colors, $event, string $ticketCode): void
    {
        // White header background
        $pdf->SetFillColor($colors['white'][0], $colors['white'][1], $colors['white'][2]);
        $pdf->Rect(0, 0, 210, 60, 'F');

        // Bottom border
        $pdf->SetDrawColor($colors['border'][0], $colors['border'][1], $colors['border'][2]);
        $pdf->Line(10, 60, 200, 60);

        // Company logo section
        $this->drawLogoSection($pdf, 20, 15);

        // Event branding - black text
        $pdf->SetTextColor($colors['black'][0], $colors['black'][1], $colors['black'][2]);
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetXY(70, 18);
        $pdf->Cell(100, 15, 'EVENT TICKET', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(70, 35);
        $pdf->Cell(100, 8, 'Powered by Eventlyy Event Management', 0, 1, 'L');

        // Ticket number badge
        $this->drawTicketNumberBadge($pdf, $colors, $ticketCode, 150, 20);
    }

    /**
     * Draw logo section with fallback
     */
    private function drawLogoSection(TCPDF $pdf, int $x, int $y): void
    {
        $logoPath = ROOT_PATH . '/public/dist/img/logo.png';
        
        if (file_exists($logoPath)) {
            try {
                $pdf->Image($logoPath, $x, $y, 35, 35, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
            } catch (Exception $e) {
                $this->drawLogoFallback($pdf, $x, $y);
            }
        } else {
            $this->drawLogoFallback($pdf, $x, $y);
        }
    }

    /**
     * Draw logo fallback design
     */
    private function drawLogoFallback(TCPDF $pdf, int $x, int $y): void
    {
        // Simple text-based logo alternative
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY($x, $y + 10);
        $pdf->Cell(35, 12, 'EVENTLYY', 0, 0, 'C');
    }

    /**
     * Draw ticket number badge
     */
    private function drawTicketNumberBadge(TCPDF $pdf, array $colors, string $ticketCode, int $x, int $y): void
    {
        // Badge background - light gray
        $pdf->SetFillColor($colors['light_gray'][0], $colors['light_gray'][1], $colors['light_gray'][2]);
        $pdf->SetDrawColor($colors['border'][0], $colors['border'][1], $colors['border'][2]);
        $pdf->RoundedRect($x, $y, 45, 25, 4, '1111', 'FD');

        // Badge content - black text
        $pdf->SetTextColor($colors['black'][0], $colors['black'][1], $colors['black'][2]);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x, $y + 5);
        $pdf->Cell(45, 6, 'TICKET #', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY($x, $y + 13);
        $pdf->Cell(45, 8, $ticketCode, 0, 1, 'C');
    }

    /**
     * Draw main ticket body
     */
    private function drawTicketBody(TCPDF $pdf, array $colors, $event, $attendee, $transaction, $ticket = null): void
    {
        $yPosition = 70;

        // Event title
        $pdf->SetTextColor($colors['black'][0], $colors['black'][1], $colors['black'][2]);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetXY(20, $yPosition);
        $pdf->MultiCell(170, 10, htmlspecialchars($event->event_title), 0, 'C');
        $yPosition += 15;

        // Divider line
        $pdf->SetDrawColor($colors['border'][0], $colors['border'][1], $colors['border'][2]);
        $pdf->Line(20, $yPosition, 190, $yPosition);
        $yPosition += 15;

        // Attendee information section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(20, $yPosition);
        $pdf->Cell(80, 8, 'ATTENDEE INFORMATION', 0, 1);
        $yPosition += 12;

        $pdf->SetFont('helvetica', '', 11);
        $this->drawInfoRow($pdf, 20, $yPosition, 'Name:', htmlspecialchars($attendee->name));
        $yPosition += 8;
        $this->drawInfoRow($pdf, 20, $yPosition, 'Email:', htmlspecialchars($attendee->email));
        $yPosition += 8;
        $this->drawInfoRow($pdf, 20, $yPosition, 'Phone:', htmlspecialchars($attendee->phone));
        $yPosition += 15;

        // Event details section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(20, $yPosition);
        $pdf->Cell(80, 8, 'EVENT DETAILS', 0, 1);
        $yPosition += 12;

        $pdf->SetFont('helvetica', '', 11);
        $eventDate = date('F j, Y', strtotime($event->event_date));
        $startTime = date('g:i A', strtotime($event->start_time));
        $this->drawInfoRow($pdf, 20, $yPosition, 'Date & Time:', $eventDate . ' at ' . $startTime);
        $yPosition += 8;
        $this->drawInfoRow($pdf, 20, $yPosition, 'Venue:', htmlspecialchars($event->venue));
        $yPosition += 8;
        $this->drawInfoRow($pdf, 20, $yPosition, 'City:', htmlspecialchars($event->city));
        $yPosition += 15;

        // Ticket information section
        if ($ticket) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetXY(20, $yPosition);
            $pdf->Cell(80, 8, 'TICKET INFORMATION', 0, 1);
            $yPosition += 12;

            $pdf->SetFont('helvetica', '', 11);
            $this->drawInfoRow($pdf, 20, $yPosition, 'Ticket Type:', htmlspecialchars($ticket->ticket_name));
            $yPosition += 8;
            
            // Format amount properly
            $formattedAmount = '₦' . number_format((float)$transaction->amount, 2);
            $this->drawInfoRow($pdf, 20, $yPosition, 'Amount:', $formattedAmount);
            $yPosition += 8;
            
            $this->drawInfoRow($pdf, 20, $yPosition, 'Reference ID:', $transaction->reference_id);
            $yPosition += 15;
        }
    }

    /**
     * Draw information row with label and value
     */
    private function drawInfoRow(TCPDF $pdf, int $x, int $y, string $label, string $value): void
    {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(40, 6, $label, 0, 0, 'L');
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetXY($x + 40, $y);
        $pdf->Cell(130, 6, $value, 0, 1, 'L');
    }

    /**
     * Draw QR code section
     */
    private function drawQRCodeSection(TCPDF $pdf, array $colors, $event, $attendee): void
    {
        $yPosition = 200;

        // Generate QR code
        $qrCodeData = json_encode([
            'ticket_code' => $attendee->ticket_code,
            'event' => $event->event_title,
            'attendee_name' => $attendee->name,
            'ticket_status' => $attendee->status
        ]);

        $qrCodeBase64 = $this->generateQrCodeBase64($qrCodeData);

        // QR code container
        $pdf->SetDrawColor($colors['border'][0], $colors['border'][1], $colors['border'][2]);
        $pdf->SetFillColor($colors['white'][0], $colors['white'][1], $colors['white'][2]);
        $pdf->RoundedRect(75, $yPosition, 60, 80, 5, '1111', 'FD');

        // QR code image
        $imageData = base64_decode($qrCodeBase64);
        $pdf->Image('@' . $imageData, 85, $yPosition + 10, 40, 40, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);

        // QR code label
        $pdf->SetTextColor($colors['black'][0], $colors['black'][1], $colors['black'][2]);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(75, $yPosition + 55);
        $pdf->Cell(60, 6, 'Scan for Entry', 0, 1, 'C');

        // Ticket code
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(75, $yPosition + 65);
        $pdf->Cell(60, 6, 'Ticket: ' . $attendee->ticket_code, 0, 1, 'C');
    }

    /**
     * Draw ticket footer
     */
    private function drawTicketFooter(TCPDF $pdf, array $colors, $transaction): void
    {
        $yPosition = 285;

        // Footer border
        $pdf->SetDrawColor($colors['border'][0], $colors['border'][1], $colors['border'][2]);
        $pdf->Line(10, $yPosition - 5, 200, $yPosition - 5);

        // Footer text
        $pdf->SetTextColor($colors['black'][0], $colors['black'][1], $colors['black'][2]);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetXY(10, $yPosition);
        $pdf->Cell(190, 5, 'Generated by Eventlyy Event Management System • ' . date('M j, Y g:i A'), 0, 1, 'C');
        
        $pdf->SetXY(10, $yPosition + 5);
        $pdf->Cell(190, 5, 'Transaction Ref: ' . $transaction->reference_id, 0, 1, 'C');
    }

    /**
     * Generate attendees report content
     */
    private function generateAttendeesReportContent(TCPDF $pdf, object $event, array $attendees, float $totalRevenue): void
    {
        // White background
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Report header
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetXY(10, 15);
        $pdf->Cell(190, 10, 'EVENT ATTENDEES REPORT', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(10, 30);
        $pdf->Cell(190, 8, htmlspecialchars($event->event_title), 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(10, 40);
        $pdf->Cell(190, 6, 'Generated on: ' . date('M j, Y g:i A'), 0, 1, 'C');

        // Event details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(10, 55);
        $pdf->Cell(190, 8, 'Event Information', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $yPosition = 65;
        $eventDetails = [
            ['Date:', date('M j, Y', strtotime($event->event_date))],
            ['Time:', date('g:i A', strtotime($event->start_time))],
            ['Venue:', htmlspecialchars($event->venue)],
            ['City:', htmlspecialchars($event->city)],
            ['Status:', ucfirst($event->status)]
        ];

        foreach ($eventDetails as $detail) {
            $pdf->SetXY(20, $yPosition);
            $pdf->Cell(30, 6, $detail[0], 0, 0, 'L');
            $pdf->SetXY(50, $yPosition);
            $pdf->Cell(150, 6, $detail[1], 0, 1, 'L');
            $yPosition += 7;
        }

        // Summary statistics
        $yPosition += 10;
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(10, $yPosition);
        $pdf->Cell(190, 8, 'Summary Statistics', 0, 1, 'L');
        $yPosition += 10;

        $confirmedCount = count(array_filter($attendees, fn($a) => $a->status === 'confirmed'));
        $pendingCount = count(array_filter($attendees, fn($a) => $a->status === 'pending'));
        $checkedInCount = count(array_filter($attendees, fn($a) => $a->status === 'checked'));
        $formattedRevenue = '₦' . number_format((float)$totalRevenue, 2);

        $stats = [
            ['Total Registrations:', (string)count($attendees)],
            ['Confirmed Attendees:', (string)$confirmedCount],
            ['Pending Confirmations:', (string)$pendingCount],
            ['Checked In:', (string)$checkedInCount],
            ['Total Revenue:', $formattedRevenue]
        ];

        $pdf->SetFont('helvetica', '', 10);
        foreach ($stats as $stat) {
            $pdf->SetXY(20, $yPosition);
            $pdf->Cell(50, 6, $stat[0], 0, 0, 'L');
            $pdf->SetXY(70, $yPosition);
            $pdf->Cell(50, 6, $stat[1], 0, 1, 'L');
            $yPosition += 7;
        }

        // Attendees table
        $yPosition += 15;
        $this->drawAttendeesTable($pdf, $attendees, $yPosition);
    }

    /**
     * Draw attendees table
     */
    private function drawAttendeesTable(TCPDF $pdf, array $attendees, int $startY): void
    {
        if (empty($attendees)) {
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->SetXY(10, $startY);
            $pdf->Cell(190, 10, 'No attendees found for this event.', 0, 1, 'C');
            return;
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        
        // Table header
        $headers = ['#', 'Name', 'Email', 'Ticket Type', 'Amount', 'Status', 'Date'];
        $widths = [10, 40, 50, 30, 25, 20, 25];
        
        $x = 10;
        foreach ($headers as $index => $header) {
            $pdf->SetXY($x, $startY);
            $pdf->Cell($widths[$index], 8, $header, 1, 0, 'C', true);
            $x += $widths[$index];
        }

        $y = $startY + 8;
        $pdf->SetFont('helvetica', '', 8);
        
        foreach ($attendees as $index => $attendee) {
            $fill = ($index % 2 == 0);
            
            if ($fill) {
                $pdf->SetFillColor(245, 245, 245);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }

            $x = 10;
            $cells = [
                ($index + 1),
                mb_strlen($attendee->name) > 25 ? mb_substr($attendee->name, 0, 22) . '...' : $attendee->name,
                mb_strlen($attendee->email) > 30 ? mb_substr($attendee->email, 0, 27) . '...' : $attendee->email,
                mb_strlen($attendee->ticket_name) > 20 ? mb_substr($attendee->ticket_name, 0, 17) . '...' : $attendee->ticket_name,
                '₦' . number_format((float)$attendee->amount, 2),
                ucfirst($attendee->status),
                date('M j, Y', strtotime($attendee->created_at))
            ];

            foreach ($cells as $cellIndex => $cell) {
                $pdf->SetXY($x, $y);
                $pdf->Cell($widths[$cellIndex], 6, htmlspecialchars((string)$cell), 1, 0, 'C', $fill);
                $x += $widths[$cellIndex];
            }

            $y += 6;
            
            // Check if we need a new page
            if ($y > 270 && $index < count($attendees) - 1) {
                $pdf->AddPage();
                $y = 20;
                
                // Redraw header on new page
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(240, 240, 240);
                $x = 10;
                foreach ($headers as $headerIndex => $header) {
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($widths[$headerIndex], 8, $header, 1, 0, 'C', true);
                    $x += $widths[$headerIndex];
                }
                $y += 8;
                $pdf->SetFont('helvetica', '', 8);
            }
        }
    }

    /**
     * Generate a QR code image as a Base64-encoded string
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