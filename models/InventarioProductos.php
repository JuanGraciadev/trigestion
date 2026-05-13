<?php
class InventarioProductos {
    private $conn;
    private $table = 'inventario_productos';

    public function __construct($db) {
        $this->conn = $db;
        $this->asegurarTabla();
    }

    private function asegurarTabla() {
        // Crear tabla base si no existe (sin FKs para máxima compatibilidad)
        $sql = "CREATE TABLE IF NOT EXISTS inventario_productos (
            id_inventario INT AUTO_INCREMENT PRIMARY KEY,
            fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
            bodega        VARCHAR(100) DEFAULT 'Principal',
            id_produccion INT,
            id_producto   INT,
            id_usuario    INT,
            cantidad      INT DEFAULT 0
        )";
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {}

        // Agregar columnas que pueden faltar en tablas ya existentes
        $columnas = [
            'bodega'   => "ALTER TABLE inventario_productos ADD COLUMN bodega VARCHAR(100) DEFAULT 'Principal'",
            'cantidad' => "ALTER TABLE inventario_productos ADD COLUMN cantidad INT DEFAULT 0",
        ];
        try {
            $existentes = $this->conn->query("SHOW COLUMNS FROM inventario_productos")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($columnas as $col => $alter) {
                if (!in_array($col, $existentes)) {
                    $this->conn->exec($alter);
                }
            }
        } catch (PDOException $e) {}
    }

    /** Inserta un registro de inventario al finalizar producción */
    public function registrarIngreso($datos) {
        // Evitar duplicados: si ya existe una entrada para esta producción, no registrar de nuevo
        $check = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE id_produccion = :id_produccion"
        );
        $check->execute([':id_produccion' => $datos['id_produccion']]);
        if ($check->fetchColumn() > 0) {
            return true; // ya fue registrado
        }

        $sql = "INSERT INTO {$this->table}
                    (fecha, bodega, id_produccion, id_producto, id_usuario, cantidad)
                VALUES
                    (NOW(), :bodega, :id_produccion, :id_producto, :id_usuario, :cantidad)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':bodega'        => $datos['bodega'] ?? 'Principal',
            ':id_produccion' => $datos['id_produccion'],
            ':id_producto'   => $datos['id_producto'],
            ':id_usuario'    => $datos['id_usuario'],
            ':cantidad'      => $datos['cantidad'],
        ]);
    }

    /** Lista todos los registros con datos de producto, producción y usuario */
    public function obtenerTodos() {
        $sql = "SELECT
                    inv.*,
                    prod.nombre   AS producto_nombre,
                    prod.precio   AS producto_precio,
                    prod.img      AS producto_img,
                    cat.nombre    AS categoria_nombre,
                    pro.lote_produccion,
                    u.nombres     AS usuario_nombre
                FROM {$this->table} inv
                LEFT JOIN producto     prod ON inv.id_producto   = prod.id_producto
                LEFT JOIN categoria    cat  ON prod.id_categoria = cat.id_categoria
                LEFT JOIN produccion   pro  ON inv.id_produccion = pro.id_produccion
                LEFT JOIN usuarios     u    ON inv.id_usuario    = u.id_usuario
                ORDER BY inv.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista todos los registros de salidas (ventas) */
    public function obtenerSalidas() {
        $sql = "SELECT 
                    dv.id_detalle_de_venta AS id_salida,
                    v.id_venta,
                    v.fecha,
                    v.estado,
                    prod.nombre AS producto_nombre,
                    cat.nombre  AS categoria_nombre,
                    dv.cantidad,
                    u.nombres   AS cliente_nombre
                FROM detalle_venta dv
                JOIN venta v ON dv.id_venta = v.id_venta
                LEFT JOIN producto prod ON dv.id_producto = prod.id_producto
                LEFT JOIN categoria cat ON prod.id_categoria = cat.id_categoria
                LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE v.estado != 'Cancelado'
                ORDER BY v.fecha DESC, v.id_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Stock total por producto */
    public function obtenerStockPorProducto() {
        $sql = "SELECT
                    prod.id_producto,
                    prod.nombre   AS producto_nombre,
                    prod.precio,
                    prod.img,
                    cat.nombre    AS categoria_nombre,
                    CAST(SUM(inv.cantidad) AS SIGNED) AS total_ingresado,
                    COALESCE((
                        SELECT CAST(SUM(dv.cantidad) AS SIGNED)
                        FROM detalle_venta dv
                        JOIN venta v ON dv.id_venta = v.id_venta
                        WHERE dv.id_producto = prod.id_producto
                          AND v.estado != 'Cancelado'
                    ), 0) AS total_vendido,
                    GREATEST(0, CAST(SUM(inv.cantidad) AS SIGNED) - COALESCE((
                        SELECT CAST(SUM(dv.cantidad) AS SIGNED)
                        FROM detalle_venta dv
                        JOIN venta v ON dv.id_venta = v.id_venta
                        WHERE dv.id_producto = prod.id_producto
                          AND v.estado != 'Cancelado'
                    ), 0)) AS total_unidades,
                    COUNT(inv.id_inventario) AS num_lotes,
                    MAX(inv.fecha) AS ultima_entrada
                FROM {$this->table} inv
                LEFT JOIN producto  prod ON inv.id_producto   = prod.id_producto
                LEFT JOIN categoria cat  ON prod.id_categoria = cat.id_categoria
                GROUP BY prod.id_producto, prod.nombre, prod.precio, prod.img, cat.nombre
                ORDER BY total_unidades DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Estadísticas para el dashboard */
    public function obtenerEstadisticas() {
        // Total unidades en inventario
        $totalIngresos = $this->conn->query(
            "SELECT COALESCE(SUM(cantidad), 0) AS total FROM {$this->table}"
        )->fetch(PDO::FETCH_ASSOC)['total'];

        $totalVentas = $this->conn->query(
            "SELECT COALESCE(SUM(dv.cantidad), 0) AS total 
             FROM detalle_venta dv 
             JOIN venta v ON dv.id_venta = v.id_venta 
             WHERE v.estado != 'Cancelado'"
        )->fetch(PDO::FETCH_ASSOC)['total'];

        $total = max(0, $totalIngresos - $totalVentas);

        // Número de productos distintos con stock
        $distintos = $this->conn->query(
            "SELECT COUNT(DISTINCT id_producto) AS cnt FROM {$this->table}"
        )->fetch(PDO::FETCH_ASSOC)['cnt'];

        // Últimas 7 fechas de ingreso (cantidad producida por día)
        $por_dia = $this->conn->query(
            "SELECT DATE(fecha) AS dia, SUM(cantidad) AS total
             FROM {$this->table}
             GROUP BY DATE(fecha)
             ORDER BY dia DESC
             LIMIT 7"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Top 5 productos por stock
        $top = $this->conn->query(
            "SELECT prod.nombre, 
                    GREATEST(0, CAST(SUM(inv.cantidad) AS SIGNED) - COALESCE((
                        SELECT CAST(SUM(dv.cantidad) AS SIGNED)
                        FROM detalle_venta dv
                        JOIN venta v ON dv.id_venta = v.id_venta
                        WHERE dv.id_producto = prod.id_producto
                          AND v.estado != 'Cancelado'
                    ), 0)) AS total
             FROM {$this->table} inv
             LEFT JOIN producto prod ON inv.id_producto = prod.id_producto
             GROUP BY inv.id_producto, prod.nombre
             ORDER BY total DESC
             LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_unidades' => $total,
            'num_productos'  => $distintos,
            'por_dia'        => array_reverse($por_dia),
            'top_productos'  => $top,
        ];
    }

    /** Actualiza bodega de un registro específico */
    public function actualizarBodega($id, $bodega) {
        $sql = "UPDATE {$this->table} SET bodega = :bodega WHERE id_inventario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':bodega' => $bodega, ':id' => $id]);
    }
}
?>
