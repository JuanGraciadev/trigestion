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

<div class="space-y-8">
    <div class="glass-card rounded-[2.5rem] premium-shadow border border-slate-100 overflow-hidden relative p-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl"></div>
        <div>
            <h2 class="text-3xl font-black text-slate-800 mb-2">Bienvenido a tu <span class="text-sky-600">Área de Trabajo</span></h2>
            <p class="text-slate-500 text-lg">Selecciona una de las herramientas de operaciones para comenzar.</p>
        </div>
        <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center text-sky-600 text-3xl shadow-sm z-10">
            <i class="fas fa-hard-hat"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Producción -->
        <a href="produccion.php" class="glass-card rounded-[2.5rem] p-8 border border-slate-100 premium-shadow hover:border-sky-300 hover:shadow-2xl hover:shadow-sky-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center text-2xl font-bold mb-4 transition-all">
                <i class="fas fa-industry"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-sky-600 transition-colors">Producción</h3>
            <p class="text-sm text-slate-500">Registra y controla el flujo de producción diaria.</p>
        </a>

        <!-- Inventario Materia Prima -->
        <a href="inventario_mp.php" class="glass-card rounded-[2.5rem] p-8 border border-slate-100 premium-shadow hover:border-emerald-300 hover:shadow-2xl hover:shadow-emerald-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-emerald-500 group-hover:text-white flex items-center justify-center text-2xl font-bold mb-4 transition-all">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-emerald-600 transition-colors">Inventario MP</h3>
            <p class="text-sm text-slate-500">Consulta insumos y materia prima disponible.</p>
        </a>

        <!-- Gestión de Lotes -->
        <a href="lotes.php" class="glass-card rounded-[2.5rem] p-8 border border-slate-100 premium-shadow hover:border-amber-300 hover:shadow-2xl hover:shadow-amber-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center text-2xl font-bold mb-4 transition-all">
                <i class="fas fa-box-open"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-amber-600 transition-colors">Gestión de Lotes</h3>
            <p class="text-sm text-slate-500">Administra lotes y detalles de envases.</p>
        </a>
        
        <!-- Inventario Productos -->
        <a href="inventario_productos.php" class="glass-card rounded-[2.5rem] p-8 border border-slate-100 premium-shadow hover:border-indigo-300 hover:shadow-2xl hover:shadow-indigo-100 transition-all group flex flex-col items-center text-center hover:-translate-y-2">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-indigo-500 group-hover:text-white flex items-center justify-center text-2xl font-bold mb-4 transition-all">
                <i class="fas fa-cubes"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors">Inventario Prod.</h3>
            <p class="text-sm text-slate-500">Visualiza y controla el stock de producto terminado.</p>
        </a>
    </div>
</div>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
