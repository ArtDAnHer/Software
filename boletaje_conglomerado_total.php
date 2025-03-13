<?php
class Database {
    private $db = "boletaje";
    private $ip = "192.168.1.98";
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
                    AVG(TBoleto) As TBoletoAvg,
                    AVG(TImporte) As TImporteAvg,
                    SUM(BEmitidos) AS BoletosEmitidos,
                    SUM(BControlados) AS BoletosControlados,
                    SUM(Deposito) AS TotalDepositos,
                    SUM(Otros) AS TotalOtros,
                    SUM(TBoleto) AS TBoletoTotal, 
                    SUM(TImporte) AS TImporteTotal,
                    SUM(boletos_perdidos) AS BoletoPerdidoTotal,
                    SUM(importe_boletos_perdidos) AS ImporteBoletosPerdidosTotal,
                    AVG(boletos_perdidos) AS BoletoPerdidoAvg,
                    SUM(importe_boletos_perdidos) AS ImporteBoletosPerdidosAvg,
                    SUM(boletoNoUtil) AS BoletoNoUtilTotal,
                    AVG(boletoNoUtil) AS BoletoNoUtilAvg,
                    SUM(apps) AS appsTotal,
                    AVG(apps) AS appsAvg
                    
                FROM boletos
                WHERE Fecha BETWEEN :startDate AND :endDate
                AND Estacionamiento LIKE :estacionamiento";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindValue(':estacionamiento', "%{$estacionamiento}%");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : '';

$estacionamientos = $db->getEstacionamientos();

$data = $db->getTotalsAndAveragesByDateRange($startDate, $endDate, $estacionamiento);

// Variables para los cálculos
$totalImportes = $data['TOImporteTotal'] + $data['TPImporteTotal'] + $data['RImporteTotal'] + $data['CImporteTotal'] + $data['TImporteTotal'] + $data['TotalOtros'] + $data['ImporteBoletosPerdidosTotal'] + $data['appsTotal'];
$totalBoletos = $data['TOBoletoTotal'] + $data['TPBoletoTotal'] + $data['RBoletosTotal'] + $data['CBoletosTotal'] + $data['TBoletoTotal'] + $data['BoletoPerdidoTotal'] + $data['BoletoNoUtil'] ;
$totalDepositos = $data['TotalDepositos'];

$promedioTOBoletoUnitario = $data['TOBoletoTotal'] > 0 ? $data['TOImporteTotal'] / $data['TOBoletoTotal'] : 0;
$promedioTPBoletoUnitario = $data['TPBoletoTotal'] > 0 ? $data['TPImporteTotal'] / $data['TPBoletoTotal'] : 0;
$promedioRBoletoUnitario = $data['RBoletosTotal'] > 0 ? $data['RImporteTotal'] / $data['RBoletosTotal'] : 0;
$promedioCBoletoUnitario = $data['CBoletosTotal'] > 0 ? $data['CImporteTotal'] / $data['CBoletosTotal'] : 0;
$promedioTBoletoUnitario = $data['TBoletoTotal'] > 0 ? $data['TImporteTotal'] / $data['TBoletoTotal'] : 0;
$promedioBoletoPerdidoUnitario = $data['BoletoPerdidoTotal'] > 0 ? $data['ImporteBoletosPerdidosTotal'] / $data['BoletoPerdidoTotal'] : 0;


$promedioTotalBoletoUnitario = $totalBoletos > 0 ? $totalImportes / $totalBoletos : 0;

// Calcular el total de tolerancias
$totalToleranciaBoletos = $data['TBoletoTotal'];
$totalToleranciaImportes = $data['TImporteTotal'];

// Calcular boletos perdidos
$boletosPerdidos = $data['BoletosEmitidos'] - $data['BoletosControlados'];
$boletosEmitidos = $data['BoletosEmitidos'];
$boletosControlados = $data['BoletosControlados'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Totales y Promedios por Rango de Fechas</title>
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
</head>
<body>
    <h2>Totales y Promedios entre Fechas</h2>
    <hr>
    <div class="form-container" >
        <form method="POST" action="" align="left">
        <label for="startDate">Seleccione la fecha de inicio:</label>
        <input type="date" name="startDate" id="startDate" value="<?= htmlspecialchars($startDate) ?>" required>

        <label for="endDate">Seleccione la fecha de fin:</label>
        <input type="date" name="endDate" id="endDate" value="<?= htmlspecialchars($endDate) ?>" required>

        <label for="estacionamiento">Seleccione el estacionamiento:</label>
            <select name="estacionamiento" id="estacionamiento">
                <option value="">Todos</option>
                <?php foreach ($estacionamientos as $est): ?>
                    <option value="<?= htmlspecialchars($est['estacionamiento']) ?>" <?= $estacionamiento === $est['estacionamiento'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($est['estacionamiento']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>

<h3>Centro Republica del salvador</h3><br>
                ordinario = autos<br>
                preferencial = camionetas<br>
                recobros = motos<br>
                tolerancia = hotel<br>

    <table>
    <thead>
        <tr>
            <th>Área</th>
            <th>Total Boletos</th>
            <th>Total Importe</th>
            <th>Promedio Boletos</th>
            <th>Promedio Importe</th>
            <th>Promedio Unitario por Boleto</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Tarifa ordinaria</td>
            <td class="text-right"><?= number_format($data['TOBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TOImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['TOBoletoAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TOImporteAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioTOBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Tarifa preferencial</td>
            <td class="text-right"><?= number_format($data['TPBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TPImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['TPBoletoAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TPImporteAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioTPBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Recobro</td>
            <td class="text-right"><?= number_format($data['RBoletosTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['RImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['RBoletosAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['RImporteAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioRBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Cortesías</td>
            <td class="text-right"><?= number_format($data['CBoletosTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['CImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['CBoletosAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['CImporteAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioCBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Tolerancia</td>
            <td class="text-right"><?= number_format($data['TBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['TBoletoAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['TImporteAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioTBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Apps</td>
            <td class="text-right">0</td>
            <td class="text-right">$<?= number_format($data['appsTotal'], 2, '.', ',') ?></td>
            <td class="text-right">0</td>
            <td class="text-right">$<?= number_format($data['appsAvg'], 2, '.', ',') ?></td>
            <td class="text-right">0</td>
        </tr>
        <tr>
            <td>Boletos Perdidos</td>
            <td class="text-right"><?= number_format($data['BoletoPerdidoTotal'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['ImporteBoletosPerdidosTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format($data['BoletoPerdidoAvg'], 0, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($data['ImporteBoletosPerdidosAvg'], 2, '.', ',') ?></td>
            <td class="text-right">$<?= number_format($promedioBoletoPerdidoUnitario, 2, '.', ',') ?></td>
        </tr>
    </tbody>
</table>

<style>
    .text-right {
        text-align: right;
    }
</style>

    <br>                    
    <!-- Segunda Tabla: Totales Generales y Otros Cálculos -->

    <br>
    <h3>Resumen de Boletaje</h3>
    <table>
    <thead>
        <tr>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>% Cantidad</th>
            <th>Importe $</th>
            <th>% Importe</th>
            <th>Ticket Prom</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Tarifa ordinaria</th>
            <td class="text-right"><?= number_format($data['TOBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TOBoletoTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['TOImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TOImporteTotal'] / $totalImportes) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioTOBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Tarifa preferencial</th>
            <td class="text-right"><?= number_format($data['TPBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TPBoletoTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['TPImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TPImporteTotal'] / $totalImportes) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioTPBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Recobro</th>
            <td class="text-right"><?= number_format($data['RBoletosTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['RBoletosTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['RImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['RImporteTotal'] / $totalImportes) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioRBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Cortesías</th>
            <td class="text-right"><?= number_format($data['CBoletosTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['CBoletosTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['CImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['CImporteTotal'] / $totalImportes) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioCBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Tolerancia</th>
            <td class="text-right"><?= number_format($data['TBoletoTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TBoletoTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['TImporteTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['TImporteTotal'] / $totalImportes) * 100, 0, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioTBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Boletos Perdidos</th>
            <td class="text-right"><?= number_format($data['BoletoPerdidoTotal'], 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['BoletoPerdidoTotal'] / $totalBoletos) * 100, 2, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($data['ImporteBoletosPerdidosTotal'], 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($data['ImporteBoletosPerdidosTotal'] / $totalImportes) * 100, 0, '.', ',') ?>%</td>
            <td class="text-right">$<?= number_format($promedioBoletoPerdidoUnitario, 2, '.', ',') ?></td>
        </tr>
        <tr>
            <th>Otros Importes</th>
            <td ></td>
            <td ></td>
            <td class="text-right">$<?= number_format($data['TotalOtros'], 2, '.', ',') ?></td>
            <td class="text-right">100%</td>
            <td ></td>
        </tr>
        <tr>
            <td><strong>Total General</strong></td>
            <td></td>
            <td></td>
            <td class="text-right">$<?= number_format($totalImportes, 2, '.', ',') ?></td>
            <td class="text-right">100%</td>
            <td></td>
        </tr>
        <tr>
            <th>Deposito</th>
            <td></td>
            <td></td>
            <td class="text-right">$<?= number_format($totalDepositos, 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($totalDepositos / $totalImportes) * 100, 0, '.', ',') ?>%</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Diferencia Importe</strong></td>
            <td></td>
            <td></td>
            <td class="text-right">$<?= number_format($totalImportes - $totalDepositos, 2, '.', ',') ?></td>
            <td class="text-right"><?= number_format((($totalImportes - $totalDepositos) / $totalDepositos ) * 100, 0, '.', ',') ?>%</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Total Emitidos</strong></td>
            <td class="text-right"><?= number_format($boletosEmitidos, 0, '.', ',') ?></td>
            <td class="text-right">100%</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Boletos Controlados</strong></td>
            <td class="text-right"><?= number_format($boletosControlados, 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($boletosEmitidos / $boletosControlados) * 100, 0, '.', ',')?>%</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Boletos Perdidos</strong></td>
            <td class="text-right"><?= number_format($boletosPerdidos, 0, '.', ',') ?></td>
            <td class="text-right"><?= number_format(($boletosPerdidos / $boletosControlados) * 100, 0, '.', ',') ?>%</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Promedio Unitario Total por Boleto</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">$<?= number_format($promedioTotalBoletoUnitario, 2, '.', ',') ?></td>
        </tr>
    </tbody>
</table>

<style>
    .text-right {
        text-align: right;
    }
</style>


    <br>
    <div align="center">
    <!-- Gráficos de Pastel -->
    <div class="chart-container">
        <h3>Distribución de Boletos</h3>
        <canvas id="boletosPieChart"></canvas>
    </div>
    <br><br>
    <div class="chart-container">
        <h3>Distribución de Importe</h3>
        <canvas id="importePieChart"></canvas>
    </div >

    <!-- Librerías necesarias para los gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos para los gráficos de pastel
            var dataBoletos = {
                labels: ['Tarifa ordinaria', 'Tarifa preferencial', 'Recobro', 'Cortesías', 'Tolerancia', 'Boletos perdidos', 'Boleto no util o muestra'],
                datasets: [{
                    data: [
                        <?= $data['TOBoletoTotal'] ?>,
                        <?= $data['TPBoletoTotal'] ?>,
                        <?= $data['RBoletosTotal'] ?>,
                        <?= $data['CBoletosTotal'] ?>,
                        <?= $data['TBoletoTotal'] ?>,
                        <?= $data['BoletoPerdidoTotal']?>
                        <?= $data['boletoNoUtilTotal']?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#E74C3C', '#34d9d7']
                }]
            };

            var dataImporte = {
                labels: ['Tarifa ordinaria', 'Tarifa preferencial', 'Recobro', 'Cortesías', 'Tolerancia', 'Boletos Perdidos'],
                datasets: [{
                    data: [
                        <?= $data['TOImporteTotal'] ?>,
                        <?= $data['TPImporteTotal'] ?>,
                        <?= $data['RImporteTotal'] ?>,
                        <?= $data['CImporteTotal'] ?>,
                        <?= $data['TImporteTotal'] ?>,
                        <?= $data['ImporteBoletosPerdidosTotal'] ?>
                        
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#E74C3C']
                }]
            };

            // Crear gráfico de pastel para boletos
            var ctxBoletos = document.getElementById('boletosPieChart').getContext('2d');
            new Chart(ctxBoletos, {
                type: 'pie',
                data: dataBoletos,
                plugins: [ChartDataLabels],
                options: {
                    plugins: {
                        datalabels: {
                            color: 'black',
                            font: {
                                weight: 'bold'
                            },
                            formatter: function(value) {
                                return value + ' boletos';
                            }
                        }
                    }
                }
            });

            // Crear gráfico de pastel para importe
            var ctxImporte = document.getElementById('importePieChart').getContext('2d');
            new Chart(ctxImporte, {
                type: 'pie',
                data: dataImporte,
                plugins: [ChartDataLabels],
                options: {
                    plugins: {
                        datalabels: {
                            color: 'black',
                            font: {
                                weight: 'bold'
                            },
                            formatter: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            });
        });
    </script>
    </div>
</body>
</html>
