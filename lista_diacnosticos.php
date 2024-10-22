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

    public function getIncidencias() {
        $sql = "SELECT * FROM incidencias where atendido = 0";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Lista de Incidencias</title>
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
    <script>
        function abrirPopup(id) {
            var url = 'crear_diagnostico.php?id=' + id;
            var popup = window.open(url, 'Editar Incidencia', 'width=600,height=600');
        }
    </script>
</head>
<body>
    <h2>Lista de Incidencias</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha Reporte</th>
                <th>Quien Reporta</th>
                <th>Tipo</th>
                <th>Lugar</th>
                <th>Equipo</th>
                <th>Descripción</th>
                <th>Operando</th>
                <th>Imagen</th>
                <th>Reincidencia</th>
                <th>Incidencia Relacionada</th>
                <th>Estado</th>
                <th>Área</th>
                <th>Técnico</th>
                <th>Diagnóstico</th>
                <th>Requiere Piezas</th>
                <th>Detalle Piezas Requeridas</th>
                <th>Refacción Adicional 1</th>
                <th>Refacción Adicional 2</th>
                <th>Foto Evidencia Atención</th>
                <th>Fecha Atención</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($incidencias as $incidencia): ?>
            <tr>
                <td><?php echo $incidencia['id']; ?></td>
                <td><?php echo $incidencia['fecha_reporte']; ?></td>
                <td><?php echo $incidencia['quien_reporta']; ?></td>
                <td><?php echo $incidencia['tipo']; ?></td>
                <td><?php echo $incidencia['lugar']; ?></td>
                <td><?php echo $incidencia['equipo']; ?></td>
                <td><?php echo $incidencia['descripcion']; ?></td>
                <td><?php echo $incidencia['operando'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo $incidencia['imagen']; ?></td>
                <td><?php echo $incidencia['reincidencia'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo $incidencia['incidencia_relacionada']; ?></td>
                <td><?php echo $incidencia['estado']; ?></td>
                <td><?php echo $incidencia['area']; ?></td>
                <td><?php echo $incidencia['tecnico']; ?></td>
                <td><?php echo $incidencia['diagnostico']; ?></td>
                <td><?php echo $incidencia['requiere_piezas'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo $incidencia['detalle_piezas_requeridas']; ?></td>
                <td><?php echo $incidencia['refaccion_adicional_1']; ?></td>
                <td><?php echo $incidencia['refaccion_adicional_2']; ?></td>
                <td><?php echo $incidencia['foto_evidencia_atencion']; ?></td>
                <td><?php echo $incidencia['fecha_atencion']; ?></td>
                <td><button onclick="abrirPopup(<?php echo $incidencia['id']; ?>)">Editar</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
