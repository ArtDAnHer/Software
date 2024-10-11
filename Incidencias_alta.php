<?php


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
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function insertIncidencia($data) {
        $sql = "INSERT INTO incidencias (fecha_reporte, quien_reporta, tipo, lugar, equipo, ubicacion, descripcion, operando, imagen, reincidencia, incidencia_relacionada, estado) 
                VALUES (CURDATE(), :quien_reporta, :tipo, :lugar, :equipo, :ubicacion, :descripcion, :operando, :imagen, :reincidencia, :incidencia_relacionada, :estado)";
        $stmt = $this->conn->prepare($sql);

        // Enlazar los parámetros
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia']);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':estado', $data['estado']); // Agregado para el nuevo campo

        return $stmt->execute();
    }

    public function getIncidencias() {
        $sql = "SELECT * FROM incidencias";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipos() {
        $sql = "SELECT id, nombre FROM tipo_equipo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstacionamientos() {
        $sql = "SELECT id, nombre FROM estacionamiento";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inicializa la variable para el mensaje de éxito
$mensaje = '';

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['usuario'])) {
        $data = [
            'quien_reporta' => $_SESSION['usuario'],
            'tipo' => $_POST['tipo'],
            'lugar' => $_POST['lugar'],
            'equipo' => $_POST['equipo'],
            'ubicacion' => $_POST['ubicacion'],
            'descripcion' => $_POST['descripcion'],
            'operando' => isset($_POST['operando']) ? 1 : 0,
            'imagen' => $_FILES['imagen']['name'],
            'reincidencia' => isset($_POST['reincidencia']) ? 1 : 0,
            'incidencia_relacionada' => $_POST['incidencia_relacionada'],
            'estado' => 'pendiente', // Estado por defecto, cámbialo según sea necesario
        ];

        if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ruta_destino = 'uploads/' . basename($_FILES['imagen']['name']);
            move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
        }

        $db = new Database();
        if ($db->insertIncidencia($data)) {
            $mensaje = "Incidencia registrada exitosamente.";
        } else {
            $mensaje = "Error al registrar la incidencia.";
        }
    } else {
        $mensaje = "Debe iniciar sesión para registrar una incidencia.";
    }
}

$db = new Database();
$incidencias = $db->getIncidencias();
$tipos = $db->getTipos();
$estacionamientos = $db->getEstacionamientos(); // Obtener los estacionamientos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Incidencias</title>
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
        form input[type="number"],
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Alta de Incidencias</h2>
    <hr>

    <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label for="tipo">Tipo</label>
        <select name="tipo" id="tipo" required>
            <option value="">Selecciona un tipo</option>
            <option value="hardware">Hardware</option>
            <option value="software">Software</option>
        </select>

        <label for="lugar">Lugar</label>
        <select name="lugar" id="lugar" required>
            <option value="">Selecciona un lugar</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo htmlspecialchars($estacionamiento['id']); ?>">
                    <?php echo htmlspecialchars($estacionamiento['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="equipo">Equipo</label>
        <select name="equipo" id="equipo" required>
            <option value="">Selecciona un equipo</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="ubicacion">Ubicación</label>
        <input type="text" name="ubicacion" id="ubicacion" required>

        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="4" required></textarea>

        <label for="operando">¿Está operando?</label>
        <input type="checkbox" name="operando" id="operando" value="1">

        <label for="imagen">Imagen</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">

        <label for="reincidencia">¿Es reincidencia?</label>
        <input type="checkbox" name="reincidencia" id="reincidencia" value="1">

        <label for="incidencia_relacionada">Incidencia relacionada</label>
        <input type="text" name="incidencia_relacionada" id="incidencia_relacionada">

        <button type="submit">Registrar Incidencia</button>
    </form>

    <h2>Incidencias Registradas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Quien Reporta</th>
                <th>Tipo</th>
                <th>Lugar</th>
                <th>Equipo</th>
                <th>Ubicación</th>
                <th>Descripción</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidencias as $incidencia): ?>
                <tr>
                    <td><?php echo htmlspecialchars($incidencia['id']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['fecha_reporte']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['quien_reporta']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['lugar']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['equipo']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['ubicacion']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($incidencia['estado']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
