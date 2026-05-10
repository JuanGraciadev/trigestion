<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOOVA! - Pureza en cada gota</title>
    <link rel="shortcut icon" type="image/png" href="../img/trigestion.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .water-gradient {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }

        .text-gradient {
            background: linear-gradient(to right, #0ea5e9, #0369a1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .slide {
            display: none;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .slide.active {
            display: block;
            opacity: 1;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

    <!-- Header / Nav -->
    <nav class="fixed w-full z-50 glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center">
                    <img src="../img/triges.png" alt="MOOVA Logo" class="h-12 w-auto">
                    <span class="ml-2 text-2xl font-bold tracking-tight text-gradient">MOOVA!</span>
                </div>
                <div class="hidden md:flex space-x-10 items-center font-semibold">
                    <a href="#inicio" class="text-slate-600 hover:text-sky-600 transition-colors">Inicio</a>
                    <a href="#productos" class="text-slate-600 hover:text-sky-600 transition-colors">Productos</a>
                    <a href="#nosotros" class="text-slate-600 hover:text-sky-600 transition-colors">Nosotros</a>
                    <a href="../views/usuarios/login.php"
                        class="water-gradient text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:shadow-sky-200 transition-all transform hover:-translate-y-0.5">
                        Área Cliente
                    </a>
                </div>
                <div class="md:hidden">
                    <button class="text-slate-600"><i class="fas fa-bars text-2xl"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="../img/hero.png" alt="Fresh Water Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/50 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <span class="inline-block px-4 py-1.5 mb-6 text-sm font-bold tracking-wider text-sky-600 uppercase bg-sky-100 rounded-full">
                    Pureza que Transforma
                </span>
                <h1 class="text-5xl md:text-7xl font-bold text-slate-900 leading-tight mb-6">
                    El agua más <span class="text-gradient">fresca</span> directamente a tu hogar.
                </h1>
                <p class="text-xl text-slate-600 mb-10 leading-relaxed">
                    Extraída de fuentes naturales y procesada con la más alta tecnología para garantizar la máxima pureza y equilibrio mineral.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#productos" class="water-gradient text-white text-center px-8 py-4 rounded-full font-bold text-lg hover:shadow-xl hover:shadow-sky-300 transition-all transform hover:-translate-y-1">
                        Hacer Pedido Ahora
                    </a>
                    <a href="#nosotros" class="bg-white text-slate-900 text-center px-8 py-4 rounded-full font-bold text-lg border border-slate-200 hover:bg-slate-50 transition-all">
                        Conocer Más
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12">
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500">
                    <div class="w-16 h-16 water-gradient rounded-2xl flex items-center justify-center text-white text-2xl mb-6 shadow-lg shadow-sky-200 group-hover:scale-110 transition-transform">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900">Máxima Pureza</h3>
                    <p class="text-slate-600 leading-relaxed">Procesos de ultrafiltración y ozonización que eliminan impurezas manteniendo el sabor natural.</p>
                </div>
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500">
                    <div class="w-16 h-16 water-gradient rounded-2xl flex items-center justify-center text-white text-2xl mb-6 shadow-lg shadow-sky-200 group-hover:scale-110 transition-transform">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900">Entrega Express</h3>
                    <p class="text-slate-600 leading-relaxed">Logística optimizada para que nunca te falte hidratación en casa o en la oficina.</p>
                </div>
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500">
                    <div class="w-16 h-16 water-gradient rounded-2xl flex items-center justify-center text-white text-2xl mb-6 shadow-lg shadow-sky-200 group-hover:scale-110 transition-transform">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900">Eco-Amigable</h3>
                    <p class="text-slate-600 leading-relaxed">Comprometidos con el medio ambiente a través de envases retornables y procesos sostenibles.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Showcase -->
    <section id="productos" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6">Nuestros <span class="text-gradient">Productos</span></h2>
                <p class="text-xl text-slate-600 max-w-2xl mx-auto">Variedad de formatos diseñados para adaptarse a cada una de tus necesidades.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="relative">
                    <div class="absolute -inset-4 bg-sky-200/50 rounded-full blur-3xl opacity-30"></div>
                    <img src="../img/products.png" alt="Products Showcase" class="relative z-10 rounded-3xl floating">
                </div>
                <div class="space-y-8">
                    <div class="flex gap-6 p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-slate-100">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 font-bold italic">20L</div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">Garrafón de 20 Litros</h4>
                            <p class="text-slate-600">Ideal para el hogar y dispensadores de oficina. Máximo ahorro.</p>
                        </div>
                    </div>
                    <div class="flex gap-6 p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-slate-100">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 font-bold italic">1L</div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">Botella de 1 Litro</h4>
                            <p class="text-slate-600">El equilibrio perfecto entre hidratación y portabilidad diaria.</p>
                        </div>
                    </div>
                    <div class="flex gap-6 p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-slate-100">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 font-bold italic">500</div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">Botella 500ml Personal</h4>
                            <p class="text-slate-600">Para llevar al gimnasio, al parque o a cualquier lugar.</p>
                        </div>
                    </div>
                    <button class="w-full water-gradient text-white py-4 rounded-xl font-bold hover:shadow-xl transition-all">Ver Catálogo Completo</button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="nosotros" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-20 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-slate-900 mb-8 leading-tight">Llevando frescura desde <span class="text-gradient">nuestras raíces</span> hasta tu mesa.</h2>
                    <div class="space-y-8">
                        <div class="border-l-4 border-sky-500 pl-6">
                            <h3 class="text-2xl font-bold mb-2">Misión</h3>
                            <p class="text-slate-600 leading-relaxed italic">"Apoyar a la industria de agua embotellada mediante soluciones integrales de gestión de inventario, ventas y producción, garantizando eficiencia, calidad y sostenibilidad."</p>
                        </div>
                        <div class="border-l-4 border-sky-500 pl-6">
                            <h3 class="text-2xl font-bold mb-2">Visión</h3>
                            <p class="text-slate-600 leading-relaxed italic">"Convertirnos en la plataforma líder de gestión para la industria de agua embotellada, integrando innovación tecnológica y responsabilidad ambiental."</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="../img/purity.png" alt="Purity Concept" class="rounded-[2rem] shadow-2xl">
                    <div class="absolute -bottom-10 -right-10 w-48 h-48 water-gradient rounded-full flex items-center justify-center text-white text-center p-6 shadow-xl floating">
                        <div>
                            <span class="block text-4xl font-bold">100%</span>
                            <span class="text-sm font-semibold uppercase tracking-widest">Natural</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-2">
                    <div class="flex items-center mb-8">
                        <img src="../img/triges.png" alt="MOOVA Logo" class="h-10 w-auto">
                        <span class="ml-2 text-2xl font-bold tracking-tight text-white">MOOVA!</span>
                    </div>
                    <p class="max-w-sm mb-8 leading-relaxed">
                        Líderes en gestión y distribución de agua potable de alta calidad. Tecnología aplicada a la pureza.
                    </p>
                    <div class="flex space-x-6">
                        <a href="#" class="text-2xl hover:text-sky-400 transition-colors"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-2xl hover:text-sky-400 transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-2xl hover:text-sky-400 transition-colors"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-8">Enlaces</h4>
                    <ul class="space-y-4">
                        <li><a href="#inicio" class="hover:text-sky-400 transition-colors">Inicio</a></li>
                        <li><a href="#productos" class="hover:text-sky-400 transition-colors">Productos</a></li>
                        <li><a href="#nosotros" class="hover:text-sky-400 transition-colors">Sobre Nosotros</a></li>
                        <li><a href="../views/usuarios/login.php" class="hover:text-sky-400 transition-colors">Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-8">Contacto</h4>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-sky-400"></i>
                            trigestion@gmail.com
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone-alt text-sky-400"></i>
                            +57 322 851 8645
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-map-marker-alt text-sky-400"></i>
                            Bogotá, Colombia
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm">
                <p>&copy; 2026 MOOVA! TRIGESTION. Todos los derechos reservados.</p>
                <p class="mt-4 md:mt-0 text-slate-500">Diseñado para la excelencia en hidratación.</p>
            </div>
        </div>
    </footer>

</body>

</html>