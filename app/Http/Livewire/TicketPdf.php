<?php

namespace App\Http\Livewire;

use App\Models\Ticket;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class TicketPdf extends Component
{
    public $ticketId;

    public function mount($ticketId = null)
    {
        $this->ticketId = $ticketId;
    }

    public function generatePdf()
    {
        try {
            if ($this->ticketId) {
                $ticket = Ticket::findOrFail($this->ticketId);
                $data = [
                    'ticket' => $ticket,
                    'title' => 'Detalles del Ticket #' . $ticket->id
                ];

                $pdf = PDF::loadView('pdf.ticket', $data);
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'ticket-' . $this->ticketId . '.pdf');
            } else {
                $tickets = Ticket::with(['user', 'assignedTo', 'status', 'unidadAcademica', 'building', 'office', 'equipment'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $data = [
                    'tickets' => $tickets,
                    'title' => 'Lista de Tickets'
                ];

                $pdf = PDF::loadView('pdf.tickets-list', $data);
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'tickets-list.pdf');
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('error', ['message' => 'No se pudo generar el PDF. Por favor, inténtelo de nuevo.']);
            Log::error('Error al generar PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.ticket-pdf');
    }
}
