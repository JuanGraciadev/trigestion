# Documentación de Controladores (Controllers)

En la arquitectura MVC (Modelo-Vista-Controlador) de **MOOVA! (Trigestion)**, los **Controladores** actúan como el cerebro y el puente de comunicación de la aplicación. Representan la "Capa Lógica".

Los controladores nunca muestran diseño (eso lo hacen las Vistas) y nunca hablan con la base de datos de manera directa (eso lo hacen los Modelos). Su trabajo es:
1. Recibir las peticiones del usuario (formularios por POST, variables por GET o peticiones AJAX).
2. Limpiar y validar la información que envía el usuario.
3. Llamar a las herramientas correspondientes dentro de los Modelos.
4. Procesar archivos subidos (como las imágenes de los productos).
5. Configurar las alertas visuales (`$_SESSION['alert']`).
6. Decidir hacia dónde redirigir al usuario al terminar (o responder con un JSON en caso del carrito de compras).

A continuación, se detalla el propósito de los 9 controladores alojados en la carpeta `/controllers/`:

---

## 🔐 Controladores de Seguridad y Personas

### 1. `AuthController.php`
- **Propósito:** Manejar el acceso principal al sistema (Inicios de sesión).
- **Acciones Principales:**
  - `login`: Toma el usuario y contraseña, le pregunta al modelo de usuario si son correctos, e inicia la variable de sesión `$_SESSION['usuario']`. Luego redirige al panel correcto dependiendo de si es Admin, Trabajador o Cliente.
  - `logout`: Destruye completamente la sesión y envía al usuario de regreso a la pantalla de inicio.

### 2. `AdminUsuarioController.php`
- **Propósito:** Dedicado exclusivamente a las acciones que el Administrador hace en el directorio de usuarios (`admin.php`).
- **Acciones Principales:**
  - `crear` y `editar`: Manejan los formularios de los modales para crear empleados o clientes nuevos.
  - `toggleEstado`: Suspende o reactiva cuentas (baneos) asegurando que usuarios suspendidos no puedan ingresar.

### 3. `UsuarioController.php`
- **Propósito:** Utilizado principalmente para el registro público de nuevos clientes.
- **Acciones Principales:** 
  - `registrar_cliente`: Recibe la información del formulario de la landing page, valida que el correo no esté duplicado, le asigna forzosamente el Rol 3 (Cliente) por seguridad, y lo guarda en la base de datos.

---

## 📦 Controladores de Catálogo y Bodega

### 4. `CategoriaController.php`
- **Propósito:** Manejar las familias de productos.
- **Acciones Principales:**
  - Procesa la subida física de imágenes de la categoría al servidor (carpeta de `uploads/categorias/`).
  - Lógica para crear, editar, eliminar o alternar el estado (activo/inactivo) de una categoría.

### 5. `ProductoController.php`
- **Propósito:** Manejar la vitrina virtual de la tienda.
- **Acciones Principales:**
  - Similar a las categorías, maneja un algoritmo complejo para subir imágenes de los productos a la carpeta de `uploads/`, nombrando los archivos de forma segura.
  - Se asegura de que cada producto esté anclado a una categoría existente al crear o editar.

### 6. `LoteController.php`
- **Propósito:** Procesar la llegada física de camiones de proveedores.
- **Acciones Principales:**
  - Crea el registro madre del `Lote`.
  - Simultáneamente procesa los "Detalles de envase" y hace que el modelo de Materia Prima registre ese ingreso como disponible en bodega.

---

## 🏭 Controladores Operativos y Financieros

### 7. `ProduccionController.php`
- **Propósito:** Manejar la línea de ensamblaje o llenado en la fábrica.
- **Acciones Principales:**
  - `crear`: Abre una nueva orden de trabajo, vinculando al trabajador que está en sesión con un código de lote de producción.
  - `finalizar`: ¡Acción Crítica! Cuando el trabajador termina, este controlador orquesta a los modelos para restar botellas de la materia prima (insumos vacíos) y enviar las botellas llenas a la base de datos de Producto Terminado.

### 8. `InventarioProductosController.php`
- **Propósito:** Controlador auxiliar y de mantenimiento para la bodega final.
- **Acciones Principales:**
  - Permite hacer correcciones manuales de stock o mermas sin tener que pasar obligatoriamente por el módulo de Producción o Ventas.

### 9. `VentaController.php` (El motor asíncrono)
- **Propósito:** Procesar todo el flujo de dinero, facturación y el carrito virtual.
- **Características Especiales:** A diferencia de los otros controladores que recargan la página, este es un controlador **API/AJAX** en muchas de sus funciones. En lugar de redirigir, "imprime" respuestas en texto JSON.
- **Acciones Principales:**
  - `agregar_carrito`, `actualizar_carrito`, `eliminar_carrito`: Administran la variable virtual `$_SESSION['carrito']` sin tocar la base de datos para que la experiencia del cliente sea ultrarrápida.
  - `finalizar_compra`: Acción Crítica. Toma el carrito virtual, le pide al modelo de Venta que haga los cobros, genera el número de ticket físico, descuenta el stock final, vacía la memoria temporal y responde un JSON de "Éxito".
  - `crear` (POS): Lógica utilizada cuando el administrador registra una venta física tradicional sin usar carrito.
