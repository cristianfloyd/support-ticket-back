# **Planificación y Definición de la API para Sistema de Tickets de TI**

## **1\. Identificación de Recursos (Entidades)**

Además de los recursos principales, incluiremos información específica de la universidad:

* **Tickets:** Representan los problemas o solicitudes de soporte.  
* **Usuarios:** Representan a las personas que crean, gestionan y resuelven tickets.  
  * **Roles:** Permisos asociados a los usuarios (ej., Administrador, Agente, Usuario).  
* **Departamentos:** Agrupan a los usuarios y pueden estar asociados con tickets.  
* **Categorías:** Clasifican los tickets (e.g., hardware, software, red).  
* **Prioridades:** Indican la urgencia de un ticket (e.g., alta, media, baja).  
* **Estados:** Indican la etapa en la que se encuentra un ticket (e.g., abierto, en progreso, cerrado).  
* **Comentarios:** Notas o mensajes asociados a un ticket.  
* **Archivos Adjuntos:** Documentos o archivos relacionados con un ticket.  
* **Facultades:** Unidades académicas de la universidad.  
* **Edificios:** Estructuras físicas dentro de la universidad.  
* **Oficinas:** Espacios específicos dentro de un edificio.  
* **Equipos:** Activos de hardware o software (ej., computadoras, proyectores).  
* **Proveedores:** Entidades externas que dan servicios o proveen equipos.  
* **Notificaciones:** Para informar a los usuarios de actualizaciones importantes.

## **2\. Definición de Operaciones (Funcionalidades)**

Las operaciones básicas se mantienen, pero ajustaremos algunos detalles y agregaremos operaciones para los nuevos recursos.

### **Tickets**

* GET /api/tickets: Obtener todos los tickets (con opciones de filtrado, ordenamiento y paginación).  
* GET /api/tickets/{ticketId}: Obtener un ticket específico por su ID.  
* POST /api/tickets: Crear un nuevo ticket.  
* PUT /api/tickets/{ticketId}: Actualizar un ticket existente.  
* PATCH /api/tickets/{ticketId}: Actualizar parcialmente un ticket existente.  
* DELETE /api/tickets/{ticketId}: Eliminar un ticket.  
* GET /api/tickets/{ticketId}/comments: Listar los comentarios de un ticket.  
* POST /api/tickets/{ticketId}/comments: Agregar un comentario a un ticket.  
* POST /api/tickets/{ticketId}/attachments: Subir un archivo adjunto a un ticket  
* POST /api/tickets/{ticketId}/notifications: Enviar una notificación sobre un ticket.

### **Usuarios**

* GET /api/users: Listar todos los usuarios.  
* GET /api/users/{userId}: Obtener un usuario por ID.  
* POST /api/users: Crear un nuevo usuario.  
* PUT /api/users/{userId}: Actualizar un usuario.  
* DELETE /api/users/{userId}: Eliminar un usuario.  
* GET /api/users/{userId}/roles: Listar los roles de un usuario.  
  \* PUT /api/users/{userId}/roles: Asignar roles a un usuario.

### **Roles**

* 'GET /api/roles': Listar todos los roles  
* 'GET /api/roles/{roleId}': Obtener un rol por Id  
* 'POST /api/roles': Crear un nuevo rol  
* 'PUT /api/roles/{roleId}': Actualizar un rol  
* 'DELETE /api/roles/{roleId}': Eliminar un rol

### **Departamentos**

* GET /api/departments: Listar todos los departamentos.  
* GET /api/departments/{departmentId}: Obtener un departamento por ID.  
* POST /api/departments: Crear un nuevo departamento.  
* PUT /api/departments/{departmentId}: Actualizar un departamento.  
* DELETE /api/departments/{departmentId}: Eliminar un departamento.  
* GET /api/departments/{departmentId}/users: Listar los usuarios de un departamento

### **Categorías**

* GET /api/categories: Listar todas las categorías.  
* GET /api/categories/{categoryId}: Obtener una categoría por ID.  
* POST /api/categories: Crear una nueva categoría.  
* PUT /api/categories/{categoryId}: Actualizar una categoría.  
* DELETE /api/categories/{categoryId}: Eliminar una categoría.

### **Prioridades**

* GET /api/priorities: Listar todas las prioridades.  
* GET /api/priorities/{priorityId}: Obtener una prioridad por ID.

### **Estados**

* GET /api/statuses: Listar todos los estados.  
* GET /api/statuses/{statusId}: Obtener un estado por ID.

### **Facultades**

* GET /api/faculties: Listar todas las facultades.  
* GET /api/faculties/{facultyId}: Obtener una facultad por ID.  
* POST /api/faculties: Crear una nueva facultad.  
* PUT /api/faculties/{facultyId}: Actualizar una facultad.  
* DELETE /api/faculties/{facultyId}: Eliminar una facultad.  
* GET /api/faculties/{facultyId}/buildings: Listar los edificios de una facultad.

### **Edificios**

* GET /api/buildings: Listar todos los edificios.  
* GET /api/buildings/{buildingId}: Obtener un edificio por ID.  
* POST /api/buildings: Crear un nuevo edificio.  
* PUT /api/buildings/{buildingId}: Actualizar un edificio.  
* DELETE /api/buildings/{buildingId}: Eliminar un edificio.  
* GET /api/buildings/{buildingId}/offices: Listar las oficinas de un edificio.

### **Oficinas**

* GET /api/offices: Listar todas las oficinas.  
* GET /api/offices/{officeId}: Obtener una oficina por ID.  
* POST /api/offices: Crear una nueva oficina.  
* PUT /api/offices/{officeId}: Actualizar una oficina.  
* DELETE /api/offices/{officeId}: Eliminar una oficina.

### **Equipos**

* GET /api/equipments: Listar todos los equipos.  
* GET /api/equipments/{equipmentId}: Obtener un equipo por ID.  
* POST /api/equipments: Crear un nuevo equipo.  
* PUT /api/equipments/{equipmentId}: Actualizar un equipo.  
* DELETE /api/equipments/{equipmentId}: Eliminar un equipo.

### **Proveedores**

* GET /api/providers: Listar todos los proveedores.  
* GET /api/providers/{providerId}: Obtener un proveedor por ID.  
* POST /api/providers: Crear un nuevo proveedor.  
* PUT /api/providers/{providerId}: Actualizar un proveedor.  
* DELETE /api/providers/{providerId}: Eliminar un proveedor.

### **Notificaciones**

* GET /api/notifications: Listar todas las notificaciones.  
* GET /api/notifications/{notificationId}: Obtener una notificación por ID.  
* POST /api/notifications: Crear una nueva notificación.  
* PUT /api/notifications/{notificationId}: Actualizar una notificacion.  
* DELETE /api/notifications/{notificationId}: Eliminar una notificacion.

## **4\. Endpoints de la API**

A continuación, se muestra la lista de endpoints actualizada:

### **Tickets**

* GET /api/tickets: Listar todos los tickets.  
* GET /api/tickets/{ticketId}: Obtener un ticket por ID.  
* POST /api/tickets: Crear un nuevo ticket.  
* PUT /api/tickets/{ticketId}: Actualizar un ticket.  
* PATCH /api/tickets/{ticketId}: Actualizar parcialmente un ticket.  
* DELETE /api/tickets/{ticketId}: Eliminar un ticket.  
* GET /api/tickets/{ticketId}/comments: Listar los comentarios de un ticket.  
* POST /api/tickets/{ticketId}/comments: Agregar un comentario a un ticket.  
* POST /api/tickets/{ticketId}/attachments: Subir un archivo adjunto a un ticket  
* POST /api/tickets/{ticketId}/notifications: Enviar una notificación sobre un ticket.

### **Usuarios**

* GET /api/users: Listar todos los usuarios.  
* GET /api/users/{userId}: Obtener un usuario por ID.  
* POST /api/users: Crear un nuevo usuario.  
* PUT /api/users/{userId}: Actualizar un usuario.  
* DELETE /api/users/{userId}: Eliminar un usuario.  
* GET /api/users/{userId}/roles: Listar los roles de un usuario.  
  \* PUT /api/users/{userId}/roles: Asignar roles a un usuario.

### **Roles**

* 'GET /api/roles': Listar todos los roles  
* 'GET /api/roles/{roleId}': Obtener un rol por Id  
* 'POST /api/roles': Crear un nuevo rol  
* 'PUT /api/roles/{roleId}': Actualizar un rol  
* 'DELETE /api/roles/{roleId}': Eliminar un rol

### **Departamentos**

* GET /api/departments: Listar todos los departamentos.  
* GET /api/departments/{departmentId}: Obtener un departamento por ID.  
* POST /api/departments: Crear un nuevo departamento.  
* PUT /api/departments/{departmentId}: Actualizar un departamento.  
* DELETE /api/departments/{departmentId}: Eliminar un departamento.  
* GET /api/departments/{departmentId}/users: Listar los usuarios de un departamento

### **Categorías**

* GET /api/categories: Listar todas las categorías.  
* GET /api/categories/{categoryId}: Obtener una categoría por ID.  
* POST /api/categories: Crear una nueva categoría.  
* PUT /api/categories/{categoryId}: Actualizar una categoría.  
* DELETE /api/categories/{categoryId}: Eliminar una categoría.

### **Prioridades**

* GET /api/priorities: Listar todas las prioridades.  
* GET /api/priorities/{priorityId}: Obtener una prioridad por ID.

### **Estados**

* GET /api/statuses: Listar todos los estados.  
* GET /api/statuses/{statusId}: Obtener un estado por ID.

### **Facultades**

* GET /api/faculties: Listar todas las facultades.  
* GET /api/faculties/{facultyId}: Obtener una facultad por ID.  
* POST /api/faculties: Crear una nueva facultad.  
* PUT /api/faculties/{facultyId}: Actualizar una facultad.  
* DELETE /api/faculties/{facultyId}: Eliminar una facultad.  
* GET /api/faculties/{facultyId}/buildings: Listar los edificios de una facultad.

### **Edificios**

* GET /api/buildings: Listar todos los edificios.  
* GET /api/buildings/{buildingId}: Obtener un edificio por ID.  
* POST /api/buildings: Crear un nuevo edificio.  
* PUT /api/buildings/{buildingId}: Actualizar un edificio.  
* DELETE /api/buildings/{buildingId}: Eliminar un edificio.  
* GET /api/buildings/{buildingId}/offices: Listar las oficinas de un edificio.

### **Oficinas**

* GET /api/offices: Listar todas las oficinas.  
* GET /api/offices/{officeId}: Obtener una oficina por ID.  
* POST /api/offices: Crear una nueva oficina.  
* PUT /api/offices/{officeId}: Actualizar una oficina.  
* DELETE /api/offices/{officeId}: Eliminar una oficina.

### **Equipos**

* GET /api/equipments: Listar todos los equipos.  
* GET /api/equipments/{equipmentId}: Obtener un equipo por ID.  
* POST /api/equipments: Crear un nuevo equipo.  
* PUT /api/equipments/{equipmentId}: Actualizar un equipo.  
* DELETE /api/equipments/{equipmentId}: Eliminar un equipo.

### **Proveedores**

* GET /api/providers: Listar todos los proveedores.  
* GET /api/providers/{providerId}: Obtener un proveedor por ID.  
* POST /api/providers: Crear un nuevo proveedor.  
* PUT /api/providers/{providerId}: Actualizar un proveedor.  
* DELETE /api/providers/{providerId}: Eliminar un proveedor.

### **Notificaciones**

* GET /api/notifications: Listar todas las notificaciones.  
* GET /api/notifications/{notificationId}: Obtener una notificación por ID.  
* POST /api/notifications: Crear una nueva notificación.  
* PUT /api/notifications/{notificationId}: Actualizar una notificacion.  
* DELETE /api/notifications/{notificationId}: Eliminar una notificacion.

## **5\. Formato de Solicitud y Respuesta**

* **Solicitudes:**  
  * Los datos se enviarán en el cuerpo de la solicitud en formato JSON.  
  * Se utilizarán los encabezados Content-Type: application/json.  
* **Respuestas:**  
  * Las respuestas se devolverán en formato JSON.  
  * Se utilizarán códigos de estado HTTP para indicar el resultado de la operación.  
  * Se incluirán mensajes de error descriptivos.  
  * Se implementará la paginación para las listas de recursos

### **Ejemplo de Respuesta Detallada para GET /api/tickets/{ticketId}**

{  
    "id": 123,  
    "title": "Problema con la impresora",  
    "description": "La impresora no imprime correctamente.",  
    "status": {  
        "id": 1,  
        "name": "Abierto"  
    },  
    "priority": {  
        "id": 2,  
        "name": "Alta"  
    },  
    "category": {  
        "id": 1,  
        "name": "Hardware"  
    },  
    "user": {  
        "id": 456,  
        "name": "Juan Pérez",  
        "email": "juan.perez@example.com"
    },  
    "assigned\_to": {  // Usuario asignado  
        "id": 789,  
        "name": "María García",  
        "email": "maria.garcia@example.com"
    },  
    "created\_at": "2024-07-24T10:00:00Z",  
    "updated\_at": "2024-07-24T12:30:00Z",  
     "faculty": { // Facultad  
         "id": 1,  
         "name": "Facultad de Ingeniería",  
     },  
     "building": { // Edificio  
         "id": 10,  
         "name": "Edificio Central",  
         "address": "Av. Principal 123",  
         "phone": "+54 11 5555-1234",  
     },  
     "office":{  //Oficina  
         "id": 20,  
         "name": "Oficina 101",  
     },  
     "equipment":{  //Equipo  
         "id": 5,  
         "name": "Proyector Epson",  
         "serial\_number":"123456789"  
     },  
    "comments": \[  
        {  
            "id": 1,  
            "user": {  
                 "name": "Pedro",  
            },  
            "text": "Se revisó la conexión de la impresora.",  
            "created\_at": "2024-07-24T12:15:00Z"  
        }  
    \],  
    "attachments": \[  
         {  
             "id": 1,  
             "filename": "informe\_impresora.pdf",  
             "url": "/api/attachments/1"  
         }  
    \]  
}

## **5\. Autenticación y Autorización**

La API debe estar protegida.

* **Autenticación:**  
  * Utilizar **Laravel Sanctum** para la autenticación basada en tokens.  
* **Autorización:**  
  * Utilizar **Policies de Laravel**.  
  * Considerar roles (e.g., "usuario", "agente", "administrador") y permisos.

## **6\. Paginación, Filtrado y Ordenamiento**

Implementar en listas de recursos.

Ejemplo: GET /api/tickets?page=2\&per\_page=10\&status\_id=1\&sort=created\_at\&sort\_direction=desc

## **7\. Control de Versiones**

Es importante implementar el control de versiones de la API, para que los cambios en la API no rompan las aplicaciones que la consumen.  
\* Utilizar versionado de URI, por ejemplo: \`/api/v1/tickets\`

## **8\. Documentación de la API**

Documentar con Swagger/OpenAPI.