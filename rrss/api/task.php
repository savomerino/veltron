<?php
// filepath: d:\SAVO\BULGARIA\CUENTAS\VELTRON\RRSS\APP\rrss\api\task.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    case 'OPTIONS':
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

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        return;
    }

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $taskId = $row['id'];
        $checklistSql = "SELECT * FROM checklist_items WHERE task_id = $taskId";
        $checklistResult = $conn->query($checklistSql);

        if (!$checklistResult) {
            http_response_code(500);
            echo json_encode(['error' => 'Checklist query failed: ' . $conn->error]);
            return;
        }

        $checklist = [];
        while ($checklistRow = $checklistResult->fetch_assoc()) {
            $checklist[] = $checklistRow;
        }

        $row['checklist'] = $checklist;
        $tasks[] = $row;
    }

    echo json_encode($tasks);
}

function createTask($conn) {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->title) || !isset($data->description) || !isset($data->assigned_to) || !isset($data->deadline)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    $title = $data->title;
    $description = $data->description;
    $assignedTo = $data->assigned_to;
    $deadline = $data->deadline;

    $sql = "INSERT INTO tasks (title, description, assigned_to, deadline) VALUES ('$title', '$description', '$assignedTo', '$deadline')";

    if ($conn->query($sql)) {
        $taskId = $conn->insert_id;
        echo json_encode(['id' => $taskId, 'title' => $title, 'description' => $description, 'assigned_to' => $assignedTo, 'deadline' => $deadline, 'checklist' => []]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create task: ' . $conn->error]);
    }
}

function updateTask($conn) {
    $taskId = intval(basename($_SERVER['REQUEST_URI']));
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->title) || !isset($data->description) || !isset($data->assigned_to) || !isset($data->deadline) || !isset($data->checklist)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    $title = $data->title;
    $description = $data->description;
    $assignedTo = $data->assigned_to;
    $deadline = $data->deadline;
    $checklist = $data->checklist;

    $sql = "UPDATE tasks SET title='$title', description='$description', assigned_to='$assignedTo', deadline='$deadline' WHERE id=$taskId";

    if ($conn->query($sql)) {
        // Delete existing checklist items
        $deleteChecklistSql = "DELETE FROM checklist_items WHERE task_id = $taskId";
        if ($conn->query($deleteChecklistSql)) {
            // Insert new checklist items
            foreach ($checklist as $item) {
                $text = $item->text;
                $done = $item->done ? 1 : 0;
                $insertChecklistSql = "INSERT INTO checklist_items (task_id, text, done) VALUES ($taskId, '$text', $done)";
                if (!$conn->query($insertChecklistSql)) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to insert checklist item: ' . $conn->error]);
                    return;
                }
            }
            echo json_encode(['message' => 'Task updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete checklist items: ' . $conn->error]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update task: ' . $conn->error]);
    }
}

function deleteTask($conn) {
    $taskId = intval(basename($_SERVER['REQUEST_URI']));

    $sql = "DELETE FROM tasks WHERE id = $taskId";

    if ($conn->query($sql)) {
        echo json_encode(['message' => 'Task deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete task: ' . $conn->error]);
    }
}
?>