<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produccion.php';
require_once __DIR__ . '/../models/InventarioProductos.php';
require_once __DIR__ . '/../models/InventarioMP.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

$database = new Database();
$db = $database->conectar();
$produccionModel   = new Produccion($db);
$inventarioModel   = new InventarioProductos($db);
$inventarioMPModel = new InventarioMP($db);

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'lote_produccion'      => $_POST['lote_produccion'],
                'cantidad'             => $_POST['cantidad'],
                'descripcion'          => $_POST['descripcion'],
                'id_usuario'           => $_SESSION['usuario']['id_usuario'],
                'id_producto'          => $_POST['id_producto'],
                'id_inventario_materia'=> !empty($_POST['id_inventario_materia']) ? $_POST['id_inventario_materia'] : null,
                'estado'               => 'En Producción'
            ];

            if ($produccionModel->crear($datos)) {
                $_SESSION['alert'] = [
                    'icon'  => 'success',
                    'title' => 'Producción Iniciada',
                    'text'  => 'La producción se ha registrado y está en curso.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'No se pudo iniciar la producción.'
                ];
            }
        }
        header("Location: ../views/dashboard/produccion.php");
        break;

    case 'finalizar':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            // 1. Cambiar estado de la producción
            if ($produccionModel->actualizarEstado($id, 'Finalizada')) {

                // 2. Obtener datos de esa producción para registrar inventario
                $prod = $produccionModel->obtenerPorId($id);

                if ($prod) {
                    // 2. Registrar ingreso en inventario de productos terminados
                    $bodega = $_GET['bodega'] ?? 'Principal';
                    $datosInv = [
                        'id_produccion' => $id,
                        'id_producto'   => $prod['id_producto'],
                        'id_usuario'    => $_SESSION['usuario']['id_usuario'],
                        'cantidad'      => $prod['cantidad'],
                        'bodega'        => $bodega,
                    ];
                    $inventarioModel->registrarIngreso($datosInv);

                    // 3. Descontar materia prima si la producción tenía una vinculada
                    if (!empty($prod['id_inventario_materia'])) {
                        $inventarioMPModel->descontarStock(
                            $prod['id_inventario_materia'],
                            $prod['cantidad']
                        );
                    }
                }

                $_SESSION['alert'] = [
                    'icon'  => 'success',
                    'title' => '¡Producción Finalizada!',
                    'text'  => 'La producción fue completada. Se actualizó el inventario de productos y se descontó la materia prima utilizada.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'No se pudo actualizar el estado de la producción.'
                ];
            }
        }
        header("Location: ../views/dashboard/produccion.php");
        break;

    default:
        header("Location: ../views/dashboard/produccion.php");
        break;
}
?>
