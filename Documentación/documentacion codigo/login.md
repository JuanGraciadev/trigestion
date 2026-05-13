# Login / Inicio de Sesión — Documentación Técnica

## Resumen

El flujo de inicio de sesión autentica a un usuario existente verificando sus credenciales contra la base de datos y redirige al dashboard correspondiente según su rol. Involucra tres capas: **Vista**, **Controlador** y **Modelo**, más la clase de conexión.

---

## Arquitectura del flujo

```
Usuario (navegador)
    │
    ▼
[Vista] views/usuarios/login.php
    │  Envía POST a:
    ▼
[Controlador] controllers/AuthController.php  →  método login()
    │  Instancia y llama métodos de:
    ▼
[Modelo] models/usuario.php
    │  Usa conexión de:
    ▼
[Config] config/database.php  →  Base de datos: dbmoova
    │
    ▼
[Sesión PHP] $_SESSION['usuario']
    │
    ▼
[Redirección por rol]
    ├─ id_rol = 1  →  views/dashboard/admin.php
    ├─ id_rol = 2  →  views/dashboard/trabajador.php
    └─ id_rol = 3  →  views/dashboard/cliente.php
```

---

## 1. Vista — `views/usuarios/login.php`

**Responsabilidad:** Renderizar el formulario de login y mostrar alertas de resultado.

### Campos del formulario

| Campo HTML (`name`) | Tipo     | Requerido | Descripción                    |
|---------------------|----------|-----------|--------------------------------|
| `email`             | email    | ✅        | Correo electrónico del usuario |
| `password`          | password | ✅        | Contraseña del usuario         |
| `remember`          | checkbox | ❌        | "Recordar sesión" (solo visual, no implementado en backend) |

### Destino del formulario

```html
<form action="../../controllers/AuthController.php" method="POST">
```

> No se envía ningún campo `accion`. El controlador ejecuta `login()` por defecto cuando `$_GET['accion']` no es `logout`.

### Sistema de alertas

La vista lee `$_SESSION['alert']` al cargar y lo destruye inmediatamente. Si existe, lo muestra con **SweetAlert2**.

```php
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
```

### Validación del lado cliente

Incluye `public/js/form-validation.js` para validación en tiempo real antes del envío.

### Redirecciones desde la vista

| Enlace                  | Destino              |
|-------------------------|----------------------|
| "Volver al inicio"      | `../../index.php`    |
| "Crear una ahora"       | `registre.php`       |
| "¿Olvidaste tu clave?"  | `#` (no implementado)|

---

## 2. Controlador — `controllers/AuthController.php`

**Responsabilidad:** Recibir el POST, validar credenciales, crear la sesión y redirigir por rol.

### Punto de entrada

El archivo se auto-ejecuta al final:

```php
$controller = new AuthController();
$accion = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} else {
    $controller->login();   // ← se ejecuta en el login normal
}
```

### Método `login()`

#### Paso a paso

```
1. Verificar que el método sea POST
   └─ Si no → redirect a login.php

2. Sanitizar con trim()
   └─ email, password

3. Validar campos vacíos
   └─ Si alguno vacío → alert warning → redirect login.php

4. Validar formato de email con filter_var(FILTER_VALIDATE_EMAIL)
   └─ Si inválido → alert error → redirect login.php

5. Conectar a BD via Database::conectar()

6. Instanciar Usuario($db)

7. Buscar usuario por email: obtenerPorEmail($email)
   └─ Si no existe O estado = 0 (inactivo) → alert error → redirect login.php

8. Verificar contraseña: password_verify($password, $usuario['password'])
   └─ Si no coincide → alert error → redirect login.php

9. Regenerar ID de sesión por seguridad: session_regenerate_id(true)

10. Guardar datos en $_SESSION['usuario']:
    └─ id_usuario, nombres, direccion, email, documento_numero, id_rol

11. Redirigir según id_rol:
    ├─ '1' → views/dashboard/admin.php
    ├─ '2' → views/dashboard/trabajador.php
    ├─ '3' → views/dashboard/cliente.php
    └─ otro → alert error → redirect login.php
```

#### Datos guardados en sesión

```php
$_SESSION['usuario'] = [
    'id_usuario'       => $usuario['id_usuario'],
    'nombres'          => $usuario['nombres'],
    'direccion'        => $usuario['direccion'],
    'email'            => $usuario['email'],
    'documento_numero' => $usuario['documento_numero'],
    'id_rol'           => $usuario['id_rol'],
];
```

> La contraseña **nunca** se guarda en sesión.

### Método `logout()`

Destruye completamente la sesión y redirige al login.

```php
public function logout() {
    session_unset();    // limpia todas las variables de sesión
    session_destroy();  // destruye la sesión en el servidor
    header("Location: ../views/usuarios/login.php");
    exit;
}
```

**Cómo se invoca el logout:**

```
GET controllers/AuthController.php?accion=logout
```

---

## 3. Modelo — `models/usuario.php`

**Responsabilidad:** Consultar la base de datos para obtener el usuario por email.

### Método usado en el login

#### `obtenerPorEmail(string $email): array|false`

Busca un usuario por su correo electrónico y retorna todos sus datos.

```sql
SELECT * FROM usuarios WHERE email = :email LIMIT 1
```

- Usa **prepared statement** con `bindParam` para prevenir SQL injection.
- Retorna un array asociativo con todos los campos del usuario, o `false` si no existe.

#### Campos relevantes que retorna

| Campo      | Uso en el controlador                              |
|------------|----------------------------------------------------|
| `password` | Comparado con `password_verify()`                  |
| `estado`   | Si es `0`, se bloquea el acceso                    |
| `id_rol`   | Determina a qué dashboard redirigir                |
| `id_usuario`, `nombres`, `direccion`, `email`, `documento_numero` | Se guardan en `$_SESSION['usuario']` |

---

## 4. Configuración de BD — `config/database.php`

| Parámetro      | Valor       |
|----------------|-------------|
| Host           | `127.0.0.1` |
| Puerto         | `3320`      |
| Base de datos  | `dbmoova`   |
| Usuario        | `root`      |
| Charset        | `utf8mb4`   |

---

## 5. Tabla de base de datos involucrada

### `usuarios`

| Columna            | Tipo         | Relevancia en login                        |
|--------------------|--------------|--------------------------------------------|
| `id_usuario`       | INT PK AUTO  | Se guarda en sesión                        |
| `nombres`          | VARCHAR(100) | Se guarda en sesión                        |
| `direccion`        | VARCHAR(100) | Se guarda en sesión                        |
| `email`            | VARCHAR(150) | Clave de búsqueda                          |
| `documento_numero` | VARCHAR(150) | Se guarda en sesión                        |
| `password`         | VARCHAR      | Hash bcrypt — verificado con `password_verify()` |
| `id_rol`           | INT FK       | Determina la redirección post-login        |
| `estado`           | TINYINT      | `0` = bloqueado, `1` = activo              |

---

## 6. Roles y redirecciones

| `id_rol` | Nombre        | Dashboard destino                    |
|----------|---------------|--------------------------------------|
| `1`      | Administrador | `views/dashboard/admin.php`          |
| `2`      | Trabajador    | `views/dashboard/trabajador.php`     |
| `3`      | Cliente       | `views/dashboard/cliente.php`        |

### Cómo los dashboards verifican la sesión

Cada dashboard protege su acceso al inicio del archivo PHP:

```php
// Ejemplo en admin.php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../usuarios/login.php");
    exit;
}
```

Si el usuario no tiene sesión activa o su rol no coincide, es redirigido al login automáticamente.

---

## 7. Diagrama de flujo completo

```
[login.php]
     │
     │  POST: email, password
     ▼
[AuthController::login()]
     │
     ├─ ¿Método POST?           NO  → redirect login.php
     ├─ ¿Campos vacíos?         SÍ  → alert warning  → redirect login.php
     ├─ ¿Email inválido?        SÍ  → alert error    → redirect login.php
     │
     ├─ Database::conectar()
     ├─ Usuario::obtenerPorEmail($email)
     │       └─ ¿No existe o estado=0?  → alert error → redirect login.php
     │
     ├─ password_verify($password, $hash)
     │       └─ ¿No coincide?           → alert error → redirect login.php
     │
     ├─ session_regenerate_id(true)
     ├─ $_SESSION['usuario'] = { id, nombres, direccion, email, doc, id_rol }
     │
     └─ switch(id_rol)
              ├─ '1' → redirect admin.php
              ├─ '2' → redirect trabajador.php
              ├─ '3' → redirect cliente.php
              └─ otro → alert error → redirect login.php
```

---

## 8. Flujo de logout

```
[Cualquier vista con enlace de logout]
     │
     │  GET: controllers/AuthController.php?accion=logout
     ▼
[AuthController::logout()]
     │
     ├─ session_unset()    → elimina variables de sesión
     ├─ session_destroy()  → destruye la sesión del servidor
     └─ redirect → views/usuarios/login.php
```

---

## 9. Archivos involucrados — resumen

| Archivo                               | Rol en el flujo                                      |
|---------------------------------------|------------------------------------------------------|
| `views/usuarios/login.php`            | Formulario HTML + mostrar alertas SweetAlert2        |
| `controllers/AuthController.php`      | Validación, autenticación, creación de sesión, logout|
| `models/usuario.php`                  | Query SQL para buscar usuario por email              |
| `config/database.php`                 | Conexión PDO a MySQL                                 |
| `public/js/form-validation.js`        | Validación del lado cliente (frontend)               |
| `views/dashboard/admin.php`           | Destino si id_rol = 1                                |
| `views/dashboard/trabajador.php`      | Destino si id_rol = 2                                |
| `views/dashboard/cliente.php`         | Destino si id_rol = 3                                |

---

## 10. Notas de seguridad

- **`password_verify()`** compara el texto plano con el hash bcrypt almacenado. Nunca se compara la contraseña directamente.
- **`session_regenerate_id(true)`** se llama tras autenticar exitosamente para prevenir ataques de **session fixation**.
- El campo `estado` permite bloquear cuentas sin eliminarlas. Un usuario con `estado = 0` recibe el mismo mensaje de error que uno inexistente, evitando revelar si el correo está registrado.
- La sesión solo almacena datos no sensibles. La contraseña hasheada **nunca** se incluye en `$_SESSION`.
- Todos los campos del POST se sanitizan con `trim()` antes de procesarse.
