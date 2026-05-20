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
    <title>MOOVA! - Registro de Cliente</title>
    <link rel="shortcut icon" type="image/png" href="../../img/trigestion.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
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

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-3 sm:p-4 relative overflow-x-hidden">
    
    <!-- Background Decoration -->
    <div class="absolute inset-0 z-0">
        <img src="../../img/login_bg.png" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-sky-900/10 backdrop-blur-[1px]"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-6xl my-2 sm:my-4">
        <div class="glass rounded-2xl sm:rounded-[3rem] shadow-2xl shadow-sky-900/20 overflow-hidden flex flex-col lg:flex-row animate-slide max-h-[95vh] overflow-y-auto lg:overflow-y-visible lg:max-h-none">
            
            <!-- Left Side: Welcome Info (Hidden on mobile, shown as compact on tablet) -->
            <div class="hidden lg:flex lg:w-1/3 water-gradient p-8 lg:p-10 xl:p-14 text-white flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-8 lg:mb-10">
                        <img src="../../img/triges.png" alt="MOOVA Logo" class="h-8 lg:h-10 w-auto brightness-0 invert">
                        <span class="text-xl lg:text-2xl font-bold tracking-tight">MOOVA!</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl xl:text-4xl font-bold leading-tight mb-4 lg:mb-6">Únete a la revolución de la <span class="text-sky-200">pureza</span>.</h1>
                    <p class="text-base lg:text-lg text-sky-50 opacity-90 leading-relaxed mb-6 lg:mb-8">
                        Crea tu cuenta hoy y comienza a disfrutar de la mejor hidratación con la comodidad que mereces.
                    </p>
                    
                    <ul class="space-y-3 lg:space-y-4">
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <i class="fas fa-check-circle text-sky-300"></i>
                            Pedidos en un solo clic
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <i class="fas fa-check-circle text-sky-300"></i>
                            Seguimiento en tiempo real
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <i class="fas fa-check-circle text-sky-300"></i>
                            Historial de consumos
                        </li>
                    </ul>
                </div>

                <div class="mt-8 lg:mt-12 pt-6 lg:pt-10 border-t border-white/20">
                    <p class="text-sm font-medium opacity-80 italic">"Hidratación inteligente para personas excepcionales."</p>
                </div>
            </div>

            <!-- Right Side: Registration Form -->
            <div class="flex-1 p-5 sm:p-6 md:p-8 lg:p-10 xl:p-14 bg-white/50 relative">
                <!-- Mobile header with branding -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-5 sm:mb-6">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/30 p-1.5">
                        <img src="../../img/triges.png" alt="MOOVA Logo" class="w-full h-full object-contain filter brightness-0 invert">
                    </div>
                    <span class="text-xl sm:text-2xl font-black text-slate-800">MOOVA!</span>
                </div>

                <div class="mb-5 sm:mb-8 lg:mb-10">
                    <a href="../../index.php" class="inline-flex items-center gap-2 text-sky-500 hover:text-sky-600 transition-colors text-[12px] sm:text-[13px] font-bold mb-4 sm:mb-6 tracking-wide">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-1.5 sm:mb-2">Crear Cuenta Nueva</h2>
                    <p class="text-slate-500 text-sm sm:text-base">Completa tus datos para empezar tu experiencia MOOVA!.</p>
                </div>

                <form action="../../controllers/UsuarioController.php" method="POST" class="space-y-4 sm:space-y-5 lg:space-y-6">
                    <input type="hidden" name="rol" value="cliente">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 lg:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nombres Completos</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="nombres" required maxlength="100"
                                    class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="Ej. Juan Pérez">
                            </div>
                        </div>

                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Dirección de Entrega</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <input type="text" name="direccion" required maxlength="100"
                                    class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="Ej. Calle 10 #45-67">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 lg:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Correo Electrónico</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" required maxlength="150"
                                class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="juan@ejemplo.com">
                            </div>
                        </div>

                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Documento / Identificación</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <input type="text" name="documento_numero" required maxlength="150"
                                class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="Número de Cédula/NIT">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Teléfono de Contacto</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="text" name="telefono" required maxlength="30"
                                class="w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                placeholder="Ej. 300 123 4567">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 lg:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Contraseña</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" required
                                class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="••••••••">
                            </div>
                        </div>

                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Confirmar Contraseña</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4 text-slate-400 z-10">
                                    <i class="fas fa-check-double"></i>
                                </span>
                                <input type="password" name="confirmar_password" required
                                class="glass-input w-full pl-10 sm:pl-11 pr-4 sm:pr-5 py-3 sm:py-3.5 border border-slate-200 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-sm sm:text-base"
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 px-1">
                        <div class="relative flex items-center justify-center mt-0.5">
                            <input id="terms" type="checkbox" required class="peer appearance-none w-5 h-5 border-2 border-slate-300 rounded-md bg-white checked:bg-sky-500 checked:border-sky-500 transition-all cursor-pointer shadow-sm">
                            <i class="fas fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                        </div>
                        <label for="terms" class="text-xs sm:text-sm text-slate-600 font-medium leading-snug cursor-pointer select-none">
                            Acepto los <a href="#" class="text-sky-600 font-bold hover:underline">términos de servicio</a> y la política de tratamiento de datos personales y comerciales de MOOVA!.
                        </label>
                    </div>

                    <div class="pt-2 sm:pt-4">
                        <button type="submit"
                            class="w-full water-gradient text-white font-bold py-3.5 sm:py-4 rounded-xl sm:rounded-2xl hover:shadow-xl hover:shadow-sky-200 transform hover:-translate-y-1 transition-all shadow-lg active:scale-95 text-sm sm:text-base">
                            Completar Registro
                        </button>
                    </div>

                    <div class="text-center pt-1 sm:pt-2">
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">
                            ¿Ya eres cliente? <a href="login.php" class="text-sky-600 font-bold hover:underline">Inicia sesión aquí</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert System -->
    <?php if ($alert): ?>
    <script>
        Swal.fire({
            icon: '<?= htmlspecialchars($alert['icon']) ?>',
            title: '<?= htmlspecialchars($alert['title']) ?>',
            text: '<?= htmlspecialchars($alert['text']) ?>',
            confirmButtonColor: '#0ea5e9',
            confirmButtonText: 'Aceptar',
            customClass: {
                popup: 'rounded-[2.5rem]',
                confirmButton: 'rounded-xl px-10 py-4 font-bold'
            }
        }).then(() => {
            <?php if (!empty($alert['redirect'])): ?>
                window.location.href = '<?= htmlspecialchars($alert['redirect']) ?>';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

<!-- Validación global de formularios -->
<script src="../../public/js/form-validation.js"></script>

</body>
</html>