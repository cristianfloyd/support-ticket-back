<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\RelationManagers\RelationManager;

class CommentsRelationManager extends RelationManager
{
    // Definición de la relación con el modelo principal
    protected static string $relationship = 'comments';

    // Título descriptivo para la sección de comentarios
    protected static ?string $title = 'Comentarios';

    // Atributo que se usará como título para cada registro
    protected static ?string $recordTitleAttribute = 'id';

    /**
     * Define el formulario para crear/editar comentarios
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Editor de Markdown para el contenido del comentario
                Forms\Components\MarkdownEditor::make('content')
                    ->label('Contenido')
                    ->required()
                    ->columnSpan(2),

                // Campo oculto para almacenar el ID del usuario actual
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    /**
     * Define la tabla para mostrar los comentarios
     *
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                // Columna para mostrar el nombre del usuario que creó el comentario
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable(),

                // Columna para mostrar el contenido del comentario (limitado a 100 caracteres)
                Tables\Columns\TextColumn::make('content')
                    ->label('Comentario')
                    ->limit(100)
                    ->html(), // Permitir renderizado HTML para el markdown

                // Columna para mostrar la fecha de creación del comentario
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Espacio para añadir filtros si son necesarios
            ])
            ->headerActions([
                // Acción para crear un nuevo comentario
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model, RelationManager $livewire) {
                        try {
                            // Asegurarse de que el user_id esté establecido
                            if (!isset($data['user_id']) || !$data['user_id']) {
                                $data['user_id'] = Auth::id();
                            }

                            // Obtener el ID del ticket (modelo padre) desde el RelationManager
                            $ticketId = $livewire->getOwnerRecord()->getKey();

                            // Asegurarse de que el ticket_id esté establecido
                            $data['ticket_id'] = $ticketId;

                            // Crear el comentario con los datos proporcionados
                            return $model::create($data);
                        } catch (\Exception $e) {
                            // Registrar cualquier error que ocurra durante la creación
                            Log::error('Error al crear comentario: ' . $e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                // Acción para editar un comentario (solo visible para el autor)
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === Auth::id()),

                // Acción para eliminar un comentario (solo visible para el autor)
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === Auth::id()),
            ])
            ->bulkActions([
                // Grupo de acciones en masa
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
