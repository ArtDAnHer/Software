<?php
class Database {
    private $db = "boletaje";
    private $ip = "192.168.1.98";
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Usuario</title>
    <link rel="stylesheet" href="style.css"> <!-- Vincula tu archivo CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
        }

        form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }

        form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        form input[type="text"],
        form input[type="number"],
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        form input[type="number"] {
            text-align: right;
        }

        form button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #218838;
        }

        form hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }

        form .required {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h2><?php echo $usuario['nombre']; ?></h2>
    <hr>
        <div class="container">
                <div class="row" align="center" style="font-size: 25px">
                        <div class="col-xs-12 col-sm-4"></div>
                        <div class="col-xs-12 col-sm-4" >
                                <div align="center" class="info">
                                        <th>Email: </th>
                                        <td><?php echo $usuario['email']; ?></td>
                                <br><br>
                                        <th>Teléfono: </th>
                                        <td><?php echo $usuario['telefono']; ?></td>
                                <br><br>
                                        <th>Dirección: </th>
                                        <td><?php echo $usuario['direccion']; ?></td>
                                <br><br>
                                        <th>Fecha de Registro: </th>
                                        <td><?php echo $usuario['fecha_registro']; ?></td>
                                <br><br>
                                        <th>Rol: </th>
                                        <td><?php echo $usuario['rol']; ?></td>
                                <br><br>
                                        <th>Estado: </th>
                                        <td><?php echo $usuario['estado']; ?></td>
                                <br><br>
                                        <th>Fecha de Nacimiento: </th>
                                        <td><?php echo $usuario['fecha_nacimiento']; ?></td>
                                <br><br>
                                        <th>Departamento: </th>
                                        <td><?php echo $usuario['departamento']; ?></td>
                                        </div>
                                </div>
                        <div class="col-xs-12 col-sm-4"></div>
                </div>
        </div>

</body>
</html>
