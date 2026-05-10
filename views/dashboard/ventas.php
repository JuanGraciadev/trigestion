<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';
require_once __DIR__ . '/../../models/Producto.php';

$database   = new Database();
$db         = $database->conectar();
$ventaModel = new Venta($db);
$productoModel = new Producto($db);

$ventas     = $ventaModel->obtenerTodas();
$stats      = $ventaModel->obtenerEstadisticas();
$pendientes = $stats['Pendiente'];

// Datos para el modal de venta POS
$productosActivos = [];
$todosProductos = $productoModel->obtenerTodos();
foreach ($todosProductos as $p) {
    if (($p['estado'] ?? 1) == 1) {
        $p['stock'] = $ventaModel->stockDisponible($p['id_producto']);
        $productosActivos[] = $p;
    }
}
$clientes = $ventaModel->obtenerClientes();

$titulo = "Ventas y Pedidos";
require_once __DIR__ . '/../layouts/header.php';



require_once __DIR__ . '/../layouts/sidebar.php';
?>



<!-- Background Elements -->
<div class="bg-blobs">
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="blob-3"></div>
</div>

<?php if (isset($_SESSION['alert'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $_SESSION['alert']['icon'] ?>',
            title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
            text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
            confirmButtonColor: '#4F46E5',
            customClass: { popup: 'rounded-[2rem] glass-card font-outfit' }
        });
    });
</script>
<?php unset($_SESSION['alert']); endif; ?>

<div class="space-y-10 font-outfit relative z-10">

    <!-- ── Header Section ─────────────────────────────────────────────────────── -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8 animate-fade-up">
        <div>
            <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-sm mb-4 shadow-sm">
                <i class="fas fa-sparkles mr-2"></i>Dashboard Pro
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-slate-800 tracking-tight leading-tight">Centro de <br class="hidden md:block" /><span class="premium-gradient-text">Ventas</span></h1>
            <p class="text-slate-500 mt-3 text-lg font-medium max-w-xl">Gestiona pedidos, maximiza tus ingresos y supervisa el estado de ventas en tiempo real con nuestra interfaz de alto rendimiento.</p>
        </div>
        <div class="flex items-center gap-4">
            <?php if ($pendientes > 0): ?>
            <div class="flex items-center gap-3 bg-white border border-amber-200 text-amber-600 px-6 py-4 rounded-2xl font-bold text-sm shadow-[0_8px_30px_rgb(245,158,11,0.2)] relative overflow-hidden group cursor-pointer hover:scale-105 transition-transform">
                <div class="absolute inset-0 bg-amber-50 translate-y-full group-hover:translate-y-0 transition-transform"></div>
                <div class="relative z-10 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fas fa-bell text-lg animate-bounce"></i>
                </div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-xs text-amber-500 uppercase tracking-wider font-extrabold">Urgente</span>
                    <span><?= $pendientes ?> pedido(s)</span>
                </div>
            </div>
            <?php endif; ?>
            <button onclick="openModalPOS()" class="premium-gradient text-white px-8 py-5 rounded-[1.5rem] font-bold shadow-[0_10px_40px_rgba(79,70,229,0.4)] transition-all transform hover:-translate-y-2 hover:shadow-[0_15px_50px_rgba(225,29,72,0.5)] flex items-center gap-3 overflow-hidden relative group">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 relative z-10">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div class="relative z-10 text-left">
                    <div class="text-xs text-white/80 uppercase tracking-wider font-bold">Venta Rápida</div>
                    <div class="text-lg">Terminal POS</div>
                </div>
            </button>
        </div>
    </div>

    <!-- ── Estadísticas ─────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-up delay-100">
        <?php
        $tarjetas = [
            ['Pendientes',     $stats['Pendiente'],    'fa-clock',         'from-amber-400 to-orange-500',   'text-amber-600', 'bg-amber-50', 'shadow-[0_10px_30px_rgba(245,158,11,0.15)]'],
            ['En Proceso',     $stats['En Proceso'],   'fa-gears',         'from-blue-400 to-indigo-500',    'text-blue-600',  'bg-blue-50',  'shadow-[0_10px_30px_rgba(59,130,246,0.15)]'],
            ['Entregados',     $stats['Entregado'],    'fa-circle-check',  'from-emerald-400 to-teal-500',   'text-emerald-600','bg-emerald-50', 'shadow-[0_10px_30px_rgba(16,185,129,0.15)]'],
            ['Cancelados',     $stats['Cancelado'],    'fa-circle-xmark',  'from-rose-400 to-red-500',       'text-rose-600',   'bg-rose-50',   'shadow-[0_10px_30px_rgba(225,29,72,0.15)]'],
        ];
        foreach ($tarjetas as [$label, $val, $ico, $grad, $txt, $bg, $shadow]):
        ?>
        <div class="stat-card glass-card rounded-[2.5rem] p-7 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br <?= $grad ?> opacity-10 rounded-full blur-[30px] group-hover:opacity-30 group-hover:scale-150 transition-all duration-700"></div>
            
            <div class="flex justify-between items-start mb-6 relative z-10">
                <div class="w-16 h-16 rounded-[1.2rem] <?= $bg ?> flex items-center justify-center <?= $txt ?> text-3xl border border-white/80 <?= $shadow ?> transform group-hover:rotate-6 group-hover:scale-110 transition-all duration-500">
                    <i class="fas <?= $ico ?>"></i>
                </div>
                <?php if ($label === 'Pendientes' && $val > 0): ?>
                <div class="bg-gradient-to-r from-red-500 to-rose-600 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.5)] animate-pulse uppercase tracking-widest border border-red-400/50">¡NUEVO!</div>
                <?php endif; ?>
            </div>
            
            <div class="relative z-10">
                <div class="text-5xl font-black text-slate-800 tracking-tighter mb-2 group-hover:translate-x-1 transition-transform"><?= $val ?></div>
                <div class="text-slate-500 font-bold text-xs uppercase tracking-[0.2em] group-hover:text-slate-800 transition-colors"><?= $label ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Total ingresos ──────────────────────────────────────────────────── -->
    <div class="relative rounded-[3rem] p-12 overflow-hidden shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] group stat-card animate-fade-up delay-200">
        <!-- Background -->
        <div class="absolute inset-0 bg-[#0B1120]"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-gradient-to-br from-indigo-500/40 to-rose-500/40 rounded-full blur-[100px] group-hover:scale-110 transition-transform duration-1000"></div>
        <div class="absolute -bottom-32 -left-32 w-[400px] h-[400px] bg-blue-600/30 rounded-full blur-[100px]"></div>
        
        <!-- Abstract Wave/Grid overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_80%_80%_at_50%_50%,#000_20%,transparent_100%)]"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 w-full">
                <div class="w-28 h-28 rounded-[2rem] bg-gradient-to-br from-indigo-500 via-purple-500 to-rose-500 p-[3px] shadow-[0_0_40px_rgba(139,92,246,0.4)] shrink-0 group-hover:rotate-12 transition-transform duration-700">
                    <div class="w-full h-full bg-[#0B1120] rounded-[1.8rem] flex items-center justify-center text-transparent bg-clip-text bg-gradient-to-br from-indigo-300 to-rose-300 text-5xl">
                        <i class="fas fa-wallet drop-shadow-lg"></i>
                    </div>
                </div>
                <div class="text-center md:text-left w-full">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-indigo-300 text-xs font-bold uppercase tracking-[0.2em] mb-4">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Balance General Confimado
                    </div>
                    <div class="text-6xl md:text-7xl lg:text-[5.5rem] font-black text-white tracking-tighter flex items-start justify-center md:justify-start gap-2 [text-shadow:0_10px_30px_rgba(0,0,0,0.5)]">
                        <span class="text-indigo-400 text-4xl lg:text-5xl mt-2">$</span><?= number_format($stats['total_ingresos'], 2) ?>
                    </div>
                </div>
            </div>
            
            <div class="hidden lg:flex w-full max-w-sm justify-end items-end h-28 gap-3">
                <?php for($i=1; $i<=8; $i++): 
                    $h = rand(30, 100);
                    $delay = $i * 100;
                ?>
                <div class="w-4 rounded-t-xl bg-gradient-to-t from-indigo-600/20 to-indigo-400/80 hover:to-rose-400 transition-all duration-500 cursor-pointer" style="height: <?= $h ?>%; transition-delay: <?= $delay ?>ms;"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ── Tabla de pedidos ────────────────────────────────────────────────── -->
    <div class="animate-fade-up delay-300">
        
        <!-- Filtros -->
        <div class="glass-panel rounded-[2rem] p-6 mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[1.5rem] bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30 shrink-0">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Registro de <span class="premium-gradient-text">Pedidos</span></h2>
                    <p class="text-slate-500 text-sm font-medium mt-1">Explora y gestiona todo el historial de ventas</p>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2 p-2 bg-slate-100/60 rounded-2xl backdrop-blur-md border border-white w-full xl:w-auto shadow-inner">
                <button onclick="filtrarTabla('todos')" class="filter-btn active flex-1 xl:flex-none px-6 py-3 rounded-xl font-bold text-sm transition-all bg-white text-indigo-600 shadow-[0_4px_15px_rgba(0,0,0,0.05)] border border-slate-100" data-filter="todos">
                    Todos <span class="ml-2 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs shadow-sm"><?= count($ventas) ?></span>
                </button>
                <?php
                $filtros = [
                    'Pendiente'  => [$stats['Pendiente'], 'amber'],
                    'En Proceso' => [$stats['En Proceso'], 'blue'],
                    'Entregado'  => [$stats['Entregado'], 'emerald'],
                    'Cancelado'  => [$stats['Cancelado'], 'red'],
                ];
                foreach ($filtros as $f => [$cnt, $color]):
                ?>
                <button onclick="filtrarTabla('<?= $f ?>')" class="filter-btn flex-1 xl:flex-none px-6 py-3 rounded-xl font-bold text-sm transition-all text-slate-500 hover:text-<?= $color ?>-600 hover:bg-white hover:shadow-sm" data-filter="<?= $f ?>" data-color="<?= $color ?>">
                    <?= $f ?> <span class="ml-2 px-2.5 py-1 rounded-lg bg-slate-200/50 text-slate-600 text-xs cnt-badge"><?= $cnt ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar relative min-h-[400px] px-2 pb-10">
            <table class="w-full text-left table-separated" id="tablaVentas">
                <thead class="text-slate-400 text-xs font-black uppercase tracking-[0.15em] sticky top-0 z-20">
                    <tr>
                        <th class="px-8 py-4">ID Pedido</th>
                        <th class="px-8 py-4">Información de Cliente</th>
                        <th class="px-8 py-4">Fecha / Hora</th>
                        <th class="px-8 py-4">Desglose de Productos</th>
                        <th class="px-8 py-4 text-right">Total Pagar</th>
                        <th class="px-8 py-4 text-center">Estado</th>
                        <th class="px-8 py-4 text-center">Gestión</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                <?php foreach ($ventas as $v):
                    $estado = $v['estado'] ?? 'Pendiente';
                    $fechaF = date('d M, Y', strtotime($v['fecha']));
                    $horaF  = date('H:i A', strtotime($v['fecha']));
                    $detalles = $ventaModel->obtenerDetalle($v['id_venta']);

                    $badgeStyles = [
                        'Pendiente'  => 'bg-amber-100/80 text-amber-700 border-amber-200 shadow-amber-500/10',
                        'En Proceso' => 'bg-blue-100/80 text-blue-700 border-blue-200 shadow-blue-500/10',
                        'Entregado'  => 'bg-emerald-100/80 text-emerald-700 border-emerald-200 shadow-emerald-500/10',
                        'Cancelado'  => 'bg-red-100/80 text-red-700 border-red-200 shadow-red-500/10',
                    ][$estado] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                ?>
                <tr class="table-row-card group pedido-row cursor-default" data-estado="<?= $estado ?>">
                    <td class="px-8 py-6">
                        <div class="inline-flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cód. Transacción</span>
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-[1rem] bg-slate-100/80 border border-slate-200 font-black text-slate-700 text-base shadow-inner group-hover:bg-indigo-50 group-hover:text-indigo-700 group-hover:border-indigo-200 transition-colors">
                                <i class="fas fa-receipt text-indigo-400/50 text-sm"></i> #<?= str_pad($v['id_venta'], 4, '0', STR_PAD_LEFT) ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-[1.2rem] bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-black text-xl border border-white shadow-[0_5px_15px_rgba(79,70,229,0.15)] shrink-0 transform group-hover:scale-110 group-hover:rotate-6 transition-all">
                                <?= strtoupper(substr($v['cliente_nombre'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="font-extrabold text-slate-800 text-lg mb-0.5 tracking-tight"><?= htmlspecialchars($v['cliente_nombre'] ?? 'N/A') ?></div>
                                <div class="text-xs text-slate-500 font-bold flex flex-col gap-1">
                                    <?php if (!empty($v['cliente_telefono'])): ?>
                                    <span class="flex items-center gap-1.5"><i class="fas fa-phone-alt text-indigo-400"></i><?= htmlspecialchars($v['cliente_telefono']) ?></span>
                                    <?php endif; ?>
                                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-indigo-400"></i><?= htmlspecialchars($v['cliente_email'] ?? '') ?></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-base font-black text-slate-800"><?= $fechaF ?></div>
                        <div class="text-xs text-slate-500 font-bold mt-1 px-2 py-1 bg-slate-100 rounded-md inline-flex items-center"><i class="far fa-clock mr-1.5 text-indigo-500"></i><?= $horaF ?></div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-2.5 max-w-[320px]">
                            <?php 
                            $maxVisible = 2;
                            $count = 0;
                            foreach ($detalles as $d): 
                                if($count < $maxVisible):
                            ?>
                            <div class="flex items-center justify-between p-2.5 rounded-[1rem] bg-white/60 border border-slate-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] group-hover:bg-white group-hover:shadow-[0_5px_15px_rgba(79,70,229,0.08)] group-hover:border-indigo-100 transition-all">
                                <div class="flex items-center gap-3 overflow-hidden pr-2">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 flex items-center justify-center text-indigo-500 shrink-0 border border-indigo-100/50">
                                        <i class="fas fa-box text-sm"></i>
                                    </div>
                                    <span class="truncate text-sm font-extrabold text-slate-700"><?= htmlspecialchars($d['producto_nombre'] ?? 'Producto') ?></span>
                                </div>
                                <div class="bg-slate-800 text-white font-black px-2.5 py-1 rounded-lg text-xs shrink-0 shadow-md">
                                    x<?= $d['cantidad'] ?>
                                </div>
                            </div>
                            <?php 
                                endif;
                                $count++;
                            endforeach; 
                            if($count > $maxVisible):
                            ?>
                            <div class="text-center py-2 rounded-[1rem] bg-slate-50 border border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:bg-indigo-50 group-hover:text-indigo-500 group-hover:border-indigo-100 transition-colors">
                                <i class="fas fa-layer-group mr-1.5"></i> +<?= $count - $maxVisible ?> producto(s) adicional(es)
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex flex-col items-end justify-center h-full">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total a Pagar</span>
                            <div class="inline-flex items-baseline px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm group-hover:shadow-[0_10px_20px_rgba(16,185,129,0.15)] group-hover:scale-105 transition-all">
                                <span class="text-emerald-500 font-extrabold text-xl mr-1">$</span>
                                <span class="text-3xl font-black text-emerald-700 tracking-tighter drop-shadow-sm"><?= number_format($v['total'], 2) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <span class="status-badge inline-flex items-center px-4 py-2.5 rounded-[1rem] text-[11px] font-black uppercase tracking-[0.1em] border-2 shadow-sm <?= $badgeStyles ?>">
                            <?php if($estado == 'Entregado') echo '<i class="fas fa-check-circle mr-2 text-sm"></i>'; ?>
                            <?php if($estado == 'Pendiente') echo '<i class="fas fa-circle-notch fa-spin mr-2 text-sm"></i>'; ?>
                            <?php if($estado == 'En Proceso') echo '<i class="fas fa-cog fa-spin mr-2 text-sm"></i>'; ?>
                            <?php if($estado == 'Cancelado') echo '<i class="fas fa-times-circle mr-2 text-sm"></i>'; ?>
                            <?= $estado ?>
                        </span>
                    </td>
                    <td class="px-8 py-6 text-center relative">
                        <?php if ($estado !== 'Cancelado' && $estado !== 'Entregado'): ?>
                        <button onclick="openModalEstado(<?= $v['id_venta'] ?>, '<?= $estado ?>')"
                            class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white border-2 border-slate-100 text-slate-400 hover:text-white hover:bg-indigo-600 hover:border-indigo-600 hover:shadow-[0_10px_20px_rgba(79,70,229,0.3)] transition-all focus:ring-4 focus:ring-indigo-500/20 transform hover:scale-110"
                            title="Actualizar estado">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <?php else: ?>
                        <div class="w-12 h-12 mx-auto rounded-2xl flex items-center justify-center text-slate-300 bg-slate-50 border-2 border-slate-100 shadow-inner">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($ventas)): ?>
                <tr>
                    <td colspan="7" class="px-8 py-24 text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-300 shadow-inner">
                            <i class="fas fa-receipt text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-700 mb-1">Sin pedidos registrados</h3>
                        <p class="text-slate-400 font-medium">No se encontraron ventas en el sistema.</p>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Modals ────────────────────────────────────────────────────────────── -->

<!-- Modal cambiar estado -->
<div id="modalEstado" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300 border border-white" id="modalEstadoContent">
        <div class="relative p-8 border-b border-slate-100 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
            <div class="flex justify-between items-center relative z-10">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-800">Actualizar Estado</h3>
                    <div class="inline-flex items-center gap-2 mt-2 px-3 py-1 rounded-lg bg-slate-100 text-sm font-bold text-slate-600">
                        <i class="fas fa-hashtag text-slate-400"></i><span id="modalPedidoId">—</span>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all border border-slate-200 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <form action="../../controllers/VentaController.php?accion=cambiar_estado" method="POST" class="p-8">
            <input type="hidden" name="id_venta" id="modalIdVenta">

            <div class="space-y-4 mb-8">
                <?php
                $opciones = [
                    ['Pendiente',  'fa-clock',        'peer-checked:bg-amber-50 peer-checked:border-amber-400 peer-checked:text-amber-700 peer-checked:shadow-md peer-checked:shadow-amber-500/10',   'hover:bg-amber-50/50 hover:border-amber-200 text-slate-500'],
                    ['En Proceso', 'fa-gears',         'peer-checked:bg-blue-50 peer-checked:border-blue-400 peer-checked:text-blue-700 peer-checked:shadow-md peer-checked:shadow-blue-500/10',     'hover:bg-blue-50/50 hover:border-blue-200 text-slate-500'],
                    ['Entregado',  'fa-circle-check',  'peer-checked:bg-emerald-50 peer-checked:border-emerald-400 peer-checked:text-emerald-700 peer-checked:shadow-md peer-checked:shadow-emerald-500/10', 'hover:bg-emerald-50/50 hover:border-emerald-200 text-slate-500'],
                    ['Cancelado',  'fa-circle-xmark',  'peer-checked:bg-red-50 peer-checked:border-red-400 peer-checked:text-red-700 peer-checked:shadow-md peer-checked:shadow-red-500/10',         'hover:bg-red-50/50 hover:border-red-200 text-slate-500'],
                ];
                foreach ($opciones as [$opt, $ico, $pcls, $hcls]):
                ?>
                <label class="relative block cursor-pointer group">
                    <input type="radio" name="estado" value="<?= $opt ?>" class="peer sr-only" required>
                    <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 transition-all duration-200 <?= $hcls ?> <?= $pcls ?>">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-lg peer-checked:scale-110 transition-transform group-hover:scale-105">
                            <i class="fas <?= $ico ?>"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-base"><?= $opt ?></div>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center opacity-0 peer-checked:opacity-100 peer-checked:border-current peer-checked:bg-current transition-all">
                            <i class="fas fa-check text-white text-[10px]"></i>
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-6 py-3.5 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="premium-gradient text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal POS -->
<div id="modalPOS" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 overflow-y-auto opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl overflow-hidden my-8 transform scale-95 transition-transform duration-300" id="modalPOSContent">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-900 to-slate-800 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl -mr-20 -mt-20"></div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-2xl border border-white/20">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold tracking-tight">Punto de Venta</h3>
                    <p class="text-sm text-slate-300 font-medium mt-0.5">Registro de venta directa</p>
                </div>
            </div>
            <button onclick="closeModalPOS()" class="relative z-10 w-10 h-10 rounded-full bg-white/10 text-white hover:bg-white/20 hover:rotate-90 transition-all border border-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="../../controllers/VentaController.php?accion=crear_pos" method="POST" class="p-8 flex flex-col lg:flex-row gap-8">
            
            <!-- Columna Izquierda: Formulario y Productos -->
            <div class="flex-1 space-y-8">
                <!-- Cliente -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fas fa-user text-indigo-500"></i> Selección de Cliente
                    </label>
                    <div class="relative">
                        <select name="id_cliente" required class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white outline-none transition-all text-slate-700 font-medium appearance-none">
                            <option value="">Seleccione un cliente registrado...</option>
                            <?php foreach ($clientes as $cli): ?>
                                <option value="<?= $cli['id_usuario'] ?>"><?= htmlspecialchars($cli['nombres']) ?> — <?= htmlspecialchars($cli['documento_numero'] ?? $cli['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        <i class="fas fa-address-card absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <!-- Productos dinámicos -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <i class="fas fa-box-open text-indigo-500"></i> Detalle de Productos
                        </label>
                        <button type="button" onclick="addProductRow()" class="text-sm font-bold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-xl hover:bg-indigo-100 hover:text-indigo-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                    <div id="posProductRows" class="space-y-3 max-h-[300px] overflow-y-auto custom-scrollbar pr-2 pb-2">
                        <!-- Filas dinámicas -->
                    </div>
                </div>
                
                <!-- Notas -->
                <div class="space-y-3">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fas fa-comment-alt text-indigo-500"></i> Notas u Observaciones
                    </label>
                    <textarea name="notas" rows="2" placeholder="Ej. Entregar en puerta principal..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white outline-none transition-all text-slate-700 resize-none font-medium"></textarea>
                </div>
            </div>

            <!-- Columna Derecha: Resumen y Acción -->
            <div class="w-full lg:w-[320px] shrink-0">
                <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-200 sticky top-8">
                    <h4 class="font-bold text-slate-800 mb-6 text-lg border-b border-slate-200 pb-4">Resumen de Venta</h4>
                    
                    <div class="space-y-4 mb-8" id="posResumenItems">
                        <!-- Resumen dinámico -->
                        <div class="text-slate-400 text-sm text-center py-4 italic">Agrega productos para ver el resumen</div>
                    </div>
                    
                    <div class="border-t border-slate-200 border-dashed pt-6 mb-8">
                        <div class="flex justify-between items-end">
                            <p class="text-slate-500 font-bold uppercase tracking-wider text-xs mb-1">Total a Pagar</p>
                            <p class="text-4xl font-black text-indigo-600 tracking-tight" id="posTotalDisplay">$0.00</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full premium-gradient text-white py-4 rounded-2xl font-bold shadow-xl shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-3 text-lg hover:shadow-indigo-500/50">
                        <i class="fas fa-check-circle"></i> Procesar Venta
                    </button>
                    <button type="button" onclick="closeModalPOS()" class="w-full mt-3 py-3 text-slate-500 font-bold hover:bg-slate-200/50 rounded-xl transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// ── Datos de productos para el POS ──
const productosDisponibles = <?= json_encode($productosActivos) ?>;

// ─── Animations for Modals ──────────────────────────────────────────────────
function showModalWithAnim(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    // Trigger reflow
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
    content.classList.add('scale-100');
}

function hideModalWithAnim(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

// ─── Modal Estado ───────────────────────────────────────────────────────────
function openModalEstado(id_venta, estadoActual) {
    document.getElementById('modalIdVenta').value = id_venta;
    document.getElementById('modalPedidoId').textContent = strPad(id_venta, 4);
    document.querySelectorAll('input[name="estado"]').forEach(r => {
        r.checked = (r.value === estadoActual);
    });
    showModalWithAnim('modalEstado', 'modalEstadoContent');
}

function closeModal() {
    hideModalWithAnim('modalEstado', 'modalEstadoContent');
}

// ─── Modal POS ──────────────────────────────────────────────────────────────
function openModalPOS() {
    showModalWithAnim('modalPOS', 'modalPOSContent');
    if (document.querySelectorAll('.pos-row').length === 0) addProductRow();
}

function closeModalPOS() {
    hideModalWithAnim('modalPOS', 'modalPOSContent');
}

let posRowCounter = 0;

function addProductRow() {
    const container = document.getElementById('posProductRows');
    const idx = posRowCounter++;

    const optionsHTML = productosDisponibles.map(p => {
        const stockLabel = p.stock > 0 ? `Stock: ${p.stock}` : 'Sin stock';
        return `<option value="${p.id_producto}" data-nombre="${p.nombre}" data-precio="${p.precio}" data-stock="${p.stock}" ${p.stock <= 0 ? 'disabled' : ''}>${p.nombre} — $${parseFloat(p.precio).toFixed(2)} (${stockLabel})</option>`;
    }).join('');

    const row = document.createElement('div');
    row.className = 'pos-row flex flex-col sm:flex-row items-center gap-3 p-4 bg-white rounded-2xl border border-slate-200 shadow-sm transition-all hover:border-indigo-200 hover:shadow-md';
    row.innerHTML = `
        <div class="flex-1 w-full">
            <div class="relative">
                <select name="pos_producto[]" required onchange="updatePOSTotal()"
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white outline-none transition-all text-slate-700 font-medium text-sm appearance-none">
                    <option value="">Seleccionar producto...</option>
                    ${optionsHTML}
                </select>
                <i class="fas fa-box absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-28">
                <input type="number" name="pos_cantidad[]" value="1" min="1" required onchange="updatePOSTotal()" oninput="updatePOSTotal()"
                    class="w-full pl-8 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-center font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm"
                    placeholder="Cant.">
                <i class="fas fa-hashtag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>
            <div class="w-24 text-right font-black text-slate-800 pos-subtotal tracking-tight">$0.00</div>
            <button type="button" onclick="removeProductRow(this)" class="w-10 h-10 rounded-xl border border-red-100 text-red-400 hover:text-white hover:bg-red-500 hover:border-red-500 transition-all flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fas fa-trash-alt text-sm"></i>
            </button>
        </div>`;
    container.appendChild(row);
}

function removeProductRow(btn) {
    const rows = document.querySelectorAll('.pos-row');
    if (rows.length <= 1) {
        Swal.fire({icon:'info', title:'Atención', text:'Debe incluir al menos un producto.', confirmButtonColor:'#4F46E5', customClass:{popup:'rounded-[2rem] font-outfit'}});
        return;
    }
    btn.closest('.pos-row').remove();
    updatePOSTotal();
}

function updatePOSTotal() {
    let total = 0;
    const resumenContainer = document.getElementById('posResumenItems');
    resumenContainer.innerHTML = ''; // Limpiar resumen
    
    let hasItems = false;

    document.querySelectorAll('.pos-row').forEach(row => {
        const select = row.querySelector('select');
        const qtyInput = row.querySelector('input[type="number"]');
        const subtotalEl = row.querySelector('.pos-subtotal');

        const option = select.options[select.selectedIndex];
        if(!option || option.value === "") return;
        
        hasItems = true;
        const nombre = option.dataset.nombre || 'Producto';
        const precio = parseFloat(option.dataset.precio || 0);
        const qty = parseInt(qtyInput.value || 0);
        const subtotal = precio * qty;

        subtotalEl.textContent = '$' + subtotal.toFixed(2);
        total += subtotal;
        
        // Add to resumen
        resumenContainer.innerHTML += `
            <div class="flex justify-between items-center text-sm">
                <div class="flex-1 truncate pr-2 text-slate-600 font-medium">
                    <span class="text-indigo-600 font-bold mr-1">${qty}x</span> ${nombre}
                </div>
                <div class="font-bold text-slate-800">$${subtotal.toFixed(2)}</div>
            </div>
        `;
    });
    
    if(!hasItems) {
        resumenContainer.innerHTML = '<div class="text-slate-400 text-sm text-center py-4 italic">Agrega productos para ver el resumen</div>';
    }
    
    document.getElementById('posTotalDisplay').textContent = '$' + total.toFixed(2);
}

// ─── Utilities ──────────────────────────────────────────────────────────────
window.onclick = function(e) {
    if (e.target.id === 'modalEstado') closeModal();
    if (e.target.id === 'modalPOS') closeModalPOS();
};

function strPad(num, size) {
    let s = num + "";
    while (s.length < size) s = "0" + s;
    return s;
}

// ─── Filtro de tabla ────────────────────────────────────────────────────────
function filtrarTabla(estado) {
    const rows = document.querySelectorAll('.pedido-row');
    rows.forEach(r => {
        r.style.display = (estado === 'todos' || r.dataset.estado === estado) ? '' : 'none';
    });
    
    document.querySelectorAll('.filter-btn').forEach(b => {
        const isActivo = b.dataset.filter === estado;
        const color = b.dataset.color; // 'amber', 'blue', 'emerald', 'red'
        const badge = b.querySelector('.cnt-badge');
        
        // Reset all
        b.className = `filter-btn flex-1 xl:flex-none px-6 py-3 rounded-xl font-bold text-sm transition-all border border-transparent text-slate-500 hover:bg-white hover:shadow-sm`;
        if(color) b.classList.add(`hover:text-${color}-600`);
        if(badge) badge.className = 'ml-2 px-2.5 py-1 rounded-lg bg-slate-200/50 text-slate-600 text-xs cnt-badge transition-colors';
        
        if (isActivo) {
            b.classList.remove('text-slate-500', 'border-transparent');
            b.classList.add('bg-white', 'shadow-[0_4px_15px_rgba(0,0,0,0.05)]', 'border-slate-100');
            
            if(estado === 'todos') {
                b.classList.add('text-indigo-600');
                if(badge) badge.classList.add('bg-indigo-50', 'text-indigo-700', 'shadow-sm');
            } else {
                b.classList.add(`text-${color}-600`);
                if(badge) {
                    badge.classList.remove('bg-slate-200/50', 'text-slate-600');
                    badge.classList.add(`bg-${color}-100`, `text-${color}-700`, 'shadow-sm');
                }
            }
        }
    });
}
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
