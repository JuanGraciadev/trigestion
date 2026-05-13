<?php
require 'config/database.php';
require 'models/Venta.php';
require 'models/Producto.php';

$db = (new Database())->conectar();
$ventaModel = new Venta($db);
$productoModel = new Producto($db);

// ─── TEST: Simular venta POS (admin) ────────────────────────────────────────
echo "=== TEST POS: Registrar venta del admin ===" . PHP_EOL;

$clientes = $ventaModel->obtenerClientes();
if (empty($clientes)) {
    echo "  ✗ No hay clientes registrados. No se puede probar." . PHP_EOL;
    exit;
}
$clienteTest = $clientes[0];
echo "  Cliente: {$clienteTest['nombres']} (ID: {$clienteTest['id_usuario']})" . PHP_EOL;

// Buscar producto con stock
$productos = $productoModel->obtenerTodos();
$productoConStock = null;
foreach ($productos as $p) {
    $stock = $ventaModel->stockDisponible($p['id_producto']);
    if ($stock > 0) {
        $productoConStock = $p;
        $productoConStock['stock'] = $stock;
        break;
    }
}

if (!$productoConStock) {
    echo "  ✗ No hay productos con stock para probar." . PHP_EOL;
    exit;
}

echo "  Producto: {$productoConStock['nombre']} (Stock: {$productoConStock['stock']}, Precio: \${$productoConStock['precio']})" . PHP_EOL;

// Crear venta POS
$items = [
    [
        'id_producto' => $productoConStock['id_producto'],
        'cantidad' => 1,
        'precio_unitario' => $productoConStock['precio'],
        'descuento' => 0,
    ]
];

$result = $ventaModel->crearVentaPOS($clienteTest['id_usuario'], $items, 'Test venta POS', 1);
echo "  Resultado: " . ($result['ok'] ? "✓ Venta #{$result['id_venta']} creada. Total: \${$result['total']}" : "✗ Error: {$result['msg']}") . PHP_EOL;

// Verificar que aparece en la lista
$ventas = $ventaModel->obtenerTodas();
echo "  Total ventas en BD: " . count($ventas) . PHP_EOL;

if ($result['ok']) {
    $ultimaVenta = $ventas[0];
    echo "  Última venta: #{$ultimaVenta['id_venta']} | Estado: {$ultimaVenta['estado']} | Total: \${$ultimaVenta['total']} | Cliente: " . ($ultimaVenta['cliente_nombre'] ?? 'N/A') . PHP_EOL;

    // Verificar detalle
    $detalle = $ventaModel->obtenerDetalle($result['id_venta']);
    echo "  Detalles de la venta:" . PHP_EOL;
    foreach ($detalle as $d) {
        echo "    - {$d['producto_nombre']}: {$d['cantidad']}x \${$d['precio_unitario']}" . PHP_EOL;
    }

    // Verificar stock fue descontado
    $nuevoStock = $ventaModel->stockDisponible($productoConStock['id_producto']);
    echo "  Stock antes: {$productoConStock['stock']} → Stock después: {$nuevoStock}" . PHP_EOL;

    // Verificar estadísticas
    $stats = $ventaModel->obtenerEstadisticas();
    echo "  Estadísticas actualizadas:" . PHP_EOL;
    foreach ($stats as $k => $v) echo "    {$k}: {$v}" . PHP_EOL;
}

// ─── TEST: Simular venta de cliente ─────────────────────────────────────────
echo PHP_EOL . "=== TEST CLIENTE: Registrar pedido del cliente ===" . PHP_EOL;

$nuevoStock = $ventaModel->stockDisponible($productoConStock['id_producto']);
if ($nuevoStock > 0) {
    $itemsCliente = [
        [
            'id_producto' => $productoConStock['id_producto'],
            'cantidad' => 1,
            'precio_unitario' => $productoConStock['precio'],
            'descuento' => 0,
        ]
    ];
    $resultCliente = $ventaModel->crearVenta($clienteTest['id_usuario'], $itemsCliente, 'Pedido online del cliente');
    echo "  Resultado: " . ($resultCliente['ok'] ? "✓ Pedido #{$resultCliente['id_venta']} creado" : "✗ Error: {$resultCliente['msg']}") . PHP_EOL;

    if ($resultCliente['ok']) {
        // Verificar que aparece en las compras del cliente
        $misCompras = $ventaModel->obtenerPorCliente($clienteTest['id_usuario']);
        echo "  Compras del cliente: " . count($misCompras) . " pedido(s)" . PHP_EOL;
        foreach ($misCompras as $c) {
            echo "    Pedido #{$c['id_venta']} | Estado: {$c['estado']} | Total: \${$c['total']} | Productos: " . ($c['productos_lista'] ?? 'N/A') . PHP_EOL;
        }
    }
} else {
    echo "  ✗ Sin stock disponible para probar venta de cliente" . PHP_EOL;
}

echo PHP_EOL . "=== TODOS LOS TESTS FINALIZADOS ✓ ===" . PHP_EOL;
?>
