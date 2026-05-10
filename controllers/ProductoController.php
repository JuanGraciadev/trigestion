<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    $redirect = isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == '2' ? 'trabajador.php' : '../views/usuarios/login.php';
    header("Location: $redirect");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';

$database = new Database();
$db = $database->conectar();
$productoModel = new Producto($db);

$accion = $_GET['accion'] ?? 'index';

// ── Helper: procesar imagen subida ──
function procesarImagenProducto() {
    if (!isset($_FILES['img_file']) || $_FILES['img_file']['error'] !== UPLOAD_ERR_OK) {
        return null; // no se subió archivo
    }

    $file = $_FILES['img_file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowedTypes)) {
        return false; // tipo no permitido
    }
    if ($file['size'] > $maxSize) {
        return false; // muy pesado
    }

    $uploadDir = __DIR__ . '/../img/productos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'prod_' . uniqid() . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        // retornar la ruta web relativa desde views/dashboard/
        return '../../img/productos/' . $newName;
    }
    return false;
}

switch ($accion) {
    case 'index':
        header("Location: ../views/dashboard/productos.php");
        break;

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imgResult = procesarImagenProducto();
            if ($imgResult === false) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error de imagen',
                    'text' => 'La imagen no es válida. Usa JPG, PNG, WEBP o GIF (máx 5MB).'
                ];
                header("Location: ../views/dashboard/productos.php");
                exit;
            }

            $datos = [
                'nombre' => $_POST['nombre'],
                'precio' => $_POST['precio'],
                'img' => $imgResult ?? '',
                'id_usuario' => $_SESSION['usuario']['id_usuario'],
                'id_categoria' => $_POST['id_categoria']
            ];

            if ($productoModel->crear($datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Éxito!',
                    'text' => 'Producto creado correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo crear el producto.'
                ];
            }
        }
        header("Location: ../views/dashboard/productos.php");
        break;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_producto = $_POST['id_producto'];

            $imgResult = procesarImagenProducto();
            if ($imgResult === false) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error de imagen',
                    'text' => 'La imagen no es válida. Usa JPG, PNG, WEBP o GIF (máx 5MB).'
                ];
                header("Location: ../views/dashboard/productos.php");
                exit;
            }

            $datos = [
                'nombre' => $_POST['nombre'],
                'precio' => $_POST['precio'],
                'img' => $imgResult ?? ($_POST['img_actual'] ?? ''),
                'id_categoria' => $_POST['id_categoria']
            ];

            if ($productoModel->editar($id_producto, $datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Actualizado!',
                    'text' => 'Producto actualizado correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo actualizar el producto.'
                ];
            }
        }
        header("Location: ../views/dashboard/productos.php");
        break;

    case 'toggleEstado':
        $id_producto = $_GET['id'];
        $estado = $_GET['estado'];

        if ($productoModel->cambiarEstado($id_producto, $estado)) {
            $nuevoEstadoStr = $estado == 1 ? 'inhabilitado' : 'habilitado';
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Estado Actualizado',
                'text' => "El producto ha sido $nuevoEstadoStr."
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo cambiar el estado del producto.'
            ];
        }
        header("Location: ../views/dashboard/productos.php");
        break;

    default:
        header("Location: ../views/dashboard/admin.php");
        break;
}
?>
