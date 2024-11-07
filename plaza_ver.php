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

    public function getPlazas() {
        $sql = "SELECT * FROM estacionamiento";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePlaza($data) {
        $sql = "UPDATE estacionamiento SET estacionamiento = :estacionamiento, direccion = :direccion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estacionamiento', $data['estacionamiento']);
        $stmt->bindParam(':direccion', $data['direccion']);
        return $stmt->execute();
    }
}

// Manejo del formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $data = [
        'estacionamiento' => $_POST['estacionamiento'],
        'direccion' => $_POST['direccion']
    ];

    $db = new Database();
    if ($db->updatePlaza($data)) {
        echo "Plaza actualizada exitosamente.";
    } else {
        echo "Error al actualizar la plaza.";
    }
}

// Manejo del formulario de eliminación
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db = new Database();
    if ($db->deletePlaza($id)) {
        echo "Plaza eliminada exitosamente.";
    } else {
        echo "Error al eliminar la plaza.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Plazas</title>
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

        form input[type="text"],
        form input[type="number"],
        form input[type="email"], /* Añadir esta línea */
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

    </style>
</head>
<body >
    <div >
        <table>
            <thead>
                <tr>
                    <th>Estacionamiento</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $db = new Database();
                $plazas = $db->getPlazas();
                foreach ($plazas as $plaza) {
                    echo "<tr>
                        <form method='POST' action=''>
                            <td><input type='text' name='estacionamiento' value='{$plaza['estacionamiento']}'></td>
                            <td><input type='text' name='direccion' value='{$plaza['direccion']}'></td>
                            <td>
                                <button type='submit' name='update'>Actualizar</button>      
                            </td>
                        </form>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
