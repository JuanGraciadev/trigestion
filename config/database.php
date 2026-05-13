<?php
class Database
{
    private $host = "sql301.byethost7.com"; // Permiso Host 
    private $port = "3306"; // ← ESTE ES EL PUERTO DE TU MYSQL
    private $db_name = "b7_41905149_dbmoova"; // Permiso Base de Datos
    private $username = "b7_41905149"; // Permiso Usuario
    private $password = "1079172033"; // Permiso Contraseña
    
    public $conn; // Variable de Conexión

    public function conectar()
    {

        $this->conn = null;

        try {

            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        catch (PDOException $e) {

            die("Error de conexión: " . $e->getMessage());

        }

        return $this->conn;
    }
}
?>