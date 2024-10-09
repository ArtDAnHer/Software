<?php
class Database {
    private $db = "insidencias"; // Nombre correcto de la base de datos
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

    public function insertTipoEquipo($data) {
        $sql = "INSERT INTO tipo_equipo (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($sql);

        // Enlazar los parámetros con los valores del array $data
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);

        return $stmt->execute(); // Retornar true si la ejecución fue exitosa
    }
}

// Inicializa la variable para el mensaje de éxito
$mensaje = '';

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nombre' => $_POST['nombre'], // Nombre del equipo
        'descripcion' => $_POST['descripcion'] // Descripción del equipo
    ];

    $db = new Database();
    if ($db->insertTipoEquipo($data)) {
        $mensaje = "Tipo de equipo registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el tipo de equipo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Tipo de Equipo</title>
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

        .mensaje {
            text-align: center;
            color: green; /* Color verde para el mensaje de éxito */
            margin: 10px 0;
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
        form textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <h2>Alta de Tipo de Equipo</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre del Tipo de Equipo</label>
        <input type="text" name="nombre" id="nombre" required><br>

        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="4" required></textarea><br>

        <button type="submit">Registrar Tipo de Equipo</button>
    </form>
</body>
</html>
