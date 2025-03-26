<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use Filament\Tables;
use App\Models\Ticket;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingTicketsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->where('is_resolved', false)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => $record->status->color ?? 'gray'),

                Tables\Columns\TextColumn::make('priority.name')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn ($record) => $record->priority->color ?? 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->limit(20),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Asignado a')
                    ->default('Sin asignar')
                    ->limit(20),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->url(fn (Ticket $record): string => route('filament.admin.resources.tickets.view', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
