# Dashboard del Administrador — Documentación Técnica

## Resumen

El dashboard de administración (`admin.php`) es el panel principal del rol **Administrador** (id_rol = 1). Permite visualizar estadísticas de usuarios, listar el directorio completo, crear nuevos usuarios con cualquier rol, editar sus datos y activar/suspender cuentas. Todas las operaciones de escritura pasan por `AdminUsuarioController.php`.

---

## Arquitectura del módulo

```
[Vista] views/dashboard/admin.php
    │  Carga datos directamente del modelo al renderizar
    │  Envía acciones a:
    ▼
[Controlador] controllers/AdminUsuarioController.php
    │  Instancia y llama métodos de:
    ▼
[Modelo] models/usuario.php
    │  Usa conexión de:
    ▼
[Config] config/database.php  →  Base de datos: dbmoova

[Layouts compartidos]
    ├─ views/layouts/header.php   → HTML head, CSS, librerías
    └─ views/layouts/sidebar.php  → Menú lateral de navegación
```

---

## 1. Vista — `views/dashboard/admin.php`

### Protección de acceso

Al inicio del archivo se verifica la sesión y el rol:

```php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

if ($_SESSION['usuario']['id_rol'] != 1) {
    // Redirige a trabajador.php o cliente.php según el rol
    exit;
}
```

Solo usuarios con `id_rol = 1` pueden acceder. Cualquier otro rol es redirigido a su dashboard correspondiente.

### Datos cargados al renderizar

La vista instancia directamente el modelo para obtener los datos antes de renderizar el HTML:

```php
$usuarioModel = new Usuario($db);
$usuarios     = $usuarioModel->obtenerTodos();   // todos los usuarios con su rol
$roles        = $usuarioModel->obtenerRoles();   // lista de roles para los selects
```

### Estadísticas calculadas en PHP

Las KPI cards se calculan en el servidor con `array_filter`:

| Variable             | Cálculo                                      | Descripción              |
|----------------------|----------------------------------------------|--------------------------|
| `$total_usuarios`    | `count($usuarios)`                           | Total de usuarios        |
| `$usuarios_activos`  | Filtro: `estado == 1`                        | Cuentas activas          |
| `$total_admins`      | Filtro: `id_rol == 1`                        | Administradores          |
| `$total_clientes`    | Filtro: `id_rol == 3`                        | Clientes registrados     |
| `$total_trabajadores`| Filtro: `id_rol == 2`                        | Trabajadores (en tabla)  |

### Secciones de la vista

| Sección              | Descripción                                                        |
|----------------------|--------------------------------------------------------------------|
| Header               | Título, badge "Control Total", botón "Añadir Usuario"             |
| SweetAlert           | Muestra `$_SESSION['alert']` si existe, luego lo destruye         |
| KPI Cards (4)        | Usuarios Totales, Cuentas Activas, Administradores, Clientes      |
| Toolbar de tabla     | Filtros por rol (Todos/Admin/Trabajador/Cliente) + buscador       |
| Tabla de usuarios    | Directorio completo con perfil, contacto, documento, rol, acciones|
| Modal Crear          | Formulario para crear un nuevo usuario con cualquier rol          |
| Modal Editar         | Formulario para editar nombre, dirección, rol y contraseña        |

### Tabla de usuarios — columnas

| Columna      | Datos mostrados                                          |
|--------------|----------------------------------------------------------|
| Perfil       | Avatar con inicial + gradiente por rol, ID formateado    |
| Contacto     | Email y teléfono en chips con ícono                      |
| Documento    | Número de cédula/NIT                                     |
| Dirección    | Dirección truncada con tooltip                           |
| Rol & Estado | Badge de rol coloreado + indicador activo/suspendido     |
| Acciones     | Botón editar (abre modal) + botón toggle estado (link)   |

### Colores por rol en la tabla

| id_rol | Nombre        | Color avatar          | Badge rol                        |
|--------|---------------|-----------------------|----------------------------------|
| 1      | Administrador | `purple → violet`     | `bg-purple-100 text-purple-700`  |
| 2      | Trabajador    | `sky → blue`          | `bg-sky-100 text-sky-700`        |
| 3      | Cliente       | `amber → orange`      | `bg-amber-100 text-amber-700`    |

### Acciones disponibles por usuario

| Acción          | Tipo   | Destino                                                                 |
|-----------------|--------|-------------------------------------------------------------------------|
| Editar          | Modal  | Abre `modalEditar` con datos del usuario pre-cargados via JS            |
| Activar/Suspender | Link | `GET AdminUsuarioController.php?accion=toggleEstado&id=X&estado=Y`     |

---

## 2. Controlador — `controllers/AdminUsuarioController.php`

### Protección de acceso

El controlador también verifica la sesión antes de ejecutar cualquier acción:

```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header("Location: ../views/usuarios/login.php");
    exit;
}
```

### Punto de entrada

```php
$controller = new AdminUsuarioController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'crear':        $controller->crear();        break;
    case 'editar':       $controller->editar();       break;
    case 'toggleEstado': $controller->toggleEstado(); break;
    default: header("Location: ../views/dashboard/admin.php"); exit;
}
```

---

### Acción `crear` — POST

**Formulario origen:** `modalCrear` en `admin.php`  
**URL:** `controllers/AdminUsuarioController.php?accion=crear`

#### Campos recibidos

| Campo `name`  | Requerido | Descripción                        |
|---------------|-----------|------------------------------------|
| `nombres`     | ✅        | Nombre completo                    |
| `direccion`   | ✅        | Dirección                          |
| `email`       | ✅        | Correo electrónico                 |
| `password`    | ✅        | Contraseña (se hashea con bcrypt)  |
| `id_rol`      | ✅        | Rol asignado (1, 2 o 3)            |
| `telefono`    | ❌        | Teléfono (opcional)                |

#### Flujo

```
1. Verificar método POST
2. Sanitizar campos con trim()
3. Validar campos vacíos → alert warning
4. Verificar email duplicado con existeCorreo() → alert error
5. Hashear contraseña con password_hash(PASSWORD_DEFAULT)
6. Llamar a Usuario::registrar($datos)
   └─ true  → alert success
   └─ error → alert error
7. Redirect → admin.php
```

> A diferencia del registro público, aquí el administrador puede asignar **cualquier rol** (1, 2 o 3).

---

### Acción `editar` — POST

**Formulario origen:** `modalEditar` en `admin.php`  
**URL:** `controllers/AdminUsuarioController.php?accion=editar`

#### Campos recibidos

| Campo `name`  | Requerido | Descripción                                      |
|---------------|-----------|--------------------------------------------------|
| `id_usuario`  | ✅        | ID del usuario a editar (hidden)                 |
| `nombres`     | ✅        | Nuevo nombre                                     |
| `direccion`   | ✅        | Nueva dirección                                  |
| `id_rol`      | ✅        | Nuevo rol                                        |
| `password`    | ❌        | Nueva contraseña (solo si no está vacío)         |

#### Flujo

```
1. Verificar método POST
2. Verificar que id_usuario exista → alert error si no
3. Construir array $datos con nombres, direccion, id_rol
4. Si password no está vacío → hashear y agregar a $datos
5. Llamar a Usuario::actualizar($id_usuario, $datos)
   └─ true  → alert success
   └─ error → alert error
6. Redirect → admin.php
```

> El email **no es editable** desde este formulario. Solo se pueden cambiar nombre, dirección, rol y contraseña.

---

### Acción `toggleEstado` — GET

**Origen:** Link directo en la columna "Acciones" de la tabla  
**URL:** `controllers/AdminUsuarioController.php?accion=toggleEstado&id={id}&estado={estado_actual}`

#### Parámetros GET

| Parámetro | Descripción                              |
|-----------|------------------------------------------|
| `id`      | ID del usuario a modificar               |
| `estado`  | Estado actual del usuario (0 o 1)        |

#### Lógica

```php
$nuevo_estado = $estado == 1 ? 0 : 1;  // invierte el estado actual
```

```
1. Leer id y estado de $_GET
2. Calcular nuevo_estado (toggle)
3. Llamar a Usuario::cambiarEstado($id, $nuevo_estado)
   └─ true  → alert success ("activado" o "desactivado")
   └─ error → alert error
4. Redirect → admin.php
```

---

## 3. Modelo — `models/usuario.php`

### Métodos usados en este módulo

#### `obtenerTodos(): array`

Retorna todos los usuarios con el nombre de su rol mediante JOIN.

```sql
SELECT u.*, r.nombre as rol_nombre
FROM usuarios u
LEFT JOIN rol r ON u.id_rol = r.id_rol
ORDER BY u.id_usuario DESC
```

---

#### `obtenerRoles(): array`

Retorna todos los roles disponibles para poblar los `<select>` de los modales.

```sql
SELECT * FROM rol ORDER BY nombre ASC
```

---

#### `existeCorreo(string $email): bool`

Verifica si el correo ya está registrado antes de crear un usuario.

```sql
SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1
```

---

#### `registrar(array $datos): true|string`

Inserta un nuevo usuario en una transacción. Si `id_rol = 3`, también inserta en la tabla `cliente`.

```sql
-- Paso 1
INSERT INTO usuarios (nombres, direccion, email, documento_numero, telefono, password, id_rol)
VALUES (:nombres, :direccion, :email, :documento_numero, :telefono, :password, :id_rol)

-- Paso 2 (solo si id_rol = 3)
INSERT INTO cliente (id_usuarios) VALUES (:id_usuarios)
```

---

#### `actualizar(int $id_usuario, array $datos): true|string`

Actualiza nombre, dirección y rol. Si `$datos['password']` existe, también actualiza la contraseña.

```sql
UPDATE usuarios
SET nombres = :nombres, direccion = :direccion, id_rol = :id_rol
[, password = :password]   -- solo si se proporcionó
WHERE id_usuario = :id_usuario
```

---

#### `cambiarEstado(int $id_usuario, int $nuevo_estado): true|string`

Activa (1) o suspende (0) una cuenta.

```sql
UPDATE usuarios SET estado = :estado WHERE id_usuario = :id_usuario
```

---

## 4. JavaScript en la vista

### Funciones de modales

```javascript
openModal(id)                        // abre modal con animación opacity + scale
closeModalAnim(modalId, contentId)   // cierra con animación inversa
openEditModal(usuario)               // pre-carga datos del usuario en el modal editar
```

### `openEditModal(usuario)`

Recibe el objeto usuario serializado como JSON desde PHP y rellena los campos del modal:

```javascript
document.getElementById('edit_id_usuario').value = u.id_usuario;
document.getElementById('edit_nombres').value    = u.nombres;
document.getElementById('edit_direccion').value  = u.direccion;
document.getElementById('edit_email').value      = u.email;      // readonly
document.getElementById('edit_rol').value        = u.id_rol;
document.getElementById('editSubtitle').textContent = 'Editando: ' + u.nombres;
```

### Buscador en tiempo real

```javascript
document.getElementById('searchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.usuario-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});
```

### Filtro por rol

```javascript
function filtrarUsuarios(rol) {
    // Activa el botón del filtro seleccionado
    // Muestra/oculta filas según data-rol del <tr>
}
```

Cada fila `<tr>` tiene `data-rol="<?= $rolId ?>"` para que el filtro funcione sin recargar la página.

---

## 5. Tablas de base de datos involucradas

### `usuarios`

| Columna            | Uso en este módulo                                    |
|--------------------|-------------------------------------------------------|
| `id_usuario`       | Clave primaria, mostrado como `#0001`                 |
| `nombres`          | Mostrado en tabla, editable                           |
| `direccion`        | Mostrado en tabla, editable                           |
| `email`            | Mostrado en tabla, **no editable** desde admin        |
| `telefono`         | Mostrado en tabla                                     |
| `documento_numero` | Mostrado en tabla                                     |
| `password`         | Hasheado al crear/editar                              |
| `id_rol`           | Determina colores, badges y filtros                   |
| `estado`           | `1` = activo (verde), `0` = suspendido (gris)         |

### `rol`

| Columna   | Uso                                          |
|-----------|----------------------------------------------|
| `id_rol`  | FK en `usuarios`, usado en filtros y badges  |
| `nombre`  | Mostrado en badge de rol (Admin, Trabajador…)|

### `cliente`

| Columna       | Uso                                                    |
|---------------|--------------------------------------------------------|
| `id_usuarios` | Se inserta automáticamente al crear un usuario rol 3   |

---

## 6. Diagrama de flujo — Crear usuario

```
[admin.php → modalCrear]
     │
     │  POST: nombres, direccion, email, password, id_rol
     ▼
[AdminUsuarioController::crear()]
     │
     ├─ ¿Campos vacíos?        → alert warning → redirect admin.php
     ├─ ¿Email duplicado?      → alert error   → redirect admin.php
     │
     ├─ password_hash(password)
     ├─ Usuario::registrar($datos)
     │       ├─ INSERT INTO usuarios
     │       └─ (si rol=3) INSERT INTO cliente
     │
     ├─ true  → alert success → redirect admin.php
     └─ error → alert error   → redirect admin.php
```

## 7. Diagrama de flujo — Editar usuario

```
[admin.php → modalEditar]
     │
     │  POST: id_usuario, nombres, direccion, id_rol, [password]
     ▼
[AdminUsuarioController::editar()]
     │
     ├─ ¿id_usuario presente?  → alert error si no
     ├─ ¿password no vacío?    → hashear y agregar a $datos
     │
     ├─ Usuario::actualizar($id_usuario, $datos)
     │       └─ UPDATE usuarios SET ...
     │
     ├─ true  → alert success → redirect admin.php
     └─ error → alert error   → redirect admin.php
```

## 8. Diagrama de flujo — Toggle estado

```
[admin.php → link en tabla]
     │
     │  GET: ?accion=toggleEstado&id=X&estado=Y
     ▼
[AdminUsuarioController::toggleEstado()]
     │
     ├─ nuevo_estado = (estado == 1) ? 0 : 1
     ├─ Usuario::cambiarEstado($id, $nuevo_estado)
     │       └─ UPDATE usuarios SET estado = :estado
     │
     ├─ true  → alert success ("activado"/"desactivado") → redirect admin.php
     └─ error → alert error → redirect admin.php
```

---

## 9. Archivos involucrados — resumen

| Archivo                                      | Rol en el módulo                                        |
|----------------------------------------------|---------------------------------------------------------|
| `views/dashboard/admin.php`                  | Vista principal: tabla, KPIs, modales, JS               |
| `controllers/AdminUsuarioController.php`     | Lógica de crear, editar y toggle estado                 |
| `models/usuario.php`                         | Queries SQL: obtener, registrar, actualizar, cambiarEstado |
| `config/database.php`                        | Conexión PDO a MySQL                                    |
| `views/layouts/header.php`                   | HTML head, Tailwind, FontAwesome, SweetAlert2           |
| `views/layouts/sidebar.php`                  | Menú lateral de navegación del dashboard                |

---

## 10. Notas importantes

- **Doble protección:** tanto la vista como el controlador verifican `id_rol == 1` de forma independiente. Si alguien accede directamente al controlador sin sesión válida, es redirigido al login.
- **Email no editable:** el formulario de edición muestra el email como `readonly`. El controlador tampoco lo incluye en el `UPDATE`, por lo que no puede modificarse desde este panel.
- **Contraseña opcional en edición:** si el campo `password` llega vacío en el POST de edición, el controlador no lo incluye en el `UPDATE`, preservando la contraseña actual.
- **Toggle de estado sin confirmación:** el cambio de estado es un link GET directo, sin modal de confirmación. Se ejecuta inmediatamente al hacer clic.
- **Transacción en creación:** al crear un usuario con rol 3 (cliente), el modelo usa una transacción para garantizar que el INSERT en `usuarios` y el INSERT en `cliente` sean atómicos.
- **Filtros y búsqueda son solo frontend:** los filtros por rol y el buscador operan sobre el DOM con JavaScript. No hacen peticiones adicionales al servidor — todos los usuarios ya están cargados en la página.
