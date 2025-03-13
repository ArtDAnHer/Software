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
            echo "Error de conexi��n: " . $e->getMessage();
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
        .negative {
            color: red;
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

    <form action="export_excel.php" method="POST">
        
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
    
        <button type="submit">Exportar</button>
    </form>

<h3>Centro Republica del salvador</h3><br>
                ordinario = autos<br>
                preferencial = camionetas<br>
                recobros = motos<br>
                tolerancia = hotel<br>
<br>
<h2>Importe</h2>
<br>
<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Estacionamiento</th>
            <th class="text-right">Tarifa Ordinaria Importe</th>
            <th class="text-right">Tarifa Preferencial Importe</th>
            <th class="text-right">Recobro Importe</th>
            <th class="text-right">Cortesia Importe</th>
            <th class="text-right">Boletos Perdidos Importe</th>
            <th class="text-right">Otros Importes</th>
            <th class="text-right">App</th>
            <th class="text-right">Importe de penciones</th>
            <th class="text-right">Total</th>
            <th class="text-right">Deposito</th>
            <th class="text-right">Diferencia</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($data)): ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= date("d/m/Y", strtotime($row['Fecha'])) ?></td>
                    <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                    <td class="text-right"><?= number_format($row['TOImporte'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['TPImporte'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['RImporte'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['CImporte'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['importe_boletos_perdidos'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['Otros'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['apps'], 2, '.', ',') ?></td>
                    <td class="text-right"><?= number_format($row['penciones_importe'], 2, '.', ',') ?></td>
                    <td class="text-right">
                        <?= number_format($row['TOImporte'] + $row['TPImporte'] + $row['RImporte'] + $row['CImporte'] + $row['importe_boletos_perdidos'] + $row['apps'] + $row['penciones_importe'], 2, '.', ',') ?>
                    </td>
                    <td class="text-right"><?= number_format($row['Deposito'], 2, '.', ',') ?></td>
                    <td class="text-right">
                        <?= number_format($row['Deposito'] - $row['TOImporte'] - $row['TPImporte'] - $row['RImporte'] - $row['CImporte'] - $row['importe_boletos_perdidos'] - $row['apps'] - $row['penciones_importe'], 2, '.', ',') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="12">No hay datos disponibles para el rango seleccionado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>
<h2>Boletos</h2>
<br>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Estacionamiento</th>
            <th class="text-right">Tarifa Ordinaria Boletos</th>
            <th class="text-right">Tarifa Preferencial Boletos</th>
            <th class="text-right">Recobro Boletos</th>
            <th class="text-right">Corte Boletos</th>
            <th class="text-right">Boletos Perdidos</th>
            <th class="text-right">Boletos Emitidos</th>
            <th class="text-right">Boletos Controlados</th>
            <th class="text-right">Penciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?= date("d/m/Y", strtotime($row['Fecha'])) ?></td>
                <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                <td class="text-right"><?= number_format($row['TOBoleto'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['TPBoleto'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['RBoletos'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['CBoletos'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['boletos_perdidos'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['BEmitidos'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['BControlados'], 2, '.', ',') ?></td>
                <td class="text-right"><?= number_format($row['penciones_importe'], 2, '.', ',') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .text-right {
        text-align: right;
    }
</style>

</body>
</html>