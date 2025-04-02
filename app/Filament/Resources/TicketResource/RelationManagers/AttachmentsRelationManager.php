<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Archivos Adjuntos';

    // Definimos la colección por defecto
    protected static string $collectionName = 'attachments';

    /**
     * Define el formulario para agregar/editar archivos adjuntos
     * 
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Configuración del componente SpatieMediaLibraryFileUpload
                SpatieMediaLibraryFileUpload::make('attachments')
                    ->collection(static::$collectionName)
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

    /**
     * Define la tabla para mostrar los archivos adjuntos
     * 
     * @param Table $table
     * @return Table
     */
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
                    ->collection(static::$collectionName)
                    ->conversion('thumb')
                    ->hidden(fn ($record) => !MediaService::isImage($record)),
                
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => MediaService::formatMimeType($state)),
                
                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => MediaService::formatFileSize($state)),
                
                Tables\Columns\TextColumn::make('created_at')
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
                        $ticket = $this->getOwnerRecord();
                        
                        if (empty($data['attachments'])) {
                            throw new \Exception('No se han proporcionado archivos para adjuntar.');
                        }

                        // Procesar cada archivo
                        foreach ($data['attachments'] as $file) {
                            $ticket->addMedia($file)
                                ->toMediaCollection(static::$collectionName);
                        }

                        return $ticket->media()->latest()->first();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => MediaService::getMediaUrl($record) ?? '#')
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
