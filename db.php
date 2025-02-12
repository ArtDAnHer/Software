<?php
class Database {
<<<<<<< HEAD
    private $db = "boletaje";
    private $ip = "192.168.1.98";
=======
    private $db = "reportes_fallas";
    private $ip = "localhost";
>>>>>>> a5e7407534ffde80b3f3d9375184a9bfd5db5ebb
    private $port = "3306";
    private $username = "celular";
    private $password = "Coemsa.2024";
    private $conn;

    public function connect() {
        try {
            $dsn = "mysql:host={$this->ip};port={$this->port};dbname={$this->db}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
            
            
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }
}
?>
