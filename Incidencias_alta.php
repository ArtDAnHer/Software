<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa la variable para el mensaje de éxito
$mensaje = ''; // Inicializa la variable para el mensaje

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

    // Función para obtener los estados
    public function getEstados() {
        $sql = "SELECT id, estado FROM estado"; // Asegúrate de que la columna 'estado' exista en la tabla
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para insertar una nueva incidencia
    public function insertIncidencia($data) {
        $sql = "INSERT INTO incidencias (fecha_reporte, quien_reporta, tipo, tipo_falla, lugar, equipo, descripcion, operando, imagen, reincidencia, incidencia_relacionada, estado) 
                VALUES (CURDATE(), :quien_reporta, :tipo, :tipo_falla, :lugar, :equipo,  :descripcion, :operando, :imagen, :reincidencia, :incidencia_relacionada, :estado)";
        $stmt = $this->conn->prepare($sql);

        // Enlazar los parámetros
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':tipo_falla', $data['tipo_falla']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia']);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':estado', $data['estado']);

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

    public function getTecnicoPorLugar() {
        $sql = "SELECT * FROM tec ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquiposPorLugar() {
        $sql = "SELECT * FROM equipos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inicialización de variables para el formulario
$tipo = '';
$lugar = '';
$equipos = []; // Inicializamos el array de equipos
$equisel = [];
$ubisel = "";
$tecnico = []; 
$tecsel = "";


if (isset($_POST['operando']) && $_POST['operando'] === 'si') {
    $valor = 1; // Cambiado a 1 para indicar que está operando
} else {
    $valor = 0; // Cambiado a 0 para indicar que no está operando
}

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['username'])) {
        // Verificamos si se enviaron los datos necesarios
        $data = [
            'quien_reporta' => $_SESSION['username'],
            'tipo' => $_POST['tipo'] ?? '',
            'tipo_falla' => $_POST['tipo_falla'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'equipo' => $_POST['equipo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'operando' => $valor,
            'imagen' => '', // Inicia como vacío
            'reincidencia' => ($_POST['reincidencia'] ?? '') === 'si' ? 1 : 0,
            'incidencia_relacionada' => $_POST['incidencia_relacionada'] ?? '',
            'estado' => $_POST['estado'] ?? '' // Captura el estado
        ];

        // Verifica si hay una imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = basename($_FILES['imagen']['name']); // Obtener solo el nombre del archivo
            $ruta_destino = 'uploads/' . $nombre_archivo; // Concatenar para formar la ruta completa

            // Mueve la imagen al directorio de destino
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $data['imagen'] = $ruta_destino; // Almacena la ruta completa de la imagen
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        } else {
            $data['imagen'] = ''; // Si no hay imagen, establece un valor vacío
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
$equipos = $db->getEquiposPorLugar();
$tecnico = $db->getTecnicoPorLugar();
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

    <form method="post" enctype="multipart/form-data">
        <label for="quien_reporta">Quien Reporta</label>
        <input type="text" id="quien_reporta" name="quien_reporta" value="<?php echo $_SESSION['username']; ?>" readonly>


        <label for="tipo">Tipo de Incidencia</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione Tipo</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo $tipo['id']; ?>"><?php echo $tipo['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tipo_falla">Tipo de Falla</label>
        <select id="tipo_falla" name="tipo_falla" required>
            <option value="">Seleccione Tipo de Falla</option>
            <?php foreach ($tipoFallas as $falla): ?>
                <option value="<?php echo $falla['id']; ?>"><?php echo $falla['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lugar">Lugar</label>
        <select id="lugar" name="lugar" required>
            <option value="">Seleccione Lugar</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo $estacionamiento['id']; ?>"><?php echo $estacionamiento['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="equipo">Equipo</label>
        <select id="equipos" name="equipo" required>
            <option value="">Seleccione Lugar</option>
            <?php foreach ($equipos as $equipos): ?>
                <option value="<?php echo $equipos['equipo']; ?>"><?php echo $equipos['equipo']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="ubicacion">Ubicacion</label>
        <input type="text" id="ubicacion" name="ubicacion" value="<?php  ?>" readonly>


        <label for="tecnico">Tecnico</label>
        <input type="text" id="quien_reporta" name="quien_reporta" value="<?php echo $tecsel; ?>" readonly>

        <label for="descripcion">Descripción</label>
        <input type="text" id="descripcion" name="descripcion" required>

        <label for="operando">¿Está operando?</label>
        <select name="operando" id="operando" required>
            <option value="si">Sí</option>
            <option value="no">No</option>
        </select>

        <label for="imagen">Subir Imagen (opcional)</label>
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
        <select name="estado" id="estado" required>
            <option value="">Seleccione Estado</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado['id']; ?>"><?php echo $estado['estado']; ?></option>
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
