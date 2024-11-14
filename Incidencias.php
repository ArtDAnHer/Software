<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Define el archivo a incluir basado en el parámetro 'page'
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'alta_previa':
            $fileToInclude = 'crear_incidencia_pasada.php';
            break;
        case 'alta':
            $fileToInclude = 'incidencias_alta.php';
            break;
        case 'asignar_tecnico':
            $fileToInclude = 'asignartecnico.php';
            break;
        case 'diagnostico':
            $fileToInclude = 'lista_diacnosticos.php';
            break;
        case 'finalizacion':
            $fileToInclude = 'finalizacion.php';
            break;
        default:
            $fileToInclude = 'incidencias_alta.php';
            break;
    }
} else {
    $fileToInclude = 'incidencias_alta.php';
}

// Pasamos la sesión como variable
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Vincula tu archivo CSS -->
    <title>Document</title>
</head>
<body>
<?php include 'header.php'; ?>

    <!-- Contenedor dividido 30% y 70% -->
    <div class="split-container">
        <!-- Sección izquierda (30%) -->
        <div class="left-section-split">
            <div align="center">
                <h2><?php echo htmlspecialchars($username); ?></h2>
            </div>
            <hr>
            <h4>Usuario</h4>
            <div>
                °<a href="?page=alta">Inicio</a><br>
            </div>
            <br>
            <h4>Estacionamientos</h4>
            <div>
                °<a href="?page=alta_previa">Alta incidencia previa</a><br>
                °<a href="?page=alta">Alta</a><br>
                °<a href="?page=asignar_tecnico">Asignar tecnico</a><br>
                °<a href="?page=diagnostico">Diagnistico</a><br>
                °<a href="?page=finalizacion">Finalizacion</a><br>

            </div>
        </div>

        <!-- Sección derecha (70%) -->
        <div class="right-section-split">
            <?php include $fileToInclude; ?>
        </div>
    </div>
</body>
</html>
