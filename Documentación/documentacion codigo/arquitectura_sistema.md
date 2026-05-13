# Arquitectura y Modelo General del Sistema (MOOVA!)

El sistema de gestión **MOOVA! (Trigestion)** es una aplicación web robusta diseñada bajo el **Patrón de Arquitectura MVC (Modelo-Vista-Controlador)**. Este enfoque divide la aplicación en tres partes principales para mantener el código organizado, seguro y escalable.

---

## 1. El Patrón MVC (¿Cómo funciona?)

La aplicación opera a través de un ciclo continuo de peticiones y respuestas:

1. **Las Vistas (Views):** Es lo que el usuario ve y toca (HTML, CSS, Tailwind, JavaScript). Cuando un usuario hace clic en un botón (ej. "Comprar"), la vista envía la petición al Controlador.
2. **Los Controladores (Controllers):** Son el "Cerebro". Reciben la petición del usuario, validan que los datos sean correctos, y le dan las órdenes al Modelo correspondiente. Cuando terminan, deciden qué vista mostrarle al usuario.
3. **Los Modelos (Models):** Son los únicos que interactúan con la Base de Datos (MySQL). Reciben órdenes del controlador, leen o modifican las tablas de información y devuelven el resultado.

**Ejemplo Práctico:** 
*El cliente hace clic en el catálogo (Vista) -> El Controlador recibe la petición y pide consultar el stock -> El Modelo revisa la base de datos y le dice al Controlador que sí hay stock -> El Controlador actualiza el carrito y le avisa a la Vista para que dibuje la confirmación.*

---

## 2. Los 3 Pilares del Sistema (Roles de Usuario)

El sistema está diseñado para atender a tres tipos distintos de personas, aislando sus funciones por seguridad:

- **Rol 1: Administrador (`admin.php`)**
  - Es el jefe del sistema. Tiene acceso a todo.
  - Administra usuarios, categorías, ve métricas financieras, controla ventas físicas (POS) y puede acceder a las herramientas operativas si lo desea.
- **Rol 2: Trabajador de Planta (`trabajador.php` / `produccion.php`)**
  - Enfocado en la operación física de la empresa.
  - Registra las botellas vacías que traen los proveedores (Gestión de Lotes).
  - Fabrica el producto (Producción) convirtiendo plástico y agua en productos terminados.
- **Rol 3: Cliente (`cliente.php`)**
  - Solo tiene acceso a la tienda virtual.
  - Explora el catálogo, añade al carrito y procesa sus compras. No puede ver ningún dato interno de la empresa.

---

## 3. El Flujo de Trabajo (Workflows)

El sistema simula el ciclo de vida real de una embotelladora de agua. Funciona en dos grandes flujos independientes que se conectan en un punto central:

### A. El Flujo de Producción (El Trabajador)
1. **Llegada de Insumos:** Un proveedor trae botellas vacías. El trabajador las registra en **Gestión de Lotes**.
2. **Bodega de Insumos:** Esos envases entran a formar parte del **Inventario de Materia Prima**.
3. **Manufactura:** El trabajador abre una orden en el panel de **Producción**. Toma botellas de la bodega de insumos (restándolas de ahí), las llena de agua y al finalizar la orden, las convierte en **Producto Terminado**.

### B. El Flujo de Ventas (El Cliente)
1. **Vitrina Virtual:** El cliente entra a su catálogo y ve los productos disponibles.
2. **Carrito de Compras:** Añade productos. El sistema vigila silenciosamente que haya **Producto Terminado** disponible.
3. **Cierre de Venta:** El cliente finaliza su pedido. El sistema crea una factura y **resta** esas botellas del Producto Terminado.

*Ambos flujos se conectan en la base de datos de Producto Terminado: el trabajador la llena y el cliente la vacía.*

---

## 4. Estructura del Directorio (Árbol de Archivos)

El proyecto está organizado en las siguientes carpetas:

```text
/trigestion
├── /config/            # Conexión a la base de datos (PDO)
├── /controllers/       # Capa Lógica. Procesan peticiones y subida de archivos
├── /models/            # Capa de Datos. Queries a MySQL y reglas de negocio
├── /views/             # Capa Gráfica. Lo que ve el usuario.
│   ├── /dashboard/     # Los paneles internos (Admin, Cliente, Trabajador, etc.)
│   ├── /layouts/       # Pedazos de HTML repetitivos (Header, Sidebar, Footer)
│   └── /usuarios/      # Formularios de Login y Registro público
├── /Documentación/     # Archivos .md que explican cómo funciona cada parte
├── /img/               # Recursos gráficos estáticos y logotipos
├── /uploads/           # Fotografías de productos y categorías subidas por usuarios
└── index.php           # Landing page principal y vitrina de marketing pública
```

---

## 5. Pila Tecnológica (Tech Stack)

- **Backend:** PHP Nativo Orientado a Objetos (OOP). Rápido, seguro (vía PDO para prevenir inyección SQL) y ligero.
- **Base de Datos:** MySQL. Relacional y estructurada para no permitir ventas falsas o inventario negativo.
- **Frontend CSS:** Tailwind CSS. Permite un diseño extremadamente rápido, responsivo y efectos modernos como *Glassmorphism*.
- **Frontend JS:** Vanilla JavaScript con *Fetch API* (para el carrito de compras sin recargar la página) y alertas inmersivas (SweetAlert2).
