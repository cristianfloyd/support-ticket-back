<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain'])
                    ->maxSize(10240) // 10MB
                    ->storeFileNamesIn('filename')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            try {
                                $path = is_array($state) ? $state[0] : $state;
                                $fullPath = Storage::disk('public')->path($path);

                                if (file_exists($fullPath)) {
                                    $set('mime_type', mime_content_type($fullPath));
                                    $set('size', filesize($fullPath));
                                }
                            } catch (\Exception $e) {
                                // Registrar el error pero permitir que continúe
                                Log::error('Error al procesar archivo adjunto: ' . $e->getMessage());
                            }
                        }
                    }),
                Forms\Components\Hidden::make('mime_type'),
                Forms\Components\Hidden::make('size'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->label('Nombre del Archivo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match (true) {
                        str_contains($state, 'image/') => 'Imagen',
                        str_contains($state, 'pdf') => 'PDF',
                        str_contains($state, 'word') => 'Word',
                        str_contains($state, 'excel') => 'Excel',
                        str_contains($state, 'text/') => 'Texto',
                        default => 'Archivo',
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'image/') => 'success',
                        str_contains($state, 'pdf') => 'danger',
                        str_contains($state, 'word') => 'primary',
                        str_contains($state, 'excel') => 'warning',
                        str_contains($state, 'text/') => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn (int $state): string => round($state / 1024, 2) . ' KB'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Subida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model) {
                        // Procesamiento adicional de datos antes de guardar
                        try {
                            if (isset($data['path'])) {
                                $path = $data['path'];
                                $fullPath = Storage::disk('public')->path($path);

                                if (!isset($data['mime_type']) || !$data['mime_type']) {
                                    $data['mime_type'] = mime_content_type($fullPath);
                                }

                                if (!isset($data['size']) || !$data['size']) {
                                    $data['size'] = filesize($fullPath);
                                }
                            }

                            return $model::create($data);
                        } catch (\Exception $e) {
                            // Capturar y registrar cualquier error durante la creación
                            Log::error('Error al crear archivo adjunto: ' . $e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => asset('storage/' . $record->path))
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
