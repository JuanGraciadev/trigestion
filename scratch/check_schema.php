<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->conectar();
$tables = ['cliente', 'usuarios'];
foreach($tables as $table) {
    echo "Table: $table\n";
    $cols = $conn->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) {
        echo " - " . $col['Field'] . " | " . $col['Type'] . " | Null: " . $col['Null'] . " | Default: " . $col['Default'] . "\n";
    }
    echo "\n";
}
?>
