# Módulo de Producción — Documentación Técnica

## ¿Qué hace este módulo?

El módulo de producción es el corazón operativo del sistema. Aquí se registra cuándo se empieza a fabricar un producto, cuántas unidades se van a producir y, opcionalmente, qué materia prima del inventario se va a consumir.

Cuando una producción se marca como finalizada, el sistema hace tres cosas automáticamente en cadena:
1. Cambia el estado de la producción a "Finalizada"
2. Registra las unidades producidas en el inventario de productos terminados
3. Descuenta la cantidad consumida del inventario de materia prima (si se vinculó una)

Todo esto ocurre en una sola acción del usuario — hacer clic en "Finalizar Producción". Es el módulo con más efectos secundarios del sistema.

Cualquier usuario con sesión activa puede acceder, sin restricción de rol.

---

## Archivos que participan

| Archivo | ¿Para qué sirve? |
|---|---|
| `views/dashboard/produccion.php` | La pantalla principal con las cards de producción |
| `controllers/ProduccionController.php` | Orquesta las acciones de crear y finalizar |
| `models/Produccion.php` | Consultas a la tabla `produccion` |
| `models/InventarioProductos.php` | Registra el ingreso al inventario de productos terminados al finalizar |
| `models/InventarioMP.php` | Descuenta el stock de materia prima al finalizar |
| `config/database.php` | Conexión a MySQL |
| `views/layouts/header.php` | HTML base y librerías |
| `views/layouts/sidebar.php` | Menú lateral |

---

## Cómo está organizado el flujo

```
Admin abre produccion.php
        │
        │  Carga todas las producciones, productos disponibles
        │  y registros de materia prima del inventario
        │
        ▼
Dos caminos posibles:

[Iniciar producción]                    [Finalizar producción]
        │                                       │
        ▼                                       ▼
Abre modal, llena datos           Clic en "Finalizar Producción"
        │                                       │
        │  POST ?accion=crear                   │  GET ?accion=finalizar&id=X
        ▼                                       ▼
Crea registro en `produccion`     1. Cambia estado a "Finalizada"
con estado "En Producción"        2. Registra ingreso en inventario_productos
        │                         3. Descuenta stock en inventario_materia_prima
        ▼                                       │
Redirect → produccion.php         Redirect → produccion.php
```

---

## La vista — `views/dashboard/produccion.php`

### Lo que carga al abrir

Verifica sesión activa y luego hace tres consultas para tener todo lo que necesita:

```php
$producciones  = $produccionModel->obtenerTodas();       // historial de producciones
$productos     = $productoModel->obtenerTodos();          // para el select del modal
$materiaPrima  = $inventarioMPModel->obtenerTodos();      // para el select de MP del modal
```

### Las cards de producción

Cada producción se muestra como una card con:
- Un badge en la esquina superior derecha: **"En Proceso"** (ámbar, pulsante) o **"Finalizada"** (verde)
- El código de lote de producción como título
- El nombre del producto que se está fabricando
- Un bloque de datos con cantidad, nombre del operario y descripción si la tiene
- Un botón al pie que cambia según el estado:
  - Si está en proceso: botón verde "Finalizar Producción" (link al controlador)
  - Si ya finalizó: botón gris deshabilitado "Lote Completado"

### El modal de iniciar producción

Tiene cuatro campos:

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `lote_produccion` | text | ✅ | Código identificador del lote de producción |
| `cantidad` | number | ✅ | Cuántas unidades se van a producir |
| `id_producto` | select | ✅ | Qué producto terminado se va a fabricar |
| `id_inventario_materia` | select | ❌ | Qué registro de MP se va a consumir |

El campo de materia prima es opcional. Si se deja en blanco, la producción se registra sin vínculo a ningún lote de materia prima y al finalizar no se descuenta nada del inventario de MP.

El select de materia prima muestra cada registro con su tipo de envase, capacidad, código de lote de origen y cantidad disponible, para que el operario pueda identificar exactamente qué está usando.

---

## El controlador — `controllers/ProduccionController.php`

### Protección de acceso

Solo verifica sesión activa, sin validar el rol.

### Acción `crear`

Recibe el formulario del modal. El `id_usuario` viene de la sesión, no del formulario. El estado siempre se crea como `'En Producción'` — no hay opción de crear una producción ya finalizada.

Si `id_inventario_materia` llega vacío del formulario, se guarda como `null`:

```php
'id_inventario_materia' => !empty($_POST['id_inventario_materia'])
                           ? $_POST['id_inventario_materia']
                           : null
```

Esto es importante porque más adelante, al finalizar, el controlador verifica si este campo es `null` para decidir si descuenta o no la materia prima.

### Acción `finalizar` — la más importante

Esta acción recibe solo el `id` de la producción por GET. Lo que hace internamente son tres pasos encadenados:

**Paso 1 — Cambiar estado:**
```php
$produccionModel->actualizarEstado($id, 'Finalizada')
```

**Paso 2 — Registrar en inventario de productos terminados:**
```php
$prod = $produccionModel->obtenerPorId($id);  // necesita los datos de la producción

$datosInv = [
    'id_produccion' => $id,
    'id_producto'   => $prod['id_producto'],
    'id_usuario'    => $_SESSION['usuario']['id_usuario'],
    'cantidad'      => $prod['cantidad'],
    'bodega'        => $_GET['bodega'] ?? 'Principal',
];
$inventarioModel->registrarIngreso($datosInv);
```

La bodega puede venir como parámetro GET (`?bodega=X`), pero en la interfaz actual el botón "Finalizar" no envía ese parámetro, así que siempre queda como `'Principal'`.

**Paso 3 — Descontar materia prima (solo si tiene vinculada):**
```php
if (!empty($prod['id_inventario_materia'])) {
    $inventarioMPModel->descontarStock(
        $prod['id_inventario_materia'],
        $prod['cantidad']
    );
}
```

Si la producción no tenía materia prima vinculada (`id_inventario_materia` es null), este paso se salta completamente.

Los tres pasos se ejecutan de forma secuencial pero **sin transacción**. Si el paso 2 falla, el estado ya habrá cambiado a "Finalizada" pero el inventario no se habrá actualizado. No hay rollback.

---

## Los modelos

### `Produccion.php`

#### `obtenerTodas()`

Trae todas las producciones con el nombre del producto y del usuario que la registró, usando dos JOINs:

```sql
SELECT p.*, prod.nombre as producto_nombre, u.nombres as usuario_nombre
FROM produccion p
LEFT JOIN producto prod ON p.id_producto = prod.id_producto
LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
ORDER BY p.id_produccion DESC
```

#### `crear($datos)`

Inserta la producción. Usa `execute()` con array en lugar de `bindParam()` — es más compacto pero funciona igual.

```sql
INSERT INTO produccion
  (lote_produccion, cantidad, estado, descripcion, id_usuario, id_producto, id_inventario_materia)
VALUES
  (:lote_produccion, :cantidad, :estado, :descripcion, :id_usuario, :id_producto, :id_inventario_materia)
```

#### `actualizarEstado($id, $estado)`

Simple UPDATE de una sola columna.

```sql
UPDATE produccion SET estado = :estado WHERE id_produccion = :id
```

#### `obtenerPorId($id)`

Trae todos los campos de una producción específica. Se usa en la acción `finalizar` para obtener `id_producto`, `cantidad` e `id_inventario_materia` antes de actualizar los inventarios.

```sql
SELECT * FROM produccion WHERE id_produccion = :id
```

---

### `InventarioProductos.php`

Este modelo tiene un constructor que hace bastante trabajo: crea la tabla `inventario_productos` si no existe y verifica que las columnas `bodega` y `cantidad` estén presentes, agregándolas si faltan. Es la misma estrategia de auto-migración que usan otros modelos del sistema.

#### `registrarIngreso($datos)`

Antes de insertar, verifica si ya existe un registro para esa producción:

```sql
SELECT COUNT(*) FROM inventario_productos WHERE id_produccion = :id_produccion
```

Si ya existe, retorna `true` sin insertar nada. Esto evita duplicados si por alguna razón se llama a `finalizar` dos veces sobre la misma producción.

Si no existe, inserta el registro:

```sql
INSERT INTO inventario_productos (fecha, bodega, id_produccion, id_producto, id_usuario, cantidad)
VALUES (NOW(), :bodega, :id_produccion, :id_producto, :id_usuario, :cantidad)
```

---

### `InventarioMP.php`

Al igual que `InventarioProductos`, el constructor crea la tabla `inventario_materia_prima` si no existe.

#### `obtenerTodos()`

Trae todos los registros de materia prima con datos del detalle de lote y el código del lote de origen. Se usa en la vista para poblar el select del modal de iniciar producción.

```sql
SELECT i.*, d.tipo_envase, d.capacidad, d.proveedor, l.codigo_lote
FROM inventario_materia_prima i
LEFT JOIN detalles d ON i.id_detalles = d.id_detalles
LEFT JOIN lote l ON d.id_lote = l.id_lote
ORDER BY i.fecha DESC
```

#### `descontarStock($id_inventario_materia, $cantidad)`

Esta función es cuidadosa: primero lee el stock actual y luego calcula el nuevo valor usando `max(0, actual - cantidad)`. Esto garantiza que el campo `ingreso` nunca quede en negativo, aunque se intente descontar más de lo que hay.

```php
$nuevo = max(0, intval($row['ingreso']) - intval($cantidad));
```

```sql
UPDATE inventario_materia_prima SET ingreso = :nuevo WHERE id_inventario_materia = :id
```

---

## Las tablas en la base de datos

### `produccion`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_produccion` | INT PK AUTO | Identificador único |
| `lote_produccion` | VARCHAR | Código del lote de producción |
| `cantidad` | INT | Unidades a producir |
| `estado` | VARCHAR | `'En Producción'` o `'Finalizada'` |
| `descripcion` | TEXT | Notas del proceso |
| `id_usuario` | INT FK | Quién registró la producción |
| `id_producto` | INT FK | Qué producto se fabrica |
| `id_inventario_materia` | INT FK | Registro de MP vinculado (puede ser NULL) |

### `inventario_productos`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_inventario` | INT PK AUTO | Identificador único |
| `fecha` | DATETIME | Cuándo se registró el ingreso |
| `bodega` | VARCHAR | Bodega de destino (siempre `'Principal'` desde la UI) |
| `id_produccion` | INT FK | Producción que originó este ingreso |
| `id_producto` | INT FK | Producto ingresado |
| `id_usuario` | INT | Quién finalizó la producción |
| `cantidad` | INT | Unidades ingresadas |

### `inventario_materia_prima`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_inventario_materia` | INT PK AUTO | Identificador único |
| `ingreso` | INT | Stock disponible (se reduce al finalizar producción) |
| `fecha` | DATETIME | Fecha de ingreso original |
| `bodega` | VARCHAR | Bodega de origen |
| `id_detalles` | INT FK | Detalle de lote que originó este registro |
| `id_retornables` | INT | Campo presente, no usado en este módulo |

---

## Flujo completo — Iniciar una producción

```
Admin hace clic en "Iniciar Producción"
        │
        ▼
Modal: llena lote, cantidad, producto y opcionalmente materia prima
        │
        │  POST → ProduccionController.php?accion=crear
        ▼
Produccion::crear($datos)
  └─ INSERT INTO produccion (..., estado='En Producción', id_inventario_materia=X o NULL)
        │
  ├─ true  → alert success
  └─ false → alert error
        │
        ▼
Redirect → produccion.php → card aparece con badge "En Proceso" pulsante
```

## Flujo completo — Finalizar una producción

```
Admin hace clic en "Finalizar Producción" en una card
        │
        │  GET → ProduccionController.php?accion=finalizar&id=X
        ▼
Paso 1: Produccion::actualizarEstado($id, 'Finalizada')
  └─ UPDATE produccion SET estado = 'Finalizada' WHERE id_produccion = X
        │
        ▼
Paso 2: Produccion::obtenerPorId($id)
  └─ Obtiene cantidad, id_producto, id_inventario_materia
        │
        ▼
Paso 3: InventarioProductos::registrarIngreso($datos)
  ├─ Verifica si ya existe registro para esta producción (anti-duplicado)
  └─ INSERT INTO inventario_productos (fecha=NOW(), bodega='Principal', cantidad, ...)
        │
        ▼
Paso 4: ¿Tiene id_inventario_materia?
  ├─ NO  → se salta este paso
  └─ SÍ  → InventarioMP::descontarStock($id_inventario_materia, $cantidad)
                └─ Lee stock actual → calcula max(0, actual - cantidad)
                └─ UPDATE inventario_materia_prima SET ingreso = nuevo_valor
        │
        ▼
Redirect → produccion.php → card muestra badge "Finalizada" y botón deshabilitado
```

---

## Relación entre módulos

Este módulo conecta tres áreas del sistema que de otra forma serían independientes:

```
[Módulo de Lotes]
    └─ crea registros en inventario_materia_prima
              │
              │  se vincula al iniciar producción (opcional)
              ▼
[Módulo de Producción]
    └─ al finalizar, actualiza dos tablas:
              │
              ├─ inventario_productos  ← alimenta el inventario de productos terminados
              │         │
              │         └─ el módulo de Ventas lee de aquí para calcular stock disponible
              │
              └─ inventario_materia_prima  ← descuenta el stock de MP consumida
                        │
                        └─ el módulo de Inventario MP muestra este stock actualizado
```

---

## Cosas a tener en cuenta

**No hay transacción al finalizar.** Los tres pasos (cambiar estado, registrar inventario, descontar MP) se ejecutan de forma secuencial sin `BEGIN TRANSACTION`. Si el servidor falla entre el paso 1 y el paso 2, la producción quedará marcada como "Finalizada" pero sin registro en el inventario de productos. No hay mecanismo de rollback.

**La bodega siempre es "Principal".** El botón "Finalizar Producción" en la vista no envía el parámetro `bodega`, así que el controlador siempre usa el valor por defecto. El parámetro existe en el código pero no se usa desde la interfaz actual.

**Finalizar dos veces no duplica el inventario.** El método `registrarIngreso` verifica si ya existe un registro para esa producción antes de insertar. Si alguien logra llamar a `finalizar` dos veces (por ejemplo, manipulando la URL), el inventario solo se registra una vez. Sin embargo, el descuento de materia prima sí se ejecutaría dos veces porque `descontarStock` no tiene esa protección.

**La materia prima vinculada es opcional.** Si no se selecciona ningún registro de MP al iniciar la producción, el sistema funciona igual pero sin trazabilidad de consumo. El inventario de MP no se toca.

**El stock de MP nunca queda negativo.** La función `descontarStock` usa `max(0, actual - cantidad)` para garantizarlo, aunque esto significa que si se descuenta más de lo disponible, el stock simplemente queda en 0 sin avisar que hubo un déficit.

**No hay edición de producciones.** Una vez creada una producción, no se puede modificar su cantidad, producto o materia prima vinculada. Solo se puede finalizar. Si se cometió un error, habría que crear una nueva producción.
