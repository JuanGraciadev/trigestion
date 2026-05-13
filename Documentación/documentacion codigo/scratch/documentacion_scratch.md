# Documentación de Scripts de Prueba (Scratch Files)

Durante el desarrollo de **MOOVA! (Trigestion)**, se han creado diversos scripts temporales y de prueba (conocidos como *scratch files*). Estos archivos son herramientas fundamentales para los desarrolladores, ya que permiten verificar el funcionamiento interno del sistema, probar conexiones, revisar la estructura de la base de datos y simular procesos complejos sin tener que interactuar directamente con la interfaz gráfica.

A continuación, se detalla la función de cada uno de estos archivos, divididos por su ubicación.

---

## 📂 Directorio `/scratch/`
Estos archivos se encuentran dentro de la carpeta `scratch`, destinada específicamente a scripts rápidos de manipulación o consulta.

### 1. `alter_db.php`
- **¿Para qué sirve?:** Es un script de actualización rápida de la base de datos.
- **Funcionamiento:** Se conecta a la base de datos e intenta ejecutar un comando `ALTER TABLE` para añadir la columna `estado` a la tabla `categoria`. Incluye manejo de errores (`try-catch`) para evitar que el script falle si la columna ya existe, mostrando un mensaje claro en la consola.

### 2. `check_schema.php`
- **¿Para qué sirve?:** Permite inspeccionar la estructura de las tablas relacionadas con los usuarios.
- **Funcionamiento:** Realiza una consulta `DESCRIBE` a las tablas `cliente` y `usuarios`. Imprime en pantalla una lista detallada de los campos, sus tipos de datos, si aceptan valores nulos y sus valores por defecto. Es muy útil para verificar que las migraciones de la base de datos se aplicaron correctamente.

### 3. `test_compra.php`
- **¿Para qué sirve?:** Prueba específica de consultas de ventas.
- **Funcionamiento:** Instancia el modelo `Venta` y ejecuta el método `obtenerPorCliente(8)`. Su propósito es verificar rápidamente si el sistema es capaz de recuperar correctamente el historial de pedidos de un cliente específico (en este caso, el usuario con ID 8) y muestra el resultado en crudo.

---

## 📂 Directorio Raíz (`/`)
Estos archivos se encuentran en la carpeta principal del proyecto. La mayoría son simuladores de procesos del sistema.

### 4. `check_cliente.php`
- **¿Para qué sirve?:** Inspección mixta de estructura y datos de clientes.
- **Funcionamiento:** Muestra la estructura de las columnas de la tabla `cliente` y, además, extrae y muestra los primeros 5 registros de la base de datos. Sirve para confirmar rápidamente qué datos se están guardando realmente y bajo qué formato.

### 5. `check_schema.php` (Raíz)
- **¿Para qué sirve?:** Inspección general del esquema principal del sistema de ventas.
- **Funcionamiento:** Muestra la estructura completa de las tablas `venta` y `detalle_venta`. Además, consulta y muestra las primeras 3 filas de las tablas `usuarios` y `producto`. Es un visor rápido del estado actual de las entidades más críticas del sistema.

### 6. `test_checkout.php`
- **¿Para qué sirve?:** Simulación directa del controlador de ventas.
- **Funcionamiento:** "Engaña" al sistema inyectando datos ficticios directamente en las variables superglobales (`$_SESSION['usuario']` y `$_SESSION['carrito']`). Luego invoca al `VentaController.php` pasando la acción `finalizar_compra` a través de `$_POST`. Es vital para probar la lógica de compra sin necesidad de usar el navegador.

### 7. `test_curl.php`
- **¿Para qué sirve?:** Prueba de peticiones HTTP (API / Endpoints).
- **Funcionamiento:** Utiliza un contexto de flujo (stream context) de PHP para simular una petición `POST` externa (como si fuera cURL) hacia el endpoint `finalizar_compra`. Es útil para comprobar cómo responde el controlador cuando recibe peticiones HTTP crudas, simulando integraciones de terceros o validando cabeceras.

### 8. `test_frontend.php`
- **¿Para qué sirve?:** Simulador de la experiencia completa del cliente (Frontend a Backend).
- **Funcionamiento:** Simula dos pasos consecutivos que normalmente haría un usuario en la página: primero envía un `POST` para agregar un producto al carrito (`agregar_carrito`) y captura la respuesta. Luego, envía otro `POST` para confirmar el pedido (`finalizar_compra`). Permite depurar el flujo de compra completo en menos de un segundo.

### 9. `test_produccion.php`
- **¿Para qué sirve?:** Verificador del módulo de producción.
- **Funcionamiento:** Al igual que los scripts de esquema, ejecuta un comando `DESCRIBE` sobre la tabla `produccion` y lista las columnas. Se utiliza para asegurarse de que la tabla requerida por el rol de trabajador ("Trabajador de Producción") está correctamente estructurada.

### 10. `test_ventas.php`
- **¿Para qué sirve?:** Suite de pruebas integral para la lógica de ventas y control de stock.
- **Funcionamiento:** Es el script de prueba más complejo y completo. Realiza dos pruebas automatizadas:
  1. **Prueba POS (Administrador):** Simula a un administrador creando una venta directa (`crearVentaPOS`). Verifica que se registre el detalle, que el sistema descuente correctamente el stock del producto y que las estadísticas generales se actualicen.
  2. **Prueba de Cliente:** Simula a un cliente realizando un pedido online (`crearVenta`), comprobando que se registre correctamente en su historial de compras personal.
  
Este script es una herramienta de "Testeo de Regresión" que garantiza que el núcleo del negocio (vender y descontar inventario) funciona perfectamente.
