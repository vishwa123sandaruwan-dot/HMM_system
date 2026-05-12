<?php
require_once '../db.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {

    // ========== LIST CATEGORIES ==========
    case 'list':
        $type = $_GET['type'] ?? '';
        $where = '';
        $params = [];
        $types = '';

        if ($type && in_array($type, ['income', 'expense'])) {
            $where = "WHERE type = ?";
            $params[] = $type;
            $types = 's';
        }

        $sql = "SELECT * FROM categories $where ORDER BY type, name";
        if ($types) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $categories = [];
        while ($row = $result->fetch_assoc()) $categories[] = $row;

        echo json_encode(['success' => true, 'data' => $categories]);
        break;

    // ========== ADD CATEGORY ==========
    case 'add':
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $icon = trim($_POST['icon'] ?? 'fa-tag');

        if (!$name || !in_array($type, ['income', 'expense'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            exit;
        }

        // Check duplicate
        $check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND type = ?");
        $check->bind_param('ss', $name, $type);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Category already exists.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO categories (name, type, icon) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $type, $icon);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added!', 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category.']);
        }
        break;

    // ========== DELETE CATEGORY ==========
    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        // Check if used
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM transactions WHERE category_id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $used = $check->get_result()->fetch_assoc()['cnt'];

        if ($used > 0) {
            echo json_encode(['success' => false, 'message' => "Cannot delete: $used transactions use this category."]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Category deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
