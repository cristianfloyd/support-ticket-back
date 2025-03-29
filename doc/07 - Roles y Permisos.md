# 7. Roles y Permisos

## 7.1 Estructura de Roles

En nuestra aplicación, utilizamos Filament Shield para gestionar los roles y permisos. La estructura de roles está diseñada para proporcionar diferentes niveles de acceso y responsabilidades:

### Roles Principales

1. **Admin (Administrador)**
   - Acceso completo al sistema
   - Puede gestionar todos los recursos
   - Puede asignar roles y permisos
   - Tiene acceso a configuraciones del sistema

2. **Supervisor**
   - Gestión de equipos y recursos
   - Acceso a reportes y estadísticas
   - Puede asignar tickets
   - Puede gestionar usuarios básicos
   - Permisos específicos:
     - CRUD completo en equipos
     - CRUD completo en proveedores
     - Gestión de estados y prioridades
     - Vista y edición de categorías

3. **Agent (Agente)**
   - Gestión de tickets asignados
   - Vista de recursos relacionados
   - Permisos específicos:
     - Ver y actualizar tickets
     - Ver equipos y oficinas
     - Ver categorías y estados
     - Ver proveedores

4. **User (Usuario)**
   - Creación de tickets
   - Vista de sus propios tickets
   - Vista limitada de recursos
   - Permisos específicos:
     - Crear tickets
     - Ver estado de sus tickets
     - Ver información básica del sistema

- Jerarquía de roles

```graph TD
    A[Admin] --> B[Supervisor]
    B --> C[Agent]
    C --> D[User]
```

## 7.2 Permisos por Recurso

### Matriz de Permisos Base

Cada recurso en Filament tiene los siguientes permisos base:

```php
[
    'view_any',       // Ver listado
    'view',          // Ver detalle
    'create',        // Crear nuevo
    'update',        // Actualizar
    'delete',        // Eliminar
    'delete_any',    // Eliminar múltiples
    'restore',       // Restaurar (soft deletes)
    'restore_any',   // Restaurar múltiples
    'force_delete',  // Eliminación permanente
    'force_delete_any' // Eliminación permanente múltiple
]
```

## 7.3 Configuración en RolePermissionSeeder

### Estructura Principal del Seeder

```php
public function run(): void
{
    // Generar permisos de FilamentShield
    Artisan::call('shield:generate', ['panel' => 'admin']);

    // Crear roles
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
    $agentRole = Role::firstOrCreate(['name' => 'agent']);
    $userRole = Role::firstOrCreate(['name' => 'user']);

    // Asignar permisos específicos a cada rol
    $adminRole->syncPermissions(Permission::all());
    $supervisorRole->syncPermissions($supervisorPermissions);
    $agentRole->syncPermissions($agentPermissions);
    $userRole->syncPermissions($userPermissions);
}
```

### Permisos Personalizados

```php
$customPermissions = [
    'assign_ticket',
    'change_status_ticket',
    'change_priority_ticket',
    'impersonate_user'
];
```

## 7.4 Gestión de Accesos

### Verificación de Permisos en Recursos

Los permisos en los recursos Filament se pueden verificar de varias maneras:

1\. **Usando Policies**

```php
class EquipmentPolicy
{
    public function view(User $user, Equipment $equipment): bool
    {
        return $user->can('view_equipment');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_equipment');
    }
}
```

2\. **En los Recursos Filament**

```php
class EquipmentResource extends Resource
{
    public static function canCreate(): bool
    {
        return auth()->user()->can('create_equipment');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update_equipment');
    }
}
```

3\. **En las Acciones**

```php
public function getActions(): array
{
    return [
        Actions\DeleteAction::make()
            ->visible(fn () => auth()->user()->can('delete_equipment')),
    ];
}
```

### Restricción de Acciones por Rol

#### 1. Navegación Condicional

```php
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->hasAnyRole(['admin', 'supervisor']);
}
```

#### 2. Filtrado de Registros

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // Los usuarios solo ven sus propios tickets
    if (auth()->user()->hasRole('user')) {
        $query->where('user_id', auth()->id());
    }

    return $query;
}
```

#### 3. Campos Condicionales

```php
public function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('title')
                ->required(),
            Forms\Components\Select::make('status_id')
                ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'supervisor'])),
        ]);
}
```

### Políticas de Acceso (Policies)

Las políticas proporcionan una forma organizada de autorizar acciones:

```php
class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('user')) {
            return $user->id === $ticket->user_id;
        }
        
        return $user->can('view_ticket');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('agent')) {
            return $ticket->assigned_to === $user->id;
        }
        
        return $user->can('update_ticket');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyRole(['admin', 'supervisor']);
    }
}
```

### Registro de Políticas

Registra las políticas en `AuthServiceProvider`:

```php
protected $policies = [
    Equipment::class => EquipmentPolicy::class,
    Ticket::class => TicketPolicy::class,
];
```

### Middleware de Roles

Protege rutas específicas usando middleware:

```php
Route::middleware(['role:admin|supervisor'])->group(function () {
    // Rutas protegidas
});
```

O en controladores:

```php
public function __construct()
{
    $this->middleware('role:admin|supervisor')->only(['create', 'store', 'edit', 'update']);
}
```

## 7.5 Mejores Prácticas

### Recomendaciones de Seguridad

1. **Principio de Mínimo Privilegio**
   - Asignar solo los permisos estrictamente necesarios
   - Revisar periódicamente los permisos asignados
   - Revocar permisos innecesarios

2. **Auditoría de Acciones**

```php
// Modelo con registro de acciones
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status_id', 'priority_id', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

3\. **Validación de Roles Críticos**

```php
public function someImportantAction()
{
    if (! auth()->user()->hasRole('admin')) {
        throw new UnauthorizedException('Acción no permitida');
    }
    
    // Proceder con la acción
}
```

### Mantenimiento de Roles y Permisos

1. **Actualización de Permisos**
   - Ejecutar `php artisan shield:generate --all` después de crear nuevos recursos
   - Actualizar el seeder de permisos cuando se añadan nuevos permisos personalizados
   - Mantener documentación actualizada de roles y permisos

2. **Limpieza de Permisos**

```php
// En un comando artisan personalizado
public function handle()
{
    // Eliminar permisos huérfanos
    Permission::whereDoesntHave('roles')->delete();
    
    // Eliminar roles sin usuarios
    Role::whereDoesntHave('users')->delete();
}
```

3\. **Verificación de Integridad**

```php
public function verifyPermissions()
{
    $requiredPermissions = [
        'view_equipment',
        'create_ticket',
        // ... otros permisos críticos
    ];

    foreach ($requiredPermissions as $permission) {
        if (!Permission::where('name', $permission)->exists()) {
            Log::warning("Permiso faltante: {$permission}");
        }
    }
}
```

### Escalabilidad del Sistema de Permisos

1. **Grupos de Permisos**
   - Organizar permisos por módulos o áreas funcionales
   - Facilitar la asignación masiva de permisos relacionados

```php
// Ejemplo de agrupación de permisos
public static function getPermissionGroups(): array
{
    return [
        'tickets' => [
            'view_ticket',
            'create_ticket',
            'update_ticket',
            'delete_ticket',
            'assign_ticket',
        ],
        'equipment' => [
            'view_equipment',
            'create_equipment',
            'update_equipment',
            'delete_equipment',
        ],
    ];
}
```

2\. **Roles Dinámicos**

- Implementar roles basados en condiciones o reglas de negocio
- Permitir la personalización de roles por departamento o unidad

```php
public function hasCustomPermission(string $permission): bool
{
    if ($this->department_id === 1) {
        return $this->hasAnyRole(['admin', 'supervisor']);
    }

    return $this->hasPermissionTo($permission);
}
```

3\. **Cache de Permisos**

- Utilizar el sistema de cache incorporado en Spatie Permission
- Implementar cache personalizado para consultas frecuentes

```php
// En el archivo de configuración permission.php
'cache' => [
    'expiration_time' => \DateInterval::createFromDateString('24 hours'),
    'key' => 'spatie.permission.cache',
    'store' => 'default',
],
```

4\. **Monitoreo y Alertas**

- Registrar intentos de acceso no autorizado
- Notificar sobre cambios en roles críticos

```php
// Observer para cambios en roles
class RoleObserver
{
    public function updated(Role $role)
    {
        if ($role->name === 'admin') {
            Log::alert('Rol de administrador modificado');
            Notification::send(
                User::admins()->get(),
                new AdminRoleModifiedNotification($role)
            );
        }
    }
}
```
