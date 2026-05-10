<?php
class Lote {
    private $conn;
    private $table_lote = "lote";
    private $table_detalles = "detalles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $query = "SELECT l.*, u.nombres as usuario_nombre, 
                         (SELECT COUNT(*) FROM " . $this->table_detalles . " d WHERE d.id_lote = l.id_lote) as num_detalles
                  FROM " . $this->table_lote . " l 
                  LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario 
                  ORDER BY l.id_lote DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerLote($id_lote) {
        $query = "SELECT l.*, u.nombres as usuario_nombre FROM " . $this->table_lote . " l LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario WHERE l.id_lote = :id_lote";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_lote', $id_lote);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($codigo_lote, $id_usuario) {
        $query = "INSERT INTO " . $this->table_lote . " (codigo_lote, id_usuario) VALUES (:codigo_lote, :id_usuario)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo_lote', $codigo_lote);
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function editar($id_lote, $codigo_lote) {
        $query = "UPDATE " . $this->table_lote . " SET codigo_lote = :codigo_lote WHERE id_lote = :id_lote";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo_lote', $codigo_lote);
        $stmt->bindParam(':id_lote', $id_lote);
        return $stmt->execute();
    }

    public function eliminar($id_lote) {
        // Primero, obtener los id_detalles vinculados al lote
        $queryDet = "SELECT id_detalles FROM " . $this->table_detalles . " WHERE id_lote = :id_lote";
        $stmtDet = $this->conn->prepare($queryDet);
        $stmtDet->bindParam(':id_lote', $id_lote);
        $stmtDet->execute();
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        // Si existen detalles, limpiar la tabla inventario_materia_prima
        if (!empty($detalles)) {
            $ids = array_column($detalles, 'id_detalles');
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $queryInv = "DELETE FROM inventario_materia_prima WHERE id_detalles IN ($inQuery)";
            $stmtInv = $this->conn->prepare($queryInv);
            foreach ($ids as $k => $id) {
                $stmtInv->bindValue(($k+1), $id);
            }
            try { $stmtInv->execute(); } catch (PDOException $e) {}
        }

        // Eliminar los detalles (por si no hay CASCADE)
        $queryDelDet = "DELETE FROM " . $this->table_detalles . " WHERE id_lote = :id_lote";
        $stmtDelDet = $this->conn->prepare($queryDelDet);
        $stmtDelDet->bindParam(':id_lote', $id_lote);
        $stmtDelDet->execute();

        // Finalmente, eliminar el lote
        $queryLote = "DELETE FROM " . $this->table_lote . " WHERE id_lote = :id_lote";
        $stmtLote = $this->conn->prepare($queryLote);
        $stmtLote->bindParam(':id_lote', $id_lote);
        return $stmtLote->execute();
    }

    // Detalles methods
    public function obtenerDetallesPorLote($id_lote) {
        $query = "SELECT * FROM " . $this->table_detalles . " WHERE id_lote = :id_lote ORDER BY id_detalles DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_lote', $id_lote);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearDetalle($datos) {
        $query = "INSERT INTO " . $this->table_detalles . " (unidades, tipo_envase, capacidad, proveedor, id_lote) 
                  VALUES (:unidades, :tipo_envase, :capacidad, :proveedor, :id_lote)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':unidades', $datos['unidades']);
        $stmt->bindParam(':tipo_envase', $datos['tipo_envase']);
        $stmt->bindParam(':capacidad', $datos['capacidad']);
        $stmt->bindParam(':proveedor', $datos['proveedor']);
        $stmt->bindParam(':id_lote', $datos['id_lote']);
        
        if ($stmt->execute()) {
            $id_detalles = $this->conn->lastInsertId();
            
            // Insertar automáticamente en inventario_materia_prima
            try {
                $queryInv = "INSERT INTO inventario_materia_prima (ingreso, fecha, bodega, id_detalles) 
                             VALUES (:ingreso, NOW(), 'Bodega Principal', :id_detalles)";
                $stmtInv = $this->conn->prepare($queryInv);
                $stmtInv->bindParam(':ingreso', $datos['unidades']);
                $stmtInv->bindParam(':id_detalles', $id_detalles);
                $stmtInv->execute();
            } catch (PDOException $e) {
                // Si la tabla no existe, la creamos y reintentamos
                $createTable = "CREATE TABLE IF NOT EXISTS inventario_materia_prima (
                    id_inventario_materia INT AUTO_INCREMENT PRIMARY KEY,
                    ingreso INT,
                    fecha DATETIME,
                    bodega VARCHAR(100),
                    id_detalles INT,
                    id_retornables INT
                )";
                $this->conn->exec($createTable);
                
                $stmtInv = $this->conn->prepare($queryInv);
                $stmtInv->bindParam(':ingreso', $datos['unidades']);
                $stmtInv->bindParam(':id_detalles', $id_detalles);
                $stmtInv->execute();
            }
            
            return true;
        }
        return false;
    }
    
    public function editarDetalle($id_detalles, $datos) {
        $query = "UPDATE " . $this->table_detalles . " SET unidades = :unidades, tipo_envase = :tipo_envase, capacidad = :capacidad, proveedor = :proveedor WHERE id_detalles = :id_detalles";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':unidades', $datos['unidades']);
        $stmt->bindParam(':tipo_envase', $datos['tipo_envase']);
        $stmt->bindParam(':capacidad', $datos['capacidad']);
        $stmt->bindParam(':proveedor', $datos['proveedor']);
        $stmt->bindParam(':id_detalles', $id_detalles);
        
        return $stmt->execute();
    }
}
?>
