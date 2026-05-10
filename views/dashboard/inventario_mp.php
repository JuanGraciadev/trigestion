<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/InventarioMP.php';

$database = new Database();
$db       = $database->conectar();
$invModel = new InventarioMP($db);

$inventario = $invModel->obtenerTodos();
$stats      = $invModel->obtenerEstadisticas();
$egresos    = $invModel->obtenerEgresos();

// Datos para gráficas
$nombres_tipo    = array_map(fn($r) => $r['tipo_envase'] ?: 'Sin Tipo', $stats['por_tipo']);
$cantidades_tipo = array_map(fn($r) => (int)$r['total'], $stats['por_tipo']);

// Flujo: construir conjunto unificado de fechas (ingresos + egresos)
$dias_ingreso = [];
foreach ($stats['por_dia'] as $r)    $dias_ingreso[$r['dia']] = (int)$r['total'];
$dias_egreso  = [];
foreach ($stats['egresos_dia'] as $r) $dias_egreso[$r['dia']] = (int)$r['total'];
$all_dias = array_unique(array_merge(array_keys($dias_ingreso), array_keys($dias_egreso)));
sort($all_dias);
$flujo_labels  = array_map(fn($d) => date('d/m', strtotime($d)), $all_dias);
$flujo_ingreso = array_map(fn($d) => $dias_ingreso[$d] ?? 0, $all_dias);
$flujo_egreso  = array_map(fn($d) => $dias_egreso[$d]  ?? 0, $all_dias);

$titulo = "Inventario de Materia Prima";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-8">

  <!-- ── HEADER ── -->
  <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div class="absolute right-0 top-0 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl -mr-24 -mt-24 pointer-events-none"></div>
    <div class="relative z-10">
      <h2 class="text-3xl font-black text-slate-800">Inventario <span class="text-emerald-600">Materia Prima</span></h2>
      <p class="text-slate-500 mt-1">Monitorea stock, ingresos y consumo en producción.</p>
    </div>
    <div class="flex flex-wrap items-center gap-4 relative z-10">
      <!-- Stock actual -->
      <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-6 py-4">
        <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-lg shadow-md shadow-emerald-200">
          <i class="fas fa-cubes"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Stock Actual</p>
          <p class="text-2xl font-black text-emerald-800"><?= number_format((float)$stats['total']) ?> <span class="text-sm font-semibold">uds</span></p>
        </div>
      </div>
      <!-- Total consumido -->
      <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-2xl px-6 py-4">
        <div class="w-11 h-11 bg-red-500 rounded-xl flex items-center justify-center text-white text-lg shadow-md shadow-red-200">
          <i class="fas fa-industry"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold text-red-600 uppercase tracking-widest">Consumido en Producción</p>
          <p class="text-2xl font-black text-red-800"><?= number_format((float)$stats['total_egreso']) ?> <span class="text-sm font-semibold">uds</span></p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── TARJETAS RESUMEN ── -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex items-center gap-5">
      <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white text-2xl shadow-md shadow-emerald-200">
        <i class="fas fa-arrow-down"></i>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Ingresado (histórico)</p>
        <p class="text-3xl font-black text-slate-800"><?= number_format((float)$stats['total'] + (float)$stats['total_egreso']) ?></p>
        <p class="text-xs text-slate-400 mt-0.5">unidades registradas</p>
      </div>
    </div>
    <div class="bg-white rounded-3xl border border-red-100 shadow-sm p-6 flex items-center gap-5">
      <div class="w-14 h-14 rounded-2xl bg-red-500 flex items-center justify-center text-white text-2xl shadow-md shadow-red-200">
        <i class="fas fa-fire"></i>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Salida a Producción</p>
        <p class="text-3xl font-black text-red-600"><?= number_format((float)$stats['total_egreso']) ?></p>
        <p class="text-xs text-slate-400 mt-0.5">unidades consumidas</p>
      </div>
    </div>
    <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm p-6 flex items-center gap-5">
      <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white text-2xl shadow-md shadow-emerald-200">
        <i class="fas fa-boxes-stacked"></i>
      </div>
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En Bodega Ahora</p>
        <p class="text-3xl font-black text-emerald-600"><?= number_format((float)$stats['total']) ?></p>
        <p class="text-xs text-slate-400 mt-0.5">stock disponible</p>
      </div>
    </div>
  </div>

  <!-- ── GRÁFICAS ── -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <!-- Flujo Ingreso vs Egreso -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 lg:col-span-2">
      <h3 class="text-lg font-bold text-slate-800 mb-1 flex items-center gap-2">
        <i class="fas fa-chart-bar text-sky-500"></i> Flujo de Materia Prima — Ingresos vs. Consumo en Producción
      </h3>
      <p class="text-xs text-slate-400 mb-6">Comparación por día de lo que entró y lo que fue usado en producción.</p>
      <div class="relative h-72">
        <canvas id="chartFlujo"></canvas>
      </div>
    </div>

    <!-- Distribución por tipo de envase -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
      <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
        <i class="fas fa-chart-pie text-emerald-500"></i> Distribución por Tipo de Envase
      </h3>
      <div class="relative h-64 w-full flex items-center justify-center">
        <canvas id="chartTipos"></canvas>
      </div>
    </div>

    <!-- Ingresos últimos 7 días -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
      <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
        <i class="fas fa-chart-line text-emerald-500"></i> Ingresos al Inventario (últimos 7 días)
      </h3>
      <div class="relative h-64 w-full">
        <canvas id="chartDias"></canvas>
      </div>
    </div>
  </div>

  <!-- ── TABLA EGRESOS A PRODUCCIÓN ── -->
  <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
          <i class="fas fa-arrow-up-from-bracket text-red-500"></i>
          Consumo de MP en <span class="text-red-600 ml-1">Producción</span>
        </h3>
        <p class="text-slate-500 text-sm mt-1">Detalle de cada producción finalizada que descontó materia prima.</p>
      </div>
      <span class="px-4 py-2 bg-red-100 text-red-700 text-sm font-bold rounded-full"><?= count($egresos) ?> registros</span>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
      <table class="w-full text-left">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold tracking-widest sticky top-0 z-10 shadow-sm">
          <tr>
            <th class="px-8 py-5">Fecha Egreso</th>
            <th class="px-8 py-5">Lote Producción</th>
            <th class="px-8 py-5">Producto Fabricado</th>
            <th class="px-8 py-5">Material Usado</th>
            <th class="px-8 py-5">Consumido</th>
            <th class="px-8 py-5">Responsable</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($egresos)): ?>
            <tr>
              <td colspan="6" class="px-8 py-16 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300 text-2xl"><i class="fas fa-box-open"></i></div>
                <p class="text-slate-400 font-semibold">Aún no hay consumos registrados.</p>
                <p class="text-slate-400 text-sm mt-1">Finaliza una producción con materia prima vinculada para verlos aquí.</p>
              </td>
            </tr>
          <?php else: foreach ($egresos as $e): ?>
            <tr class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-8 py-5 text-slate-600 text-sm">
                <?= $e['fecha_egreso'] ? date('d/m/Y H:i', strtotime($e['fecha_egreso'])) : '—' ?>
              </td>
              <td class="px-8 py-5">
                <span class="px-3 py-1 bg-sky-100 text-sky-700 rounded-lg text-xs font-bold">
                  <?= htmlspecialchars($e['lote_produccion']) ?>
                </span>
              </td>
              <td class="px-8 py-5 font-semibold text-slate-800">
                <?= htmlspecialchars($e['producto_nombre'] ?? '—') ?>
              </td>
              <td class="px-8 py-5">
                <div class="font-bold text-slate-700"><?= htmlspecialchars($e['tipo_envase'] ?? '—') ?></div>
                <div class="text-xs text-slate-400"><?= htmlspecialchars($e['capacidad'] ?? '') ?></div>
              </td>
              <td class="px-8 py-5">
                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 font-black px-4 py-1.5 rounded-full text-base shadow-sm">
                  <i class="fas fa-minus text-xs"></i><?= number_format($e['cantidad_consumida']) ?>
                </span>
              </td>
              <td class="px-8 py-5 text-slate-600"><?= htmlspecialchars($e['usuario_nombre'] ?? '—') ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── TABLA HISTÓRICO INGRESOS ── -->
  <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex items-center gap-3">
      <i class="fas fa-list-ul text-emerald-500"></i>
      <h3 class="text-lg font-bold text-slate-800">Historial de Ingresos</h3>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
      <table class="w-full text-left">
        <thead class="bg-slate-50/50 text-slate-500 uppercase text-xs font-bold tracking-widest sticky top-0 z-10 shadow-sm">
          <tr>
            <th class="px-8 py-5">Fecha / Lote</th>
            <th class="px-8 py-5">Ingreso</th>
            <th class="px-8 py-5">Stock Actual</th>
            <th class="px-8 py-5">Detalle Envase</th>
            <th class="px-8 py-5">Bodega</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($inventario as $inv): ?>
          <tr class="hover:bg-slate-50/50 transition-colors">
            <td class="px-8 py-6">
              <div class="font-bold text-slate-800"><?= date('d/m/Y H:i', strtotime($inv['fecha'])) ?></div>
              <div class="text-sm font-semibold text-emerald-600 mt-1">Lote: <?= htmlspecialchars($inv['codigo_lote'] ?? 'N/A') ?></div>
            </td>
            <td class="px-8 py-6">
              <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 font-black px-4 py-1.5 rounded-full shadow-sm text-base">
                <i class="fas fa-plus text-xs"></i><?= htmlspecialchars($inv['ingreso']) ?>
              </span>
            </td>
            <td class="px-8 py-6">
              <span class="inline-flex items-center gap-1.5 bg-sky-100 text-sky-700 font-black px-4 py-1.5 rounded-full shadow-sm text-base">
                <i class="fas fa-layer-group text-xs"></i><?= htmlspecialchars($inv['ingreso']) ?>
              </span>
            </td>
            <td class="px-8 py-6">
              <div class="font-bold text-slate-700"><?= htmlspecialchars($inv['tipo_envase'] ?? '-') ?></div>
              <div class="text-xs text-slate-400 font-medium">Cap: <?= htmlspecialchars($inv['capacidad'] ?? '-') ?> | Prov: <?= htmlspecialchars($inv['proveedor'] ?? '-') ?></div>
            </td>
            <td class="px-8 py-6">
              <span class="text-slate-600 font-bold flex items-center gap-2">
                <i class="fas fa-warehouse text-slate-400"></i> <?= htmlspecialchars($inv['bodega']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>

          <?php if (empty($inventario)): ?>
          <tr>
            <td colspan="5" class="px-8 py-20 text-center">
              <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-folder-open text-3xl"></i>
              </div>
              <p class="text-slate-500 font-medium text-lg">No hay registros de inventario.</p>
              <p class="text-slate-400 mt-2">Los ingresos aparecerán cuando agregues detalles a un Lote.</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Inter', sans-serif";

    const premiumTooltip = {
      backgroundColor: 'rgba(255, 255, 255, 0.98)',
      titleColor: '#1e293b',
      bodyColor: '#475569',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: 16,
      boxPadding: 8,
      usePointStyle: true,
      titleFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
      bodyFont: { size: 13, family: "'Inter', sans-serif", weight: '500' },
      cornerRadius: 16,
      boxWidth: 10,
      boxHeight: 10,
      titleSpacing: 8,
      displayColors: true
    };

    // ── Gráfica Flujo: Ingresos vs Egresos (barras agrupadas) ──
    const ctxFlujo = document.getElementById('chartFlujo').getContext('2d');
    const gradIngreso = ctxFlujo.createLinearGradient(0, 0, 0, 300);
    gradIngreso.addColorStop(0, 'rgba(16, 185, 129, 0.9)');
    gradIngreso.addColorStop(1, 'rgba(16, 185, 129, 0.1)');
    
    const gradEgreso = ctxFlujo.createLinearGradient(0, 0, 0, 300);
    gradEgreso.addColorStop(0, 'rgba(239, 68, 68, 0.9)');
    gradEgreso.addColorStop(1, 'rgba(239, 68, 68, 0.1)');

    new Chart(ctxFlujo, {
        type: 'bar',
        data: {
            labels: <?= json_encode($flujo_labels) ?>,
            datasets: [
                {
                    label: 'Ingreso MP',
                    data: <?= json_encode($flujo_ingreso) ?>,
                    backgroundColor: gradIngreso,
                    borderColor: '#10b981',
                    borderWidth: { top: 2, right: 2, left: 2, bottom: 0 },
                    borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Consumo en Producción',
                    data: <?= json_encode($flujo_egreso) ?>,
                    backgroundColor: gradEgreso,
                    borderColor: '#ef4444',
                    borderWidth: { top: 2, right: 2, left: 2, bottom: 0 },
                    borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { y: { duration: 1500, easing: 'easeOutQuart' } },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { weight: '600' }, color: '#475569', padding: 20, usePointStyle: true, pointStyle: 'circle' }
                },
                tooltip: {
                    ...premiumTooltip,
                    callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} uds` }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f8fafc', borderDash: [5, 5], drawBorder: false },
                    ticks: { color: '#94a3b8', font: { weight: '600' }, padding: 12 },
                    border: { display: false }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#64748b', font: { weight: '600' }, padding: 10 },
                    border: { display: false }
                }
            }
        }
    });

    // ── Dona: Tipos de Envase ──
    new Chart(document.getElementById('chartTipos'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($nombres_tipo) ?>,
            datasets: [{
                data: <?= json_encode($cantidades_tipo) ?>,
                backgroundColor: ['#ef4444','#3b82f6','#f59e0b','#10b981','#8b5cf6','#ec4899','#06b6d4'],
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 12,
                borderRadius: 10,
                spacing: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1500,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { position: 'right', labels: { font: { weight: '600' }, color: '#475569', padding: 20, usePointStyle: true, pointStyle: 'circle' } },
                tooltip: premiumTooltip
            }
        }
    });

    // ── Barras: Ingresos por día ──
    const ctxDias = document.getElementById('chartDias').getContext('2d');
    const gradDias = ctxDias.createLinearGradient(0, 0, 0, 300);
    gradDias.addColorStop(0, 'rgba(59, 130, 246, 0.9)');
    gradDias.addColorStop(1, 'rgba(59, 130, 246, 0.1)');

    new Chart(ctxDias, {
        type: 'bar',
        data: {
            labels: <?= json_encode($flujo_labels) ?>,
            datasets: [{
                label: 'Unidades Ingresadas',
                data: <?= json_encode($flujo_ingreso) ?>,
                backgroundColor: gradDias,
                borderColor: '#3b82f6',
                borderWidth: { top: 2, right: 2, left: 2, bottom: 0 },
                borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                borderSkipped: false,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { y: { duration: 1500, easing: 'easeOutQuart' } },
            plugins: { legend: { display: false }, tooltip: premiumTooltip },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f8fafc', drawBorder: false }, ticks: { color: '#94a3b8', font: { weight: '600' }, padding: 12 }, border: { display: false } },
                x: { grid: { display: false, drawBorder: false }, ticks: { color: '#64748b', font: { weight: '600' }, padding: 10 }, border: { display: false } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
