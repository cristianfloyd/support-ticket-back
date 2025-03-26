<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Ticket;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Resources\TicketResource\Pages\CreateTicket;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\TicketResource\Widgets\TicketStatsWidget;
use App\Filament\Resources\TicketResource\Widgets\PendingTicketsWidget;
use App\Filament\Resources\TicketResource\Widgets\ResolutionTimeWidget;
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\TicketResource\RelationManagers\AttachmentsRelationManager;

class TicketResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Soporte Técnico';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Ticket';
    protected static ?string $pluralModelLabel = 'Tickets';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_resolved', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_resolved', false)->count() > 10
            ? 'danger'
            : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Ticket')
                    ->tabs([
                        Tab::make('Información Principal')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                MarkdownEditor::make('description')
                                    ->label('Descripción')
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('status_id')
                                    ->label('Estado')
                                    ->relationship('status', 'name')
                                    ->preload()
                                    ->required(),

                                Select::make('priority_id')
                                    ->label('Prioridad')
                                    ->relationship('priority', 'name')
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->required(),

                                Select::make('assigned_to')
                                    ->label('Asignado a')
                                    ->relationship('assignedTo', 'name')
                                    ->preload()
                                    ->searchable(),

                                Select::make('unidad_academica_id')
                                    ->label('Unidad Académica')
                                    ->relationship('unidadAcademica', 'name')
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('building_id', null)),

                                Select::make('building_id')
                                    ->label('Edificio')
                                    ->relationship('building', 'name', function ($query, $get) {
                                        $unidadAcademicaId = $get('unidad_academica_id');
                                        if ($unidadAcademicaId) {
                                            return $query->where('unidad_academica_id', $unidadAcademicaId);
                                        }
                                        return $query;
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('office_id', null)),

                                Select::make('office_id')
                                    ->label('Oficina')
                                    ->relationship('office', 'name', function ($query, $get) {
                                        $buildingId = $get('building_id');
                                        if ($buildingId) {
                                            return $query->where('building_id', $buildingId);
                                        }
                                        return $query;
                                    })
                                    ->preload()
                                    ->searchable(),

                                Select::make('equipment_id')
                                    ->label('Equipo')
                                    ->relationship('equipment', 'name')
                                    ->preload()
                                    ->searchable(),

                                Toggle::make('is_resolved')
                                    ->label('Resuelto')
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $set('resolved_at', now()->format('Y-m-d H:i:s'));
                                        } else {
                                            $set('resolved_at', null);
                                        }
                                    }),
                            ])->columns(2),

                        Tab::make('Comentarios')
                            ->schema([
                                // Los comentarios se gestionan a través del RelationManager
                            ]),

                        Tab::make('Archivos Adjuntos')
                            ->schema([
                                // Los archivos adjuntos se gestionan a través del RelationManager
                            ]),

                        Tab::make('Historial')
                            ->schema([
                                // Aquí se podría implementar un historial de cambios del ticket
                            ]),
                    ])->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => $record->status->color ?? 'gray'),

                TextColumn::make('priority.name')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn ($record) => $record->priority->color ?? 'gray'),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),

                TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('assignedTo.name')
                    ->label('Asignado a')
                    ->sortable()
                    ->searchable()
                    ->default('Sin asignar'),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Fecha de Resolución')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->label('Estado')
                    ->relationship('status', 'name'),

                SelectFilter::make('priority_id')
                    ->label('Prioridad')
                    ->relationship('priority', 'name'),

                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                SelectFilter::make('unidad_academica_id')
                    ->label('Unidad Académica')
                    ->relationship('unidadAcademica', 'name'),

                SelectFilter::make('assigned_to')
                    ->label('Asignado a')
                    ->relationship('assignedTo', 'name'),

                Tables\Filters\TernaryFilter::make('is_resolved')
                    ->label('Resuelto')
                    ->trueLabel('Resueltos')
                    ->falseLabel('Pendientes')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('resolve')
                    ->label('Resolver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_resolved)
                    ->action(function (Ticket $record) {
                        $record->update([
                            'is_resolved' => true,
                            'resolved_at' => now(),
                        ]);
                    }),
                Action::make('reopen')
                    ->label('Reabrir')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_resolved)
                    ->action(function (Ticket $record) {
                        $record->update([
                            'is_resolved' => false,
                            'resolved_at' => null,
                        ]);
                    }),
                Action::make('generatePdf')
                    ->label('Generar PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn ($record) => route('tickets.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('resolveMany')
                        ->label('Resolver seleccionados')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if (!$record->is_resolved) {
                                    $record->update([
                                        'is_resolved' => true,
                                        'resolved_at' => now(),
                                    ]);
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
            'view' => ViewTicket::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TicketStatsWidget::class,
            PendingTicketsWidget::class,
            ResolutionTimeWidget::class,
        ];
    }
}
