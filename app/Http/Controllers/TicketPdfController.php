<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPdfController extends Controller
{
    public function generate(Ticket $ticket)
    {
        $pdf = Pdf::loadView('pdf.ticket', [
            'ticket' => $ticket->load(['status', 'priority', 'category', 'creator', 'assignedTo'])
        ]);

        return $pdf->download("ticket-{$ticket->id}.pdf");
    }
}