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

    // Método para insertar equipo
    public function insertEquipo($data) {
        $sql = "INSERT INTO equipos (estacionamiento, tipo_de_equipo, nombre_equipo, ubicacion) VALUES (:estacionamiento, :tipo_de_equipo, :nombre_equipo, :ubicacion)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estacionamiento', $data['estacionamiento']);
        $stmt->bindParam(':tipo_de_equipo', $data['tipo_de_equipo']);
        $stmt->bindParam(':nombre_equipo', $data['nombre_equipo']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        return $stmt->execute(); // Retornar true si la ejecución fue exitosa
    }

    // Método para obtener la lista de estacionamientos
    public function getEstacionamientos() {
        $sql = "SELECT id, nombre FROM estacionamiento";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener la lista de tipos de equipo
    public function getTiposDeEquipo() {
        $sql = "SELECT id, nombre FROM tipo_equipo";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inicializa la variable para el mensaje de éxito, la lista de estacionamientos y tipos de equipo
$mensaje = '';
$estacionamientos = [];
$tiposDeEquipo = [];

// Crear una instancia de la base de datos y obtener las listas
$db = new Database();
$estacionamientos = $db->getEstacionamientos();
$tiposDeEquipo = $db->getTiposDeEquipo();

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'estacionamiento' => $_POST['estacionamiento'], // ID del estacionamiento seleccionado
        'tipo_de_equipo' => $_POST['tipo_de_equipo'], // ID del tipo de equipo seleccionado
        'nombre_equipo' => $_POST['nombre_equipo'], // Nombre del equipo
        'ubicacion' => $_POST['ubicacion'] // Ubicación del equipo
    ];

    if ($db->insertEquipo($data)) {
        $mensaje = "Equipo registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el equipo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Equipo</title>
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

        form input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        form select {
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
    <h2>Alta de Equipo</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="estacionamiento">Estacionamiento</label>
        <select name="estacionamiento" id="estacionamiento" required>
            <option value="">Seleccione un estacionamiento</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo $estacionamiento['id']; ?>">
                    <?php echo $estacionamiento['nombre']; ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="tipo_de_equipo">Tipo de Equipo</label>
        <select name="tipo_de_equipo" id="tipo_de_equipo" required>
            <option value="">Seleccione un tipo de equipo</option>
            <?php foreach ($tiposDeEquipo as $tipo): ?>
                <option value="<?php echo $tipo['id']; ?>">
                    <?php echo $tipo['nombre']; ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="nombre_equipo">Nombre del Equipo</label>
        <input type="text" name="nombre_equipo" id="nombre_equipo" required><br>

        <label for="ubicacion">Ubicación</label>
        <input type="text" name="ubicacion" id="ubicacion" required><br>

        <button type="submit">Registrar Equipo</button>
    </form>
</body>
</html>
