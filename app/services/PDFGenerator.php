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
    // Brand colors from your CSS
    private array $brandColors = [
        'primary' => [30, 136, 229],     // --blue-2: #1e88e5
        'secondary' => [100, 181, 246],  // --blue-1: #64b5f6
        'dark' => [13, 71, 161],         // --blue-3: #0d47a1
        'bg_dark' => [11, 18, 32],       // --bg-0: #0b1220
        'bg_medium' => [14, 21, 38],     // --bg-1: #0e1526
        'bg_light' => [16, 26, 48],      // --bg-2: #101a30
        'text_light' => [226, 232, 240], // --text-1: #e2e8f0
        'text_medium' => [159, 179, 200], // --text-2: #9fb3c8
        'white' => [255, 255, 255],
        'light_gray' => [248, 250, 252],
        'border' => [226, 232, 240],
        'success' => [34, 197, 94],
        'warning' => [245, 158, 11],
        'danger' => [239, 68, 68]
    ];

    /**
     * Generate a professional ticket PDF using the event ticket data structure
     */
    public function generateTicketPdf(array $ticketData): TCPDF
    {
        $event = $ticketData['event'];
        $attendee = $ticketData['attendee'];
        $transaction = $ticketData['transaction'];
        $ticket = $ticketData['ticket'] ?? null;

        $pdf = $this->createProfessionalPdfInstance('Event Ticket - ' . $event->event_title);
        $this->generateEnhancedTicketDesign($pdf, $event, $attendee, $transaction, $ticket);

        return $pdf;
    }

    /**
     * Generate an attendees report PDF for an event
     */
    public function generateAttendeesPdf(object $event, array $attendees, float $totalRevenue): TCPDF
    {
        $pdf = $this->createProfessionalPdfInstance('Event Attendees Report - ' . $event->event_title);
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
     * Generate enhanced ticket design with brand colors
     */
    private function generateEnhancedTicketDesign(TCPDF $pdf, $event, $attendee, $transaction, $ticket = null): void
    {
        // Set dark background for better logo visibility
        $pdf->SetFillColor($this->brandColors['bg_dark'][0], $this->brandColors['bg_dark'][1], $this->brandColors['bg_dark'][2]);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Add subtle gradient overlay
        $this->addGradientBackground($pdf);

        // Header section with brand styling
        $this->drawBrandedHeader($pdf, $event, $attendee->ticket_code);

        // Main ticket body with improved layout
        $this->drawEnhancedTicketBody($pdf, $event, $attendee, $transaction, $ticket);

        // QR Code section with modern styling
        $this->drawModernQRSection($pdf, $event, $attendee);

        // Professional footer
        $this->drawBrandedFooter($pdf, $transaction);

        // Add decorative elements
        $this->addDecorativeElements($pdf);
    }

    /**
     * Add gradient background effect
     */
    private function addGradientBackground(TCPDF $pdf): void
    {
        // Create subtle gradient rectangles for depth
        $pdf->SetAlpha(0.05);
        $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->Ellipse(50, -50, 200, 100, 0, 0, 360, 'F');
        
        $pdf->SetFillColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->Ellipse(160, 350, 180, 90, 0, 0, 360, 'F');
        $pdf->SetAlpha(1);
    }

    /**
     * Draw branded header with improved styling
     */
    private function drawBrandedHeader(TCPDF $pdf, $event, string $ticketCode): void
    {
        // Header background with gradient effect
        $pdf->SetFillColor($this->brandColors['bg_medium'][0], $this->brandColors['bg_medium'][1], $this->brandColors['bg_medium'][2]);
        $pdf->RoundedRect(10, 10, 190, 60, 8, '1111', 'F');

        // Border accent
        $pdf->SetDrawColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->SetLineWidth(0.5);
        $pdf->RoundedRect(10, 10, 190, 60, 8, '1111', 'D');

        // Company logo section with better positioning
        $this->drawEnhancedLogoSection($pdf, 25, 25);

        // Event branding with better typography
        $pdf->SetTextColor($this->brandColors['text_light'][0], $this->brandColors['text_light'][1], $this->brandColors['text_light'][2]);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(85, 25);
        $pdf->Cell(85, 12, 'EVENT TICKET', 0, 1, 'L');

        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(85, 40);
        $pdf->Cell(85, 8, 'Powered by Eventlyy', 0, 1, 'L');

        // Enhanced ticket number badge
        $this->drawEnhancedTicketBadge($pdf, $ticketCode, 140, 20);
    }

    /**
     * Draw enhanced logo section
     */
    private function drawEnhancedLogoSection(TCPDF $pdf, int $x, int $y): void
    {
        $logoPath = ROOT_PATH . '/public/dist/img/eventlyy.png';
        
        if (file_exists($logoPath)) {
            try {
                // Add logo background for better visibility
                $pdf->SetFillColor($this->brandColors['light_gray'][0], $this->brandColors['light_gray'][1], $this->brandColors['light_gray'][2]);
                $pdf->RoundedRect($x - 2, $y - 2, 44, 44, 6, '1111', 'F');
                
                $pdf->Image($logoPath, $x, $y, 40, 40, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
            } catch (Exception $e) {
                $this->drawEnhancedLogoFallback($pdf, $x, $y);
            }
        } else {
            $this->drawEnhancedLogoFallback($pdf, $x, $y);
        }
    }

    /**
     * Draw enhanced logo fallback with brand colors
     */
    private function drawEnhancedLogoFallback(TCPDF $pdf, int $x, int $y): void
    {
        // Logo container with gradient
        $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->RoundedRect($x, $y, 40, 40, 8, '1111', 'F');

        // Brand text
        $pdf->SetTextColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY($x, $y + 10);
        $pdf->Cell(40, 8, 'EVENT', 0, 0, 'C');
        
        $pdf->SetXY($x, $y + 22);
        $pdf->Cell(40, 8, 'LYYYY', 0, 0, 'C');
    }

    /**
     * Draw enhanced ticket badge
     */
    private function drawEnhancedTicketBadge(TCPDF $pdf, string $ticketCode, int $x, int $y): void
    {
        // Badge background with gradient effect
        $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->RoundedRect($x, $y, 50, 30, 8, '1111', 'F');

        // Badge border
        $pdf->SetDrawColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetLineWidth(0.5);
        $pdf->RoundedRect($x, $y, 50, 30, 8, '1111', 'D');

        // Badge content
        $pdf->SetTextColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetXY($x, $y + 6);
        $pdf->Cell(50, 6, 'TICKET #', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($x, $y + 16);
        $pdf->Cell(50, 8, $ticketCode, 0, 1, 'C');
    }

    /**
     * Draw enhanced ticket body with better formatting
     */
    private function drawEnhancedTicketBody(TCPDF $pdf, $event, $attendee, $transaction, $ticket = null): void
    {
        $yPosition = 85;

        // Event title section
        $this->drawSectionCard($pdf, 15, $yPosition, 180, 25);
        
        $pdf->SetTextColor($this->brandColors['text_light'][0], $this->brandColors['text_light'][1], $this->brandColors['text_light'][2]);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetXY(25, $yPosition + 8);
        $pdf->MultiCell(160, 10, htmlspecialchars($event->event_title), 0, 'C');
        
        $yPosition += 40;

        // Attendee information section
        $this->drawInfoSection($pdf, 'ATTENDEE INFORMATION', $yPosition, [
            'Name' => htmlspecialchars($attendee->name),
            'Email' => htmlspecialchars($attendee->email),
            'Phone' => htmlspecialchars($attendee->phone)
        ]);
        $yPosition += 60;

        // Event details section
        $eventDate = date('l, F j, Y', strtotime($event->event_date));
        $startTime = date('g:i A', strtotime($event->start_time));
        
        $this->drawInfoSection($pdf, 'EVENT DETAILS', $yPosition, [
            'Date & Time' => $eventDate . ' at ' . $startTime,
            'Venue' => htmlspecialchars($event->venue),
            'Location' => htmlspecialchars($event->city)
        ]);
        $yPosition += 60;

        // Ticket information section (if ticket exists)
        if ($ticket) {
            $formattedAmount = $this->formatCurrency((float)$transaction->amount);
            
            $this->drawInfoSection($pdf, 'TICKET INFORMATION', $yPosition, [
                'Ticket Type' => htmlspecialchars($ticket->ticket_name),
                'Amount Paid' => $formattedAmount,
                'Reference ID' => $transaction->reference_id,
                'Status' => ucfirst($attendee->status ?? 'confirmed')
            ]);
        }
    }

    /**
     * Draw a section card background
     */
    private function drawSectionCard(TCPDF $pdf, int $x, int $y, int $width, int $height): void
    {
        $pdf->SetFillColor($this->brandColors['bg_light'][0], $this->brandColors['bg_light'][1], $this->brandColors['bg_light'][2]);
        $pdf->RoundedRect($x, $y, $width, $height, 6, '1111', 'F');

        $pdf->SetDrawColor($this->brandColors['border'][0], $this->brandColors['border'][1], $this->brandColors['border'][2]);
        $pdf->SetLineWidth(0.2);
        $pdf->RoundedRect($x, $y, $width, $height, 6, '1111', 'D');
    }

    /**
     * Draw information section with improved styling
     */
    private function drawInfoSection(TCPDF $pdf, string $title, int $yPosition, array $data): void
    {
        // Section background
        $this->drawSectionCard($pdf, 20, $yPosition, 170, 15 + (count($data) * 10));

        // Section title
        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(30, $yPosition + 5);
        $pdf->Cell(150, 8, $title, 0, 1, 'L');

        $itemY = $yPosition + 18;
        foreach ($data as $label => $value) {
            $this->drawEnhancedInfoRow($pdf, 30, $itemY, $label . ':', $value);
            $itemY += 10;
        }
    }

    /**
     * Draw enhanced information row
     */
    private function drawEnhancedInfoRow(TCPDF $pdf, int $x, int $y, string $label, string $value): void
    {
        // Label
        $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
        $pdf->SetFont('helvetica', '600', 11);
        $pdf->SetXY($x, $y);
        $pdf->Cell(50, 6, $label, 0, 0, 'L');

        // Value
        $pdf->SetTextColor($this->brandColors['text_light'][0], $this->brandColors['text_light'][1], $this->brandColors['text_light'][2]);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetXY($x + 50, $y);
        $pdf->Cell(120, 6, $value, 0, 1, 'L');
    }

    /**
     * Draw modern QR code section
     */
    private function drawModernQRSection(TCPDF $pdf, $event, $attendee): void
    {
        $yPosition = 220;

        // QR code container with modern styling
        $pdf->SetFillColor($this->brandColors['bg_medium'][0], $this->brandColors['bg_medium'][1], $this->brandColors['bg_medium'][2]);
        $pdf->RoundedRect(70, $yPosition, 70, 90, 12, '1111', 'F');

        $pdf->SetDrawColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->SetLineWidth(0.8);
        $pdf->RoundedRect(70, $yPosition, 70, 90, 12, '1111', 'D');

        // Generate QR code with enhanced data
        $qrCodeData = json_encode([
            'ticket_code' => $attendee->ticket_code,
            'event_id' => $event->id ?? null,
            'event_title' => $event->event_title,
            'attendee_name' => $attendee->name,
            'status' => $attendee->status ?? 'confirmed',
            'generated_at' => date('Y-m-d H:i:s')
        ]);

        $qrCodeBase64 = $this->generateQrCodeBase64($qrCodeData, 200);
        $imageData = base64_decode($qrCodeBase64);

        // QR code with white background for better scanning
        $pdf->SetFillColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
        $pdf->RoundedRect(80, $yPosition + 10, 50, 50, 6, '1111', 'F');
        $pdf->Image('@' . $imageData, 85, $yPosition + 15, 40, 40, 'PNG');

        // QR code labels
        $pdf->SetTextColor($this->brandColors['text_light'][0], $this->brandColors['text_light'][1], $this->brandColors['text_light'][2]);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(70, $yPosition + 70);
        $pdf->Cell(70, 6, 'Scan for Entry', 0, 1, 'C');

        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(70, $yPosition + 80);
        $pdf->Cell(70, 6, $attendee->ticket_code, 0, 1, 'C');
    }

    /**
     * Draw branded footer
     */
    private function drawBrandedFooter(TCPDF $pdf, $transaction): void
    {
        $yPosition = 270;

        // Footer background
        $pdf->SetFillColor($this->brandColors['bg_medium'][0], $this->brandColors['bg_medium'][1], $this->brandColors['bg_medium'][2]);
        $pdf->RoundedRect(15, $yPosition, 180, 20, 6, '1111', 'F');

        // Footer border
        $pdf->SetDrawColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(25, $yPosition + 5, 185, $yPosition + 5);

        // Footer text
        $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(25, $yPosition + 8);
        $pdf->Cell(160, 4, 'Generated by Eventlyy Event Management System • ' . date('M j, Y g:i A'), 0, 1, 'C');

        $pdf->SetXY(25, $yPosition + 13);
        $pdf->Cell(160, 4, 'Transaction Reference: ' . $transaction->reference_id, 0, 1, 'C');
    }

    /**
     * Add decorative elements
     */
    private function addDecorativeElements(TCPDF $pdf): void
    {
        // Add subtle corner decorations
        $pdf->SetAlpha(0.1);
        $pdf->SetFillColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        
        // Top corners
        $pdf->Circle(15, 15, 8, 0, 360, 'F');
        $pdf->Circle(195, 15, 8, 0, 360, 'F');
        
        // Bottom corners
        $pdf->Circle(15, 282, 8, 0, 360, 'F');
        $pdf->Circle(195, 282, 8, 0, 360, 'F');
        
        $pdf->SetAlpha(1);
    }

    /**
     * Format currency properly with Nigerian Naira symbol
     */
    private function formatCurrency(float $amount): string
    {
        // Ensure amount is properly formatted with 2 decimal places
        $formattedNumber = number_format($amount, 2, '.', ',');
        return '₦' . $formattedNumber;
    }

    /**
     * Generate attendees report with enhanced styling
     */
    private function generateAttendeesReportContent(TCPDF $pdf, object $event, array $attendees, float $totalRevenue): void
    {
        // Dark background for consistency
        $pdf->SetFillColor($this->brandColors['bg_dark'][0], $this->brandColors['bg_dark'][1], $this->brandColors['bg_dark'][2]);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Report header with branding
        $this->drawReportHeader($pdf, $event);

        // Event information section
        $this->drawEventInfoSection($pdf, $event, 60);

        // Statistics section
        $this->drawStatisticsSection($pdf, $attendees, $totalRevenue, 120);

        // Attendees table
        $this->drawEnhancedAttendeesTable($pdf, $attendees, 180);
    }

    /**
     * Draw enhanced report header
     */
    private function drawReportHeader(TCPDF $pdf, object $event): void
    {
        // Header background
        $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->RoundedRect(15, 15, 180, 35, 8, '1111', 'F');

        // Title
        $pdf->SetTextColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetXY(15, 25);
        $pdf->Cell(180, 8, 'EVENT ATTENDEES REPORT', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(15, 35);
        $pdf->Cell(180, 8, htmlspecialchars($event->event_title), 0, 1, 'C');

        // Generation info
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(15, 45);
        $pdf->Cell(180, 4, 'Generated on: ' . date('l, F j, Y \a\t g:i A'), 0, 1, 'C');
    }

    /**
     * Draw event information section
     */
    private function drawEventInfoSection(TCPDF $pdf, object $event, int $yPosition): void
    {
        $this->drawSectionCard($pdf, 20, $yPosition, 170, 45);

        // Section title
        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(30, $yPosition + 5);
        $pdf->Cell(150, 8, 'EVENT INFORMATION', 0, 1, 'L');

        // Event details in two columns
        $leftColumn = [
            'Date' => date('l, F j, Y', strtotime($event->event_date)),
            'Time' => date('g:i A', strtotime($event->start_time)),
            'Status' => ucfirst($event->status ?? 'active')
        ];

        $rightColumn = [
            'Venue' => htmlspecialchars($event->venue),
            'City' => htmlspecialchars($event->city),
            'Category' => htmlspecialchars($event->category ?? 'General')
        ];

        $itemY = $yPosition + 18;
        foreach ($leftColumn as $label => $value) {
            $this->drawEnhancedInfoRow($pdf, 30, $itemY, $label . ':', $value);
            $itemY += 8;
        }

        $itemY = $yPosition + 18;
        foreach ($rightColumn as $label => $value) {
            $this->drawEnhancedInfoRow($pdf, 110, $itemY, $label . ':', $value);
            $itemY += 8;
        }
    }

    /**
     * Draw statistics section with visual enhancements
     */
    private function drawStatisticsSection(TCPDF $pdf, array $attendees, float $totalRevenue, int $yPosition): void
    {
        $this->drawSectionCard($pdf, 20, $yPosition, 170, 45);

        // Section title
        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(30, $yPosition + 5);
        $pdf->Cell(150, 8, 'SUMMARY STATISTICS', 0, 1, 'L');

        // Calculate statistics
        $confirmedCount = count(array_filter($attendees, fn($a) => ($a->status ?? 'confirmed') === 'confirmed'));
        $pendingCount = count(array_filter($attendees, fn($a) => ($a->status ?? 'confirmed') === 'pending'));
        $checkedInCount = count(array_filter($attendees, fn($a) => ($a->status ?? 'confirmed') === 'checked_in'));

        $leftStats = [
            'Total Registrations' => (string)count($attendees),
            'Confirmed Attendees' => (string)$confirmedCount,
            'Checked In' => (string)$checkedInCount
        ];

        $rightStats = [
            'Pending Confirmations' => (string)$pendingCount,
            'Total Revenue' => $this->formatCurrency($totalRevenue),
            'Avg. Ticket Price' => count($attendees) > 0 ? $this->formatCurrency($totalRevenue / count($attendees)) : $this->formatCurrency(0)
        ];

        $itemY = $yPosition + 18;
        foreach ($leftStats as $label => $value) {
            $this->drawStatisticRow($pdf, 30, $itemY, $label . ':', $value);
            $itemY += 8;
        }

        $itemY = $yPosition + 18;
        foreach ($rightStats as $label => $value) {
            $this->drawStatisticRow($pdf, 110, $itemY, $label . ':', $value);
            $itemY += 8;
        }
    }

    /**
     * Draw statistic row with emphasis on values
     */
    private function drawStatisticRow(TCPDF $pdf, int $x, int $y, string $label, string $value): void
    {
        // Label
        $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY($x, $y);
        $pdf->Cell(40, 6, $label, 0, 0, 'L');

        // Value with emphasis
        $pdf->SetTextColor($this->brandColors['secondary'][0], $this->brandColors['secondary'][1], $this->brandColors['secondary'][2]);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetXY($x + 40, $y);
        $pdf->Cell(40, 6, $value, 0, 1, 'L');
    }

    /**
     * Draw enhanced attendees table
     */
    private function drawEnhancedAttendeesTable(TCPDF $pdf, array $attendees, int $startY): void
    {
        if (empty($attendees)) {
            $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->SetXY(20, $startY);
            $pdf->Cell(170, 10, 'No attendees found for this event.', 0, 1, 'C');
            return;
        }

        // Table header
        $headers = ['#', 'Name', 'Email', 'Ticket Type', 'Amount', 'Status', 'Date'];
        $widths = [12, 35, 50, 28, 25, 18, 22];

        // Header background
        $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
        $pdf->SetTextColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
        $pdf->SetFont('helvetica', 'B', 9);

        $x = 20;
        $y = $startY;
        
        foreach ($headers as $index => $header) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($widths[$index], 8, $header, 1, 0, 'C', true);
            $x += $widths[$index];
        }

        // Table rows
        $y += 8;
        $pdf->SetFont('helvetica', '', 8);
        
        foreach ($attendees as $index => $attendee) {
            // Alternate row colors
            $fill = ($index % 2 == 0);
            
            if ($fill) {
                $pdf->SetFillColor($this->brandColors['bg_light'][0], $this->brandColors['bg_light'][1], $this->brandColors['bg_light'][2]);
                $pdf->SetTextColor($this->brandColors['text_light'][0], $this->brandColors['text_light'][1], $this->brandColors['text_light'][2]);
            } else {
                $pdf->SetFillColor($this->brandColors['bg_medium'][0], $this->brandColors['bg_medium'][1], $this->brandColors['bg_medium'][2]);
                $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
            }

            $x = 20;
            $cells = [
                ($index + 1),
                $this->truncateText($attendee->name, 20),
                $this->truncateText($attendee->email, 30),
                $this->truncateText($attendee->ticket_name ?? 'Standard', 18),
                $this->formatCurrency((float)($attendee->amount ?? 0)),
                $this->getStatusDisplay($attendee->status ?? 'confirmed'),
                date('M j, Y', strtotime($attendee->created_at ?? date('Y-m-d')))
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
                $pdf->SetFillColor($this->brandColors['bg_dark'][0], $this->brandColors['bg_dark'][1], $this->brandColors['bg_dark'][2]);
                $pdf->Rect(0, 0, 210, 297, 'F');
                $y = 20;
                
                // Redraw header on new page
                $pdf->SetFillColor($this->brandColors['primary'][0], $this->brandColors['primary'][1], $this->brandColors['primary'][2]);
                $pdf->SetTextColor($this->brandColors['white'][0], $this->brandColors['white'][1], $this->brandColors['white'][2]);
                $pdf->SetFont('helvetica', 'B', 9);
                
                $x = 20;
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
     * Truncate text to fit in table cells
     */
    private function truncateText(string $text, int $maxLength): string
    {
        if (mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }

    /**
     * Get formatted status display
     */
    private function getStatusDisplay(string $status): string
    {
        $statusMap = [
            'confirmed' => 'Confirmed',
            'pending' => 'Pending',
            'checked_in' => 'Checked In',
            'cancelled' => 'Cancelled'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Generate a QR code image as a Base64-encoded string with enhanced settings
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
     * Output PDF for download with proper headers and enhanced metadata
     */
    public function outputPdfForDownload(TCPDF $pdf, string $filename): void
    {
        // Clean any output buffer
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // Set proper headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Output PDF
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Output PDF for inline viewing in browser
     */
    public function outputPdfInline(TCPDF $pdf, string $filename): void
    {
        // Clean any output buffer
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // Set proper headers for PDF inline viewing
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Output PDF
        $pdf->Output($filename, 'I');
        exit;
    }

    /**
     * Save PDF to file system
     */
    public function savePdfToFile(TCPDF $pdf, string $filepath): bool
    {
        try {
            $pdf->Output($filepath, 'F');
            return true;
        } catch (Exception $e) {
            error_log('PDF Save Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate PDF as string for further processing
     */
    public function getPdfString(TCPDF $pdf): string
    {
        return $pdf->Output('', 'S');
    }

    /**
     * Add watermark to PDF (useful for draft versions)
     */
    public function addWatermark(TCPDF $pdf, string $text = 'DRAFT'): void
    {
        // Save current settings
        $currentAlpha = 1;
        $currentFont = $pdf->getFontFamily();
        $currentFontStyle = $pdf->getFontStyle();
        $currentFontSize = $pdf->getFontSizePt();

        // Set watermark properties
        $pdf->SetAlpha(0.3);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('helvetica', 'B', 60);

        // Calculate position for center of page
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        // Add watermark text
        $pdf->SetXY(0, $pageHeight / 2 - 15);
        $pdf->Cell($pageWidth, 30, $text, 0, 1, 'C');

        // Restore original settings
        $pdf->SetAlpha(1);
        $pdf->SetFont($currentFont, $currentFontStyle, $currentFontSize);
    }

    /**
     * Add page numbering to multi-page reports
     */
    public function addPageNumbers(TCPDF $pdf): void
    {
        $totalPages = $pdf->getNumPages();
        
        for ($pageNum = 1; $pageNum <= $totalPages; $pageNum++) {
            $pdf->setPage($pageNum);
            
            $pdf->SetTextColor($this->brandColors['text_medium'][0], $this->brandColors['text_medium'][1], $this->brandColors['text_medium'][2]);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetXY(180, 285);
            $pdf->Cell(20, 5, "Page {$pageNum} of {$totalPages}", 0, 0, 'R');
        }
    }

    /**
     * Validate ticket data structure
     */
    private function validateTicketData(array $ticketData): bool
    {
        $requiredKeys = ['event', 'attendee', 'transaction'];
        
        foreach ($requiredKeys as $key) {
            if (!isset($ticketData[$key])) {
                throw new Exception("Missing required ticket data: {$key}");
            }
        }

        // Validate event object
        $event = $ticketData['event'];
        if (!isset($event->event_title, $event->event_date, $event->start_time, $event->venue, $event->city)) {
            throw new Exception("Invalid event data structure");
        }

        // Validate attendee object
        $attendee = $ticketData['attendee'];
        if (!isset($attendee->name, $attendee->email, $attendee->ticket_code)) {
            throw new Exception("Invalid attendee data structure");
        }

        // Validate transaction object
        $transaction = $ticketData['transaction'];
        if (!isset($transaction->amount, $transaction->reference_id)) {
            throw new Exception("Invalid transaction data structure");
        }

        return true;
    }
}