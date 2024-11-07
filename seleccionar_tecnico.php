<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getLastIncidencia() {
        $sql = "SELECT * FROM incidencias ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTecnicosByPlaza($lugar) {
        $sql = "SELECT * FROM reportes_fallas.Tec WHERE plaza = :plaza";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':plaza', $lugar);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nuevo método para obtener áreas
    public function getAreas() {
        $sql = "SELECT area FROM reportes_fallas.area";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Nuevo método para obtener equipos
    public function getEquiposByLugarYTipo($lugar, $tipo) {
        $sql = "SELECT * FROM reportes_fallas.equipos WHERE lugar = :lugar AND tipo = :tipo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lugar', $lugar);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarTecnico($tecnico, $area, $incidencia_id, $equipo) {
        $sql = "UPDATE incidencias SET tecnico = :tecnico, area = :area, equipo = :equipo WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tecnico', $tecnico);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':equipo', $equipo); // Bind del equipo
        $stmt->bindParam(':id', $incidencia_id);
        return $stmt->execute();
    }
}

// Inicializar la base de datos
$db = new Database();

// Obtener la última incidencia registrada
$ultimaIncidencia = $db->getLastIncidencia();

// Obtener técnicos por plaza de la última incidencia
$tecnicos = [];
if ($ultimaIncidencia) {
    $tecnicos = $db->getTecnicosByPlaza($ultimaIncidencia['lugar']);
    
    // Obtener equipos según lugar y tipo
    $equipos = $db->getEquiposByLugarYTipo($ultimaIncidencia['lugar'], $ultimaIncidencia['tipo']);
}

// Obtener áreas
$areas = $db->getAreas(); // Llamada para obtener áreas

// Procesar el formulario de asignación de técnico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['incidencia_id'])) {
    $incidencia_id = $_POST['incidencia_id'];
    $tecnico = $_POST['tecnico'];
    $area = $_POST['area'];
    $equipoSeleccionado = $_POST['equipo']; // Obtener el equipo seleccionado

    if ($db->asignarTecnico($tecnico, $area, $incidencia_id, $equipoSeleccionado)) {
        echo "Técnico, área y equipo asignados exitosamente.";
    } else {
        echo "Error al asignar el técnico, el área y el equipo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Última Incidencia Registrada</title>
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

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .field-value, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h2>Última Incidencia Registrada</h2>

    <div class="container">
        <?php if ($ultimaIncidencia): ?>
            <label>ID:</label>
            <div class="field-value"><?php echo htmlspecialchars($ultimaIncidencia['id']); ?></div>

            <label>Fecha de Reporte:</label>
            <div class="field-value"><?php echo htmlspecialchars($ultimaIncidencia['fecha_reporte']); ?></div>

            <label>Quien Reporta:</label>
            <div class="field-value"><?php echo htmlspecialchars($ultimaIncidencia['quien_reporta']); ?></div>

            <label>Estacionamiento:</label>
            <div class="field-value"><?php echo htmlspecialchars($ultimaIncidencia['lugar']); ?></div>

            <form method="POST" action="">
                <input type="hidden" name="incidencia_id" value="<?php echo htmlspecialchars($ultimaIncidencia['id']); ?>">

                <label for="equipo">Equipo:</label>
                <select id="equipo" name="equipo" required>
                    <option value="">Seleccione Equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                        <option value="<?php echo htmlspecialchars($equipo['equipo']); ?>"><?php echo htmlspecialchars($equipo['equipo']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tecnico">Técnico:</label>
                <select id="tecnico" name="tecnico" required>
                    <option value="">Seleccione Técnico</option>
                    <option value="otros">Otros</option>
                    <?php foreach ($tecnicos as $tec): ?>
                        <option value="<?php echo htmlspecialchars($tec['tecnico']); ?>"><?php echo htmlspecialchars($tec['tecnico']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="area">Área:</label>
                <select id="area" name="area" required>
                    <option value="">Seleccione Área</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="button-container">
                    <button type="submit">Asignar Técnico, Área y Equipo</button>
                </div>
            </form>
        <?php else: ?>
            <p>No se encontró ninguna incidencia registrada.</p>
        <?php endif; ?>
    </div>

</body>
</html>
