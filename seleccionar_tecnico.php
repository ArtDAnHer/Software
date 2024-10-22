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

    public function getIncidenciaById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM incidencias WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function asignarTecnico($tecnico, $area, $incidencia_id) {
        $stmt = $this->conn->prepare("UPDATE incidencias SET tecnico = :tecnico, area = :area WHERE id = :id");
        $stmt->bindParam(':tecnico', $tecnico);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':id', $incidencia_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTecnicos() {
        $sql = "SELECT id, tecnico FROM tecnico";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAreas() {
        $sql = "SELECT id, area FROM area";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <form method="POST" action="">
        <input type="hidden" name="incidencia_id" value="<?php echo htmlspecialchars($incidencia['id']); ?>">

        <label for="area">Área:</label>
        <select name="area" id="area" required>
            <option value="">Selecciona un área</option>
            <?php foreach ($areas as $ar): ?>
                <option value="<?php echo htmlspecialchars($ar['area']); ?>" <?php echo $incidencia['area'] === $ar['area'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ar['area']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="tecnico">Técnico:</label>
        <select name="tecnico" id="tecnico" required>
            <option value="">Selecciona un técnico</option>
            <?php foreach ($tecnicos as $tec): ?>
                <option value="<?php echo htmlspecialchars($tec['tecnico']); ?>" <?php echo $incidencia['tecnico'] === $tec['tecnico'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tec['tecnico']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Asignar Técnico y Área</button>
    </form>
</body>
</html>
