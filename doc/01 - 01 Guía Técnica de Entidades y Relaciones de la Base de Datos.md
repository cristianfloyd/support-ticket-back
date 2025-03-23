# **Guía Técnica de Entidades y Relaciones de la Base de Datos**

Este documento define las entidades (tablas) de la base de datos y sus relaciones para el sistema de tickets de TI de la universidad.

## **1\. Entidades (Tablas)**

A continuación, se describen las entidades y sus atributos:

### **tickets**

* id (INT, clave primaria, autoincremental)  
* title (VARCHAR(255), no nulo)  
* description (TEXT, no nulo)  
* status\_id (INT, clave foránea a statuses, **INDEX**)  
* priority\_id (INT, clave foránea a priorities, **INDEX**)  
* category\_id (INT, clave foránea a categories, **INDEX**)  
* user\_id (INT, clave foránea a users, **INDEX**) \- Creador del ticket  
* assigned\_to (INT, clave foránea a users, nullable)  
* unidad\_academica\_id (INT, clave foránea a unidades\_academicas, **INDEX**)  
* building\_id (INT, clave foránea a buildings, **INDEX**)  
* office\_id (INT, clave foránea a offices, **INDEX**)  
* created\_at (TIMESTAMP, **INDEX**)  
* updated\_at (TIMESTAMP)

### **users**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo)  
* email (VARCHAR(255), no nulo, único)  
* password (VARCHAR(255), no nulo)  
* department\_id (INT, clave foránea a departments, nullable, **INDEX**)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **roles**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **role\_user (Tabla pivote para la relación muchos a muchos entre users y roles)**

* user\_id (INT, clave foránea a users, **INDEX**)  
* role\_id (INT, clave foránea a roles, **INDEX**)

### **departments**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **categories**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **priorities**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **statuses**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **comments**

* id (INT, clave primaria, autoincremental)  
* ticket\_id (INT, clave foránea a tickets, **INDEX**)  
* user\_id (INT, clave foránea a users, **INDEX**)  
* text (TEXT, no nulo)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **attachments**

* id (INT, clave primaria, autoincremental)  
* ticket\_id (INT, clave foránea a tickets, **INDEX**)  
* filename (VARCHAR(255), no nulo)  
* path (VARCHAR(255), no nulo)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **unidades\_academicas**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **buildings**

* id (INT, clave primaria, autoincremental)  
* unidad\_academica\_id (INT, clave foránea a unidades\_academicas, **INDEX**)  
* name (VARCHAR(255), no nulo)  
* address (VARCHAR(255), nullable)  
* phone (VARCHAR(20), nullable)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **offices**

* id (INT, clave primaria, autoincremental)  
* building\_id (INT, clave foránea a buildings, **INDEX**)  
* name (VARCHAR(255), no nulo)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

### **equipments**

* id (INT, clave primaria, autoincremental)  
* name (VARCHAR(255), no nulo)  
* serial\_number (VARCHAR(255), nullable, único)  
* created\_at (TIMESTAMP)  
* updated\_at (TIMESTAMP)

\#\#\# proveedores

\* \`id\` (INT, clave primaria, autoincremental)  
\* \`name\` (VARCHAR(255), no nulo)  
\* \`email\` (VARCHAR(255), nullable)  
\* \`phone\` (VARCHAR(20), nullable)  
\* \`created\_at\` (TIMESTAMP)  
\* \`updated\_at\` (TIMESTAMP)

\#\#\# notificaciones

\`\`\`  
\* \`id\` (INT, clave primaria, autoincremental)  
\* \`user\_id\` (INT, clave foránea a \`users\`, \*\*INDEX\*\*)  
\* \`ticket\_id\` (INT, clave foránea a \`tickets\`, \*\*INDEX\*\*)  
\* \`message\` (TEXT, no nulo)  
\* \`read\_at\` (TIMESTAMP, nullable)  
\* \`created\_at\` (TIMESTAMP)  
\* \`updated\_at\` (TIMESTAMP)  
\`\`\`

## **2\. Relaciones**

A continuación, se describen las relaciones entre las entidades:

* Un usuario pertenece a un departamento (1 a muchos).  
* Un ticket pertenece a un usuario (creador) (1 a muchos).  
* Un ticket puede ser asignado a un usuario (1 a muchos).  
* Un ticket tiene un estado (1 a muchos).  
* Un ticket tiene una prioridad (1 a muchos).  
* Un ticket pertenece a una categoría (1 a muchos).  
* Un ticket puede pertenecer a una unidad\_academica (1 a muchos).  
* Un ticket puede pertenecer a un edificio (1 a muchos).  
* Un ticket puede pertenecer a una oficina (1 a muchos).  
* Un comentario pertenece a un ticket (1 a muchos).  
* Un comentario pertenece a un usuario (1 a muchos).  
* Un archivo adjunto pertenece a un ticket (1 a muchos).  
* Una unidad\_academica tiene muchos edificios (1 a muchos).  
* Un edificio pertenece a una unidad\_academica (1 a muchos).  
* Un edificio tiene muchas oficinas (1 a muchos).  
* Una oficina pertenece a un edificio (1 a muchos).  
  \* Un usuario puede tener muchos roles y un rol puede pertenecer a muchos usuarios (muchos a muchos, tabla pivote role\_user).  
  \* Un ticket puede tener muchas notificaciones (1 a muchos).  
  \* Una notificacion pertenece a un usuario (1 a muchos).

## **4\. Consideraciones Adicionales**

* **Índices:** Se indican las columnas indexadas con "**INDEX**" en la definición de cada tabla. Esto incluye claves foráneas y columnas de uso común en filtros y ordenamientos.  
* **Convenciones de Nombres:** Se utiliza plural para nombres de tablas y snake\_case para nombres de columnas.  
* **Tipos de Datos:** Se especifican los tipos de datos para cada columna.  
* **Borrado en Cascada:** No se especifica el borrado en cascada en este resumen. Se debe definir según los requerimientos de la aplicación.