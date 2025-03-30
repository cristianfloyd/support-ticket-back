<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
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
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                    ->maxSize(5120)
                    ->live()
                    ->afterStateUpdated(function (Get $get, Forms\Set $set, $state) {
                        try {
                            // Si no hay archivo seleccionado, no hacemos nada
                            if (!$state) {
                                return;
                            }
                            $fileName = $state->getFilename();
                            $set('filename', $fileName);
                            
                            // Obtenemos el nombre del archivo original
                            $originalName = $state->getClientOriginalName();
                            $set('original_name', $originalName);
                            
                            // Obtenemos el tipo MIME y tamaño del archivo
                            $filePath = $state->getRealPath();
                            if (file_exists($filePath)) {
                                $mimeType = $state->getMimeType();
                                $fileSize = $state->getSize();
                                
                                $set('mime_type', $mimeType);
                                $set('size', $fileSize);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error al procesar el archivo adjunto: ' . $e->getMessage());
                        }
                    }),
                Hidden::make('ticket_id')
                    ->default(state: fn() => $this->getOwnerRecord()->id),
                TextInput::make('original_name')->dehydrated(true),
                Hidden::make('filename')->dehydrated(true),
                Hidden::make('mime_type')->dehydrated(true),   
                Hidden::make('size')->dehydrated(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')
                    ->label('Nombre original'),
                Tables\Columns\TextColumn::make('filename')
                    ->label('Nombre del archivo')
                    ->searchable()
                    ->visible(fn() => auth()->guard('web')->user()->can('ticket_attachments.view_filename')),
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
                        try {
                            // Aseguramos que el ticket_id esté establecido
                            $data['ticket_id'] = $this->getOwnerRecord()->id;
                            
                            // Si por alguna razón los metadatos no se establecieron automáticamente,
                            // intentamos establecerlos aquí como respaldo
                            if (isset($data['path']) && (!isset($data['filename']) || !$data['filename'])) {
                                $data['filename'] = pathinfo($data['path'], PATHINFO_BASENAME);
                                
                                $filePath = Storage::disk('public')->path($data['path']);
                                if (file_exists($filePath)) {
                                    $data['mime_type'] ??= mime_content_type($filePath);
                                    $data['size'] ??= filesize($filePath);
                                }
                            }
                            
                            return $data;
                        } catch (\Exception $e) {
                            Log::error('Error al procesar datos del formulario: ' . $e->getMessage());
                            return $data;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                ->before(function ($record) {
                    try {
                        // Eliminar el archivo físico cuando se elimina el registro
                        if ($record->path && Storage::disk('public')->exists($record->path)) {
                            Storage::disk('public')->delete($record->path);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo: ' . $e->getMessage());
                    }
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records) {
                        try {
                            // Eliminar los archivos físicos cuando se eliminan los registros en masa
                            foreach ($records as $record) {
                                if ($record->path && Storage::disk('public')->exists($record->path)) {
                                    Storage::disk('public')->delete($record->path);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Error al eliminar archivos en masa: ' . $e->getMessage());
                        }
                    }),
                ]),
            ]);
    }
}
