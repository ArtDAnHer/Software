<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa la variable para el mensaje de éxito
$mensaje = '';

// Clase para la conexión a la base de datos
class Database {
    private $db = "insidencias"; // Nombre de la base de datos
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

    // Función para insertar una nueva incidencia
    public function insertIncidencia($data) {
        $sql = "INSERT INTO incidencias (fecha_reporte, quien_reporta, tipo, tipo_falla, lugar, equipo, descripcion, operando, imagen, reincidencia, incidencia_relacionada, estado, tecnico) 
                VALUES (CURDATE(), :quien_reporta, :tipo, :tipo_falla, :lugar, :equipo, :descripcion, :operando, :imagen, :reincidencia, :incidencia_relacionada, :estado, :tecnico)";
        $stmt = $this->conn->prepare($sql);

        // Enlazar los parámetros
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':tipo_falla', $data['tipo_falla']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando'], PDO::PARAM_BOOL);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia'], PDO::PARAM_BOOL);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':tecnico', $data['tecnico']);

        return $stmt->execute();
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

    public function getTipoFallas() {
        $sql = "SELECT id, nombre FROM tipo_falla";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipos() {
        $sql = "SELECT * FROM equipos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnico() {
        $sql = "SELECT * FROM tecnico";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para obtener los estados
    public function getEstados() {
        $sql = "SELECT id, estado FROM estado"; // Asegúrate de que la columna 'estado' exista en la tabla
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquiposByLugar($lugar) {
        $sql = "SELECT * FROM equipos WHERE lugar = :lugar and tipo = :tipo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lugar', $lugar);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnicoByLugar() {
        $sql = "SELECT * FROM tecnico where lugar = :lugar";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lugar', $lugar);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

// Inicialización de variables para el formulario
$mensaje = '';

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['username'])) {
        // Recoger los datos del formulario
        $data = [
            'quien_reporta' => $_SESSION['username'],
            'tipo' => $_POST['tipo'] ?? '',
            'tipo_falla' => $_POST['tipo_falla'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'equipo' => $_POST['equipo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'operando' => ($_POST['operando'] === 'si') ? 1 : 0,
            'imagen' => '', // Inicializa como vacío
            'reincidencia' => ($_POST['reincidencia'] ?? '') === 'si' ? 1 : 0,
            'incidencia_relacionada' => $_POST['incidencia_relacionada'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'tecnico' => $_POST['tecnico'] ?? '' // Captura el técnico
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
$estacionamientos = $db->getEstacionamientos(); // Obtener los estacionamientos
$tipoFallas = $db->getTipoFallas(); // Obtener los tipos de falla
$estados = $db->getEstados(); // Obtener los estados
$equipo = $db->getEquipos();
$equipos = $db->getEquiposByLugar();
$tecnico = $db->getTecnico(); // Obtener los técnicos
$tecnicos = $db->getTecnicoByLugar(); // Obtener los técnicos

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
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form input[type="file"] {
            margin-bottom: 15px;
        }
        form input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Registro de Incidencias</h2>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione Tipo</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo $tipo['nombre']; ?>"><?php echo $tipo['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tipo_falla">Tipo de Falla</label>
        <select id="tipo_falla" name="tipo_falla" required>
            <option value="">Seleccione Tipo de Falla</option>
            <?php foreach ($tipoFallas as $tipoFalla): ?>
                <option value="<?php echo $tipoFalla['nombre']; ?>"><?php echo $tipoFalla['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lugar">Estacionamiento</label>
        <select id="lugar" name="lugar" required>
            <option value="">Seleccione Estacionamiento</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo $estacionamiento['nombre']; ?>"><?php echo $estacionamiento['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="equipo">Equipo</label>
        <select id="equipo" name="equipo" required>
            <option value="">Seleccione Equipo</option>
            <?php foreach ($equipos as $equipo): ?>
                <option value="<?php echo $equipo['equipo']; ?>"><?php echo $equipo['equipo']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="descripcion">Descripción</label>
        <input type="text" id="descripcion" name="descripcion" required>

        <label for="operando">¿Está operando?</label>
        <select id="operando" name="operando">
            <option value="si">Sí</option>
            <option value="no">No</option>
        </select>

        <label for="imagen">Imagen</label>
        <input type="file" id="imagen" name="imagen">

        <label for="reincidencia">¿Es reincidencia?</label>
        <select name="reincidencia" id="reincidencia" required>
            <option value="no">No</option>
            <option value="si">Sí</option>
        </select>

        <div id="incidencia_relacionada_container" style="display:none;">
            <label for="incidencia_relacionada">Incidencia Relacionada</label>
            <input type="text" id="incidencia_relacionada" name="incidencia_relacionada">
        </div>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="">Seleccione Estado</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado['estado']; ?>"><?php echo $estado['estado']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tecnico">Técnico</label>
        <select id="tecnico" name="tecnico" required>
            <option value="">Seleccione Técnico</option>
            <?php foreach ($tecnicos as $tec): ?>
                <option value="<?php echo $tec['tecnico']; ?>"><?php echo $tec['tecnico']; ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Registrar Incidencia">
    </form>

    <script>

document.getElementById('reincidencia').addEventListener('change', function() {
    var reincidenciaValue = this.value;
    var incidenciaRelacionadaContainer = document.getElementById('incidencia_relacionada_container');
    
    if (reincidenciaValue === 'si') {
        incidenciaRelacionadaContainer.style.display = 'block'; // Mostrar campo
    } 
    else {
        incidenciaRelacionadaContainer.style.display = 'none'; // Ocultar campo
    }
});

// Verificar el valor inicial al cargar la página
window.onload = function() {
    var reincidenciaValue = document.getElementById('reincidencia').value;
    var incidenciaRelacionadaContainer = document.getElementById('incidencia_relacionada_container');
    
    if (reincidenciaValue === 'si') {
        incidenciaRelacionadaContainer.style.display = 'block'; // Mostrar campo
    } else {
        incidenciaRelacionadaContainer.style.display = 'none'; // Ocultar campo
    }
};

</script>

</body>
</html>
