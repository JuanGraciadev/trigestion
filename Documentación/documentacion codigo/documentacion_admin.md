# Documentación del Dashboard del Administrador (`admin.php`)

El archivo `admin.php` es el panel principal (Dashboard) para los usuarios con rol de Administrador dentro de la plataforma **MOOVA! (Trigestion)**. Desde aquí, el administrador tiene control total sobre los integrantes del sistema, pudiendo visualizar, crear, editar y cambiar el estado (activar/suspender) de cualquier usuario (Administradores, Trabajadores y Clientes).

A continuación, se detalla la estructura del código y cómo funciona cada una de sus partes.

---

## 1. Protección y Lógica Backend (PHP)

El archivo comienza asegurando que sólo los usuarios autorizados puedan acceder:

### Verificación de Sesión y Roles
- **Autenticación:** Se verifica si existe la variable de sesión `$_SESSION['usuario']`. Si no, se redirige inmediatamente a `login.php`.
- **Autorización:** Se comprueba que el `id_rol` sea igual a `1` (Administrador). Si es `2` (Trabajador de Producción) o `3` (Cliente), se les redirige forzosamente a sus respectivos paneles (`trabajador.php` o `cliente.php`), previniendo el acceso no autorizado mediante la alteración de URLs.

### Conexión a Base de Datos y Obtención de Datos
- Se instancia la clase `Database` para establecer la conexión mediante PDO.
- Se utiliza el modelo `Usuario` para extraer toda la información necesaria:
  - `$usuarios = $usuarioModel->obtenerTodos();` — Obtiene la lista completa de usuarios registrados.
  - `$roles = $usuarioModel->obtenerRoles();` — Obtiene los roles disponibles para el formulario de creación y edición.

### Cálculos Estadísticos (KPIs)
El script calcula métricas clave en tiempo real utilizando las funciones nativas de arreglos de PHP (`array_filter`). Estas métricas sirven para alimentar las tarjetas superiores:
- **Total de usuarios**
- **Usuarios activos** (estado = 1)
- **Total de administradores** (rol = 1)
- **Total de clientes** (rol = 3)

---

## 2. Interfaz de Usuario (UI) y Diseño (HTML/Tailwind)

La interfaz gráfica de este panel sigue el estilo visual "SaaS premium" y *Glassmorphism* característico del proyecto. 

### Cabecera (Header) y Botón de Acción
- **Título Premium:** Utiliza gradientes de texto (`premium-gradient-text`) y la tipografía moderna definida para el proyecto (`Outfit`).
- **Botón "Nuevo Registro":** Abre el modal de creación de usuarios. Posee animaciones avanzadas de hover en CSS (`group-hover:translate-y-0`) que revelan un fondo translúcido al pasar el ratón, dando un efecto táctil.

### Tarjetas de Resumen (KPI Cards)
Exhiben las estadísticas calculadas en el backend. 
- Cada tarjeta utiliza el diseño de "cristal" (`glass-card`) y contiene en el fondo orbes de luz desenfocados (`blur-[30px]`) que reaccionan animándose (`scale-150`) cuando el administrador pasa el ratón por encima de la tarjeta.

---

## 3. Tabla Dinámica de Usuarios

Es el núcleo visual del panel, donde se lista el directorio completo. Se renderiza dinámicamente mediante un bucle `foreach` en PHP.

- **Diseño de Filas (`table-separated`):** Las filas de la tabla están separadas visualmente simulando tarjetas individuales, lo que mejora drásticamente la legibilidad en comparación con las tablas HTML tradicionales.
- **Avatares Dinámicos:** Si no hay foto de perfil, el sistema genera automáticamente un avatar tomando la primera letra del nombre del usuario (`substr`) y le asigna un color de fondo (gradiente) específico dependiendo de su rol.
- **Identificadores Visuales:**
  - El **Estado** se indica de forma clara con una insignia verde pulsante (Activo) o una insignia gris de bloqueo (Suspendido).
  - El **Rol** tiene su propia insignia con icono específico (`fa-user-shield` para admin, `fa-hard-hat` para trabajador).

### Herramientas de la Tabla (Filtros y Búsqueda)
La tabla incluye herramientas implementadas completamente en **JavaScript (Vanilla)** del lado del cliente para no recargar la página:
- **Buscador (Search):** Filtra filas en tiempo real al escribir. Compara el texto introducido con el contenido completo de las filas (`.usuario-row`) y las oculta (`display: none`) si no hay coincidencia.
- **Filtro por Rol:** Botones interactivos que muestran u ocultan filas dependiendo del atributo HTML `data-rol` asignado a cada `<tr>`.

---

## 4. Gestión de Usuarios (Modales y Formularios)

Para interactuar con los datos (Crear / Editar), el sistema utiliza Modales emergentes con efecto cristalizado y desenfoque de fondo (`backdrop-blur-sm`).

### Modal "Nuevo Usuario"
- **Estructura:** Formulario (`<form>`) que envía los datos mediante el método `POST` al endpoint controlador `AdminUsuarioController.php?accion=crear`.
- **Campos:** Nombres, Dirección, Correo Electrónico (requerido como único por la base de datos), Contraseña (encriptada en el backend) y Selector de Rol dinámico.

### Modal "Editar Usuario"
- **Estructura:** Formulario similar que envía los datos al endpoint `AdminUsuarioController.php?accion=editar`.
- **Rellenado Automático (Magia Frontend):** Cuando el administrador hace clic en el botón del lápiz en la tabla, se invoca la función JS `openEditModal(u)`, la cual recibe un objeto JSON generado por PHP con los datos del usuario. El script automáticamente rellena los campos del formulario (Nombres, Dirección, etc.) en milisegundos.
- **Reglas de Negocio en Edición:** 
  - El campo **Correo Electrónico** se muestra como `readonly` (solo lectura) para evitar inconsistencias en el sistema de inicio de sesión. 
  - La **Contraseña** es opcional; si se deja vacía en la interfaz, el controlador backend entiende que no debe sobrescribirla.

### Suspender / Activar (Toggle Estado)
- Se realiza a través de un botón de acción rápida en la tabla. Este botón es un simple enlace (`<a>`) que apunta a `AdminUsuarioController.php?accion=toggleEstado` enviando el ID del usuario y su estado actual por método GET.

---

## 5. Sistema de Alertas (SweetAlert2)

El archivo incorpora un bloque de código inteligente en PHP+JS que verifica si existe una variable de sesión `$_SESSION['alert']`.
- Si el controlador (después de crear, editar o suspender) define esta alerta, la página inyecta un script para renderizar un modal de *SweetAlert2*.
- **Personalización Premium:** El script JS inyectado anula el CSS estándar de SweetAlert mediante un objeto `iconConfig` y modifica el DOM al vuelo (`didOpen`). Esto personaliza radicalmente el diseño (bordes redondeados, tipografía del sistema, sombras de colores brillantes según el tipo de alerta y botones con gradiente) para asegurar que ninguna alerta rompa la inmersión del diseño general de la plataforma.

---

## Resumen del Flujo de Trabajo
1. El Administrador carga la URL `admin.php`.
2. PHP verifica permisos, obtiene usuarios de la base de datos, calcula métricas y renderiza la vista HTML final.
3. El Administrador interactúa: puede usar la barra de búsqueda o filtros para encontrar un usuario instantáneamente (vía JS).
4. Al hacer clic en una acción, se abre un modal de edición o se redirige directamente al controlador (caso "Suspender").
5. El controlador (`AdminUsuarioController.php`) procesa la solicitud, actualiza la base de datos, establece un mensaje de alerta en la sesión, y redirige de vuelta a `admin.php`.
6. Al recargar `admin.php`, PHP lee la alerta guardada en sesión, renderiza el cuadro emergente premium informando del éxito o error, la alerta se elimina de la memoria, y el ciclo se completa de forma limpia.
