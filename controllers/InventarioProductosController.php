<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/InventarioProductos.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$database       = new Database();
$db             = $database->conectar();
$inventarioModel = new InventarioProductos($db);

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'actualizarBodega':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_inventario'], $_POST['bodega'])) {
            $id     = intval($_POST['id_inventario']);
            $bodega = trim($_POST['bodega']);
            if ($inventarioModel->actualizarBodega($id, $bodega)) {
                $_SESSION['alert'] = [
                    'icon'  => 'success',
                    'title' => 'Bodega actualizada',
                    'text'  => 'La ubicación del registro ha sido actualizada.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'No se pudo actualizar la bodega.'
                ];
            }
        }
        header("Location: ../views/dashboard/inventario_productos.php");
        break;

    default:
        header("Location: ../views/dashboard/inventario_productos.php");
        break;
}
?>
