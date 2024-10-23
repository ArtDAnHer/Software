<?php
require 'Database.php'; // Asegúrate de tener acceso a tu clase Database

if (isset($_GET['lugar'])) {
    $lugar = $_GET['lugar'];

    $db = new Database();

    // Obtener equipos y técnicos por lugar
    $equipos = $db->getEquiposPorLugar($lugar);
    $tecnicos = $db->getTecnicoPorLugar($lugar);

    // Devolver los datos en formato JSON
    echo json_encode([
        'equipos' => $equipos,
        'tecnicos' => $tecnicos
    ]);
}
?>
