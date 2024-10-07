<?php
class Database {
    private $db = "boletaje";
    private $ip = "72.167.59.89";
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
