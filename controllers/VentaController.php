<?php
/**
 * VentaController.php
 * Maneja: carrito (sesión), finalizar compra, cambio de estado (admin)
 */
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Producto.php';

$database = new Database();
$db       = $database->conectar();
$ventaModel   = new Venta($db);
$productoModel = new Producto($db);

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// ═══════════════════════════════════════════════════════════════
// CARRITO – operaciones vía AJAX (devuelven JSON)
// ═══════════════════════════════════════════════════════════════
if ($accion === 'agregar_carrito') {
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '3') {
        echo json_encode(['ok' => false, 'msg' => 'No autorizado']); exit;
    }

    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $cantidad    = max(1, (int)($_POST['cantidad'] ?? 1));

    // Buscar datos del producto
    $todos = $productoModel->obtenerTodos();
    $prod = null;
    foreach ($todos as $p) { if ($p['id_producto'] == $id_producto) { $prod = $p; break; } }

    if (!$prod) { echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado']); exit; }

    // Verificar stock disponible
    $stock = $ventaModel->stockDisponible($id_producto);
    if ($stock < 1) {
        echo json_encode(['ok' => false, 'msg' => 'Sin stock disponible para este producto']);
        exit;
    }

    // Inicializar carrito en sesión
    if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

    // Acumular cantidad
    if (isset($_SESSION['carrito'][$id_producto])) {
        $nueva = $_SESSION['carrito'][$id_producto]['cantidad'] + $cantidad;
        if ($nueva > $stock) {
            echo json_encode(['ok' => false, 'msg' => "Stock máximo disponible: {$stock} unidades"]);
            exit;
        }
        $_SESSION['carrito'][$id_producto]['cantidad'] = $nueva;
    } else {
        $_SESSION['carrito'][$id_producto] = [
            'id_producto'     => $id_producto,
            'nombre'          => $prod['nombre'],
            'precio_unitario' => $prod['precio'],
            'img'             => $prod['img'] ?? '',
            'cantidad'        => $cantidad,
        ];
    }

    $total_items = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    echo json_encode(['ok' => true, 'total_items' => $total_items, 'msg' => 'Producto añadido al carrito']);
    exit;
}

// ─── Obtener carrito ────────────────────────────────────────────────────────
if ($accion === 'obtener_carrito') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario'])) { echo json_encode(['ok'=>false,'msg'=>'Sin sesión']); exit; }
    $carrito = $_SESSION['carrito'] ?? [];
    $total = 0;
    foreach ($carrito as $item) $total += $item['precio_unitario'] * $item['cantidad'];
    echo json_encode(['ok' => true, 'carrito' => array_values($carrito), 'total' => $total]);
    exit;
}

// ─── Actualizar cantidad en carrito ────────────────────────────────────────
if ($accion === 'actualizar_carrito') {
    header('Content-Type: application/json');
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $cantidad    = (int)($_POST['cantidad'] ?? 0);

    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$id_producto]);
    } else {
        $stock = $ventaModel->stockDisponible($id_producto);
        if ($cantidad > $stock) $cantidad = $stock;
        if (isset($_SESSION['carrito'][$id_producto]))
            $_SESSION['carrito'][$id_producto]['cantidad'] = $cantidad;
    }
    $carrito = $_SESSION['carrito'] ?? [];
    $total = 0;
    foreach ($carrito as $item) $total += $item['precio_unitario'] * $item['cantidad'];
    $total_items = array_sum(array_column($carrito, 'cantidad'));
    echo json_encode(['ok'=>true,'carrito'=>array_values($carrito),'total'=>$total,'total_items'=>$total_items]);
    exit;
}

// ─── Eliminar item del carrito ──────────────────────────────────────────────
if ($accion === 'eliminar_carrito') {
    header('Content-Type: application/json');
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    unset($_SESSION['carrito'][$id_producto]);
    $carrito = $_SESSION['carrito'] ?? [];
    $total = 0;
    foreach ($carrito as $item) $total += $item['precio_unitario'] * $item['cantidad'];
    $total_items = array_sum(array_column($carrito, 'cantidad'));
    echo json_encode(['ok'=>true,'carrito'=>array_values($carrito),'total'=>$total,'total_items'=>$total_items]);
    exit;
}

// ─── Finalizar compra ───────────────────────────────────────────────────────
if ($accion === 'finalizar_compra') {
    if (ob_get_level()) ob_clean(); // Evitar que warnings o notices rompan el JSON
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '3') {
        echo json_encode(['ok' => false, 'msg' => 'No autorizado']); exit;
    }

    $carrito = $_SESSION['carrito'] ?? [];
    if (empty($carrito)) {
        echo json_encode(['ok' => false, 'msg' => 'El carrito está vacío']); exit;
    }

    $id_cliente = $_SESSION['usuario']['id_usuario'];
    $notas      = $_POST['notas'] ?? '';

    $items = [];
    foreach ($carrito as $item) {
        $items[] = [
            'id_producto'     => $item['id_producto'],
            'cantidad'        => $item['cantidad'],
            'precio_unitario' => $item['precio_unitario'],
            'descuento'       => 0,
        ];
    }

    $result = $ventaModel->crearVenta($id_cliente, $items, $notas);
    if ($result['ok']) {
        $_SESSION['carrito'] = []; // limpiar carrito
        echo json_encode(['ok' => true, 'id_venta' => $result['id_venta'], 'msg' => '¡Pedido realizado con éxito!']);
    } else {
        echo json_encode(['ok' => false, 'msg' => $result['msg']]);
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════
// ADMIN – cambiar estado del pedido
// ═══════════════════════════════════════════════════════════════
if ($accion === 'cambiar_estado') {
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
        header('Location: ../views/dashboard/admin.php'); exit;
    }

    $id_venta    = (int)($_POST['id_venta'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? '';
    $id_usuario  = $_SESSION['usuario']['id_usuario'];

    $estadosValidos = ['Pendiente','En Proceso','Entregado','Cancelado'];
    if (!in_array($nuevo_estado, $estadosValidos)) {
        $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Estado no válido.'];
        header('Location: ../views/dashboard/ventas.php'); exit;
    }

    $ventaModel->cambiarEstado($id_venta, $nuevo_estado, $id_usuario);
    $_SESSION['alert'] = [
        'icon'  => 'success',
        'title' => 'Estado Actualizado',
        'text'  => "El pedido #{$id_venta} ahora está en estado: {$nuevo_estado}"
    ];
    header('Location: ../views/dashboard/ventas.php'); exit;
}

// ═══════════════════════════════════════════════════════════════
// ADMIN – Registrar venta directa (Punto de Venta)
// ═══════════════════════════════════════════════════════════════
if ($accion === 'crear_pos') {
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
        header('Location: ../views/dashboard/admin.php'); exit;
    }

    $id_cliente  = (int)($_POST['id_cliente'] ?? 0);
    $notas       = $_POST['notas'] ?? 'Venta en punto de venta';
    $id_usuario  = $_SESSION['usuario']['id_usuario'];

    // Recoger productos del formulario dinámico
    $productos_ids = $_POST['pos_producto'] ?? [];
    $cantidades    = $_POST['pos_cantidad'] ?? [];

    if (empty($productos_ids) || $id_cliente <= 0) {
        $_SESSION['alert'] = [
            'icon'  => 'error',
            'title' => 'Error',
            'text'  => 'Debes seleccionar un cliente y al menos un producto.'
        ];
        header('Location: ../views/dashboard/ventas.php'); exit;
    }

    $items = [];
    $todos = $productoModel->obtenerTodos();
    $productosMap = [];
    foreach ($todos as $p) { $productosMap[$p['id_producto']] = $p; }

    for ($i = 0; $i < count($productos_ids); $i++) {
        $pid = (int)$productos_ids[$i];
        $qty = max(1, (int)($cantidades[$i] ?? 1));
        if ($pid > 0 && isset($productosMap[$pid])) {
            $items[] = [
                'id_producto'     => $pid,
                'cantidad'        => $qty,
                'precio_unitario' => $productosMap[$pid]['precio'],
                'descuento'       => 0,
            ];
        }
    }

    if (empty($items)) {
        $_SESSION['alert'] = [
            'icon'  => 'error',
            'title' => 'Error',
            'text'  => 'No se encontraron productos válidos.'
        ];
        header('Location: ../views/dashboard/ventas.php'); exit;
    }

    $result = $ventaModel->crearVentaPOS($id_cliente, $items, $notas, $id_usuario);
    if ($result['ok']) {
        $_SESSION['alert'] = [
            'icon'  => 'success',
            'title' => '¡Venta Registrada!',
            'text'  => "Venta #{$result['id_venta']} registrada exitosamente. Total: $" . number_format($result['total'], 2)
        ];
    } else {
        $_SESSION['alert'] = [
            'icon'  => 'error',
            'title' => 'Error al registrar',
            'text'  => $result['msg']
        ];
    }
    header('Location: ../views/dashboard/ventas.php'); exit;
}

// ─── Acción no reconocida ───────────────────────────────────────────────────
header('Location: ../views/dashboard/admin.php');
exit;
?>
