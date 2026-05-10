<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';
$db = (new Database())->conectar();
$ventaModel = new Venta($db);
$ventas = $ventaModel->obtenerPorCliente(8);
print_r($ventas);
?>
