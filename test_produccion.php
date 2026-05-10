<?php
require_once __DIR__ . '/config/database.php';
$db = (new Database())->conectar();
$stmt = $db->query('DESCRIBE produccion');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in produccion table:\n";
foreach ($columns as $c) {
    echo $c['Field'] . "\n";
}
?>
