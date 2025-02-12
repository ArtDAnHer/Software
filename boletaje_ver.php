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

        if ($startDate && $endDate) {
            $sql .= " WHERE Fecha BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }

        if ($estacionamiento) {
            if ($startDate && $endDate) {
                $sql .= " AND Estacionamiento = :estacionamiento";
            } else {
                $sql .= " WHERE Estacionamiento = :estacionamiento";
            }
            $params[':estacionamiento'] = $estacionamiento;
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
$data = $db->getEntries($startDate, $endDate, $estacionamiento);
$estacionamientos = $db->getEstacionamientos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Entradas</title>
    <style>
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
        .text-right {
            text-align: right;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2>Dashboard de Entradas por Rango de Fechas y Estacionamiento</h2>
    <hr>

    <form method="POST" action="">
        <label for="startDate">Fecha de inicio:</label>
        <input type="date" name="startDate" id="startDate" value="<?= htmlspecialchars($startDate) ?>" required>

        <label for="endDate">Fecha de fin:</label>
        <input type="date" name="endDate" id="endDate" value="<?= htmlspecialchars($endDate) ?>" required>

        <label for="estacionamiento">Estacionamiento:</label>
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

    <form method="POST" action="exportar_excel.php">
        <input type="hidden" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
        <input type="hidden" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
        <input type="hidden" name="estacionamiento" value="<?= htmlspecialchars($estacionamiento) ?>">
        <button type="submit">Exportar a Excel</button>
    </form>

    <table>
        <thead>
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
        </thead>
        <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= date("d/m/Y", strtotime($row['Fecha'])) ?></td>
                        <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                        <td class="text-right"><?= number_format($row['TOBoleto'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['TOImporte'], 2, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['TPBoleto'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['TPImporte'], 2, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['RBoletos'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['RImporte'], 2, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['CBoletos'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['CImporte'], 2, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['boletos_perdidos'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['importe_boletos_perdidos'], 2, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['BEmitidos'], 0, '.', ',') ?></td>
                        <td class="text-right"><?= number_format($row['BControlados'], 0, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['Otros'], 2, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['TOImporte'] + $row['TPImporte'] + $row['RImporte'] + $row['CImporte'] + $row['Otros'], 2, '.', ',') ?></td>
                        <td class="text-right">$<?= number_format($row['Deposito'], 2, '.', ',') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="17">No hay datos disponibles para el rango seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
