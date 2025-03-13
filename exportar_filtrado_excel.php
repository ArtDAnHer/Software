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
            exit;
        }
    }

    public function getTotalsByDateAndEstacionamiento($date, $estacionamiento) {
        $sql = "
            SELECT 
                Fecha,
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
                SUM(boletoNoUtil) AS total_no_util,
                SUM(penciones_cantidad) AS total_penciones,
                SUM(penciones_importe) AS total_importe_penciones
            FROM boletos
            WHERE Fecha = :date
            GROUP BY Estacionamiento, Fecha
        ";

        if ($estacionamiento !== 'Todos') {
            $sql .= " AND Estacionamiento = :estacionamiento";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':date', $date);
        if ($estacionamiento !== 'Todos') {
            $stmt->bindParam(':estacionamiento', $estacionamiento);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$db = new Database();

$date = $_POST['date'] ?? '';
$estacionamiento = $_POST['estacionamiento'] ?? 'Todos';

if (!$date) {
    die("Debe proporcionar una fecha.");
}

$totals = $db->getTotalsByDateAndEstacionamiento($date, $estacionamiento);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=boletaje_data_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1' style='text-align: right;'>";
echo "<tr>
        <th>Estacionamiento</th>
        <th>Con Sello</th>
        <th>Sin Sello</th>
        <th>Exentos</th>
        <th>Perdidos</th>
        <th>Muestras</th>
        <th>Total</th>
        <th>Emitidos</th>
        <th>Diferencia</th>
    </tr>";

foreach ($totals as $row) {
    $boletajeTotal = ((int)($row['total_tpboleto'] ?? 0))
                    + ((int)($row['total_toboleto'] ?? 0))
                    + ((int)($row['total_cboletos'] ?? 0))
                    + ((int)($row['total_boletos_perdidos'] ?? 0))
                    + ((int)($row['total_no_util'] ?? 0));
    $diferencia = ((int)($row['total_bemitidos'] ?? 0)) - $boletajeTotal;
    echo "<tr>
            <td style='text-align: left;'>" . htmlspecialchars($row['Estacionamiento']) . "</td>
            <td style='text-align: right;'>" . number_format($row['total_tpboleto'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['total_toboleto'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['total_cboletos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['total_boletos_perdidos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['total_no_util'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($boletajeTotal, 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['total_bemitidos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($diferencia, 2, '.', ',') . "</td>
        </tr>";
}
echo "</table><br>";

echo "<table border='1' style='text-align: right;'>";
echo "<tr>
        <th>Estacionamiento</th>
        <th>Con Sello</th>
        <th>Sin Sello</th>
        <th>Perdidos</th>
        <th>Otros</th>
        <th>Total</th>
        <th>Apps</th>
        <th>Deposito</th>
        <th>Total Apps y Deposito</th>
        <th>Diferencia</th>
    </tr>";

foreach ($totals as $row) {
    $importeTotal = $row['total_tpimporte'] + $row['total_toimporte'] + $row['total_cimporte'] + $row['total_importe_boletos_perdidos'];
    $importeappTotal = ((float)($row['total_apps'] ?? 0)) + $importeTotal;
    $diferenciaImporte = ((float)($row['total_deposito'] ?? 0)) - $importeappTotal;
    echo "<tr>
             <td style='text-align: left;'>" . htmlspecialchars($row['Estacionamiento']) . "</td>
             <td style='text-align: right;'>" . number_format($row['total_tpimporte'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($row['total_toimporte'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($row['total_importe_boletos_perdidos'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($row['total_otros'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($importeTotal, 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($row['total_apps'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($row['total_deposito'], 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($importeappTotal, 2, '.', ',') . "</td>
             <td style='text-align: right;'>" . number_format($diferenciaImporte, 2, '.', ',') . "</td>
        </tr>";
}
echo "</table>";
exit;
?>