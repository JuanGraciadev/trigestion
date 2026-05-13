# Registro de Cliente — Documentación Técnica

## Resumen

El flujo de registro permite a un usuario nuevo crear una cuenta con rol **cliente** (id_rol = 3). Involucra tres capas: **Vista**, **Controlador** y **Modelo**, más la clase de conexión a base de datos.

---

## Arquitectura del flujo

```
Usuario (navegador)
    │
    ▼
[Vista] views/usuarios/registre.php
    │  Envía POST a:
    ▼
[Controlador] controllers/UsuarioController.php
    │  Instancia y llama métodos de:
    ▼
[Modelo] models/usuario.php
    │  Usa conexión de:
    ▼
[Config] config/database.php  →  Base de datos: dbmoova
```

---

## 1. Vista — `views/usuarios/registre.php`

**Responsabilidad:** Renderizar el formulario de registro y mostrar alertas de resultado.

### Campos del formulario

| Campo HTML (`name`)    | Tipo       | Requerido | Descripción                          |
|------------------------|------------|-----------|--------------------------------------|
| `nombres`              | text       | ✅        | Nombre completo del cliente          |
| `direccion`            | text       | ✅        | Dirección de entrega                 |
| `email`                | email      | ✅        | Correo electrónico (único)           |
| `documento_numero`     | text       | ✅        | Cédula o NIT                         |
| `telefono`             | text       | ✅        | Teléfono de contacto                 |
| `password`             | password   | ✅        | Contraseña (mín. 6 caracteres)       |
| `confirmar_password`   | password   | ✅        | Confirmación de contraseña           |
| `rol` (hidden)         | hidden     | —         | Valor fijo: `"cliente"`              |

### Destino del formulario

```html
<form action="../../controllers/UsuarioController.php" method="POST">
```

### Sistema de alertas

La vista lee `$_SESSION['alert']` al cargar y lo muestra con **SweetAlert2**. Si la alerta incluye `redirect`, redirige automáticamente al login tras confirmar.

```php
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
```

### Validación del lado cliente

Incluye `public/js/form-validation.js` para validación en tiempo real antes del envío.

---

## 2. Controlador — `controllers/UsuarioController.php`

**Responsabilidad:** Recibir el POST, validar los datos, y delegar al modelo.

### Método principal: `registrar()`

#### Paso a paso

```
1. Verificar que el método sea POST
   └─ Si no → redirigir a registre.php

2. Sanitizar campos con trim()
   └─ nombres, direccion, email, documento_numero, telefono, password, confirmar_password

3. Determinar id_rol
   └─ rol = "cliente" → id_rol = "3"

4. Validaciones en orden:
   a. Campos vacíos          → alert warning
   b. Email inválido         → alert error
   c. Contraseñas distintas  → alert error
   d. Password < 6 chars     → alert warning
   e. Teléfono vacío (rol 3) → alert warning

5. Conectar a BD via Database::conectar()

6. Instanciar Usuario($db)

7. Verificar email duplicado con existeCorreo($email)
   └─ Si existe → alert error

8. Hashear contraseña con password_hash($password, PASSWORD_DEFAULT)

9. Llamar a $usuario->registrar($datos)
   └─ true  → alert success + redirect a login.php
   └─ error → alert error con mensaje

10. Redirigir a registre.php (con la alerta en sesión)
```

#### Datos que pasa al modelo

```php
$datos = [
    'nombres'          => $nombres,
    'direccion'        => $direccion,
    'email'            => $email,
    'documento_numero' => $documento_numero,
    'telefono'         => $telefono,
    'password'         => password_hash($password, PASSWORD_DEFAULT),
    'id_rol'           => '3',
];
```

---

## 3. Modelo — `models/usuario.php`

**Responsabilidad:** Interactuar directamente con la base de datos.

### Métodos usados en el registro

#### `existeCorreo(string $email): bool`

Verifica si el correo ya está registrado antes de insertar.

```sql
SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1
```

Retorna `true` si ya existe, `false` si está disponible.

---

#### `registrar(array $datos): true|string`

Inserta el nuevo usuario en una **transacción** que cubre dos tablas:

**Paso 1 — Insertar en `usuarios`:**

```sql
INSERT INTO usuarios
  (nombres, direccion, email, documento_numero, telefono, password, id_rol)
VALUES
  (:nombres, :direccion, :email, :documento_numero, :telefono, :password, :id_rol)
```

**Paso 2 — Si id_rol = 3, insertar en `cliente`:**

```sql
INSERT INTO cliente (id_usuarios) VALUES (:id_usuarios)
```

> El `id_usuarios` se obtiene con `$this->conn->lastInsertId()` tras el primer INSERT.

**Manejo de errores:**

- Si cualquier INSERT falla → `rollBack()` y retorna el mensaje de error.
- Si todo sale bien → `commit()` y retorna `true`.

---

## 4. Configuración de BD — `config/database.php`

**Responsabilidad:** Proveer la conexión PDO a MySQL.

| Parámetro   | Valor         |
|-------------|---------------|
| Host        | `127.0.0.1`   |
| Puerto      | `3320`        |
| Base de datos | `dbmoova`   |
| Usuario     | `root`        |
| Charset     | `utf8mb4`     |

```php
$dsn = "mysql:host=127.0.0.1;port=3320;dbname=dbmoova;charset=utf8mb4";
$this->conn = new PDO($dsn, $this->username, $this->password);
$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

---

## 5. Tablas de base de datos involucradas

### `usuarios`

| Columna            | Tipo         | Descripción                        |
|--------------------|--------------|------------------------------------|
| `id_usuario`       | INT PK AUTO  | Identificador único                |
| `nombres`          | VARCHAR(100) | Nombre completo                    |
| `direccion`        | VARCHAR(100) | Dirección de entrega               |
| `email`            | VARCHAR(150) | Correo (único)                     |
| `documento_numero` | VARCHAR(150) | Cédula o NIT                       |
| `telefono`         | VARCHAR(30)  | Teléfono de contacto               |
| `password`         | VARCHAR      | Hash bcrypt                        |
| `id_rol`           | INT FK       | Rol asignado (3 = cliente)         |
| `estado`           | TINYINT      | 1 = activo, 0 = inactivo           |

### `cliente`

| Columna       | Tipo        | Descripción                          |
|---------------|-------------|--------------------------------------|
| `id_cliente`  | INT PK AUTO | Identificador del cliente            |
| `id_usuarios` | INT FK      | Referencia a `usuarios.id_usuario`   |

### `rol`

| Columna   | Tipo         | Descripción         |
|-----------|--------------|---------------------|
| `id_rol`  | INT PK       | Identificador       |
| `nombre`  | VARCHAR      | Nombre del rol      |

> Roles del sistema: `1` = Administrador, `2` = Trabajador, `3` = Cliente

---

## 6. Diagrama de flujo completo

```
[registre.php]
     │
     │  POST: nombres, direccion, email, documento_numero,
     │        telefono, password, confirmar_password, rol="cliente"
     ▼
[UsuarioController::registrar()]
     │
     ├─ ¿Método POST?          NO  → redirect registre.php
     ├─ ¿Campos vacíos?        SÍ  → $_SESSION['alert'] warning  → redirect
     ├─ ¿Email inválido?       SÍ  → $_SESSION['alert'] error    → redirect
     ├─ ¿Passwords distintas?  SÍ  → $_SESSION['alert'] error    → redirect
     ├─ ¿Password < 6 chars?   SÍ  → $_SESSION['alert'] warning  → redirect
     ├─ ¿Teléfono vacío?       SÍ  → $_SESSION['alert'] warning  → redirect
     │
     ├─ Database::conectar()
     ├─ Usuario::existeCorreo($email)
     │       └─ SÍ existe → $_SESSION['alert'] error → redirect
     │
     ├─ password_hash($password, PASSWORD_DEFAULT)
     │
     └─ Usuario::registrar($datos)
              │
              ├─ BEGIN TRANSACTION
              ├─ INSERT INTO usuarios (...)
              ├─ lastInsertId() → $id_usuario
              ├─ INSERT INTO cliente (id_usuarios)
              ├─ COMMIT
              │
              ├─ true  → $_SESSION['alert'] success + redirect: login.php
              └─ error → $_SESSION['alert'] error  → redirect registre.php
```

---

## 7. Archivos involucrados — resumen

| Archivo                                    | Rol en el flujo                              |
|--------------------------------------------|----------------------------------------------|
| `views/usuarios/registre.php`              | Formulario HTML + mostrar alertas SweetAlert |
| `controllers/UsuarioController.php`        | Validación, lógica de negocio, orquestación  |
| `models/usuario.php`                       | Queries SQL, transacción de inserción        |
| `config/database.php`                      | Conexión PDO a MySQL                         |
| `public/js/form-validation.js`             | Validación del lado cliente (frontend)       |

---

## 8. Notas importantes

- La contraseña **nunca se almacena en texto plano**. Se usa `password_hash()` con `PASSWORD_DEFAULT` (bcrypt).
- El registro usa una **transacción** para garantizar que si falla el INSERT en `cliente`, también se revierte el INSERT en `usuarios`.
- El campo `rol` en el formulario es un `hidden` con valor `"cliente"`. El controlador lo mapea a `id_rol = 3` — no hay forma de registrarse con otro rol desde esta vista.
- Tras un registro exitoso, la alerta incluye `redirect: 'login.php'` para llevar al usuario directamente al login.
