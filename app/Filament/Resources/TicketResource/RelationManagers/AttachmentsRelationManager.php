<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Archivos Adjuntos';

    protected static ?string $recordTitleAttribute = 'filename';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('Archivo')
                    ->required()
                    ->disk('public')
                    ->directory('ticket-attachments')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                    ->maxSize(5120)
                    ->live()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                        if ($state) {
                            $set('filename', $state);
                            $set('ticket_id', $this->getOwnerRecord()->id);

                            // Obtener el mime_type y size del archivo temporal
                            $tmpFile = Storage::disk('public')->path($state);
                            if (file_exists($tmpFile)) {
                                $set('mime_type', mime_content_type($tmpFile));
                                $set('size', filesize($tmpFile));
                            } else {
                                dump('no existe');
                            }
                        }
                    }),
                Forms\Components\Hidden::make('ticket_id')
                    ->default(fn() => $this->getOwnerRecord()->id),
                Forms\Components\Hidden::make('filename'),
                Forms\Components\Hidden::make('mime_type'),
                Forms\Components\Hidden::make('size'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->label('Nombre del archivo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn(int $state): string => number_format($state / 1024, 2) . ' KB'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['ticket_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
