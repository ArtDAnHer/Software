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

    public function getIncidencias() {
        $sql = "SELECT * FROM incidencias where atendido = 0";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIncidencia($data) {
        $sql = "UPDATE incidencias SET 
                    fecha_reporte = :fecha_reporte, 
                    quien_reporta = :quien_reporta, 
                    tipo = :tipo, 
                    lugar = :lugar, 
                    equipo = :equipo, 
                    estado = :estado, 
                    area = :area, 
                    descripcion = :descripcion, 
                    operando = :operando, 
                    imagen = :imagen, 
                    reincidencia = :reincidencia, 
                    incidencia_relacionada = :incidencia_relacionada 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        // Bind params for all the fields
        $stmt->bindParam(':fecha_reporte', $data['fecha_reporte']);
        $stmt->bindParam(':quien_reporta', $data['quien_reporta']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':operando', $data['operando']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':reincidencia', $data['reincidencia']);
        $stmt->bindParam(':incidencia_relacionada', $data['incidencia_relacionada']);
        $stmt->bindParam(':id', $data['id']);

        return $stmt->execute();
    }

    public function getIncidenciaById($id) {
        $sql = "SELECT * FROM incidencias WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$db = new Database();
$incidencias = $db->getIncidencias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias</title>
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

        table {
            width: 90%;
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
    <script>
        function abrirPopup(id) {
            var url = 'seleccionar_tecnico.php?id=' + id;
            var popup = window.open(url, 'Editar Incidencia', 'width=600,height=600');
        }
    </script>
</head>
<body>
    <h2>Lista de Incidencias</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha Reporte</th>
                <th>Quien Reporta</th>
                <th>Tipo</th>
                <th>Lugar</th>
                <th>Equipo</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th>Operando</th>
                <th>Reincidencia</th>
                <th>Incidencia Relacionada</th>
                <th>Área</th>
                <th>Tecnico</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($incidencias as $incidencia): ?>
            <tr>
                <td><?php echo $incidencia['fecha_reporte']; ?></td>
                <td><?php echo $incidencia['quien_reporta']; ?></td>
                <td><?php echo $incidencia['tipo']; ?></td>
                <td><?php echo $incidencia['lugar']; ?></td>
                <td><?php echo $incidencia['equipo']; ?></td>
                <td><?php echo $incidencia['estado']; ?></td>
                <td><?php echo $incidencia['descripcion']; ?></td>
                <td><?php echo $incidencia['operando']; ?></td>
                <td><?php echo $incidencia['reincidencia']; ?></td>
                <td><?php echo $incidencia['incidencia_relacionada']; ?></td>
                <td><?php echo $incidencia['area']; ?></td>
                <td><?php echo $incidencia['tecnico']; ?></td>
                <td><button onclick="abrirPopup(<?php echo $incidencia['id']; ?>)">Editar</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
