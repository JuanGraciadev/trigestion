-- ===================================================
-- MÓDULO DE VENTAS - MOOVA! TRIGESTION
-- Ejecutar en: dbmoova
-- ===================================================

-- Tabla principal de ventas / pedidos
CREATE TABLE IF NOT EXISTS `venta` (
    `id_venta`    INT AUTO_INCREMENT PRIMARY KEY,
    `fecha`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado`      ENUM('Pendiente','En Proceso','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    `id_cliente`  INT NOT NULL,
    `id_usuario`  INT DEFAULT NULL,  -- admin que gestionó
    `total`       DECIMAL(10,2) DEFAULT 0.00,
    `notas`       TEXT DEFAULT NULL,
    FOREIGN KEY (`id_cliente`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Detalle de cada producto en la venta
CREATE TABLE IF NOT EXISTS `detalle_venta` (
    `id_detalle_venta` INT AUTO_INCREMENT PRIMARY KEY,
    `id_venta`         INT NOT NULL,
    `id_producto`      INT NOT NULL,
    `cantidad`         INT NOT NULL DEFAULT 1,
    `precio_unitario`  DECIMAL(10,2) NOT NULL,
    `descuento`        DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (`id_venta`)    REFERENCES `venta`(`id_venta`)    ON DELETE CASCADE,
    FOREIGN KEY (`id_producto`) REFERENCES `producto`(`id_producto`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
