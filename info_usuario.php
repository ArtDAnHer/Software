<?php
class Database {
    private $db = "insidencias";
    private $ip = "192.168.1.17";
    private $port = "3306";
    private $username = "celular";
    private $password = "Coemsa.2024";
    private $conn;


    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->ip};port={$this->port};dbname={$this->db}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    // Método para obtener los datos de la tabla usuarios_empresa
    public function getFirstUser() {
        $sql = "SELECT nombre, email, telefono, direccion, fecha_registro, rol, estado, fecha_nacimiento, departamento FROM usuarios_empresa LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve solo una fila
    }
}

// Crear una instancia de la clase Database
$db = new Database();

// Obtener el primer usuario
$usuario = $db->getFirstUser();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info usuario</title>
</head>
<body align="center" class="row">
    <H1>Info usuario</h1><br>
    <br>
    <div align="center" style="font-size: 25px;" class="col-xs-3 col-md-2"></div>
    <div align="center" style="font-size: 25px;" class="col-xs-3 col-md-2">
        <div align="left">
            User: <?php echo $usuario['email']; ?><br>
            Nombre: <?php echo $usuario['nombre']; ?><br>
            Telefono: <?php echo $usuario['telefono']; ?><br>
            Direccion: <?php echo $usuario['direccion']; ?><br>
            Registro: <?php echo $usuario['fecha_registro']; ?><br>
            Rol: <?php echo $usuario['rol']; ?><br>
            Estado: <?php echo $usuario['estado']; ?><br>
            Nacimiento: <?php echo $usuario['fecha_nacimiento']; ?><br>
            Departamento: <?php echo $usuario['departamento']; ?><br>
        </div>
    </div>

    <div class="container">
    <div class="row">
        <div class="col-md-6">
            <p>Columna 1 (mitad de la fila)</p>
        </div>
        <div class="col-md-6">
            <p>Columna 2 (mitad de la fila)</p>
        </div>
    </div>
</div>

</body>
</html>