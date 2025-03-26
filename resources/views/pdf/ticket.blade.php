<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .ticket-info {
            margin-bottom: 20px;
        }
        .ticket-info div {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket #{{ $ticket->id }}</h1>
    </div>

    <div class="ticket-info">
        <div>
            <span class="label">Título:</span>
            {{ $ticket->title }}
        </div>
        <div>
            <span class="label">Estado:</span>
            {{ $ticket->status->name }}
        </div>
        <div>
            <span class="label">Prioridad:</span>
            {{ $ticket->priority->name }}
        </div>
        <div>
            <span class="label">Categoría:</span>
            {{ $ticket->category->name }}
        </div>
        <div>
            <span class="label">Creado por:</span>
            {{ $ticket->creator->name }}
        </div>
        <div>
            <span class="label">Asignado a:</span>
            {{ $ticket->assignedTo->name ?? 'Sin asignar' }}
        </div>
        <div>
            <span class="label">Fecha de creación:</span>
            {{ $ticket->created_at->format('d/m/Y H:i') }}
        </div>
        @if($ticket->resolved_at)
        <div>
            <span class="label">Fecha de resolución:</span>
            {{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d/m/Y H:i') }}
        </div>
        @endif
    </div>

    <div class="description">
        <h2>Descripción</h2>
        {!! $ticket->description !!}
    </div>
</body>
</html>