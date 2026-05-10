<?php
class Categoria {
    private $conn;
    private $table_name = "categoria";

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

    public function obtenerTodas() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id_categoria DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, descripcion, imagen, estado) VALUES (:nombre, :descripcion, :imagen, 1)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':imagen', $datos['imagen']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function editar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre, descripcion = :descripcion, imagen = :imagen WHERE id_categoria = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':imagen', $datos['imagen']);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function cambiarEstado($id, $estado) {
        $nuevoEstado = $estado == 1 ? 0 : 1;
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id_categoria = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function obtenerProductosPorCategoria($id_categoria) {
        $query = "SELECT * FROM producto WHERE id_categoria = :id_categoria ORDER BY id_producto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
