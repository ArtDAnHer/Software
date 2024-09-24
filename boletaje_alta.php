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

    public function insertEntry($data) {
        $fechaAlta = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO boletos (Fecha, Turno, TOBoleto, TOImporte, TPBoleto, TPImporte, RBoletos, RImporte, CBoletos, CImporte, TBoleto, TImporte, BEmitidos, BControlados, Otros, Deposito, Estacionamiento)
        VALUES (:Fecha, :Turno, :TOBoleto, :TOImporte, :TPBoleto, :TPImporte, :RBoletos, :RImporte, :CBoletos, :CImporte, :TBoleto, :TImporte, :BEmitidos, :BControlados, :Otros, :Deposito, :Estacionamiento)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':Fecha', $fechaAlta);
        $stmt->bindParam(':Turno', $data['Turno']);
        $stmt->bindParam(':TOBoleto', $data['TOBoleto']);
        $stmt->bindParam(':TOImporte', $data['TOImporte']);
        $stmt->bindParam(':TPBoleto', $data['TPBoleto']);
        $stmt->bindParam(':TPImporte', $data['TPImporte']);
        $stmt->bindParam(':RBoletos', $data['RBoletos']);
        $stmt->bindParam(':RImporte', $data['RImporte']);
        $stmt->bindParam(':CBoletos', $data['CBoletos']);
        $stmt->bindParam(':CImporte', $data['CImporte']);
        $stmt->bindParam(':TBoleto', $data['TBoleto']);
        $stmt->bindParam(':TImporte', $data['TImporte']);
        $stmt->bindParam(':BEmitidos', $data['BEmitidos']);
        $stmt->bindParam(':BControlados', $data['BControlados']);
        $stmt->bindParam(':Otros', $data['Otros']);
        $stmt->bindParam(':Deposito', $data['Deposito']);
        $stmt->bindParam(':Estacionamiento', $data['Estacionamiento']);
        
        if ($stmt->execute()) {
            echo "Entrada creada exitosamente.";
        } else {
            echo "Error al crear la entrada.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'Turno' => $_POST['Turno'],
        'TOBoleto' => $_POST['TOBoleto'],
        'TOImporte' => $_POST['TOImporte'],
        'TPBoleto' => $_POST['TPBoleto'],
        'TPImporte' => $_POST['TPImporte'],
        'RBoletos' => $_POST['RBoletos'],
        'RImporte' => $_POST['RImporte'],
        'CBoletos' => $_POST['CBoletos'],
        'CImporte' => $_POST['CImporte'],
        'TBoleto' => $_POST['TBoleto'], // Nuevo campo
        'TImporte' => $_POST['TImporte'], // Nuevo campo
        'BEmitidos' => $_POST['BEmitidos'],
        'BControlados' => $_POST['BControlados'],
        'Otros' => $_POST['Otros'],
        'Deposito' => $_POST['Deposito'],
        'Estacionamiento' => $_POST['Estacionamiento'],
    ];

    $db = new Database();
    $db->insertEntry($data);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Entrada</title>
    <script>
        function confirmSubmission(event) {
            var confirmAction = confirm("¿Está seguro de que desea enviar estos datos?");
            if (!confirmAction) {
                event.preventDefault();
            }
        }
    </script>
    <link rel="stylesheet" href="style.css"> <!-- Vincula tu archivo CSS -->
    <style>
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

    <h2>Crear Nueva Entrada</h2>
    <hr>
    <form id="entryForm" method="POST" action="" onsubmit="confirmSubmission(event)">
        Estacionamiento: 
        <select name="Estacionamiento" required>
            <option value="">Seleccione un estacionamiento</option>
            <option value="Santin">Santin</option>
            <option value="Lomas Reforma">Lomas Reforma</option>
            <option value="City Center">City Center</option>
        </select><br><br>
        Turno: <input type="text" name="Turno" required><br><br>
        Tarifa ordinaria boleto: <input type="number" name="TOBoleto" required><br><br>
        Tarifa ordinaria importe: <input type="number" step="0.01" name="TOImporte" required><br><br>
        Tarifa preferencial boleto: <input type="number" name="TPBoleto" required><br><br>
        Tarifa preferencial importe: <input type="number" step="0.01" name="TPImporte" required><br><br>
        Recobros boletos: <input type="number" name="RBoletos" required><br><br>
        Recobros importe: <input type="number" step="0.01" name="RImporte" required><br><br>
        Cortesia boletos: <input type="number" name="CBoletos" required><br><br>
        Cortesia importe: <input type="number" step="0.01" name="CImporte" required><br><br>
        Tolerancia boleto: <input type="number" name="TBoleto" required><br><br>
        Tolerancia importe: <input type="number" step="0.01" name="TImporte" required><br><br>
        Boletos emitidos: <input type="number" name="BEmitidos" required><br><br>
        Boletos controlados: <input type="number" name="BControlados" required><br><br>
        Otros: <input type="number" name="Otros" required><br><br>
        Deposito: <input type="number" step="0.01" name="Deposito" required><br><br>
        <button type="submit">Crear Entrada</button>
    </form>
</body>
</html>
