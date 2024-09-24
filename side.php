
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Vincula tu archivo CSS -->
    <title>Document</title>
</head>
<body>
    <div align="left" style="width: 400px; background-color: #fff;">
        <div style="height: 50px; "></div>
        <br>
        <br>
        <b><Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?>.</b>
        <br>
        <br>
        <br>
        <br>
        <a href="Boletaje_ver.php"> Ver</a><br>
        <a href="Boletaje_alta.php"> Alta</a>
    </div>
</body>
</html>