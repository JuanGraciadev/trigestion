<?php
require 'config/database.php';
$db = (new Database())->conectar();

echo "=== TABLA VENTA ===" . PHP_EOL;
$cols = $db->query("SHOW COLUMNS FROM venta")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null:' . $c['Null'] . ' | Key:' . $c['Key'] . ' | Default:' . ($c['Default'] ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL . "=== TABLA DETALLE_VENTA ===" . PHP_EOL;
$cols2 = $db->query("SHOW COLUMNS FROM detalle_venta")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null:' . $c['Null'] . ' | Key:' . $c['Key'] . ' | Default:' . ($c['Default'] ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL . "=== TABLA USUARIOS (primeras 3 filas) ===" . PHP_EOL;
$users = $db->query("SELECT id_usuario, nombres, id_rol FROM usuarios LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID:{$u['id_usuario']} | {$u['nombres']} | Rol:{$u['id_rol']}" . PHP_EOL;
}

echo PHP_EOL . "=== PRODUCTOS (primeros 3) ===" . PHP_EOL;
$prods = $db->query("SELECT id_producto, nombre, precio FROM producto LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($prods as $p) {
    echo "ID:{$p['id_producto']} | {$p['nombre']} | Precio:{$p['precio']}" . PHP_EOL;
}
?>
