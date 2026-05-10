<?php
session_start();
$_SESSION['usuario'] = [
    'id_usuario' => 8, // tefa (rol 3)
    'id_rol' => 3
];
$_SESSION['carrito'] = [
    2 => [
        'id_producto' => 2,
        'cantidad' => 1,
        'precio_unitario' => 6900
    ]
];
$_POST['accion'] = 'finalizar_compra';

// Mock request
ob_start();
include 'controllers/VentaController.php';
$output = ob_get_clean();
echo "Resultado: " . $output . PHP_EOL;
?>
