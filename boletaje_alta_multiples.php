<?php
// Configuración de la conexión a la base de datos
$servername = "192.168.1.98";
$username = "celular";
$password = "Coemsa.2024";
$dbname = "boletaje";

try {
    // Conexión a MySQL usando PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener estacionamientos disponibles desde la base de datos
    $estacionamientos = [];
    $query = $conn->query("SELECT DISTINCT estacionamiento FROM estacionamiento");
    $estacionamientos = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['entries']) && is_array($_POST['entries'])) {
            $entries = $_POST['entries'];

            // Preparar la consulta SQL
            $stmt = $conn->prepare("INSERT INTO boletos (
                Fecha, Turno, TOBoleto, TOImporte, TPBoleto, TPImporte, RBoletos, RImporte, CBoletos, CImporte, 
                TBoleto, TImporte, BEmitidos, BControlados, Otros, Deposito, Estacionamiento, boletos_perdidos, importe_boletos_perdidos, boletoNoUtil, apps, penciones_cantidad, penciones_importe
            ) VALUES (
                :Fecha, :Turno, :TOBoleto, :TOImporte, :TPBoleto, :TPImporte, :RBoletos, :RImporte, :CBoletos, :CImporte, 
                :TBoleto, :TImporte, :BEmitidos, :BControlados, :Otros, :Deposito, :Estacionamiento, :BoletoPerdido, :ImporteBoletoPerdido, :boletoNoUtil, :apps, :penciones_cantidad, :penciones_importe
            )");

            foreach ($entries as $entry) {
                $entry = array_map('trim', $entry);
                // Ejecutar la consulta con los parámetros correctos
                $stmt->execute([
                    ':Fecha' => $entry['Fecha'],
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
                    ':Estacionamiento' => $entry['Estacionamiento'],
                    ':BoletoPerdido' => $entry['BoletoPerdido'],
                    ':ImporteBoletoPerdido' => $entry['ImporteBoletoPerdido'],
                    ':boletoNoUtil' => $entry['boletoNoUtil'],
                    ':apps' => $entry['apps'],
                    ':penciones_cantidad' => $entry['penciones_cantidad'],
                    ':penciones_importe' => $entry['penciones_importe']
                ]);
            }
            echo "<script>alert('Entradas guardadas correctamente.');</script>";
        }
    }
} catch (PDOException $e) {
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
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #eaeaea; }
        .form-container { overflow-y: auto; padding-bottom: 20px; }
        .buttons { text-align: center; margin-top: 20px; }
        button { margin: 5px; padding: 10px 20px; cursor: pointer; }
    </style>
    <script>
        function confirmSubmission(event) {
            if (!confirm("¿Está seguro de que desea enviar estos datos?")) {
                event.preventDefault();
            }
        }
        function addEntry() {
            const tableBody = document.querySelector('#entries tbody');
            const rowCount = tableBody.rows.length;
            const row = tableBody.insertRow();

            row.innerHTML = ` 
                <td>
                    <select name="entries[${rowCount}][Estacionamiento]" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($estacionamientos as $est): ?>
                            <option value="<?= htmlspecialchars($est['estacionamiento']) ?>">
                                <?= htmlspecialchars($est['estacionamiento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
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
                <td><input type="number" name="entries[${rowCount}][BoletoPerdido]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][ImporteBoletoPerdido]" required></td>
                <td><input type="number" name="entries[${rowCount}][BEmitidos]" required></td>
                <td><input type="number" name="entries[${rowCount}][BControlados]" required></td>
                <td><input type="number" name="entries[${rowCount}][Otros]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][Deposito]" required></td>
                <td><input type="date" name="entries[${rowCount}][Fecha]" required value="<?= date('Y-m-d') ?>"></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][boletoNoUtil]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][apps]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][penciones_cantidad]" required></td>
                <td><input type="number" step="0.01" name="entries[${rowCount}][penciones_importe]" required></td>
                <td><button type="button" onclick="deleteRow(this)">Eliminar</button></td>
            `;
        }
        function deleteRow(button) {
            const row = button.parentNode.parentNode;
            row.remove();
        }
    </script>
</head>
<body>
    <h2>Crear Nuevas Entradas</h2>
    
     <h2>Centro Republica del salvador</h2><br>
                ordinario = autos<br>
                preferencial = camionetas<br>
                recobros = motos<br>
                tolerancia = hotel<br>

    <form method="POST" onsubmit="confirmSubmission(event)">
        <div class="form-container">
            <table id="entries">
                <thead>
                    <tr>
                        <th>Estacionamiento</th>
                        <th>Turno</th>
                        <th>Tiempo iOrdinarioempo  rdinario Boleto</th>
                        <th>Tiempo iOrdinarioempo  rdinario Importe</th>
                        <th>Tiempo iempo Preferencial Boleto</th>
                        <th>Tiempo Preferencial Importe</th>
                        <th>Recobro Boletos</th>
                        <th>Recobro Importe</th>
                        <th>Cortecia Boletos</th>
                        <th>Cortecia Importe</th>
                        <th>Tolerancia Boleto</th>
                        <th>Tolerancia Importe</th>
                        <th>Boleto Perdido</th>
                        <th>Importe Boleto Perdido</th>
                        <th>Boletos Emitidos</th>
                        <th>BoletosControlados</th>
                        <th>Otros</th>
                        <th>Deposito</th>
                        <th>Fecha</th>
                        <th>BoletoNoUtil</th>
                        <th>App</th>
                        <th>Cantidad de penciones</th>
                        <th>Importe de penciones</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="buttons">
            <button type="button" onclick="addEntry()">Añadir Fila</button>
            <button type="submit">Guardar</button>
        </div>
    </form>
</body>
</html>
