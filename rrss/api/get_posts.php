<?php
// --- CÓDIGO TEMPORAL PARA DEPURACIÓN ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN DEL CÓDIGO DE DEPURACIÓN ---

require 'db.php';

$sql = "SELECT id, tipo, titulo, idea_principal, desarrollo, fecha_publicacion, completado FROM entradas_contenido ORDER BY fecha_publicacion, id";
$result = $conn->query($sql);

$calendarPosts = [];
$brainstormingIdeas = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Convertir el 'completado' a booleano para JavaScript
        $row['completado'] = (bool)$row['completado'];

        // Cambiamos los nombres de las claves para que coincidan con el JS existente
        $post = [
            'id' => (int)$row['id'],
            'type' => $row['tipo'],
            'title' => $row['titulo'],
            'idea' => $row['idea_principal'],
            'development' => $row['desarrollo'],
            'completed' => $row['completado']
        ];

        if ($row['fecha_publicacion']) {
            $date = $row['fecha_publicacion'];
            if (!isset($calendarPosts[$date])) {
                $calendarPosts[$date] = [];
            }
            $calendarPosts[$date][] = $post;
        } else {
            $brainstormingIdeas[] = $post;
        }
    }
}

$conn->close();

echo json_encode(['calendarPosts' => $calendarPosts, 'brainstormingIdeas' => $brainstormingIdeas]);
?>
