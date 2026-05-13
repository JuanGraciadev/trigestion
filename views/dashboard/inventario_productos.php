<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/InventarioProductos.php';

$database = new Database();
$db       = $database->conectar();
$invModel = new InventarioProductos($db);

$registros  = $invModel->obtenerTodos();
$stock      = $invModel->obtenerStockPorProducto();
$stats      = $invModel->obtenerEstadisticas();
$salidas    = $invModel->obtenerSalidas();

$titulo = "Inventario de Productos";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>



<div class="space-y-10 animate-fade-in-up outfit-font pb-12">

    <!-- ── HEADER ── -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
      <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="p-3 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl text-white shadow-lg shadow-emerald-200">
                <i class="fas fa-boxes-stacked text-2xl"></i>
            </div>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-800 tracking-tight">Centro de <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500">Inventario</span></h2>
        </div>
        <p class="text-slate-500 font-medium text-lg ml-2">Stock actualizado automáticamente tras cada producción.</p>
      </div>
      <div class="flex items-center gap-3 text-sm bg-white/80 backdrop-blur-md border border-emerald-100 text-emerald-700 font-bold px-6 py-4 rounded-[1.25rem] premium-shadow">
        <i class="fas fa-satellite-dish animate-pulse"></i> Sincronizado en tiempo real
      </div>
    </div>

    <!-- Alertas -->
    <?php if (isset($_SESSION['alert'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded',function(){
        Swal.fire({
          icon:'<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
          title:'<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
          text:'<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
          confirmButtonColor:'#10b981',
          confirmButtonText:'Entendido',
          customClass:{popup:'rounded-[2rem] outfit-font', confirmButton:'rounded-xl px-6 py-3 font-bold'}
        });
      });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>

    <?php 
    $productos_bajos = [];
    foreach ($stock as $s) {
        if ((int)$s['total_unidades'] <= 10) {
            $productos_bajos[] = $s['producto_nombre'];
        }
    }
    if (!empty($productos_bajos)): 
    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'warning',
          title: 'Stock Bajo Detectado',
          html: 'Los siguientes productos tienen pocas unidades:<br><b><?= htmlspecialchars(implode(", ", $productos_bajos)) ?></b>',
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true,
          customClass: { popup: 'rounded-[1.5rem] shadow-xl border border-amber-100 bg-amber-50' }
        });
      });
    </script>
    <?php endif; ?>

    <!-- ── STAT CARDS ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $cards = [
        ['icon'=>'fa-boxes-stacked','label'=>'Total en Stock','value'=> number_format($stats['total_unidades']),'sub'=>'unidades disponibles','grad'=>'emerald-gradient','text'=>'text-emerald-500'],
        ['icon'=>'fa-bottle-water','label'=>'Productos Diferentes','value'=> $stats['num_productos'],'sub'=>'en catálogo','grad'=>'water-gradient','text'=>'text-sky-500'],
        ['icon'=>'fa-layer-group','label'=>'Lotes Registrados','value'=> count($registros),'sub'=>'ingresos históricos','grad'=>'violet-gradient','text'=>'text-violet-500'],
        ['icon'=>'fa-chart-line','label'=>'Último Ingreso','value'=> !empty($registros) ? date('d M',strtotime($registros[0]['fecha'])) : '—','sub'=>'fecha reciente','grad'=>'amber-gradient','text'=>'text-amber-500'],
      ];
      foreach($cards as $c): ?>
      <div class="stat-card glass-card rounded-[2rem] p-7 border border-slate-100 premium-shadow relative group overflow-hidden">
        <div class="absolute -right-6 -top-6 w-32 h-32 opacity-10 rounded-full blur-[30px] <?= $c['grad'] ?> group-hover:opacity-30 group-hover:scale-125 transition-all duration-700"></div>
        <div class="flex items-start justify-between mb-4 relative z-10">
          <div class="w-14 h-14 rounded-[1.25rem] <?= $c['grad'] ?> flex items-center justify-center text-white text-2xl shadow-lg transform group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
            <i class="fas <?= $c['icon'] ?>"></i>
          </div>
          <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100"><?= $c['label'] ?></span>
        </div>
        <div class="relative z-10">
            <p class="text-4xl font-black <?= $c['text'] ?> tracking-tight mb-1"><?= $c['value'] ?></p>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider"><?= $c['sub'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── CHARTS ── -->
    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Producción por día -->
      <div class="glass-card rounded-[2.5rem] border border-slate-100 premium-shadow p-8 transition-all duration-500 hover:shadow-[0_20px_50px_-12px_rgba(16,185,129,0.15)] group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[40px] -mr-20 -mt-20 group-hover:bg-emerald-500/15 transition-colors duration-700 pointer-events-none"></div>
        <h3 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-3 relative z-10">
          <div class="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm"><i class="fas fa-calendar-days text-xl"></i></div>
          Ingresos Recientes
        </h3>
        <div class="relative h-[300px] w-full">
          <canvas id="chartDias"></canvas>
        </div>
      </div>
      <!-- Top productos -->
      <div class="glass-card rounded-[2.5rem] border border-slate-100 premium-shadow p-8 transition-all duration-500 hover:shadow-[0_20px_50px_-12px_rgba(245,158,11,0.15)] group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-[40px] -mr-20 -mt-20 group-hover:bg-amber-500/15 transition-colors duration-700 pointer-events-none"></div>
        <h3 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-3 relative z-10">
          <div class="w-12 h-12 bg-amber-50 border border-amber-100 rounded-2xl flex items-center justify-center text-amber-600 shadow-sm"><i class="fas fa-trophy text-xl"></i></div>
          Distribución de Stock
        </h3>
        <div class="relative h-[300px] flex justify-center w-full">
          <canvas id="chartTop"></canvas>
        </div>
      </div>
    </div>

    <!-- ── STOCK POR PRODUCTO ── -->
    <div class="glass-card rounded-[2.5rem] border border-slate-100 premium-shadow overflow-hidden">
      <div class="p-8 border-b border-slate-100/50 bg-gradient-to-r from-slate-50/50 to-white">
        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Resumen por <span class="text-emerald-600">Producto</span></h3>
        <p class="text-slate-500 text-sm mt-2 font-medium">Consolidado general de tu bodega en tiempo real.</p>
      </div>
      <div class="p-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 bg-slate-50/30">
        <?php if (empty($stock)): ?>
          <div class="col-span-full p-20 text-center">
            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl premium-shadow border border-slate-100"><i class="fas fa-box-open"></i></div>
            <p class="text-slate-500 font-bold text-lg">Sin stock registrado aún. Finaliza una producción para ver resultados.</p>
          </div>
        <?php else: foreach($stock as $s): ?>
          <?php
            $img = !empty($s['producto_img']) ? '../../img/'.$s['producto_img'] : null;
            $initials = strtoupper(substr($s['producto_nombre'],0,2));
          ?>
          <div class="glass-card rounded-[2rem] border border-slate-100 premium-shadow p-6 flex flex-col gap-5 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 relative overflow-hidden group bg-white">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            
            <div class="flex items-center gap-5 relative z-10">
              <?php if($img): ?>
                <img src="<?= htmlspecialchars($img) ?>" class="w-16 h-16 rounded-[1.25rem] object-cover shadow-md border border-slate-100" alt="">
              <?php else: ?>
                <div class="w-16 h-16 rounded-[1.25rem] emerald-gradient flex items-center justify-center text-white font-black text-2xl shadow-md"><?= $initials ?></div>
              <?php endif; ?>
              <div>
                <p class="font-black text-lg text-slate-800 leading-tight mb-1 group-hover:text-emerald-600 transition-colors"><?= htmlspecialchars($s['producto_nombre']) ?></p>
                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest bg-slate-50 px-2.5 py-1 rounded-md inline-block border border-slate-100"><?= htmlspecialchars($s['categoria_nombre'] ?? 'Sin categoría') ?></p>
              </div>
            </div>
            
            <div class="grid grid-cols-4 gap-3 text-center relative z-10">
              <div class="bg-violet-50/80 rounded-2xl p-2 border border-violet-100/50 hover:bg-violet-100 transition-colors">
                <p class="text-xl font-black text-violet-600"><?= number_format($s['total_ingresado']) ?></p>
                <p class="text-[9px] font-bold text-violet-500 uppercase mt-1">Producido</p>
              </div>
              <div class="bg-amber-50/80 rounded-2xl p-2 border border-amber-100/50 hover:bg-amber-100 transition-colors">
                <p class="text-xl font-black text-amber-600"><?= number_format($s['total_vendido']) ?></p>
                <p class="text-[9px] font-bold text-amber-500 uppercase mt-1">Vendido</p>
              </div>
              <div class="bg-emerald-50 rounded-2xl p-2 shadow-inner border border-emerald-200/60 transform scale-105">
                <p class="text-xl font-black text-emerald-600"><?= number_format($s['total_unidades']) ?></p>
                <p class="text-[9px] font-bold text-emerald-600 uppercase mt-1">Stock</p>
              </div>
              <div class="bg-sky-50/80 rounded-2xl p-2 border border-sky-100/50 hover:bg-sky-100 transition-colors">
                <p class="text-xl font-black text-sky-600"><?= $s['num_lotes'] ?></p>
                <p class="text-[9px] font-bold text-sky-500 uppercase mt-1">Lotes</p>
              </div>
            </div>
            <div class="pt-2 mt-auto border-t border-slate-50 flex justify-between items-center relative z-10">
               <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Último Ingreso</span>
               <span class="text-xs font-bold text-slate-500"><?= date('d/m/Y H:i', strtotime($s['ultima_entrada'])) ?></span>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- ── TABLA DETALLADA INGRESO ── -->
      <div class="glass-card rounded-[2.5rem] border border-slate-100 premium-shadow overflow-hidden flex flex-col">
        <div class="p-8 border-b border-slate-100/50 bg-gradient-to-r from-violet-50/50 to-white flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Historial de <span class="text-violet-600">Ingresos</span></h3>
            <p class="text-slate-500 text-sm mt-1 font-medium">Producciones finalizadas.</p>
          </div>
          <span class="px-4 py-2 bg-violet-100 text-violet-700 text-sm font-bold rounded-xl border border-violet-200"><?= count($registros) ?> registros</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar flex-1 p-2">
          <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[10px] font-black tracking-widest sticky top-0 z-10 backdrop-blur-md">
              <tr>
                <th class="px-6 py-4 rounded-tl-xl rounded-bl-xl">Producto</th>
                <th class="px-6 py-4">Lote</th>
                <th class="px-6 py-4">Cantidad</th>
                <th class="px-6 py-4">Bodega</th>
                <th class="px-6 py-4 rounded-tr-xl rounded-br-xl text-center">Act</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <?php if (empty($registros)): ?>
                <tr><td colspan="5" class="p-16 text-center text-slate-400 font-bold">No hay ingresos aún.</td></tr>
              <?php else: foreach($registros as $r): ?>
              <tr class="hover:bg-violet-50/30 transition-colors group">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($r['producto_nombre'] ?? '—') ?></div>
                  <div class="text-[10px] text-slate-400 uppercase font-bold"><?= date('d/m/Y', strtotime($r['fecha'])) ?></div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-black border border-slate-200"><?= htmlspecialchars($r['lote_produccion'] ?? '—') ?></span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-lg font-black text-emerald-600">+<?= number_format($r['cantidad']) ?></span>
                </td>
                <td class="px-6 py-4">
                  <span class="flex items-center gap-1.5 text-slate-600 font-bold text-xs bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                    <i class="fas fa-warehouse text-slate-400"></i>
                    <?= htmlspecialchars($r['bodega']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <button onclick="openEditBodega(<?= $r['id_inventario'] ?>, '<?= htmlspecialchars($r['bodega']) ?>')"
                    class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-violet-600 hover:border-violet-300 hover:bg-violet-50 hover:shadow-md transition-all shadow-sm" title="Editar bodega">
                    <i class="fas fa-pen text-xs"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── TABLA DETALLADA DE SALIDAS ── -->
      <div class="glass-card rounded-[2.5rem] border border-slate-100 premium-shadow overflow-hidden flex flex-col">
        <div class="p-8 border-b border-slate-100/50 bg-gradient-to-r from-amber-50/50 to-white flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Historial de <span class="text-amber-600">Salidas</span></h3>
            <p class="text-slate-500 text-sm mt-1 font-medium">Despachos por ventas.</p>
          </div>
          <span class="px-4 py-2 bg-amber-100 text-amber-700 text-sm font-bold rounded-xl border border-amber-200"><?= count($salidas) ?> ventas</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar flex-1 p-2">
          <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[10px] font-black tracking-widest sticky top-0 z-10 backdrop-blur-md">
              <tr>
                <th class="px-6 py-4 rounded-tl-xl rounded-bl-xl">Producto</th>
                <th class="px-6 py-4">Cliente</th>
                <th class="px-6 py-4">Cantidad</th>
                <th class="px-6 py-4 rounded-tr-xl rounded-br-xl">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <?php if (empty($salidas)): ?>
                <tr><td colspan="4" class="p-16 text-center text-slate-400 font-bold">No hay salidas registradas aún.</td></tr>
              <?php else: foreach($salidas as $s): ?>
              <tr class="hover:bg-amber-50/30 transition-colors group">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($s['producto_nombre'] ?? '—') ?></div>
                  <div class="text-[10px] text-slate-400 uppercase font-bold"><?= date('d/m/Y', strtotime($s['fecha'])) ?></div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-bold text-slate-600 text-xs"><?= htmlspecialchars($s['cliente_nombre'] ?? '—') ?></div>
                    <div class="text-[10px] text-slate-400">Venta #<?= $s['id_venta'] ?></div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-lg font-black text-amber-500">-<?= number_format($s['cantidad']) ?></span>
                </td>
                <td class="px-6 py-4">
                  <?php
                    $est = $s['estado'];
                    $cls = $est === 'Entregado' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : ($est === 'En Proceso' ? 'bg-blue-50 text-blue-600 border-blue-200' : ($est === 'Pendiente' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-slate-50 text-slate-600 border-slate-200'));
                  ?>
                  <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border <?= $cls ?> shadow-sm"><?= htmlspecialchars($est) ?></span>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<!-- Modal editar bodega -->
<div id="modalBodega" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
  <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-slate-800">Actualizar Bodega</h3>
        <p class="text-sm text-slate-500">Cambia la ubicación de este registro.</p>
      </div>
      <button onclick="closeModal('modalBodega')" class="w-9 h-9 rounded-full bg-slate-100 text-slate-400 hover:text-red-500 hover:rotate-90 transition-all">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form action="../../controllers/InventarioProductosController.php?accion=actualizarBodega" method="POST" class="p-6 space-y-4">
      <input type="hidden" name="id_inventario" id="edit_id_inv">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase">Nombre de Bodega</label>
        <input type="text" name="bodega" id="edit_bodega" required
          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 outline-none transition-all text-slate-700">
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal('modalBodega')" class="px-6 py-3 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
        <button type="submit" class="violet-gradient text-white px-8 py-3 rounded-2xl font-bold shadow-lg transition-all active:scale-95"
          style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── Datos PHP → JS ──
const dayLabels  = <?= json_encode(array_column($stats['por_dia'], 'dia')) ?>;
const dayData    = <?= json_encode(array_map('intval', array_column($stats['por_dia'], 'total'))) ?>;
const topLabels  = <?= json_encode(array_column($stats['top_productos'], 'nombre')) ?>;
const topData    = <?= json_encode(array_map('intval', array_column($stats['top_productos'], 'total'))) ?>;

// Chart defaults
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#64748b';

// Tooltip global config
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

// ── Ingresos por día (Bar Chart) ──
const ctxDias = document.getElementById('chartDias').getContext('2d');
const gradientDias = ctxDias.createLinearGradient(0, 0, 0, 300);
gradientDias.addColorStop(0, 'rgba(16, 185, 129, 0.9)');
gradientDias.addColorStop(1, 'rgba(16, 185, 129, 0.1)');

new Chart(ctxDias, {
  type: 'bar',
  data: {
    labels: dayLabels,
    datasets: [{
      label: 'Unidades ingresadas',
      data: dayData,
      backgroundColor: gradientDias,
      borderColor: '#10b981',
      borderWidth: { top: 2, right: 2, left: 2, bottom: 0 },
      borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
      borderSkipped: false,
      barPercentage: 0.5,
      hoverBackgroundColor: '#059669'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: { y: { duration: 1500, easing: 'easeOutQuart' } },
    plugins: { 
      legend: { display: false },
      tooltip: premiumTooltip
    },
    scales: {
      y: { 
        beginAtZero: true, 
        grid: { color: '#f8fafc', borderDash: [5, 5], drawBorder: false },
        ticks: { padding: 12, font: { weight: '600' } },
        border: { display: false }
      },
      x: { 
        grid: { display: false, drawBorder: false },
        ticks: { padding: 10, font: { weight: '600' } },
        border: { display: false }
      }
    }
  }
});

// ── Top productos (Doughnut) ──
const ctxTop = document.getElementById('chartTop').getContext('2d');
const doughnutColors = ['#0ea5e9','#10b981','#8b5cf6','#f59e0b','#f43f5e'];

new Chart(ctxTop, {
  type: 'doughnut',
  data: {
    labels: topLabels,
    datasets: [{
      data: topData,
      backgroundColor: doughnutColors,
      hoverOffset: 12,
      borderWidth: 4,
      borderColor: '#ffffff',
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
      legend: { 
        position: 'bottom', 
        labels: { padding: 24, font: { size: 13, weight: '600' }, usePointStyle: true, pointStyle: 'circle' }
      },
      tooltip: premiumTooltip
    }
  }
});

// Modals
function openModal(id) {
  const m = document.getElementById(id);
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeModal(id) {
  const m = document.getElementById(id);
  m.classList.add('hidden');
  m.classList.remove('flex');
}
function openEditBodega(id, bodega) {
  document.getElementById('edit_id_inv').value = id;
  document.getElementById('edit_bodega').value  = bodega;
  openModal('modalBodega');
}
window.onclick = function(e) {
  if (e.target.classList.contains('bg-slate-900/60')) {
    e.target.classList.add('hidden');
    e.target.classList.remove('flex');
  }
};
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
