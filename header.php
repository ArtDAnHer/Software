<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Vincula tu archivo CSS -->
    <title>Mi Página</title>
    <style>
        /* Estilo general del contenedor dividido */
.split-container {
    display: flex;
    height: 100vh; /* Ocupa el 100% de la altura de la ventana */
}

/* Sección izquierda que ocupa el 30% del ancho */
.left-section-split {
    width: 30%;
    background-color: #f4f4f4;
    padding: 20px;
    box-sizing: border-box;
    border-right: 1px solid #ccc;
    overflow-y: auto; /* Añade un scroll si el contenido es más grande que la sección */
}

/* Sección derecha que ocupa el 70% del ancho */
.right-section-split {
    width: 70%;
    padding: 20px;
    box-sizing: border-box;
    overflow-y: auto; /* Añade un scroll si el contenido es más grande que la sección */
}

    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <div style="width: 50px"></div>
                <a href="welcome.php">
                    <img style="height: 40px;" src="imagenes\logo .jpg" />
                </a>
            </ul>
        </nav>
        <nav align="center">
            <ul align="center">
                <li><a href="welcome.php">Inicio</a></li>

                <li><a href="incidencias.php">Incidencias</a></li>

            </ul>
        </nav>
        <nav align="right">
            <div align="right">
                <a href="logout.php" style="color: white">Cerrar sesión</a>
            </div>
        </nav>
    </header>
</body>
</html>
