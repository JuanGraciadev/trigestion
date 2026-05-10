<?php
class Producto {
    private $conn;
    private $table_name = "producto";

    public function __construct($db) {
        $this->conn = $db;
        $this->checkAndCreateEstadoColumn();
    }

    private function checkAndCreateEstadoColumn() {
        try {
            $query = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'estado'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $alterQuery = "ALTER TABLE " . $this->table_name . " ADD COLUMN estado TINYINT(1) DEFAULT 1";
                $this->conn->exec($alterQuery);
            }
        } catch(PDOException $e) {
            // Ignore error if it fails (user might not have privileges to alter)
        }
    }

    public function obtenerTodos() {
        $query = "SELECT p.*, c.nombre as categoria_nombre FROM " . $this->table_name . " p LEFT JOIN categoria c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        // According to DB schema: id_producto, nombre, precio, img, fk_id_usuario, fk_id_categoria
        $query = "INSERT INTO " . $this->table_name . " (nombre, precio, img, id_usuario, id_categoria, estado) 
                  VALUES (:nombre, :precio, :img, :id_usuario, :id_categoria, 1)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':img', $datos['img']);
        $stmt->bindParam(':id_usuario', $datos['id_usuario']);
        $stmt->bindParam(':id_categoria', $datos['id_categoria']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function editar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre, precio = :precio, img = :img, id_categoria = :id_categoria WHERE id_producto = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':img', $datos['img']);
        $stmt->bindParam(':id_categoria', $datos['id_categoria']);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function cambiarEstado($id, $estado) {
        $nuevoEstado = $estado == 1 ? 0 : 1;
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id_producto = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
