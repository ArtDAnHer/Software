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

    public function getIncidencias($fechaInicio = null, $fechaFin = null, $estacionamiento = null) {
        $sql = "SELECT * FROM incidencias WHERE 1=1";
        $params = [];

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND fecha_reporte BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        if ($estacionamiento) {
            $sql .= " AND lugar = :estacionamiento";
            $params[':estacionamiento'] = $estacionamiento;
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

    public function getAtendidoCounts($fechaInicio = null, $fechaFin = null, $estacionamiento = null) {
        $sql = "SELECT COUNT(*) as total, SUM(atendido = 1) as atendido FROM incidencias WHERE 1=1";
        $params = [];

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND fecha_reporte BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        if ($estacionamiento) {
            $sql .= " AND lugar = :estacionamiento";
            $params[':estacionamiento'] = $estacionamiento;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$db = new Database();
$estacionamientos = $db->getUniqueValues('lugar');

$incidencias = $db->getIncidencias(
    $_GET['fecha_inicio'] ?? null,
    $_GET['fecha_fin'] ?? null,
    $_GET['estacionamiento'] ?? null
);

$atendidoCounts = $db->getAtendidoCounts(
    $_GET['fecha_inicio'] ?? null,
    $_GET['fecha_fin'] ?? null,
    $_GET['estacionamiento'] ?? null
);

$totalIncidencias = $atendidoCounts['total'];
$incidenciasAtendidas = $atendidoCounts['atendido'];
$incidenciasNoAtendidas = $totalIncidencias - $incidenciasAtendidas;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Incidencias</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    /* Styles for form and table */
    body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
    h2 { text-align: center; margin-top: 20px; }
    form { background-color: #fff; max-width: 600px; margin: 20px auto; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    form label { font-weight: bold; display: block; margin-bottom: 5px; }
    form input, form select { width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
    form button { background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 16px; cursor: pointer; transition: background-color 0.3s; }
    form button:hover { background-color: #218838; }
    table { width: 80%; margin: 20px auto; border-collapse: collapse; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    table th, table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
    table th { background-color: #28a745; color: white; }
    #chartContainer { width: 60%; margin: 20px auto; }
</style>

<body>
    <h2>Filtrar Incidencias por Fecha y Estacionamiento</h2>
    <form method="GET">
        <label for="fecha_inicio">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">

        <label for="fecha_fin">Fecha Fin:</label>
        <input type="date" name="fecha_fin" value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">

        <label for="estacionamiento">Estacionamiento:</label>
        <select name="estacionamiento">
            <option value="">Todos</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo $estacionamiento; ?>" <?php echo isset($_GET['estacionamiento']) && $_GET['estacionamiento'] == $estacionamiento ? 'selected' : ''; ?>>
                    <?php echo $estacionamiento; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Buscar</button>
    </form>

    <h2>Resultados de Incidencias</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha Reporte</th>
                <th>Quien Reporta</th>
                <th>Lugar</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Técnico</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($incidencias)): ?>
                <?php foreach ($incidencias as $incidencia): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($incidencia['id']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['fecha_reporte']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['quien_reporta']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['lugar']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['estado']); ?></td>
                        <td><?php echo htmlspecialchars($incidencia['tecnico']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No se encontraron resultados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Gráfica de pastel -->
    <div id="chartContainer">
        <canvas id="incidenciasChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('incidenciasChart').getContext('2d');
        const incidenciasChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Atendidas', 'No Atendidas'],
                datasets: [{
                    data: [<?php echo $incidenciasAtendidas; ?>, <?php echo $incidenciasNoAtendidas; ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>
