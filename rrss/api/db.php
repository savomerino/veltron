<?php
header('Content-Type: application/json');

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
// Completa con tus credenciales de la base de datos de tu hosting.
$servername = "127.0.0.1:3306"; // O la dirección que te provea tu hosting
$username = "u592897176_veltron";
$password = "velt!rOn#2025";
$dbname = "u592897176_veltron";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // En lugar de die(), devolvemos un JSON con el error
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => "Error de conexión a la base de datos: " . $conn->connect_error]);
    exit();
}

// Establecer el charset a utf8mb4 para soportar emojis y caracteres especiales
$conn->set_charset("utf8mb4");

?>
