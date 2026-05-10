<?php
class Produccion {
    private $conn;
    private $table = 'produccion';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodas() {
        $query = "SELECT p.*, prod.nombre as producto_nombre, u.nombres as usuario_nombre 
                  FROM " . $this->table . " p 
                  LEFT JOIN producto prod ON p.id_producto = prod.id_producto 
                  LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                  ORDER BY p.id_produccion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table . " 
                  (lote_produccion, cantidad, estado, descripcion, id_usuario, id_producto, id_inventario_materia) 
                  VALUES (:lote_produccion, :cantidad, :estado, :descripcion, :id_usuario, :id_producto, :id_inventario_materia)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':lote_produccion' => $datos['lote_produccion'],
            ':cantidad' => $datos['cantidad'],
            ':estado' => $datos['estado'] ?? 'En Producción',
            ':descripcion' => $datos['descripcion'],
            ':id_usuario' => $datos['id_usuario'],
            ':id_producto' => $datos['id_producto'],
            ':id_inventario_materia' => $datos['id_inventario_materia'] ?? null
        ]);
    }

    public function actualizarEstado($id, $estado) {
        $query = "UPDATE " . $this->table . " SET estado = :estado WHERE id_produccion = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_produccion = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
