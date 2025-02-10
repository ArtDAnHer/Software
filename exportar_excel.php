<?php
require_once 'Database.php';

// Recibir los parámetros filtrados
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : null;

// Obtener los datos filtrados desde la base de datos
$db = new Database();
$data = $db->getEntries($startDate, $endDate, $estacionamiento);

// Cabeceras para forzar la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=tabla_boletos_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Crear el contenido del archivo
echo "<table border='1'>";
echo "<thead>
    <tr>
        <th>Fecha</th>
        <th>Estacionamiento</th>
        <th>Tarifa Ordinaria Boletos</th>
        <th>Tarifa Ordinaria Importe</th>
        <th>Tarifa Preferencial Boletos</th>
        <th>Tarifa Preferencial Importe</th>
        <th>Recobro Boletos</th>
        <th>Recobro Importe</th>
        <th>Cortesía Boletos</th>
        <th>Cortesía Importe</th>
        <th>Boletos Perdidos</th>
        <th>Boletos Perdidos Importe</th>
        <th>Boletos Emitidos</th>
        <th>Boletos Controlados</th>
        <th>Otros Importes</th>
        <th>Total</th>
        <th>Depósito</th>
    </tr>
</thead>";

foreach ($data as $row) {
    $total = $row['TOImporte'] + $row['TPImporte'] + $row['RImporte'] + $row['CImporte'] + $row['Otros'];
    echo "<tr>
        <td>" . date("d/m/Y", strtotime($row['Fecha'])) . "</td>
        <td>" . htmlspecialchars($row['Estacionamiento']) . "</td>
        <td>" . number_format($row['TOBoleto'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['TOImporte'], 2, '.', ',') . "</td>
        <td>" . number_format($row['TPBoleto'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['TPImporte'], 2, '.', ',') . "</td>
        <td>" . number_format($row['RBoletos'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['RImporte'], 2, '.', ',') . "</td>
        <td>" . number_format($row['CBoletos'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['CImporte'], 2, '.', ',') . "</td>
        <td>" . number_format($row['boletos_perdidos'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['importe_boletos_perdidos'], 2, '.', ',') . "</td>
        <td>" . number_format($row['BEmitidos'], 0, '.', ',') . "</td>
        <td>" . number_format($row['BControlados'], 0, '.', ',') . "</td>
        <td>$" . number_format($row['Otros'], 2, '.', ',') . "</td>
        <td>$" . number_format($total, 2, '.', ',') . "</td>
        <td>$" . number_format($row['Deposito'], 2, '.', ',') . "</td>
    </tr>";
}
echo "</table>";
