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
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['username'] = $username;
            $_SESSION['rol'] = $user['rol'];

            // Redirigir según el rol del usuario
            if ($_SESSION['rol'] === 'Tecnico' || $_SESSION['rol'] === 'Mantenimiento') {
                header("Location: tecnico.php");
            } elseif ($_SESSION['rol'] === 'Estacionamiento') {
                header("Location: estacionamiento.php");
            } elseif ($_SESSION['rol'] === 'Admin') {
                header("Location: welcome.php");
            } else {
                header("Location: error.php");
            }
            exit();
        } else {
            $_SESSION['username'] = $username;
            header("Location: Usuario.php");
            exit();
        }
    } else {
        $_SESSION['username'] = $username;
        header("Location: errorConexion.php");
        exit();
    }
} else {
    header("Location: error.php");
    exit();
}
?>
