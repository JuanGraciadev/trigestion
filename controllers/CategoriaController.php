<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Producto.php';

$database = new Database();
$db = $database->conectar();
$categoriaModel = new Categoria($db);
$productoModel = new Producto($db);

$accion = $_GET['accion'] ?? 'index';

// ── Helper: procesar imagen subida ──
function procesarImagenCategoria() {
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

    $uploadDir = __DIR__ . '/../img/categorias/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'cat_' . uniqid() . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        // retornar la ruta web relativa desde views/dashboard/
        return '../../img/categorias/' . $newName;
    }
    return false;
}

switch ($accion) {
    case 'index':
        // The view will load the data directly or we can load it here
        header("Location: ../views/dashboard/categorias.php");
        break;

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imgResult = procesarImagenCategoria();
            if ($imgResult === false) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error de imagen',
                    'text' => 'La imagen no es válida. Usa JPG, PNG, WEBP o GIF (máx 5MB).'
                ];
                header("Location: ../views/dashboard/categorias.php");
                exit;
            }

            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'imagen' => $imgResult ?? ($_POST['imagen'] ?? '')
            ];

            if ($categoriaModel->crear($datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Éxito!',
                    'text' => 'Categoría creada correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo crear la categoría.'
                ];
            }
        }
        header("Location: ../views/dashboard/categorias.php");
        break;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_categoria = $_POST['id_categoria'];

            $imgResult = procesarImagenCategoria();
            if ($imgResult === false) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error de imagen',
                    'text' => 'La imagen no es válida. Usa JPG, PNG, WEBP o GIF (máx 5MB).'
                ];
                header("Location: ../views/dashboard/categorias.php");
                exit;
            }

            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'imagen' => $imgResult ?? ($_POST['img_actual'] ?? '')
            ];

            if ($categoriaModel->editar($id_categoria, $datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Actualizada!',
                    'text' => 'Categoría actualizada correctamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo actualizar la categoría.'
                ];
            }
        }
        header("Location: ../views/dashboard/categorias.php");
        break;

    case 'toggleEstado':
        $id_categoria = $_GET['id'];
        $estado = $_GET['estado'];

        if ($categoriaModel->cambiarEstado($id_categoria, $estado)) {
            $nuevoEstadoStr = $estado == 1 ? 'inhabilitada' : 'habilitada';
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Estado Actualizado',
                'text' => "La categoría ha sido $nuevoEstadoStr."
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo cambiar el estado de la categoría.'
            ];
        }
        header("Location: ../views/dashboard/categorias.php");
        break;

    case 'agregarProducto':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // According to DB, producto has img as blob, but we might just use text/url or handle upload.
            // For simplicity and matching typical use cases without complex upload handling unless requested, 
            // we will store simple text if the DB allows it, or empty blob if needed.
            // Wait, schema says `img blob`. We will try to store a URL or basic info or empty for now.
            // In many prototype apps, they use a URL in a varchar, but here it's a BLOB.
            // To keep it simple, we will just leave img empty or store a small string if the user didn't request file upload handling.
            
            $datos = [
                'nombre' => $_POST['nombre'],
                'precio' => $_POST['precio'],
                'img' => '', // placeholder
                'id_usuario' => $_SESSION['usuario']['id_usuario'],
                'id_categoria' => $_POST['id_categoria']
            ];

            if ($productoModel->crear($datos)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => '¡Producto Añadido!',
                    'text' => 'El producto se ha agregado a la categoría exitosamente.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No se pudo agregar el producto.'
                ];
            }
        }
        header("Location: ../views/dashboard/categorias.php");
        break;

    default:
        header("Location: ../views/dashboard/admin.php");
        break;
}
