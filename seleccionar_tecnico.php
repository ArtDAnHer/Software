<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $db = "insidencias"; // Nombre de la base de datos
    private $ip = "192.168.1.17"; // Cambia esto según tu configuración
    private $port = "3306"; // Cambia esto si es necesario
    private $username = "celular"; // Cambia esto según tu configuración
    private $password = "Coemsa.2024"; // Cambia esto según tu configuración
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->ip};port={$this->port};dbname={$this->db}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

}

// Inicializa la base de datos
$db = new Database();

// Verificar si se ha enviado el formulario para asignar el técnico
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $incidencia_id = $_POST['incidencia_id'];
    $tecnico = $_POST['tecnico'];
    $area = $_POST['area'];

    // Asignar el técnico y el área a la incidencia
    if ($db->asignarTecnico($tecnico, $area, $incidencia_id)) {
        echo "Técnico y área asignados exitosamente.";
    } else {
        echo "Error al asignar el técnico y el área.";
    }
}

// Obtener la incidencia por ID (pasada por la URL o formulario)
if (isset($_GET['id'])) {
    $incidencia_id = $_GET['id'];
    $incidencia = $db->getIncidenciaById($incidencia_id);
} else {
    die("ID de incidencia no especificado.");
}

// Obtener técnicos y áreas
$tecnicos = $db->getTecnicos();
$areas = $db->getAreas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Técnico y Área</title>
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
    <h2>Asignar Técnico y Área a la Incidencia</h2>
    <label for="equipo">Equipo</label>
        <select id="equipos" name="equipo" required>
            <option value="">Seleccione Lugar</option>
            <?php foreach ($equipos as $equipos): ?>
                <option value="<?php echo $equipos['equipo']; ?>"><?php echo $equipos['equipo']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="ubicacion">Ubicacion</label>
        <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $ubisel ?>" readonly>


        <label for="tecnico">Tecnico</label>
        <input type="text" id="quien_reporta" name="quien_reporta" value="<?php echo $tecsel; ?>" readonly>    

        <input type="submit" value="Registrar Incidencia">
</body>
</html>
