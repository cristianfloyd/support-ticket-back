<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Attachment;
use Filament\Tables\Table;
use App\Services\MediaService;
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
    protected static string $collectionName = 'file';

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
                        Forms\Components\FileUpload::make('files')
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
                            $createdAttachment = null;

                            // Crear un nuevo attachment para cada archivo
                            foreach ($files as $file) {
                                $filePath = Storage::disk('public')->path($file);

                                // Crear el registro de attachment con datos básicos
                                $attachment = Attachment::create([
                                    'ticket_id' => $ticket->id,
                                    'filename' => basename($file),
                                    'original_name' => basename($file),
                                    'path' => $filePath,
                                    'mime_type' => 'application/octet-stream', // Valor temporal
                                    'size' => filesize($filePath)
                                ]);

                                // Usar MediaService para añadir el archivo a la coleccion
                                $media = MediaService::addMedia(
                                    $attachment,
                                    $filePath,
                                    'file',
                                    ['ticket_id' => $ticket->id ]
                                );

                                // Actualizar el attachment con la información del media
                                if ($media) {
                                    $attachment->update([
                                        'path' => $media->getPath(),
                                        'mime_type' => $media->mime_type,
                                        'size' => $media->size
                                    ]);
                                }

                                $createdAttachment = $attachment;
                                Log::info($createdAttachment);
                                
                                return $createdAttachment ?? Attachment::create([
                                    'ticket_id' => $ticket->id,
                                    'filename' => 'placeholder',
                                    'original_name' => 'placeholder',
                                    'path' => 'placeholder',
                                    'mime_type' => 'text/plain',
                                    'size' => 0
                                ]);
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

                Tables\Actions\DeleteAction::make()
                    ->before( function(Model $record) {
                        $media = $record->getFirstMedia('file');
                        if($media){
                            MediaService::deleteMedia($media);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function(array $records){
                            foreach($records as $record) {
                                $media = $record->getFirstMedia('file');
                                if($media){
                                    MediaService::deleteMedia($media);
                                }
                            }
                        }),
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

        return MediaService::isImage($file);
    }
}
