# Documentación de Modelos (Models)

En la arquitectura MVC (Modelo-Vista-Controlador) de **MOOVA! (Trigestion)**, los **Modelos** son el corazón de la aplicación. Representan la "Capa de Datos". 

Estos archivos (ubicados en la carpeta `/models/`) son los únicos autorizados para "hablar" directamente con la base de datos (MySQL). Se encargan de insertar, consultar, actualizar y eliminar (CRUD) registros, además de contener las reglas de negocio más importantes (como no dejar que el stock quede en números negativos).

A continuación, se detalla el propósito y la responsabilidad de cada modelo en el sistema:

---

### 1. `usuario.php` (Gestión de Accesos y Personas)
- **Propósito:** Es el encargado de la seguridad y el control de identidades.
- **Responsabilidades:**
  - Validar credenciales durante el inicio de sesión (Login).
  - Registrar nuevos clientes o usuarios desde el panel de administrador.
  - Encriptar las contraseñas antes de guardarlas en la base de datos para garantizar la seguridad.
  - Administrar los 3 tipos de roles: Administrador (1), Trabajador (2) y Cliente (3).

### 2. `Categoria.php` (Organización del Catálogo)
- **Propósito:** Administrar las familias o clasificaciones de los productos que se venden.
- **Responsabilidades:**
  - Crear y listar categorías (Ej: "Agua Alcalina", "Agua Purificada", "Dispensadores").
  - Proveer la información para los botones de filtros en el catálogo del cliente.

### 3. `Producto.php` (Catálogo de Ventas)
- **Propósito:** Manejar el portafolio de productos terminados listos para la venta.
- **Responsabilidades:**
  - Guardar el nombre, precio, descripción e imagen del producto.
  - Vincular cada producto a una Categoría específica.
  - Proveer los datos base para que el cliente pueda armar su carrito de compras.

---

## 🏭 Ciclo Operativo e Inventarios

Los siguientes 4 modelos trabajan en equipo para trazar la línea de vida del agua: desde que llega el proveedor, hasta que se embotella y se almacena.

### 4. `Lote.php` (Recepción de Proveedores)
- **Propósito:** Registrar físicamente todo lo que entra a la fábrica de forma externa.
- **Responsabilidades:**
  - Registrar el código del lote, fecha de fabricación y caducidad.
  - Guardar los "Detalles de envase" (Ej: Entraron 1000 botellas plásticas de 500ml del proveedor X).
  - Alimentar automáticamente a la tabla de Materia Prima.

### 5. `InventarioMP.php` (Inventario de Materia Prima)
- **Propósito:** Controlar los insumos (botellas vacías, tapas, etiquetas) que están listos para usarse en la fábrica.
- **Responsabilidades:**
  - Sumar stock cuando llega un `Lote`.
  - **Regla de Negocio Crítica:** Descontar stock (función `descontarStock()`) automáticamente cuando los trabajadores finalizan una jornada de embotellado, impidiendo que el registro caiga en números negativos.
  - Generar estadísticas de qué material se consume más rápido.

### 6. `Produccion.php` (Planta / Fabricación)
- **Propósito:** Registrar el trabajo humano y la conversión de Materia Prima a Producto Terminado.
- **Responsabilidades:**
  - Abrir órdenes de trabajo (Ej: "Juan va a embotellar 500 garrafones").
  - Al marcar el estado como "Finalizada", hace un puente automático: le avisa a `InventarioMP.php` que reste botellas vacías, y le avisa a `InventarioProductos.php` que sume botellas llenas a la bodega.

### 7. `InventarioProductos.php` (Bodega de Producto Terminado)
- **Propósito:** Controlar qué productos están listos para venderse y enviarse al cliente final.
- **Responsabilidades:**
  - Recibir y sumar unidades provenientes del módulo de `Produccion.php`.
  - Entregar reportes rápidos de stock.
  - Restar unidades cuando el modelo de Ventas aprueba una compra.

---

## 💰 El Motor Financiero

### 8. `Venta.php` (Ventas y Carrito de Compras)
- **Propósito:** Es el modelo más grande y complejo del sistema. Es el cajero virtual de la empresa.
- **Responsabilidades:**
  - **Cálculo de Stock en Vivo:** Antes de permitir una venta, revisa si hay suficientes unidades en `InventarioProductos.php`.
  - **Ventas POS (Administrador):** Permite a los administradores registrar ventas físicas hechas en el mostrador.
  - **Pedidos Online (Clientes):** Transforma la memoria del carrito de compras (Sesión) en registros inmutables en la base de datos.
  - **Trazabilidad:** Cuando se realiza una venta, no solo guarda el monto final, sino que crea "Detalles de Venta" (desglose factura) y resta permanentemente las unidades vendidas de la bodega de Productos Terminados.
  - **Reportes (Dashboards):** Es el modelo encargado de hacer sumatorias de dinero (`SUM(total)`) para dibujar las gráficas financieras (ingresos diarios, productos más vendidos) en el panel del administrador, excluyendo inteligentemente las ventas canceladas.
