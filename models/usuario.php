<?php
class Usuario {
    private $conn;
    private $tabla = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function existeCorreo($email) {
        $sql = "SELECT id_usuario FROM " . $this->tabla . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);+
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrar($datos) {
        try {
            $this->conn->beginTransaction();

            $sqlUsuario = "INSERT INTO usuarios
                (nombres, direccion, email, documento_numero, telefono, password, id_rol )
                VALUES
                (:nombres, :direccion, :email, :documento_numero, :telefono, :password, :id_rol )";

            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bindParam(":nombres", $datos['nombres']);
            $stmtUsuario->bindParam(":direccion", $datos['direccion']);
            $stmtUsuario->bindParam(":email", $datos['email']);
            $stmtUsuario->bindParam(":documento_numero", $datos["documento_numero"]);
            $stmtUsuario->bindParam(":telefono", $datos["telefono"]);
            $stmtUsuario->bindParam(":password", $datos['password']);
            $stmtUsuario->bindParam(":id_rol", $datos['id_rol']);
            $stmtUsuario->execute();

            $id_usuario = $this->conn->lastInsertId();

            if ($datos['id_rol'] === '3') {
                $sqlcliente = "INSERT INTO cliente
                    (id_usuarios)
                    VALUES
                    (:id_usuarios)";

                $stmtcliente = $this->conn->prepare($sqlcliente);
                $stmtcliente->bindParam(":id_usuarios", $id_usuario);
                $stmtcliente->execute();
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Error al registrar: " . $e->getMessage();
        }
    }

    public function obtenerTodos() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN rol r ON u.id_rol = r.id_rol 
                ORDER BY u.id_usuario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id_usuario) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id_usuario, $datos) {
        try {
            $sql = "UPDATE " . $this->tabla . " 
                    SET nombres = :nombres, direccion = :direccion, id_rol = :id_rol";
            
            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
            }
            
            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":nombres", $datos['nombres']);
            $stmt->bindParam(":direccion", $datos['direccion']);
            $stmt->bindParam(":id_rol", $datos['id_rol']);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            
            if (!empty($datos['password'])) {
                $stmt->bindParam(":password", $datos['password']);
            }
            
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al actualizar: " . $e->getMessage();
        }
    }

    public function obtenerRoles() {
        $sql = "SELECT * FROM rol ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cambiarEstado($id_usuario, $nuevo_estado) {
        try {
            $sql = "UPDATE usuarios SET estado = :estado WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":estado", $nuevo_estado, PDO::PARAM_INT);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al cambiar estado: " . $e->getMessage();
        }
    }
}
?>