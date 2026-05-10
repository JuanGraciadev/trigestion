<?php
require_once __DIR__ . '/../../config/database.php';
$db = new Database();
$conn = $db->conectar();
try {
    $conn->exec("ALTER TABLE categoria ADD COLUMN estado TINYINT(1) DEFAULT 1;");
    echo "Column 'estado' added successfully to 'categoria' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'estado' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
