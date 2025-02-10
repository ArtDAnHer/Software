<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $sql = "SELECT DISTINCT Estacionamiento FROM boletos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Crear una instancia de la base de datos
$db = new Database();

$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-t');
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : '';
$data = $db->getEntries($startDate, $endDate, $estacionamiento);
$estacionamientos = $db->getEstacionamientos();

// Exportar los datos a Excel si se envía la solicitud
if (isset($_POST['export'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados
    $sheet->setCellValue('A1', 'Fecha');
    $sheet->setCellValue('B1', 'TOBoleto');
    $sheet->setCellValue('C1', 'TOImporte');
    $sheet->setCellValue('D1', 'TPBoleto');
    $sheet->setCellValue('E1', 'TPImporte');
    $sheet->setCellValue('F1', 'RBoletos');
    $sheet->setCellValue('G1', 'RImporte');
    $sheet->setCellValue('H1', 'CBoletos');
    $sheet->setCellValue('I1', 'CImporte');
    $sheet->setCellValue('J1', 'BEmitidos');
    $sheet->setCellValue('K1', 'BControlados');
    $sheet->setCellValue('L1', 'Otros');
    $sheet->setCellValue('M1', 'Deposito');
    $sheet->setCellValue('N1', 'Estacionamiento');

    // Datos
    $fila = 2;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $fila, $row['Fecha']);
        $sheet->setCellValue('B' . $fila, $row['TOBoleto']);
        $sheet->setCellValue('C' . $fila, $row['TOImporte']);
        $sheet->setCellValue('D' . $fila, $row['TPBoleto']);
        $sheet->setCellValue('E' . $fila, $row['TPImporte']);
        $sheet->setCellValue('F' . $fila, $row['RBoletos']);
        $sheet->setCellValue('G' . $fila, $row['RImporte']);
        $sheet->setCellValue('H' . $fila, $row['CBoletos']);
        $sheet->setCellValue('I' . $fila, $row['CImporte']);
        $sheet->setCellValue('J' . $fila, $row['BEmitidos']);
        $sheet->setCellValue('K' . $fila, $row['BControlados']);
        $sheet->setCellValue('L' . $fila, $row['Otros']);
        $sheet->setCellValue('M' . $fila, $row['Deposito']);
        $sheet->setCellValue('N' . $fila, $row['Estacionamiento']);
        $fila++;
    }

    // Exportar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $fileName = 'reporte_boletaje.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Entradas</title>
    <style>
        /* Tus estilos aquí */
    </style>
</head>
<body>

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

    <h2>Dashboard de Entradas por Rango de Fechas y Estacionamiento</h2>
    <hr>

    <form method="POST" action="">
        <label for="startDate">Seleccione la fecha de inicio:</label>
        <input type="date" name="startDate" id="startDate" value="<?= htmlspecialchars($startDate) ?>" required>

        <label for="endDate">Seleccione la fecha de fin:</label>
        <input type="date" name="endDate" id="endDate" value="<?= htmlspecialchars($endDate) ?>" required>

        <label for="estacionamiento">Seleccione un estacionamiento:</label>
        <select name="estacionamiento" id="estacionamiento">
            <option value="">Todos</option>
            <?php foreach ($estacionamientos as $est): ?>
                <option value="<?= htmlspecialchars($est['Estacionamiento']) ?>" <?= $estacionamiento === $est['Estacionamiento'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($est['Estacionamiento']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
        <button type="submit" name="export">Exportar a Excel</button>
        <H3 style="color: black"><a href='boletaje.php'>Regresar</a></H3>
    </form>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>TOBoleto</th>
                <th>TOImporte</th>
                <th>TPBoleto</th>
                <th>TPImporte</th>
                <th>RBoletos</th>
                <th>RImporte</th>
                <th>CBoletos</th>
                <th>CImporte</th>
                <th>Boletos Emitidos</th>
                <th>Boletos Controlados</th>
                <th>Otros</th>
                <th>Deposito</th>
                <th>Estacionamiento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Fecha']) ?></td>
                        <td><?= number_format($row['TOBoleto'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($row['TOImporte'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['TPBoleto'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($row['TPImporte'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['RBoletos'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($row['RImporte'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['CBoletos'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($row['CImporte'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['BEmitidos'], 0, ',', '.') ?></td>
                        <td><?= number_format($row['BControlados'], 0, ',', '.') ?></td>
                        <td><?= number_format($row['Otros'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($row['Deposito'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14">No se encontraron datos en este rango de fechas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
