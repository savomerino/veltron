<?php
// filepath: d:\SAVO\BULGARIA\CUENTAS\TODO MOTO SRL\WEB\TodoMoto\rrss\api\export.php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluimos la configuración de la base de datos
require 'db.php';

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed: " . $conn->connect_error); // Or handle more gracefully
}

// Nombre del archivo que se descargará
$filename = "VELTRON_calendario_" . date('Y-m-d') . ".csv";

// Encabezados para forzar la descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abrir el "output stream" de PHP para escribir el CSV
$output = fopen('php://output', 'w');

if ($output === FALSE) {
    error_log("Failed to open php://output");
    die("Failed to open php://output"); // Or handle more gracefully
}

// Escribir la fila de encabezados del CSV (los nombres de las columnas)
fputcsv($output, array('ID', 'Tipo', 'Titulo', 'Idea Principal', 'Desarrollo', 'Fecha Publicacion', 'Completado'));

// Consulta para obtener todos los datos (USING PREPARED STATEMENT)
$sql = "SELECT id, tipo, titulo, idea_principal, desarrollo, fecha_publicacion, completado FROM entradas_contenido ORDER BY fecha_publicacion";
$stmt = $conn->prepare($sql);

if ($stmt === FALSE) {
    error_log("SQL prepare failed: " . $conn->error);
    die("SQL prepare failed: " . $conn->error); // Or handle more gracefully
}

$stmt->execute();
$result = $stmt->get_result();

if ($result === FALSE) {
    error_log("SQL execution failed: " . $stmt->error);
    die("SQL execution failed: " . $stmt->error); // Or handle more gracefully
}


if ($result->num_rows > 0) {
    // Recorrer cada fila de la base de datos y escribirla en el CSV
    while($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Close statement
$stmt->close();

// Close database connection
$conn->close();

// Close file
fclose($output);

// Exit
exit();
?>