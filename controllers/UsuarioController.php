<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    public function registrar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $documento_numero = trim($_POST['documento_numero'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');

        $rol_name = $_POST['rol'] ?? 'cliente';
        $id_rol = ($rol_name === 'cliente') ? '3' : '3';

        // Validaciones
        if (empty($nombres) || empty($direccion) || empty($email) || empty($documento_numero) || empty($password) || empty($confirmar_password)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Campos incompletos',
                'text' => 'Debe completar todos los campos'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Correo inválido',
                'text' => 'Ingrese un correo válido'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Las contraseñas no coinciden'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Contraseña inválida',
                'text' => 'La contraseña debe tener al menos 6 caracteres' 
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if ($id_rol === '3' && empty($telefono)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Teléfono requerido',
                'text' => 'Debe ingresar el teléfono'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $database = new Database();
        $db = $database->conectar();

        $usuario = new Usuario($db);

        if ($usuario->existeCorreo($email)) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Correo existente',
                'text' => 'Este correo ya está registrado'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $datos = [
            'nombres' => $nombres,
            'direccion' => $direccion,
            'email' => $email,
            'documento_numero' => $documento_numero,
            'telefono' => $telefono,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id_rol' => $id_rol,
        ];

        $resultado = $usuario->registrar($datos);

        if ($resultado === true) {
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Registro exitoso',
                'text' => 'Tu cuenta fue creada correctamente',
                'redirect' => 'login.php'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => $resultado
            ];
        }

        header("Location: ../views/usuarios/registre.php");
        exit;
    }
}

$controller = new UsuarioController();
$controller->registrar();