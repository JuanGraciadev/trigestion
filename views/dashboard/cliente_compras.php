<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '3') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';

$database   = new Database();
$db         = $database->conectar();
$ventaModel = new Venta($db);

$id_cliente = $_SESSION['usuario']['id_usuario'];
$ventas     = $ventaModel->obtenerPorCliente($id_cliente);

$titulo = "Mis Compras";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>



<div class="space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl"></div>
        <div class="z-10">
            <h2 class="text-3xl font-bold text-slate-800">Estado de mis <span class="text-sky-600">Pedidos</span></h2>
            <p class="text-slate-500 mt-1">Sigue el progreso de todas tus órdenes en tiempo real.</p>
        </div>
        <a href="cliente.php" class="z-10 water-gradient text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-sky-200 hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-cart-shopping"></i> Seguir Comprando
        </a>
    </div>

    <!-- Leyenda de estados -->
    <div class="flex flex-wrap gap-4 justify-center">
        <?php
        $estados = [
            'Pendiente'   => ['bg-amber-100 text-amber-700',  'fa-clock'],
            'En Proceso'  => ['bg-blue-100 text-blue-700',    'fa-gears'],
            'Entregado'   => ['bg-emerald-100 text-emerald-700','fa-circle-check'],
            'Cancelado'   => ['bg-red-100 text-red-700',      'fa-circle-xmark'],
        ];
        foreach ($estados as $e => [$cls, $ico]):
        ?>
        <span class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold <?= $cls ?>">
            <i class="fas <?= $ico ?>"></i> <?= $e ?>
        </span>
        <?php endforeach; ?>
    </div>

    <!-- Lista de pedidos -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Mis Pedidos</h3>
                <p class="text-slate-400 text-sm mt-1"><?= count($ventas) ?> pedido(s) en total</p>
            </div>
            <div class="w-14 h-14 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-500 text-2xl">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>

        <div class="p-8">
        <?php if (empty($ventas)): ?>
            <div class="text-center py-16 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-sm">
                    <i class="fas fa-receipt text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Aún no tienes pedidos</h3>
                <p class="text-slate-500 mb-6">¡Explora nuestro catálogo y realiza tu primer pedido!</p>
                <a href="cliente.php" class="inline-block water-gradient text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-sky-200">
                    Ir al Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
            <?php foreach ($ventas as $v):
                $estado = $v['estado'] ?? 'Pendiente';
                [$estadoCls, $estadoIco] = $estados[$estado] ?? ['bg-slate-100 text-slate-600','fa-circle'];
                $fecha = date('d/m/Y H:i', strtotime($v['fecha']));
            ?>
            <div class="group relative bg-white border border-slate-200 hover:border-sky-200 rounded-3xl overflow-hidden transition-all hover:shadow-xl hover:shadow-sky-50">
                <!-- Color accent bar -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-3xl
                    <?= $estado === 'Entregado' ? 'bg-emerald-400' : ($estado === 'Cancelado' ? 'bg-red-400' : ($estado === 'En Proceso' ? 'bg-blue-400' : 'bg-amber-400')) ?>"></div>

                <div class="p-6 pl-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Info pedido -->
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600 text-xl font-black flex-shrink-0">
                                #<?= $v['id_venta'] ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-lg">Pedido #<?= $v['id_venta'] ?></h4>
                                <p class="text-sm text-slate-500"><i class="fas fa-calendar-alt mr-1"></i><?= $fecha ?></p>
                                <?php if (!empty($v['productos_lista'])): ?>
                                <p class="text-xs text-slate-400 mt-1 font-medium line-clamp-1">
                                    <i class="fas fa-box mr-1"></i><?= htmlspecialchars($v['productos_lista']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Total y Estado -->
                        <div class="flex items-center gap-6 md:flex-row flex-col md:items-center">
                            <div class="text-center">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total</p>
                                <p class="text-2xl font-black text-sky-600">$<?= number_format($v['total'], 2) ?></p>
                            </div>
                            <span class="px-5 py-2 rounded-full text-sm font-bold flex items-center gap-2 <?= $estadoCls ?>">
                                <i class="fas <?= $estadoIco ?>"></i> <?= $estado ?>
                            </span>
                        </div>
                    </div>

                    <!-- Timeline de progreso -->
                    <?php
                    $pasos = ['Pendiente','En Proceso','Entregado'];
                    $pasoActual = array_search($estado, $pasos);
                    if ($pasoActual === false) $pasoActual = -1; // cancelado
                    ?>
                    <?php if ($estado !== 'Cancelado'): ?>
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <?php foreach ($pasos as $i => $paso):
                                $activo = $i <= $pasoActual;
                                $actual = $i === $pasoActual;
                                $iconos = ['fa-clock','fa-gears','fa-circle-check'];
                            ?>
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all
                                    <?= $activo ? ($actual ? 'water-gradient text-white shadow-lg' : 'bg-emerald-100 text-emerald-600') : 'bg-slate-100 text-slate-400' ?>">
                                    <i class="fas <?= $iconos[$i] ?>"></i>
                                </div>
                                <p class="text-[10px] font-bold text-center <?= $activo ? 'text-slate-700' : 'text-slate-400' ?>"><?= $paso ?></p>
                            </div>
                            <?php if ($i < count($pasos)-1): ?>
                            <div class="flex-1 h-0.5 mb-5 <?= $pasoActual > $i ? 'bg-emerald-300' : 'bg-slate-200' ?>"></div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-2xl text-sm text-red-600 font-medium flex items-center gap-2">
                        <i class="fas fa-circle-xmark"></i> Este pedido fue cancelado.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
