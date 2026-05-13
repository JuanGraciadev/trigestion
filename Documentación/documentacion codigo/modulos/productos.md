# Módulo de Productos — Documentación Técnica

## ¿Qué hace este módulo?

Este módulo le permite al administrador gestionar el catálogo de productos de la plataforma. Desde aquí se pueden crear productos nuevos con nombre, precio, categoría e imagen, editar los existentes y activar o desactivar cualquier producto sin necesidad de eliminarlo.

Solo los usuarios con rol de administrador (id_rol = 1) pueden acceder. Si alguien más intenta entrar directamente a la URL, el sistema lo redirige automáticamente.

---

## Archivos que participan

| Archivo | ¿Para qué sirve? |
|---|---|
| `views/dashboard/productos.php` | La pantalla que ve el administrador: cards de productos, estadísticas y modales |
| `controllers/ProductoController.php` | Recibe las acciones del formulario y decide qué hacer |
| `models/Producto.php` | Habla directamente con la base de datos |
| `config/database.php` | Abre la conexión a MySQL |
| `views/layouts/header.php` | Carga el HTML base, Tailwind, FontAwesome y SweetAlert2 |
| `views/layouts/sidebar.php` | El menú lateral de navegación |
| `img/productos/` | Carpeta donde se guardan las imágenes subidas |

---

## Cómo está organizado el flujo

```
El admin abre productos.php
        │
        │  La vista carga los productos y categorías directamente del modelo
        │
        ▼
Hace una acción (crear / editar / cambiar estado)
        │
        │  El formulario envía los datos a ProductoController.php
        │
        ▼
El controlador valida, procesa la imagen si hay una,
llama al modelo y guarda el resultado en $_SESSION['alert']
        │
        ▼
Redirige de vuelta a productos.php
        │
        ▼
La vista muestra la alerta con SweetAlert2 y la destruye
```

---

## La vista — `views/dashboard/productos.php`

### Lo primero que hace al cargar

Antes de mostrar nada, verifica que el usuario tenga sesión activa y sea administrador:

```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    // redirige a trabajador.php o login.php según el caso
    exit;
}
```

Después conecta a la base de datos y carga los datos que necesita para renderizar la página:

```php
$productos  = $productoModel->obtenerTodos();   // todos los productos con su categoría
$categorias = $categoriaModel->obtenerTodas();  // para los <select> de los modales
```

### Las estadísticas de arriba

Las cuatro cards de estadísticas se calculan en PHP antes de renderizar, sin consultas adicionales a la base de datos — simplemente se filtran los datos que ya se cargaron:

| Card | Cómo se calcula |
|---|---|
| Total Productos | `count($productos)` |
| Activos | Filtra los que tienen `estado == 1` |
| Inactivos | Total menos activos |
| Categorías | `count($categorias)` |

### El grid de productos

Cada producto se muestra como una card con:

- Una zona de imagen con fondo degradado azul claro. Si el producto no tiene imagen, aparece un ícono de caja.
- El precio en un chip blanco en la esquina superior derecha.
- El estado (Activo / Inactivo) también en la esquina superior derecha.
- El nombre de la categoría en un chip en la esquina inferior izquierda de la imagen.
- El nombre del producto en el cuerpo de la card.
- Dos botones al pie: **Editar** (abre el modal) e **Inhabilitar/Activar** (link directo al controlador).

### El buscador

Hay un campo de texto en la barra de herramientas que filtra las cards en tiempo real con JavaScript. Compara el texto escrito contra el atributo `data-nombre` de cada card, que contiene el nombre del producto en minúsculas.

### Los modales

Hay dos modales en esta vista:

**Modal Crear** — aparece al hacer clic en "Nuevo Producto". Tiene campos para nombre, precio, categoría e imagen. Al enviarlo hace POST a `ProductoController.php?accion=crear`.

**Modal Editar** — se abre al hacer clic en "Editar" en cualquier card. Los campos se pre-llenan automáticamente con los datos del producto usando JavaScript. Si el producto ya tiene imagen, se muestra en la zona de upload. Al enviarlo hace POST a `ProductoController.php?accion=editar`.

---

## El controlador — `controllers/ProductoController.php`

### Protección de acceso

Lo primero que hace el archivo es verificar la sesión. Si no hay sesión o el rol no es 1, redirige inmediatamente:

```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ...");
    exit;
}
```

Esto es importante porque el controlador recibe datos de formularios — si no se protegiera, cualquiera podría enviar peticiones directamente a la URL.

### Cómo decide qué hacer

Lee el parámetro `accion` de la URL y ejecuta el bloque correspondiente:

```
GET/POST ProductoController.php?accion=crear        → crea un producto
GET/POST ProductoController.php?accion=editar       → edita un producto
GET      ProductoController.php?accion=toggleEstado → cambia el estado
```

### La función de imágenes — `procesarImagenProducto()`

Esta función auxiliar se encarga de todo lo relacionado con el archivo subido. Antes de guardar nada, hace tres verificaciones:

1. ¿Se subió un archivo sin errores?
2. ¿El tipo MIME es JPG, PNG, WEBP o GIF?
3. ¿El tamaño es menor a 5 MB?

Si alguna falla, retorna `false` y el controlador cancela la operación mostrando un error. Si todo está bien, genera un nombre único para el archivo usando `uniqid()` con el prefijo `prod_`, lo mueve a `img/productos/` y retorna la ruta relativa que se guardará en la base de datos.

Si no se subió ningún archivo (campo vacío), retorna `null` — lo que significa "no hay imagen nueva, usar la que ya había".

```
Archivo subido → validar tipo y tamaño → mover a img/productos/
                                              │
                              retorna: '../../img/productos/prod_abc123.jpg'
                              o false si algo falló
                              o null si no se subió nada
```

### Acción `crear`

Recibe el formulario del modal de creación. Primero intenta procesar la imagen. Si la imagen falla, cancela todo y muestra error. Si la imagen está bien (o no se subió ninguna), arma el array de datos y llama al modelo.

Datos que recibe del formulario:

| Campo | Descripción |
|---|---|
| `nombre` | Nombre del producto |
| `precio` | Precio unitario |
| `id_categoria` | ID de la categoría seleccionada |
| `img_file` | Archivo de imagen (opcional) |

El `id_usuario` no viene del formulario — se toma directamente de `$_SESSION['usuario']['id_usuario']` para que no pueda manipularse.

### Acción `editar`

Funciona igual que crear, con una diferencia importante en el manejo de la imagen:

```php
'img' => $imgResult ?? ($_POST['img_actual'] ?? '')
```

Si se subió una imagen nueva (`$imgResult` tiene valor), se usa esa. Si no se subió nada (`$imgResult` es `null`), se conserva la ruta de la imagen anterior que viene en el campo oculto `img_actual` del formulario. Así el administrador puede editar el nombre o precio sin perder la imagen que ya tenía el producto.

Datos adicionales que recibe:

| Campo | Descripción |
|---|---|
| `id_producto` | ID del producto a editar (campo oculto) |
| `img_actual` | Ruta de la imagen actual (campo oculto, por si no se sube nueva) |

### Acción `toggleEstado`

Recibe el ID del producto y su estado actual por GET. El modelo se encarga de invertir el valor (si era 1 pasa a 0, si era 0 pasa a 1). El controlador solo le pasa el estado actual y el modelo decide el nuevo.

```
URL: ?accion=toggleEstado&id=5&estado=1
→ el producto 5 pasará a estado 0 (inactivo)
```

---

## El modelo — `models/Producto.php`

### Una cosa curiosa al instanciar

Cuando se crea un objeto `Producto`, el constructor llama automáticamente a `checkAndCreateEstadoColumn()`. Esta función verifica si la columna `estado` existe en la tabla `producto` y, si no existe, la crea con `ALTER TABLE`. Es una medida de compatibilidad para bases de datos antiguas que no tenían esa columna. Si falla (por falta de permisos), simplemente lo ignora.

### `obtenerTodos()`

Trae todos los productos ordenados del más reciente al más antiguo, incluyendo el nombre de la categoría mediante un JOIN:

```sql
SELECT p.*, c.nombre as categoria_nombre
FROM producto p
LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
ORDER BY p.id_producto DESC
```

Usa `LEFT JOIN` para que los productos sin categoría asignada también aparezcan (con `categoria_nombre` como NULL).

### `crear($datos)`

Inserta un producto nuevo. El estado siempre se crea como `1` (activo) — no hay opción de crear un producto inactivo desde el formulario.

```sql
INSERT INTO producto (nombre, precio, img, id_usuario, id_categoria, estado)
VALUES (:nombre, :precio, :img, :id_usuario, :id_categoria, 1)
```

### `editar($id, $datos)`

Actualiza nombre, precio, imagen y categoría. No toca el estado ni el id_usuario — esos no se pueden cambiar desde la edición.

```sql
UPDATE producto
SET nombre = :nombre, precio = :precio, img = :img, id_categoria = :id_categoria
WHERE id_producto = :id
```

### `cambiarEstado($id, $estado)`

Recibe el estado **actual** del producto e invierte el valor antes de guardarlo:

```php
$nuevoEstado = $estado == 1 ? 0 : 1;
```

```sql
UPDATE producto SET estado = :estado WHERE id_producto = :id
```

---

## La tabla en la base de datos — `producto`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_producto` | INT PK AUTO | Identificador único |
| `nombre` | VARCHAR | Nombre del producto |
| `precio` | DECIMAL | Precio unitario |
| `img` | VARCHAR | Ruta relativa a la imagen (`../../img/productos/...`) |
| `id_usuario` | INT FK | Quién creó el producto (referencia a `usuarios`) |
| `id_categoria` | INT FK | Categoría a la que pertenece (referencia a `categoria`) |
| `estado` | TINYINT | `1` = activo, `0` = inactivo |

---

## El JavaScript en la vista

### Abrir y cerrar modales

Los modales usan animaciones de opacidad y escala para abrirse y cerrarse suavemente. Las funciones `openModal(id)` y `closeModalAnim(modalId, contentId)` manejan esto.

### Pre-llenar el modal de edición

Cuando el admin hace clic en "Editar" en una card, se llama a `openEditModal(producto)` con el objeto del producto serializado como JSON desde PHP:

```php
onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)"
```

La función JavaScript toma ese objeto y rellena cada campo del modal:

```javascript
function openEditModal(producto) {
    document.getElementById('edit_id_producto').value  = producto.id_producto;
    document.getElementById('edit_nombre').value       = producto.nombre;
    document.getElementById('edit_precio').value       = producto.precio;
    document.getElementById('edit_id_categoria').value = producto.id_categoria;
    document.getElementById('edit_img_actual').value   = producto.img || '';
    // Si tiene imagen, la muestra en la zona de upload
    // Si no, limpia la zona
}
```

### La zona de carga de imágenes

Ambos modales tienen una zona de drag & drop para imágenes. Soporta tanto hacer clic para seleccionar como arrastrar y soltar el archivo. Al seleccionar una imagen, la muestra como preview antes de enviar el formulario. También valida el tamaño (máx 5 MB) en el frontend antes de enviarlo.

### El buscador

```javascript
document.getElementById('searchProductos').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.producto-card').forEach(card => {
        card.style.display = card.dataset.nombre.includes(term) ? '' : 'none';
    });
});
```

Filtra en tiempo real sin recargar la página. Cada card tiene `data-nombre` con el nombre en minúsculas para que la comparación no sea sensible a mayúsculas.

---

## Flujo completo — Crear un producto

```
Admin hace clic en "Nuevo Producto"
        │
        ▼
Se abre el modalCrear
Admin llena: nombre, precio, categoría y opcionalmente sube una imagen
        │
        │  POST → ProductoController.php?accion=crear
        ▼
procesarImagenProducto()
  ├─ No se subió imagen → retorna null (sin imagen)
  ├─ Imagen inválida    → retorna false → alert error → redirect
  └─ Imagen válida      → guarda en img/productos/ → retorna ruta
        │
        ▼
Producto::crear($datos)
  └─ INSERT INTO producto (nombre, precio, img, id_usuario, id_categoria, estado=1)
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → productos.php → muestra alerta → listo
```

## Flujo completo — Editar un producto

```
Admin hace clic en "Editar" en una card
        │
        ▼
openEditModal(producto) pre-llena el modalEditar con los datos actuales
Admin modifica lo que necesita (puede o no subir imagen nueva)
        │
        │  POST → ProductoController.php?accion=editar
        ▼
procesarImagenProducto()
  ├─ No se subió imagen → null → se usa img_actual (la que ya tenía)
  ├─ Imagen inválida    → false → alert error → redirect
  └─ Imagen válida      → guarda nueva imagen → retorna nueva ruta
        │
        ▼
Producto::editar($id, $datos)
  └─ UPDATE producto SET nombre, precio, img, id_categoria WHERE id_producto = :id
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → productos.php → muestra alerta → listo
```

## Flujo completo — Cambiar estado

```
Admin hace clic en "Inhabilitar" o "Activar" en una card
        │
        │  GET → ProductoController.php?accion=toggleEstado&id=X&estado=Y
        ▼
Producto::cambiarEstado($id, $estado_actual)
  └─ nuevo_estado = estado_actual == 1 ? 0 : 1
  └─ UPDATE producto SET estado = :nuevo_estado WHERE id_producto = :id
        │
  ├─ true  → alert success ("habilitado" o "inhabilitado")
  └─ false → alert error
        │
        ▼
Redirect → productos.php → muestra alerta → listo
```

---

## Cosas a tener en cuenta

**Las imágenes antiguas no se eliminan.** Cuando se edita un producto y se sube una imagen nueva, la imagen anterior queda en `img/productos/` sin borrarse. Con el tiempo esto puede acumular archivos huérfanos en el servidor.

**El estado siempre empieza en activo.** No hay forma de crear un producto inactivo desde la interfaz — el INSERT siempre pone `estado = 1`.

**La columna `estado` se crea automáticamente si no existe.** El constructor del modelo verifica esto cada vez que se instancia la clase. Es útil para compatibilidad pero genera una consulta extra en cada carga.

**El buscador y los filtros son solo del lado del cliente.** Todos los productos ya están cargados en la página cuando llega al navegador. El buscador solo muestra u oculta cards con CSS — no hace ninguna petición al servidor.

**El `id_usuario` del creador no se puede cambiar.** Una vez creado el producto, el campo `id_usuario` queda fijo. La edición no lo toca.
