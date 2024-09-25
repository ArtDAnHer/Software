<?php
class Database {
    private $db = "Boletaje";
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

    public function insertEntry($data) {
        $fechaAlta = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO usuarios_empresa (nombre, email, telefono, direccion, fecha_registro, rol, estado, fecha_nacimiento, departamento) 
        VALUES (:nombre, :email, :telefono, :direccion, :fecha_registro, :rol, :estado, :fecha_nacimiento, :departamento)";

        $stmt = $this->conn->prepare($sql);

        // Enlazar los parámetros con los valores del array $data
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':fecha_registro', $fechaAlta); // Usar fecha actual para 'fecha_registro'
        $stmt->bindParam(':rol', $data['rol']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
        $stmt->bindParam(':departamento', $data['departamento']);
        
        if ($stmt->execute()) {
            echo "Usuario registrado exitosamente.";
        } else {
            echo "Error al registrar el usuario.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nombre' => $_POST['nombre'],
        'email' => $_POST['email'],
        'telefono' => $_POST['telefono'],
        'direccion' => $_POST['direccion'],
        'rol' => $_POST['rol'],
        'estado' => $_POST['estado'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'departamento' => $_POST['departamento']
    ];

    $db = new Database();
    $db->insertEntry($data);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <script>
        function confirmSubmission(event) {
            var confirmAction = confirm("¿Está seguro de que desea enviar estos datos?");
            if (!confirmAction) {
                event.preventDefault();
            }
        }
    </script>
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

        form input[type="text"],
        form input[type="number"],
        form input[type="email"],
        form input[type="date"], /* Añadir esta línea */
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

    </style>
</head>
<body>
    <h2>Registrar Nuevo Usuario</h2>
    <br>
    <hr>
    <br>
    <form id="userForm" method="POST" action="" onsubmit="confirmSubmission(event)">
        Nombre: <input type="text" name="nombre" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Teléfono: <input type="text" name="telefono" required><br><br>
        Dirección: <input type="text" name="direccion" required><br><br>
        Rol: <input type="text" name="rol" required><br><br>
        Estado: <input type="text" name="estado" required><br><br>
        Fecha de Nacimiento: <input type="date" name="fecha_nacimiento" required><br><br>
        Departamento: <input type="text" name="departamento" required><br><br>
        <button type="submit">Registrar Usuario</button>
    </form>
</body>
</html>
