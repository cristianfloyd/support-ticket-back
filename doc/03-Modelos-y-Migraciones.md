# Modelos y Migraciones del Sistema de Tickets v2

## Orden de Ejecución de Migraciones

Debido a las dependencias entre tablas, las migraciones deben ejecutarse en el siguiente orden:

1. Tablas Base (sin dependencias):
   - departments
   - roles
   - statuses
   - priorities
   - categories
   - unidades_academicas
   - proveedores

2. Tablas con Dependencias Simples:
   - users (requiere departments)
   - buildings (requiere unidades_academicas)
   - equipments (requiere proveedores)

3. Tablas con Dependencias Múltiples:
   - offices (requiere buildings)
   - role_user (requiere users, roles)
   - tickets (requiere múltiples tablas)

4. Tablas Dependientes de Tickets:
   - comments
   - attachments
   - notificaciones

## Entidades Principales

### 1. Tickets

- **Modelo**: `Ticket`
- **Migración**: `create_tickets_table`
- **Atributos**:

  ```php
  Schema::create('tickets', function (Blueprint $table) {
      $table->id();
      $table->string('title', 255);
      $table->text('description');
      $table->foreignId('status_id')->constrained()->index();
      $table->foreignId('priority_id')->constrained()->index();
      $table->foreignId('category_id')->constrained()->index();
      $table->foreignId('user_id')->constrained()->index(); // Creador
      $table->foreignId('assigned_to')->nullable()->constrained('users')->index();
      $table->foreignId('unidad_academica_id')->constrained()->index();
      $table->foreignId('building_id')->constrained()->index();
      $table->foreignId('office_id')->constrained()->index();
      $table->foreignId('equipment_id')->nullable()->constrained()->index();
      $table->boolean('is_resolved')->default(false);
      $table->timestamp('resolved_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });
  ```

- **Índices**:
  - Índices compuestos para búsquedas comunes

  ```php
  $table->index(['status_id', 'created_at']);
  $table->index(['assigned_to', 'status_id']);
  ```

### 2. Users (Usuarios)

- **Modelo**: `User`
- **Migración**: `create_users_table`
- **Atributos**:

  ```php
  Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->foreignId('department_id')->nullable()->constrained();
      $table->boolean('is_active')->default(true);
      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();
  });
  ```

- **Índices**:

  ```php
  $table->index(['email', 'deleted_at']);
  $table->index(['department_id', 'is_active']);
  ```

### 3. Roles

- **Modelo**: `Role`
- **Migración**: `create_roles_table`
- **Relaciones**:
  - Pertenece a muchos (`belongsToMany`): Users

### 4. Departments (Departamentos)

- **Modelo**: `Department`
- **Migración**: `create_departments_table`
- **Relaciones**:
  - Tiene muchos (`hasMany`): Users

### 5. Categories (Categorías)

- **Modelo**: `Category`
- **Migración**: `create_categories_table`
- **Relaciones**:
  - Tiene muchos (`hasMany`): Tickets

### 6. Priorities (Prioridades)

- **Modelo**: `Priority`
- **Migración**: `create_priorities_table`
- **Relaciones**:
  - Tiene muchos (`hasMany`): Tickets

### 7. Statuses (Estados)

- **Modelo**: `Status`
- **Migración**: `create_statuses_table`
- **Relaciones**:
  - Tiene muchos (`hasMany`): Tickets

### 8. Comments (Comentarios)

- **Modelo**: `Comment`
- **Migración**: `create_comments_table`
- **Relaciones**:
  - Pertenece a (`belongsTo`): Ticket, User

### 9. Attachments (Archivos Adjuntos)

- **Modelo**: `Attachment`
- **Migración**: `create_attachments_table`
- **Relaciones**:
  - Pertenece a (`belongsTo`): Ticket

### 10. UnidadesAcademicas (Facultades)

- **Modelo**: `UnidadAcademica`
- **Migración**: `create_unidades_academicas_table`
- **Relaciones**:
  - Tiene muchos (`hasMany`): Buildings, Tickets

### 11. Buildings (Edificios)

- **Modelo**: `Building`
- **Migración**: `create_buildings_table`
- **Relaciones**:
  - Pertenece a (`belongsTo`): UnidadAcademica
  - Tiene muchos (`hasMany`): Offices, Tickets

### 12. Offices (Oficinas)

- **Modelo**: `Office`
- **Migración**: `create_offices_table`
- **Relaciones**:
  - Pertenece a (`belongsTo`): Building
  - Tiene muchos (`hasMany`): Tickets

### 13. Equipments (Equipos)

- **Modelo**: `Equipment`
- **Migración**: `create_equipments_table`
- **Relaciones**:
  - Puede tener muchos (`hasMany`): Tickets

### 14. Proveedores

- **Modelo**: `Proveedor`
- **Migración**: `create_proveedores_table`
- **Relaciones**:
  - Puede tener muchos (`hasMany`): Equipments

### 15. Notificaciones

- **Modelo**: `Notificacion`
- **Migración**: `create_notificaciones_table`
- **Atributos**:

  ```php
  Schema::create('notificaciones', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->index();
      $table->foreignId('ticket_id')->constrained()->index();
      $table->string('type')->index(); // tipo de notificación
      $table->text('message');
      $table->timestamp('read_at')->nullable();
      $table->timestamps();
  });
  ```

- **Índices**:

  ```php
  $table->index(['user_id', 'read_at']);
  $table->index(['ticket_id', 'created_at']);
  ```

## Tablas Pivote

### 1. role_user

- **Migración**: `create_role_user_table`
- **Propósito**: Gestiona la relación muchos a muchos entre Users y Roles

## Convenciones de Nomenclatura

- Los nombres de los modelos están en singular y PascalCase
- Los nombres de las tablas están en plural y snake_case
- Las claves foráneas siguen el patrón `tabla_en_singular_id`
- Las tablas pivote usan los nombres de los modelos en singular, en orden alfabético

## Notas Adicionales

- Todos los modelos extenderán de `Illuminate\Database\Eloquent\Model`
- Se utilizará soft deletes (`SoftDeletes`) en los modelos relevantes
- Las timestamps (`created_at` y `updated_at`) están habilitadas por defecto
- Las relaciones polimórficas se implementarán según sea necesario

## Valores por Defecto y Estados Iniciales

### Estados (Statuses)

```php
[
    ['name' => 'Abierto', 'color' => '#FF0000'],
    ['name' => 'En Progreso', 'color' => '#FFA500'],
    ['name' => 'En Espera', 'color' => '#FFFF00'],
    ['name' => 'Resuelto', 'color' => '#00FF00'],
    ['name' => 'Cerrado', 'color' => '#808080']
]
```

### Prioridades (Priorities)

```php
[
    ['name' => 'Baja', 'color' => '#00FF00'],
    ['name' => 'Media', 'color' => '#FFA500'],
    ['name' => 'Alta', 'color' => '#FF0000'],
    ['name' => 'Crítica', 'color' => '#800000']
]
```

### Roles

```php
[
    ['name' => 'admin', 'display_name' => 'Administrador'],
    ['name' => 'agent', 'display_name' => 'Agente'],
    ['name' => 'user', 'display_name' => 'Usuario']
]
```

## Traits y Scopes Comunes

### SoftDeletes

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;
}
```

### HasActiveScope

```php
trait HasActiveScope
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
```

### Scopes Globales

```php
// Ordenamiento por fecha
public function scopeLatest($query)
{
    return $query->orderBy('created_at', 'desc');
}
```

## Consideraciones de Implementación

1. **Validación**
   - Usar Form Requests para validación
   - Implementar reglas de negocio en los modelos
   - Usar observers para eventos del modelo

2. **Seguridad**
   - Implementar políticas de acceso (Policies)
   - Usar middleware para control de acceso
   - Validar relaciones en las operaciones

3. **Optimización**
   - Usar eager loading para relaciones
   - Implementar caching donde sea necesario
   - Considerar chunking para operaciones masivas

4. **Mantenimiento**
   - Usar factories para testing
   - Implementar seeders para datos iniciales
   - Documentar cambios en las migraciones

## Ejemplo de Factory

```php
class TicketFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => Status::inRandomOrder()->first()->id,
            'priority_id' => Priority::inRandomOrder()->first()->id,
            // ... otros campos
        ];
    }
}
```

## Seeders para Testing

### DatabaseSeeder

```php
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            StatusSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            UnidadAcademicaSeeder::class,
            ProveedorSeeder::class,
            UserSeeder::class,
            BuildingSeeder::class,
            EquipmentSeeder::class,
            OfficeSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
```

### Seeders Individuales

```php
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create(['name' => 'TI', 'description' => 'Departamento de Tecnología']);
        Department::create(['name' => 'Soporte', 'description' => 'Departamento de Soporte Técnico']);
        Department::create(['name' => 'Redes', 'description' => 'Departamento de Redes']);
    }
}

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'admin', 'display_name' => 'Administrador'],
            ['name' => 'agent', 'display_name' => 'Agente'],
            ['name' => 'user', 'display_name' => 'Usuario']
        ] as $role) {
            Role::create($role);
        }
    }
}

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Abierto', 'color' => '#FF0000'],
            ['name' => 'En Progreso', 'color' => '#FFA500'],
            ['name' => 'En Espera', 'color' => '#FFFF00'],
            ['name' => 'Resuelto', 'color' => '#00FF00'],
            ['name' => 'Cerrado', 'color' => '#808080']
        ] as $status) {
            Status::create($status);
        }
    }
}

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Baja', 'color' => '#00FF00'],
            ['name' => 'Media', 'color' => '#FFA500'],
            ['name' => 'Alta', 'color' => '#FF0000'],
            ['name' => 'Crítica', 'color' => '#800000']
        ] as $priority) {
            Priority::create($priority);
        }
    }
}

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Hardware',
            'Software',
            'Red',
            'Impresoras',
            'Email',
            'Accesos',
            'Otros'
        ] as $category) {
            Category::create(['name' => $category]);
        }
    }
}

class UnidadAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Facultad de Ingeniería', 'code' => 'FI'],
            ['name' => 'Facultad de Ciencias', 'code' => 'FC'],
            ['name' => 'Facultad de Medicina', 'code' => 'FM']
        ] as $unidad) {
            UnidadAcademica::create($unidad);
        }
    }
}

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password')
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        // Usuario Agente
        $agent = User::factory()->create([
            'name' => 'Agent User',
            'email' => 'agent@example.com',
            'password' => Hash::make('password')
        ]);
        $agent->roles()->attach(Role::where('name', 'agent')->first());

        // Usuarios normales
        User::factory(10)->create()->each(function ($user) {
            $user->roles()->attach(Role::where('name', 'user')->first());
        });
    }
}

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        UnidadAcademica::all()->each(function ($unidad) {
            Building::factory(3)->create([
                'unidad_academica_id' => $unidad->id
            ]);
        });
    }
}

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        Building::all()->each(function ($building) {
            Office::factory(5)->create([
                'building_id' => $building->id
            ]);
        });
    }
}

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'HP',
            'Dell',
            'Lenovo',
            'Apple',
            'Samsung'
        ] as $proveedor) {
            Proveedor::create(['name' => $proveedor]);
        }
    }
}

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        Proveedor::all()->each(function ($proveedor) {
            Equipment::factory(5)->create([
                'proveedor_id' => $proveedor->id
            ]);
        });
    }
}

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->get();

        // Crear 50 tickets de prueba
        Ticket::factory(50)->create()->each(function ($ticket) use ($agents) {
            // Asignar aleatoriamente a un agente
            $ticket->assigned_to = $agents->random()->id;
            $ticket->save();

            // Crear algunos comentarios
            Comment::factory(rand(1, 5))->create([
                'ticket_id' => $ticket->id
            ]);

            // Crear algunos adjuntos
            if (rand(0, 1)) {
                Attachment::factory(rand(1, 3))->create([
                    'ticket_id' => $ticket->id
                ]);
            }
        });
    }
}

Este documento proporciona una guía más completa y detallada para la implementación de los modelos y migraciones, incluyendo consideraciones importantes para el desarrollo y mantenimiento del sistema.
