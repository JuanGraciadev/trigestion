# Documentación del Dashboard del Cliente (`cliente.php`)

El archivo `cliente.php` es el catálogo de compras e interfaz principal para los usuarios con rol de **Cliente** (Rol 3) en **MOOVA! (Trigestion)**. 

Este módulo está diseñado como una aplicación de comercio electrónico moderna tipo SPA (Single Page Application). La página nunca se recarga durante el proceso de compra; todo el carrito de compras funciona de manera reactiva y en segundo plano utilizando JavaScript (Fetch API/AJAX).

---

## 1. Arquitectura MVC del Cliente

El módulo del cliente depende de los siguientes componentes para funcionar:

### Modelos (Capa de Datos)
- **`Producto.php`**: Extrae la información (nombre, precio, imagen) de los productos que están marcados como activos (`estado = 1`).
- **`Categoria.php`**: Extrae la lista de categorías para generar dinámicamente los botones de filtrado rápido.
- **`Venta.php`**: Es vital, ya que el archivo `cliente.php` ejecuta una función llamada `stockDisponible()` por cada producto antes de mostrarlo, asegurándose de que el cliente no pueda comprar algo que no existe en bodega física.

### Controlador (Capa Lógica)
- **`VentaController.php`**: El cliente nunca interactúa directamente con la base de datos para modificar su carrito. Envía peticiones HTTP a este controlador que maneja la memoria de la sesión PHP mediante 4 acciones: `agregar_carrito`, `actualizar_carrito`, `eliminar_carrito` y `finalizar_compra`.

---

## 2. Interfaz de Usuario (UI) y Características

El diseño está fuertemente inspirado en aplicaciones de *Delivery* premium.

### Banner Principal (Hero)
- Contiene un diseño moderno (`glass-card`) con fondos de colores degradados que generan una estética limpia.
- **Botón Flotante del Carrito:** Se encuentra en la esquina inferior del banner. Cuenta con un pequeño "Badge" rojo que muestra la cantidad de ítems en el carrito. Al hacer clic, abre un panel lateral superpuesto.

### Barra de Herramientas Flotante (Sticky Toolbar)
Se queda pegada a la parte superior de la pantalla al hacer scroll (`sticky top-28`):
- **Filtros por Categoría:** Permiten recargar la página filtrando por el ID de la categoría (Ej: `cliente.php?cat=2`).
- **Buscador en Tiempo Real:** Un input de texto que dispara la función JS `filtrarProductos()` cada vez que se presiona una tecla. Esta función oculta o muestra las tarjetas de productos sin conectarse al servidor, logrando una búsqueda instantánea.

### Cuadrícula de Productos (Grid)
- **Etiquetas de Stock Inteligentes:** Si el stock es 0, la tarjeta se vuelve gris (`grayscale-[30%]`), el botón de compra desaparece y la etiqueta roja dice "Sin Stock". Si quedan menos de 5 unidades, la etiqueta se vuelve amarilla alertando "Quedan X". Si hay suficiente, es verde.
- **Botones de Acción:** Envían el ID del producto, nombre, precio e imagen a la función JS `añadirCarrito()`.

---

## 3. El Carrito de Compras (Sidebar Reactivo)

Es la parte más avanzada del archivo. Es un panel lateral (`cart-sidebar`) que se desliza desde la derecha y oscurece el fondo (`cart-overlay`). 

### ¿Cómo funciona la sincronización (El Frontend JS)?
1. **El Estado Local:** En la línea `let carrito = <?= json_encode(array_values($carrito)) ?>;`, PHP inyecta el contenido del carrito (guardado en sesión) directamente en una variable de JavaScript. Esto permite que el JS conozca el carrito sin tener que hacer una petición al servidor cada vez que abres el menú.
2. **Renderizado (`renderCarrito()`):** Esta función JS borra el contenido visual del carrito y lo reconstruye desde cero basándose en la variable `carrito`. Calcula el total, sub-totales y actualiza la burbuja roja de notificaciones del botón.
3. **Peticiones AJAX:**
   - Cuando das clic en el símbolo `+` o `-`, JS llama a `cambiarCantidad()`, que hace una petición `POST` oculta a `VentaController.php`.
   - El controlador de PHP recibe la petición, actualiza el archivo de sesión en el servidor, y responde devolviendo el carrito actualizado en formato JSON.
   - JS recibe el JSON, sobreescribe su variable `carrito` y ejecuta `renderCarrito()` de nuevo para mostrar los cambios visualmente al instante.

### Checkout y Confirmación (`finalizarCompra()`)
Al dar clic en "Confirmar Pedido":
1. JavaScript valida que el carrito no esté vacío.
2. Se muestra un cuadro de confirmación (SweetAlert2) sumamente estilizado con el monto total a pagar de forma gigante.
3. Si el cliente confirma, se desactiva el botón (para evitar múltiples cobros) y se hace una petición POST al controlador con las instrucciones o notas de entrega.
4. El controlador crea el registro real en la base de datos (descontando stock final).
5. JS recibe un `ok = true` con el ID de la orden (`data.id_venta`), limpia el carrito, y redirige automáticamente al usuario a `cliente_compras.php` para que vea su factura.
