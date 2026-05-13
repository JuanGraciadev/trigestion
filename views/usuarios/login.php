<?php
session_start();
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOOVA! - Iniciar Sesión</title>
    <link rel="shortcut icon" type="image/png" href="../../img/trigestion.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .water-gradient { background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); }
        .glass-input { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); }
        .animate-fade { animation: fadeIn 0.6s ease-out; }
        .animate-slide { animation: slideUp 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <img src="../../img/login_bg.png" alt="Background" class="w-full h-full object-cover">
        <!-- Soft overlay -->
        <div class="absolute inset-0 bg-sky-900/10 backdrop-blur-[4px]"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-5xl px-4 flex items-center justify-center">
        <div class="glass w-full rounded-[2.5rem] shadow-[0_30px_60px_-15px_rgba(14,165,233,0.15)] overflow-hidden flex flex-col lg:flex-row min-h-[640px] animate-fade">
            
            <!-- Left Side: Branding/Info (Hidden on small screens) -->
            <div class="hidden lg:flex lg:w-1/2 water-gradient p-14 text-white flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-sky-300/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-14">
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner p-2">
                            <img src="../../img/triges.png" alt="MOOVA Logo" class="w-full h-full object-contain filter brightness-0 invert">
                        </div>
                        <span class="text-3xl font-black tracking-tight">MOOVA!</span>
                    </div>
                    <h2 class="text-5xl font-black leading-[1.1] mb-6 tracking-tight">Gestiona la pureza con precisión.</h2>
                    <p class="text-lg text-sky-50 leading-relaxed opacity-90 font-medium">
                        La plataforma definitiva para el control integral de inventario, producción y ventas de agua embotellada.
                    </p>
                </div>

                <div class="relative z-10 bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-2xl flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-sky-100 uppercase tracking-widest mb-1">Certificación</div>
                        <div class="text-sm font-bold">CALIDAD GARANTIZADA</div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full lg:w-1/2 p-10 md:p-14 flex flex-col justify-center bg-white/50 relative">
                
                <div class="mb-10 text-center lg:text-left">
                    <div class="lg:hidden flex items-center justify-center gap-3 mb-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/30 p-2">
                            <img src="../../img/triges.png" alt="MOOVA Logo" class="w-full h-full object-contain filter brightness-0 invert">
                        </div>
                        <span class="text-3xl font-black text-slate-800">MOOVA!</span>
                    </div>

                    <a href="../../index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-500 hover:text-sky-600 hover:bg-sky-50 transition-all text-[12px] font-bold mb-8 shadow-sm border border-slate-200">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                    
                    <h1 class="text-3xl lg:text-4xl font-black text-slate-800 mb-2 tracking-tight">¡Bienvenido!</h1>
                    <p class="text-slate-500 font-medium text-[15px]">Ingresa tus credenciales para acceder al sistema.</p>
                </div>

                <form action="../../controllers/AuthController.php" method="POST" class="space-y-6 relative z-10">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Correo Electrónico</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-sky-500 transition-colors z-10">
                                <i class="fas fa-envelope text-lg"></i>
                            </span>
                            <input
                                type="email"
                                name="email"
                                placeholder="tu@correo.com"
                                required
                                class="glass-input w-full pl-12 pr-5 py-4 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 focus:bg-white transition-all text-slate-700 font-medium shadow-sm"
                            >
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Contraseña</label>
                            <a href="#" class="text-[12px] font-bold text-sky-600 hover:text-sky-700 transition-colors">¿Olvidaste tu clave?</a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-sky-500 transition-colors z-10">
                                <i class="fas fa-lock text-lg"></i>
                            </span>
                            <input
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="glass-input w-full pl-12 pr-5 py-4 border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 focus:bg-white transition-all text-slate-700 font-medium shadow-sm"
                            >
                        </div>
                    </div>

                    <div class="flex items-center px-1">
                        <div class="relative flex items-center justify-center mr-3">
                            <input type="checkbox" id="remember" class="peer appearance-none w-5 h-5 border-2 border-slate-300 rounded-md bg-white checked:bg-sky-500 checked:border-sky-500 transition-all cursor-pointer shadow-sm">
                            <i class="fas fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                        </div>
                        <label for="remember" class="text-sm text-slate-600 cursor-pointer font-medium select-none">Recordar mi sesión</label>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full water-gradient text-white font-bold py-4 rounded-2xl hover:shadow-lg hover:shadow-sky-300 transform active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 text-[15px]"
                        >
                            Acceder al Panel <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center pt-8 border-t border-slate-200/60">
                    <p class="text-slate-500 font-medium text-sm">
                        ¿No tienes cuenta de cliente? 
                        <a href="registre.php" class="text-sky-600 font-bold hover:text-sky-700 hover:underline transition-all ml-1">Crear una ahora</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert System Integration -->
    <?php if ($alert): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= htmlspecialchars($alert['icon']) ?>',
                title: '<?= htmlspecialchars($alert['title']) ?>',
                text: '<?= htmlspecialchars($alert['text']) ?>',
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'Aceptar',
                customClass: {
                    popup: 'rounded-[2rem] shadow-[0_20px_50px_-10px_rgba(0,0,0,0.1)] border border-slate-100',
                    confirmButton: 'rounded-xl px-8 py-3 font-bold'
                }
            });
        });
    </script>
    <?php endif; ?>

<script src="../../public/js/form-validation.js"></script>

</body>
</html>