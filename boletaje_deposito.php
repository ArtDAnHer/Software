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
            exit;
        }
    }

    public function getTotalsByDateRangeAndEstacionamiento($startDate, $endDate) {
        $sql = "
            SELECT 
                Estacionamiento,
                SUM(importe_boletos_perdidos) AS total_importe_boletos_perdidos,
                SUM(boletos_perdidos) AS total_boletos_perdidos,
                SUM(Deposito) AS total_deposito,
                SUM(Otros) AS total_otros,
                SUM(BControlados) AS total_bcontrolados,
                SUM(BEmitidos) AS total_bemitidos,
                SUM(TImporte) AS total_timporte,
                SUM(TBoleto) AS total_tboleto,
                SUM(CImporte) AS total_cimporte,
                SUM(CBoletos) AS total_cboletos,
                SUM(RImporte) AS total_rimporte,
                SUM(RBoletos) AS total_rboletos,
                SUM(TPImporte) AS total_tpimporte,
                SUM(TPBoleto) AS total_tpboleto,
                SUM(TOImporte) AS total_toimporte,
                SUM(TOBoleto) AS total_toboleto,
                SUM(apps) AS total_apps,
                SUM(boletoNoUtil) AS total_no_util
            FROM boletos
            WHERE Fecha BETWEEN :startDate AND :endDate
            GROUP BY Estacionamiento
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inicialización de variables y lógica principal
$db = new Database();

$totals = [];
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';
$boletajeTotal = [];
$importeTotal = [];
$importeappTotal = [];
$diferencia = [];
$diferenciaImporte = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $startDate && $endDate) {
    $totals = $db->getTotalsByDateRangeAndEstacionamiento($startDate, $endDate);

    foreach ($totals as $key => $row) {
        $importeTotal[$key] = ((float)($row['total_tpimporte'] ?? 0))  
                            + ((float)($row['total_toimporte'] ?? 0)) 
                            + ((float)($row['total_cimporte'] ?? 0)) 
                            + ((float)($row['total_importe_boletos_perdidos'] ?? 0));

        $importeappTotal[$key] = ((float)($row['total_apps'] ?? 0)) + ((float)($row['total_deposito'] ?? 0));

        $boletajeTotal[$key] = ((int)($row['total_tpboleto'] ?? 0))
                             + ((int)($row['total_toboleto'] ?? 0))
                             + ((int)($row['total_cboletos'] ?? 0))
                             + ((int)($row['total_boletos_perdidos'] ?? 0))
                             + ((int)($row['total_no_util'] ?? 0));

        $diferencia[$key] = ((int)($row['total_bemitidos'] ?? 0)) - $boletajeTotal[$key];

        $diferenciaImporte[$key] = $importeTotal[$key] - $importeappTotal[$key];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Totales y Promedios por Rango de Fechas</title>
    <style>
        canvas {
            width: 800px!important;
            height: 800px !important;
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
    </style>
</head>
<body>
<div align="center">
    <h1>Tabla de boletajes y depósitos</h1>
</div>

<div class="form-container">
    <form method="POST" action="">
        <label for="startDate">Seleccione la fecha de inicio:</label>
        <input type="date" name="startDate" id="startDate" value="<?= htmlspecialchars($startDate) ?>" required>

        <label for="endDate">Seleccione la fecha de fin:</label>
        <input type="date" name="endDate" id="endDate" value="<?= htmlspecialchars($endDate) ?>" required>

        <button type="submit">Filtrar</button>
    </form>
</div>
<br>
<div class="table-title">Boletaje</div>
<table>
    <thead>
        <tr>
            <th>Estacionamiento</th>
            <th>Con Sello</th>
            <th>Sin Sello</th>
            <th>Exentos</th>
            <th>Perdidos</th>
            <th>Muestras</th>
            <th>Total</th>
            <th>Emitidos</th>
            <th>Diferencia</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($totals)): ?>
            <?php foreach ($totals as $key => $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                    <td><?= htmlspecialchars($row['total_tpboleto'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['total_toboleto'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['total_cboletos'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['total_boletos_perdidos'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['total_no_util'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($boletajeTotal[$key] ?? 0) ?></td>
                    <td><?= htmlspecialchars($row['total_bemitidos'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($diferencia[$key] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No se encontraron datos para el rango de fechas seleccionado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<br>
<div class="table-title">Importe</div>
<table>
    <thead>
        <tr>
            <th>Estacionamiento</th>
            <th>Con Sello</th>
            <th>Sin Sello</th>
            <th>Perdidos</th>
            <th>Otros</th>
            <th>Total</th>
            <th>Apps</th>
            <th>Depósito</th>
            <th>Total Apps y Depósito</th>
            <th>Diferencia</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($totals)): ?>
            <?php foreach ($totals as $key => $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_tpimporte'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_toimporte'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_cimporte'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_importe_boletos_perdidos'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($importeTotal[$key] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_apps'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total_deposito'] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($importeappTotal[$key] ?? 0, 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($diferenciaImporte[$key] ?? 0, 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No se encontraron datos para el rango de fechas seleccionado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
