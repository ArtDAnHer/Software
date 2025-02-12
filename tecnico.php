<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Define el archivo a incluir basado en el parámetro 'page'
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
<<<<<<< HEAD:Boletaje.php
        case 'conglomerado';
            $fileToInclude = 'boletaje_conglomerado_total.php';
            break;
        case 'alta multiple';
            $fileToInclude = 'boletaje_alta_multiples.php';
            break;
        case 'ver':
            $fileToInclude = 'boletaje_ver.php';
            break;
=======
>>>>>>> a5e7407534ffde80b3f3d9375184a9bfd5db5ebb:tecnico.php
        case 'alta':
            $fileToInclude = 'incidencias_alta.php';
            break;
        case 'diagnostico':
            $fileToInclude = 'lista_diacnosticos.php';
            break;
        case 'refacciones':
            $fileToInclude = 'refacciones_alta.php';
            break;
        case 'tablas':
            $fileToInclude = 'boletaje_deposito.php';
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
<?php include 'header_tecnico.php'; ?>

    <!-- Contenedor dividido 30% y 70% -->
    <div class="split-container">
        <!-- Sección izquierda (30%) -->
        <div class="left-section-split">
<<<<<<< HEAD:Boletaje.php
            <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
=======
            <div align="center">
                <h2><?php echo htmlspecialchars($username); ?></h2>
            </div>
>>>>>>> a5e7407534ffde80b3f3d9375184a9bfd5db5ebb:tecnico.php
            <hr>
            <h4>Usuario</h4>
            <div>
                °<a href="?page=alta">Inicio</a><br>
            </div>
            <br>
<<<<<<< HEAD:Boletaje.php
            °<a href="?page=alta">Alta</a><br>
            °<a href="?page=alta multiple">Alta multiple</a><br>
            °<a href="?page=ver">Ver</a><br>
            °<a href="?page=conglomerado">Info de totales</a><br>
            °<a href="?page=dashboard">Dashboard</a><br>
            °<a href="?page=tablas">Tabla de boletaje y depositos</a><br>
=======
            <h4>Estacionamientos</h4>
            <div>
                °<a href="?page=alta">Alta</a><br>
                °<a href="?page=diagnostico">Diagnostico</a><br>
                °<a href="?page=refacciones">Refacciones</a><br>
            </div>
>>>>>>> a5e7407534ffde80b3f3d9375184a9bfd5db5ebb:tecnico.php
        </div>

        <!-- Sección derecha (70%) -->
        <div class="right-section-split">
            <?php include $fileToInclude; ?>
        </div>
    </div>
</body>
</html>
