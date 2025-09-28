<?php
require 'db.php';

// Leer el cuerpo de la petición que contiene el JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validar que los datos necesarios están presentes
if (!$data || !isset($data['title']) || !isset($data['type'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Datos incompletos.']);
    exit();
}

// Asignar variables desde el array de datos
$id = isset($data['id']) ? (int)$data['id'] : null;
$type = $data['type'];
$title = $data['title'];
$idea = $data['idea'] ?? '';
$development = $data['development'] ?? '';
$completed = isset($data['completed']) ? (int)(bool)$data['completed'] : 0;
$date = $data['date'] ?? null;

if ($id) {
    // --- ACTUALIZAR (UPDATE) ---
    $sql = "UPDATE entradas_contenido SET tipo=?, titulo=?, idea_principal=?, desarrollo=?, fecha_publicacion=?, completado=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al preparar la consulta de actualización: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("sssssii", $type, $title, $idea, $development, $date, $completed, $id);

} else {
    // --- INSERTAR (CREATE) ---
    $sql = "INSERT INTO entradas_contenido (tipo, titulo, idea_principal, desarrollo, fecha_publicacion, completado) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
     if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al preparar la consulta de inserción: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("sssssi", $type, $title, $idea, $development, $date, $completed);
}

if ($stmt->execute()) {
    $newId = $id ? $id : $conn->insert_id;
    echo json_encode(['success' => true, 'id' => $newId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
