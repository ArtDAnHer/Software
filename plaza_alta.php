<?php
class Database {
    private $db = "reportes_fallas";
    private $ip = "localhost";
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

    public function insertPlaza($data) {
        $sql = "INSERT INTO estacionamiento (nombre, direccion) VALUES (:nombre, :direccion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':direccion', $data['direccion']);

        return $stmt->execute();
    }

    public function getPlazas() {
        $sql = "SELECT * FROM estacionamiento";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nombre' => $_POST['estacionamiento'],
        'direccion' => $_POST['direccion']
    ];

    $db = new Database();
    if ($db->insertPlaza($data)) {
        $mensaje = "Estacionamiento registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el estacionamiento.";
    }
}

$db = new Database();
$plazas = $db->getPlazas(); // Obtener las plazas registradas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Plaza</title>
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

        .mensaje {
            text-align: center;
            color: green;
            margin: 10px 0;
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

        form input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
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

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <h2>Alta de Plaza</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="estacionamiento">Nombre del Estacionamiento</label>
        <input type="text" name="estacionamiento" id="estacionamiento" required><br>

        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion" required><br>

        <button type="submit">Registrar Plaza</button>
    </form>

    <!-- Sección para mostrar la tabla de plazas -->
    <h2>Plazas Registradas</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($plazas): ?>
                <?php foreach ($plazas as $plaza): ?>
                    <tr>
                        <td><?php echo $plaza['nombre']; ?></td>
                        <td><?php echo $plaza['direccion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No hay plazas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
