<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Genera un PDF para un ticket específico
     */
    public function generateTicketPdf(Ticket $ticket)
    {
        $data = [
            'ticket' => $ticket,
            'title' => 'Detalles del Ticket #' . $ticket->id
        ];

        return PDF::loadView('pdf.ticket', $data);
    }

    /**
     * Genera un PDF con la lista de tickets
     */
    public function generateTicketsListPdf($tickets = null)
    {
        if (!$tickets) {
            $tickets = Ticket::with(['user', 'assignedTo', 'status', 'unidadAcademica', 'building', 'office', 'equipment'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $data = [
            'tickets' => $tickets,
            'title' => 'Lista de Tickets'
        ];

        return PDF::loadView('pdf.tickets-list', $data);
    }
}
