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

    public function insertTecnico($data) {
        $sql = "INSERT INTO tecnico (tecnico, area) VALUES (:tecnico, :area)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':tecnico', $data['tecnico']);
        $stmt->bindParam(':area', $data['area']);

        return $stmt->execute();
    }

    public function getAreas() {
        $sql = "SELECT * FROM area";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnicos() {
        $sql = "SELECT * FROM tecnico";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'tecnico' => $_POST['tecnico'],
        'area' => $_POST['area']
    ];

    $db = new Database();
    if ($db->insertTecnico($data)) {
        $mensaje = "Técnico registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el técnico.";
    }
}

$db = new Database();
$areas = $db->getAreas();
$tecnicos = $db->getTecnicos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Técnico</title>
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

        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
    <h2>Registro de Técnico</h2>
    <hr>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="tecnico">Nombre del Técnico</label>
        <input type="text" name="tecnico" id="tecnico" required><br>

        <label for="area">Área</label>
        <select name="area" id="area" required>
            <option value="">Selecciona un área</option>
            <?php foreach ($areas as $area): ?>
                <option value="<?php echo $area['area']; ?>"><?php echo $area['area']; ?></option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Registrar Técnico</button>
    </form>

    <h2>Técnicos Registrados</h2>
    <table>
        <thead>
            <tr>
                <th>Técnico</th>
                <th>Área</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tecnicos): ?>
                <?php foreach ($tecnicos as $tecnico): ?>
                    <tr>
                        <td><?php echo $tecnico['tecnico']; ?></td>
                        <td><?php echo $tecnico['area']; ?></td>
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
