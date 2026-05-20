<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

// Redirect if not a worker (assuming role '2' is worker)
if ($_SESSION['usuario']['id_rol'] != '2' && $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$titulo = "Panel de Trabajador";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="space-y-6 sm:space-y-8">
    <div class="glass-card rounded-xl sm:rounded-[2rem] lg:rounded-[2.5rem] premium-shadow border border-slate-100 overflow-hidden relative p-5 sm:p-7 lg:p-10 flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl"></div>
        <div class="text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-800 mb-1 sm:mb-2">Bienvenido a tu <span class="text-sky-600">Área de Trabajo</span></h2>
            <p class="text-slate-500 text-sm sm:text-base lg:text-lg">Selecciona una de las herramientas de operaciones para comenzar.</p>
        </div>
        <div class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 bg-sky-100 rounded-full flex items-center justify-center text-sky-600 text-2xl sm:text-3xl shadow-sm z-10 shrink-0">
            <i class="fas fa-hard-hat"></i>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        <!-- Producción -->
        <a href="produccion.php" class="glass-card rounded-xl sm:rounded-[2rem] lg:rounded-[2.5rem] p-4 sm:p-6 lg:p-8 border border-slate-100 premium-shadow hover:border-sky-300 hover:shadow-2xl hover:shadow-sky-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center text-xl sm:text-2xl font-bold mb-3 sm:mb-4 transition-all">
                <i class="fas fa-industry"></i>
            </div>
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 mb-1 sm:mb-2 group-hover:text-sky-600 transition-colors">Producción</h3>
            <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Registra y controla el flujo de producción diaria.</p>
        </a>

        <!-- Inventario Materia Prima -->
        <a href="inventario_mp.php" class="glass-card rounded-xl sm:rounded-[2rem] lg:rounded-[2.5rem] p-4 sm:p-6 lg:p-8 border border-slate-100 premium-shadow hover:border-emerald-300 hover:shadow-2xl hover:shadow-emerald-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-emerald-500 group-hover:text-white flex items-center justify-center text-xl sm:text-2xl font-bold mb-3 sm:mb-4 transition-all">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 mb-1 sm:mb-2 group-hover:text-emerald-600 transition-colors">Inventario MP</h3>
            <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Consulta insumos y materia prima disponible.</p>
        </a>

        <!-- Gestión de Lotes -->
        <a href="lotes.php" class="glass-card rounded-xl sm:rounded-[2rem] lg:rounded-[2.5rem] p-4 sm:p-6 lg:p-8 border border-slate-100 premium-shadow hover:border-amber-300 hover:shadow-2xl hover:shadow-amber-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center text-xl sm:text-2xl font-bold mb-3 sm:mb-4 transition-all">
                <i class="fas fa-box-open"></i>
            </div>
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 mb-1 sm:mb-2 group-hover:text-amber-600 transition-colors">Gestión de Lotes</h3>
            <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Administra lotes y detalles de envases.</p>
        </a>
        
        <!-- Inventario Productos -->
        <a href="inventario_productos.php" class="glass-card rounded-xl sm:rounded-[2rem] lg:rounded-[2.5rem] p-4 sm:p-6 lg:p-8 border border-slate-100 premium-shadow hover:border-indigo-300 hover:shadow-2xl hover:shadow-indigo-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-indigo-500 group-hover:text-white flex items-center justify-center text-xl sm:text-2xl font-bold mb-3 sm:mb-4 transition-all">
                <i class="fas fa-cubes"></i>
            </div>
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 mb-1 sm:mb-2 group-hover:text-indigo-600 transition-colors">Inventario Prod.</h3>
            <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Visualiza y controla el stock de producto terminado.</p>
        </a>
    </div>
</div>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
