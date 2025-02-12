<?php

// Iniciar sesión solo si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// El resto de tu código
$rolUsuario = $_SESSION['rol'] ?? 'Admin'; // Obtener el rol del usuario


// Clase de conexión y manejo de la base de datos
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
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getIncidencias($rol, $filters = []) {
        $sql = "SELECT * FROM incidencias WHERE atendido = 0";
        $params = [];

        if ($rol !== 'Admin') {
            $sql .= " AND area = :rol";
            $params[':rol'] = $rol;
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $sql .= " AND fecha_reporte BETWEEN :fecha_inicio AND :fecha_fin";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }

        if (!empty($filters['lugar'])) {
            $sql .= " AND lugar = :lugar";
            $params[':lugar'] = $filters['lugar'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }

        if (isset($filters['operando']) && $filters['operando'] !== '') {
            $sql .= " AND operando = :operando";
            $params[':operando'] = $filters['operando'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['tecnico'])) {
            $sql .= " AND tecnico = :tecnico";
            $params[':tecnico'] = $filters['tecnico'];
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTiposDeFalla() {
        $sql = "SELECT id, nombre FROM tipo_falla";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstacionamientos() {
        $sql = "SELECT id, nombre FROM estacionamiento";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTecnicos() {
        $sql = "SELECT id, tecnico FROM tecnico";  
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Obtener filtros del formulario
$filters = [
    'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
    'fecha_fin' => $_POST['fecha_fin'] ?? null,
    'lugar' => $_POST['lugar'] ?? null,
    'tipo' => $_POST['tipo'] ?? null,
    'operando' => $_POST['operando'] ?? null,
    'estado' => $_POST['estado'] ?? null,
    'tecnico' => $_POST['tecnico'] ?? null,
];

// Instanciar la base de datos
$db = new Database();

// Obtener datos para el formulario
$tiposDeFalla = $db->getTiposDeFalla();
$estacionamientos = $db->getEstacionamientos();
$tecnicos = $db->getTecnicos();

// Obtener incidencias
$incidencias = $db->getIncidencias($rolUsuario, $filters);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Incidencias</title>
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
            text-align: center;
            margin-bottom: 20px;
        }
        form input, form select, form button {
            margin: 5px;
            padding: 8px;
        }
        table {
            width: 100%;
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
            font-size: 12px;
        }
        table th {
            background-color: #28a745;
            color: white;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Estilo para el modal */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.9);
            padding-top: 60px;
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .img-thumbnail {
            cursor: pointer;
        }
    </style>
    <script>
        // Rol de usuario y nombre de usuario pasados desde PHP
        const rolUsuario = "<?php echo $rolUsuario; ?>";
        const usuarioSesion = "<?php echo $_SESSION['usuario'] ?? ''; ?>"; // Ajusta la variable de usuario de sesión según tu implementación


        function abrirPopup(id, tecnicoIncidencia) {
        // Verificar si el rol es Admin o si el usuario de sesión es el técnico asignado
        if (rolUsuario === 'Admin') {
            var url = 'crear_diagnostico.php?id=' + id;
            var popup = window.open(url, 'Editar Incidencia', 'width=600,height=600');
        } else if (rolUsuario === 'Tecnico' || rolUsuario === 'Mantenimiento') {
            if (usuarioSesion === tecnicoIncidencia) {
                var url = 'crear_diagnostico.php?id=' + id;
                var popup = window.open(url, 'Editar Incidencia', 'width=600,height=600');
            } else {
                alert('No tienes permisos para editar esta incidencia.');
            }
        } else {
            alert('No tienes permisos para editar esta incidencia.');
        }
}



    </script>


</head>
<body>
    <h2>Lista de Incidencias</h2>

    <!-- Formulario de búsqueda -->
    <form method="POST">
        <label for="fecha_inicio">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $filters['fecha_inicio']; ?>">
        <label for="fecha_fin">Fecha Fin:</label>
        <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $filters['fecha_fin']; ?>">
        <label for="lugar">Lugar:</label>
        <select name="lugar" id="lugar">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($estacionamientos as $estacionamiento): ?>
                <option value="<?php echo $estacionamiento['nombre']; ?>" 
                    <?php echo ($filters['lugar'] === $estacionamiento['nombre']) ? 'selected' : ''; ?>>
                    <?php echo $estacionamiento['nombre']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="tipo">Tipo de Falla:</label>
        <select name="tipo" id="tipo">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($tiposDeFalla as $tipo): ?>
                <option value="<?php echo $tipo['nombre']; ?>" 
                    <?php echo ($filters['tipo'] === $tipo['nombre']) ? 'selected' : ''; ?>>
                    <?php echo $tipo['nombre']; ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <label for="operando">Operando:</label>
        <select name="operando" id="operando">
            <option value="">-- Seleccionar --</option>
            <option value="1" <?php echo ($filters['operando'] === '1') ? 'selected' : ''; ?>>Sí</option>
            <option value="0" <?php echo ($filters['operando'] === '0') ? 'selected' : ''; ?>>No</option>
        </select>
        <label for="estado">Estado:</label>
        <select name="estado" id="estado">
            <option value="">-- Seleccionar --</option>
            <option value="Cerrado" <?php echo ($filters['estado'] === 'Cerrado') ? 'selected' : ''; ?>>Cerrado</option>
            <option value="Fallando" <?php echo ($filters['estado'] === 'Fallando') ? 'selected' : ''; ?>>Fallando</option>
            <option value="Funcionando" <?php echo ($filters['estado'] === 'Funcionando') ? 'selected' : ''; ?>>Funcional</option>
        </select>
        <label for="tecnico">Técnico:</label>
        <select name="tecnico" id="tecnico">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?php echo $tecnico['tecnico']; ?>" 
                    <?php echo ($filters['tecnico'] == $tecnico['tecnico']) ? 'selected' : ''; ?>>
                    <?php echo $tecnico['tecnico']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Buscar</button>
    </form>

    <!-- Tabla de resultados -->
    <?php if (!empty($incidencias)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha Reporte</th>
                    <th>Quien Reporta</th>
                    <th>Tipo</th>
                    <th>Lugar</th>
                    <th>Equipo</th>
                    <th>Descripción</th>
                    <th>Operando</th>
                    <th>Imagen</th>
                    <th>Reincidencia</th>
                    <th>Incidencia Relacionada</th>
                    <th>Estado</th>
                    <th>Área</th>
                    <th>Técnico</th>
                    <th>Diagnostico</th>
                    <th>Se necesita Pieza</th>
                    <th>Pieza 1</th>
                    <th>Pieza 2</th>
                    <th>Pieza 3</th>
                    <th>Foto de Atencion</th>
                    <th>Atendido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidencias as $incidencia): ?>
                    <tr>
                        <td><?php echo $incidencia['id']; ?></td>
                        <td><?php echo $incidencia['fecha_reporte']; ?></td>
                        <td><?php echo $incidencia['quien_reporta']; ?></td>
                        <td><?php echo $incidencia['tipo']; ?></td>
                        <td><?php echo $incidencia['lugar']; ?></td>
                        <td><?php echo $incidencia['equipo']; ?></td>
                        <td><?php echo $incidencia['descripcion']; ?></td>
                        <td><?php echo $incidencia['operando'] ? 'Sí' : 'No'; ?></td>
                        <td>
                            <?php if (!empty($incidencia['imagen'])): ?>
                                <img src="<?php echo $incidencia['imagen']; ?>" alt="Imagen de incidencia" width="100" class="img-thumbnail" onclick="openModal('<?php echo $incidencia['imagen']; ?>')">
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </td>
                        <td><?php echo $incidencia['reincidencia'] ? 'Sí' : 'No'; ?></td>
                        <td><?php echo $incidencia['incidencia_relacionada']; ?></td>
                        <td style="background-color: <?php
                            if ($incidencia['estado'] == 'Cerrado') echo 'red';
                            elseif ($incidencia['estado'] == 'Activo') echo 'green';
                            elseif ($incidencia['estado'] == 'Funcional') echo 'yellow';
                        ?>;">
                            <?php echo $incidencia['estado']; ?>
                        </td>
                        <td><?php echo $incidencia['area']; ?></td>
                        <td><?php echo $incidencia['tecnico']; ?></td>
                        <td><?php echo $incidencia['diagnostico']; ?></td>
                        <td><?php echo $incidencia['requiere_piezas'] == 1 ? 'Sí' : 'No'; ?></td>
                        <td><?php echo $incidencia['detalle_piezas_requeridas']; ?></td>
                        <td><?php echo $incidencia['refaccion_adicional_1']; ?></td>
                        <td><?php echo $incidencia['refaccion_adicional_2']; ?></td>
                        <td>
                            <?php if (!empty($incidencia['foto_evidencia_atencion'])): ?>
                                <img src="<?php echo $incidencia['foto_evidencia_atencion']; ?>" alt="Foto de atención" width="100" class="img-thumbnail" onclick="openModal('<?php echo $incidencia['foto_evidencia_atencion']; ?>')">
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </td>
                        <td><?php echo $incidencia['atendido'] == 1 ? 'Sí' : 'No'; ?></td>
                        <td>
                            <button onclick="abrirPopup(<?php echo $incidencia['id']; ?>, '<?php echo $incidencia['tecnico']; ?>')">Editar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Modal -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="close" onclick="closeModal()">×</span>
        <img class="modal-content" id="modalImage" />
    </div>

    <script>
        // Abre el modal y muestra la imagen
        function openModal(imageUrl) {
            var modal = document.getElementById("imageModal");
            var modalImg = document.getElementById("modalImage");
            modal.style.display = "block";
            modalImg.src = imageUrl;
        }

        // Cierra el modal
        function closeModal() {
            var modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }

        // Evita que el modal se cierre si se hace clic en la imagen
        document.getElementById("modalImage").onclick = function(event) {
            event.stopPropagation();
        };
    </script>
</body>
</html>
