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

    public function insertIncidencia($data) {
        $sql = "INSERT INTO incidencias (fecha_reporte, quien_reporta, tipo, tipo_falla, lugar, equipo, ubicacion, descripcion, operando, imagen, reincidencia, incidencia_relacionada, estado, area, tecnico) 
                VALUES (:fecha_reporte, :quien_reporta, :tipo, :tipo_falla, :lugar, :equipo, :ubicacion, :descripcion, :operando, :imagen, :reincidencia, :incidencia_relacionada, :estado, :area, :tecnico)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':fecha_reporte', $data['fecha_reporte']);
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':tipo_falla', $data['tipo_falla']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia']);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':tecnico', $data['tecnico']);

        return $stmt->execute();
    }

    public function getIncidencias() {
        $sql = "SELECT * FROM incidencias";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipos() {
        $sql = "SELECT * FROM tipo_equipo";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTiposFalla() {
        $sql = "SELECT * FROM tipo_falla";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstacionamientos() {
        $sql = "SELECT * FROM estacionamiento";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstado() {
        $sql = "SELECT * FROM estado";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArea() {
        $sql = "SELECT * FROM area";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTec() {
        $sql = "SELECT * FROM tec";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnico() {
        $sql = "SELECT * FROM tecnico";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipos() {
        $sql = "SELECT * FROM equipos";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Manejar la subida de la imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $uploadDir = 'uploads/';  // Define la carpeta donde guardarás las imágenes
        $uploadFile = $uploadDir . basename($_FILES['imagen']['name']);

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadFile)) {
            $imagenPath = $uploadFile;  // Ruta de la imagen a guardar en la base de datos
        } else {
            $imagenPath = null;  // No se subió la imagen correctamente
        }
    } else {
        $imagenPath = null;  // No se seleccionó ninguna imagen
    }

    $data = [
        'fecha_reporte' => date('Y-m-d'),
        'quien_reporta' => $_SESSION['username'], // Assuming the username is stored in session
        'tipo' => isset($_POST['tipo']) ? $_POST['tipo'] : '',
        'tipo_falla' => isset($_POST['tipo_falla']) ? $_POST['tipo_falla'] : null,
        'lugar' => isset($_POST['lugar']) ? $_POST['lugar'] : null,
        'equipo' => isset($_POST['equipo']) ? $_POST['equipo'] : '',
        'ubicacion' => isset($_POST['ubicacion']) ? $_POST['ubicacion'] : '',
        'descripcion' => isset($_POST['descripcion']) ? $_POST['descripcion'] : '',
        'operando' => isset($_POST['operando']) ? 1 : 0,
        'imagen' => $imagenPath,  // Guardar la ruta de la imagen en la base de datos
        'reincidencia' => isset($_POST['reincidencia']) ? 1 : 0,
        'incidencia_relacionada' => isset($_POST['incidencia_relacionada']) ? $_POST['incidencia_relacionada'] : null,
        'estado' => isset($_POST['estado']) ? $_POST['estado'] : '',
        'area' => isset($_POST['area']) ? $_POST['area'] : '',
        'tecnico' => isset($_POST['tecnico']) ? $_POST['tecnico'] : '',
    ];

    $db = new Database();
    if ($db->insertIncidencia($data)) {
        $mensaje = "Incidencia registrada exitosamente.";
    } else {
        $mensaje = "Error al registrar la incidencia.";
    }
}

$db = new Database();
$incidencias = $db->getIncidencias(); // Get registered incidencias
$tipos = $db->getTipos(); // Fetch tipos for the dropdown
$tipos_falla = $db->getTiposFalla(); // Fetch tipos_falla for the dropdown
$estacionamiento = $db->getEstacionamientos();
$estado = $db->getEstado();
$area = $db->getArea();
$tec = $db->getTec();
$tec = $db->getTecnico();
$equipo = $db->getEquipos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Incidencias</title>
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
            color: green;
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

        form input[type="text"], form select, form input[type="file"] {
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

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <h2>Registro de Incidencias</h2>
    <div class="mensaje"><?= $mensaje ?></div>

    <form method="POST" action="" enctype="multipart/form-data">
        <label for="tipo">Tipo de Incidencia</label>
        <select name="tipo" id="tipo" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?= $tipo['nombre'] ?>"><?= $tipo['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tipo_falla">Tipo de Falla</label>
        <select name="tipo_falla" id="tipo_falla">
            <option value="">Seleccione</option>
            <?php foreach ($tipos_falla as $tipo_falla): ?>
                <option value="<?= $tipo_falla['id'] ?>"><?= $tipo_falla['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lugar">Lugar</label>
        <select name="lugar" id="lugar">
            <option value="">Seleccione</option>
            <?php foreach ($estacionamiento as $est): ?>
                <option value="<?= $est['id'] ?>"><?= $est['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="equipo">Equipo</label>
        <select name="equipo" id="equipo">
            <option value="">Seleccione</option>
            <?php foreach ($equipo as $equipo): ?>
                <option value="<?= $equipo['id'] ?>"><?= $equipo['equipo'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="ubicacion">Ubicación</label>
        <input type="text" name="ubicacion" id="ubicacion">

        <label for="estado">Estado</label>
        <select name="estado" id="estado">
            <option value="">Seleccione</option>
            <?php foreach ($estado as $estado): ?>
                <option value="<?= $estado['estado'] ?>"><?= $estado['estado'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="area">Área</label>
        <select name="area" id="area">
            <option value="">Seleccione</option>
            <?php foreach ($area as $area): ?>
                <option value="<?= $area['area'] ?>"><?= $area['area'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tecnico">Técnico</label>
        <select name="tecnico" id="tecnico">
            <option value="">Seleccione</option>
            <?php foreach ($tec as $tec): ?>
                <option value="<?= $tec['tecnico'] ?>"><?= $tec['tecnico'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Registrar Incidencia</button>
    </form>
</body>
</html>
