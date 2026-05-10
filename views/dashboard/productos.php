<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    $redirect = isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == '2' ? 'trabajador.php' : '../usuarios/login.php';
    header("Location: $redirect");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';

$database = new Database();
$db = $database->conectar();
$productoModel = new Producto($db);
$categoriaModel = new Categoria($db);

$productos = $productoModel->obtenerTodos();
$categorias = $categoriaModel->obtenerTodas();

$titulo = "Gestión de Productos";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>



<div class="space-y-10">
    <!-- Main Management Card -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
        <div class="p-8 md:p-10 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gradient-to-r from-slate-50 to-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-sky-500/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Catálogo Administrador</span>
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-800 tracking-tight outfit-font">Catálogo de <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-indigo-600">Productos</span></h2>
                <p class="text-slate-500 mt-2 font-medium">Administra el stock, precios y detalles de los productos de tu negocio.</p>
            </div>
            <button onclick="openModal('modalCrear')" class="relative z-10 bg-gradient-to-r from-sky-500 to-indigo-600 hover:shadow-[0_10px_25px_-5px_rgba(14,165,233,0.4)] text-white px-8 py-4 rounded-[1.25rem] font-bold transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 outfit-font tracking-wide">
                <i class="fas fa-plus"></i>
                Nuevo Producto
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

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($productos as $p): ?>
            <div class="bg-white rounded-[2rem] border border-slate-100 overflow-hidden hover:shadow-[0_20px_40px_-12px_rgba(0,0,0,0.1)] hover:-translate-y-1 transition-all duration-400 group flex flex-col relative">
                <div class="h-56 bg-slate-50/50 relative overflow-hidden flex items-center justify-center p-6 border-b border-slate-50">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(14,165,233,0.05)_0,transparent_70%)] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    
                    <?php if(!empty($p['img'])): ?>
                        <img src="<?= htmlspecialchars($p['img']) ?>" alt="Producto" class="w-full h-full object-contain relative z-10 group-hover:scale-110 transition-transform duration-700 ease-out drop-shadow-xl">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-sky-50 flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform duration-700 border border-sky-100">
                            <i class="fas fa-bottle-water text-5xl text-sky-300"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="absolute top-4 right-4 flex flex-col items-end gap-2 z-20">
                        <?php if (($p['estado'] ?? 1) == 1): ?>
                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm backdrop-blur-md">
                                Activo
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1.5 bg-slate-100 text-slate-500 border border-slate-200 text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm backdrop-blur-md">
                                Inactivo
                            </span>
                        <?php endif; ?>
                        
                        <span class="px-3 py-1.5 bg-white/90 backdrop-blur-md text-sky-600 text-[11px] font-black rounded-full shadow-sm border border-slate-100 mt-1">
                            $<?= number_format($p['precio'], 2) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6 flex-1 flex flex-col bg-white">
                    <div class="text-[10px] font-bold text-indigo-500 mb-2 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        <?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin Categoría') ?>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-4 line-clamp-2 flex-1 outfit-font leading-tight group-hover:text-sky-600 transition-colors"><?= htmlspecialchars($p['nombre']) ?></h3>
                    
                    <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 mt-auto">
                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)" class="py-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 font-bold hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center justify-center gap-2 text-[13px] shadow-sm">
                            <i class="fas fa-pen"></i> Editar
                        </button>
                        
                        <a href="../../controllers/ProductoController.php?accion=toggleEstado&id=<?= $p['id_producto'] ?>&estado=<?= $p['estado'] ?? 1 ?>" class="py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold transition-all flex items-center justify-center gap-2 text-[13px] shadow-sm <?= ($p['estado'] ?? 1) == 1 ? 'text-rose-500 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600' : 'text-emerald-500 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-600' ?>" title="<?= ($p['estado'] ?? 1) == 1 ? 'Inhabilitar' : 'Habilitar' ?>">
                            <i class="fas <?= ($p['estado'] ?? 1) == 1 ? 'fa-ban' : 'fa-check-circle' ?>"></i>
                            <?= ($p['estado'] ?? 1) == 1 ? 'Inhabilitar' : 'Activar' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($productos)): ?>
                <div class="col-span-full p-20 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-sm">
                        <i class="fas fa-box-open text-3xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium text-lg">No hay productos registrados.</p>
                    <button onclick="openModal('modalCrear')" class="mt-4 text-sky-600 font-bold hover:underline">Crear mi primer producto</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Crear Producto -->
<div id="modalCrear" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden animate-fade-in my-8">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Nuevo Producto</h3>
                <p class="text-sm text-slate-500">Agrega un nuevo artículo a tu inventario.</p>
            </div>
            <button onclick="closeModal('modalCrear')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-red-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/ProductoController.php?accion=crear" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nombre de Producto</label>
                <input type="text" name="nombre" required placeholder="Ej. Garrafón 20L" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Precio ($)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">$</span>
                        <input type="number" name="precio" required min="0" step="0.01" placeholder="0.00" class="w-full pl-8 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Categoría</label>
                    <select name="id_categoria" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700 appearance-none">
                        <option value="">Seleccione...</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Imagen del Producto (Opcional)</label>
                <div class="upload-zone" id="createUploadZone" onclick="document.getElementById('create_img_file').click()">
                    <input type="file" name="img_file" id="create_img_file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="handleFileSelect(this, 'createUploadZone', 'createPreview')">
                    <img id="createPreview" class="preview-img" alt="Preview">
                    <button type="button" class="remove-btn" onclick="event.stopPropagation(); removeImage('createUploadZone', 'create_img_file', 'createPreview')">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="upload-placeholder">
                        <div class="w-16 h-16 bg-sky-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cloud-arrow-up text-2xl text-sky-500"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-600">Haz clic o arrastra una imagen aquí</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP o GIF • Máximo 5MB</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCrear')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="water-gradient text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-sky-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Producto -->
<div id="modalEditar" class="fixed inset-0 bg-slate-900/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden animate-fade-in my-8">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Editar Producto</h3>
                <p class="text-sm text-slate-500">Modifica los detalles del producto.</p>
            </div>
            <button onclick="closeModal('modalEditar')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-red-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/ProductoController.php?accion=editar" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="id_producto" id="edit_id_producto">
            <input type="hidden" name="img_actual" id="edit_img_actual">
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nombre de Producto</label>
                <input type="text" name="nombre" id="edit_nombre" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Precio ($)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">$</span>
                        <input type="number" name="precio" id="edit_precio" required min="0" step="0.01" class="w-full pl-8 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase ml-1">Categoría</label>
                    <select name="id_categoria" id="edit_id_categoria" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none transition-all text-slate-700 appearance-none">
                        <option value="">Seleccione...</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase ml-1">Imagen del Producto</label>
                <div class="upload-zone" id="editUploadZone" onclick="document.getElementById('edit_img_file').click()">
                    <input type="file" name="img_file" id="edit_img_file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="handleFileSelect(this, 'editUploadZone', 'editPreview')">
                    <img id="editPreview" class="preview-img" alt="Preview">
                    <button type="button" class="remove-btn" onclick="event.stopPropagation(); removeImage('editUploadZone', 'edit_img_file', 'editPreview')">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="upload-placeholder">
                        <div class="w-16 h-16 bg-sky-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cloud-arrow-up text-2xl text-sky-500"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-600">Haz clic o arrastra una nueva imagen</p>
                        <p class="text-xs text-slate-400 mt-1">Deja vacío para mantener la imagen actual</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditar')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="water-gradient text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-sky-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Actualizar
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

    function openEditModal(producto) {
        document.getElementById('edit_id_producto').value = producto.id_producto;
        document.getElementById('edit_nombre').value = producto.nombre;
        document.getElementById('edit_precio').value = producto.precio;
        document.getElementById('edit_id_categoria').value = producto.id_categoria;
        document.getElementById('edit_img_actual').value = producto.img || '';

        // Mostrar imagen actual si existe
        const zone = document.getElementById('editUploadZone');
        const preview = document.getElementById('editPreview');
        if (producto.img) {
            preview.src = producto.img;
            zone.classList.add('has-image');
        } else {
            preview.src = '';
            zone.classList.remove('has-image');
        }
        // Limpiar el file input
        document.getElementById('edit_img_file').value = '';

        openModal('modalEditar');
    }

    // ── Upload zone handlers ──
    function handleFileSelect(input, zoneId, previewId) {
        const zone = document.getElementById(zoneId);
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const file = input.files[0];
            // Validar tamaño
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'La imagen no debe superar 5MB.', confirmButtonColor: '#0ea5e9', customClass: { popup: 'rounded-[2rem]' } });
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                zone.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    }

    function removeImage(zoneId, inputId, previewId) {
        const zone = document.getElementById(zoneId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        input.value = '';
        preview.src = '';
        zone.classList.remove('has-image');
    }

    // Drag & drop support
    document.querySelectorAll('.upload-zone').forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
        });
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const fileInput = zone.querySelector('input[type="file"]');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    });
    
    // Close modal on outside click
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/60')) {
            event.target.classList.add('hidden');
            event.target.classList.remove('flex');
        }
    }
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
