# Documentación de la Landing Page (`index.php`)

El archivo `index.php` sirve como la puerta de entrada principal (Landing Page) para la plataforma **MOOVA! (Trigestion)**. Está diseñado para ser la cara pública del negocio, atrayendo tanto a nuevos clientes como facilitando el acceso a los usuarios que ya están registrados en el sistema.

Este archivo es casi en su totalidad una vista de *Frontend*, pero incluye lógicas clave de presentación y animación avanzada que le otorgan un aspecto "SaaS Premium".

---

## 1. Lógica Backend Básica (PHP)

Dado que es una página pública, la lógica del servidor es mínima pero sumamente importante:

- **Inicio de Sesión (`session_start()`):** Se inicia inmediatamente en la línea 2.
- **Navegación Dinámica:** En la barra de navegación (Navbar), el código PHP verifica si el visitante ya tiene una sesión iniciada (`isset($_SESSION['usuario'])`).
  - **Si no está logueado:** Muestra los botones de "Iniciar Sesión" y "Pedir Ahora" (Registro).
  - **Si está logueado:** El sistema detecta qué rol tiene el usuario (`1` Admin, `2` Trabajador, `3` Cliente) y dibuja un botón inteligente llamado **"Mi Panel"** que lo redirige automáticamente a su área de trabajo respectiva (`admin.php`, `produccion.php` o `cliente.php`), ahorrándole clics.

---

## 2. Arquitectura CSS Avanzada (Estética Premium)

El archivo no depende de hojas de estilo externas complejas, sino que implementa una poderosa combinación del framework **Tailwind CSS** y etiquetas `<style>` personalizadas para lograr efectos visuales de alta gama.

### Técnicas Visuales Utilizadas:
1. **Glassmorphism (Efecto Cristal):** Utilizando las clases `.water-glass` y `.water-nav`, se logran contenedores translúcidos que difuminan el fondo (`backdrop-filter: blur(16px)`).
2. **Textos con Gradientes:** La clase `.text-water-gradient` aplica colores degradados específicamente a las letras (y no al fondo) usando `-webkit-background-clip: text`.
3. **Animaciones Fluidas (Keyframes):**
   - `.animate-float`: Hace que el gran "vaso de agua" y las burbujas floten simulando gravedad cero (o flotabilidad en agua).
   - `.animate-blob`: Crea "orbes" de color borrosos en el fondo de la pantalla que se mueven y cambian de tamaño de forma autónoma.
4. **Brillo en Botones (`.btn-shine`):** Añade un destello blanco en diagonal que atraviesa el botón cuando el usuario pasa el cursor por encima, dando un toque "metálico" o "líquido".

---

## 3. Estructura de la Página (Secciones)

La página está dividida en 4 bloques principales:

### A. Hero Section (Sección Principal)
Es lo primero que ve el usuario. Tiene una imagen de fondo de agua tratada con filtros, un título poderoso de llamado a la acción y un "Mockup" (Maqueta) flotante a la derecha con un vaso de cristal simulado que contiene el logotipo del producto.

### B. Sección de Beneficios
Exhibe 3 tarjetas de cristal que resaltan los valores de la empresa (Agua pura, Entrega inmediata, Plataforma fácil). Al pasar el cursor sobre cada tarjeta, los iconos internos giran o se amplían.

### C. Demostración de Catálogo
Funciona como un "gancho" comercial. Muestra 3 tarjetas de productos (Garrafón, Botella de Cristal y Termo Deportivo) con precios de demostración. Los botones de `+` no añaden al carrito inmediatamente, sino que dirigen al usuario a registrarse en `registre.php` para poder comprar de verdad.

### D. Call to Action (Bloque Final)
Un gran banner de cristal con un degradado azul fuerte que invita por última vez al visitante a crear su cuenta antes de llegar al Footer.

---

## 4. Javascript (Interactividad y Rendimiento)

El final del archivo cuenta con un bloque inteligente de JavaScript para mejorar la experiencia de usuario (UX):

- **Observer de Animaciones (Intersection Observer):** En lugar de cargar todas las animaciones a la vez (lo cual alenta la página), el script vigila la barra de desplazamiento (Scroll). Cuando una sección o tarjeta (que tiene la clase `.reveal`) entra en el campo de visión del usuario, el script dispara la animación. Esto le da un aspecto fluido a la página a medida que se va bajando.
- **Navbar Inteligente:** Vigila el scroll; si el usuario está en la parte superior absoluta (`scrollY < 20`), la barra es transparente y grande. Al bajar, la barra reduce su tamaño y se vuelve un "cristal borroso" para no entorpecer la lectura pero permitir la navegación en todo momento.
