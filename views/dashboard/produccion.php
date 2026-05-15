<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Produccion.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/InventarioMP.php';

$database = new Database();
$db = $database->conectar();
$produccionModel = new Produccion($db);
$productoModel = new Producto($db);
$inventarioMPModel = new InventarioMP($db);

$producciones = $produccionModel->obtenerTodas();
$productos = $productoModel->obtenerTodos();
$materiaPrima = $inventarioMPModel->obtenerTodos();

$titulo = "Panel de Producción";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>



<div class="space-y-10">
    <!-- Main Header Card -->
    <div class="glass-card rounded-[2.5rem] premium-shadow border border-slate-100 overflow-hidden">
        <div class="p-8 md:p-10 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gradient-to-r from-slate-50 to-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Planta de Producción</span>
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-800 tracking-tight outfit-font">Control de <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-sky-600">Producción</span></h2>
                <p class="text-slate-500 mt-2 font-medium">Inicia, monitorea y finaliza los procesos de producción de MOOVA!</p>
            </div>
            <button onclick="openModal('modalIniciar')" class="relative z-10 bg-gradient-to-r from-emerald-500 to-sky-600 hover:shadow-[0_10px_25px_-5px_rgba(16,185,129,0.4)] text-white px-8 py-4 rounded-[1.25rem] font-bold transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 outfit-font tracking-wide">
                <i class="fas fa-play"></i>
                Iniciar Producción
            </button>
        </div>

        <?php if (isset($_SESSION['alert'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
                        title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
                        text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
                        confirmButtonColor: '#0ea5e9',
                        confirmButtonText: 'Entendido',
                        customClass: { popup: 'rounded-[2rem]' }
                    });
                });
            </script>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>

        <!-- List of Productions -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($producciones as $prod): ?>
                <div class="glass-card rounded-[2.5rem] border border-slate-100 overflow-hidden premium-shadow hover:shadow-2xl hover:-translate-y-1 transition-all duration-400 group relative">
                    <div class="p-7 relative bg-gradient-to-b from-slate-50/50 to-white">
                        <div class="absolute top-5 right-5">
                            <?php if (($prod['estado'] ?? '') === 'Finalizada'): ?>
                                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Finalizada
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1.5 bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm animate-pulse flex items-center gap-1">
                                    <i class="fas fa-spinner fa-spin"></i> En Proceso
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mb-5 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-industry"></i>
                        </div>
                        
                        <h3 class="text-xl font-black text-slate-800 mb-1 outfit-font group-hover:text-emerald-600 transition-colors">Lote: <?= htmlspecialchars($prod['lote_produccion']) ?></h3>
                        <p class="text-[13px] font-bold text-slate-400 mb-5 tracking-wide"><?= htmlspecialchars($prod['producto_nombre'] ?? 'Producto Desconocido') ?></p>
                        
                        <div class="space-y-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium flex items-center gap-2"><i class="fas fa-cubes text-slate-400 w-4"></i> Cantidad</span>
                                <span class="font-black text-slate-700"><?= htmlspecialchars($prod['cantidad']) ?> unds.</span>
                            </div>
                            <div class="w-full h-[1px] bg-slate-200/60"></div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium flex items-center gap-2"><i class="fas fa-user-gear text-slate-400 w-4"></i> Operario</span>
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($prod['usuario_nombre'] ?? 'N/A') ?></span>
                            </div>
                            <?php if (!empty($prod['descripcion'])): ?>
                            <div class="w-full h-[1px] bg-slate-200/60"></div>
                            <div class="text-[12px] text-slate-500 italic">
                                "<?= htmlspecialchars($prod['descripcion']) ?>"
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (($prod['estado'] ?? '') !== 'Finalizada'): ?>
                            <a href="../../controllers/ProduccionController.php?accion=finalizar&id=<?= $prod['id_produccion'] ?>" class="w-full block text-center py-3.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 font-bold hover:bg-emerald-500 hover:text-white hover:border-emerald-500 hover:shadow-lg hover:shadow-emerald-200 transition-all transform active:scale-95 text-[13px]">
                                <i class="fas fa-flag-checkered mr-1.5"></i> Finalizar Producción
                            </a>
                        <?php else: ?>
                            <button disabled class="w-full block text-center py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 font-bold cursor-not-allowed text-[13px]">
                                <i class="fas fa-check-double mr-1.5"></i> Lote Completado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($producciones)): ?>
                    <div class="col-span-full p-20 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-sm">
                            <i class="fas fa-clipboard-list text-3xl"></i>
                        </div>
                        <p class="text-slate-500 font-medium text-lg">No hay procesos de producción registrados.</p>
                        <button onclick="openModal('modalIniciar')" class="mt-4 text-sky-600 font-bold hover:underline">Iniciar primera producción</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Iniciar Produccion -->
<div id="modalIniciar" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm overflow-y-auto">
    <div class="glass-card rounded-[2.5rem] premium-shadow w-full max-w-2xl overflow-hidden animate-fade-in my-8">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Iniciar Producción</h3>
                <p class="text-sm text-slate-500">Configura un nuevo lote de producción.</p>
            </div>
            <button onclick="closeModal('modalIniciar')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-red-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/ProduccionController.php?accion=crear" method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Código de Lote</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="lote_produccion" required placeholder="Ej. LOTE-2023X" class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Cantidad a Producir</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400"><i class="fas fa-layer-group"></i></span>
                        <input type="number" name="cantidad" required min="1" placeholder="Ej. 100" class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
                    </div>
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Producto Terminado</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 z-10"><i class="fas fa-bottle-water"></i></span>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10"></i>
                    <select name="id_producto" required class="w-full pl-12 pr-10 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700 appearance-none">
                        <option value="">Seleccione el producto...</option>
                        <?php foreach($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>"><?= htmlspecialchars($p['nombre']) ?> - $<?= number_format($p['precio'], 2) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Materia Prima a Utilizar (Opcional)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 z-10"><i class="fas fa-boxes-stacked"></i></span>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10"></i>
                    <select name="id_inventario_materia" class="w-full pl-12 pr-10 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700 appearance-none">
                        <option value="">No deducir / Lote Genérico...</option>
                        <?php foreach($materiaPrima as $mp): ?>
                            <option value="<?= $mp['id_inventario_materia'] ?>">
                                MP-<?= $mp['id_inventario_materia'] ?>: <?= htmlspecialchars($mp['tipo_envase']) ?> <?= htmlspecialchars($mp['capacidad']) ?> (Lote: <?= htmlspecialchars($mp['codigo_lote'] ?? 'N/A') ?>) - Qty: <?= $mp['ingreso'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="text-xs text-slate-400 italic mt-1 ml-2">Asocia un registro de entrada de materia prima para la trazabilidad.</p>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Descripción o Notas</label>
                <div class="relative">
                    <span class="absolute top-4 left-0 flex items-start pl-5 text-slate-400"><i class="fas fa-comment-alt"></i></span>
                    <textarea name="descripcion" rows="3" placeholder="Instrucciones especiales o notas del proceso..." class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700 resize-none"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalIniciar')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="water-gradient text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-sky-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-cogs"></i> Iniciar Proceso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Close modal on outside click
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/60')) {
            event.target.classList.add('hidden');
            event.target.classList.remove('flex');
        }
    }
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
