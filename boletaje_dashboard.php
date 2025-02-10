<?php
class Database {
    private $db = "boletaje";
    private $ip = "192.168.1.73";
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

    public function getTotalsAndAveragesByDateRange($startDate, $endDate, $estacionamiento) {
        $sql = "SELECT 
                    Fecha,
                    SUM(TOImporte) AS TOImporteTotal, 
                    SUM(TPImporte) AS TPImporteTotal, 
                    SUM(RImporte) AS RImporteTotal, 
                    SUM(CImporte) AS CImporteTotal,
                    AVG(TOImporte) AS TOImporteAvg, 
                    AVG(TPImporte) AS TPImporteAvg, 
                    AVG(RImporte) AS RImporteAvg, 
                    AVG(CImporte) AS CImporteAvg,
                    SUM(TOBoleto) AS TOBoletoTotal,
                    SUM(TPBoleto) AS TPBoletoTotal,
                    SUM(RBoletos) AS RBoletosTotal,
                    SUM(CBoletos) AS CBoletosTotal,
                    AVG(TOBoleto) AS TOBoletoAvg,
                    AVG(TPBoleto) AS TPBoletoAvg,
                    AVG(RBoletos) AS RBoletosAvg,
                    AVG(CBoletos) AS CBoletosAvg,
                    AVG(TBoleto) AS TBoletoAvg,
                    AVG(TImporte) AS TImporteAvg,
                    SUM(BEmitidos) AS BoletosEmitidos,
                    SUM(BControlados) AS BoletosControlados,
                    SUM(Deposito) AS TotalDepositos,
                    SUM(TBoleto) AS TBoletoTotal, 
                    SUM(TImporte) AS TImporteTotal,
                    SUM(boletoNoUtil) AS boletoNoUtilTotal,
                    AVG(boletoNoUtil) As boletoNoUtilAvg
                FROM boletos
                WHERE Fecha BETWEEN :startDate AND :endDate
                AND (:estacionamiento = 'Todo' OR Estacionamiento LIKE :estacionamiento)
                GROUP BY Fecha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindValue(':estacionamiento', $estacionamiento === 'Todo' ? '%' : "%{$estacionamiento}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPreviousMonthTotalsAndAverages($startDate, $endDate, $estacionamiento) {
        $prevStartDate = date('Y-m-01', strtotime('-1 month', strtotime($startDate)));
        $prevEndDate = date('Y-m-t', strtotime('-1 month', strtotime($endDate)));

        return $this->getTotalsAndAveragesByDateRange($prevStartDate, $prevEndDate, $estacionamiento);
    }

    // Método para obtener todos los estacionamientos distintos
    public function getEstacionamientos() {
        $sql = "SELECT DISTINCT estacionamiento FROM estacionamiento";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$db = new Database();

$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-t');
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : 'Todo';

$estacionamientos = $db->getEstacionamientos();
$data = $db->getTotalsAndAveragesByDateRange($startDate, $endDate, $estacionamiento);
$prevMonthData = $db->getPreviousMonthTotalsAndAverages($startDate, $endDate, $estacionamiento);

$fechas = [];
$importeTotal = [];
$boletajeTotal = [];
$boletosPerdidos = [];
$importeTotalPrev = [];
$boletajeTotalPrev = [];
$boletosPerdidosPrev = [];

// Rellenar los arrays con datos del mes actual
foreach ($data as $row) {
    $fechas[] = $row['Fecha'];
    $importeTotal[] = $row['TOImporteTotal'] + $row['TPImporteTotal'] + $row['RImporteTotal'] + $row['CImporteTotal'];
    $boletajeTotal[] = $row['TOBoletoTotal'] + $row['TPBoletoTotal'] + $row['RBoletosTotal'] + $row['CBoletosTotal']+ $row['TBoletoTotal'] + $row['boletoNoUtilTotal'];
    $boletosPerdidos[] = $row['BoletosEmitidos'] - $row['BoletosControlados'];
}

// Rellenar los arrays con datos del mes anterior
foreach ($prevMonthData as $row) {
    $importeTotalPrev[] = $row['TOImporteTotal'] + $row['TPImporteTotal'] + $row['RImporteTotal'] + $row['CImporteTotal'];
    $boletajeTotalPrev[] = $row['TOBoletoTotal'] + $row['TPBoletoTotal'] + $row['RBoletosTotal'] + $row['CBoletosTotal']+ $row['TBoletoTotal'] + $row['boletoNoUtilTotal'];
    $boletosPerdidosPrev[] = $row['BoletosEmitidos'] - $row['BoletosControlados'];
}
?>
<!DOCTYPE html>



<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Entradas</title>
    <style>
        /* Añadir estilo para los gráficos */
        
        canvas {
            width: 800px!important; /* Ajusta el ancho del canvas */
            height: 800px !important; /* Ajusta la altura según sea necesario */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        .form-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container label {
            margin-right: 10px;
        }

        /* style.css */

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
form input[type="number"],
form select {
    width: calc(100% - 22px);
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

form input[type="number"] {
    text-align: right;
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

form hr {
    border: 0;
    border-top: 1px solid #ddd;
    margin: 20px 0;
}

form .required {
    color: red;
    font-size: 0.9em;
}

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Dashboard de Entradas por Rango de Fechas y Estacionamiento</h2>
    <hr>

    <!-- Formulario para seleccionar el rango de fechas y estacionamiento -->
    <form method="POST" action="">
        <label for="startDate">Seleccione la fecha de inicio:</label>
        <input type="date" name="startDate" id="startDate" value="<?= htmlspecialchars($startDate) ?>" required>

        <label for="endDate">Seleccione la fecha de fin:</label>
        <input type="date" name="endDate" id="endDate" value="<?= htmlspecialchars($endDate) ?>" required>

        <label for="estacionamiento">Seleccione el estacionamiento:</label>
        <select name="estacionamiento" id="estacionamiento" required>
            <option value="Todo" <?= ($estacionamiento === 'Todo') ? 'selected' : '' ?>>Todo</option>
            <?php foreach ($estacionamientos as $est) : ?>
                <option value="<?= htmlspecialchars($est['estacionamiento']) ?>" <?= ($estacionamiento === $est['estacionamiento']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($est['estacionamiento']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Actualizar</button>
    </form>

    <!-- Tabla de Datos -->
    <table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Importe Total (Actual)</th>
            <th>Boletaje Total (Actual)</th>
            <th>Boletos no Cobrados (Actual)</th>
            <th>Importe Total (Anterior)</th>
            <th>Boletaje Total (Anterior)</th>
            <th>Boletos no Cobrados (Anterior)</th>
            <th>Boleto no util y pruebas</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 0; $i < count($fechas); $i++) : ?>
            <tr>
                <td><?= htmlspecialchars($fechas[$i]) ?></td>
                <td class="text-right"><?= number_format($importeTotal[$i], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($boletajeTotal[$i], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($boletosPerdidos[$i], 0, ',', '.') ?></td>
                <td class="text-right"><?= isset($importeTotalPrev[$i]) ? htmlspecialchars($importeTotalPrev[$i]) : 'N/A' ?></td>
                <td class="text-right"><?= isset($boletajeTotalPrev[$i]) ? htmlspecialchars($boletajeTotalPrev[$i]) : 'N/A' ?></td>
                <td class="text-right"><?= isset($boletosPerdidosPrev[$i]) ? htmlspecialchars($boletosPerdidosPrev[$i]) : 'N/A' ?></td>
                <td class="text-right"><?= isset($boletoNoUtil) ? htmlspecialchars($boletoNoUtil) : 'N/A' ?></td>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>

<style>
    .text-right {
        text-align: right;
    }
</style>




    <!-- Gráficos -->
    <div style="width: 80%; margin: 20px auto;">
        <canvas id="importesChart"></canvas>
    </div>
    <div style="width: 80%; margin: 20px auto;">
        <canvas id="boletajeChart"></canvas>
    </div>
    <div style="width: 80%; margin: 20px auto;">
        <canvas id="boletosPerdidosChart"></canvas>
    </div>

    <script>
        // Datos para gráficos
        const fechas = <?= json_encode($fechas) ?>;
        const importeTotal = <?= json_encode($importeTotal) ?>;
        const boletajeTotal = <?= json_encode($boletajeTotal) ?>;
        const boletosPerdidos = <?= json_encode($boletosPerdidos) ?>;
        const importeTotalPrev = <?= json_encode($importeTotalPrev) ?>;
        const boletajeTotalPrev = <?= json_encode($boletajeTotalPrev) ?>;
        const boletosPerdidosPrev = <?= json_encode($boletosPerdidosPrev) ?>;

        // Configuración del gráfico de importes
        const ctxImportes = document.getElementById('importesChart').getContext('2d');
        new Chart(ctxImportes, {
            type: 'bar',
            data: {
                labels: fechas,
                datasets: [
                    {
                        label: 'Importe Total (Actual)',
                        data: importeTotal,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 1)',
                        fill: true,
                    },
                    {
                        label: 'Importe Total (Anterior)',
                        data: importeTotalPrev,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 1)',
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Configuración del gráfico de boletaje
        const ctxBoletaje = document.getElementById('boletajeChart').getContext('2d');
        new Chart(ctxBoletaje, {
            type: 'bar',
            data: {
                labels: fechas,
                datasets: [
                    {
                        label: 'Boletaje Total (Actual)',
                        data: boletajeTotal,
                        backgroundColor: 'rgba(153, 102, 255, 1)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Boletaje Total (Anterior)',
                        data: boletajeTotalPrev,
                        backgroundColor: 'rgba(75, 192, 192, 1)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Configuración del gráfico de boletos perdidos
        const ctxBoletosPerdidos = document.getElementById('boletosPerdidosChart').getContext('2d');
        new Chart(ctxBoletosPerdidos, {
            type: 'bar',
            data: {
                labels: fechas,
                datasets: [
                    {
                        label: 'Boletos no cobrados (Actual)',
                        data: boletosPerdidos,
                        backgroundColor: 'rgba(255, 99, 132, 1)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Boletos no cobrados (Anterior)',
                        data: boletosPerdidosPrev,
                        backgroundColor: 'rgba(255, 159, 64, 1)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
