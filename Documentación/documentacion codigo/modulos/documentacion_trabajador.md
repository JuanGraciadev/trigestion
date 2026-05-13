# Documentación del Dashboard del Trabajador (`trabajador.php`)

El archivo `trabajador.php` sirve como el panel de bienvenida y centro de distribución (Hub Operativo) para los usuarios que tienen el rol de **Trabajador de Producción** en la plataforma **MOOVA! (Trigestion)**. 

A diferencia del panel de administrador que muestra listas de datos y estadísticas complejas, este panel está diseñado para ser rápido, intuitivo y orientado a la acción, proporcionando accesos directos a las herramientas clave necesarias para la operación diaria de la planta.

---

## 1. Protección y Lógica Backend (PHP)

El archivo asegura de inmediato que el usuario correcto esté viendo la pantalla:

### Verificación de Sesión y Rol
- **Autenticación:** Comprueba si existe la variable de sesión `$_SESSION['usuario']`. Si no hay una sesión activa, el usuario es redirigido inmediatamente a la página de inicio de sesión (`login.php`).
- **Autorización:** Valida que el `id_rol` del usuario sea igual a `2` (Trabajador) o `1` (Administrador). Esto significa que los administradores también pueden acceder a esta vista si lo necesitan, pero un usuario con rol `3` (Cliente) sería rechazado y enviado al inicio de sesión.

---

## 2. Interfaz de Usuario (UI) y Diseño

El diseño está enfocado en la usabilidad y la reducción de la carga cognitiva para el operario. Utiliza el framework CSS Tailwind y mantiene la estética premium de la plataforma.

### Tarjeta de Bienvenida (Header Card)
- Es una tarjeta grande y destacada en la parte superior.
- Utiliza la clase `.glass-card` con un efecto `premium-shadow` para simular un contenedor de cristal levitando.
- Tiene un destello circular de luz azul en el fondo (`blur-3xl`, `bg-sky-500/10`) que le da un aspecto moderno e inmersivo.
- Da una instrucción clara y directa: *"Selecciona una de las herramientas de operaciones para comenzar"*.

---

## 3. Accesos Directos Operativos (Grid de Herramientas)

El cuerpo principal del Dashboard es una cuadrícula (Grid) que se adapta al tamaño de la pantalla (`grid-cols-1` en móviles, hasta `grid-cols-4` en pantallas grandes). Cada módulo está representado por una tarjeta interactiva de gran tamaño.

### Animaciones e Interactividad
Cada tarjeta (que internamente es una etiqueta `<a>` de enlace) cuenta con efectos visuales avanzados al pasar el cursor (Hover) usando las clases de grupo (`group`) de Tailwind CSS:
- La tarjeta entera flota ligeramente hacia arriba (`hover:-translate-y-2`) y su sombra se intensifica.
- El ícono central cambia su fondo y el color del ícono se invierte para volverse blanco.
- El borde de la tarjeta y el color del título adoptan el color temático del módulo al que apuntan.

### Los 4 Módulos del Trabajador

1. **🏭 Producción (`produccion.php`)**
   - **Color Temático:** Azul Cielo / Sky (`hover:border-sky-300`, `group-hover:text-sky-600`).
   - **Propósito:** Es la herramienta principal. Aquí el trabajador inicia nuevos lotes de producción de botellas y garrafones, registra quién hizo el trabajo, y finaliza el lote para dar entrada al inventario de productos terminados.

2. **📦 Inventario Materia Prima (`inventario_mp.php`)**
   - **Color Temático:** Verde Esmeralda / Emerald (`hover:border-emerald-300`).
   - **Propósito:** Permite al operario consultar los insumos físicos disponibles en la bodega (botellas vacías, tapas, etiquetas, etc.) y visualizar gráficas de qué tanto material se ha consumido.

3. **🗃️ Gestión de Lotes (`lotes.php`)**
   - **Color Temático:** Ámbar / Amber (`hover:border-amber-300`).
   - **Propósito:** Se utiliza para registrar formalmente los lotes físicos de proveedores. Aquí se anotan las fechas, los detalles de los envases (tipo, capacidad) y de qué proveedor vinieron, antes de que entren como materia prima utilizable.

4. **🧊 Inventario Productos (`inventario_productos.php`)**
   - **Color Temático:** Índigo / Indigo (`hover:border-indigo-300`).
   - **Propósito:** Herramienta para visualizar el stock final de producto terminado (agua purificada ya embotellada lista para vender) y el histórico de despachos.

---

## Resumen del Flujo del Trabajador
1. El trabajador inicia sesión y el sistema detecta que tiene el rol `2`.
2. Es redirigido automáticamente a `trabajador.php`.
3. Se encuentra con un menú claro de 4 botones.
4. Para empezar su jornada, normalmente hace clic en **"Producción"**, donde selecciona el producto, inicia la tarea, e informa al sistema cuando las botellas están listas. 
5. Si el trabajador necesita revisar existencias antes de producir, simplemente hace clic en cualquiera de los módulos de inventario para consultar la base de datos en tiempo real.
