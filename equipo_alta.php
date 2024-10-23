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

    public function insertEquipo($data) {
        $sql = "INSERT INTO equipos (tipo, lugar, estado, equipo, ubicacion) VALUES (:tipo, :lugar, :estado, :equipo, :ubicacion)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':lugar', $data['lugar']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':equipo', $data['equipo']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);

        return $stmt->execute();
    }

    public function getEquipos() {
        // Modificación para traer los nombres de las tablas relacionadas en lugar de los IDs
        $sql = "SELECT * FROM equipos e ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipos() {
        $sql = "SELECT id, nombre FROM tipo_equipo";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstados() {
        $sql = "SELECT id, estado FROM estado";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLugares() {
        $sql = "SELECT id, nombre FROM estacionamiento";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'tipo' => $_POST['tipo'],
        'lugar' => $_POST['lugar'],
        'estado' => $_POST['estado'],
        'equipo' => $_POST['equipo'],
    ];

    $db = new Database();
    if ($db->insertEquipo($data)) {
        $mensaje = "Equipo registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el equipo.";
    }
}

$db = new Database();
$equipos = $db->getEquipos(); // Obtener los equipos registrados
$tipos = $db->getTipos(); // Obtener los tipos de equipo
$estados = $db->getEstados(); // Obtener los estados
$lugares = $db->getLugares(); // Obtener los lugares
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Equipos</title>
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

        form input[type="text"], form select {
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
    <h2>Alta de Equipos</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="tipo">Tipo de Equipo</label>
        <select name="tipo" id="tipo" required>
            <option value="">Seleccione un tipo</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo $tipo['nombre']; ?>"><?php echo $tipo['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lugar">Lugar</label>
        <select name="lugar" id="lugar" required>
            <option value="">Seleccione un lugar</option>
            <?php foreach ($lugares as $lugar): ?>
                <option value="<?php echo $lugar['nombre']; ?>"><?php echo $lugar['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="estado">Estado</label>
        <select name="estado" id="estado" required>
            <option value="">Seleccione un estado</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado['estado']; ?>"><?php echo $estado['estado']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="ubicacion">Ubicacion</label>
        <input type="text" name="ubicacion" id="ubicacion" required>

        <label for="equipo">Nombre del Equipo</label>
        <input type="text" name="equipo" id="equipo" required>

        <button type="submit">Registrar Equipo</button>
    </form>

    <h2>Equipos Registrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Lugar</th>
                <th>Estado</th>
                <th>Equipo</th>
                <th>Ubicacion</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($equipos): ?>
                <?php foreach ($equipos as $equipo): ?>
                    <tr>
                        <td><?php echo $equipo['id']; ?></td>
                        <td><?php echo $equipo['tipo']; ?></td> <!-- Mostrar el nombre del tipo -->
                        <td><?php echo $equipo['lugar']; ?></td> <!-- Mostrar el nombre del lugar -->
                        <td><?php echo $equipo['estado']; ?></td> <!-- Mostrar el estado -->
                        <td><?php echo $equipo['equipo']; ?></td>
                        <td><?php echo $equipo['ubicacion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay equipos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
