<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'teachflow_hmm');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function formatLKR($amount) {
    return 'Rs. ' . number_format($amount, 2);
}
?>
