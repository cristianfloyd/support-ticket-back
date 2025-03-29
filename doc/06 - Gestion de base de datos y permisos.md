# 6. Gestión de Base de Datos y Permisos

## 1. Preparación

Antes de comenzar, asegúrate de:

- Tener una copia de seguridad de la base de datos si es necesario
- Tener todos los seeders actualizados
- No estar en ambiente de producción

## 2. Secuencia de Comandos

### 2.1 Limpiar Cachés del Sistema

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2.2 Reiniciar la Base de Datos y Ejecutar Seeders

```bash
php artisan migrate:fresh --seed
```

### 2.3 Regenerar Permisos de Filament Shield

```bash
# Generar permisos para todos los recursos, páginas y widgets
php artisan shield:generate --all

# Crear/Actualizar el super admin
php artisan shield:super-admin
```

### 2.4 Reconstruir Assets

```bash
npm run build
```

## 3. Verificación

Después de ejecutar todos los comandos, verifica:

1. Accede al panel de administración
2. Confirma que todos los recursos son visibles
3. Verifica que los permisos están correctamente asignados

## 4. Solución de Problemas

Si algunos recursos no son visibles:

1. Verifica los permisos en la base de datos:

```php
// En tinker (php artisan tinker)
\Spatie\Permission\Models\Permission::all()->pluck('name');
```

2. Verifica los roles del usuario:

```php
// En tinker (php artisan tinker)
$user = \App\Models\User::find(1); // Ajusta el ID según necesites
$user->roles->pluck('name');
$user->getAllPermissions()->pluck('name');
```

3. Si es necesario, vuelve a ejecutar:

```bash
php artisan shield:generate --all
php artisan shield:super-admin
```

## 5. Notas Importantes

- Este procedimiento eliminará todos los datos existentes en la base de datos
- Asegúrate de que todos los seeders estén actualizados antes de ejecutar
- El comando `shield:generate --all` debe ejecutarse después del `migrate:fresh --seed`
- Siempre ejecuta `npm run build` al final para asegurar que los cambios se reflejen en el frontend
- En ambiente de producción, considera usar `migrate` en lugar de `migrate:fresh`

## 6. Comandos Adicionales de Shield

Otros comandos útiles de Filament Shield:

```bash
# Generar permisos para un recurso específico
php artisan shield:generate --resource=NombreResource

# Generar permisos para una página específica
php artisan shield:generate --page=NombrePage

# Generar permisos para un widget específico
php artisan shield:generate --widget=NombreWidget

```
