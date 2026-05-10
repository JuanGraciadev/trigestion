<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Lote.php';

$database = new Database();
$db = $database->conectar();
$loteModel = new Lote($db);

$accion = $_GET['accion'] ?? 'index';

switch ($accion) {
    case 'index':
        header("Location: ../views/dashboard/lotes.php");
        break;

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo_lote = $_POST['codigo_lote'];
            $id_usuario = $_SESSION['usuario']['id_usuario'];

            $id_lote = $loteModel->crear($codigo_lote, $id_usuario);
            if ($id_lote) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Lote Creado',
                    'text' => 'Lote creado con éxito. Ahora ingresa los detalles del lote.'
                ];
                // Redirect immediately to the details page for the sequence
                header("Location: ../views/dashboard/lote_detalles.php?id_lote=" . $id_lote);
                exit;
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo crear el lote.'
                ];
                header("Location: ../views/dashboard/lotes.php");
                exit;
            }
        }
        break;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_lote = $_POST['id_lote'];
            $codigo_lote = $_POST['codigo_lote'];

            if ($loteModel->editar($id_lote, $codigo_lote)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Actualizado!',
                    'text' => 'El código de lote se ha actualizado.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo actualizar el lote.'
                ];
            }
        }
        header("Location: ../views/dashboard/lotes.php");
        break;

    case 'eliminar':
        if (isset($_GET['id_lote'])) {
            $id_lote = $_GET['id_lote'];
            if ($loteModel->eliminar($id_lote)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Lote Eliminado',
                    'text' => 'El lote y todos sus detalles asociados fueron eliminados correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo eliminar el lote.'
                ];
            }
        }
        header("Location: ../views/dashboard/lotes.php");
        break;

    case 'crearDetalle':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_lote' => $_POST['id_lote'],
                'unidades' => $_POST['unidades'],
                'tipo_envase' => $_POST['tipo_envase'],
                'capacidad' => $_POST['capacidad'],
                'proveedor' => $_POST['proveedor']
            ];

            if ($loteModel->crearDetalle($datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Detalle Añadido',
                    'text' => 'Los detalles se registraron exitosamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo registrar el detalle.'
                ];
            }
            header("Location: ../views/dashboard/lote_detalles.php?id_lote=" . $_POST['id_lote']);
            exit;
        }
        break;

    case 'editarDetalle':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_detalles = $_POST['id_detalles'];
            $datos = [
                'unidades' => $_POST['unidades'],
                'tipo_envase' => $_POST['tipo_envase'],
                'capacidad' => $_POST['capacidad'],
                'proveedor' => $_POST['proveedor']
            ];

            if ($loteModel->editarDetalle($id_detalles, $datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Detalle Actualizado',
                    'text' => 'Los detalles se actualizaron correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo actualizar el detalle.'
                ];
            }
            header("Location: ../views/dashboard/lote_detalles.php?id_lote=" . $_POST['id_lote']);
            exit;
        }
        break;

    default:
        header("Location: ../views/dashboard/admin.php");
        break;
}
?>
