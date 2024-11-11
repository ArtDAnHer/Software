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

    public function getIncidencias($tecnico = null, $lugar = null, $estado = null, $fechaInicio = null, $fechaFin = null, $area = null) {
        $sql = "SELECT * FROM incidencias WHERE 1=1";
        $params = [];

        if ($tecnico) {
            $sql .= " AND tecnico = :tecnico";
            $params[':tecnico'] = $tecnico;
        }

        if ($lugar) {
            $sql .= " AND lugar = :lugar";
            $params[':lugar'] = $lugar;
        }

        if ($estado) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $estado;
        }

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND fecha_reporte BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        if ($area) {
            $sql .= " AND area = :area";
            $params[':area'] = $area;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueValues($column) {
        $sql = "SELECT DISTINCT $column FROM incidencias";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$db = new Database();
$tecnicos = $db->getUniqueValues('tecnico');
$lugares = $db->getUniqueValues('lugar');
$estados = $db->getUniqueValues('estado');
$areas = $db->getUniqueValues('area');

$incidencias = $db->getIncidencias(
    $_GET['tecnico'] ?? null,
    $_GET['lugar'] ?? null,
    $_GET['estado'] ?? null,
    $_GET['fecha_inicio'] ?? null,
    $_GET['fecha_fin'] ?? null,
    $_GET['area'] ?? null
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Incidencias</title>
</head>

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

    form input[type="text"],
    form textarea {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    form textarea {
        height: 100px;
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

    form select {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
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
        text-align: center;
    }

    table th {
        background-color: #28a745;
        color: white;
    }

    .estado-cerrado {
        background-color: red;
        color: white;
    }

    .estado-fallando {
        background-color: yellow;
        color: black;
    }

    .estado-funcionando {
        background-color: green;
        color: white;
    }
</style>

<body>
    <h2>Filtrar Incidencias</h2>
    <form method="GET">
        <label for="tecnico">Técnico:</label>
        <select name="tecnico">
            <option value="">Todos</option>
            <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?php echo $tecnico; ?>"><?php echo $tecnico; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lugar">Lugar:</label>
        <select name="lugar">
            <option value="">Todos</option>
            <?php foreach ($lugares as $lugar): ?>
                <option value="<?php echo $lugar; ?>"><?php echo $lugar; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="estado">Estado:</label>
        <select name="estado">
            <option value="">Todos</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado; ?>"><?php echo $estado; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="area">Área:</label>
        <select name="area">
            <option value="">Todas</option>
            <?php foreach ($areas as $area): ?>
                <option value="<?php echo $area; ?>"><?php echo $area; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_inicio">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio">
        <br>
        <br>
        <label for="fecha_fin">Fecha Fin:</label>
        <input type="date" name="fecha_fin">
        <br>
        <br>
        <button type="submit">Buscar</button>
    </form>

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
                <th>Atendido</th>
                <th>Firma</th>
                <th>File</th>
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

                    <!-- Aplicar color al estado -->
                    <td class="<?php 
                        if ($incidencia['estado'] == 'cerrado') {
                            echo 'estado-cerrado';
                        } elseif ($incidencia['estado'] == 'fallando') {
                            echo 'estado-fallando';
                        } elseif ($incidencia['estado'] == 'funcionando') {
                            echo 'estado-funcionando';
                        } ?>">
                        <?php echo $incidencia['estado']; ?>
                    </td>

                    <td><?php echo $incidencia['area']; ?></td>
                    <td><?php echo $incidencia['tecnico']; ?></td>
                    <td><?php echo $incidencia['diagnostico']; ?></td>
                    <td><?php echo $incidencia['requiere_piezas'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo $incidencia['detalle_piezas_requeridas']; ?></td>
                    <td><?php echo $incidencia['refaccion_adicional_1']; ?></td>
                    <td><?php echo $incidencia['refaccion_adicional_2']; ?></td>
                    <td><?php echo $incidencia['foto_evidencia_atencion']; ?></td>
                    <td><?php echo $incidencia['fecha_atencion']; ?></td>
                    <td><?php echo $incidencia['atendido'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo $incidencia['firma']; ?></td>
                    <td><button onclick="window.location.href='visualizar.php?id=<?php echo $incidencia['id']; ?>'">Visualizar</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
