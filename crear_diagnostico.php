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
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateIncidencia($data) {
        $sql = "UPDATE incidencias SET 
                    diagnostico = :diagnostico, 
                    requiere_piezas = :requiere_piezas, 
                    detalle_piezas_requeridas = :detalle_piezas_requeridas, 
                    refaccion_adicional_1 = :refaccion_adicional_1, 
                    refaccion_adicional_2 = :refaccion_adicional_2, 
                    foto_evidencia_atencion = :foto_evidencia_atencion, 
                    fecha_atencion = :fecha_atencion 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':diagnostico', $data['diagnostico']);
        $stmt->bindParam(':requiere_piezas', $data['requiere_piezas']);
        $stmt->bindParam(':detalle_piezas_requeridas', $data['detalle_piezas_requeridas']);
        $stmt->bindParam(':refaccion_adicional_1', $data['refaccion_adicional_1']);
        $stmt->bindParam(':refaccion_adicional_2', $data['refaccion_adicional_2']);
        $stmt->bindParam(':foto_evidencia_atencion', $data['foto_evidencia_atencion']);
        
        // Cambiar fecha_atencion para que sea la fecha actual
        $fecha_actual = date('Y-m-d H:i:s');
        $stmt->bindParam(':fecha_atencion', $fecha_actual);
        
        $stmt->bindParam(':id', $data['id']);

        return $stmt->execute();
    }
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $data = [
        'id' => $_POST['id'] ?? null,
        'diagnostico' => $_POST['diagnostico'] ?? '',
        'requiere_piezas' => $_POST['requiere_piezas'] ?? '',
        'detalle_piezas_requeridas' => $_POST['detalle_piezas_requeridas'] ?? '',
        'refaccion_adicional_1' => $_POST['refaccion_adicional_1'] ?? '',
        'refaccion_adicional_2' => $_POST['refaccion_adicional_2'] ?? '',
        'foto_evidencia_atencion' => '', // Se llenará después de la subida
    ];

    // Validar campos requeridos
    $required_fields = ['diagnostico']; // Solo el diagnóstico es obligatorio

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo "El campo $field es obligatorio.";
            exit; // Detener la ejecución si un campo requerido está vacío
        }
    }

    // Manejar la subida del archivo
    if (isset($_FILES['foto_evidencia_atencion']) && $_FILES['foto_evidencia_atencion']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['foto_evidencia_atencion']['tmp_name'];
        $file_name = $_FILES['foto_evidencia_atencion']['name'];
        $file_size = $_FILES['foto_evidencia_atencion']['size'];
        $file_type = $_FILES['foto_evidencia_atencion']['type'];
        $file_name_cmp = explode('.', $file_name);
        $file_extension = end($file_name_cmp);

        // Directorio donde se guardarán las imágenes
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $file_name;

        // Verificar el tamaño máximo permitido (opcional)
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        if ($file_size > $maxFileSize) {
            echo "El archivo es demasiado grande. Debe ser menor a 2MB.";
            exit;
        }

        // Mover el archivo al directorio de destino
        if(move_uploaded_file($file_tmp_path, $dest_path)) {
            $data['foto_evidencia_atencion'] = $dest_path; // Guardar la ruta del archivo
        } else {
            echo "Error al mover el archivo subido.";
            exit;
        }
    } else {
        // En caso de que no se suba archivo, usar el valor anterior
        $data['foto_evidencia_atencion'] = $_POST['foto_evidencia_atencion'] ?? '';
    }

    // Actualizar la incidencia en la base de datos
    if ($db->updateIncidencia($data)) {
        echo "Incidencia actualizada correctamente";
    } else {
        echo "Error al actualizar la incidencia";
    }
} else if (isset($_GET['id'])) {
    $db = new Database();
    $incidencia = $db->getIncidenciaById($_GET['id']);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Incidencia</title>
    <script>
        function togglePiezasFields() {
            var requierePiezas = document.querySelector('select[name="requiere_piezas"]').value;
            var piezasFields = document.getElementById('piezas-fields');
            var refaccion1Field = document.getElementById('refaccion1-fields');
            var refaccion2Field = document.getElementById('refaccion2-fields');

            if (requierePiezas === '1') {
                piezasFields.style.display = 'table-row';
                refaccion1Field.style.display = 'table-row'; // Mostrar campos de refacción
                refaccion2Field.style.display = 'table-row'; // Mostrar campos de refacción
            } else {
                piezasFields.style.display = 'none';
                refaccion1Field.style.display = 'none'; // Ocultar campos de refacción
                refaccion2Field.style.display = 'none'; // Ocultar campos de refacción
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            togglePiezasFields(); // Para mostrar/ocultar al cargar la página
            var requierePiezasSelect = document.querySelector('select[name="requiere_piezas"]');
            requierePiezasSelect.addEventListener('change', togglePiezasFields);
        });
    </script>
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

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
        }

        table th {
            background-color: #28a745;
            color: white;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Crear diagnostico</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $incidencia['id']; ?>">
        <table>
            <tr>
                <th>Diagnóstico</th>
                <td><input type="text" name="diagnostico" value="<?php echo $incidencia['diagnostico']; ?>" required></td>
            </tr>
            <tr>
                <th>Requiere Piezas</th>
                <td>
                    <select name="requiere_piezas" onchange="togglePiezasFields()" required>
                        <option value="0" <?php echo $incidencia['requiere_piezas'] == 'No' ? 'selected' : ''; ?>>No</option>
                        <option value="1" <?php echo $incidencia['requiere_piezas'] == 'Si' ? 'selected' : ''; ?>>Sí</option>
                    </select>
                </td>
            </tr>
            <tr id="piezas-fields" style="display: <?php echo $incidencia['requiere_piezas'] == 1 ? 'table-row' : 'none'; ?>">
                <th>Detalle Piezas Requeridas</th>
                <td><input type="text" name="detalle_piezas_requeridas" value="<?php echo $incidencia['detalle_piezas_requeridas']; ?>"></td>
            </tr>
            <tr id="refaccion1-fields" style="display: <?php echo $incidencia['requiere_piezas'] == 1 ? 'table-row' : 'none'; ?>">
                <th>Refacción Adicional 1</th>
                <td><input type="text" name="refaccion_adicional_1" value="<?php echo $incidencia['refaccion_adicional_1']; ?>"></td>
            </tr>
            <tr id="refaccion2-fields" style="display: <?php echo $incidencia['requiere_piezas'] == 1 ? 'table-row' : 'none'; ?>">
                <th>Refacción Adicional 2</th>
                <td><input type="text" name="refaccion_adicional_2" value="<?php echo $incidencia['refaccion_adicional_2']; ?>"></td>
            </tr>
            <tr>
                <th>Foto Evidencia Atención</th>
                <td>
                    <input type="file" name="foto_evidencia_atencion" accept="image/*">
                    <?php if (!empty($incidencia['foto_evidencia_atencion'])): ?>
                        <p><img src="<?php echo $incidencia['foto_evidencia_atencion']; ?>" alt="Evidencia" style="max-width: 100px; max-height: 100px;"></p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit">Actualizar Incidencia</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
