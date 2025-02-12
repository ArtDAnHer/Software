<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa la variable para el mensaje de éxito
$mensaje = '';
$exito = false; // Variable para verificar el éxito de la inserción

// Clase para la conexión a la base de datos
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
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function insertIncidencia($data) {
        $sql = "INSERT INTO incidencias (fecha_reporte, quien_reporta, tipo, tipo_falla, lugar, descripcion, operando, imagen, reincidencia, incidencia_relacionada, estado, tecnico) 
                VALUES (:fecha_reporte, :quien_reporta, :tipo, :tipo_falla, :lugar, :descripcion, :operando, :imagen, :reincidencia, :incidencia_relacionada, :estado, :tecnico)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':fecha_reporte', $data['fecha_reporte']);
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':tipo_falla', $data['tipo_falla']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando'], PDO::PARAM_BOOL);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia'], PDO::PARAM_BOOL);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':tecnico', $data['tecnico']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Funciones para obtener datos para los selects
    public function getTipos() {
        $sql = "SELECT id, nombre FROM tipo_equipo"; // Cambiar según tu estructura de datos
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstacionamientos() {
        $sql = "SELECT id, nombre FROM estacionamiento"; // Cambiar según tu estructura de datos
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipoFallas() {
        $sql = "SELECT id, nombre FROM tipo_falla"; // Cambiar según tu estructura de datos
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstados() {
        $sql = "SELECT id, estado FROM estado"; // Cambiar según tu estructura de datos
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['username'])) {
        // Recoger los datos del formulario
        $data = [
            'fecha_reporte' => $_POST['fecha_reporte'], // Añade la fecha de reporte
            'quien_reporta' => $_SESSION['username'],
            'tipo' => $_POST['tipo'] ?? '',
            'tipo_falla' => $_POST['tipo_falla'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'operando' => isset($_POST['operando']) && $_POST['operando'] === 'si' ? 1 : 0,
            'imagen' => '', // Inicializa como vacío
            'reincidencia' => ($_POST['reincidencia'] ?? '') === 'si' ? 1 : 0,
            'incidencia_relacionada' => $_POST['incidencia_relacionada'] ?? '',
            'estado' => $_POST['estado'] ?? '',
        ];

        // Verifica si hay una imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = basename($_FILES['imagen']['name']);
            $ruta_destino = 'uploads/' . $nombre_archivo;

            // Mueve la imagen al directorio de destino
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $data['imagen'] = $ruta_destino;
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        }

        // Inserta la incidencia en la base de datos
        $db = new Database();
        try {
            if ($db->insertIncidencia($data)) {
                $mensaje = "Incidencia registrada exitosamente.";
                $exito = true; // Marcar el éxito
            } else {
                $mensaje = "Error al registrar la incidencia.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $mensaje = "Debe iniciar sesión para registrar una incidencia.";
    }
}

// Inicialización de base de datos
$db = new Database();
$tipos = $db->getTipos();
$estacionamientos = $db->getEstacionamientos();
$tipoFallas = $db->getTipoFallas();
$estados = $db->getEstados();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Incidencias</title>
    <style>
        /* Estilos generales */
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
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            max-width: 500px;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        select, input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        /* Ocultar campo inicialmente */
        #incidencia_relacionada_group {
            display: none;
        }
    </style>
</head>
<body>

<h2>Registro de Incidencias</h2>
<div class="mensaje"><?php echo $mensaje; ?></div>
<form method="POST" enctype="multipart/form-data">
    <label for="fecha_reporte">Fecha de Reporte:</label>
    <input type="date" name="fecha_reporte" required>

    <label for="tipo">Tipo de Incidencia:</label>
    <select name="tipo" required>
        <option value="">Seleccione un tipo</option>
        <?php foreach ($tipos as $tipo): ?>
            <option value="<?php echo $tipo['nombre']; ?>"><?php echo $tipo['nombre']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="tipo_falla">Tipo de Falla:</label>
    <select name="tipo_falla" required>
        <option value="">Seleccione un tipo de falla</option>
        <?php foreach ($tipoFallas as $tipoFalla): ?>
            <option value="<?php echo $tipoFalla['nombre']; ?>"><?php echo $tipoFalla['nombre']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="lugar">Lugar:</label>
    <select name="lugar" required>
        <option value="">Seleccione un lugar</option>
        <?php foreach ($estacionamientos as $lugar): ?>
            <option value="<?php echo $lugar['nombre']; ?>"><?php echo $lugar['nombre']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" rows="4" required></textarea>

    <label for="operando">Operando:</label>
    <input type="radio" name="operando" value="si" required> Sí
    <input type="radio" name="operando" value="no" required> No

    <label for="imagen">Imagen:</label>
    <input type="file" name="imagen">

    <label for="reincidencia">¿Es una reincidencia?</label>
    <input type="radio" name="reincidencia" value="si"> Sí
    <input type="radio" name="reincidencia" value="no" checked> No

    <div id="incidencia_relacionada_group">
        <label for="incidencia_relacionada">Incidencia Relacionada:</label>
        <input type="text" name="incidencia_relacionada">
    </div>

    <label for="estado">Estado:</label>
    <select name="estado" required>
        <option value="">Seleccione un estado</option>
        <?php foreach ($estados as $estado): ?>
            <option value="<?php echo $estado['estado']; ?>"><?php echo $estado['estado']; ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Registrar Incidencia">
</form>

<script>
    function toggleIncidenciaRelacionada() {
        const reincidencia = document.getElementById('reincidencia').value;
        const incidenciaRelacionadaGroup = document.getElementById('incidencia_relacionada_group');
        incidenciaRelacionadaGroup.style.display = (reincidencia === 'si') ? 'block' : 'none';
    }

    <?php if ($exito): ?>
        // Abre el pop-up para seleccionar el técnico solo si la incidencia se registró correctamente
        window.open("seleccionar_tecnico.php", "popup", "width=600,height=600,scrollbars=yes,resizable=yes");
    <?php endif; ?>
</script>

</body>
</html>
