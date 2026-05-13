# Documentación Técnica: Modelo `Venta.php`

**Ruta:** `c:\laragon\www\trigestion\models\Venta.php`  
**Responsabilidad:** Manejar todo el motor financiero, validaciones de stock en tiempo real y el registro de compras físicas (POS) y online (Carrito).

Este es uno de los modelos más importantes y complejos de la arquitectura. Utiliza **Transacciones SQL (PDO Transactions)** para asegurar que si falla la creación del detalle de una venta, no se cobre ni se guarde la factura, manteniendo la base de datos libre de ventas fantasma.

A continuación, se detalla el funcionamiento de sus métodos clave:

---

## 1. El Algoritmo de Inventario

### `stockDisponible($id_producto)`
Este método es el guardián de la bodega. En lugar de tener una columna estática de "Stock", el sistema lo calcula dinámicamente cada vez que se llama, garantizando precisión absoluta.

**¿Cómo funciona?**
1. **Entradas:** Suma todas las unidades que han salido del módulo de "Producción" hacia la tabla `inventario_productos` para ese producto.
2. **Salidas:** Suma todas las unidades registradas en `detalle_venta` de todas las ventas que **NO** estén en estado "Cancelado".
3. **Retorno:** Devuelve el resultado de `Entradas - Salidas`. Si la resta es negativa, devuelve `0` gracias a la función `max(0, $resultado)`.

> 💡 **Nota de Negocio:** Si el administrador cancela una venta, las unidades "Salidas" dejan de contar, lo que significa que el stock vuelve automáticamente a la bodega sin tener que hacer ajustes manuales.

---

## 2. Creación de Ventas (Transaccionales)

El modelo tiene dos métodos distintos para crear ventas dependiendo de quién lo solicita. Ambos están envueltos en `$this->conn->beginTransaction()` y terminan con `commit()` (éxito) o `rollBack()` (fracaso).

### `crearVenta($id_usuario, $items, $notas)`
*Utilizado por el Cliente al hacer "Checkout" en el carrito virtual.*

**Proceso Lógico:**
1. **Validación de Stock (Doble Check):** Itera sobre los `$items` del carrito y verifica el stock actual. Si alguien más compró la última unidad un segundo antes, la venta se aborta (RollBack).
2. **Vinculación de Cliente:** Busca si el `id_usuario` ya tiene un registro en la tabla `cliente`. Si no (es su primera compra), crea silenciosamente el registro en `cliente`.
3. **Cálculo Financiero:** Recalcula el `$total` multiplicando el precio unitario por la cantidad directamente en el servidor (previniendo que un usuario malicioso haya manipulado los precios en su navegador web).
4. **Guardado:** Inserta la cabecera en `venta` con estado **"Pendiente"** y luego inserta cada producto iterando en la tabla `detalle_venta`.

### `crearVentaPOS($id_usuario_cliente, $items, $notas, $id_usuario_admin)`
*Utilizado por el Administrador al vender en físico (Point Of Sale).*

**Diferencias clave con `crearVenta`:**
- La venta se registra automáticamente con estado **"Entregado"** (ya que la transacción es presencial y el dinero/producto se cambia en el momento).
- Se guarda el `id_usuario_admin` en la factura para saber qué empleado/administrador realizó la venta física.

---

## 3. Consultas y Estadísticas

### `cambiarEstado($id_venta, $estado, $id_usuario = null)`
Actualiza el estado de una orden. Es usado principalmente para cambiar a "En Proceso", "Entregado" o "Cancelado". Al registrar el estado, opcionalmente puede registrar qué empleado manejó la orden (`id_usuario`).

### `obtenerTodas()` y `obtenerPorCliente($id_usuario)`
Traen el historial de facturas. 
- Utilizan `LEFT JOIN` con `cliente` y `usuarios` para traer en la misma consulta el nombre, email, teléfono y dirección.
- En `obtenerPorCliente`, se usa una función especial de MySQL llamada `GROUP_CONCAT(p.nombre)` para traer en una sola línea todos los productos que compró el cliente separados por comas (Ej: *"Garrafón 20L, Botella 500ml"*), facilitando el diseño de la tabla en la vista.

### `obtenerDetalle($id_venta)`
Recupera las filas exactas de un ticket. Incluye joins con `producto` para obtener la ruta de la imagen (`img`) y el `nombre` en tiempo real.

### `obtenerEstadisticas()`
Alimenta los KPIs superiores del panel del Administrador.
1. Ejecuta 4 mini-consultas para contar cuántos pedidos hay en cada estado (Pendientes, Proceso, Entregados, Cancelados).
2. Suma todo el campo `total` de las ventas, **ignorando** las canceladas y las pendientes. Esto significa que la gráfica de ingresos solo muestra dinero "real" (entregado o en ruta).

---

## Flujo de Vida de una Transacción
```text
[Cliente] -> click Comprar -> VentaController (AJAX)
   └─ [VentaController] -> llama a $ventaModel->crearVenta()
        ├─ BEGIN TRANSACTION
        ├─ Valida Stock
        ├─ Si OK -> UPDATE venta, UPDATE detalle_venta
        ├─ COMMIT
        └─ Retorna ['ok' => true, 'id_venta' => X]
   └─ [VentaController] JSON Response -> Vista actualiza el DOM
```
