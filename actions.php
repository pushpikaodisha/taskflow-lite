<?php
require_once __DIR__ . '/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. CREATE TASK
if ($action === 'create') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'todo';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';

    if (empty($title)) {
        header("Location: index.php?error=empty_title");
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO tasks (title, description, status, priority) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $status, $priority]);
        header("Location: index.php?success=created");
        exit;
    } catch (PDOException $e) {
        die("Error creating task: " . $e->getMessage());
    }
}

// 2. QUICK MOVE STATUS (SHIFT LEFT / RIGHT)
if ($action === 'move') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $direction = isset($_GET['dir']) ? $_GET['dir'] : ''; // left, right

    if ($id <= 0 || !in_array($direction, ['left', 'right'])) {
        header("Location: index.php");
        exit;
    }

    try {
        // Fetch current status
        $stmt = $db->prepare("SELECT status FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) {
            header("Location: index.php");
            exit;
        }

        $columns = ['todo', 'in_progress', 'review', 'done'];
        $currIndex = array_search($task['status'], $columns);
        $newIndex = $currIndex;

        if ($direction === 'right' && $currIndex < 3) {
            $newIndex++;
        } elseif ($direction === 'left' && $currIndex > 0) {
            $newIndex--;
        }

        if ($newIndex !== $currIndex) {
            $newStatus = $columns[$newIndex];
            $updateStmt = $db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $updateStmt->execute([$newStatus, $id]);
        }

        header("Location: index.php?success=moved");
        exit;
    } catch (PDOException $e) {
        die("Error shifting status: " . $e->getMessage());
    }
}

// 2b. DIRECT STATUS UPDATE (HTML5 DRAG-AND-DROP SUPPORT)
if ($action === 'move_status') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $targetStatus = isset($_POST['status']) ? trim($_POST['status']) : '';
    $allowed = ['todo', 'in_progress', 'review', 'done'];
    $wantsJson = (isset($_GET['format']) && $_GET['format'] === 'json');

    if ($id <= 0 || !in_array($targetStatus, $allowed, true)) {
        if ($wantsJson) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Invalid task or target status']);
            exit;
        }
        header("Location: index.php?error=invalid_status");
        exit;
    }

    try {
        $updateStmt = $db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $updateStmt->execute([$targetStatus, $id]);
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'id' => $id, 'status' => $targetStatus]);
            exit;
        }
        header("Location: index.php?success=moved");
        exit;
    } catch (PDOException $e) {
        if ($wantsJson) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            exit;
        }
        die("Error shifting status: " . $e->getMessage());
    }
}

// 3. EDIT TASK DETAILS
if ($action === 'edit') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'todo';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';

    if ($id <= 0 || empty($title)) {
        header("Location: index.php?error=invalid_parameters");
        exit;
    }

    try {
        $stmt = $db->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, priority = ? WHERE id = ?");
        $stmt->execute([$title, $description, $status, $priority, $id]);
        header("Location: index.php?success=updated");
        exit;
    } catch (PDOException $e) {
        die("Error updating task: " . $e->getMessage());
    }
}

// 4. DELETE TASK
if ($action === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        header("Location: index.php");
        exit;
    }

    try {
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        die("Error deleting task: " . $e->getMessage());
    }
}

// Redirect on fallthrough
header("Location: index.php");
exit;
?>
