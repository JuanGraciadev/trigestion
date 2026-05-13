# Módulo de Categorías — Documentación Técnica

## ¿Qué hace este módulo?

Las categorías son la forma de organizar los productos del catálogo. Desde esta pantalla el administrador puede crear categorías nuevas con nombre, descripción e imagen, editar las existentes, activarlas o desactivarlas, y también agregar productos directamente desde la card de cada categoría sin tener que ir al módulo de productos.

A diferencia del módulo de productos, este no restringe el acceso solo a administradores — cualquier usuario con sesión activa puede ver la pantalla. Sin embargo, en la práctica solo los administradores tienen el enlace en el sidebar.

---

## Archivos que participan

| Archivo | ¿Para qué sirve? |
|---|---|
| `views/dashboard/categorias.php` | La pantalla principal: cards de categorías, estadísticas y modales |
| `controllers/CategoriaController.php` | Recibe las acciones y coordina todo |
| `models/Categoria.php` | Consultas a la tabla `categoria` |
| `models/Producto.php` | Se usa para la acción de agregar producto desde una categoría |
| `config/database.php` | Conexión a MySQL |
| `views/layouts/header.php` | HTML base, librerías CSS y JS |
| `views/layouts/sidebar.php` | Menú lateral |
| `img/categorias/` | Donde se guardan las imágenes de las categorías |

---

## Cómo está organizado el flujo

```
El admin abre categorias.php
        │
        │  La vista carga todas las categorías y cuenta los productos de cada una
        │
        ▼
Hace una acción (crear / editar / cambiar estado / agregar producto)
        │
        │  El formulario envía los datos a CategoriaController.php
        │
        ▼
El controlador valida, procesa la imagen si hay una,
llama al modelo y guarda el resultado en $_SESSION['alert']
        │
        ▼
Redirige de vuelta a categorias.php
        │
        ▼
La vista muestra la alerta con SweetAlert2 y la destruye
```

---

## La vista — `views/dashboard/categorias.php`

### Lo primero que hace al cargar

Verifica que haya sesión activa (sin importar el rol) y luego carga los datos:

```php
$categorias = $categoriaModel->obtenerTodas();

// Para cada categoría, cuenta cuántos productos tiene
foreach ($categorias as $cat) {
    $productosPorCategoria[$cat['id_categoria']] =
        $categoriaModel->obtenerProductosPorCategoria($cat['id_categoria']);
}
```

Esto significa que si hay 10 categorías, se hacen 11 consultas a la base de datos al cargar la página: una para traer todas las categorías y una por cada categoría para contar sus productos. No es lo más eficiente, pero funciona bien para volúmenes pequeños.

### Las estadísticas de arriba

Tres cards calculadas en PHP con los datos ya cargados:

| Card | Cómo se calcula |
|---|---|
| Total Categorías | `count($categorias)` |
| Categorías Activas | Filtra las que tienen `estado == 1` |
| Productos Totales | Suma el `count()` de cada array en `$productosPorCategoria` |

### El grid de categorías

Cada categoría se muestra como una card con:

- Una zona de imagen con fondo degradado violeta/índigo. Si no tiene imagen, aparece un ícono de carpeta abierta.
- El estado (Activa / Inactiva) en la esquina superior derecha.
- Un chip en la esquina inferior izquierda que muestra cuántos productos tiene esa categoría.
- El nombre y descripción en el cuerpo de la card.
- Tres botones al pie: **+** (agregar producto), **Editar** (abre modal) y el botón de toggle estado (link directo).

### El buscador

Igual que en productos, filtra las cards en tiempo real con JavaScript comparando contra el atributo `data-nombre` de cada card. No hace peticiones al servidor.

### Los tres modales

**Modal Crear** — para crear una categoría nueva. Campos: nombre, descripción e imagen opcional. Envía POST a `CategoriaController.php?accion=crear`.

**Modal Editar** — se pre-llena con los datos de la categoría al hacer clic en "Editar". Si la categoría tiene imagen, la muestra en la zona de upload. Envía POST a `CategoriaController.php?accion=editar`.

**Modal Agregar Producto** — es el más diferente. Aparece al hacer clic en el botón "+" de cualquier card. Muestra el nombre de la categoría en el header del modal para que quede claro a cuál se está agregando. Solo pide nombre y precio — sin imagen. Envía POST a `CategoriaController.php?accion=agregarProducto`.

---

## El controlador — `controllers/CategoriaController.php`

### Protección de acceso

Solo verifica que haya sesión activa, sin validar el rol:

```php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}
```

Esto es diferente al controlador de productos, que sí exige `id_rol == 1`. Aquí cualquier usuario autenticado podría técnicamente enviar peticiones al controlador si conoce la URL.

### Cómo decide qué hacer

Lee `$_GET['accion']` y ejecuta el bloque correspondiente:

```
POST CategoriaController.php?accion=crear           → crea una categoría
POST CategoriaController.php?accion=editar          → edita una categoría
GET  CategoriaController.php?accion=toggleEstado    → cambia el estado
POST CategoriaController.php?accion=agregarProducto → crea un producto en esa categoría
```

### La función de imágenes — `procesarImagenCategoria()`

Funciona exactamente igual que la de productos, con dos diferencias:

1. Guarda los archivos en `img/categorias/` en lugar de `img/productos/`
2. El prefijo del nombre generado es `cat_` en lugar de `prod_`

El resto es idéntico: valida tipo MIME (JPG, PNG, WEBP, GIF), valida tamaño máximo de 5 MB, genera nombre único con `uniqid()` y retorna la ruta relativa, `null` si no se subió nada, o `false` si algo falló.

### Acción `crear`

Recibe el formulario del modal de creación. Procesa la imagen primero — si falla, cancela todo. Si está bien, arma el array y llama al modelo.

Datos que recibe:

| Campo | Descripción |
|---|---|
| `nombre` | Nombre de la categoría |
| `descripcion` | Descripción |
| `img_file` | Imagen (opcional) |

Hay algo curioso aquí: si no se sube imagen, el controlador hace esto:

```php
'imagen' => $imgResult ?? ($_POST['imagen'] ?? '')
```

`$imgResult` sería `null` (no se subió nada), entonces busca `$_POST['imagen']`, que tampoco existe en el formulario de creación. El resultado es que `imagen` queda como string vacío `''`. Esto es correcto para una categoría nueva sin imagen.

### Acción `editar`

Igual que crear, pero con el manejo de imagen existente:

```php
'imagen' => $imgResult ?? ($_POST['img_actual'] ?? '')
```

Si se sube imagen nueva, se usa esa. Si no, se conserva la ruta que viene en el campo oculto `img_actual`. Así el admin puede cambiar el nombre o descripción sin perder la imagen.

Datos adicionales:

| Campo | Descripción |
|---|---|
| `id_categoria` | ID de la categoría a editar (campo oculto) |
| `img_actual` | Ruta de la imagen actual (campo oculto) |

### Acción `toggleEstado`

Recibe el ID y el estado actual por GET, igual que en productos. El modelo invierte el valor.

```
URL: ?accion=toggleEstado&id=3&estado=1
→ la categoría 3 pasará a estado 0 (inactiva)
```

### Acción `agregarProducto`

Esta es la acción más interesante del módulo porque usa el modelo de `Producto` en lugar del de `Categoria`. Básicamente crea un producto nuevo asignándolo directamente a la categoría desde la que se abrió el modal.

```php
$datos = [
    'nombre'       => $_POST['nombre'],
    'precio'       => $_POST['precio'],
    'img'          => '',   // sin imagen
    'id_usuario'   => $_SESSION['usuario']['id_usuario'],
    'id_categoria' => $_POST['id_categoria']
];

$productoModel->crear($datos);
```

El producto se crea sin imagen (campo `img` vacío). Si el admin quiere agregarle imagen después, tendría que ir al módulo de productos y editarlo desde allí.

El `id_categoria` viene del campo oculto del modal, que se rellena con JavaScript cuando el admin hace clic en el "+" de una card específica.

---

## El modelo — `models/Categoria.php`

### El mismo truco del constructor

Al igual que el modelo de Producto, el constructor de Categoria llama a `checkAndCreateEstadoColumn()` cada vez que se instancia. Verifica si la columna `estado` existe en la tabla `categoria` y la crea si no está. Es una medida de compatibilidad que genera una consulta extra en cada carga de página.

### `obtenerTodas()`

Trae todas las categorías ordenadas de la más reciente a la más antigua. No hace JOIN con productos — el conteo de productos se hace por separado con `obtenerProductosPorCategoria()`.

```sql
SELECT * FROM categoria ORDER BY id_categoria DESC
```

### `crear($datos)`

Inserta una categoría nueva. El estado siempre empieza en `1` (activa).

```sql
INSERT INTO categoria (nombre, descripcion, imagen, estado)
VALUES (:nombre, :descripcion, :imagen, 1)
```

### `editar($id, $datos)`

Actualiza nombre, descripción e imagen. No toca el estado.

```sql
UPDATE categoria
SET nombre = :nombre, descripcion = :descripcion, imagen = :imagen
WHERE id_categoria = :id
```

### `cambiarEstado($id, $estado)`

Recibe el estado actual e invierte el valor antes de guardarlo, igual que en el modelo de Producto.

```sql
UPDATE categoria SET estado = :estado WHERE id_categoria = :id
```

### `obtenerProductosPorCategoria($id_categoria)`

Trae todos los productos que pertenecen a una categoría específica. Se usa en la vista para mostrar el contador de productos en cada card.

```sql
SELECT * FROM producto
WHERE id_categoria = :id_categoria
ORDER BY id_producto DESC
```

---

## La tabla en la base de datos — `categoria`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_categoria` | INT PK AUTO | Identificador único |
| `nombre` | VARCHAR | Nombre de la categoría |
| `descripcion` | TEXT | Descripción |
| `imagen` | VARCHAR | Ruta relativa a la imagen (`../../img/categorias/...`) |
| `estado` | TINYINT | `1` = activa, `0` = inactiva |

---

## El JavaScript en la vista

### Pre-llenar el modal de edición

Cuando el admin hace clic en "Editar", se llama a `openEditModal(categoria)` con el objeto serializado desde PHP:

```javascript
function openEditModal(categoria) {
    document.getElementById('edit_id_categoria').value  = categoria.id_categoria;
    document.getElementById('edit_nombre').value        = categoria.nombre;
    document.getElementById('edit_descripcion').value   = categoria.descripcion;
    document.getElementById('edit_img_actual').value    = categoria.imagen || '';
    document.getElementById('editCatSubtitle').textContent = 'Editando: ' + categoria.nombre;
    // Si tiene imagen, la muestra en la zona de upload
}
```

### Pre-llenar el modal de agregar producto

Cuando el admin hace clic en "+" en una card, se llama a `openAddProductModal()` con el ID y nombre de la categoría:

```javascript
function openAddProductModal(idCategoria, nombreCategoria) {
    document.getElementById('add_prod_id_categoria').value = idCategoria;
    document.getElementById('add_prod_cat_name').textContent = nombreCategoria;
    openModal('modalAddProducto');
}
```

El nombre de la categoría aparece en el subtítulo del header del modal para que el admin sepa exactamente a cuál está agregando el producto.

---

## Flujo completo — Crear una categoría

```
Admin hace clic en "Nueva Categoría"
        │
        ▼
Se abre el modalCrear
Admin llena: nombre, descripción y opcionalmente sube imagen
        │
        │  POST → CategoriaController.php?accion=crear
        ▼
procesarImagenCategoria()
  ├─ No se subió imagen → null → imagen queda como ''
  ├─ Imagen inválida    → false → alert error → redirect
  └─ Imagen válida      → guarda en img/categorias/ → retorna ruta
        │
        ▼
Categoria::crear($datos)
  └─ INSERT INTO categoria (nombre, descripcion, imagen, estado=1)
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → categorias.php → muestra alerta → listo
```

## Flujo completo — Editar una categoría

```
Admin hace clic en "Editar" en una card
        │
        ▼
openEditModal(categoria) pre-llena el modalEditar
Admin modifica lo que necesita
        │
        │  POST → CategoriaController.php?accion=editar
        ▼
procesarImagenCategoria()
  ├─ No se subió imagen → null → se usa img_actual (la que ya tenía)
  ├─ Imagen inválida    → false → alert error → redirect
  └─ Imagen válida      → guarda nueva imagen → retorna nueva ruta
        │
        ▼
Categoria::editar($id, $datos)
  └─ UPDATE categoria SET nombre, descripcion, imagen WHERE id_categoria = :id
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → categorias.php → muestra alerta → listo
```

## Flujo completo — Agregar producto desde una categoría

```
Admin hace clic en "+" en la card de una categoría
        │
        ▼
openAddProductModal(id, nombre) pre-llena el modalAddProducto
Admin llena: nombre del producto y precio
        │
        │  POST → CategoriaController.php?accion=agregarProducto
        ▼
Producto::crear($datos)
  └─ INSERT INTO producto (nombre, precio, img='', id_usuario, id_categoria, estado=1)
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → categorias.php → muestra alerta → listo
```

---

## Diferencias clave con el módulo de productos

Vale la pena comparar los dos módulos porque son muy similares pero tienen algunas diferencias importantes:

**Protección de acceso:** Productos exige `id_rol == 1` tanto en la vista como en el controlador. Categorías solo verifica que haya sesión activa, sin importar el rol.

**Acción extra:** Categorías tiene la acción `agregarProducto` que no existe en productos. Permite crear un producto básico (sin imagen) directamente desde la pantalla de categorías.

**Conteo de productos:** La vista de categorías hace una consulta adicional por cada categoría para contar sus productos. La vista de productos no necesita esto.

**Campo `id_usuario`:** Al crear un producto desde el módulo de productos, el `id_usuario` viene de la sesión. Al crearlo desde el módulo de categorías (acción `agregarProducto`), también viene de la sesión — consistente en ambos casos.
