<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Equipment;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EquipmentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class EquipmentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Equipo';

    protected static ?string $pluralModelLabel = 'Equipos';

    protected static ?string $slug = 'equipos';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }

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
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Card::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Número de Serie')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('provider_id')
                                    ->label('Proveedor')
                                    ->relationship('provider', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20),
                                    ]),

                                Forms\Components\Select::make('office_id')
                                    ->label('Ubicación')
                                    ->relationship('office', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Section::make('Especificaciones')
                            ->schema([
                                Forms\Components\Textarea::make('specifications')
                                    ->label('Especificaciones Técnicas')
                                    ->rows(4)
                                    ->maxLength(1000),

                                Forms\Components\Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->rows(4)
                                    ->maxLength(1000),
                            ]),

                        Section::make('Fechas Importantes')
                            ->schema([
                                DatePicker::make('purchase_date')
                                    ->label('Fecha de Compra')
                                    ->required(),

                                DatePicker::make('warranty_expiration')
                                    ->label('Vencimiento de Garantía')
                                    ->required(),

                                DatePicker::make('last_maintenance')
                                    ->label('Último Mantenimiento'),

                                DatePicker::make('next_maintenance')
                                    ->label('Próximo Mantenimiento'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Número de Serie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('office.name')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warranty_expiration')
                    ->label('Garantía')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->warranty_expiration < now() ? 'danger' :
                        ($record->warranty_expiration < now()->addMonths(3) ? 'warning' : 'success')
                    ),

                Tables\Columns\TextColumn::make('next_maintenance')
                    ->label('Próximo Mantenimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->next_maintenance < now() ? 'danger' :
                        ($record->next_maintenance < now()->addWeek() ? 'warning' : 'success')
                    ),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label('Tickets')
                    ->counts('tickets')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->relationship('provider', 'name')
                    ->label('Proveedor')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->label('Ubicación')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('warranty_expired')
                    ->label('Garantía Vencida')
                    ->query(fn (Builder $query) => $query->where('warranty_expiration', '<', now())),

                Tables\Filters\Filter::make('maintenance_due')
                    ->label('Mantenimiento Pendiente')
                    ->query(fn (Builder $query) => $query->where('next_maintenance', '<', now())),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
