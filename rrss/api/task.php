<?php
// filepath: d:\SAVO\BULGARIA\CUENTAS\TODO MOTO SRL\WEB\TodoMoto\rrss\api\task.php
// filepath: /api/tasks.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production!
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getTasks($conn);
        break;
    case 'POST':
        createTask($conn);
        break;
    case 'PUT':
        updateTask($conn);
        break;
    case 'DELETE':
        deleteTask($conn);
        break;
    case 'OPTIONS':  // Handle preflight requests
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

$conn->close();

function getTasks($conn) {
    $sql = "SELECT * FROM tasks";
    $result = $conn->query($sql);

    if ($result === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        return;
    }

    $tasks = array();
    while($row = $result->fetch_assoc()) {
        // Fetch checklist items for each task
        $task_id = $row['id'];
        $checklist_sql = "SELECT * FROM checklist_items WHERE task_id = $task_id";
        $checklist_result = $conn->query($checklist_sql);

        if ($checklist_result === FALSE) {
            http_response_code(500);
            echo json_encode(['error' => 'Checklist query failed: ' . $conn->error]);
            return;
        }

        $checklist = array();
        while ($checklist_row = $checklist_result->fetch_assoc()) {
            $checklist[] = $checklist_row;
        }

        $row['checklist'] = $checklist;
        $tasks[] = $row;
    }

    echo json_encode($tasks);
}

function createTask($conn) {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->title)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title is required']);
        return;
    }

    $title = $data->title;
    $status = 'pendiente'; // Default status

    $sql = "INSERT INTO tasks (title, status) VALUES ('$title', '$status')";

    if ($conn->query($sql) === TRUE) {
        $task_id = $conn->insert_id;  // Get the ID of the newly inserted task

        // Respond with the newly created task, including the ID
        $newTask = [
            'id' => $task_id,
            'title' => $title,
            'status' => $status,
            'checklist' => []
        ];
        echo json_encode($newTask);

    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error creating task: ' . $conn->error]);
    }
}


function updateTask($conn) {
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $uri);
    $task_id = intval(end($parts));

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->title) || !isset($data->status) || !isset($data->checklist)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title, status, and checklist are required']);
        return;
    }

    $title = $data->title;
    $status = $data->status;
    $checklist = $data->checklist;

    $sql = "UPDATE tasks SET title='$title', status='$status' WHERE id=$task_id";

    if ($conn->query($sql) === TRUE) {
        // Update checklist items
        // First, delete existing checklist items for the task
        $delete_checklist_sql = "DELETE FROM checklist_items WHERE task_id = $task_id";
        if ($conn->query($delete_checklist_sql) === FALSE) {
            http_response_code(500);
            echo json_encode(['error' => 'Error deleting checklist items: ' . $conn->error]);
            return;
        }

        // Then, insert the new checklist items
        foreach ($checklist as $item) {
            $text = $item['text'];
            $done = $item['done'] ? 1 : 0;  // Convert boolean to 0 or 1
            $insert_checklist_sql = "INSERT INTO checklist_items (task_id, text, done) VALUES ($task_id, '$text', $done)";
            if ($conn->query($insert_checklist_sql) === FALSE) {
                http_response_code(500);
                echo json_encode(['error' => 'Error inserting checklist item: ' . $conn->error]);
                return;
            }
        }

        echo json_encode(['message' => 'Task updated successfully']);

    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error updating task: ' . $conn->error]);
    }
}


function deleteTask($conn) {
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $uri);
    $task_id = intval(end($parts));

    $sql = "DELETE FROM tasks WHERE id = $task_id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['message' => 'Task deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error deleting task: ' . $conn->error]);
    }
}
?>