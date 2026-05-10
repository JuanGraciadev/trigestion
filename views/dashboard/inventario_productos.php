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



<div class="space-y-8">

  <!-- ── HEADER ── -->
  <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden relative">
    <div class="absolute right-0 top-0 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl -mr-24 -mt-24"></div>
    <div class="p-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-bold text-slate-800">Inventario de <span class="text-emerald-600">Productos</span></h2>
        <p class="text-slate-500 mt-1">Stock actualizado automáticamente al finalizar cada producción.</p>
      </div>
      <div class="flex items-center gap-3 text-sm bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold px-5 py-3 rounded-2xl">
        <i class="fas fa-circle-check"></i> Se actualiza al finalizar producción
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['alert'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      Swal.fire({
        icon:'<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
        title:'<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
        text:'<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
        confirmButtonColor:'#10b981',
        confirmButtonText:'Entendido',
        customClass:{popup:'rounded-[2rem]'}
      });
    });
  </script>
  <?php unset($_SESSION['alert']); endif; ?>

  <!-- ── STAT CARDS ── -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php
    $cards = [
      ['icon'=>'fa-boxes-stacked','label'=>'Total Unidades en Stock','value'=> number_format($stats['total_unidades']),'sub'=>'unidades totales','grad'=>'emerald-gradient','text'=>'text-emerald-500'],
      ['icon'=>'fa-bottle-water','label'=>'Productos Diferentes','value'=> $stats['num_productos'],'sub'=>'en bodega','grad'=>'water-gradient','text'=>'text-sky-500'],
      ['icon'=>'fa-layer-group','label'=>'Lotes Registrados','value'=> count($registros),'sub'=>'ingresos totales','grad'=>'violet-gradient','text'=>'text-violet-500'],
      ['icon'=>'fa-chart-line','label'=>'Último Ingreso','value'=> !empty($registros) ? date('d M',strtotime($registros[0]['fecha'])) : '—','sub'=>'fecha reciente','grad'=>'amber-gradient','text'=>'text-amber-500'],
    ];
    foreach($cards as $c): ?>
    <div class="stat-card bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
      <div class="flex items-start justify-between mb-4">
        <div class="w-12 h-12 rounded-2xl <?= $c['grad'] ?> flex items-center justify-center text-white text-xl shadow-md">
          <i class="fas <?= $c['icon'] ?>"></i>
        </div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= $c['label'] ?></span>
      </div>
      <p class="text-4xl font-black <?= $c['text'] ?>"><?= $c['value'] ?></p>
      <p class="text-xs text-slate-400 mt-1 font-semibold uppercase tracking-wide"><?= $c['sub'] ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── CHARTS ── -->
  <div class="grid lg:grid-cols-2 gap-8">
    <!-- Producción por día -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8 transition-all duration-300 hover:shadow-xl">
      <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
        <div class="p-2 bg-emerald-100 rounded-lg"><i class="fas fa-calendar-days text-emerald-600"></i></div>
        Ingresos al Inventario (últimos días)
      </h3>
      <div class="relative h-72">
        <canvas id="chartDias"></canvas>
      </div>
    </div>
    <!-- Top productos -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8 transition-all duration-300 hover:shadow-xl">
      <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
        <div class="p-2 bg-amber-100 rounded-lg"><i class="fas fa-trophy text-amber-600"></i></div>
        Top Productos en Stock
      </h3>
      <div class="relative h-72 flex justify-center">
        <canvas id="chartTop"></canvas>
      </div>
    </div>
  </div>

  <!-- ── STOCK POR PRODUCTO ── -->
  <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 border-b border-slate-100">
      <h3 class="text-2xl font-bold text-slate-800">Resumen de <span class="text-emerald-600">Stock</span> por Producto</h3>
      <p class="text-slate-500 text-sm mt-1">Consolidado de todas las producciones finalizadas.</p>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <?php if (empty($stock)): ?>
        <div class="col-span-full p-16 text-center">
          <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl"><i class="fas fa-box-open"></i></div>
          <p class="text-slate-400 font-semibold">Sin stock registrado aún. Finaliza una producción para ver resultados.</p>
        </div>
      <?php else: foreach($stock as $s): ?>
        <?php
          $img = !empty($s['producto_img']) ? '../../img/'.$s['producto_img'] : null;
          $initials = strtoupper(substr($s['producto_nombre'],0,2));
        ?>
        <div class="rounded-3xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-lg transition-all duration-300 p-6 flex flex-col gap-4">
          <div class="flex items-center gap-4">
            <?php if($img): ?>
              <img src="<?= htmlspecialchars($img) ?>" class="w-14 h-14 rounded-2xl object-cover shadow" alt="">
            <?php else: ?>
              <div class="w-14 h-14 rounded-2xl emerald-gradient flex items-center justify-center text-white font-black text-lg shadow"><?= $initials ?></div>
            <?php endif; ?>
            <div>
              <p class="font-bold text-slate-800"><?= htmlspecialchars($s['producto_nombre']) ?></p>
              <p class="text-xs text-slate-400 font-semibold uppercase"><?= htmlspecialchars($s['categoria_nombre'] ?? 'Sin categoría') ?></p>
            </div>
          </div>
          <div class="grid grid-cols-4 gap-2 text-center">
            <div class="bg-violet-50 rounded-xl p-2" title="Producido">
              <p class="text-lg font-black text-violet-600"><?= number_format($s['total_ingresado']) ?></p>
              <p class="text-[9px] font-bold text-violet-500 uppercase">Producido</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-2" title="Vendido">
              <p class="text-lg font-black text-amber-600"><?= number_format($s['total_vendido']) ?></p>
              <p class="text-[9px] font-bold text-amber-500 uppercase">Vendido</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-2 shadow-inner border border-emerald-100" title="Stock Actual">
              <p class="text-lg font-black text-emerald-600"><?= number_format($s['total_unidades']) ?></p>
              <p class="text-[9px] font-bold text-emerald-500 uppercase">Stock</p>
            </div>
            <div class="bg-sky-50 rounded-xl p-2" title="Lotes">
              <p class="text-lg font-black text-sky-600"><?= $s['num_lotes'] ?></p>
              <p class="text-[9px] font-bold text-sky-500 uppercase">Lotes</p>
            </div>
          </div>
          <p class="text-xs text-slate-400 text-right">Último ingreso: <?= date('d/m/Y H:i', strtotime($s['ultima_entrada'])) ?></p>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- ── TABLA DETALLADA ── -->
  <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="text-2xl font-bold text-slate-800">Historial de <span class="text-violet-600">Ingresos</span></h3>
        <p class="text-slate-500 text-sm mt-1">Cada fila representa una producción finalizada.</p>
      </div>
      <span class="px-4 py-2 bg-violet-100 text-violet-700 text-sm font-bold rounded-full"><?= count($registros) ?> registros</span>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
      <table class="w-full text-left">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold tracking-widest sticky top-0 z-10 shadow-sm">
          <tr>
            <th class="px-8 py-5">#</th>
            <th class="px-8 py-5">Producto</th>
            <th class="px-8 py-5">Lote Producción</th>
            <th class="px-8 py-5">Cantidad</th>
            <th class="px-8 py-5">Bodega</th>
            <th class="px-8 py-5">Responsable</th>
            <th class="px-8 py-5">Fecha Ingreso</th>
            <th class="px-8 py-5 text-center">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($registros)): ?>
            <tr><td colspan="8" class="p-16 text-center text-slate-400 font-medium">No hay registros aún.</td></tr>
          <?php else: foreach($registros as $r): ?>
          <tr class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-8 py-5 text-slate-400 font-mono text-sm">#<?= $r['id_inventario'] ?></td>
            <td class="px-8 py-5">
              <div class="font-bold text-slate-800"><?= htmlspecialchars($r['producto_nombre'] ?? '—') ?></div>
              <div class="text-xs text-slate-400"><?= htmlspecialchars($r['categoria_nombre'] ?? '') ?></div>
            </td>
            <td class="px-8 py-5">
              <span class="px-3 py-1 bg-sky-100 text-sky-700 rounded-lg text-xs font-bold"><?= htmlspecialchars($r['lote_produccion'] ?? '—') ?></span>
            </td>
            <td class="px-8 py-5">
              <span class="text-2xl font-black text-emerald-600"><?= number_format($r['cantidad']) ?></span>
              <span class="text-xs text-slate-400 ml-1">unds.</span>
            </td>
            <td class="px-8 py-5">
              <span class="flex items-center gap-1 text-slate-700 font-semibold text-sm">
                <i class="fas fa-warehouse text-slate-400 text-xs"></i>
                <?= htmlspecialchars($r['bodega']) ?>
              </span>
            </td>
            <td class="px-8 py-5 text-slate-600"><?= htmlspecialchars($r['usuario_nombre'] ?? '—') ?></td>
            <td class="px-8 py-5 text-slate-500 text-sm"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></td>
            <td class="px-8 py-5 text-center">
              <button onclick="openEditBodega(<?= $r['id_inventario'] ?>, '<?= htmlspecialchars($r['bodega']) ?>')"
                class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-violet-600 hover:border-violet-400 hover:bg-violet-50 transition-all" title="Editar bodega">
                <i class="fas fa-pen-to-square text-sm"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── TABLA DETALLADA DE SALIDAS ── -->
  <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden mt-8">
    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="text-2xl font-bold text-slate-800">Historial de <span class="text-amber-600">Salidas</span></h3>
        <p class="text-slate-500 text-sm mt-1">Registros de los productos que han salido por ventas.</p>
      </div>
      <span class="px-4 py-2 bg-amber-100 text-amber-700 text-sm font-bold rounded-full"><?= count($salidas) ?> ventas</span>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
      <table class="w-full text-left">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold tracking-widest sticky top-0 z-10 shadow-sm">
          <tr>
            <th class="px-8 py-5">Venta #</th>
            <th class="px-8 py-5">Fecha</th>
            <th class="px-8 py-5">Cliente</th>
            <th class="px-8 py-5">Producto</th>
            <th class="px-8 py-5">Cantidad Saliente</th>
            <th class="px-8 py-5">Estado Venta</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($salidas)): ?>
            <tr><td colspan="6" class="p-16 text-center text-slate-400 font-medium">No hay salidas registradas aún.</td></tr>
          <?php else: foreach($salidas as $s): ?>
          <tr class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-8 py-5 text-slate-400 font-mono text-sm">#<?= $s['id_venta'] ?></td>
            <td class="px-8 py-5 text-slate-500 text-sm"><?= date('d/m/Y H:i', strtotime($s['fecha'])) ?></td>
            <td class="px-8 py-5 font-semibold text-slate-700"><?= htmlspecialchars($s['cliente_nombre'] ?? '—') ?></td>
            <td class="px-8 py-5">
              <div class="font-bold text-slate-800"><?= htmlspecialchars($s['producto_nombre'] ?? '—') ?></div>
              <div class="text-xs text-slate-400"><?= htmlspecialchars($s['categoria_nombre'] ?? '') ?></div>
            </td>
            <td class="px-8 py-5">
              <span class="text-xl font-black text-amber-600">-<?= number_format($s['cantidad']) ?></span>
              <span class="text-xs text-slate-400 ml-1">unds.</span>
            </td>
            <td class="px-8 py-5">
              <?php
                $est = $s['estado'];
                $cls = $est === 'Entregado' ? 'bg-emerald-100 text-emerald-700' : ($est === 'En Proceso' ? 'bg-blue-100 text-blue-700' : ($est === 'Pendiente' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700'));
              ?>
              <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $cls ?>"><?= htmlspecialchars($est) ?></span>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
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
