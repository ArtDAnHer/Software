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

    public function getIncidenciaById($id) {
        $sql = "SELECT * FROM incidencias WHERE id = :id"; // Consulta para obtener los datos de una incidencia
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Obtener el ID de la incidencia desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$db = new Database();
$incidencia = $db->getIncidenciaById($id);

if ($incidencia) {
    // Mostrar los datos de la incidencia
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalles de la Incidencia</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 20px;
            }
            .container {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                max-width: 800px;
                margin: auto;
            }
            h2 {
                text-align: center;
                color: #28a745;
            }
            .data {
                margin: 10px 0;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background-color: #f9f9f9;
            }
            .data strong {
                display: inline-block;
                width: 150px;
                color: #333;
            }
            img {
                max-width: 100%;
                height: auto;
                border-radius: 4px;
                margin-top: 10px;
            }
            a {
                display: inline-block;
                margin: 20px 0;
                padding: 10px 15px;
                background-color: #28a745;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                text-align: center;
            }
            a:hover {
                background-color: #218838;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Detalles de la Incidencia</h2>
            <div class="data"><strong>ID:</strong> <?php echo $incidencia['id']; ?></div>
            <div class="data"><strong>Fecha Reporte:</strong> <?php echo $incidencia['fecha_reporte']; ?></div>
            <div class="data"><strong>Quien Reporta:</strong> <?php echo $incidencia['quien_reporta']; ?></div>
            <div class="data"><strong>Tipo:</strong> <?php echo $incidencia['tipo']; ?></div>
            <div class="data"><strong>Lugar:</strong> <?php echo $incidencia['lugar']; ?></div>
            <div class="data"><strong>Equipo:</strong> <?php echo $incidencia['equipo']; ?></div>
            <div class="data"><strong>Descripción:</strong> <?php echo nl2br($incidencia['descripcion']); ?></div>
            <div class="data"><strong>Operando:</strong> <?php echo $incidencia['operando'] ? 'Sí' : 'No'; ?></div>
            <div class="data"><strong>Estado:</strong> <?php echo $incidencia['estado']; ?></div>
            <div class="data"><strong>Diagnóstico:</strong> <?php echo nl2br($incidencia['diagnostico']); ?></div>

            <!-- Mostrar imágenes de 'imagen' y 'foto_evidencia_atencion' -->
            <?php 
            // Combinar imágenes de ambos campos
            $imagenes = array_merge(
                !empty($incidencia['imagen']) ? explode(',', $incidencia['imagen']) : [],
                !empty($incidencia['foto_evidencia_atencion']) ? explode(',', $incidencia['foto_evidencia_atencion']) : []
            );
            if (!empty($imagenes)): ?>
                <div class="data">
                    <strong>Imágenes:</strong><br>
                    <?php 
                    foreach ($imagenes as $imagen): ?>
                        <img src="<?php echo trim($imagen); ?>" alt="Imagen de la incidencia">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Agrega más campos según sea necesario -->
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Incidencia no encontrada. Verifique el ID proporcionado.";
}
?>
