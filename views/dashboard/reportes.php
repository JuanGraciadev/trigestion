<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->conectar();

// 1. Métricas Generales (KPIs)
$stats = [
    'ventas_totales' => 0,
    'ingresos_totales' => 0,
    'lotes_activos' => 0,
    'productos_registrados' => 0
];

try {
    // Ventas Totales y Ingresos (solo ventas completadas o enviadas, asumimos estado != 'Pendiente')
    $stmtVentas = $db->query("SELECT COUNT(*) as total, SUM(total) as ingresos FROM venta WHERE estado != 'Pendiente'");
    $resVentas = $stmtVentas->fetch(PDO::FETCH_ASSOC);
    $stats['ventas_totales'] = $resVentas['total'] ?? 0;
    $stats['ingresos_totales'] = $resVentas['ingresos'] ?? 0;

    // Lotes
    $stmtLotes = $db->query("SELECT COUNT(*) as total FROM lote");
    $resLotes = $stmtLotes->fetch(PDO::FETCH_ASSOC);
    $stats['lotes_activos'] = $resLotes['total'] ?? 0;

    // Productos
    $stmtProd = $db->query("SELECT COUNT(*) as total FROM producto WHERE estado = 1");
    $resProd = $stmtProd->fetch(PDO::FETCH_ASSOC);
    $stats['productos_registrados'] = $resProd['total'] ?? 0;
    
    // 2. Datos para Gráfico de Ventas por Estado
    $stmtVentasEstado = $db->query("SELECT estado, COUNT(*) as cantidad FROM venta GROUP BY estado");
    $ventasPorEstado = $stmtVentasEstado->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Datos para Top Productos más Vendidos
    $queryTopProductos = "
        SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
        FROM detalle_venta dv 
        JOIN producto p ON dv.id_producto = p.id_producto 
        JOIN venta v ON dv.id_venta = v.id_venta 
        WHERE v.estado != 'Pendiente'
        GROUP BY p.id_producto 
        ORDER BY total_vendido DESC 
        LIMIT 5
    ";
    $stmtTopProd = $db->query($queryTopProductos);
    $topProductos = $stmtTopProd->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {}

$titulo = "Reportes Generales";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Chart.js para visualizaciones premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-10">
    <!-- Encabezado -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-sky-500/5 rounded-full blur-3xl -ml-20 -mb-20 pointer-events-none"></div>
        
        <div class="p-8 md:p-12 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 mb-3 shadow-sm">
                    <i class="fas fa-chart-line text-indigo-500"></i>
                    <span class="text-[10px] font-black text-indigo-700 uppercase tracking-widest">Inteligencia de Negocio</span>
                </div>
                <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight outfit-font">Reportes <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-sky-500">Generales</span></h2>
                <p class="text-slate-500 mt-2 font-medium max-w-xl">Métricas de rendimiento, análisis de ventas y visión panorámica del negocio.</p>
            </div>
            <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 px-6 py-3 rounded-2xl font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fas fa-print"></i>
                Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- KPIs Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Ingresos -->
        <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wallet text-6xl text-emerald-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-4 shadow-sm border border-emerald-100">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-[11px] uppercase tracking-wider mb-1">Ingresos Totales</h3>
            <p class="text-3xl font-black text-slate-800">$<?= number_format($stats['ingresos_totales'], 2) ?></p>
        </div>

        <!-- Ventas -->
        <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-shopping-cart text-6xl text-indigo-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl mb-4 shadow-sm border border-indigo-100">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-[11px] uppercase tracking-wider mb-1">Ventas Completadas</h3>
            <p class="text-3xl font-black text-slate-800"><?= $stats['ventas_totales'] ?></p>
        </div>

        <!-- Lotes -->
        <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-boxes-stacked text-6xl text-amber-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl mb-4 shadow-sm border border-amber-100">
                <i class="fas fa-industry"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-[11px] uppercase tracking-wider mb-1">Lotes Procesados</h3>
            <p class="text-3xl font-black text-slate-800"><?= $stats['lotes_activos'] ?></p>
        </div>

        <!-- Productos -->
        <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-cubes text-6xl text-sky-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl mb-4 shadow-sm border border-sky-100">
                <i class="fas fa-bottle-water"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-[11px] uppercase tracking-wider mb-1">Productos Catálogo</h3>
            <p class="text-3xl font-black text-slate-800"><?= $stats['productos_registrados'] ?></p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Gráfico Ventas por Estado -->
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
            <h3 class="text-lg font-black text-slate-800 mb-6 outfit-font flex items-center gap-2">
                <i class="fas fa-chart-pie text-indigo-500"></i> Estados de Pedidos
            </h3>
            <div class="relative h-64 flex items-center justify-center">
                <?php if (empty($ventasPorEstado)): ?>
                    <p class="text-slate-400 font-medium">No hay datos suficientes.</p>
                <?php else: ?>
                    <canvas id="chartEstados"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico Productos Top -->
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
            <h3 class="text-lg font-black text-slate-800 mb-6 outfit-font flex items-center gap-2">
                <i class="fas fa-trophy text-amber-500"></i> Top Productos Vendidos
            </h3>
            <div class="relative h-64 flex items-center justify-center">
                <?php if (empty($topProductos)): ?>
                    <p class="text-slate-400 font-medium">No hay datos suficientes.</p>
                <?php else: ?>
                    <canvas id="chartTopProductos"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Preparar datos para los gráficos
$labelsEstados = [];
$dataEstados = [];
foreach ($ventasPorEstado as $v) {
    $labelsEstados[] = $v['estado'];
    $dataEstados[] = $v['cantidad'];
}

$labelsTop = [];
$dataTop = [];
foreach ($topProductos as $p) {
    $labelsTop[] = $p['nombre'];
    $dataTop[] = $p['total_vendido'];
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Configuración general Chart.js
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
    Chart.defaults.plugins.tooltip.titleFont = { size: 14, weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 13 };
    Chart.defaults.plugins.tooltip.cornerRadius = 8;

    <?php if (!empty($ventasPorEstado)): ?>
    const ctxEstados = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctxEstados, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labelsEstados) ?>,
            datasets: [{
                data: <?= json_encode($dataEstados) ?>,
                backgroundColor: [
                    '#3b82f6', // blue
                    '#10b981', // emerald
                    '#f59e0b', // amber
                    '#ef4444', // red
                    '#8b5cf6'  // violet
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });
    <?php endif; ?>

    <?php if (!empty($topProductos)): ?>
    const ctxTop = document.getElementById('chartTopProductos').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labelsTop) ?>,
            datasets: [{
                label: 'Unidades Vendidas',
                data: <?= json_encode($dataTop) ?>,
                backgroundColor: '#0ea5e9',
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false } },
                x: { grid: { display: false, drawBorder: false } }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
