<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOOVA! - El Agua Más Pura</title>
    <link rel="shortcut icon" type="image/png" href="img/trigestion.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f0f9ff; color: #0f172a; }
        
        .water-glass {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(14, 165, 233, 0.15);
        }

        .water-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.6);
        }

        .text-water-gradient {
            background: linear-gradient(to right, #0ea5e9, #0284c7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-water-gradient {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }

        /* Floating Animation for Water Feel */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: floating 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="relative overflow-x-hidden selection:bg-sky-500/30">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 water-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <img src="img/triges.png" alt="MOOVA Logo" class="h-10 w-auto drop-shadow-md">
                <span class="text-3xl font-black text-sky-800 tracking-tight">MOOVA!</span>
            </div>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center gap-8 font-bold text-slate-600">
                <a href="#beneficios" class="hover:text-sky-600 transition-colors">Beneficios</a>
                <a href="#productos" class="hover:text-sky-600 transition-colors">Productos</a>
                
                <?php if(isset($_SESSION['usuario'])): ?>
                    <a href="views/dashboard/<?= $_SESSION['usuario']['id_rol'] == 1 ? 'admin.php' : ($_SESSION['usuario']['id_rol'] == 2 ? 'produccion.php' : 'cliente.php') ?>" 
                       class="px-6 py-2.5 rounded-full bg-white border-2 border-sky-100 hover:border-sky-500 text-sky-700 transition-all font-bold shadow-sm">
                        Mi Panel
                    </a>
                <?php else: ?>
                    <a href="views/usuarios/login.php" class="text-sky-700 hover:text-sky-900 transition-colors">Iniciar Sesión</a>
                    <a href="views/usuarios/registre.php" class="px-7 py-3 rounded-full bg-water-gradient hover:shadow-[0_10px_20px_-5px_rgba(14,165,233,0.4)] transition-all text-white transform hover:-translate-y-1">
                        Pedir Ahora
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-[90vh] flex items-center pt-20 overflow-hidden">
        <!-- Water Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="img/login_bg.png" alt="Agua Pura" class="w-full h-full object-cover object-center filter brightness-110">
            <!-- White fade at the bottom to blend with next section -->
            <div class="absolute inset-0 bg-gradient-to-b from-white/40 via-white/20 to-white/90"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10 w-full grid lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left Text -->
            <div class="text-center lg:text-left mt-10 lg:mt-0">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 backdrop-blur border border-white mb-6 shadow-sm">
                    <i class="fas fa-droplet text-sky-500"></i>
                    <span class="text-sm font-black text-sky-700 uppercase tracking-widest">Frescura y Pureza</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black text-slate-800 leading-[1.1] mb-6 tracking-tight drop-shadow-sm">
                    La mejor <span class="text-water-gradient">hidratación</span> para tu familia.
                </h1>
                
                <p class="text-lg md:text-xl text-slate-600 mb-10 font-medium max-w-lg mx-auto lg:mx-0 leading-relaxed drop-shadow-sm">
                    Agua purificada de máxima calidad, directo a tu puerta. Realiza tu pedido en segundos con la plataforma MOOVA!.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="views/usuarios/registre.php" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-water-gradient text-white font-bold hover:shadow-[0_10px_30px_-10px_rgba(14,165,233,0.5)] transform hover:-translate-y-1 transition-all text-lg flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-basket"></i> Haz tu pedido
                    </a>
                    <a href="#beneficios" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white border border-slate-200 text-sky-700 font-bold hover:bg-sky-50 transition-all text-lg flex items-center justify-center gap-2 shadow-sm">
                        Conoce más
                    </a>
                </div>
            </div>

            <!-- Right Image/Mockup -->
            <div class="hidden lg:flex justify-center items-center relative animate-float">
                <div class="relative w-[400px] h-[500px] water-glass rounded-[3rem] p-8 flex flex-col items-center justify-center text-center">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-sky-200 rounded-full blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-200 rounded-full blur-3xl opacity-50"></div>
                    
                    <img src="img/triges.png" alt="Botella MOOVA" class="w-48 h-auto object-contain mb-8 filter drop-shadow-2xl">
                    <h3 class="text-3xl font-black text-slate-800 mb-2">Agua Premium</h3>
                    <p class="text-sky-600 font-bold mb-6">100% Purificada</p>
                    
                    <div class="flex gap-2">
                        <span class="w-3 h-3 rounded-full bg-sky-300"></span>
                        <span class="w-3 h-3 rounded-full bg-sky-400"></span>
                        <span class="w-3 h-3 rounded-full bg-sky-500"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios Section -->
    <section id="beneficios" class="py-24 px-6 relative bg-gradient-to-b from-white/90 to-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-4xl md:text-5xl font-black text-slate-800 mb-6">¿Por qué elegir <span class="text-sky-600">MOOVA!</span>?</h2>
                <p class="text-slate-500 max-w-2xl mx-auto font-medium text-lg">Nos preocupamos por tu salud y bienestar, por eso nuestro proceso garantiza la máxima calidad en cada gota.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="water-glass p-10 rounded-[2.5rem] text-center group hover:-translate-y-2 transition-transform">
                    <div class="w-20 h-20 mx-auto rounded-full bg-sky-100 text-sky-500 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-4">100% Pura</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Sometida a rigurosos procesos de filtración y purificación para asegurar un sabor fresco e inigualable.</p>
                </div>
                
                <div class="water-glass p-10 rounded-[2.5rem] text-center group hover:-translate-y-2 transition-transform">
                    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-4">Entrega a Domicilio</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Olvídate de cargar pesados garrafones. Pídelo desde tu celular y nosotros lo llevamos hasta tu puerta.</p>
                </div>
                
                <div class="water-glass p-10 rounded-[2.5rem] text-center group hover:-translate-y-2 transition-transform">
                    <div class="w-20 h-20 mx-auto rounded-full bg-indigo-100 text-indigo-500 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-mobile-screen"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-4">Pedidos Fáciles</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Una plataforma moderna e intuitiva donde puedes ver tus compras y realizar nuevos pedidos en segundos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demostración de Productos Section -->
    <section id="productos" class="py-24 px-6 relative bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-sky-50 border border-sky-100 mb-6 shadow-sm">
                    <i class="fas fa-star text-amber-400"></i>
                    <span class="text-[10px] font-black text-sky-700 uppercase tracking-widest">Catálogo Premium</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-black text-slate-800 mb-6 tracking-tight">Nuestros <span class="text-sky-500">Productos</span></h2>
                <p class="text-slate-500 max-w-2xl mx-auto font-medium text-lg leading-relaxed">Conoce nuestra línea de hidratación. Agua pura, alcalina y mineralizada en presentaciones adaptadas a tu estilo de vida.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <!-- Producto 1: Garrafón -->
                <div class="bg-slate-50 rounded-[2.5rem] p-6 border border-slate-200 hover:shadow-[0_20px_40px_-10px_rgba(14,165,233,0.15)] hover:-translate-y-2 transition-all duration-300 group flex flex-col relative overflow-hidden">
                    <div class="absolute top-6 left-6 z-10 bg-white/90 backdrop-blur border border-slate-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-slate-500 uppercase tracking-wider shadow-sm">
                        Agua Purificada
                    </div>
                    <div class="w-full aspect-square bg-white rounded-[2rem] mb-6 flex items-center justify-center border border-slate-100 shadow-inner p-4 relative group-hover:bg-sky-50/50 transition-colors">
                        <div class="absolute inset-0 bg-sky-400/20 blur-2xl rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 opacity-0 group-hover:opacity-100"></div>
                        <img src="img/garrafon_premium.png" alt="Garrafón MOOVA 20L" class="w-full h-full object-contain relative z-10 group-hover:scale-110 transition-transform duration-500 filter drop-shadow-2xl">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-sky-600 transition-colors">Garrafón Premium 20L</h3>
                        <p class="text-slate-500 text-sm font-medium mb-6">Agua 100% purificada ideal para el dispensador de tu hogar o negocio.</p>
                        <div class="mt-auto pt-6 flex items-end justify-between border-t border-slate-200/60">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Precio Unitario</span>
                                <span class="text-2xl font-black text-slate-800">$12,000</span>
                            </div>
                            <a href="views/usuarios/registre.php" class="w-12 h-12 rounded-2xl bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/30 transform active:scale-95 transition-all">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Producto 2: Botella Individual -->
                <div class="bg-slate-50 rounded-[2.5rem] p-6 border border-slate-200 hover:shadow-[0_20px_40px_-10px_rgba(14,165,233,0.15)] hover:-translate-y-2 transition-all duration-300 group flex flex-col relative overflow-hidden">
                    <div class="absolute top-6 left-6 z-10 bg-white/90 backdrop-blur border border-slate-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-slate-500 uppercase tracking-wider shadow-sm">
                        Agua Premium
                    </div>
                    <div class="w-full aspect-square bg-white rounded-[2rem] mb-6 flex items-center justify-center border border-slate-100 shadow-inner p-4 relative group-hover:bg-sky-50/50 transition-colors">
                        <div class="absolute inset-0 bg-sky-400/20 blur-2xl rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 opacity-0 group-hover:opacity-100"></div>
                        <img src="img/botella_individual.png" alt="Botella Cristal MOOVA 500ml" class="w-full h-full object-contain relative z-10 group-hover:scale-110 transition-transform duration-500 filter drop-shadow-2xl">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-sky-600 transition-colors">Botella de Cristal 500ml</h3>
                        <p class="text-slate-500 text-sm font-medium mb-6">Diseño minimalista y elegante. La porción perfecta para acompañar tus comidas.</p>
                        <div class="mt-auto pt-6 flex items-end justify-between border-t border-slate-200/60">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Precio Unitario</span>
                                <span class="text-2xl font-black text-slate-800">$3,500</span>
                            </div>
                            <a href="views/usuarios/registre.php" class="w-12 h-12 rounded-2xl bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/30 transform active:scale-95 transition-all">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Producto 3: Botella Deportiva -->
                <div class="bg-slate-50 rounded-[2.5rem] p-6 border border-slate-200 hover:shadow-[0_20px_40px_-10px_rgba(14,165,233,0.15)] hover:-translate-y-2 transition-all duration-300 group flex flex-col relative overflow-hidden">
                    <div class="absolute top-6 left-6 z-10 bg-white/90 backdrop-blur border border-slate-200 px-3 py-1.5 rounded-full text-[10px] font-bold text-emerald-500 uppercase tracking-wider shadow-sm">
                        Agua Alcalina
                    </div>
                    <div class="w-full aspect-square bg-white rounded-[2rem] mb-6 flex items-center justify-center border border-slate-100 shadow-inner p-4 relative group-hover:bg-emerald-50/50 transition-colors">
                        <div class="absolute inset-0 bg-emerald-400/20 blur-2xl rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 opacity-0 group-hover:opacity-100"></div>
                        <img src="img/botella_deportiva.png" alt="Botella Deportiva MOOVA 750ml" class="w-full h-full object-contain relative z-10 group-hover:scale-110 transition-transform duration-500 filter drop-shadow-2xl">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-emerald-600 transition-colors">Termo Deportivo 750ml</h3>
                        <p class="text-slate-500 text-sm font-medium mb-6">Mantiene el frío por horas. Diseñado para el gimnasio o actividades al aire libre.</p>
                        <div class="mt-auto pt-6 flex items-end justify-between border-t border-slate-200/60">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Precio Unitario</span>
                                <span class="text-2xl font-black text-slate-800">$8,900</span>
                            </div>
                            <a href="views/usuarios/registre.php" class="w-12 h-12 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 transform active:scale-95 transition-all">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="views/usuarios/registre.php" class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-slate-900 text-white font-bold hover:scale-105 transition-all text-sm shadow-[0_10px_30px_-10px_rgba(15,23,42,0.5)]">
                    Ver todo el catálogo <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-24 px-6 relative">
        <div class="max-w-5xl mx-auto water-glass p-12 md:p-20 rounded-[3rem] text-center relative overflow-hidden bg-sky-600 border-none shadow-2xl">
            <!-- Decorative background inside CTA -->
            <div class="absolute inset-0 bg-[url('img/login_bg.png')] opacity-20 mix-blend-overlay bg-cover bg-center"></div>
            
            <div class="relative z-10">
                <i class="fas fa-bottle-water text-5xl text-white mb-6"></i>
                <h2 class="text-4xl md:text-5xl font-black text-white mb-6 tracking-tight">Refresca tu vida hoy.</h2>
                <p class="text-sky-100 mb-10 font-medium text-lg md:text-xl max-w-2xl mx-auto">Únete a nuestra plataforma y mantén tu hogar siempre hidratado con la mejor calidad.</p>
                <a href="views/usuarios/registre.php" class="inline-block px-10 py-5 rounded-full bg-white text-sky-700 font-black hover:scale-105 transition-all text-lg shadow-xl">
                    Crear cuenta de cliente
                </a>
                <p class="mt-6 text-sky-200 text-sm font-medium">¿Ya tienes cuenta? <a href="views/usuarios/login.php" class="text-white hover:underline font-bold">Inicia sesión</a></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 px-6 bg-slate-900 text-slate-400 text-center relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-center gap-3 mb-6">
                <img src="img/triges.png" alt="MOOVA Logo" class="h-8 w-auto filter brightness-0 invert opacity-50">
                <span class="text-2xl font-black tracking-tight text-slate-500">MOOVA!</span>
            </div>
            <p class="text-sm font-medium uppercase tracking-widest">&copy; <?= date('Y') ?> MOOVA! System. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>
