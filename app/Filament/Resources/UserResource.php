<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Widgets\UserStatsWidget;
use App\Filament\Resources\UserResource\Widgets\UserActivityWidget;
use App\Filament\Resources\UserResource\RelationManagers\TicketsCreatedRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\TicketsAssignedRelationManager;

class UserResource extends Resource
{
    // Definimos el modelo asociado al recurso
    protected static ?string $model = User::class;

    // Icono que se mostrará en el panel de navegación
    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Grupo de navegación donde aparecerá este recurso
    protected static ?string $navigationGroup = 'Administración';

    // Prioridad en el menú de navegación
    protected static ?int $navigationSort = 1;

    // Etiqueta para el recurso en singular y plural
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';

    // Verificar permisos para acceder al recurso
    public static function canAccess(): bool
    {
        // return Auth::user()->can('view_any_user');
        return true;
    }

    // Verificar permisos para crear
    public static function canCreate(): bool
    {
        // return Auth::user()->can('create_user');
        return true;

    }

    // Verificar permisos para editar
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // return Auth::user()->can('update_user');
        return true;

    }

    // Verificar permisos para eliminar
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // return Auth::user()->can('delete_user');
        return true;

    }


    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // return Auth::user()->can('view_user');
        return true;

    }

    /**
     * Obtiene las páginas de navegación para este recurso
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    /**
     * Implementación de políticas de autorización
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // No permitir que un usuario se edite a sí mismo (excepto administradores)
        // if (!auth()->user()->hasRole('admin')) {
        //     $query->whereNot('id', Auth::id());
        // }

        // Filtrar usuarios según permisos
        // if (Auth::user()->hasRole('manager') && !Auth::user()->hasRole('admin')) {
        //     // Los managers solo pueden ver usuarios de su departamento
        //     $query->where('department_id', Auth::user()->department_id);
        // }

        return $query;
    }

    /**
     * Define la estructura del formulario para crear/editar usuarios
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Información del Usuario')
                    ->tabs([
                        Tab::make('Información Básica')
                            ->schema([
                                FileUpload::make('profile_photo')
                                    ->label('Foto de Perfil')
                                    ->image()
                                    ->avatar()
                                    ->directory('profile-photos')
                                    ->maxSize(1024)
                                    ->columnSpan(2),

                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Select::make('department_id')
                                    ->label('Departamento')
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Estado Activo')
                                    ->default(true),
                            ])->columns(2),

                        Tab::make('Seguridad')
                            ->schema([
                                TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(8)
                                    ->rule('regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
                                    ->helperText('Mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.')
                                    ->columnSpan(1),

                                TextInput::make('password_confirmation')
                                    ->label('Confirmar Contraseña')
                                    ->password()
                                    ->dehydrated(false)
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->same('password')
                                    ->visible(fn ($get) => $get('password') !== null)
                                    ->columnSpan(1),

                                Select::make('roles')
                                    ->label('Roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(2),

                                Toggle::make('two_factor_enabled')
                                    ->label('Autenticación de dos factores')
                                    ->helperText('Requiere configuración adicional por parte del usuario')
                                    ->columnSpan(2),

                                Card::make()
                                    ->schema([
                                        Toggle::make('account_locked')
                                            ->label('Bloquear cuenta')
                                            ->helperText('Bloquea temporalmente el acceso a esta cuenta'),

                                        TextInput::make('failed_login_attempts')
                                            ->label('Intentos fallidos de inicio de sesión')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Se reinicia automáticamente después de un inicio de sesión exitoso'),
                                    ])
                                    ->columns(2)
                                    ->columnSpan(2),
                            ])->columns(2),

                        Tab::make('Información de Contacto')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('extension')
                                    ->label('Extensión')
                                    ->maxLength(10),

                                TextInput::make('position')
                                    ->label('Cargo')
                                    ->maxLength(100),

                                Textarea::make('address')
                                    ->label('Dirección')
                                    ->rows(3)
                                    ->columnSpan(2),
                            ])->columns(2),

                        Tab::make('Preferencias')
                            ->schema([
                                CheckboxList::make('notification_preferences')
                                    ->label('Preferencias de Notificación')
                                    ->options([
                                        'email' => 'Correo Electrónico',
                                        'sms' => 'SMS',
                                        'push' => 'Notificaciones Push',
                                        'in_app' => 'Notificaciones en la Aplicación',
                                    ])
                                    ->default(['email', 'in_app'])
                                    ->columnSpan(2),

                                Select::make('language')
                                    ->label('Idioma')
                                    ->options([
                                        'es' => 'Español',
                                        'en' => 'Inglés',
                                    ])
                                    ->default('es'),

                                Select::make('theme')
                                    ->label('Tema')
                                    ->options([
                                        'light' => 'Claro',
                                        'dark' => 'Oscuro',
                                        'system' => 'Sistema',
                                    ])
                                    ->default('system'),
                            ])->columns(2),
                    ])->columnSpan('full'),
            ]);
    }

    /**
     * Define la estructura de la tabla para listar usuarios
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn ($record) => $record->hasRole('admin') ? 'danger' : 'primary'),

                ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->label('Departamento'),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos los estados')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Rol'),

                TernaryFilter::make('account_locked')
                    ->label('Cuenta bloqueada')
                    ->placeholder('Todas las cuentas')
                    ->trueLabel('Solo bloqueadas')
                    ->falseLabel('Solo desbloqueadas')
                    ->queries(
                        true: fn (Builder $query) => $query->where('account_locked', true),
                        false: fn (Builder $query) => $query->where('account_locked', false)->orWhereNull('account_locked'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('resetPassword')
                    ->label('Restablecer Contraseña')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('password')
                            ->label('Nueva Contraseña')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->rule('regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->required()
                            ->same('password'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => Hash::make($data['password']),
                            'failed_login_attempts' => 0,
                            'account_locked' => false,
                        ]);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activar')
                        ->label('Activar Usuarios')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('desactivar')
                        ->label('Desactivar Usuarios')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('desbloquear')
                        ->label('Desbloquear Cuentas')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update([
                            'account_locked' => false,
                            'failed_login_attempts' => 0,
                        ]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Define las páginas relacionadas con este recurso
     */
    public static function getRelations(): array
    {
        return [
            TicketsCreatedRelationManager::class,
            TicketsAssignedRelationManager::class,
        ];
    }

    /**
     * Define las páginas disponibles para este recurso
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
            'view'   => ViewUser::route('/{record}'),
        ];
    }

    /**
     * Define los widgets disponibles para este recurso
     */
    public static function getWidgets(): array
    {
        return [
            UserStatsWidget::class,
            UserActivityWidget::class,
        ];
    }
}
