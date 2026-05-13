# Documentación de Scripts de Diagnóstico (Checks y Tests)

Los archivos que empiezan con `check_` y `test_` son pequeños programas ("scripts de diagnóstico") que utilizamos los desarrolladores detrás de escena. Sirven para probar si el sistema funciona correctamente sin tener que abrir la página web ni hacer clics manualmente. Toda esta colección de archivos es el **"taller de reparación y pruebas"** del sistema. No los ve el usuario final, pero nos permiten asegurar que la plataforma sea estable.

Aquí tienes la explicación **una por una** de para qué sirve cada archivo:

---

## 🔍 Archivos `check_` (Revisión de Estructura)
Estos archivos sirven para "mirar" dentro de la base de datos y comprobar que las tablas estén bien construidas.

### 1. `check_cliente.php`
- **Para qué sirve:** Imprime la estructura exacta de la tabla `cliente` y, además, muestra los primeros 5 clientes registrados en la base de datos.
- **Utilidad:** Sirve para verificar rápidamente si los datos de los clientes se están guardando con el formato correcto o si falta alguna columna.

### 2. `check_schema.php` (Raíz)
- **Para qué sirve:** Muestra el "esqueleto" (columnas, tipo de datos, si es llave primaria) de las tablas más importantes para las ventas (`venta` y `detalle_venta`). También muestra las primeras 3 filas de `usuarios` y `producto`.
- **Utilidad:** Es una radiografía del sistema comercial. Sirve para asegurar que la base de datos soporta correctamente el registro de facturas y ventas.

### 3. `scratch/check_schema.php` (Carpeta interna)
- **Para qué sirve:** Hace lo mismo que el anterior, pero enfocado exclusivamente en las tablas `cliente` y `usuarios`. 
- **Utilidad:** Se usa cuando se hacen cambios en los permisos o roles para asegurar que no se haya roto el sistema de inicio de sesión.

---

## ⚙️ Archivos `test_` (Simulación de Procesos)
Estos archivos son simuladores. En lugar de que un humano navegue, haga clic y compre, el código finge ser un humano para ver si el servidor responde bien a las órdenes y no hay errores de programación.

### 4. `test_checkout.php`
- **Para qué sirve:** Finge ser el carrito de compras de un cliente que presiona el botón "Finalizar Compra". Le inyecta a la memoria del servidor (Sesión) un carrito imaginario y manda la orden.
- **Utilidad:** Asegura que la lógica de cobro y guardado del carrito de compras funciona bien.

### 5. `test_curl.php`
- **Para qué sirve:** Simula una petición "externa" (como si una aplicación móvil o una página distinta se intentara comunicar con nuestra tienda) para comprar.
- **Utilidad:** Sirve para probar la seguridad y la recepción de datos crudos hacia el controlador de ventas.

### 6. `test_frontend.php`
- **Para qué sirve:** Hace un proceso doble de compra instantánea: 1) Finge agregar un producto al carrito, y 2) Finge darle al botón pagar de inmediato.
- **Utilidad:** Permite probar el ciclo de vida completo de un usuario rápido sin tener que abrir la interfaz gráfica. Depuración a la velocidad de la luz.

### 7. `test_produccion.php`
- **Para qué sirve:** Simplemente revisa que la tabla `produccion` (donde los trabajadores registran los lotes de agua fabricada) esté bien construida.
- **Utilidad:** Verificación de integridad para el panel del trabajador.

### 8. `test_ventas.php`
- **Para qué sirve:** ¡Es el test maestro (el más grande)! Simula a dos personas a la vez:
  - *Simula al administrador* haciendo una venta directa en físico (POS) y revisa que se descuente el inventario.
  - *Simula al cliente* haciendo un pedido online.
- **Utilidad:** Garantiza que el núcleo del negocio de MOOVA! funciona: Si este archivo corre sin dar error, significa que el sistema puede vender, descontar stock de la bodega y sumar las ganancias al dashboard correctamente.

### 9. `scratch/test_compra.php`
- **Para qué sirve:** Es una prueba microscópica que intenta buscar el historial de compras exclusivamente del cliente con el "ID número 8".
- **Utilidad:** Sirve para probar fallos específicos si un cliente reportara que no puede ver su historial.
