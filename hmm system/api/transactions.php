<?php
require_once '../db.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$userId = $_SESSION['user_id'];

switch ($action) {

    // ========== ADD TRANSACTION ==========
    case 'add':
        $type = $_POST['type'] ?? '';
        $categoryId = intval($_POST['category_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $date = $_POST['transaction_date'] ?? date('Y-m-d');

        if (!in_array($type, ['income', 'expense']) || $categoryId <= 0 || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, amount, description, transaction_date, type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iidsss', $userId, $categoryId, $amount, $description, $date, $type);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Transaction added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add transaction.']);
        }
        break;

    // ========== DELETE TRANSACTION ==========
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaction deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        break;

    // ========== GET TRANSACTIONS (with filters) ==========
    case 'list':
        $type = $_GET['type'] ?? '';
        $category = intval($_GET['category_id'] ?? 0);
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        $params = [];
        $types = '';

        if ($type && in_array($type, ['income', 'expense'])) {
            $where .= " AND t.type = ?";
            $params[] = $type;
            $types .= 's';
        }

        if ($category > 0) {
            $where .= " AND t.category_id = ?";
            $params[] = $category;
            $types .= 'i';
        }

        if ($dateFrom) {
            $where .= " AND t.transaction_date >= ?";
            $params[] = $dateFrom;
            $types .= 's';
        }

        if ($dateTo) {
            $where .= " AND t.transaction_date <= ?";
            $params[] = $dateTo;
            $types .= 's';
        }

        // Count
        $countSql = "SELECT COUNT(*) as total FROM transactions t $where";
        if ($types) {
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $total = $countStmt->get_result()->fetch_assoc()['total'];
        } else {
            $total = $conn->query($countSql)->fetch_assoc()['total'];
        }

        // Data
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, u.username as added_by
                FROM transactions t 
                LEFT JOIN categories c ON t.category_id = c.id 
                LEFT JOIN users u ON t.user_id = u.id
                $where 
                ORDER BY t.transaction_date DESC, t.created_at DESC 
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $transactions,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $limit)
        ]);
        break;

    // ========== DASHBOARD STATS ==========
    case 'dashboard':
        $month = $_GET['month'] ?? date('Y-m');

        // Monthly income (shared — all users)
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
        $stmt->bind_param('s', $month);
        $stmt->execute();
        $monthlyIncome = $stmt->get_result()->fetch_assoc()['total'];

        // Monthly expense
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
        $stmt->bind_param('s', $month);
        $stmt->execute();
        $monthlyExpense = $stmt->get_result()->fetch_assoc()['total'];

        // Total income (all time)
        $totalIncome = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'")->fetch_assoc()['total'];

        // Total expense (all time)
        $totalExpense = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'")->fetch_assoc()['total'];

        // Recent transactions
        $result = $conn->query("SELECT t.*, c.name as category_name, c.icon as category_icon, u.username as added_by FROM transactions t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id ORDER BY t.transaction_date DESC, t.created_at DESC LIMIT 8");
        $recent = [];
        while ($row = $result->fetch_assoc()) $recent[] = $row;

        // Expense by category (this month)
        $stmt = $conn->prepare("SELECT c.name, c.icon, SUM(t.amount) as total FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.type = 'expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ? GROUP BY t.category_id ORDER BY total DESC");
        $stmt->bind_param('s', $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $expenseByCategory = [];
        while ($row = $result->fetch_assoc()) $expenseByCategory[] = $row;

        // Daily trend (last 30 days)
        $result = $conn->query("SELECT transaction_date as date, type, SUM(amount) as total FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY transaction_date, type ORDER BY transaction_date");
        $dailyTrend = [];
        while ($row = $result->fetch_assoc()) $dailyTrend[] = $row;

        // Monthly trend (last 6 months)
        $result = $conn->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, type, SUM(amount) as total FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(transaction_date, '%Y-%m'), type ORDER BY month");
        $monthlyTrend = [];
        while ($row = $result->fetch_assoc()) $monthlyTrend[] = $row;

        echo json_encode([
            'success' => true,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'savings' => $monthlyIncome - $monthlyExpense,
            'totalSavings' => $totalIncome - $totalExpense,
            'recent' => $recent,
            'expenseByCategory' => $expenseByCategory,
            'dailyTrend' => $dailyTrend,
            'monthlyTrend' => $monthlyTrend
        ]);
        break;

    // ========== REPORT DATA ==========
    case 'report':
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        // Income by category
        $stmt = $conn->prepare("SELECT c.name, c.icon, SUM(t.amount) as total FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.type = 'income' AND t.transaction_date BETWEEN ? AND ? GROUP BY t.category_id ORDER BY total DESC");
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        $incomeByCategory = [];
        while ($row = $result->fetch_assoc()) $incomeByCategory[] = $row;

        // Expense by category
        $stmt = $conn->prepare("SELECT c.name, c.icon, SUM(t.amount) as total FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.type = 'expense' AND t.transaction_date BETWEEN ? AND ? GROUP BY t.category_id ORDER BY total DESC");
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        $expByCategory = [];
        while ($row = $result->fetch_assoc()) $expByCategory[] = $row;

        // Totals
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND transaction_date BETWEEN ? AND ?");
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $totalInc = $stmt->get_result()->fetch_assoc()['total'];

        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense' AND transaction_date BETWEEN ? AND ?");
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $totalExp = $stmt->get_result()->fetch_assoc()['total'];

        // Daily breakdown
        $stmt = $conn->prepare("SELECT transaction_date as date, type, SUM(amount) as total FROM transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY transaction_date, type ORDER BY transaction_date");
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        $daily = [];
        while ($row = $result->fetch_assoc()) $daily[] = $row;

        echo json_encode([
            'success' => true,
            'totalIncome' => $totalInc,
            'totalExpense' => $totalExp,
            'savings' => $totalInc - $totalExp,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expByCategory,
            'dailyBreakdown' => $daily
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
