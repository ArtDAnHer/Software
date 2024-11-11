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

    public function getIncidenciaById($id) {
        $sql = "SELECT * FROM incidencias WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTiposRefacciones() {
        $sql = "SELECT id, nombre FROM tipos_refacciones";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIncidencia($data) {
        $sql = "UPDATE incidencias SET diagnostico = :diagnostico, requiere_piezas = :requiere_piezas,
                detalle_piezas_requeridas = :detalle_piezas_requeridas,
                requerimiento = :requerimiento, refaccion_adicional_1 = :refaccion_adicional_1, 
                requerimiento1 = :requerimiento1, refaccion_adicional_2 = :refaccion_adicional_2, 
                requerimiento2 = :requerimiento2, foto_evidencia_atencion = :foto_evidencia_atencion
                WHERE id = :id";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':diagnostico', $data['diagnostico']);
        $stmt->bindParam(':requiere_piezas', $data['requiere_piezas']);
        $stmt->bindParam(':detalle_piezas_requeridas', $data['detalle_piezas_requeridas']);
        $stmt->bindParam(':requerimiento', $data['requerimiento']);
        $stmt->bindParam(':refaccion_adicional_1', $data['refaccion_adicional_1']);
        $stmt->bindParam(':requerimiento1', $data['requerimiento1']);
        $stmt->bindParam(':refaccion_adicional_2', $data['refaccion_adicional_2']);
        $stmt->bindParam(':requerimiento2', $data['requerimiento2']);
        $stmt->bindParam(':foto_evidencia_atencion', $data['foto_evidencia_atencion']);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
    
        return $stmt->execute();
    }
}

// Fetch options and incidencia data
$db = new Database();
$tiposRefacciones = $db->getTiposRefacciones();
$incidencia = isset($_GET['id']) ? $db->getIncidenciaById($_GET['id']) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get existing photo_evidencia_atencion if not changed
    $fotoEvidencia = $incidencia['foto_evidencia_atencion']; 

    // Handle file upload with image validation
    if (isset($_FILES['foto_evidencia_atencion']) && $_FILES['foto_evidencia_atencion']['error'] == 0) {
        $fileType = $_FILES["foto_evidencia_atencion"]["type"];
        if (in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
            $targetDirectory = "uploads/";
            $targetFile = $targetDirectory . basename($_FILES["foto_evidencia_atencion"]["name"]);
            if (move_uploaded_file($_FILES["foto_evidencia_atencion"]["tmp_name"], $targetFile)) {
                $fotoEvidencia = $targetFile;  // Update photo if new image is uploaded
            } else {
                echo "Error al subir la imagen.";
            }
        } else {
            echo "Solo se permiten archivos de imagen (JPEG, PNG, GIF).";
        }
    }

    $data = [
        'id' => $_POST['id'],
        'diagnostico' => $_POST['diagnostico'],
        'requiere_piezas' => $_POST['requiere_piezas'],
        'detalle_piezas_requeridas' => $_POST['detalle_piezas_requeridas'],
        'requerimiento' => $_POST['requerimiento'],
        'refaccion_adicional_1' => $_POST['refaccion_adicional_1'],
        'requerimiento1' => $_POST['requerimiento1'],
        'refaccion_adicional_2' => $_POST['refaccion_adicional_2'],
        'requerimiento2' => $_POST['requerimiento2'],
        'foto_evidencia_atencion' => $fotoEvidencia // Use the existing or new image
    ];

    if ($db->updateIncidencia($data)) {
        echo "<script type='text/javascript'>
                window.opener.location.reload(); // Reload parent window
                window.close(); // Close the current window
              </script>";
    } else {
        echo "Error al actualizar la incidencia.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Incidencia</title>
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
            padding: 20px;
            width: 70%;
            margin: auto;
        }
        table {
            width: 100%;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .thumbnail {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleFields() {
            var requierePiezas = document.getElementsByName("requiere_piezas")[0].value;
            var fieldsToHide = document.getElementsByClassName("hidden-fields");
            if (requierePiezas == "0") {
                for (var i = 0; i < fieldsToHide.length; i++) {
                    fieldsToHide[i].style.display = "none";
                }
            } else {
                for (var i = 0; i < fieldsToHide.length; i++) {
                    fieldsToHide[i].style.display = "";
                }
            }
        }

        window.onload = toggleFields; // Initial call to adjust visibility on page load
    </script>
</head>
<body>
    <h2>Editar Incidencia</h2>
    <form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $incidencia['id']; ?>">
    <table>
        <tr>
            <th>Diagnóstico</th>
            <td><input type="text" name="diagnostico" value="<?php echo htmlspecialchars($incidencia['diagnostico']); ?>" required></td>
        </tr>
        <tr>
            <th>Requiere Piezas</th>
            <td>
                <select name="requiere_piezas" onchange="toggleFields()">
                    <option value="0" <?php echo $incidencia['requiere_piezas'] == '0' ? 'selected' : ''; ?>>No</option>
                    <option value="1" <?php echo $incidencia['requiere_piezas'] == '1' ? 'selected' : ''; ?>>Sí</option>
                </select>
            </td>
        </tr>
        <tr class="hidden-fields">
            <th>Detalle de Piezas Requeridas</th>
            <td><input type="text" name="detalle_piezas_requeridas" value="<?php echo htmlspecialchars($incidencia['detalle_piezas_requeridas']); ?>"></td>
        </tr>
        <tr class="hidden-fields">
            <th>Requerimiento</th>
            <td>
                <select name="requerimiento">
                    <?php foreach ($tiposRefacciones as $refaccion) { ?>
                        <option value="<?php echo $refaccion['id']; ?>" <?php echo $incidencia['requerimiento'] == $refaccion['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($refaccion['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr class="hidden-fields">
            <th>Refacción Adicional 1</th>
            <td><input type="text" name="refaccion_adicional_1" value="<?php echo htmlspecialchars($incidencia['refaccion_adicional_1']); ?>"></td>
        </tr>
        <tr class="hidden-fields">
            <th>Requerimiento 1</th>
            <td>
                <select name="requerimiento1">
                    <?php foreach ($tiposRefacciones as $refaccion) { ?>
                        <option value="<?php echo $refaccion['id']; ?>" <?php echo $incidencia['requerimiento1'] == $refaccion['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($refaccion['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr class="hidden-fields">
            <th>Refacción Adicional 2</th>
            <td><input type="text" name="refaccion_adicional_2" value="<?php echo htmlspecialchars($incidencia['refaccion_adicional_2']); ?>"></td>
        </tr>
        <tr class="hidden-fields">
            <th>Requerimiento 2</th>
            <td>
                <select name="requerimiento2">
                    <?php foreach ($tiposRefacciones as $refaccion) { ?>
                        <option value="<?php echo $refaccion['id']; ?>" <?php echo $incidencia['requerimiento2'] == $refaccion['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($refaccion['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Foto de Evidencia de Atención</th>
            <td>
                <?php if ($incidencia['foto_evidencia_atencion']) { ?>
                    <img class="thumbnail" src="<?php echo $incidencia['foto_evidencia_atencion']; ?>" alt="Foto de evidencia">
                <?php } ?>
                <input type="file" name="foto_evidencia_atencion">
            </td>
        </tr>
    </table>
    <button type="submit">Actualizar</button>
</form>
</body>
</html>
