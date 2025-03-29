<?php

namespace App\Filament\Resources\ProviderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EquipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipments';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Equipos';

    protected static ?string $modelLabel = 'Equipo';

    protected static ?string $pluralModelLabel = 'Equipos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Número de Serie')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('office_id')
                    ->label('Oficina')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Textarea::make('specifications')
                    ->label('Especificaciones')
                    ->rows(3)
                    ->maxLength(500),

                Forms\Components\DatePicker::make('purchase_date')
                    ->label('Fecha de Compra')
                    ->required(),

                Forms\Components\DatePicker::make('warranty_expiration')
                    ->label('Vencimiento de Garantía')
                    ->required(),

                Forms\Components\DatePicker::make('last_maintenance')
                    ->label('Último Mantenimiento'),

                Forms\Components\DatePicker::make('next_maintenance')
                    ->label('Próximo Mantenimiento'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Número de Serie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('office.name')
                    ->label('Oficina')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Compra')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('warranty_expiration')
                    ->label('Garantía')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_maintenance')
                    ->label('Próx. Mant.')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label('Tickets')
                    ->counts('tickets')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->label('Oficina')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('active')
                    ->label('Activos')
                    ->query(fn (Builder $query) => $query->where('is_active', true))
                    ->default(),

                Tables\Filters\Filter::make('warranty_expired')
                    ->label('Garantía Vencida')
                    ->query(fn (Builder $query) => $query->where('warranty_expiration', '<', now())),

                Tables\Filters\Filter::make('maintenance_due')
                    ->label('Mantenimiento Pendiente')
                    ->query(fn (Builder $query) => $query->where('next_maintenance', '<', now())),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
