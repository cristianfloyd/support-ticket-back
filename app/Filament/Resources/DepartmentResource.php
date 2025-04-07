<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\DepartmentService;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\DepartmentResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\DepartmentResource\Widgets\DepartmentTicketsOverview;
use App\Filament\Resources\DepartmentResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\DepartmentResource\RelationManagers\TicketsRelationManager;

class DepartmentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $departmentService = app(DepartmentService::class);
            return (string) $departmentService->getAllDepartments()->count();
        } catch (\Exception $e) {
            Log::error('Error al obtener el contador de departamentos: ' . $e->getMessage());
            return null;
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Departamento')
                    ->description('Datos básicos del departamento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nombre del departamento'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->required()
                                    ->helperText('Determina si el departamento está activo en el sistema'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Descripción breve del departamento y sus funciones')
                            ->helperText('Máximo 500 caracteres'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label('Tickets')
                    ->counts('tickets')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueLabel('Departamentos activos')
                    ->falseLabel('Departamentos inactivos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Departamento actualizado correctamente'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Departamento eliminado correctamente')
                    ->before(function (Tables\Actions\DeleteAction $action, Department $record) {
                        $departmentService = app(DepartmentService::class);
                        
                        if ($departmentService->hasDepartmentUsers($record->id)) {
                            $action->cancel();
                            $action->failureNotificationTitle('No se puede eliminar el departamento');
                            $action->failureNotification(fn() => 'El departamento tiene usuarios asignados. Reasigne los usuarios antes de eliminar.');
                        }
                        
                        if ($departmentService->hasDepartmentTickets($record->id)) {
                            $action->cancel();
                            $action->failureNotificationTitle('No se puede eliminar el departamento');
                            $action->failureNotification(fn() => 'El departamento tiene tickets asociados. Reasigne los tickets antes de eliminar.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotificationTitle('Departamentos eliminados correctamente')
                        ->before(function (Tables\Actions\DeleteBulkAction $action, Collection $records) {
                            $departmentService = app(DepartmentService::class);
                            $hasRelatedData = false;
                            
                            foreach ($records as $record) {
                                if ($departmentService->hasDepartmentUsers($record->id) || 
                                    $departmentService->hasDepartmentTickets($record->id)) {
                                    $hasRelatedData = true;
                                    break;
                                }
                            }
                            
                            if ($hasRelatedData) {
                                $action->cancel();
                                $action->failureNotificationTitle('No se pueden eliminar algunos departamentos');
                                $action->failureNotification(fn() => 'Algunos departamentos tienen usuarios o tickets asociados. Reasigne antes de eliminar.');
                            }
                        }),
                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            try {
                                $departmentService = app(DepartmentService::class);
                                $ids = $records->pluck('id')->toArray();
                                $departmentService->bulkToggleDepartmentsActive($ids, true);
                            } catch (\Exception $e) {
                                Log::error('Error al activar departamentos: ' . $e->getMessage());
                                throw $e;
                            }
                        })
                        ->successNotificationTitle('Departamentos activados correctamente'),
                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            try {
                                $departmentService = app(DepartmentService::class);
                                $ids = $records->pluck('id')->toArray();
                                $departmentService->bulkToggleDepartmentsActive($ids, false);
                            } catch (\Exception $e) {
                                Log::error('Error al desactivar departamentos: ' . $e->getMessage());
                                throw $e;
                            }
                        })
                        ->successNotificationTitle('Departamentos desactivados correctamente'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            TicketsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            DepartmentTicketsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
