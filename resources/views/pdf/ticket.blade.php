<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .ticket-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="ticket-info">
        <table>
            <tr>
                <th>ID</th>
                <td>{{ $ticket->id }}</td>
            </tr>
            <tr>
                <th>Título</th>
                <td>{{ $ticket->title }}</td>
            </tr>
            <tr>
                <th>Descripción</th>
                <td>{{ $ticket->description }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ $ticket->status->name ?? 'No asignado' }}</td>
            </tr>
            <tr>
                <th>Creado por</th>
                <td>{{ $ticket->user->name ?? 'Usuario desconocido' }}</td>
            </tr>
            <tr>
                <th>Asignado a</th>
                <td>{{ $ticket->assignedTo->name ?? 'No asignado' }}</td>
            </tr>
            <tr>
                <th>Unidad Académica</th>
                <td>{{ $ticket->unidadAcademica->name ?? 'No especificada' }}</td>
            </tr>
            <tr>
                <th>Edificio</th>
                <td>{{ $ticket->building->name ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Oficina</th>
                <td>{{ $ticket->office->name ?? 'No especificada' }}</td>
            </tr>
            <tr>
                <th>Equipo</th>
                <td>{{ $ticket->equipment->name ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Resuelto</th>
                <td>{{ $ticket->is_resolved ? 'Sí' : 'No' }}</td>
            </tr>
            <tr>
                <th>Fecha de resolución</th>
                <td>{{ $ticket->resolved_at ?? 'Pendiente' }}</td>
            </tr>
            <tr>
                <th>Creado el</th>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Actualizado el</th>
                <td>{{ $ticket->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
