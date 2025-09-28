<?php
require 'db.php';

$initialContent = [
    "2025-10-01" => [['id' => 1, 'type' => "Reel", 'title' => "Checklist para tu Moto", 'idea' => "5 puntos clave para revisar antes de la primavera.", 'development' => "Publicar un Reel mostrando 5 puntos clave para revisar en la moto antes de que empiece la temporada de primavera/verano. Usar música en tendencia para mayor alcance.", 'completed' => false]],
    "2025-10-03" => [['id' => 2, 'type' => "Carrusel", 'title' => "Guía Stihl", 'idea' => "La herramienta ideal para cada tarea de tu jardín.", 'development' => "Crear un carrusel de imágenes titulado 'La herramienta Stihl ideal para cada tarea de tu jardín'. Cada diapositiva debe mostrar una herramienta (ej. motoguadaña, sopladora) y su uso principal. Foco en contenido útil para que los usuarios lo guarden.", 'completed' => false]],
    "2025-10-06" => [['id' => 3, 'type' => "Story", 'title' => "Encuesta Interactiva", 'idea' => "¿Service: en taller o en casa?", 'development' => "Subir una historia con el sticker de encuesta: 'Para el service de tu moto, ¿confiás en un taller profesional o lo hacés en casa?'. El objetivo es fomentar la participación y el engagement.", 'completed' => false]],
    "2025-10-08" => [['id' => 4, 'type' => "Reel", 'title' => "Recetas en Air Fryer", 'idea' => "3 ideas sanas y rápidas en 1 minuto.", 'development' => "Video corto y dinámico mostrando '3 recetas sanas y rápidas en 1 minuto' usando una Air Fryer de la tienda. Optimizado para 'watch time' y para que la gente guarde el video.", 'completed' => false]],
    "2025-10-10" => [['id' => 5, 'type' => "Carrusel", 'title' => "Guía de Aires", 'idea' => "Aprendé a elegir las frigorías.", 'development' => "Contenido educativo: 'Cómo elegir tu Aire Acondicionado: Guía simple de Frigorías'. Explicar de forma sencilla cómo calcular la capacidad necesaria para un ambiente.", 'completed' => false]],
    "2025-10-13" => [['id' => 6, 'type' => "Story", 'title' => "Preguntas y Respuestas", 'idea' => "Consultanos por la financiación para renovar tu living.", 'development' => "Usar el sticker de Preguntas en una historia con el texto: '¿Pensando en renovar tu living? Consultanos por la financiación para tu nuevo sillón y TV'. Responder las consultas por mensaje directo.", 'completed' => false]],
    "2025-10-15" => [['id' => 10, 'type' => "Reel", 'title' => "Un Día en la Tienda", 'idea' => "Todo lo que podés encontrar en TODOMOTO.", 'development' => "Video dinámico con transiciones rápidas mostrando la variedad de productos en la tienda. Desde una moto, pasando por una licuadrora, hasta un sillón. Mensaje: 'Todo lo que necesitás, en un solo lugar'.", 'completed' => false]],
    "2025-10-17" => [['id' => 11, 'type' => "Carrusel", 'title' => "Ofertas del Mes", 'idea' => "Placas claras y directas con las mejores ofertas.", 'development' => "Placas gráficas claras y directas con las 3 mejores ofertas de octubre. Foco 100% en la venta, con un llamado a la acción claro para consultar por WhatsApp.", 'completed' => false]],
    "2025-10-20" => [['id' => 9, 'type' => "Story", 'title' => "Testimonio Real", 'idea' => "Video corto de un cliente emprendedor.", 'development' => "Compartir un video corto (15-30 segundos) de un cliente emprendedor contando su experiencia: 'Así empecé yo gracias al equipamiento de TODOMOTO'. La prueba social es muy poderosa.", 'completed' => false]],
    "2025-10-22" => [['id' => 13, 'type' => "Reel", 'title' => "Antes y Después", 'idea' => "Transformación de un espacio (living, cocina).", 'development' => "Un Reel mostrando la transformación de un espacio. Ejemplo: un living vacío y luego amueblado con un sillón y TV de la tienda, o una cocina antigua vs. una renovada.", 'completed' => false]],
    "2025-10-24" => [['id' => 14, 'type' => "Carrusel", 'title' => "Cierre de Mes", 'idea' => "Recopilación de los más vendidos y recordatorio de financiación.", 'development' => "Un post de carrusel titulado 'Terminá el mes con TODO'. Mostrar un resumen de los productos más populares del mes y un último recordatorio sobre las opciones de financiación.", 'completed' => false]],
    "2025-10-27" => [['id' => 12, 'type' => "Story", 'title' => "Cuenta Regresiva", 'idea' => "Sticker de cuenta regresiva para fin de ofertas.", 'development' => "Usar el sticker de cuenta regresiva en Stories para anunciar el fin de las ofertas de octubre. Esto genera un sentido de urgencia en la audiencia.", 'completed' => false]]
];

foreach ($initialContent as $date => $posts) {
    foreach ($posts as $post) {
        $id = $post['id'];
        $type = $conn->real_escape_string($post['type']);
        $title = $conn->real_escape_string($post['title']);
        $idea = $conn->real_escape_string($post['idea']);
        $development = $conn->real_escape_string($post['development']);
        $completed = $post['completed'] ? 1 : 0;

        $sql = "INSERT INTO entradas_contenido (id, tipo, titulo, idea_principal, desarrollo, fecha_publicacion, completado) VALUES ('$id', '$type', '$title', '$idea', '$development', '$date', '$completed') ON DUPLICATE KEY UPDATE tipo=VALUES(tipo), titulo=VALUES(titulo), idea_principal=VALUES(idea_principal), desarrollo=VALUES(desarrollo), fecha_publicacion=VALUES(fecha_publicacion), completado=VALUES(completado)";

        if ($conn->query($sql) === TRUE) {
            echo "Record with ID $id inserted/updated successfully for date $date.\n";
        } else {
            echo "Error for ID $id on date $date: " . $sql . "<br>" . $conn->error . "\n";
        }
    }
}

$conn->close();
?>
