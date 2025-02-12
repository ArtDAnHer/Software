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
            $fileToInclude = 'usuario_info.php';
            break;
        case 'estacionamientos':
            $fileToInclude = 'plaza_alta.php';
            break;
        case 'alta_tipo':
            $fileToInclude = 'equipo_tipo_alta.php';
            break;
        case 'alta_equipo':
            $fileToInclude = 'equipo_alta.php';
            break;
        case 'alta_area':
            $fileToInclude = 'area_alta.php';
            break;
        case 'alta_tecnico':
            $fileToInclude = 'tecnico_alta.php';
            break;
        case 'alta_falla':
            $fileToInclude = 'falla_tipo.php';
            break;
        case 'alta_estado':
            $fileToInclude = 'estado_alta.php';
            break;
        case 'relacion_tec':
            $fileToInclude = 'relacion_tec.php';
            break;
        case 'refacciones':
            $fileToInclude = 'refacciones_alta.php';
            break;
        default:
            $fileToInclude = 'usuario_info.php';
            break;
    }
} else {
    $fileToInclude = 'usuario_info.php';
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
            <h4>Estacionamientos</h4>
            <div>
                °<a href="?page=estacionamientos">Estacionamientos</a><br>
                °<a href="?page=alta_tipo">Tipo de equipo</a><br>
                °<a href="?page=alta_equipo">Equipo</a><br>
                °<a href="?page=alta_area">Area</a><br>
                °<a href="?page=alta_tecnico">Tecnico</a><br>
                °<a href="?page=alta_falla">Falla</a><br>
                °<a href="?page=alta_estado">Estado</a><br>
                °<a href="?page=relacion_tec">Plaza por tecnico</a><br>
                °<a href="?page=refacciones">Refacciones</a><br>
            </div>
        </div>

        <!-- Sección derecha (70%) -->
        <div class="right-section-split">
            <?php include $fileToInclude; ?>
        </div>
    </div>
</body>
</html>

