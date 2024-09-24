<?php
class Database {
    private $db = "Boletaje";
    private $ip = "192.168.1.17";
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

    // Método para obtener las entradas filtradas por rango de fechas y estacionamiento
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

    // Método para obtener todos los estacionamientos distintos
    public function getEstacionamientos() {
        $sql = "SELECT DISTINCT Estacionamiento FROM boletos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Crear una instancia de la base de datos
$db = new Database();

// Obtener los parámetros del formulario
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01');
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-t');
$estacionamiento = isset($_POST['estacionamiento']) ? $_POST['estacionamiento'] : '';

// Obtener los datos filtrados o todos los datos
$data = $db->getEntries($startDate, $endDate, $estacionamiento);

// Obtener todos los estacionamientos distintos
$estacionamientos = $db->getEstacionamientos();

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
</head>
<body>
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
        <a href="excel.php" >Descargar excel</a>
    </form>

    <table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tarifa ordinaria boleto</th>
            <th>Tarifa ordinaria importe</th>
            <th>Tarifa preferencial boleto</th>
            <th>Tarifa preferencial importe</th>
            <th>Recobro boletos</th>
            <th>Recobro importe</th>
            <th>CBoletos</th>
            <th>CImporte</th>
            <th>Boletos emitidos</th>
            <th>Boletos controlados</th>
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
                    <td class="text-right"><?= number_format($row['TOBoleto'], 0, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($row['TOImporte'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['TPBoleto'], 0, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($row['TPImporte'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['RBoletos'], 0, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($row['RImporte'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['CBoletos'], 0, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($row['CImporte'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['BEmitidos'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['BControlados'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['Otros'], 0, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($row['Deposito'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['Estacionamiento']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="14">No hay datos disponibles para el rango seleccionado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
    .text-right {
        text-align: right;
    }
</style>

</body>
</html>
