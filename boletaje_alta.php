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

    if (empty($estacionamientos)) {
        echo "<script>alert('No hay estacionamientos disponibles.');</script>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['entries']) && is_array($_POST['entries']) && !empty($_POST['entries'])) {
            $entries = $_POST['entries'];

            // Preparar la consulta SQL
            $stmt = $conn->prepare("INSERT INTO boletos (
                Fecha, Turno, TOBoleto, TOImporte, TPBoleto, TPImporte, RBoletos, RImporte, CBoletos, CImporte, 
                TBoleto, TImporte, BEmitidos, BControlados, Otros, Deposito, Estacionamiento, boletos_perdidos, importe_boletos_perdidos, boletoNoUtil, apps, penciones_cantidad, penciones_importe
            ) VALUES (
                :Fecha, :Turno, :TOBoleto, :TOImporte, :TPBoleto, :TPImporte, :RBoletos, :RImporte, :CBoletos, :CImporte, 
                :TBoleto, :TImporte, :BEmitidos, :BControlados, :Otros, :Deposito, :Estacionamiento, :BoletoPerdido, :ImporteBoletoPerdido, :boletoNoUtil, :apps, :penciones_cantidad, :penciones_importe
            )");

            $count = 0;

            foreach ($entries as $entry) {
                if (!empty($entry['Estacionamiento']) && !empty($entry['Fecha'])) {
                    $entry = array_map('trim', $entry);
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
                    $count++;
                }
            }

            if ($count > 0) {
                echo "<script>alert('Entradas guardadas correctamente.');</script>";
            } else {
                echo "<script>alert('No se guardaron entradas. Verifique los datos.');</script>";
            }
        } else {
            echo "<script>alert('No hay datos para guardar.');</script>";
        }
    }
} catch (PDOException $e) {
    echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
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
        form { width: 50%; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>

<h2>Crear Nueva Entrada</h2>

<form method="POST">
    <div class="form-group">
        <label for="Estacionamiento">Estacionamiento</label>
        <select name="entries[0][Estacionamiento]" required>
            <option value="">Seleccione</option>
            <?php foreach ($estacionamientos as $est): ?>
                <option value="<?= htmlspecialchars($est['estacionamiento']) ?>">
                    <?= htmlspecialchars($est['estacionamiento']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="Fecha">Fecha</label>
        <input type="date" name="entries[0][Fecha]" required value="<?= date('Y-m-d') ?>">
    </div>

    <div class="form-group">
        <label for="Turno">Turno</label>
        <input type="text" name="entries[0][Turno]" required>
    </div>

    <div class="form-group">
        <label for="TOBoleto">Tiempo Ordinario Boleto</label>
        <input type="number" name="entries[0][TOBoleto]" required>
    </div>

    <div class="form-group">
        <label for="TOImporte">Tiempo Ordinario Importe</label>
        <input type="number" step="0.01" name="entries[0][TOImporte]" required>
    </div>

    <div class="buttons">
        <button type="submit">Guardar</button>
    </div>
</form>

</body>
</html>

