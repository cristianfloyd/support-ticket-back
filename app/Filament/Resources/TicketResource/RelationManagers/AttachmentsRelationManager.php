<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Archivos Adjuntos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Usamos el componente FileUpload estándar en lugar de SpatieMediaLibraryFileUpload
                FileUpload::make('attachments')
                    ->multiple()
                    ->maxFiles(10)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->openable()
                    ->directory('ticket-attachments')
                    ->disk('public')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nombre del archivo')
                    ->searchable()
                    ->sortable(),
                
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('Vista previa')
                    ->collection('attachments')
                    ->conversion('thumb')
                    ->visibleOn('image/*'),
                
                Tables\Columns\TextColumn::make('mime_type')
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
                    ->using(function (array $data): Model {
                        try {
                            Log::info('Datos recibidos en using:', $data);
                            
                            $ticket = $this->getOwnerRecord();
                            
                            // Si no hay archivos, lanzamos una excepción
                            if (!isset($data['attachments']) || empty($data['attachments'])) {
                                throw new \Exception('No se han proporcionado archivos');
                            }
                            
                            // Procesamos cada archivo manualmente
                            $files = $data['attachments'];
                            $firstMedia = null;
                            
                            foreach ($files as $file) {
                                $filePath = storage_path('app/public/' . $file);
                                
                                // Verificamos que el archivo existe
                                if (!file_exists($filePath)) {
                                    Log::warning("El archivo no existe: {$filePath}");
                                    continue;
                                }
                                
                                // Añadimos el archivo a la colección 'attachments'
                                $media = $ticket->addMedia($filePath)
                                    ->toMediaCollection('attachments');
                                
                                // Guardamos la primera media para devolverla
                                if (!$firstMedia) {
                                    $firstMedia = $media;
                                }
                            }
                            
                            // Si no se ha podido añadir ningún archivo, lanzamos una excepción
                            if (!$firstMedia) {
                                throw new \Exception('No se ha podido añadir ningún archivo');
                            }
                            
                            return $firstMedia;
                        } catch (\Exception $e) {
                            Log::error('Error al procesar archivos: ' . $e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->getUrl())
                    ->openUrlInNewTab(),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
