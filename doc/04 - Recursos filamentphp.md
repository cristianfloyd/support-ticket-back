# Documentación de Recursos Filament - Sistema de Tickets TI

## Índice

1. Recursos Principales
2. Recursos de Soporte
3. Recursos de Configuración
4. Widgets y Personalizaciones
5. Recursos Principales

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

## DepartmentResource

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

## Notas de Implementación

- Usar Filament v3.x
- Implementar dark mode
- Configurar middleware de autenticación
- Establecer políticas de autorización
- Implementar validaciones personalizadas
- Configurar eventos y listeners
