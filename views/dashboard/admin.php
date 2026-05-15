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
require_once __DIR__ . '/../../models/usuario.php';

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


<!-- Background Elements -->
<div class="bg-blobs">
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="blob-3"></div>
</div>

<div class="space-y-10 font-outfit relative z-10 pb-12">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 animate-fade-in-up">
        <div>
            <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-sm mb-4 shadow-sm">
                <i class="fas fa-shield-halved mr-2"></i>Control Total
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-slate-800 tracking-tight leading-tight">
                Gestión de<br class="hidden md:block"/>
                <span class="premium-gradient-text">Usuarios</span>
            </h1>
            <p class="text-slate-500 mt-3 text-lg font-medium max-w-xl">Administra accesos, roles y permisos de todos los integrantes del sistema en tiempo real.</p>
        </div>
        <button onclick="openModal('modalCrear')"
            class="premium-gradient text-white px-8 py-5 rounded-[1.5rem] font-bold shadow-[0_10px_40px_rgba(79,70,229,0.4)] transition-all transform hover:-translate-y-2 hover:shadow-[0_15px_50px_rgba(225,29,72,0.5)] flex items-center gap-3 overflow-hidden relative group w-full md:w-auto justify-center">
            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 relative z-10 shrink-0">
                <i class="fas fa-user-plus text-xl"></i>
            </div>
            <div class="relative z-10 text-left">
                <div class="text-xs text-white/80 uppercase tracking-wider font-bold">Nuevo Registro</div>
                <div class="text-lg">Añadir Usuario</div>
            </div>
        </button>
    </div>

    <!-- ── SweetAlert ──────────────────────────────────────────────────────── -->
    <?php if (isset($_SESSION['alert'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertIcon  = '<?= $_SESSION['alert']['icon'] ?>',
                  alertTitle = '<?= addslashes(htmlspecialchars($_SESSION['alert']['title'])) ?>',
                  alertText  = '<?= addslashes(htmlspecialchars($_SESSION['alert']['text'])) ?>';

            const iconConfig = {
                success: { gradient: 'linear-gradient(135deg,#10b981,#059669)', shadow: '0 20px 60px rgba(16,185,129,.35)', iconBg: '#ecfdf5', iconColor: '#10b981' },
                error:   { gradient: 'linear-gradient(135deg,#ef4444,#dc2626)', shadow: '0 20px 60px rgba(239,68,68,.35)',  iconBg: '#fef2f2', iconColor: '#ef4444' },
                warning: { gradient: 'linear-gradient(135deg,#f59e0b,#d97706)', shadow: '0 20px 60px rgba(245,158,11,.35)', iconBg: '#fffbeb', iconColor: '#f59e0b' },
                info:    { gradient: 'linear-gradient(135deg,#3b82f6,#2563eb)', shadow: '0 20px 60px rgba(59,130,246,.35)',  iconBg: '#eff6ff', iconColor: '#3b82f6' },
            };
            const cfg = iconConfig[alertIcon] || iconConfig.info;

            Swal.fire({
                title: alertTitle, text: alertText, icon: alertIcon,
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Entendido',
                confirmButtonColor: 'transparent',
                customClass: { popup: 'swal-premium-popup', confirmButton: 'swal-premium-btn' },
                didOpen: (popup) => {
                    popup.style.cssText = `border-radius:2rem;padding:2.5rem 2rem 2rem;box-shadow:${cfg.shadow};border:1px solid rgba(255,255,255,.8);background:rgba(255,255,255,.95);backdrop-filter:blur(20px);font-family:'Outfit',sans-serif`;
                    const icon = popup.querySelector('.swal2-icon');
                    if (icon) { icon.style.cssText = `border:none;width:80px;height:80px;border-radius:1.5rem;background:${cfg.iconBg};margin:0 auto 1.5rem;display:flex;align-items:center;justify-content:center`; }
                    const lines = popup.querySelectorAll('.swal2-success-line-tip,.swal2-success-line-long');
                    lines.forEach(l => l.style.background = cfg.iconColor);
                    const ring = popup.querySelector('.swal2-success-ring');
                    if (ring) ring.style.border = `4px solid ${cfg.iconColor}40`;
                    const title = popup.querySelector('.swal2-title');
                    if (title) title.style.cssText = 'font-size:1.6rem;font-weight:800;color:#1e293b;margin:0 0 .5rem;padding:0';
                    const text = popup.querySelector('.swal2-html-container,.swal2-content');
                    if (text) text.style.cssText = 'font-size:.95rem;color:#64748b;margin:0 0 1.5rem;padding:0';
                    const btn = popup.querySelector('.swal2-confirm');
                    if (btn) { btn.style.cssText = `background:${cfg.gradient};border:none;border-radius:.875rem;padding:.75rem 2rem;font-size:.95rem;font-weight:700;color:#fff;box-shadow:${cfg.shadow.replace('60px','25px')};cursor:pointer;transition:transform .15s`; btn.onmouseenter=()=>{btn.style.transform='translateY(-2px)'}; btn.onmouseleave=()=>{btn.style.transform=''}; }
                }
            });
        });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>

    <!-- ── KPI Cards ───────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in-up delay-100">
        <?php
        $total_trabajadores = count(array_filter($usuarios, function($u){ return $u['id_rol'] == 2; }));
        $tarjetas = [
            ['Usuarios Totales',     $total_usuarios,      'fa-users',        'from-indigo-400 to-purple-500',  'text-indigo-600',  'bg-indigo-50',  'shadow-[0_10px_30px_rgba(99,102,241,0.15)]'],
            ['Cuentas Activas',      $usuarios_activos,    'fa-user-check',   'from-emerald-400 to-teal-500',   'text-emerald-600', 'bg-emerald-50', 'shadow-[0_10px_30px_rgba(16,185,129,0.15)]'],
            ['Administradores',      $total_admins,        'fa-user-shield',  'from-purple-400 to-violet-500',  'text-purple-600',  'bg-purple-50',  'shadow-[0_10px_30px_rgba(139,92,246,0.15)]'],
            ['Clientes Registrados', $total_clientes,      'fa-user-tag',     'from-amber-400 to-orange-500',   'text-amber-600',   'bg-amber-50',   'shadow-[0_10px_30px_rgba(245,158,11,0.15)]'],
        ];
        foreach ($tarjetas as [$label, $val, $ico, $grad, $txt, $bg, $shadow]):
        ?>
        <div class="stat-card glass-card rounded-[2.5rem] p-7 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br <?= $grad ?> opacity-10 rounded-full blur-[30px] group-hover:opacity-30 group-hover:scale-150 transition-all duration-700"></div>
            <div class="flex justify-between items-start mb-6 relative z-10">
                <div class="w-16 h-16 rounded-[1.2rem] <?= $bg ?> flex items-center justify-center <?= $txt ?> text-3xl border border-white/80 <?= $shadow ?> transform group-hover:rotate-6 group-hover:scale-110 transition-all duration-500">
                    <i class="fas <?= $ico ?>"></i>
                </div>
            </div>
            <div class="relative z-10">
                <div class="text-5xl font-black text-slate-800 tracking-tighter mb-2 group-hover:translate-x-1 transition-transform"><?= $val ?></div>
                <div class="text-slate-500 font-bold text-xs uppercase tracking-[0.2em] group-hover:text-slate-800 transition-colors"><?= $label ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Tabla de Usuarios ──────────────────────────────────────────────── -->
    <div class="animate-fade-in-up delay-200">

        <!-- Toolbar -->
        <div class="glass-panel rounded-[2rem] p-6 mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[1.5rem] bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30 shrink-0">
                    <i class="fas fa-users-gear"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Directorio de <span class="premium-gradient-text">Usuarios</span></h2>
                    <p class="text-slate-500 text-sm font-medium mt-1">Gestión completa de accesos, roles y permisos</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                <!-- Filtros de rol -->
                <div class="flex gap-2 p-1.5 bg-slate-100/60 rounded-2xl backdrop-blur-md border border-white shadow-inner">
                    <button onclick="filtrarUsuarios('todos')" class="filter-usr active px-4 py-2 rounded-xl font-bold text-sm transition-all bg-white text-indigo-600 shadow-sm border border-slate-100" data-filter="todos">Todos</button>
                    <button onclick="filtrarUsuarios('1')" class="filter-usr px-4 py-2 rounded-xl font-bold text-sm transition-all text-slate-500 hover:bg-white hover:text-purple-600 hover:shadow-sm" data-filter="1">Admin</button>
                    <button onclick="filtrarUsuarios('2')" class="filter-usr px-4 py-2 rounded-xl font-bold text-sm transition-all text-slate-500 hover:bg-white hover:text-sky-600 hover:shadow-sm" data-filter="2">Trabajador</button>
                    <button onclick="filtrarUsuarios('3')" class="filter-usr px-4 py-2 rounded-xl font-bold text-sm transition-all text-slate-500 hover:bg-white hover:text-amber-600 hover:shadow-sm" data-filter="3">Cliente</button>
                </div>
                <!-- Buscador -->
                <div class="relative w-full sm:w-auto">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario..."
                        class="pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm w-full sm:w-64 font-medium shadow-sm">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto custom-scrollbar relative min-h-[400px] px-2 pb-10">
            <table class="w-full text-left table-separated min-w-[900px]" id="tablaUsuarios">
                <thead class="text-slate-400 text-xs font-black uppercase tracking-[0.15em] sticky top-0 z-20">
                    <tr>
                        <th class="px-8 py-4">Perfil</th>
                        <th class="px-8 py-4">Contacto</th>
                        <th class="px-8 py-4">Documento</th>
                        <th class="px-8 py-4">Dirección</th>
                        <th class="px-8 py-4 text-center">Rol & Estado</th>
                        <th class="px-8 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                <?php foreach ($usuarios as $u):
                    $estado   = ($u['estado'] ?? 1) == 1;
                    $rolId    = $u['id_rol'];
                    $rolNombre = htmlspecialchars($u['rol_nombre'] ?? 'Sin Rol');
                    $rolStyles = [
                        1 => ['bg-purple-100 text-purple-700 border-purple-200', 'fa-user-shield', 'from-purple-400 to-violet-500'],
                        2 => ['bg-sky-100 text-sky-700 border-sky-200',           'fa-hard-hat',    'from-sky-400 to-blue-500'],
                        3 => ['bg-amber-100 text-amber-700 border-amber-200',     'fa-user-tag',    'from-amber-400 to-orange-500'],
                    ];
                    [$rolBadge, $rolIco, $rolGrad] = $rolStyles[$rolId] ?? ['bg-slate-100 text-slate-600 border-slate-200', 'fa-user', 'from-slate-400 to-slate-500'];
                    $avatarGrad = [
                        1 => 'from-purple-400 to-violet-500',
                        2 => 'from-sky-400 to-blue-500',
                        3 => 'from-amber-400 to-orange-500',
                    ][$rolId] ?? 'from-slate-400 to-slate-500';
                ?>
                <tr class="table-row-card group usuario-row cursor-default" data-rol="<?= $rolId ?>">
                    <!-- Perfil -->
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-[1.2rem] bg-gradient-to-br <?= $avatarGrad ?> flex items-center justify-center font-black text-white text-xl shadow-lg transform group-hover:rotate-6 group-hover:scale-110 transition-all duration-500">
                                    <?= strtoupper(substr($u['nombres'], 0, 1)) ?>
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white shadow-sm <?= $estado ? 'bg-emerald-500' : 'bg-slate-300' ?>"></div>
                            </div>
                            <div>
                                <div class="font-extrabold text-slate-800 text-base tracking-tight"><?= htmlspecialchars($u['nombres']) ?></div>
                                <div class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-lg bg-slate-100/80 border border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <i class="fas fa-receipt text-indigo-400/60"></i> #<?= str_pad($u['id_usuario'], 4, '0', STR_PAD_LEFT) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <!-- Contacto -->
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2.5 p-2 rounded-xl bg-white/60 border border-slate-100 group-hover:bg-white group-hover:border-indigo-100 transition-all">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0">
                                    <i class="fas fa-envelope text-xs"></i>
                                </div>
                                <span class="text-sm font-semibold text-slate-700 truncate max-w-[180px]"><?= htmlspecialchars($u['email']) ?></span>
                            </div>
                            <div class="flex items-center gap-2.5 p-2 rounded-xl bg-white/60 border border-slate-100 group-hover:bg-white group-hover:border-indigo-100 transition-all">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0">
                                    <i class="fas fa-phone text-xs"></i>
                                </div>
                                <span class="text-sm font-semibold text-slate-600"><?= htmlspecialchars($u['telefono'] ?? '—') ?></span>
                            </div>
                        </div>
                    </td>
                    <!-- Documento -->
                    <td class="px-8 py-6">
                        <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-600 shadow-sm group-hover:border-indigo-200 group-hover:text-indigo-700 group-hover:shadow-[0_5px_15px_rgba(79,70,229,0.1)] transition-all">
                            <i class="far fa-id-card text-slate-400 group-hover:text-indigo-400 transition-colors"></i>
                            <?= htmlspecialchars($u['documento_numero'] ?? '—') ?>
                        </div>
                    </td>
                    <!-- Dirección -->
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2.5 max-w-[200px]">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 flex items-center justify-center text-rose-400 shrink-0">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <span class="text-sm text-slate-500 font-medium truncate" title="<?= htmlspecialchars($u['direccion'] ?? '') ?>"><?= htmlspecialchars($u['direccion'] ?? '—') ?></span>
                        </div>
                    </td>
                    <!-- Rol & Estado -->
                    <td class="px-8 py-6 text-center">
                        <div class="flex flex-col items-center gap-2.5">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-[1rem] text-[11px] font-black uppercase tracking-[0.1em] border-2 <?= $rolBadge ?> shadow-sm">
                                <i class="fas <?= $rolIco ?>"></i><?= $rolNombre ?>
                            </span>
                            <?php if ($estado): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-black uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Activo
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 text-[11px] font-black uppercase tracking-wider">
                                <i class="fas fa-ban text-xs"></i> Suspendido
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <!-- Acciones -->
                    <td class="px-8 py-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)"
                                class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-white border-2 border-slate-100 text-slate-400 hover:text-indigo-600 hover:border-indigo-300 hover:shadow-[0_8px_20px_rgba(79,70,229,0.2)] transition-all transform hover:scale-110 hover:-translate-y-1"
                                title="Editar usuario">
                                <i class="fas fa-pen text-sm"></i>
                            </button>
                            <a href="../../controllers/AdminUsuarioController.php?accion=toggleEstado&id=<?= $u['id_usuario'] ?>&estado=<?= $u['estado'] ?? 1 ?>"
                               class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-white border-2 border-slate-100 transition-all transform hover:scale-110 hover:-translate-y-1 <?= $estado ? 'text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:shadow-[0_8px_20px_rgba(239,68,68,0.2)]' : 'text-slate-400 hover:text-emerald-600 hover:border-emerald-300 hover:shadow-[0_8px_20px_rgba(16,185,129,0.2)]' ?>"
                               title="<?= $estado ? 'Suspender cuenta' : 'Activar cuenta' ?>">
                                <i class="fas <?= $estado ? 'fa-user-lock' : 'fa-user-check' ?> text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" class="px-8 py-24 text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-300 shadow-inner">
                            <i class="fas fa-users-slash text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-700 mb-1">Sin usuarios registrados</h3>
                        <p class="text-slate-400 font-medium">No se encontraron usuarios en el sistema.</p>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══ MODAL Crear Usuario ══ -->
<div id="modalCrear" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="glass-card rounded-[2.5rem] w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300 border border-white shadow-[0_30px_60px_rgba(0,0,0,0.15)]" id="modalCrearContent">
        <div class="relative p-8 border-b border-slate-100 overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white text-xl">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-white tracking-tight">Nuevo Usuario</h3>
                        <p class="text-white/70 text-sm font-medium mt-0.5">Registra un nuevo integrante en el sistema</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalAnim('modalCrear','modalCrearContent')" class="w-10 h-10 rounded-full bg-white/20 text-white hover:bg-white hover:text-rose-500 transition-all border border-white/30 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <form action="../../controllers/AdminUsuarioController.php?accion=crear" method="POST" class="p-8 space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nombres Completos</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-user text-sm"></i></span>
                        <input type="text" name="nombres" required placeholder="Ej. Juan Pérez" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Dirección</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-map-marker-alt text-sm"></i></span>
                        <input type="text" name="direccion" required placeholder="Calle, ciudad..." class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300">
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Correo Electrónico</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="email" name="email" required placeholder="correo@ejemplo.com" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Contraseña</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Rol del Usuario</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 z-10"><i class="fas fa-user-tag text-sm"></i></span>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10 text-sm"></i>
                        <select name="id_rol" required class="w-full pl-11 pr-10 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700 appearance-none cursor-pointer">
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalAnim('modalCrear','modalCrearContent')" class="px-6 py-3.5 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="premium-gradient text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL Editar Usuario ══ -->
<div id="modalEditar" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="glass-card rounded-[2.5rem] w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300 border border-white shadow-[0_30px_60px_rgba(0,0,0,0.15)]" id="modalEditarContent">
        <div class="relative p-8 border-b border-slate-100 overflow-hidden bg-gradient-to-r from-slate-800 to-slate-700">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 text-white text-xl">
                        <i class="fas fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-white tracking-tight">Editar Perfil</h3>
                        <p class="text-white/60 text-sm font-medium mt-0.5" id="editSubtitle">Modifica la información del usuario</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalAnim('modalEditar','modalEditarContent')" class="w-10 h-10 rounded-full bg-white/10 text-white hover:bg-white hover:text-rose-500 transition-all border border-white/20 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <form action="../../controllers/AdminUsuarioController.php?accion=editar" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id_usuario" id="edit_id_usuario">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nombres Completos</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-user text-sm"></i></span>
                        <input type="text" name="nombres" id="edit_nombres" required class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Dirección</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-map-marker-alt text-sm"></i></span>
                        <input type="text" name="direccion" id="edit_direccion" required class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    Correo Electrónico <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 text-[10px] normal-case font-bold">No editable</span>
                </label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                    <input type="email" id="edit_email" readonly class="w-full pl-11 pr-4 py-3.5 bg-slate-100 border-2 border-slate-100 rounded-2xl text-slate-400 font-medium outline-none cursor-not-allowed">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        Nueva Contraseña <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 text-[10px] normal-case font-bold">Opcional</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" name="password" placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Rol Asignado</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 z-10"><i class="fas fa-user-tag text-sm"></i></span>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10 text-sm"></i>
                        <select name="id_rol" id="edit_rol" required class="w-full pl-11 pr-10 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 outline-none transition-all font-medium text-slate-700 cursor-pointer appearance-none">
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalAnim('modalEditar','modalEditarContent')" class="px-6 py-3.5 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="premium-gradient text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-floppy-disk"></i> Actualizar Datos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ─── Modal animations ────────────────────────────────────────────────────────
function openModal(id) {
    const modal   = document.getElementById(id);
    const content = document.getElementById(id + 'Content');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        if (content) content.style.transform = 'scale(1)';
    });
}

function closeModalAnim(modalId, contentId) {
    const modal   = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    modal.style.opacity = '0';
    if (content) content.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.opacity = '';
        if (content) content.style.transform = '';
    }, 300);
}

// ─── Open edit modal ─────────────────────────────────────────────────────────
function openEditModal(u) {
    document.getElementById('edit_id_usuario').value = u.id_usuario;
    document.getElementById('edit_nombres').value    = u.nombres;
    document.getElementById('edit_direccion').value  = u.direccion;
    document.getElementById('edit_email').value      = u.email;
    document.getElementById('edit_rol').value        = u.id_rol;
    document.getElementById('editSubtitle').textContent = 'Editando: ' + u.nombres;
    openModal('modalEditar');
}

// ─── Search ──────────────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.usuario-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});

// ─── Role filter ─────────────────────────────────────────────────────────────
function filtrarUsuarios(rol) {
    document.querySelectorAll('.filter-usr').forEach(btn => {
        const active = btn.dataset.filter === rol;
        btn.classList.toggle('bg-white',         active);
        btn.classList.toggle('text-indigo-600',  active);
        btn.classList.toggle('shadow-sm',        active);
        btn.classList.toggle('border',           active);
        btn.classList.toggle('border-slate-100', active);
        btn.classList.toggle('text-slate-500',   !active);
    });
    document.querySelectorAll('.usuario-row').forEach(row => {
        row.style.display = (rol === 'todos' || row.dataset.rol === rol) ? '' : 'none';
    });
}

// ─── Close on backdrop ───────────────────────────────────────────────────────
window.addEventListener('click', function(e) {
    ['modalCrear','modalEditar'].forEach(id => {
        if (e.target.id === id) closeModalAnim(id, id + 'Content');
    });
});
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>