<?php
class InventarioMP {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        $this->asegurarTabla();
    }

    private function asegurarTabla() {
        $createTable = "CREATE TABLE IF NOT EXISTS inventario_materia_prima (
            id_inventario_materia INT AUTO_INCREMENT PRIMARY KEY,
            ingreso INT,
            fecha DATETIME,
            bodega VARCHAR(100),
            id_detalles INT,
            id_retornables INT
        )";
        try {
            $this->conn->exec($createTable);
        } catch(PDOException $e) {}
    }

    public function obtenerTodos() {
        $query = "SELECT i.*, d.tipo_envase, d.capacidad, d.proveedor, l.codigo_lote 
                FROM inventario_materia_prima i 
                LEFT JOIN detalles d ON i.id_detalles = d.id_detalles
                LEFT JOIN lote l ON d.id_lote = l.id_lote
                ORDER BY i.fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticas() {
        // Stock actual total (suma de ingreso actual restante)
        $total = $this->conn->query(
            "SELECT COALESCE(SUM(ingreso), 0) AS total_mp FROM inventario_materia_prima"
        )->fetch(PDO::FETCH_ASSOC)['total_mp'] ?? 0;

        // Total consumido en producción (suma de cantidad de producciones finalizadas con MP vinculada)
        $totalEgreso = $this->conn->query(
            "SELECT COALESCE(SUM(p.cantidad), 0) AS total_egreso
             FROM produccion p
             WHERE p.estado = 'Finalizada'
               AND p.id_inventario_materia IS NOT NULL"
        )->fetch(PDO::FETCH_ASSOC)['total_egreso'] ?? 0;

        // Por tipo de envase (stock actual)
        $por_tipo = $this->conn->query(
            "SELECT d.tipo_envase, SUM(i.ingreso) AS total
             FROM inventario_materia_prima i
             JOIN detalles d ON i.id_detalles = d.id_detalles
             GROUP BY d.tipo_envase"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Ingresos por día (últimos 7 días)
        $por_dia = $this->conn->query(
            "SELECT DATE(fecha) AS dia, SUM(ingreso) AS total
             FROM inventario_materia_prima
             GROUP BY DATE(fecha)
             ORDER BY dia DESC LIMIT 7"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Egresos por día — usando inventario_productos.fecha (fuente confiable)
        try {
            $egresos_dia = $this->conn->query(
                "SELECT DATE(ip.fecha) AS dia, SUM(ip.cantidad) AS total
                 FROM inventario_productos ip
                 JOIN produccion p ON ip.id_produccion = p.id_produccion
                 WHERE p.id_inventario_materia IS NOT NULL
                 GROUP BY DATE(ip.fecha)
                 ORDER BY dia DESC LIMIT 7"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $egresos_dia = [];
        }

        return [
            'total'        => $total,
            'total_egreso' => $totalEgreso,
            'por_tipo'     => $por_tipo,
            'por_dia'      => array_reverse($por_dia),
            'egresos_dia'  => array_reverse($egresos_dia),
        ];
    }

    /** Detalle de cada consumo: qué producción usó qué MP y cuánto */
    public function obtenerEgresos() {
        $sql = "SELECT
                    p.id_produccion,
                    p.lote_produccion,
                    p.cantidad        AS cantidad_consumida,
                    p.id_inventario_materia,
                    imp.bodega,
                    d.tipo_envase,
                    d.capacidad,
                    prod.nombre       AS producto_nombre,
                    u.nombres         AS usuario_nombre,
                    ip.fecha          AS fecha_egreso
                FROM produccion p
                JOIN inventario_materia_prima imp ON p.id_inventario_materia = imp.id_inventario_materia
                LEFT JOIN detalles d    ON imp.id_detalles  = d.id_detalles
                LEFT JOIN producto prod ON p.id_producto    = prod.id_producto
                LEFT JOIN usuarios u    ON p.id_usuario     = u.id_usuario
                LEFT JOIN inventario_productos ip ON ip.id_produccion = p.id_produccion
                WHERE p.estado = 'Finalizada'
                  AND p.id_inventario_materia IS NOT NULL
                ORDER BY ip.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Descuenta 'cantidad' del campo 'ingreso' del registro de materia prima.
     * No permite que quede en negativo.
     */
    public function descontarStock($id_inventario_materia, $cantidad) {
        // Obtener el stock actual
        $stmt = $this->conn->prepare(
            "SELECT ingreso FROM inventario_materia_prima WHERE id_inventario_materia = :id"
        );
        $stmt->execute([':id' => $id_inventario_materia]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false; // registro no encontrado

        $nuevo = max(0, intval($row['ingreso']) - intval($cantidad));

        $upd = $this->conn->prepare(
            "UPDATE inventario_materia_prima SET ingreso = :nuevo WHERE id_inventario_materia = :id"
        );
        return $upd->execute([':nuevo' => $nuevo, ':id' => $id_inventario_materia]);
    }
}
?>
