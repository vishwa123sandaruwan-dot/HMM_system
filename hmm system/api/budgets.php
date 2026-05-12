<?php
require_once '../db.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$userId = $_SESSION['user_id'];

switch ($action) {

    // ========== LIST BUDGETS ==========
    case 'list':
        $month = $_GET['month'] ?? date('Y-m');

        $stmt = $conn->prepare("SELECT b.*, c.name as category_name, c.icon as category_icon,
            COALESCE((SELECT SUM(t.amount) FROM transactions t WHERE t.category_id = b.category_id AND t.type = 'expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?), 0) as spent
            FROM budgets b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.month = ?
            ORDER BY c.name");
        $stmt->bind_param('ss', $month, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        $budgets = [];
        while ($row = $result->fetch_assoc()) $budgets[] = $row;

        echo json_encode(['success' => true, 'data' => $budgets]);
        break;

    // ========== SET BUDGET ==========
    case 'set':
        $categoryId = intval($_POST['category_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $month = $_POST['month'] ?? date('Y-m');

        if ($categoryId <= 0 || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            exit;
        }

        // Upsert — shared budget (use user_id=1 as placeholder for shared)
        $check = $conn->prepare("SELECT id FROM budgets WHERE category_id = ? AND month = ?");
        $check->bind_param('is', $categoryId, $month);
        $check->execute();
        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE budgets SET amount = ? WHERE category_id = ? AND month = ?");
            $stmt->bind_param('dis', $amount, $categoryId, $month);
        } else {
            $stmt = $conn->prepare("INSERT INTO budgets (user_id, category_id, amount, month) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iids', $userId, $categoryId, $amount, $month);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Budget saved!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save budget.']);
        }
        break;

    // ========== DELETE BUDGET ==========
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Budget deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
