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

    public function insertTec($data) {
        $sql = "INSERT INTO Tec (tecnico, plaza) VALUES (:tecnico, :plaza)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':tecnico', $data['tecnico']); // ID del técnico
        $stmt->bindParam(':plaza', $data['plaza']); // ID de la plaza

        return $stmt->execute();
    }

    public function getTecnicosRegistrados() {
        $sql = "SELECT `Tec`.`id`, `Tec`.`tecnico`, `Tec`.`plaza` FROM `reportes_fallas`.`Tec`"; // Traer los datos de la tabla Tec con relaciones
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnicosList() {
        $sql = "SELECT * FROM reportes_fallas.tecnico"; // Obtener la lista de técnicos
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLugares() {
        $sql = "SELECT id, nombre FROM estacionamiento"; // Obtener los lugares (plazas)
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'tecnico' => $_POST['tecnico'],
        'plaza' => $_POST['plaza'],
    ];

    $db = new Database();
    if ($db->insertTec($data)) {
        $mensaje = "Técnico registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el técnico.";
    }
}

$db = new Database();
$tecnicosRegistrados = $db->getTecnicosRegistrados(); // Obtener los técnicos ya registrados
$tecnicosList = $db->getTecnicosList(); // Obtener la lista de técnicos
$lugares = $db->getLugares(); // Obtener los lugares (plazas)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Técnicos</title>
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

        form select {
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
    <h2>Alta de Técnicos</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="tecnico">Técnico</label>
        <select name="tecnico" id="tecnico" required>
            <option value="">Seleccione un técnico</option>
            <?php foreach ($tecnicosList as $tec): ?>
                <option value="<?php echo $tec['tecnico']; ?>"><?php echo $tec['tecnico']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="plaza">Plaza</label>
        <select name="plaza" id="plaza" required>
            <option value="">Seleccione una plaza</option>
            <?php foreach ($lugares as $lugar): ?>
                <option value="<?php echo $lugar['nombre']; ?>"><?php echo $lugar['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Registrar Técnico</button>
    </form>

    <h2>Técnicos Registrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Técnico</th>
                <th>Plaza</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tecnicosRegistrados): ?>
                <?php foreach ($tecnicosRegistrados as $tec): ?>
                    <tr>
                        <td><?php echo $tec['id']; ?></td>
                        <td><?php echo $tec['tecnico']; ?></td> <!-- Mostrar nombre del técnico -->
                        <td><?php echo $tec['plaza']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No hay técnicos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
