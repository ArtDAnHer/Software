<?php
// Conexión a la base de datos
class Database {
    private $db = "reportes_fallas";
    private $ip = "localhost";
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

    public function getTecnicos() {
        $sql = "SELECT * FROM tecnico";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIncidenciaConTecnico($incidencia_id, $tecnico_id) {
        $sql = "UPDATE incidencias SET tecnico = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$tecnico_id, $incidencia_id]);
    }
}

$db = new Database();

// Verifica que el ID de la incidencia esté presente en la URL
if (!isset($_GET['id'])) {
    echo "No se ha proporcionado el ID de la incidencia.";
    exit();
}

// Obtiene el ID de la incidencia desde la URL
$incidencia_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tecnico_id = $_POST['tecnico_id'];

    if ($db->updateIncidenciaConTecnico($incidencia_id, $tecnico_id)) {
        echo "Técnico asignado correctamente.";
    } else {
        echo "Error al asignar el técnico.";
    }
}

// Obtiene la lista de técnicos
$tecnicos = $db->getTecnicos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Técnico</title>
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
            width: 350px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Asignacion manual de tecnico</h2>

    <form method="POST" action="">
        <input type="hidden" name="incidencia_id" value="<?php echo $incidencia_id; ?>">

        <label for="tecnico_id">Selecciona el Técnico:</label>
        <select name="tecnico_id" id="tecnico_id" required>
            <option value="">Selecciona un técnico</option>
            <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?php echo $tecnico['tecnico']; ?>"><?php echo $tecnico['tecnico']; ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Asignar Técnico">
    </form>
</body>
</html>
