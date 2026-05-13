/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `dbmoova` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `dbmoova`;

CREATE TABLE IF NOT EXISTS `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `categoria` (`id_categoria`, `nombre`, `descripcion`, `imagen`, `estado`) VALUES
	(1, 'garrafones', 'de gran capacidad', '../../img/categorias/cat_69fc141fa4927.jpeg', 1),
	(2, 'consumo personal', 'botellas de agua de consumo personal', '../../img/categorias/cat_69fc9c676360b.png', 1);

CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `id_usuarios` int DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `id_usuarios` (`id_usuarios`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_usuarios`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `cliente` (`id_cliente`, `id_usuarios`) VALUES
	(1, 2),
	(2, 3),
	(3, 4);

CREATE TABLE IF NOT EXISTS `detalles` (
  `id_detalles` int NOT NULL AUTO_INCREMENT,
  `unidades` int DEFAULT NULL,
  `tipo_envase` varchar(50) DEFAULT NULL,
  `capacidad` varchar(50) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `id_lote` int DEFAULT NULL,
  PRIMARY KEY (`id_detalles`),
  KEY `id_lote` (`id_lote`),
  CONSTRAINT `detalles_ibfk_1` FOREIGN KEY (`id_lote`) REFERENCES `lote` (`id_lote`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `detalles` (`id_detalles`, `unidades`, `tipo_envase`, `capacidad`, `proveedor`, `id_lote`) VALUES
	(1, 200, 'Garrafon', '12 LT', 'PLASTICOS ANDINA', 1),
	(2, 400, 'BOTELLA DE CUELLO LARGO', '800 ML', 'PLASTICOS ANDINA', 2),
	(3, 700, 'bolsa de agua pequeña', '600 ML', 'PLASTICOS ANDINA', 3),
	(4, 500, 'bolsa de agua grande', '5 LT', 'PLASTICOS ANDINA', 4);

CREATE TABLE IF NOT EXISTS `detalle_venta` (
  `id_detalle_de_venta` int NOT NULL AUTO_INCREMENT,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `descuento` float DEFAULT NULL,
  `id_venta` int DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `id_producto` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_detalle_de_venta`),
  KEY `id_venta` (`id_venta`),
  CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `detalle_venta` (`id_detalle_de_venta`, `precio_unitario`, `descuento`, `id_venta`, `cantidad`, `id_producto`) VALUES
	(1, 7000.00, 0, 1, 1, 1),
	(2, 7000.00, 0, 2, 1, 1),
	(3, 7000.00, 0, 3, 1, 1),
	(4, 7000.00, 0, 4, 1, 1),
	(5, 7000.00, 0, 5, 1, 1),
	(6, 7000.00, 0, 6, 4, 1);

CREATE TABLE IF NOT EXISTS `devolucion_retornables` (
  `id_retornables` int NOT NULL AUTO_INCREMENT,
  `cantidad` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id_retornables`),
  KEY `id_producto` (`id_producto`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `devolucion_retornables_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  CONSTRAINT `devolucion_retornables_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `inventario_materia_prima` (
  `id_inventario_materia` int NOT NULL AUTO_INCREMENT,
  `ingreso` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `bodega` varchar(100) DEFAULT NULL,
  `id_detalles` int DEFAULT NULL,
  `id_retornables` int DEFAULT NULL,
  PRIMARY KEY (`id_inventario_materia`),
  KEY `id_detalles` (`id_detalles`),
  KEY `id_retornables` (`id_retornables`),
  CONSTRAINT `inventario_materia_prima_ibfk_1` FOREIGN KEY (`id_detalles`) REFERENCES `detalles` (`id_detalles`),
  CONSTRAINT `inventario_materia_prima_ibfk_2` FOREIGN KEY (`id_retornables`) REFERENCES `devolucion_retornables` (`id_retornables`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `inventario_materia_prima` (`id_inventario_materia`, `ingreso`, `fecha`, `bodega`, `id_detalles`, `id_retornables`) VALUES
	(1, 120, '2026-05-06', 'Bodega Principal', 1, NULL),
	(2, 400, '2026-05-06', 'Bodega Principal', 2, NULL),
	(3, 700, '2026-05-06', 'Bodega Principal', 3, NULL),
	(4, 500, '2026-05-06', 'Bodega Principal', 4, NULL);

CREATE TABLE IF NOT EXISTS `inventario_productos` (
  `id_inventario` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `bodega` varchar(100) DEFAULT NULL,
  `id_produccion` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `cantidad` int DEFAULT '0',
  PRIMARY KEY (`id_inventario`),
  KEY `id_produccion` (`id_produccion`),
  KEY `id_producto` (`id_producto`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `inventario_productos_ibfk_1` FOREIGN KEY (`id_produccion`) REFERENCES `produccion` (`id_produccion`),
  CONSTRAINT `inventario_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  CONSTRAINT `inventario_productos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `inventario_productos` (`id_inventario`, `fecha`, `bodega`, `id_produccion`, `id_producto`, `id_usuario`, `cantidad`) VALUES
	(1, '2026-05-07', 'Principal', 1, 1, 3, 80);

CREATE TABLE IF NOT EXISTS `lote` (
  `id_lote` int NOT NULL AUTO_INCREMENT,
  `codigo_lote` varchar(100) DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id_lote`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `lote_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `lote` (`id_lote`, `codigo_lote`, `id_usuario`) VALUES
	(1, 'garrafonHTK898', 2),
	(2, 'botellacuellolargo/HKUTR909', 2),
	(3, 'bolsa600/MRTY96036', 2),
	(4, 'LTR117', 2);

CREATE TABLE IF NOT EXISTS `produccion` (
  `id_produccion` int NOT NULL AUTO_INCREMENT,
  `lote_produccion` varchar(100) DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `descripcion` text,
  `id_usuario` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `id_inventario_materia` int DEFAULT NULL,
  PRIMARY KEY (`id_produccion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_producto` (`id_producto`),
  KEY `id_inventario_materia` (`id_inventario_materia`),
  CONSTRAINT `produccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `produccion_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  CONSTRAINT `produccion_ibfk_3` FOREIGN KEY (`id_inventario_materia`) REFERENCES `inventario_materia_prima` (`id_inventario_materia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `produccion` (`id_produccion`, `lote_produccion`, `cantidad`, `estado`, `descripcion`, `id_usuario`, `id_producto`, `id_inventario_materia`) VALUES
	(1, '1', 80, 'Finalizada', '', 3, 1, 1);

CREATE TABLE IF NOT EXISTS `producto` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `img` blob,
  `id_usuario` int DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_producto`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `producto` (`id_producto`, `nombre`, `precio`, `img`, `id_usuario`, `id_categoria`, `estado`) VALUES
	(1, 'garrafones', 7000.00, _binary 0x2e2e2f2e2e2f696d672f70726f647563746f732f70726f645f363966633134393365393764662e6a706567, 2, 1, 1);

CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `rol` (`id_rol`, `nombre`) VALUES
	(1, 'administrador'),
	(2, 'trabajador'),
	(3, 'cliente');

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `documento_numero` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_rol` int DEFAULT NULL,
  `estado` tinyint DEFAULT '1' COMMENT '1: Activo, 0: Inactivo',
  PRIMARY KEY (`id_usuario`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `usuarios` (`id_usuario`, `nombres`, `direccion`, `email`, `documento_numero`, `telefono`, `password`, `id_rol`, `estado`) VALUES
	(2, 'Juan Jose Gracia Cocoma', 'cra 4 #21-32 barrio panama', 'jjgc710@gmail.com', '1079172033', '3228518645', '$2y$10$1mGQd49HdkHT4Ud.fB3IZ.leZ80hHp6xEf3J7LCqn0Kf975AETP2e', 1, 1),
	(3, 'Osman David Ovalle', 'Cra 4 #21-50 barrio panama', 'osman@gmail.com', '1079174661', '3204417080', '$2y$10$YmU8MHcOmdeV.oy8FhsEV.XWlQeH3kEzLJzB6E0k14o5wdKI3hDb2', 2, 1),
	(4, 'Angie andrade', 'Calle 50 #34-47', 'angie@gmail.com', '1079173034', '3238528746', '$2y$10$xSLYi3T3Zx5AS7NWUVv4M.S965OgERKUBTW.V3Fl1iJ4RullUizGy', 3, 1);

CREATE TABLE IF NOT EXISTS `venta` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `id_cliente` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `total` decimal(10,2) DEFAULT '0.00',
  `notas` text,
  PRIMARY KEY (`id_venta`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `venta_ibfk_3` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `venta` (`id_venta`, `fecha`, `cantidad`, `precio`, `estado`, `id_cliente`, `id_usuario`, `id_producto`, `total`, `notas`) VALUES
	(1, '2026-05-10', NULL, NULL, 'Entregado', 3, 2, NULL, 7000.00, ''),
	(2, '2026-05-10', NULL, NULL, 'Entregado', 3, 2, NULL, 7000.00, ''),
	(3, '2026-05-10', NULL, NULL, 'Entregado', 3, 2, NULL, 7000.00, ''),
	(4, '2026-05-10', NULL, NULL, 'Entregado', 3, 2, NULL, 7000.00, ''),
	(5, '2026-05-11', NULL, NULL, 'Entregado', 3, 2, NULL, 7000.00, ''),
	(6, '2026-05-11', NULL, NULL, 'Cancelado', 3, 2, NULL, 28000.00, '');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
