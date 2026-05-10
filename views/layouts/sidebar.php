<?php
$id_rol = $usuario['id_rol'];
$nombreCompleto = $usuario['nombres'];
?>

<aside class="w-[280px] bg-[#090e17] text-slate-400 flex flex-col z-30 relative shadow-[10px_0_40px_-15px_rgba(0,0,0,0.5)] border-r border-white/5">
    <!-- Decals -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-sky-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 flex flex-col h-full">
        <!-- Branding -->
        <div class="h-24 flex items-center px-8 border-b border-white/5">
            <img src="../../img/triges.png" alt="Logo" class="h-10 w-auto brightness-0 invert opacity-90">
            <div class="ml-4">
                <div class="text-[10px] font-black text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-400 tracking-[0.2em] uppercase">MOOVA!</div>
                <div class="text-xl font-bold text-white leading-none tracking-wide outfit-font">TRIGESTION</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-8 px-5 space-y-1.5 custom-scrollbar">
        <div class="px-4 mb-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Menú Principal</div>
        
        <?php 
        $dashboard_url = 'admin.php';
        if ($id_rol == '2') $dashboard_url = 'trabajador.php';
        if ($id_rol == '3') $dashboard_url = 'cliente.php';
        ?>
        <a href="<?= $dashboard_url ?>" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-sky-600/10 text-sky-400 font-semibold border border-sky-600/20 transition-all">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($id_rol == '1'): ?>
            <a href="admin.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-users group-hover:text-sky-400"></i>
                <span>Gestión Usuarios</span>
            </a>
            
            <div class="pt-6 px-4 mb-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Operaciones</div>
            
            <a href="inventario_mp.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-boxes-stacked group-hover:text-sky-400"></i>
                <span>Inventario MP</span>
            </a>
            <a href="lotes.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-box-open group-hover:text-sky-400"></i>
                <span>Gestión de Lotes</span>
            </a>

            <a href="categorias.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-tags group-hover:text-sky-400"></i>
                <span>Categorías</span>
            </a>
            <a href="productos.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-tint group-hover:text-sky-400"></i>
                <span>Productos</span>
            </a>
            <a href="inventario_productos.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-cubes group-hover:text-emerald-400"></i>
                <span>Inventario Productos</span>
            </a>
            <a href="ventas.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group relative">
                <i class="fas fa-cart-shopping group-hover:text-sky-400"></i>
                <span>Ventas y Pedidos</span>
                <?php
                // Mostrar badge de pedidos pendientes
                try {
                    require_once __DIR__ . '/../../config/database.php';
                    require_once __DIR__ . '/../../models/Venta.php';
                    $__db = (new Database())->conectar();
                    $__vm = new Venta($__db);
                    $__pend = $__vm->contarPendientes();
                    if ($__pend > 0):
                ?>
                <span class="ml-auto bg-red-500 text-white text-xs font-black px-2 py-0.5 rounded-full min-w-[1.2rem] text-center animate-pulse">
                    <?= $__pend ?>
                </span>
                <?php endif; } catch(Exception $e) {} ?>
            </a>
            
            <div class="pt-6 px-4 mb-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Análisis</div>
            
            <a href="reportes.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-file-invoice-dollar group-hover:text-sky-400"></i>
                <span>Reportes Generales</span>
            </a>
        <?php endif; ?>

        <?php if ($id_rol == '2'): ?>
            <div class="pt-6 px-4 mb-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Área de Trabajo</div>
            
            <a href="produccion.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-industry group-hover:text-sky-400"></i>
                <span>Producción</span>
            </a>
            <a href="inventario_mp.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-boxes-stacked group-hover:text-sky-400"></i>
                <span>Inventario MP</span>
            </a>
            <a href="lotes.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-box-open group-hover:text-sky-400"></i>
                <span>Gestión de Lotes</span>
            </a>
            <a href="inventario_productos.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-cubes group-hover:text-emerald-400"></i>
                <span>Inventario Productos</span>
            </a>
        <?php endif; ?>

        <?php if ($id_rol == '3'): ?>
            <div class="pt-6 px-4 mb-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Mi Tienda</div>
            
            <a href="cliente.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-store group-hover:text-sky-400"></i>
                <span>Catálogo de Productos</span>
            </a>
            <a href="cliente_compras.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all group">
                <i class="fas fa-shopping-bag group-hover:text-sky-400"></i>
                <span>Mis Compras</span>
            </a>
        <?php endif; ?>

            <!-- Bottom Actions -->
            <div class="pt-8 mt-4 border-t border-white/5">
                <a href="../../controllers/AuthController.php?accion=logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all font-bold text-sm">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </nav>

        <!-- User Footer -->
        <div class="p-6 bg-[#06090f] border-t border-white/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-indigo-400 font-bold text-lg shadow-inner">
                    <?= strtoupper(substr($nombreCompleto, 0, 1)) ?>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="text-sm font-bold text-white truncate outfit-font"><?= htmlspecialchars($nombreCompleto) ?></div>
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mt-0.5"><?= $id_rol == '1' ? 'Administrador' : ($id_rol == '2' ? 'Trabajador' : 'Cliente') ?></div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Top Navbar Replacement and Content Wrapper -->
<div class="flex-1 flex flex-col min-h-screen relative overflow-hidden">
    <header class="h-24 glass-panel px-10 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-[28px] font-extrabold text-slate-800 tracking-tight outfit-font"><?= htmlspecialchars($titulo) ?></h1>
            <p class="text-sm text-slate-500 font-medium">Bienvenido al panel de control de MOOVA!</p>
        </div>

        <div class="flex items-center gap-6">
            <?php
            $__pendBell = 0;
            if ($id_rol == '1') {
                try {
                    require_once __DIR__ . '/../../config/database.php';
                    require_once __DIR__ . '/../../models/Venta.php';
                    $__dbBell = (new Database())->conectar();
                    $__vmBell = new Venta($__dbBell);
                    $__pendBell = $__vmBell->contarPendientes();
                } catch(Exception $e) {}
            }
            ?>
            <button class="w-11 h-11 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-300 hover:shadow-lg transition-all relative flex items-center justify-center" id="bellBtn"
                title="<?= $__pendBell > 0 ? htmlspecialchars("{$__pendBell} pedido(s) pendiente(s)") : 'Sin notificaciones' ?>">
                <i class="fas fa-bell text-[17px]"></i>
                <?php if ($__pendBell > 0): ?>
                <span class="absolute -top-1 -right-1 bg-gradient-to-r from-rose-500 to-red-600 text-white text-[10px] font-black rounded-full min-w-[20px] h-[20px] flex items-center justify-center border-2 border-white shadow-sm animate-pulse">
                    <?= $__pendBell ?>
                </span>
                <?php else: ?>
                <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-slate-300 rounded-full border border-white"></span>
                <?php endif; ?>
            </button>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bellBtn = document.getElementById('bellBtn');
                if(bellBtn) {
                    bellBtn.addEventListener('click', function() {
                        <?php if ($__pendBell > 0): ?>
                        Swal.fire({
                            icon: 'info',
                            title: '🔔 Pedidos Pendientes',
                            html: '<strong style="color:#4f46e5;font-size:2.5rem"><?= $__pendBell ?></strong><br><span style="color:#64748b;font-weight:500;">pedido(s) esperan tu atención.</span>',
                            confirmButtonColor: '#4f46e5',
                            confirmButtonText: '<i class="fas fa-cart-shopping mr-2"></i>Ver Pedidos',
                            showCancelButton: true,
                            cancelButtonText: 'Cerrar',
                            cancelButtonColor: '#94a3b8',
                            customClass: { popup: 'rounded-[2rem] shadow-2xl border border-slate-100', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'ventas.php';
                            }
                        });
                        <?php else: ?>
                        Swal.fire({
                            icon: 'success',
                            title: 'Todo al día',
                            text: 'No tienes pedidos pendientes por revisar.',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'Genial',
                            customClass: { popup: 'rounded-[2rem] shadow-2xl border border-slate-100', confirmButton: 'rounded-xl' }
                        });
                        <?php endif; ?>
                    });
                }
            });
            </script>

            <div class="h-10 w-[1px] bg-slate-200"></div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-800 outfit-font"><?= htmlspecialchars($nombreCompleto) ?></div>
                    <div class="text-[11px] text-emerald-500 font-bold tracking-wide uppercase flex items-center justify-end gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online</div>
                </div>
                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center text-indigo-600 text-[17px] font-bold shadow-sm border border-indigo-200/50">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main class="p-10 flex-1">