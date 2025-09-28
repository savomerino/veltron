<?php
require 'db.php';

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No se proporcionó un ID.']);
    exit();
}

$id = (int)$data['id'];

$sql = "DELETE FROM entradas_contenido WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'No se encontró ninguna entrada con ese ID.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
