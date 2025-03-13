<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $database = new Database();
    $conn = $database->connect();

    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $_SESSION['username'] = $username;
            header("Location: Boletaje.php");
            exit();
        } else {
            $_SESSION['username'] = $username;
            header("Location: usuario.php");
            exit(); // Importante para detener la ejecución después de la redirección
        }
    } else {
        $_SESSION['username'] = $username;
        header("Location: errorConexion.php");
        exit();
    }
} else {
    $_SESSION['username'] = $username;
    header("Location: error.php");
    echo "Método de solicitud no permitido.";
    exit();
}
?>
