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

    public function insertEstado($data) {
        $sql = "INSERT INTO estado (estado, descripcion) VALUES (:estado, :descripcion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':descripcion', $data['descripcion']);

        return $stmt->execute();
    }

    public function getEstados() {
        $sql = "SELECT id, estado, descripcion FROM estado";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'estado' => $_POST['estado'],
        'descripcion' => $_POST['descripcion'],
    ];

    $db = new Database();
    if ($db->insertEstado($data)) {
        $mensaje = "Estado registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el estado.";
    }
}

$db = new Database();
$estados = $db->getEstados(); // Obtener los estados registrados
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estados</title>
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
    <h2>Gestión de Estados</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="estado">Estado</label>
        <input type="text" name="estado" id="estado" required>

        <label for="descripcion">Descripción</label>
        <input type="text" name="descripcion" id="descripcion" required>

        <button type="submit">Registrar Estado</button>
    </form>

    <!-- Sección para mostrar la tabla de estados -->
    <h2>Estados Registrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Estado</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($estados): ?>
                <?php foreach ($estados as $estado): ?>
                    <tr>
                        <td><?php echo $estado['id']; ?></td>
                        <td><?php echo $estado['estado']; ?></td>
                        <td><?php echo $estado['descripcion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No hay estados registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
