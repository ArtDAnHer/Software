<?php
// Configuración de la conexión a la base de datos
$servername = "192.168.1.17";
$username = "celular";
$password = "Coemsa.2024";
$dbname = "boletaje";

try {
    // Conexión a MySQL usando PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verificar si hay datos enviados desde el formulario
        if (isset($_POST['entries']) && is_array($_POST['entries'])) {
            $entries = $_POST['entries'];

            // Preparar la consulta SQL
            $stmt = $conn->prepare("INSERT INTO boletos (Fecha, Turno, TOBoleto, TOImporte, TPBoleto, TPImporte, RBoletos, RImporte, CBoletos, CImporte, TBoleto, TImporte, BEmitidos, BControlados, Otros, Deposito, Estacionamiento)
        VALUES (:Fecha, :Turno, :TOBoleto, :TOImporte, :TPBoleto, :TPImporte, :RBoletos, :RImporte, :CBoletos, :CImporte, :TBoleto, :TImporte, :BEmitidos, :BControlados, :Otros, :Deposito, :Estacionamiento)");

            // Procesar cada entrada
            foreach ($entries as $entry) {
                // Ejecutar la consulta para cada fila del formulario
                $stmt->execute([
                    ':Estacionamiento' => $entry['Estacionamiento'],
                    ':Turno' => $entry['Turno'],
                    ':TOBoleto' => $entry['TOBoleto'],
                    ':TOImporte' => $entry['TOImporte'],
                    ':TPBoleto' => $entry['TPBoleto'],
                    ':TPImporte' => $entry['TPImporte'],
                    ':RBoletos' => $entry['RBoletos'],
                    ':RImporte' => $entry['RImporte'],
                    ':CBoletos' => $entry['CBoletos'],
                    ':CImporte' => $entry['CImporte'],
                    ':TBoleto' => $entry['TBoleto'],
                    ':TImporte' => $entry['TImporte'],
                    ':BEmitidos' => $entry['BEmitidos'],
                    ':BControlados' => $entry['BControlados'],
                    ':Otros' => $entry['Otros'],
                    ':Deposito' => $entry['Deposito'],
                    ':Fecha' => $entry['Fecha']
                ]);
            }

            echo "Entradas guardadas correctamente.";
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Entradas Múltiples</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin: 20px 0;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .form-container {
            flex: 1;
            width: 100%;
            overflow-y: auto;
            padding-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .buttons {
            margin: 20px 0;
            text-align: center;
        }
        .add-row-btn, .delete-btn, button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 10px;
        }
        .add-row-btn:hover, .delete-btn:hover, button[type="submit"]:hover {
            background-color: #45a049;
        }
        .delete-btn {
            background-color: #f44336;
        }
    </style>
    <script>
        function confirmSubmission(event) {
            var confirmAction = confirm("¿Está seguro de que desea enviar estos datos?");
            if (!confirmAction) {
                event.preventDefault();
            }
        }

        function addEntry() {
            var table = document.getElementById('entries').getElementsByTagName('tbody')[0];
            var rowCount = table.rows.length;

            var row = table.insertRow();
            row.innerHTML = `
                <td><select name="entries[${rowCount}][Estacionamiento]" required>
                    <option value="">Seleccione un estacionamiento</option>
                    <option value="Santin">Santin</option>
                    <option value="Lomas Reforma">Lomas Reforma</option>
                    <option value="City Center">City Center</option>
                </select></td>
                <td><input type="text" name="entries[${rowCount}][Turno]" required></td>
                <td><input type="number" name="entries[${rowCount}][TOBoleto]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][TOImporte]" required></td>
                <td><input type="number" name="entries[${rowCount}][TPBoleto]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][TPImporte]" required></td>
                <td><input type="number" name="entries[${rowCount}][RBoletos]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][RImporte]" required></td>
                <td><input type="number" name="entries[${rowCount}][CBoletos]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][CImporte]" required></td>
                <td><input type="number" name="entries[${rowCount}][TBoleto]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][TImporte]" required></td>
                <td><input type="number" name="entries[${rowCount}][BEmitidos]" required></td>
                <td><input type="number" name="entries[${rowCount}][BControlados]" required></td>
                <td><input type="number" name="entries[${rowCount}][Otros]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][Deposito]" required></td>
                <td><input type="date" name="entries[${rowCount}][Fecha]" required></td>
                <td><button type="button" class="delete-btn" onclick="deleteRow(this)">Eliminar</button></td>
            `;
        }

        function deleteRow(button) {
            var row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
</head>
<body>
    <h2>Crear Nuevas Entradas</h2>
    <hr>
    <form id="entryForm" method="POST" action="" onsubmit="confirmSubmission(event)">
        <div class="form-container">
            <table id="entries">
                <thead>
                    <tr>
                        <th>Estacionamiento</th>
                        <th>Turno</th>
                        <th>Tarifa ordinaria boleto</th>
                        <th>Tarifa ordinaria importe</th>
                        <th>Tarifa preferencial boleto</th>
                        <th>Tarifa preferencial importe</th>
                        <th>Recobros boletos</th>
                        <th>Recobros importe</th>
                        <th>Cortesia boletos</th>
                        <th>Cortesia importe</th>
                        <th>Tolerancia boleto</th>
                        <th>Tolerancia importe</th>
                        <th>Boletos emitidos</th>
                        <th>Boletos controlados</th>
                        <th>Otros</th>
                        <th>Deposito</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Las filas de entradas se agregarán aquí dinámicamente -->
                </tbody>
            </table>
        </div>
        <div class="buttons">
            <button type="button" class="add-row-btn" onclick="addEntry()">Añadir nueva fila</button>
            <button type="submit">Enviar Datos</button>
        </div>
    </form>
</body>
</html>
