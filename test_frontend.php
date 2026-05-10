<?php
session_start();
$_SESSION['usuario'] = [
    'id_usuario' => 8, // tefa (rol 3)
    'id_rol' => 3
];

// Simulamos agregar al carrito
$_POST = [
    'id_producto' => 2,
    'cantidad' => 1
];
ob_start();
$accion = 'agregar_carrito';
include 'controllers/VentaController.php';
$output1 = ob_get_clean();

// Simulamos finalizar compra
$_POST = [
    'notas' => 'Prueba desde el script'
];
ob_start();
$accion = 'finalizar_compra';
include 'controllers/VentaController.php';
$output2 = ob_get_clean();

echo "Agregar carrito: " . $output1 . "\n";
echo "Finalizar compra: " . $output2 . "\n";
?>
