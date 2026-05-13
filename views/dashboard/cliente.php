<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '3') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';
require_once __DIR__ . '/../../models/Venta.php';

$database      = new Database();
$db            = $database->conectar();
$productoModel = new Producto($db);
$categoriaModel= new Categoria($db);
$ventaModel    = new Venta($db);

$filtro_cat = $_GET['cat'] ?? null;

$todos_productos = $productoModel->obtenerTodos();
$categorias      = $categoriaModel->obtenerTodas();

$productos = [];
foreach ($todos_productos as $p) {
    if (($p['estado'] ?? 1) == 1) {
        $p['stock'] = $ventaModel->stockDisponible($p['id_producto']);
        if ($filtro_cat) {
            if ($p['id_categoria'] == $filtro_cat) $productos[] = $p;
        } else {
            $productos[] = $p;
        }
    }
}

// Carrito en sesión
$carrito     = $_SESSION['carrito'] ?? [];
$carrito_cnt = array_sum(array_column($carrito, 'cantidad'));
$carrito_total = 0;
foreach ($carrito as $item) $carrito_total += $item['precio_unitario'] * $item['cantidad'];

$titulo = "Catálogo MOOVA!";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<style>
/* Cart Sidebar Overlay */
.cart-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);
    z-index: 40; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.cart-overlay.open { opacity: 1; visibility: visible; }

/* Cart Sidebar */
.cart-sidebar {
    position: fixed; top: 0; right: -100%; width: 100%; max-width: 450px; height: 100vh;
    background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    z-index: 50; box-shadow: -10px 0 40px rgba(0, 0, 0, 0.1);
    transition: right 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex; flex-direction: column;
}
.cart-sidebar.open { right: 0; }

/* Stock Badges */
.stock-badge-0 { background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
.stock-badge-low { background-color: #fffbeb; color: #f59e0b; border: 1px solid #fde68a; }
.stock-badge-ok { background-color: #f0fdf4; color: #10b981; border: 1px solid #bbf7d0; }

/* Badge Flotante */
.badge-cart {
    position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #f43f5e, #e11d48);
    color: white; font-size: 11px; font-weight: 900; border-radius: 999px; min-width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center; border: 2px solid #0f172a;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.4); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.badge-cart.scale-110 { transform: scale(1.2); }
</style>

<!-- Cart overlay -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="p-8 border-b border-slate-200/60 flex items-center justify-between bg-white/50 relative z-10">
        <div>
            <h3 class="text-2xl font-extrabold text-slate-800 outfit-font tracking-tight">Mi Pedido</h3>
            <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider">Revisión de Carrito</p>
        </div>
        <button onclick="toggleCart()" class="w-11 h-11 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all flex items-center justify-center shadow-sm hover:rotate-90">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Items -->
    <div class="flex-1 overflow-y-auto p-8 space-y-4 custom-scrollbar" id="cartItems">
        <div class="text-center py-20 text-slate-400" id="cartEmpty" style="display:none">
            <div class="w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-6 border border-slate-100">
                <i class="fas fa-shopping-basket text-4xl opacity-30 text-slate-500"></i>
            </div>
            <p class="font-bold text-lg text-slate-600 outfit-font">Tu carrito está vacío</p>
            <p class="text-sm mt-1">¡Explora nuestro catálogo y agrega productos!</p>
        </div>
    </div>

    <!-- Footer checkout -->
    <div class="p-8 border-t border-slate-200/60 bg-white/80 backdrop-blur-md relative z-10">
        <div class="flex justify-between items-end mb-6 bg-slate-50 p-5 rounded-2xl border border-slate-100">
            <span class="text-slate-500 font-bold uppercase tracking-wider text-xs">Total Estimado</span>
            <span class="text-3xl font-black text-sky-600 outfit-font" id="cartTotal">$0.00</span>
        </div>
        
        <div class="relative mb-6">
            <i class="fas fa-comment-dots absolute left-4 top-4 text-slate-400"></i>
            <textarea id="notasCompra" placeholder="Instrucciones especiales para el pedido..." rows="2"
                class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 outline-none focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all resize-none shadow-sm"></textarea>
        </div>
        
        <button onclick="finalizarCompra()" id="btnCheckout"
            class="w-full py-4.5 bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-bold rounded-2xl shadow-[0_10px_20px_-10px_rgba(14,165,233,0.5)] hover:shadow-[0_15px_25px_-10px_rgba(14,165,233,0.6)] transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 text-lg outfit-font tracking-wide">
            <i class="fas fa-check-circle"></i> <span>Confirmar Pedido</span>
        </button>
        
        <button onclick="toggleCart()" class="w-full mt-4 py-3 text-slate-400 font-bold text-sm hover:text-slate-700 transition-colors">
            ← Seguir explorando
        </button>
    </div>
</div>

<div class="max-w-[1400px] mx-auto space-y-10 pb-12">
    <!-- Premium Hero Banner -->
    <div class="relative rounded-[2.5rem] shadow-[0_20px_50px_-12px_rgba(14,165,233,0.1)] border border-white overflow-hidden p-10 md:p-14 flex flex-col md:flex-row items-center justify-between gap-10 bg-gradient-to-br from-white via-sky-50/50 to-indigo-50/30">
        <!-- Decoraciones de fondo -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-gradient-to-br from-sky-400/20 to-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-gradient-to-tr from-emerald-400/10 to-sky-500/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMTQsIDE2NSwgMjMzLCAwLjA1KSIvPjwvc3ZnPg==')] opacity-60"></div>
        </div>
        
        <div class="z-10 relative max-w-2xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-slate-100 shadow-sm mb-6">
                <span class="w-2 h-2 rounded-full bg-sky-500 animate-ping absolute opacity-75"></span>
                <span class="w-2 h-2 rounded-full bg-sky-500 relative"></span>
                <span class="text-[11px] font-black text-slate-700 uppercase tracking-widest">Catálogo Oficial</span>
            </div>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-800 mb-4 tracking-tight outfit-font leading-tight">
                Hidratación <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-indigo-600">Premium</span>
            </h2>
            <p class="text-slate-500 text-lg font-medium max-w-lg leading-relaxed">
                Descubre nuestra exclusiva selección de productos MOOVA!. Haz tu pedido en segundos con la máxima calidad directo a tu puerta.
            </p>
        </div>
        
        <!-- Botón carrito flotante en el banner -->
        <button onclick="toggleCart()" id="cartBtn"
            class="z-10 relative flex items-center justify-center gap-3 bg-gradient-to-br from-slate-900 to-slate-800 text-white px-8 py-5 rounded-[1.25rem] font-bold shadow-[0_15px_30px_-10px_rgba(15,23,42,0.5)] hover:shadow-[0_20px_40px_-10px_rgba(15,23,42,0.6)] transition-all transform hover:-translate-y-1 border border-slate-700">
            <div class="relative">
                <i class="fas fa-shopping-bag text-2xl"></i>
                <span class="badge-cart" id="cartBadge" style="<?= $carrito_cnt > 0 ? '' : 'display:none' ?>">
                    <?= $carrito_cnt ?>
                </span>
            </div>
            <div class="text-left ml-2 hidden sm:block">
                <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Mi Pedido</div>
                <div class="text-base outfit-font">Revisar Carrito</div>
            </div>
        </button>
    </div>

    <!-- Barra de Búsqueda y Filtros Premium -->
    <div class="flex flex-col xl:flex-row items-center justify-between gap-6 bg-white p-3 rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sticky top-28 z-20">
        
        <!-- Category Filter Pills -->
        <div class="flex flex-wrap gap-2 px-2 py-1 w-full xl:w-auto">
            <a href="cliente.php" class="px-5 py-2.5 rounded-xl font-bold text-[13px] transition-all flex items-center gap-2 <?= !$filtro_cat ? 'bg-slate-900 text-white shadow-md' : 'bg-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
                <i class="fas fa-border-all opacity-70"></i> Todo
            </a>
            <?php foreach($categorias as $cat): ?>
                <?php if(($cat['estado'] ?? 1) == 1): ?>
                <a href="cliente.php?cat=<?= $cat['id_categoria'] ?>"
                   class="px-5 py-2.5 rounded-xl font-bold text-[13px] transition-all flex items-center gap-2 <?= $filtro_cat == $cat['id_categoria'] ? 'bg-slate-900 text-white shadow-md' : 'bg-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
                    <span class="w-2 h-2 rounded-full <?= $filtro_cat == $cat['id_categoria'] ? 'bg-sky-400' : 'bg-slate-300' ?>"></span>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Buscador Moderno -->
        <div class="relative w-full xl:w-96 shrink-0 group pr-2 pb-2 xl:pb-0 pt-2 xl:pt-0">
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none transition-colors group-focus-within:text-sky-500 text-slate-400">
                <i class="fas fa-search"></i>
            </div>
            <input type="text" id="searchInput" placeholder="Busca por nombre..." onkeyup="filtrarProductos()"
                class="w-full pl-12 pr-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 focus:bg-white outline-none transition-all text-slate-700 font-medium text-[15px] shadow-sm">
            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none">
                <span class="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-2 py-1 rounded shadow-sm">/</span>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" id="productsGrid">
        <?php foreach ($productos as $p): ?>
        <?php
            $stock = $p['stock'];
            $sin_stock = $stock <= 0;
            $stock_badge = $sin_stock ? 'stock-badge-0' : ($stock < 5 ? 'stock-badge-low' : 'stock-badge-ok');
            $stock_label = $sin_stock ? 'Sin Stock' : ($stock < 5 ? "Quedan {$stock}" : "Stock: {$stock}");
        ?>
        <div class="product-card glass-card rounded-[2rem] border border-slate-100 overflow-hidden premium-shadow hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group flex flex-col relative <?= $sin_stock ? 'opacity-60 grayscale-[30%]' : '' ?>" data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>">
            
            <!-- Etiqueta de Stock Flotante -->
            <div class="absolute top-5 right-5 z-10">
                <div class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm backdrop-blur-md bg-white/90 <?= $stock_badge ?>">
                    <?= $stock_label ?>
                </div>
            </div>

            <div class="h-64 bg-gradient-to-b from-slate-50 to-white relative overflow-hidden flex items-center justify-center p-6 border-b border-slate-50">
                <!-- Fondo decorativo del producto -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(14,165,233,0.05)_0,transparent_70%)] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <?php if(!empty($p['img'])): ?>
                    <img src="<?= htmlspecialchars($p['img']) ?>" alt="Producto" class="max-w-[75%] max-h-[75%] object-contain relative z-[5] group-hover:scale-105 transition-transform duration-700 ease-out drop-shadow-xl">
                <?php else: ?>
                    <div class="w-32 h-32 rounded-full bg-sky-50 flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform duration-700">
                        <i class="fas fa-bottle-water text-6xl text-sky-200"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-8 flex-1 flex flex-col relative bg-white">
                <div class="text-[11px] font-bold text-sky-500 mb-2 uppercase tracking-widest flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    <?= htmlspecialchars($p['categoria_nombre'] ?? 'Agua Purificada') ?>
                </div>
                
                <h3 class="text-xl font-black text-slate-800 mb-2 leading-tight outfit-font line-clamp-2"><?= htmlspecialchars($p['nombre']) ?></h3>
                
                <div class="text-2xl font-black text-slate-900 mb-6 mt-auto font-mono tracking-tight flex items-end gap-1">
                    <span class="text-sm text-slate-400 font-bold pb-1">$</span><?= number_format($p['precio'], 2) ?>
                </div>

                <?php if ($sin_stock): ?>
                    <div class="w-full py-4 rounded-[14px] font-bold text-center text-slate-400 bg-slate-50 border border-slate-100 cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-box-open"></i> Agotado
                    </div>
                <?php else: ?>
                    <button
                        onclick="añadirCarrito(<?= $p['id_producto'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= $p['precio'] ?>, '<?= htmlspecialchars(addslashes($p['img'] ?? '')) ?>')"
                        class="w-full py-4 rounded-[14px] font-bold text-sky-600 bg-sky-50 hover:bg-sky-500 hover:text-white hover:shadow-[0_10px_20px_-10px_rgba(14,165,233,0.5)] transition-all duration-300 flex items-center justify-center gap-2 group/btn border border-sky-100 hover:border-sky-500">
                        <i class="fas fa-plus group-hover/btn:rotate-90 transition-transform duration-300"></i> Agregar
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($productos)): ?>
            <div class="col-span-full py-24 text-center bg-white rounded-[2.5rem] border border-slate-100 shadow-sm mt-4">
                <div class="w-24 h-24 bg-slate-50 rounded-[1.5rem] flex items-center justify-center mx-auto mb-6 text-slate-300 shadow-inner">
                    <i class="fas fa-boxes-stacked text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-700 mb-2 outfit-font">Catálogo Vacío</h3>
                <p class="text-slate-500 font-medium">No se encontraron productos en esta categoría o están agotados.</p>
                <a href="cliente.php" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-colors">
                    <i class="fas fa-arrow-left"></i> Ver todo el catálogo
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// ─── Estado local del carrito ───────────────────────────────────────────────
let carrito = <?= json_encode(array_values($carrito)) ?>;

function openCart() {
    document.getElementById('cartSidebar').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    renderCarrito();
}

function closeCart() {
    document.getElementById('cartSidebar').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
}

function toggleCart() {
    if (document.getElementById('cartSidebar').classList.contains('open')) {
        closeCart();
    } else {
        openCart();
    }
}

function renderCarrito() {
    const container = document.getElementById('cartItems');
    const emptyMsg  = document.getElementById('cartEmpty');
    const totalEl   = document.getElementById('cartTotal');
    const badge     = document.getElementById('cartBadge');
    
    // Header banner badge
    const headerBadge = document.getElementById('cartBadgeHeader');

    // Limpiar items anteriores
    container.querySelectorAll('.cart-item-row').forEach(el => el.remove());

    let total = 0, totalItems = 0;

    if (carrito.length === 0) {
        emptyMsg.style.display = 'block';
    } else {
        emptyMsg.style.display = 'none';
        carrito.forEach(item => {
            const subtotal = parseFloat(item.precio_unitario) * parseInt(item.cantidad);
            total += subtotal;
            totalItems += parseInt(item.cantidad);

            const div = document.createElement('div');
            div.className = 'cart-item-row flex items-center gap-4 p-4 rounded-[1.25rem] border border-slate-100 bg-white shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] transition-all hover:border-sky-200 group relative';
            div.innerHTML = `
                <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0 p-1">
                    ${item.img ? '<img src="' + item.img + '" class="w-full h-full object-contain mix-blend-multiply">' : '<i class="fas fa-bottle-water text-3xl text-sky-200"></i>'}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-[13px] truncate pr-6">${item.nombre}</p>
                    <p class="text-[11px] font-bold text-slate-400">$${parseFloat(item.precio_unitario).toFixed(2)} c/u</p>
                    <div class="flex items-center gap-1.5 mt-2.5">
                        <button class="qty-btn" onclick="cambiarCantidad(${item.id_producto}, ${parseInt(item.cantidad) - 1})"><i class="fas fa-minus text-[10px]"></i></button>
                        <span class="text-[13px] font-black text-slate-700 w-6 text-center font-mono">${item.cantidad}</span>
                        <button class="qty-btn" onclick="cambiarCantidad(${item.id_producto}, ${parseInt(item.cantidad) + 1})"><i class="fas fa-plus text-[10px]"></i></button>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 flex flex-col items-end justify-between h-full">
                    <button onclick="eliminarItem(${item.id_producto})" class="w-7 h-7 rounded-full bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm absolute top-3 right-3 opacity-0 group-hover:opacity-100 transform translate-y-1 group-hover:translate-y-0">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                    <div class="mt-auto pt-8">
                        <p class="font-black text-slate-800 text-[15px] font-mono">$${subtotal.toFixed(2)}</p>
                    </div>
                </div>`;
            container.appendChild(div);
        });
    }

    totalEl.textContent = '$' + total.toFixed(2);
    
    if (totalItems > 0) {
        badge.textContent = totalItems;
        badge.style.display = 'flex';
        // Add animation class to make it pop
        badge.classList.remove('scale-100');
        badge.classList.add('scale-110');
        setTimeout(() => { badge.classList.remove('scale-110'); badge.classList.add('scale-100'); }, 200);
    } else {
        badge.style.display = 'none';
    }
}

// ─── Añadir al carrito (AJAX) ───────────────────────────────────────────────
function añadirCarrito(id_producto, nombre, precio, img) {
    fetch('../../controllers/VentaController.php?accion=agregar_carrito', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id_producto=' + id_producto + '&cantidad=1'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            var idx = carrito.findIndex(function(i) { return i.id_producto == id_producto; });
            if (idx >= 0) {
                carrito[idx].cantidad = parseInt(carrito[idx].cantidad) + 1;
            } else {
                carrito.push({ id_producto: id_producto, nombre: nombre, precio_unitario: precio, img: img, cantidad: 1 });
            }
            renderCarrito();
            openCart();

            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Producto añadido', text: nombre, showConfirmButton: false, timer: 2000,
                customClass: { popup: 'rounded-[1.5rem] shadow-xl border border-slate-100' }
            });
        } else {
            Swal.fire({ icon:'warning', title:'Atención', text: data.msg,
                confirmButtonColor:'#0ea5e9', customClass:{popup:'rounded-[2rem]'} });
        }
    })
    .catch(function(err) {
        Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo comunicar con el servidor.',
            confirmButtonColor:'#0ea5e9', customClass:{popup:'rounded-[2rem]'} });
    });
}

// ─── Cambiar cantidad ───────────────────────────────────────────────────────
function cambiarCantidad(id_producto, nueva_cantidad) {
    fetch('../../controllers/VentaController.php?accion=actualizar_carrito', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id_producto=' + id_producto + '&cantidad=' + nueva_cantidad
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            carrito = data.carrito;
            renderCarrito();
        }
    });
}

// ─── Eliminar item ──────────────────────────────────────────────────────────
function eliminarItem(id_producto) {
    fetch('../../controllers/VentaController.php?accion=eliminar_carrito', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id_producto=' + id_producto
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            carrito = data.carrito;
            renderCarrito();
        }
    });
}

// ─── Finalizar compra ───────────────────────────────────────────────────────
function finalizarCompra() {
    if (carrito.length === 0) {
        Swal.fire({ icon:'info', title:'Carrito vacío', text:'Agrega al menos un producto.',
            confirmButtonColor:'#0ea5e9', customClass:{popup:'rounded-[2rem]'} });
        return;
    }

    var totalCompra = 0;
    carrito.forEach(function(item) { totalCompra += parseFloat(item.precio_unitario) * parseInt(item.cantidad); });
    var notas = document.getElementById('notasCompra').value;

    Swal.fire({
        title: 'Confirmar Orden',
        html: '<p style="color:#64748b;font-weight:500;margin-bottom:12px;">Se enviará tu pedido y será procesado a la brevedad.</p>' +
              '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:20px;text-align:center;">' +
              '<p style="color:#94a3b8;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1px;">Monto a pagar</p>' +
              '<p style="color:#0f172a;font-size:36px;font-weight:900;font-family:\'Outfit\',sans-serif;">$' + totalCompra.toFixed(2) + '</p></div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-[2rem] shadow-2xl', confirmButton: 'rounded-xl font-bold', cancelButton: 'rounded-xl font-bold' }
    }).then(function(result) {
        if (!result.isConfirmed) return;

        var btn = document.getElementById('btnCheckout');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        fetch('../../controllers/VentaController.php?accion=finalizar_compra', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'notas=' + encodeURIComponent(notas)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';
            if (data.ok) {
                carrito = [];
                renderCarrito();
                closeCart();

                Swal.fire({
                    icon: 'success',
                    title: '¡Orden Confirmada!',
                    html: '<p style="color:#64748b;margin-top:10px;">Tu pedido ha sido registrado con éxito.</p><div style="margin-top:15px;display:inline-block;background:#f0fdf4;color:#16a34a;padding:8px 16px;border-radius:99px;font-weight:bold;font-size:14px;border:1px solid #bbf7d0;">Orden #' + data.id_venta + '</div>',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Ver mis compras',
                    customClass: { popup: 'rounded-[2.5rem] shadow-2xl', confirmButton: 'rounded-xl font-bold' }
                }).then(function() {
                    window.location.href = 'cliente_compras.php';
                });
            } else {
                Swal.fire({ icon:'error', title:'Error al procesar', text: data.msg,
                    confirmButtonColor:'#0ea5e9', customClass:{popup:'rounded-[2rem]'} });
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';
            Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo procesar tu pedido. Intenta de nuevo.',
                confirmButtonColor:'#0ea5e9', customClass:{popup:'rounded-[2rem]'} });
        });
    });
}

// ─── Buscador en tiempo real ────────────────────────────────────────────────
function filtrarProductos() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    let visibles = 0;
    
    cards.forEach(card => {
        const nombre = card.getAttribute('data-nombre');
        if (nombre.includes(query)) {
            card.style.display = 'flex';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    let emptyMsgJS = document.getElementById('noProductsMsgJS');
    if (visibles === 0 && cards.length > 0) {
        if (!emptyMsgJS) {
            const grid = document.getElementById('productsGrid');
            grid.insertAdjacentHTML('beforeend', `
                <div id="noProductsMsgJS" class="col-span-full py-20 text-center animate-fade-in bg-white rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-[1.2rem] flex items-center justify-center mx-auto mb-5 text-slate-300 border border-slate-100">
                        <i class="fas fa-search text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700 mb-1 outfit-font">No hay coincidencias</h3>
                    <p class="text-slate-500 font-medium text-sm">Prueba buscar con otros términos.</p>
                </div>
            `);
        } else {
            emptyMsgJS.style.display = 'block';
        }
    } else if (emptyMsgJS) {
        emptyMsgJS.style.display = 'none';
    }
}

// Renderizar al cargar (por si hay items en sesión)
renderCarrito();
</script>

<?php require_once __DIR__. '/../layouts/footer.php'; ?>
