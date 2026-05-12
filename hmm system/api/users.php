<?php
require_once '../db.php';
requireAdmin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {

    // ========== LIST USERS ==========
    case 'list':
        $result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY role DESC, username");
        $users = [];
        while ($row = $result->fetch_assoc()) $users[] = $row;
        echo json_encode(['success' => true, 'data' => $users]);
        break;

    // ========== ADD USER ==========
    case 'add':
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'member';

        if (!$username || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'සියලුම ක්ෂේත්‍ර පුරවන්න.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'මුරපදය අවම අක්ෂර 6ක් විය යුතුය.']);
            exit;
        }

        if (!in_array($role, ['admin', 'member'])) $role = 'member';

        // Check max 3 users
        $count = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
        if ($count >= 3) {
            echo json_encode(['success' => false, 'message' => 'උපරිම පරිශීලකයන් 3ක් පමණි.']);
            exit;
        }

        // Check duplicate
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'මෙම පරිශීලක නාමය හෝ ඊමේල් දැනටමත් ඇත.']);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $email, $hashed, $role);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'පරිශීලකයා සාර්ථකව එකතු කරන ලදී!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'එකතු කිරීම අසාර්ථක විය.']);
        }
        break;

    // ========== DELETE USER ==========
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Can't delete yourself
        if ($id === $userId) {
            echo json_encode(['success' => false, 'message' => 'ඔබටම ඔබව මකා දැමිය නොහැක.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'පරිශීලකයා මකා දැමීය.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'මකා දැමීම අසාර්ථක විය.']);
        }
        break;

    // ========== RESET PASSWORD ==========
    case 'reset_password':
        $id = intval($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'මුරපදය අවම අක්ෂර 6ක් විය යුතුය.']);
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hashed, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'මුරපදය යළි සකසන ලදී!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'මුරපදය යළි සැකසීම අසාර්ථක විය.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
