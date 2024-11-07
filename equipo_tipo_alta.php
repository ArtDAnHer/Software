<?php
class Database {
    private $db = "insidencias";
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

    // Método para insertar un nuevo equipo
    public function insertTipoEquipo($data) {
        $sql = "INSERT INTO tipo_equipo (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);

        return $stmt->execute();
    }

    // Método para obtener los equipos registrados
    public function getTipoEquipos() {
        $sql = "SELECT * FROM tipo_equipo";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inicializa la variable para el mensaje de éxito o error
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recolectar los datos enviados por el formulario
    $data = [
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion']
    ];

    $db = new Database();
    // Insertar el nuevo equipo y mostrar mensaje de éxito o error
    if ($db->insertTipoEquipo($data)) {
        $mensaje = "Equipo registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el equipo.";
    }
}

// Obtener todos los equipos ya registrados para mostrarlos en la tabla
$db = new Database();
$tipo_equipos = $db->getTipoEquipos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Tipo de Equipo</title>
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

        form input[type="text"],
        form textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        form textarea {
            height: 100px;
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
    <h2>Alta de Tipo de Equipo</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre del Equipo</label>
        <input type="text" name="nombre" id="nombre" required><br>

        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" required></textarea><br>

        <button type="submit">Registrar Equipo</button>
    </form>

    <!-- Sección para mostrar la tabla de equipos registrados -->
    <h2>Equipos Registrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tipo_equipos): ?>
                <?php foreach ($tipo_equipos as $equipo): ?>
                    <tr>
                        <td><?php echo $equipo['id']; ?></td>
                        <td><?php echo $equipo['nombre']; ?></td>
                        <td><?php echo $equipo['descripcion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No hay equipos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
