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

    public function getEntries($startDate = null, $endDate = null, $estacionamiento = null) {
        $sql = "SELECT * FROM boletos";
        $params = [];
        $conditions = [];

        if ($startDate && $endDate) {
            $conditions[] = "Fecha BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }

        if ($estacionamiento) {
            $conditions[] = "Estacionamiento = :estacionamiento";
            $params[':estacionamiento'] = $estacionamiento;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Ordenar por Fecha de menor a mayor
        $sql .= " ORDER BY Fecha ASC";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$db = new Database();
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-t');
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : '';
$data = $db->getEntries($startDate, $endDate, $estacionamiento);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=boletaje_data_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>Fecha</th>
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

foreach ($data as $row) {
    $boletajeTotal = ((int)($row['TPBoleto'] ?? 0))
                    + ((int)($row['TOBoleto'] ?? 0))
                    + ((int)($row['CBoletos'] ?? 0))
                    + ((int)($row['boletos_perdidos'] ?? 0))
                    + ((int)($row['boletoNoUtil'] ?? 0));
    $diferencia = ((int)($row['BEmitidos'] ?? 0)) - $boletajeTotal;
    echo "<tr>
            <td>{$row['Fecha']}</td>
            <td>{$row['Estacionamiento']}</td>
            <td style='text-align: right;'>" . number_format($row['TPBoleto'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['TOBoleto'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['CBoletos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['boletos_perdidos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['boletoNoUtil'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($boletajeTotal, 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['BEmitidos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($diferencia, 2, '.', ',') . "</td>
        </tr>";
}
echo "</table>";

echo "<table border='1'>";
echo "<tr>
        <th>Fecha</th>
        <th>Estacionamiento</th>
        <th>Con Sello</th>
        <th>Sin Sello</th>
        <th>Exentos</th>
        <th>Perdidos</th>
        <th>Otros</th>
        <th>Penciones</th>
        <th>Apps</th>
        <th>Total</th>
        <th>Deposito</th>
        <th>Diferencia</th>
    </tr>";

foreach ($data as $row) {
    $importeTotal = ((int)($row['TOImporte'] ?? 0))
                    + ((int)($row['TPImporte'] ?? 0))
                    + ((int)($row['RImporte'] ?? 0))
                    + ((int)($row['CImporte'] ?? 0))
                    + ((int)($row['TImporte'] ?? 0))
                    + ((int)($row['Otros'] ?? 0))
                    + ((int)($row['importe_boletos_perdidos'] ?? 0))
                    + ((int)($row['penciones_importe'] ?? 0));
                    
    $importeAppTotal = $importeTotal + ((int)($row['apps'] ?? 0));
    $diferenciaImporte = ((int)($row['Deposito'] ?? 0)) - $importeAppTotal;
    echo "<tr>
            <td>{$row['Fecha']}</td>
            <td>{$row['Estacionamiento']}</td>
            <td style='text-align: right;'>" . number_format($row['TPImporte'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['TOImporte'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['CImporte'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['importe_boletos_perdidos'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['Otros'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['penciones_importe'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['apps'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($importeTotal, 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($row['Deposito'], 2, '.', ',') . "</td>
            <td style='text-align: right;'>" . number_format($diferenciaImporte, 2, '.', ',') . "</td>
        </tr>";
}
echo "</table>";

exit;
?>
