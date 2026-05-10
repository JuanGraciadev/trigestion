<?php
require 'config/database.php';
$db = (new Database())->conectar();

echo "=== TABLA CLIENTE ===" . PHP_EOL;
try {
    $cols = $db->query("SHOW COLUMNS FROM cliente")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . ' | ' . $c['Type'] . ' | Null:' . $c['Null'] . ' | Key:' . $c['Key'] . PHP_EOL;
    }

    echo PHP_EOL . "=== DATOS CLIENTE (primeros 5) ===" . PHP_EOL;
    $clientes = $db->query("SELECT * FROM cliente LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($clientes as $c) {
        print_r($c);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
