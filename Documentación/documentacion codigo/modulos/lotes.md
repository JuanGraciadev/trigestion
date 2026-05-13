# Módulo de Lotes — Documentación Técnica

## ¿Qué hace este módulo?

Los lotes son la forma en que el sistema rastrea la procedencia de la materia prima (envases) que entra al negocio. Cada lote tiene un código identificador y puede tener uno o varios **detalles**, donde se especifica cuántas unidades llegaron, de qué tipo de envase, con qué capacidad y de qué proveedor.

Lo que hace especial a este módulo es que tiene un **flujo de dos pasos obligatorio**: primero se crea el lote con su código, y el sistema te lleva automáticamente a una segunda pantalla para registrar los detalles. Además, cada vez que se agrega un detalle, el sistema crea automáticamente un registro en la tabla `inventario_materia_prima` — es decir, registrar un detalle de lote también actualiza el inventario sin que el usuario tenga que hacer nada extra.

Cualquier usuario con sesión activa puede acceder a este módulo, sin restricción de rol.

---

## Archivos que participan

| Archivo | ¿Para qué sirve? |
|---|---|
| `views/dashboard/lotes.php` | Lista todos los lotes con sus acciones |
| `views/dashboard/lote_detalles.php` | Muestra y gestiona los detalles de un lote específico |
| `controllers/LoteController.php` | Recibe todas las acciones del módulo |
| `models/Lote.php` | Consultas a las tablas `lote`, `detalles` e `inventario_materia_prima` |
| `config/database.php` | Conexión a MySQL |
| `views/layouts/header.php` | HTML base y librerías |
| `views/layouts/sidebar.php` | Menú lateral |

---

## El flujo de dos pasos

Este módulo es diferente a los demás porque crear un lote no termina en la misma pantalla — te lleva a otra:

```
Admin hace clic en "Nuevo Lote"
        │
        ▼
Ingresa el código del lote (Paso 1)
        │
        │  POST → LoteController.php?accion=crear
        ▼
Se crea el lote en la BD y retorna el id_lote nuevo
        │
        ▼
Redirect → lote_detalles.php?id_lote={id}   ← pantalla diferente
        │
        ▼
Si el lote no tiene detalles aún, el modal de "Añadir Detalle" se abre automáticamente
        │
        ▼
Admin completa los datos del detalle (Paso 2)
        │
        │  POST → LoteController.php?accion=crearDetalle
        ▼
Se crea el detalle Y se crea automáticamente un registro en inventario_materia_prima
        │
        ▼
Redirect → lote_detalles.php?id_lote={id}   ← misma pantalla, ahora con el detalle
```

La apertura automática del modal en `lote_detalles.php` se hace con JavaScript al cargar la página, pero solo si el lote no tiene detalles todavía:

```php
<?php if(empty($detalles) && !isset($_SESSION['alert'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            openModal('modalCrearDetalle');
        });
    </script>
<?php endif; ?>
```

---

## La vista principal — `views/dashboard/lotes.php`

### Lo que carga al abrir

Verifica sesión activa y trae todos los lotes con una sola consulta que ya incluye el nombre del usuario que lo creó y el conteo de detalles:

```php
$lotes = $loteModel->obtenerTodos();
```

### La tabla de lotes

Cada fila muestra:
- El código del lote con su ID interno
- El nombre del usuario que lo registró
- Un badge verde/rojo indicando cuántos detalles tiene (verde si tiene al menos uno, rojo si está vacío)
- Tres botones de acción que solo aparecen al hacer hover sobre la fila

### Los tres botones de acción

| Botón | Color | Qué hace |
|---|---|---|
| Ver detalles | Azul | Navega a `lote_detalles.php?id_lote=X` |
| Editar código | Ámbar | Abre el modal de edición |
| Eliminar | Rojo | Abre confirmación con SweetAlert2 |

El botón de eliminar no ejecuta la acción directamente — primero muestra un diálogo de confirmación con SweetAlert2 que advierte que la eliminación es irreversible y borrará también todos los detalles e inventario asociados. Solo si el usuario confirma, redirige al controlador.

---

## La vista de detalles — `views/dashboard/lote_detalles.php`

### Cómo llega a esta pantalla

Siempre recibe `id_lote` por GET. Si no viene ese parámetro, o si el lote no existe en la BD, redirige de vuelta a `lotes.php`:

```php
if (!isset($_GET['id_lote'])) {
    header("Location: lotes.php");
    exit;
}
$lote = $loteModel->obtenerLote($id_lote);
if (!$lote) {
    header("Location: lotes.php");
    exit;
}
```

### Lo que muestra

- Un header con el código del lote y un enlace para volver a la lista
- Una tabla con todos los detalles del lote: unidades, tipo de envase, capacidad y proveedor
- Cada fila tiene un botón de editar que abre el modal de edición con los datos pre-cargados
- Si no hay detalles, muestra un estado vacío y abre el modal automáticamente

### Los dos modales

**Modal Crear Detalle** — etiquetado como "Paso 2" para reforzar el flujo. Campos: unidades, tipo de envase, capacidad y proveedor. El `id_lote` viene como campo oculto con el valor de la URL actual.

**Modal Editar Detalle** — igual que el de crear pero pre-llenado. El `id_detalles` viene como campo oculto.

---

## El controlador — `controllers/LoteController.php`

### Protección de acceso

Solo verifica sesión activa, sin validar el rol. Igual que categorías, cualquier usuario autenticado puede usar este módulo.

### Acción `crear`

Recibe solo el código del lote. El `id_usuario` viene de la sesión.

Lo más importante de esta acción es lo que hace **después** de crear el lote: en lugar de redirigir a `lotes.php`, redirige a `lote_detalles.php` con el ID del lote recién creado. Esto es lo que fuerza el flujo de dos pasos.

```php
$id_lote = $loteModel->crear($codigo_lote, $id_usuario);
if ($id_lote) {
    header("Location: ../views/dashboard/lote_detalles.php?id_lote=" . $id_lote);
    exit;
}
```

### Acción `editar`

Solo permite cambiar el código del lote. Recibe `id_lote` y `codigo_lote` por POST. Redirige a `lotes.php`.

### Acción `eliminar`

Recibe `id_lote` por GET. Delega completamente al modelo, que se encarga de borrar en el orden correcto para no violar restricciones de clave foránea. Redirige a `lotes.php`.

### Acción `crearDetalle`

Recibe los datos del detalle por POST y llama al modelo. Después de guardar, redirige de vuelta a `lote_detalles.php` con el mismo `id_lote` para que el usuario vea el detalle recién agregado.

```php
header("Location: ../views/dashboard/lote_detalles.php?id_lote=" . $_POST['id_lote']);
```

### Acción `editarDetalle`

Igual que crear detalle pero actualiza un registro existente. También redirige de vuelta a `lote_detalles.php`.

---

## El modelo — `models/Lote.php`

Este modelo es el más complejo del sistema porque trabaja con tres tablas a la vez y tiene lógica de negocio importante dentro de él.

### `obtenerTodos()`

Trae todos los lotes con el nombre del usuario que los creó y el conteo de detalles, todo en una sola consulta usando una subconsulta correlacionada:

```sql
SELECT l.*, u.nombres as usuario_nombre,
       (SELECT COUNT(*) FROM detalles d WHERE d.id_lote = l.id_lote) as num_detalles
FROM lote l
LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario
ORDER BY l.id_lote DESC
```

### `obtenerLote($id_lote)`

Trae un lote específico con el nombre del usuario. Se usa en `lote_detalles.php` para mostrar el código en el header.

```sql
SELECT l.*, u.nombres as usuario_nombre
FROM lote l
LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario
WHERE l.id_lote = :id_lote
```

### `crear($codigo_lote, $id_usuario)`

Inserta el lote y retorna el `lastInsertId()` — no `true` como los otros modelos. Esto es necesario porque el controlador necesita el ID para construir la URL de redirección al paso 2.

```sql
INSERT INTO lote (codigo_lote, id_usuario) VALUES (:codigo_lote, :id_usuario)
```

### `editar($id_lote, $codigo_lote)`

Solo actualiza el código. Simple.

```sql
UPDATE lote SET codigo_lote = :codigo_lote WHERE id_lote = :id_lote
```

### `eliminar($id_lote)` — la más compleja

Esta función no hace un simple DELETE. Tiene que borrar en el orden correcto para no romper las relaciones entre tablas. El proceso es:

**Paso 1** — Obtiene los IDs de todos los detalles del lote:
```sql
SELECT id_detalles FROM detalles WHERE id_lote = :id_lote
```

**Paso 2** — Si hay detalles, borra los registros de inventario asociados a esos detalles:
```sql
DELETE FROM inventario_materia_prima WHERE id_detalles IN (?, ?, ...)
```
Usa `bindValue` en un loop para construir el `IN` dinámicamente. Si esta tabla no existe, el error se captura y se ignora.

**Paso 3** — Borra los detalles del lote:
```sql
DELETE FROM detalles WHERE id_lote = :id_lote
```

**Paso 4** — Finalmente borra el lote:
```sql
DELETE FROM lote WHERE id_lote = :id_lote
```

El comentario en el código dice "por si no hay CASCADE", lo que indica que las claves foráneas en la base de datos no tienen `ON DELETE CASCADE` configurado, así que el modelo lo hace manualmente.

### `obtenerDetallesPorLote($id_lote)`

Trae todos los detalles de un lote específico, ordenados del más reciente al más antiguo.

```sql
SELECT * FROM detalles WHERE id_lote = :id_lote ORDER BY id_detalles DESC
```

### `crearDetalle($datos)` — el efecto en cadena

Esta es la función más importante del módulo porque hace dos cosas: crea el detalle y luego automáticamente crea un registro en `inventario_materia_prima`.

**Paso 1** — Inserta el detalle:
```sql
INSERT INTO detalles (unidades, tipo_envase, capacidad, proveedor, id_lote)
VALUES (:unidades, :tipo_envase, :capacidad, :proveedor, :id_lote)
```

**Paso 2** — Con el `lastInsertId()` del detalle recién creado, inserta en inventario:
```sql
INSERT INTO inventario_materia_prima (ingreso, fecha, bodega, id_detalles)
VALUES (:ingreso, NOW(), 'Bodega Principal', :id_detalles)
```

El campo `ingreso` toma el valor de `unidades` del detalle. La `bodega` siempre se guarda como `'Bodega Principal'` — está hardcodeado, no es configurable desde la interfaz.

Si la tabla `inventario_materia_prima` no existe, el modelo la crea automáticamente con `CREATE TABLE IF NOT EXISTS` y reintenta la inserción. Esto es similar al truco de `checkAndCreateEstadoColumn()` que usan los modelos de Producto y Categoria.

### `editarDetalle($id_detalles, $datos)`

Actualiza los cuatro campos del detalle. **No actualiza** el registro de inventario correspondiente — si se cambia la cantidad de unidades en un detalle, el inventario queda desincronizado.

```sql
UPDATE detalles
SET unidades = :unidades, tipo_envase = :tipo_envase,
    capacidad = :capacidad, proveedor = :proveedor
WHERE id_detalles = :id_detalles
```

---

## Las tablas en la base de datos

### `lote`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_lote` | INT PK AUTO | Identificador único |
| `codigo_lote` | VARCHAR | Código legible del lote (ej. `L-202310A`) |
| `id_usuario` | INT FK | Quién registró el lote |

### `detalles`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_detalles` | INT PK AUTO | Identificador único |
| `unidades` | INT | Cantidad de envases en este detalle |
| `tipo_envase` | VARCHAR | Tipo de envase (ej. Garrafón, Botella) |
| `capacidad` | VARCHAR | Capacidad del envase (ej. 20 Litros) |
| `proveedor` | VARCHAR | Nombre del proveedor |
| `id_lote` | INT FK | Lote al que pertenece |

### `inventario_materia_prima`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_inventario_materia` | INT PK AUTO | Identificador único |
| `ingreso` | INT | Cantidad ingresada (igual a `unidades` del detalle) |
| `fecha` | DATETIME | Fecha y hora del ingreso (se guarda con `NOW()`) |
| `bodega` | VARCHAR | Siempre `'Bodega Principal'` |
| `id_detalles` | INT FK | Detalle que originó este ingreso |
| `id_retornables` | INT | Campo presente en la tabla, no se usa en este módulo |

---

## Flujo completo — Crear lote con detalle

```
Admin hace clic en "Nuevo Lote"
        │
        ▼
Modal Paso 1: ingresa código del lote
        │
        │  POST → LoteController.php?accion=crear
        ▼
Lote::crear(codigo_lote, id_usuario)
  └─ INSERT INTO lote → retorna id_lote
        │
        ▼
Redirect → lote_detalles.php?id_lote={id}
        │
        ▼
La página carga sin detalles → abre modal automáticamente
        │
        ▼
Modal Paso 2: ingresa unidades, tipo_envase, capacidad, proveedor
        │
        │  POST → LoteController.php?accion=crearDetalle
        ▼
Lote::crearDetalle($datos)
  ├─ INSERT INTO detalles → retorna id_detalles
  └─ INSERT INTO inventario_materia_prima (ingreso=unidades, fecha=NOW(), bodega='Bodega Principal')
        │
        ▼
Redirect → lote_detalles.php?id_lote={id} → muestra el detalle recién creado
```

## Flujo completo — Eliminar un lote

```
Admin hace clic en el botón rojo de eliminar
        │
        ▼
SweetAlert2 pide confirmación (advierte que es irreversible)
        │
  ├─ Cancela → no pasa nada
  └─ Confirma → redirect a LoteController.php?accion=eliminar&id_lote=X
        │
        ▼
Lote::eliminar($id_lote)
  ├─ SELECT id_detalles FROM detalles WHERE id_lote = X
  ├─ DELETE FROM inventario_materia_prima WHERE id_detalles IN (...)
  ├─ DELETE FROM detalles WHERE id_lote = X
  └─ DELETE FROM lote WHERE id_lote = X
        │
        ▼
Redirect → lotes.php → muestra alerta de éxito
```

---

## Cosas a tener en cuenta

**Editar un detalle no actualiza el inventario.** Si el admin cambia la cantidad de unidades de un detalle existente, el registro en `inventario_materia_prima` queda con el valor original. Solo la creación de un detalle nuevo dispara la inserción en inventario.

**La bodega siempre es "Bodega Principal".** No hay forma de especificar otra bodega desde la interfaz. Está hardcodeado en el modelo.

**El modelo crea la tabla de inventario si no existe.** Al igual que los modelos de Producto y Categoria con la columna `estado`, aquí el modelo intenta crear `inventario_materia_prima` si no existe. Esto puede ser problemático si la tabla existe pero con una estructura diferente.

**No hay restricción de rol.** Cualquier usuario con sesión puede crear, editar y eliminar lotes. Si se quiere restringir esto a administradores o trabajadores, habría que agregar la verificación de `id_rol` tanto en la vista como en el controlador.

**La eliminación es manual, no por CASCADE.** El modelo borra en tres pasos (inventario → detalles → lote) porque la base de datos no tiene `ON DELETE CASCADE` configurado en las claves foráneas. Si se agrega CASCADE a la BD en el futuro, el código del modelo seguiría funcionando pero haría trabajo redundante.

**El flujo de dos pasos se puede saltear.** Aunque el sistema redirige automáticamente a `lote_detalles.php` al crear un lote, el admin puede cerrar el modal del Paso 2 y volver a la lista. El lote quedaría creado sin detalles, mostrando el badge rojo de "0 Registros".
