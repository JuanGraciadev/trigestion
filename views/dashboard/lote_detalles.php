<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

if (!isset($_GET['id_lote'])) {
    header("Location: lotes.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Lote.php';

$database = new Database();
$db = $database->conectar();
$loteModel = new Lote($db);

$id_lote = $_GET['id_lote'];
$lote = $loteModel->obtenerLote($id_lote);

if (!$lote) {
    header("Location: lotes.php");
    exit;
}

$detalles = $loteModel->obtenerDetallesPorLote($id_lote);

$titulo = "Detalles del Lote " . $lote['codigo_lote'];
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="space-y-8">
    <!-- Header with Breadcrumbs & Add Button -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-600 text-2xl font-bold shadow-sm">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
                <a href="lotes.php" class="text-sm font-bold text-slate-400 hover:text-sky-500 transition-colors uppercase tracking-widest"><i class="fas fa-arrow-left mr-1"></i> Volver a Lotes</a>
                <h2 class="text-3xl font-black text-slate-800 mt-1">Lote: <span class="text-sky-600"><?= htmlspecialchars($lote['codigo_lote']) ?></span></h2>
            </div>
        </div>
        <button onclick="openModal('modalCrearDetalle')" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:shadow-lg hover:shadow-emerald-200 text-white px-8 py-4 rounded-2xl font-bold transition-all transform hover:-translate-y-1 flex items-center gap-2">
            <i class="fas fa-plus-circle"></i>
            Añadir Detalle
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

    <!-- Auto-open modal if there are no details yet (forcing sequence) -->
    <?php if(empty($detalles) && !isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openModal('modalCrearDetalle');
            });
        </script>
    <?php endif; ?>

    <!-- Table of details -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 text-slate-500 uppercase text-xs font-bold tracking-widest">
                    <tr>
                        <th class="px-8 py-5">Unidades</th>
                        <th class="px-8 py-5">Tipo de Envase</th>
                        <th class="px-8 py-5">Capacidad</th>
                        <th class="px-8 py-5">Proveedor</th>
                        <th class="px-8 py-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($detalles as $d): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-6">
                            <span class="font-black text-slate-800 text-lg"><?= htmlspecialchars($d['unidades']) ?></span>
                            <span class="text-xs text-slate-400 font-bold ml-1">uds</span>
                        </td>
                        <td class="px-8 py-6 text-slate-600 font-bold">
                            <?= htmlspecialchars($d['tipo_envase']) ?>
                        </td>
                        <td class="px-8 py-6 text-slate-600 font-bold">
                            <?= htmlspecialchars($d['capacidad']) ?>
                        </td>
                        <td class="px-8 py-6 text-slate-600">
                            <?= htmlspecialchars($d['proveedor']) ?>
                        </td>
                        <td class="px-8 py-6 text-center whitespace-nowrap">
                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($d)) ?>)" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-600 hover:bg-amber-50 transition-all flex items-center justify-center mx-auto" title="Editar">
                                <i class="fas fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($detalles)): ?>
                <div class="p-20 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-clipboard-list text-3xl"></i>
                    </div>
                    <p class="text-slate-400 font-medium">Este lote aún no tiene detalles registrados.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Crear Detalle -->
<div id="modalCrearDetalle" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-fade-in my-8">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-emerald-50/50">
            <div>
                <h3 class="text-2xl font-bold text-emerald-800">Paso 2: Añadir Detalle</h3>
                <p class="text-sm text-emerald-600/70">Ingresa la información específica de los envases para el lote <?= htmlspecialchars($lote['codigo_lote']) ?>.</p>
            </div>
            <button onclick="closeModal('modalCrearDetalle')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-red-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/LoteController.php?accion=crearDetalle" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id_lote" value="<?= $id_lote ?>">
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Unidades</label>
                    <input type="number" name="unidades" required min="1" placeholder="Ej. 100" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Tipo de Envase</label>
                    <input type="text" name="tipo_envase" required placeholder="Ej. Garrafón" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Capacidad</label>
                    <input type="text" name="capacidad" required placeholder="Ej. 20 Litros" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Proveedor</label>
                    <input type="text" name="proveedor" required placeholder="Nombre del proveedor" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700">
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCrearDetalle')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-emerald-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Detalle
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Detalle -->
<div id="modalEditarDetalle" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-fade-in my-8">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-amber-50/50">
            <div>
                <h3 class="text-2xl font-bold text-amber-800">Editar Detalle</h3>
            </div>
            <button onclick="closeModal('modalEditarDetalle')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-red-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/LoteController.php?accion=editarDetalle" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id_lote" value="<?= $id_lote ?>">
            <input type="hidden" name="id_detalles" id="edit_id_detalles">
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Unidades</label>
                    <input type="number" name="unidades" id="edit_unidades" required min="1" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all font-bold text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Tipo de Envase</label>
                    <input type="text" name="tipo_envase" id="edit_tipo_envase" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all font-bold text-slate-700">
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Capacidad</label>
                    <input type="text" name="capacidad" id="edit_capacidad" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all font-bold text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Proveedor</label>
                    <input type="text" name="proveedor" id="edit_proveedor" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all text-slate-700">
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditarDetalle')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="bg-gradient-to-r from-amber-500 to-amber-600 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-amber-200 transition-all transform active:scale-95 flex items-center gap-2">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    function openEditModal(detalle) {
        document.getElementById('edit_id_detalles').value = detalle.id_detalles;
        document.getElementById('edit_unidades').value = detalle.unidades;
        document.getElementById('edit_tipo_envase').value = detalle.tipo_envase;
        document.getElementById('edit_capacidad').value = detalle.capacidad;
        document.getElementById('edit_proveedor').value = detalle.proveedor;
        openModal('modalEditarDetalle');
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/60')) {
            event.target.classList.add('hidden');
            event.target.classList.remove('flex');
        }
    }
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
