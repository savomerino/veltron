<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php'; // conexión con $conn

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
    case 'OPTIONS':
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

$conn->close();

function getTasks($conn) {
    $sql = "SELECT * FROM tasks ORDER BY id DESC";
    $result = $conn->query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        return;
    }

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $taskId = $row['id'];

        // checklist
        $checklist = [];
        $checklistSql = "SELECT * FROM checklist_items WHERE task_id = $taskId";
        $checklistResult = $conn->query($checklistSql);
        if ($checklistResult) {
            while ($checklistRow = $checklistResult->fetch_assoc()) {
                $checklistRow['done'] = (bool)$checklistRow['done'];
                $checklist[] = $checklistRow;
            }
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
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    $title = $conn->real_escape_string($data->title);
    $description = $conn->real_escape_string($data->description ?? '');
    $assignedTo = $conn->real_escape_string($data->assigned_to ?? '');
    $deadline = $conn->real_escape_string($data->deadline ?? '');
    $status = $conn->real_escape_string($data->status ?? 'pendiente');

    $sql = "INSERT INTO tasks (title, description, assigned_to, deadline, status) 
            VALUES ('$title', '$description', '$assignedTo', '$deadline', '$status')";

    if ($conn->query($sql)) {
        $taskId = $conn->insert_id;

        // checklist
        if (!empty($data->checklist) && is_array($data->checklist)) {
            foreach ($data->checklist as $item) {
                $text = $conn->real_escape_string($item->text ?? '');
                $done = !empty($item->done) ? 1 : 0;
                if ($text !== '') {
                    $conn->query("INSERT INTO checklist_items (task_id, text, done) 
                                  VALUES ($taskId, '$text', $done)");
                }
            }
        }

        echo json_encode(['message' => 'Task created successfully', 'id' => $taskId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create task: ' . $conn->error]);
    }
}

function updateTask($conn) {
    $taskId = intval($_GET['id'] ?? 0);
    $data = json_decode(file_get_contents("php://input"));

    if ($taskId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid task ID']);
        return;
    }

    $title = $conn->real_escape_string($data->title ?? '');
    $description = $conn->real_escape_string($data->description ?? '');
    $assignedTo = $conn->real_escape_string($data->assigned_to ?? '');
    $deadline = $conn->real_escape_string($data->deadline ?? '');
    $status = $conn->real_escape_string($data->status ?? 'pendiente');

    $sql = "UPDATE tasks 
            SET title='$title', description='$description', assigned_to='$assignedTo', deadline='$deadline', status='$status'
            WHERE id=$taskId";

    if ($conn->query($sql)) {
        // reset checklist
        $conn->query("DELETE FROM checklist_items WHERE task_id = $taskId");
        if (!empty($data->checklist) && is_array($data->checklist)) {
            foreach ($data->checklist as $item) {
                $text = $conn->real_escape_string($item->text ?? '');
                $done = !empty($item->done) ? 1 : 0;
                if ($text !== '') {
                    $conn->query("INSERT INTO checklist_items (task_id, text, done) 
                                  VALUES ($taskId, '$text', $done)");
                }
            }
        }
        echo json_encode(['message' => 'Task updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update task: ' . $conn->error]);
    }
}

function deleteTask($conn) {
    $taskId = intval($_GET['id'] ?? 0);

    if ($taskId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid task ID']);
        return;
    }

    $sql = "DELETE FROM tasks WHERE id = $taskId";

    if ($conn->query($sql)) {
        echo json_encode(['message' => 'Task deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete task: ' . $conn->error]);
    }
}
