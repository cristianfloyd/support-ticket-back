<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Attachment;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Archivos Adjuntos';

    // Definimos la colección por defecto
    protected static string $collectionName = 'attachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('files')
                    ->label('Archivos')
                    ->multiple()
                    ->maxFiles(10)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.ms-excel'])
                    ->maxSize(5120)
                    ->directory('ticket-attachments')
                    ->disk('public')
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('filename')
                    ->label('Nombre del archivo')
                    ->searchable()
                    ->sortable(),

                ViewColumn::make('thumbnail')
                    ->label('Vista previa')
                    ->view('filament.tables.columns.attachment-thumbnail')
                    ->visible(fn($record) => !$this->isImageAttachment($record)),


                TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->formatStateUsing(function ($state) {
                        if (str_contains($state, 'image')) {
                            return 'Imagen';
                        } elseif (str_contains($state, 'pdf')) {
                            return 'PDF';
                        } elseif (str_contains($state, 'word') || str_contains($state, 'msword')) {
                            return 'Word';
                        } elseif (str_contains($state, 'excel') || str_contains($state, 'spreadsheet')) {
                            return 'Excel';
                        } else {
                            return explode('/', $state)[1] ?? $state;
                        }
                    }),

                TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn(int $state): string => number_format($state / 1024, 2) . ' KB'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->maxFiles(10)
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.ms-excel'])
                            ->maxSize(5120)
                            ->directory('ticket-attachments')
                            ->disk('public')
                            ->required()
                    ])
                    ->using(function (array $data): Model {
                        try {
                            $ticket = $this->getOwnerRecord();
                            $files = $data['files'] ?? [];

                            // Crear un nuevo attachment para cada archivo
                            foreach ($files as $file) {
                                $filePath = Storage::disk('public')->path($file);

                                // Obtener el tipo MIME de manera segura
                                $mimeType = null;
                                if (function_exists('mime_content_type')) {
                                    $mimeType = mime_content_type($filePath);
                                } else {
                                    // Alternativa usando la extensión del archivo
                                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                    $mimeTypes = [
                                        'pdf' => 'application/pdf',
                                        'jpg' => 'image/jpeg',
                                        'jpeg' => 'image/jpeg',
                                        'png' => 'image/png',
                                        'doc' => 'application/msword',
                                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'xls' => 'application/vnd.ms-excel',
                                        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        // Añadir más tipos según sea necesario
                                    ];
                                    $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
                                }

                                // Crear el registro de attachment
                                $attachment = Attachment::create([
                                    'ticket_id' => $ticket->id,
                                    'filename' => basename($file),
                                    'original_name' => basename($file),
                                    'path' => $filePath,
                                    'mime_type' => $mimeType,
                                    'size' => filesize($filePath)
                                ]);

                                // Agregar el archivo a la colección de medios del attachment
                                $attachment->addMediaFromDisk($file, 'public')
                                    ->toMediaCollection('file');

                                // Actualizar el attachment con la información del media
                                $media = $attachment->getFirstMedia('file');
                                if ($media) {
                                    $attachment->update([
                                        'path' => $media->getPath(),
                                        'mime_type' => $media->mime_type,
                                        'size' => $media->size
                                    ]);
                                }
                            }

                            // Devolver el primer attachment creado (o crear uno vacío si no hay archivos)
                            return $attachment ?? Attachment::create([
                                'ticket_id' => $ticket->id,
                                'filename' => 'placeholder',
                                'original_name' => 'placeholder',
                                'path' => 'placeholder',
                                'mime_type' => 'text/plain',
                                'size' => 0
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error al crear archivo: ' . $e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => $record->file_url)
                    ->visible(fn($record) => $record->file_url !== null)
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function isImageAttachment($record): bool
    {
        if ($record === null) {
            return false;
        }

        $file = $record->getFile();
        if (!$file) {
            return false;
        }

        return str_contains($file->mime_type, 'image');
    }
}
