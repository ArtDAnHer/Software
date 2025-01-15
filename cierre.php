<?php
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

    public function getIncidenciaById($id) {
        $sql = "SELECT * FROM incidencias WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateFirma($id, $firma, $fotoCierre) {
        $sql = "UPDATE incidencias SET estado = 'cerrado', atendido = 1, firma = ?, foto_evidencia_atencion = ?, fecha_atencion = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$firma, $fotoCierre, $id]);
    }
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $firma = $_POST['firma'];

    // Verifica que el archivo haya sido subido correctamente
    if (isset($_FILES['foto_evidencia_atencion']) && $_FILES['foto_evidencia_atencion']['error'] === UPLOAD_ERR_OK) {
        // Define la carpeta de destino
        $uploadDir = 'uploads/'; // Carpeta donde guardarás las imágenes
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Crea la carpeta si no existe
        }
        
        // Define el nombre del archivo (asegúrate de evitar nombres duplicados)
        $uploadFile = $uploadDir . basename($_FILES['foto_evidencia_atencion']['name']);
        
        // Mueve el archivo a la carpeta de destino
        if (move_uploaded_file($_FILES['foto_evidencia_atencion']['tmp_name'], $uploadFile)) {
            // Llama a la función para actualizar los datos con la ruta del archivo
            if ($db->updateFirma($id, $firma, $uploadFile)) {
                echo "<script>alert('Actualización exitosa'); window.close();</script>";
            } else {
                echo "Error: No se pudo actualizar el registro.";
            }
        } else {
            echo "Error al guardar la imagen en el servidor.";
        }
    } else {
        echo "Error al cargar la imagen.";
    }
    exit();
}

$id = $_GET['id'];
$incidencia = $db->getIncidenciaById($id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Firma de Incidencia</title>
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

        input[type="text"], input[type="file"], input[type="submit"] {
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
    <h2>Editar Firma de Incidencia</h2>

    <form method="POST" action="cierre.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $incidencia['id']; ?>">

        <label for="firma">Firma:</label>
        <input type="text" name="firma" id="firma" value="<?php echo $incidencia['firma']; ?>" required>

        <label for="foto_evidencia_atencion">Foto de Cierre:</label>
        <input type="file" name="foto_evidencia_atencion" id="foto_evidencia_atencion" required>

        <input type="submit" value="Actualizar Firma y Foto">
    </form>
</body>
</html>
