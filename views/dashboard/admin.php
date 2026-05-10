<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

if ($_SESSION['usuario']['id_rol'] != 1) {
    if ($_SESSION['usuario']['id_rol'] == 2) {
        header("Location: trabajador.php");
    } else {
        header("Location: cliente.php");
    }
    exit;
}
require_once __DIR__. '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

$database = new Database();
$db = $database->conectar();
$usuarioModel = new Usuario($db);
$usuarios = $usuarioModel->obtenerTodos();
$roles = $usuarioModel->obtenerRoles();

// Calculate stats
$total_usuarios = count($usuarios);
$usuarios_activos = count(array_filter($usuarios, function($u) { return ($u['estado'] ?? 1) == 1; }));
$total_admins = count(array_filter($usuarios, function($u) { return $u['id_rol'] == 1; }));
$total_clientes = count(array_filter($usuarios, function($u) { return $u['id_rol'] == 3; }));

$titulo = "Panel de Administración";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>


<div class="max-w-[1400px] mx-auto space-y-10 pb-12">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 animate-fade-in-up">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl text-white shadow-lg shadow-indigo-200">
                    <i class="fas fa-shield-halved text-xl"></i>
                </div>
                <h2 class="text-4xl font-extrabold text-slate-800 tracking-tight outfit-font">Centro de <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Mando</span></h2>
            </div>
            <p class="text-slate-500 text-lg font-medium ml-1">Bienvenido al panel principal. Vista general del sistema y usuarios.</p>
        </div>
        <button onclick="openModal('modalCrear')" class="btn-primary text-white px-8 py-4 rounded-2xl font-bold flex items-center justify-center gap-3 w-full md:w-auto outfit-font tracking-wide">
            <i class="fas fa-user-plus text-lg"></i>
            <span>Añadir Usuario</span>
        </button>
    </div>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
                    title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
                    text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Continuar',
                    customClass: { popup: 'rounded-[2rem] premium-shadow' }
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <!-- KPI Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Card 1 -->
        <div class="stat-card bg-white rounded-[2rem] p-7 border border-slate-100 premium-shadow relative group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out opacity-60 z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center text-indigo-600 border border-indigo-200/50">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <span class="px-3 py-1.5 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-full border border-indigo-100 flex items-center gap-1">
                        <i class="fas fa-arrow-trend-up"></i> Total
                    </span>
                </div>
                <div>
                    <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-1 outfit-font">Usuarios Totales</h3>
                    <div class="flex items-baseline gap-2">
                        <p class="text-4xl font-black text-slate-800 outfit-font"><?= number_format($total_usuarios) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card bg-white rounded-[2rem] p-7 border border-slate-100 premium-shadow relative group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out opacity-60 z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 border border-emerald-200/50">
                        <i class="fas fa-user-check text-2xl"></i>
                    </div>
                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-full border border-emerald-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Activos
                    </span>
                </div>
                <div>
                    <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-1 outfit-font">Cuentas Activas</h3>
                    <p class="text-4xl font-black text-slate-800 outfit-font"><?= number_format($usuarios_activos) ?></p>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card bg-white rounded-[2rem] p-7 border border-slate-100 premium-shadow relative group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-purple-50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out opacity-60 z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center text-purple-600 border border-purple-200/50">
                        <i class="fas fa-user-tie text-2xl"></i>
                    </div>
                    <span class="px-3 py-1.5 bg-purple-50 text-purple-600 text-xs font-bold rounded-full border border-purple-100">
                        Staff
                    </span>
                </div>
                <div>
                    <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-1 outfit-font">Administradores</h3>
                    <p class="text-4xl font-black text-slate-800 outfit-font"><?= number_format($total_admins) ?></p>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="stat-card bg-white rounded-[2rem] p-7 border border-slate-100 premium-shadow relative group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-amber-50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out opacity-60 z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center text-amber-600 border border-amber-200/50">
                        <i class="fas fa-briefcase text-2xl"></i>
                    </div>
                    <span class="px-3 py-1.5 bg-amber-50 text-amber-600 text-xs font-bold rounded-full border border-amber-100">
                        Externos
                    </span>
                </div>
                <div>
                    <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-1 outfit-font">Clientes Registrados</h3>
                    <p class="text-4xl font-black text-slate-800 outfit-font"><?= number_format($total_clientes) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="bg-white rounded-[2.5rem] premium-shadow overflow-hidden border border-slate-100 animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800 outfit-font">Directorio de Usuarios</h3>
                <p class="text-sm text-slate-500 mt-1 font-medium">Gestión completa de accesos y permisos.</p>
            </div>
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" placeholder="Buscar usuario..." class="pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm w-full sm:w-80 font-medium shadow-sm">
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead class="bg-white text-slate-400 uppercase text-[11px] font-bold tracking-widest border-b border-slate-100 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-8 py-6">Perfil de Usuario</th>
                        <th class="px-8 py-6">Información de Contacto</th>
                        <th class="px-8 py-6">Documento</th>
                        <th class="px-8 py-6">Dirección</th>
                        <th class="px-8 py-6">Rol y Estado</th>
                        <th class="px-8 py-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-slate-50/30" id="tablaUsuarios">
                    <?php foreach ($usuarios as $u): ?>
                    <tr class="table-row-hover group usuario-row">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-5">
                                <div class="relative">
                                    <div class="w-14 h-14 rounded-[1.2rem] bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 flex items-center justify-center font-bold text-xl shadow-sm border border-slate-200/50 group-hover:from-indigo-100 group-hover:to-purple-100 group-hover:text-indigo-700 transition-colors">
                                        <?= strtoupper(substr($u['nombres'], 0, 1)) ?>
                                    </div>
                                    <?php if (($u['estado'] ?? 1) == 1): ?>
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 border-[3px] border-white rounded-full shadow-sm"></div>
                                    <?php else: ?>
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-slate-300 border-[3px] border-white rounded-full shadow-sm"></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-base outfit-font"><?= htmlspecialchars($u['nombres']) ?></div>
                                    <div class="text-xs text-slate-400 font-semibold mt-0.5">ID: #<?= str_pad($u['id_usuario'], 4, '0', STR_PAD_LEFT) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-slate-700 font-semibold text-sm flex items-center gap-2.5">
                                <i class="fas fa-envelope text-slate-400 w-4 text-center"></i> <?= htmlspecialchars($u['email']) ?>
                            </div>
                            <div class="text-xs text-slate-500 font-medium flex items-center gap-2.5 mt-2">
                                <i class="fas fa-phone text-slate-400 w-4 text-center"></i> <?= htmlspecialchars($u['telefono']) ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-600 shadow-sm group-hover:border-indigo-200 group-hover:text-indigo-600 transition-colors">
                                <i class="far fa-id-card text-slate-400 group-hover:text-indigo-400 transition-colors"></i>
                                <?= htmlspecialchars($u['documento_numero']) ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm text-slate-500 font-medium truncate max-w-[200px]" title="<?= htmlspecialchars($u['direccion']) ?>">
                                <i class="fas fa-map-marker-alt text-slate-400 mr-1.5"></i> <?= htmlspecialchars($u['direccion']) ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col items-start gap-2.5">
                                <?php 
                                    $rolColor = 'bg-slate-100 text-slate-700 border-slate-200';
                                    if ($u['id_rol'] == 1) $rolColor = 'bg-purple-50 text-purple-700 border-purple-200';
                                    if ($u['id_rol'] == 2) $rolColor = 'bg-sky-50 text-sky-700 border-sky-200';
                                    if ($u['id_rol'] == 3) $rolColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                ?>
                                <span class="status-badge inline-block px-3.5 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-widest border <?= $rolColor ?>">
                                    <?= htmlspecialchars($u['rol_nombre'] ?? 'Sin Rol') ?>
                                </span>
                                
                                <?php if (($u['estado'] ?? 1) == 1): ?>
                                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5 bg-emerald-50 px-2 py-1 rounded-md"><i class="fas fa-check-circle"></i> Activo</span>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-slate-500 flex items-center gap-1.5 bg-slate-100 px-2 py-1 rounded-md"><i class="fas fa-ban"></i> Suspendido</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-3 opacity-40 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" class="w-11 h-11 rounded-[14px] bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-100 transition-all flex items-center justify-center transform hover:-translate-y-1" title="Editar Perfil">
                                    <i class="fas fa-pen text-[15px]"></i>
                                </button>
                                
                                <a href="../../controllers/AdminUsuarioController.php?accion=toggleEstado&id=<?= $u['id_usuario'] ?>&estado=<?= $u['estado'] ?? 1 ?>" 
                                   class="w-11 h-11 rounded-[14px] bg-white border border-slate-200 transition-all flex items-center justify-center transform hover:-translate-y-1 hover:shadow-lg <?= ($u['estado'] ?? 1) == 1 ? 'text-slate-500 hover:text-rose-600 hover:border-rose-300 hover:shadow-rose-100' : 'text-slate-500 hover:text-emerald-600 hover:border-emerald-300 hover:shadow-emerald-100' ?>" 
                                   title="<?= ($u['estado'] ?? 1) == 1 ? 'Suspender Cuenta' : 'Activar Cuenta' ?>">
                                    <i class="fas <?= ($u['estado'] ?? 1) == 1 ? 'fa-user-lock' : 'fa-user-check' ?> text-[15px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($usuarios)): ?>
                <div class="p-24 text-center bg-white">
                    <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-300 border border-slate-100">
                        <i class="fas fa-users-slash text-4xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 outfit-font">Sin Registros</h4>
                    <p class="text-slate-500 font-medium">Aún no hay usuarios registrados en la plataforma.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div id="modalCrear" class="fixed inset-0 bg-slate-900/40 hidden z-50 flex items-center justify-center p-4 backdrop-blur-md">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden animate-fade-in border border-white/20">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800 outfit-font">Nuevo Usuario</h3>
                <p class="text-sm text-slate-500 font-medium mt-1">Registra un nuevo integrante en el sistema.</p>
            </div>
            <button onclick="closeModal('modalCrear')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-rose-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/AdminUsuarioController.php?accion=crear" method="POST" class="p-10 space-y-8">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombres Completos</label>
                    <input type="text" name="nombres" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dirección</label>
                    <input type="text" name="direccion" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo Electrónico</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" name="email" required class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Contraseña</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" name="password" required class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rol del Usuario</label>
                    <select name="id_rol" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700 appearance-none cursor-pointer">
                        <option value="">Seleccione...</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCrear')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="btn-primary text-white px-10 py-4 rounded-2xl font-bold outfit-font tracking-wide">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditar" class="fixed inset-0 bg-slate-900/40 hidden z-50 flex items-center justify-center p-4 backdrop-blur-md">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden animate-fade-in border border-white/20">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-bold text-slate-800 outfit-font">Editar Perfil</h3>
                <p class="text-sm text-slate-500 font-medium mt-1">Modifica la información del usuario seleccionado.</p>
            </div>
            <button onclick="closeModal('modalEditar')" class="w-10 h-10 rounded-full bg-white text-slate-400 hover:text-rose-500 hover:rotate-90 transition-all border border-slate-100 shadow-sm flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="../../controllers/AdminUsuarioController.php?accion=editar" method="POST" class="p-10 space-y-8">
            <input type="hidden" name="id_usuario" id="edit_id_usuario">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombres Completos</label>
                    <input type="text" name="nombres" id="edit_nombres" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dirección</label>
                    <input type="text" name="direccion" id="edit_direccion" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo Electrónico (No editable)</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" id="edit_email" readonly class="w-full pl-12 pr-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500 font-medium outline-none cursor-not-allowed">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nueva Contraseña (opcional)</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" name="password" placeholder="••••••••" class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rol Asignado</label>
                    <select name="id_rol" id="edit_rol" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700 cursor-pointer appearance-none">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditar')" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="btn-primary text-white px-10 py-4 rounded-2xl font-bold outfit-font tracking-wide">Actualizar Datos</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.usuario-row');
        
        rows.forEach(row => {
            const content = row.innerText.toLowerCase();
            if (content.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

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

    function openEditModal(usuario) {
        document.getElementById('edit_id_usuario').value = usuario.id_usuario;
        document.getElementById('edit_nombres').value = usuario.nombres;
        document.getElementById('edit_direccion').value = usuario.direccion;
        document.getElementById('edit_email').value = usuario.email;
        document.getElementById('edit_rol').value = usuario.id_rol;
        openModal('modalEditar');
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/40')) {
            event.target.classList.add('hidden');
            event.target.classList.remove('flex');
        }
    }
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>