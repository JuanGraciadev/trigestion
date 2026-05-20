<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

// Ruta absoluta base — funciona en cualquier hosting
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'];
$admin_url   = $base_url . '/views/dashboard/admin.php';
$login_url   = $base_url . '/views/usuarios/login.php';

// Verificación de seguridad de id_rol
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header("Location: $login_url");
    exit;
}

class AdminUsuarioController {
    private $usuarioModel;
    private $admin_url;

    public function __construct($admin_url) {
        $database = new Database();
        $db = $database->conectar();
        $this->usuarioModel = new Usuario($db);
        $this->admin_url = $admin_url;
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $this->admin_url);
            exit;
        }

        $nombres   = trim($_POST['nombres'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $id_rol    = trim($_POST['id_rol'] ?? '');

        if (empty($nombres) || empty($direccion) || empty($email) || empty($password) || empty($id_rol)) {
            $this->setAlert('warning', 'Campos incompletos', 'Debe completar todos los campos obligatorios');
            header("Location: " . $this->admin_url);
            exit;
        }

        if ($this->usuarioModel->existeCorreo($email)) {
            $this->setAlert('error', 'Correo existente', 'Este correo ya está registrado en el sistema');
            header("Location: " . $this->admin_url);
            exit;
        }

        $datos = [
            'nombres'           => $nombres,
            'direccion'         => $direccion,
            'email'             => $email,
            'password'          => password_hash($password, PASSWORD_DEFAULT),
            'id_rol'            => $id_rol,
            'codigo_estudiantil'=> $_POST['codigo_estudiantil'] ?? '',
            'fecha_nacimiento'  => $_POST['fecha_nacimiento'] ?? null,
            'grado_actual'      => $_POST['grado_actual'] ?? null,
            'telefono'          => $_POST['telefono'] ?? ''
        ];

        $resultado = $this->usuarioModel->registrar($datos);

        if ($resultado === true) {
            $this->setAlert('success', 'Éxito', 'Usuario creado correctamente');
        } else {
            $this->setAlert('error', 'Error', $resultado);
        }
        header("Location: " . $this->admin_url);
        exit;
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $this->admin_url);
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;
        if (!$id_usuario) {
            $this->jsonOrRedirect(false, 'ID de usuario no proporcionado');
        }

        $datos = [
            'nombres'   => trim($_POST['nombres'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'id_rol'    => trim($_POST['id_rol'] ?? '')
        ];

        if (!empty($_POST['password'])) {
            $datos['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $resultado = $this->usuarioModel->actualizar($id_usuario, $datos);

        if ($resultado === true) {
            $this->jsonOrRedirect(true, 'Usuario actualizado correctamente');
        } else {
            $this->jsonOrRedirect(false, $resultado);
        }
    }

    public function toggleEstado() {
        $id_usuario = $_GET['id'] ?? null;
        $estado     = $_GET['estado'] ?? null;

        if ($id_usuario !== null && $estado !== null) {
            $nuevo_estado = $estado == 1 ? 0 : 1;
            $resultado    = $this->usuarioModel->cambiarEstado($id_usuario, $nuevo_estado);

            if ($resultado === true) {
                $status_text = $nuevo_estado == 1 ? 'activado' : 'desactivado';
                $this->setAlert('success', 'Éxito', "Usuario $status_text correctamente");
            } else {
                $this->setAlert('error', 'Error', $resultado);
            }
        } else {
            $this->setAlert('error', 'Error', 'Parámetros no válidos');
        }

        header("Location: " . $this->admin_url);
        exit;
    }

    private function setAlert($icon, $title, $text) {
        $_SESSION['alert'] = [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text
        ];
    }

    private function jsonOrRedirect($ok, $message) {
        $isFetch = isset($_SERVER['HTTP_ACCEPT']) &&
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isFetch || $isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => $ok, 'message' => $message]);
            exit;
        }

        $this->setAlert($ok ? 'success' : 'error', $ok ? 'Éxito' : 'Error', $message);
        header("Location: " . $this->admin_url);
        exit;
    }
}

$controller = new AdminUsuarioController($admin_url);
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'crear':
        $controller->crear();
        break;
    case 'editar':
        $controller->editar();
        break;
    case 'toggleEstado':
        $controller->toggleEstado();
        break;
    default:
        header("Location: $admin_url");
        exit;
}
?>

class AdminUsuarioController {
    private $usuarioModel;

    public function __construct() {
        $database = new Database();
        $db = $database->conectar();
        $this->usuarioModel = new Usuario($db);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $id_rol = trim($_POST['id_rol'] ?? '');

        if (empty($nombres) || empty($direccion) || empty($email) || empty($password) || empty($id_rol)) {
            $this->setAlert('warning', 'Campos incompletos', 'Debe completar todos los campos obligatorios');
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        if ($this->usuarioModel->existeCorreo($email)) {
            $this->setAlert('error', 'Correo existente', 'Este correo ya está registrado en el sistema');
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $datos = [
            'nombres' => $nombres,
            'direccion' => $direccion,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id_rol' => $id_rol,
            'codigo_estudiantil' => $_POST['codigo_estudiantil'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'grado_actual' => $_POST['grado_actual'] ?? null,
            'telefono' => $_POST['telefono'] ?? ''
        ];

        $resultado = $this->usuarioModel->registrar($datos);

        if ($resultado === true) {
            $this->setAlert('success', 'Éxito', 'Usuario creado correctamente');
            header("Location: ../views/dashboard/admin.php");
        } else {
            $this->setAlert('error', 'Error', $resultado);
            header("Location: ../views/dashboard/admin.php");
        }
        exit;
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;
        if (!$id_usuario) {
            $this->jsonOrRedirect(false, 'ID de usuario no proporcionado');
        }

        $datos = [
            'nombres'   => trim($_POST['nombres'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'id_rol'    => trim($_POST['id_rol'] ?? '')
        ];

        if (!empty($_POST['password'])) {
            $datos['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $resultado = $this->usuarioModel->actualizar($id_usuario, $datos);

        if ($resultado === true) {
            $this->jsonOrRedirect(true, 'Usuario actualizado correctamente');
        } else {
            $this->jsonOrRedirect(false, $resultado);
        }
    }

    public function toggleEstado() {
        $id_usuario = $_GET['id'] ?? null;
        $estado = $_GET['estado'] ?? null;

        if ($id_usuario !== null && $estado !== null) {
            $nuevo_estado = $estado == 1 ? 0 : 1;
            $resultado = $this->usuarioModel->cambiarEstado($id_usuario, $nuevo_estado);

            if ($resultado === true) {
                $status_text = $nuevo_estado == 1 ? 'activado' : 'desactivado';
                $this->setAlert('success', 'Éxito', "Usuario $status_text correctamente");
            } else {
                $this->setAlert('error', 'Error', $resultado);
            }
        } else {
            $this->setAlert('error', 'Error', 'Parámetros no válidos');
        }

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    private function setAlert($icon, $title, $text) {
        $_SESSION['alert'] = [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text
        ];
    }

    private function jsonOrRedirect($ok, $message) {
        // Si la petición viene de fetch/XHR, responde JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isFetch = isset($_SERVER['HTTP_ACCEPT']) && 
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if ($isAjax || $isFetch) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => $ok, 'message' => $message]);
            exit;
        }

        // Petición normal: usar sesión y redirigir
        $this->setAlert($ok ? 'success' : 'error', $ok ? 'Éxito' : 'Error', $message);
        header("Location: ../views/dashboard/admin.php");
        exit;
    }
}

$controller = new AdminUsuarioController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'crear':
        $controller->crear();
        break;
    case 'editar':
        $controller->editar();
        break;
    case 'toggleEstado':
        $controller->toggleEstado();
        break;
    default:
        header("Location: ../views/dashboard/admin.php");
        exit;
}
?>