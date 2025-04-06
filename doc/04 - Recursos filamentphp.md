# Documentación de Recursos Filament - Sistema de Tickets TI

## Índice

1. Comandos Artisan para Implementación
2. Recursos Principales
3. Recursos de Soporte
4. Recursos de Configuración
5. Widgets y Personalizaciones

## Comandos Artisan para Implementación

### Creación de Recursos

```bash
# Crear un nuevo recurso con todas las clases necesarias
php artisan make:filament-resource NombreResource --generate

# Crear un recurso con un modelo específico
php artisan make:filament-resource Nombre --model=App\\Models\\NombreModelo --generate
```

### Creación de Widgets

```bash
# Crear un widget de tipo stats (estadísticas)
php artisan make:filament-widget NombreStats --stats

# Crear un widget de tipo chart (gráficos)
php artisan make:filament-widget NombreChart --chart

# Crear un widget de tipo list (listas)
php artisan make:filament-widget NombreLista --list
```

### Creación de RelationManagers

```bash
# Crear un gestor de relaciones
php artisan make:filament-relation-manager RecursoResource Relacion campo_id

# Ejemplo para usuarios en departamento
php artisan make:filament-relation-manager DepartmentResource users user_id
```

### Implementación de Seguridad (FilamentShield)

```bash
# Instalar FilamentShield
php artisan shield:install --fresh

# Generar permisos para todos los recursos
php artisan shield:generate --resource=* --all

# Generar permisos para un recurso específico
php artisan shield:generate --resource=NombreResource

# Generar política para un recurso
php artisan shield:policy NombreResource --generate
```

### Actualización y Mantenimiento

```bash
# Optimizar Filament (después de cambios importantes)
php artisan filament:optimize

# Limpiar caché después de cambios
php artisan optimize:clear

# Actualizar permisos y roles
php artisan db:seed --class=RolePermissionSeeder
```

### Buenas Prácticas

1. Siempre generar los recursos con la opción `--generate` para crear todas las clases necesarias
2. Después de crear un nuevo recurso, generar sus permisos con FilamentShield
3. Actualizar el seeder de roles y permisos cuando se agreguen nuevos recursos
4. Ejecutar los comandos de optimización después de cambios importantes
5. Mantener la documentación actualizada con los nuevos recursos y sus características

## UserResource

- **Campos principales**:
  - Nombre
  - Email
  - Contraseña
  - Departamento (Select)
  - Estado (Toggle)
  - Roles (Select múltiple)

- **Funcionalidades**:
  - CRUD completo de usuarios
  - Asignación de roles y permisos
  - Filtros por departamento y estado
  - Acciones masivas para activar/desactivar usuarios

- **Relaciones**:
  - Departamento (belongsTo)
  - Tickets creados (hasMany)
  - Tickets asignados (hasMany)

- **Permisos y Roles (FilamentShield)**:

  - Permisos generados:
    - `view_user`
    - `view_any_user`
    - `create_user`
    - `update_user`
    - `delete_user`
    - `delete_any_user`
  - Roles con acceso:
    - Administrador: todos los permisos
    - Supervisor: `view_any_user`, `view_user`
    - Restricciones específicas: Solo usuarios del mismo departamento

## TicketResource

### Ruta: App\Filament\Resources\TicketResource

- **Campos principales**:
  - Título
  - Descripción (Markdown/Rich Editor)
  - Estado (Select)
  - Prioridad (Select)
  - Categoría (Select)
  - Asignado a (Select)
  - Ubicación (Unidad Académica, Edificio, Oficina)
  - Equipo relacionado (Select)

- **Tabs**:
  1. Información Principal
  2. Comentarios
  3. Archivos Adjuntos
  4. Historial

- **Acciones personalizadas**:
  - Cambiar estado
  - Reasignar
  - Marcar como resuelto
  - Generar reporte PDF

- **Widgets**:
  - Estadísticas de tickets
  - Tickets pendientes
  - Tiempo promedio de resolución

- **Permisos y Roles (FilamentShield)**:
  - Permisos generados:
    - `view_ticket`
    - `view_any_ticket`
    - `create_ticket`
    - `update_ticket`
    - `delete_ticket`
    - `delete_any_ticket`
  - Permisos personalizados:
    - `assign_ticket`
    - `change_ticket_status`
    - `change_ticket_priority`
  - Roles con acceso:
    - Técnico: `view_any_ticket`, `update_ticket`, `assign_ticket`
    - Usuario: `create_ticket`, `view_ticket` (solo propios)
  - Restricciones específicas: Filtrado por departamento o creador

## DepartmentResource  (En revision)

### Ruta: App\Filament\Resources\DepartmentResource

- **Campos principales**:
  - Nombre
  - Descripción
  - Estado activo

- **Relaciones mostradas**:
  - Lista de usuarios
  - Estadísticas de tickets por departamento

## UnidadAcademicaResource

### Ruta: App\Filament\Resources\UnidadAcademicaResource

- **Campos principales**:
  - Nombre
  - Código
  - Descripción

- **Relaciones mostradas**:
  - Edificios
  - Tickets relacionados

## Recursos de Soporte

### BuildingResource y OfficeResource

- **Campos principales**:
  - Nombre
  - Código
  - Descripción
  - Relaciones jerárquicas

- **Características**:
  - Vista jerárquica de edificios y oficinas
  - Mapeo de ubicaciones

  EquipmentResource
  
  - **Campos principales**:
  - Nombre
  - Número de serie
  - Proveedor
  - Especificaciones
  - Fechas importantes

- **Funcionalidades**:
  - Seguimiento de garantías
  - Historial de mantenimiento
  - QR para identificación

  CategoryResource y ProveedorResource

  - **Campos básicos**:
  - Nombre
  - Descripción
  - Estado

- **Características**:
  - Gestión simple CRUD
  - Estadísticas relacionadas

## Recursos de Configuración

### StatusResource y PriorityResource

- **Campos**:
  - Nombre
  - Color
  - Orden

- **Características**:
  - Configuración de flujo de trabajo
  - Personalización de estados y prioridades

## Widgets y Personalizaciones

### Widgets Globales

1. **Dashboard Principal**:
   - Resumen de tickets
   - Gráficos de rendimiento
   - Tickets pendientes por departamento

2. **Widgets de Recursos**:
   - Estadísticas específicas por recurso
   - Indicadores KPI
   - Alertas y notificaciones

### Personalizaciones Globales

1. **Tema y Diseño**:
   - Esquema de colores personalizado
   - Logos institucionales
   - Diseño responsivo

2. **Seguridad**:
   - Implementación de Filament Shield:
     - Generación automática de permisos por recurso
     - Panel de administración de permisos
     - Roles de super admin
     - Permisos generados automáticamente:
       - view_[resource]
       - view_any_[resource]
       - create_[resource]
       - update_[resource]
       - delete_[resource]
       - delete_any_[resource]
     - Configuración de políticas automáticas
     - Interfaz visual para gestión de permisos
   - Políticas de acceso por rol:
     - Implementación de roles jerárquicos
     - Herencia de permisos
     - Restricciones por departamento
   - Registro de actividades:
     - Logging de cambios en permisos
     - Auditoría de accesos
     - Historial de modificaciones

3. **Notificaciones**:
   - Sistema de notificaciones en tiempo real
   - Correos electrónicos automáticos
   - Alertas del sistema

### Acciones Globales

1. **Exportación de Datos**:
   - Excel
   - PDF
   - CSV

2. **Acciones Masivas**:
   - Actualización por lotes
   - Eliminación segura
   - Cambios de estado masivos

## Notas de Implementación y Solución de Problemas Comunes

### Compatibilidad con Filament v3

- **Cambios en la API**: Filament v3 ha modificado varios métodos respecto a versiones anteriores.
  - El método `mutateFormDataUsing()` ha sido reemplazado por alternativas como `using()` o `afterStateUpdated()`.
  - Ejemplo de uso correcto:

```php
    Forms\Components\FileUpload::make('path')
        ->afterStateUpdated(function ($state, callable $set) {
            // Procesar el archivo después de la subida
        })
```

- **RelationManagers**: Al trabajar con RelationManagers, es necesario asignar correctamente los IDs de los modelos relacionados.
  - Para acceder al modelo padre (por ejemplo, un Ticket) desde un RelationManager:

```php
    Tables\Actions\CreateAction::make()
        ->using(function (array $data, string $model, RelationManager $livewire) {
            // Obtener el ID del modelo padre
            $parentId = $livewire->getOwnerRecord()->getKey();
            $data['parent_id'] = $parentId;
            
            return $model::create($data);
        })
```

### Manejo de Archivos Adjuntos

- **Procesamiento de Metadatos**: Es importante procesar los metadatos de los archivos después de la subida.
  - Verificar la existencia del archivo antes de obtener sus metadatos.
  - Usar `Storage::disk('public')->path()` para obtener rutas de archivos de manera segura.
  - Implementar manejo de excepciones al trabajar con archivos.

- **Tipos de Archivos Aceptados**: La configuración actual acepta:
  - Imágenes (image/*)
  - PDF (application/pdf)
  - Documentos de Word (.doc, .docx)
  - Hojas de cálculo de Excel (.xls, .xlsx)
  - Archivos de texto (text/plain)
  - Tamaño máximo: 10MB

### Seguridad y Autorización

- **Asignación de Usuario**: Siempre asignar el ID del usuario actual a los registros que requieren autoría.
  - Usar `Auth::id()` para obtener el ID del usuario autenticado.
  
- **Control de Acceso**: Implementar restricciones de visibilidad para acciones como editar o eliminar.
  - Ejemplo:

```php
    Tables\Actions\EditAction::make()
        ->visible(fn ($record) => $record->user_id === Auth::id())
```

### Solución de Problemas Comunes

- **Error: Method does not exist**
  - Problema: Uso de métodos obsoletos de versiones anteriores de Filament.
  - Solución: Consultar la documentación oficial de Filament v3 para encontrar los métodos equivalentes actualizados.

- **Error: Not null violation**
  - Problema: No se asignan valores a campos obligatorios al crear registros relacionados.
  - Solución: Asegurarse de que todos los campos con restricciones NOT NULL tengan valores asignados antes de crear el registro.
