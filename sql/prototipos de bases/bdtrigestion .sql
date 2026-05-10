CREATE TABLE `rol` (
  `id_rol` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255)
);

CREATE TABLE `usuarios` (
  `id_usuarios` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `direccion` varchar(255),
  `documento_numero` varchar(255),
  `telefono` varchar(255),
  `correo` varchar(255),
  `contraseña` varchar(255),
  `fk_id_rol` integer
);

CREATE TABLE `administrador` (
  `id_administrador` integer PRIMARY KEY AUTO_INCREMENT,
  `fk_id_usuarios` integer
);

CREATE TABLE `trabajador` (
  `id_trabajador` integer PRIMARY KEY AUTO_INCREMENT,
  `fk_id_usuarios` integer
);

CREATE TABLE `cliente` (
  `id_cliente` integer PRIMARY KEY AUTO_INCREMENT,
  `fk_id_usuarios` integer
);

CREATE TABLE `materia_prima` (
  `id_materia_prima` integer PRIMARY KEY AUTO_INCREMENT,
  `lote` varchar(255),
  `unidadesxlote` integer,
  `material` varchar(255),
  `capacidad` varchar(255),
  `proveedor` varchar(255),
  `fk_id_trabajador` integer,
  `fk_id_administrador` integer
);

CREATE TABLE `inventario_materia_prima` (
  `id_inventario_materia` integer PRIMARY KEY AUTO_INCREMENT,
  `ingreso` integer,
  `salida` integer,
  `fecha` date,
  `bodega` varchar(255),
  `fk_id_materia_prima` integer
);

CREATE TABLE `produccion` (
  `id_produccion` integer PRIMARY KEY AUTO_INCREMENT,
  `fecha` date,
  `observaciones` varchar(255),
  `fk_id_producto` integer,
  `fk_id_inventario_materia` integer
);

CREATE TABLE `categoria` (
  `id_categoria` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` varchar(255),
  `imagen` varchar(255)
);

CREATE TABLE `producto` (
  `id_producto` integer PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `precio` decimal,
  `img` blob,
  `fk_id_administrador` integer,
  `fk_id_categoria` integer
);

CREATE TABLE `inventario_productos` (
  `id_inventario` integer PRIMARY KEY AUTO_INCREMENT,
  `cantidad_ingreso` integer,
  `cantidad_salida` integer,
  `fecha` date,
  `bodega` varchar(255),
  `fk_id_produccion` integer,
  `fk_id_administrador` integer,
  `fk_id_producto` integer,
  `fk_id_trabajador` integer
);

CREATE TABLE `venta` (
  `id_venta` integer PRIMARY KEY AUTO_INCREMENT,
  `tipo` varchar(255),
  `fecha` date,
  `monto` decimal,
  `fk_id_cliente` integer,
  `fk_id_administrador` integer,
  `fk_id_producto` integer
);

CREATE TABLE `detalle_venta` (
  `id_detalle_de_venta` integer PRIMARY KEY AUTO_INCREMENT,
  `precio_unitario` decimal,
  `cantidad` integer,
  `descuento` decimal,
  `fk_id_venta` integer
);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`fk_id_rol`) REFERENCES `rol` (`id_rol`);

ALTER TABLE `administrador` ADD FOREIGN KEY (`fk_id_usuarios`) REFERENCES `usuarios` (`id_usuarios`);

ALTER TABLE `trabajador` ADD FOREIGN KEY (`fk_id_usuarios`) REFERENCES `usuarios` (`id_usuarios`);

ALTER TABLE `cliente` ADD FOREIGN KEY (`fk_id_usuarios`) REFERENCES `usuarios` (`id_usuarios`);

ALTER TABLE `inventario_materia_prima` ADD FOREIGN KEY (`fk_id_materia_prima`) REFERENCES `materia_prima` (`id_materia_prima`);

ALTER TABLE `materia_prima` ADD FOREIGN KEY (`fk_id_trabajador`) REFERENCES `trabajador` (`id_trabajador`);

ALTER TABLE `materia_prima` ADD FOREIGN KEY (`fk_id_administrador`) REFERENCES `administrador` (`id_administrador`);

ALTER TABLE `produccion` ADD FOREIGN KEY (`fk_id_inventario_materia`) REFERENCES `inventario_materia_prima` (`id_inventario_materia`);

ALTER TABLE `produccion` ADD FOREIGN KEY (`fk_id_producto`) REFERENCES `producto` (`id_producto`);

ALTER TABLE `producto` ADD FOREIGN KEY (`fk_id_categoria`) REFERENCES `categoria` (`id_categoria`);

ALTER TABLE `producto` ADD FOREIGN KEY (`fk_id_administrador`) REFERENCES `administrador` (`id_administrador`);

ALTER TABLE `inventario_productos` ADD FOREIGN KEY (`fk_id_produccion`) REFERENCES `produccion` (`id_produccion`);

ALTER TABLE `inventario_productos` ADD FOREIGN KEY (`fk_id_producto`) REFERENCES `producto` (`id_producto`);

ALTER TABLE `inventario_productos` ADD FOREIGN KEY (`fk_id_administrador`) REFERENCES `administrador` (`id_administrador`);

ALTER TABLE `inventario_productos` ADD FOREIGN KEY (`fk_id_trabajador`) REFERENCES `trabajador` (`id_trabajador`);

ALTER TABLE `venta` ADD FOREIGN KEY (`fk_id_cliente`) REFERENCES `cliente` (`id_cliente`);

ALTER TABLE `venta` ADD FOREIGN KEY (`fk_id_administrador`) REFERENCES `administrador` (`id_administrador`);

ALTER TABLE `venta` ADD FOREIGN KEY (`fk_id_producto`) REFERENCES `producto` (`id_producto`);

ALTER TABLE `detalle_venta` ADD FOREIGN KEY (`fk_id_venta`) REFERENCES `venta` (`id_venta`);
