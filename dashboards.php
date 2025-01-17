<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Define el archivo a incluir basado en el parámetro 'page'
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'ver':
            $fileToInclude = 'incidenciasdash.php';
            break;
        default:
            $fileToInclude = 'incidenciasdash.php';
            break;
    }
} else {
    $fileToInclude = 'incidenciasdash.php';
}
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
        <!-- Sección izquierda (30%) -->
        <div class="left-section-split">
            <div align="center">
                <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            </div>
            <hr>
            <h4>Usuario</h4>
            <div>
                °<a href="?page=ver">Inicio</a><br>
            </div>
            <br>
            <h4>Dashboards</h4>
            <div>
                °<a href="?page=estacionamientos">Estacionamientos</a><br>
            </div>
        </div>

        <!-- Sección derecha (70%) -->
        <div class="right-section-split">
            <?php include $fileToInclude; ?>
        </div>
    </div>
</body>
</html>

