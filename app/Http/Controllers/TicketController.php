<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Genera un PDF para un ticket específico o para todos los tickets
     *
     * @param Ticket|null $ticket Ticket específico (opcional)
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(Ticket $ticket = null)
    {
        try {
            // Si se proporciona un ticket específico
            if ($ticket) {
                $data = [
                    'ticket' => $ticket,
                    'title' => "Detalles del Ticket #{$ticket->id}"
                ];

                $pdf = PDF::loadView('pdf.ticket', $data);
                return $pdf->download('ticket-' . $ticket->id . '.pdf');
            }
            // Si no se proporciona un ticket, generar PDF con todos los tickets
            else {
                $tickets = Ticket::with(['user', 'assignedTo', 'status', 'unidadAcademica', 'building', 'office', 'equipment'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $data = [
                    'tickets' => $tickets,
                    'title' => 'Lista de Tickets'
                ];

                $pdf = PDF::loadView('pdf.tickets-list', $data);
                return $pdf->download('tickets-list.pdf');
            }
        } catch (\Exception $e) {
            // Registrar el error y devolver una respuesta amigable
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return back()->with('error', 'No se pudo generar el PDF. Por favor, inténtelo de nuevo.');
        }
    }
}
