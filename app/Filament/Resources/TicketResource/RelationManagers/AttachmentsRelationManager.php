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
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Archivos Adjuntos';

    // Importante: Definimos la colección por defecto
    protected static string $collectionName = 'attachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Configuración correcta del componente SpatieMediaLibraryFileUpload
                SpatieMediaLibraryFileUpload::make('attachments')
                    ->collection(static::$collectionName) // Usamos la propiedad estática
                    ->multiple()
                    ->maxFiles(10)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.ms-excel'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->openable()
                    ->directory('ticket-attachments')
                    ->disk('public')
                    ->required()
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
                    ->collection(static::$collectionName) // Usamos la propiedad estática
                    ->conversion('thumb')
                    ->hidden(fn ($record) => !str_contains($record->mime_type, 'image')),
                
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
                // Necesitamos volver a agregar using() pero de forma más simple
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data): Model {
                        try {
                            $ticket = $this->getOwnerRecord();
                            
                            // Creamos un registro de media directamente con la colección
                            $media = $ticket->media()->create([
                                'collection_name' => static::$collectionName,
                                'name' => $data['attachments'][0] ?? 'Archivo sin nombre',
                                'file_name' => $data['attachments'][0] ?? 'archivo.txt',
                            ]);
                            
                            return $media;
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
                    ->url(fn ($record) => $record->getUrl())
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
}
