<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Define el archivo a incluir basado en el parámetro 'page'
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'conglomerado';
            $fileToInclude = 'boletaje_conglomerado_total.php';
            break;
        case 'alta multiple';
            $fileToInclude = 'boletaje_alta_multiples.php';
            break;
        case 'ver':
            $fileToInclude = 'boletaje_Ver.php';
            break;
        case 'alta':
            $fileToInclude = 'boletaje_alta.php';
            break;
        case 'dashboard':
            $fileToInclude = 'boletaje_dashboard.php'; // Archivo del Dashboard
            break;
        default:
            $fileToInclude = 'boletaje_alta.php';
            break;
    }
} else {
    $fileToInclude = 'boletaje_alta.php';
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
        <div class="left-section-split">
            <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <hr>
            <h4>Boletaje</h4>
            <br>
            °<a href="?page=alta">Alta</a><br>
            °<a href="?page=alta multiple">Alta multiple</a><br>
            °<a href="?page=ver">Ver</a><br>
            °<a href="?page=conglomerado">Info de totales</a><br>
            °<a href="?page=dashboard">Dashboard</a><br>
        </div>

        <!-- Sección derecha (70%) -->
        <div class="right-section-split">
            <?php include $fileToInclude; ?>
        </div>
    </div>
</body>
</html>

