<?php
class Venta {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ─── Stock disponible por producto ─────────────────────────────────────────
    public function stockDisponible($id_producto) {
        try {
            // Total ingresado al inventario de productos terminados
            $q = "SELECT COALESCE(SUM(cantidad),0) FROM inventario_productos WHERE id_producto = :id";
            $st = $this->conn->prepare($q);
            $st->execute([':id' => $id_producto]);
            $ingresado = (int)$st->fetchColumn();

            // Total vendido (estados que descuentan stock: todo excepto Cancelado)
            $q2 = "SELECT COALESCE(SUM(dv.cantidad),0)
                   FROM detalle_venta dv
                   JOIN venta v ON dv.id_venta = v.id_venta
                   WHERE dv.id_producto = :id
                     AND (v.estado IS NULL OR v.estado != 'Cancelado')";
            $st2 = $this->conn->prepare($q2);
            $st2->execute([':id' => $id_producto]);
            $vendido = (int)$st2->fetchColumn();

            return max(0, $ingresado - $vendido);
        } catch (PDOException $e) {
            return 0;
        }
    }

    // ─── Crear venta desde cliente (carrito) ──────────────────────────────────
    public function crearVenta($id_usuario, $items, $notas = '') {
        $this->conn->beginTransaction();
        try {
            // Validar stock
            foreach ($items as $item) {
                $stock = $this->stockDisponible($item['id_producto']);
                if ($stock < $item['cantidad']) {
                    $this->conn->rollBack();
                    return ['ok' => false, 'msg' => 'Stock insuficiente para el producto ID '.$item['id_producto']];
                }
            }

            // Buscar id_cliente asociado al id_usuario
            $stmtC = $this->conn->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = :id_usuario");
            $stmtC->execute([':id_usuario' => $id_usuario]);
            $id_cliente = $stmtC->fetchColumn();

            if (!$id_cliente) {
                $stmtInsert = $this->conn->prepare("INSERT INTO cliente (id_usuario) VALUES (:id)");
                $stmtInsert->execute([':id' => $id_usuario]);
                $id_cliente = $this->conn->lastInsertId();
            }

            // Calcular total
            $total = 0;
            foreach ($items as $item) {
                $total += ($item['precio_unitario'] * $item['cantidad']) - ($item['descuento'] ?? 0);
            }

            // Insertar cabecera en tabla venta
            $stmt = $this->conn->prepare(
                "INSERT INTO venta (id_cliente, id_usuario, estado, total, notas, fecha)
                 VALUES (:id_cliente, :id_usuario, 'Pendiente', :total, :notas, CURDATE())"
            );
            $stmt->execute([
                ':id_cliente' => $id_cliente,
                ':id_usuario' => $id_usuario,
                ':total'      => $total,
                ':notas'      => $notas,
            ]);
            $id_venta = $this->conn->lastInsertId();

            // Insertar detalle de cada producto
            $stmtD = $this->conn->prepare(
                "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, descuento)
                 VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :descuento)"
            );
            foreach ($items as $item) {
                $stmtD->execute([
                    ':id_venta'        => $id_venta,
                    ':id_producto'     => $item['id_producto'],
                    ':cantidad'        => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':descuento'       => $item['descuento'] ?? 0,
                ]);
            }

            $this->conn->commit();
            return ['ok' => true, 'id_venta' => $id_venta];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    // ─── Crear venta directa desde punto de venta (admin) ─────────────────────
    public function crearVentaPOS($id_usuario_cliente, $items, $notas, $id_usuario_admin) {
        $this->conn->beginTransaction();
        try {
            // Validar stock
            foreach ($items as $item) {
                $stock = $this->stockDisponible($item['id_producto']);
                if ($stock < $item['cantidad']) {
                    $this->conn->rollBack();
                    return ['ok' => false, 'msg' => 'Stock insuficiente para el producto ID '.$item['id_producto']];
                }
            }

            // Buscar id_cliente asociado al id_usuario del cliente
            $stmtC = $this->conn->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = :id_usuario");
            $stmtC->execute([':id_usuario' => $id_usuario_cliente]);
            $id_cliente = $stmtC->fetchColumn();

            if (!$id_cliente) {
                $stmtInsert = $this->conn->prepare("INSERT INTO cliente (id_usuario) VALUES (:id)");
                $stmtInsert->execute([':id' => $id_usuario_cliente]);
                $id_cliente = $this->conn->lastInsertId();
            }

            // Calcular total
            $total = 0;
            foreach ($items as $item) {
                $total += ($item['precio_unitario'] * $item['cantidad']) - ($item['descuento'] ?? 0);
            }

            // Insertar venta como Entregado (venta presencial)
            $stmt = $this->conn->prepare(
                "INSERT INTO venta (id_cliente, id_usuario, estado, total, notas, fecha)
                 VALUES (:id_cliente, :id_usuario, 'Entregado', :total, :notas, CURDATE())"
            );
            $stmt->execute([
                ':id_cliente' => $id_cliente,
                ':id_usuario' => $id_usuario_admin, // el admin que registró la venta
                ':total'      => $total,
                ':notas'      => $notas,
            ]);
            $id_venta = $this->conn->lastInsertId();

            // Insertar detalles
            $stmtD = $this->conn->prepare(
                "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, descuento)
                 VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :descuento)"
            );
            foreach ($items as $item) {
                $stmtD->execute([
                    ':id_venta'        => $id_venta,
                    ':id_producto'     => $item['id_producto'],
                    ':cantidad'        => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':descuento'       => $item['descuento'] ?? 0,
                ]);
            }

            $this->conn->commit();
            return ['ok' => true, 'id_venta' => $id_venta, 'total' => $total];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    // ─── Cambiar estado de una venta (admin) ───────────────────────────────────
    public function cambiarEstado($id_venta, $estado, $id_usuario = null) {
        $sql = "UPDATE venta SET estado = :estado, id_usuario = :id_usuario WHERE id_venta = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':estado'     => $estado,
            ':id_usuario' => $id_usuario,
            ':id'         => $id_venta,
        ]);
    }

    // ─── Cancelar una venta ────────────────────────────────────────────────────
    public function cancelar($id_venta) {
        return $this->cambiarEstado($id_venta, 'Cancelado');
    }

    // ─── Obtener todas las ventas (admin) ─────────────────────────────────────
    public function obtenerTodas() {
        $sql = "SELECT v.*,
                       u.nombres  AS cliente_nombre,
                       u.email    AS cliente_email,
                       u.telefono AS cliente_telefono,
                       u.direccion AS cliente_direccion
                FROM venta v
                LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                ORDER BY v.id_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Detalle de una venta (productos) ─────────────────────────────────────
    public function obtenerDetalle($id_venta) {
        $sql = "SELECT dv.*, p.nombre AS producto_nombre, p.img AS producto_img
                FROM detalle_venta dv
                LEFT JOIN producto p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_venta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Ventas de un cliente ─────────────────────────────────────────────────
    public function obtenerPorCliente($id_usuario) {
        $sql = "SELECT v.*, GROUP_CONCAT(p.nombre SEPARATOR ', ') AS productos_lista
                FROM venta v
                LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
                LEFT JOIN producto p       ON dv.id_producto = p.id_producto
                LEFT JOIN cliente c        ON v.id_cliente = c.id_cliente
                WHERE c.id_usuario = :id_usuario
                GROUP BY v.id_venta
                ORDER BY v.id_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Estadísticas para el dashboard admin ─────────────────────────────────
    public function obtenerEstadisticas() {
        $estados = ['Pendiente','En Proceso','Entregado','Cancelado'];
        $stats = [];
        foreach ($estados as $e) {
            try {
                $st = $this->conn->prepare("SELECT COUNT(*) FROM venta WHERE estado = :e");
                $st->execute([':e' => $e]);
                $stats[$e] = (int)$st->fetchColumn();
            } catch (PDOException $ex) {
                $stats[$e] = 0;
            }
        }
        try {
            $total = $this->conn
                ->query("SELECT COALESCE(SUM(total),0) FROM venta WHERE estado NOT IN ('Pendiente', 'Cancelado')")
                ->fetchColumn();
            $stats['total_ingresos'] = $total;
        } catch (PDOException $ex) {
            $stats['total_ingresos'] = 0;
        }
        return $stats;
    }

    // ─── Cantidad de pedidos pendientes (para notificación) ───────────────────
    public function contarPendientes() {
        $st = $this->conn->prepare("SELECT COUNT(*) FROM venta WHERE estado = 'Pendiente'");
        $st->execute();
        return (int)$st->fetchColumn();
    }

    // ─── Obtener lista de clientes (para ventas POS del admin) ────────────────
    public function obtenerClientes() {
        $sql = "SELECT u.id_usuario, u.nombres, u.email, u.telefono, u.documento_numero
                FROM usuarios u
                WHERE u.id_rol = 3
                ORDER BY u.nombres ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
