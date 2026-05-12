<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'පරිශීලක නාමය හෝ මුරපදය වැරදියි.';
        }
    } else {
        $error = 'පරිශීලක නාමය හෝ මුරපදය වැරදියි.';
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeachFlow – ගෘහ මුදල් කළමනාකරණ පද්ධතිය</title>
    <meta name="description" content="ශ්‍රී ලාංකික පවුල් සඳහා නිවස මුදල් කළමනාකරණය.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body,.auth-card,.form-group input,.btn-primary{font-family:'Noto Sans Sinhala','Inter',sans-serif;}</style>
</head>
<body class="auth-page">

<div class="auth-bg">
    <div class="auth-orb orb1"></div>
    <div class="auth-orb orb2"></div>
    <div class="auth-orb orb3"></div>
</div>

<div class="auth-container">
    <div class="auth-left">
        <div class="brand-logo">
            <div class="logo-icon"><i class="fas fa-coins"></i></div>
            <div>
                <h1>TeachFlow</h1>
                <p>ගෘහ මුදල් කළමනාකරණය</p>
            </div>
        </div>
        <div class="auth-illustration">
            <div class="stat-card floating">
                <i class="fas fa-arrow-up text-green"></i>
                <div><span class="stat-label">මුළු ආදායම</span><span class="stat-val">Rs. 125,000</span></div>
            </div>
            <div class="stat-card floating delay1">
                <i class="fas fa-arrow-down text-red"></i>
                <div><span class="stat-label">මුළු වියදම්</span><span class="stat-val">Rs. 87,400</span></div>
            </div>
            <div class="stat-card floating delay2">
                <i class="fas fa-piggy-bank text-purple"></i>
                <div><span class="stat-label">ඉතිරිය</span><span class="stat-val">Rs. 37,600</span></div>
            </div>
        </div>
        <div class="auth-tagline">
            <h2>ඔබේ ගෙදර මුදල් පාලනය කරන්න</h2>
            <p>developed by vishwa sandaruwan</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <h2 style="text-align:center; margin-bottom: 24px; font-size: 20px; font-weight: 700;">
                <i class="fas fa-sign-in-alt" style="color: var(--accent);"></i> පිවිසෙන්න
            </h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> පරිශීලක නාමය</label>
                    <input type="text" name="username" required placeholder="පරිශීලක නාමය ඇතුලත් කරන්න" autocomplete="username">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> මුරපදය</label>
                    <div class="input-pw">
                        <input type="password" name="password" id="loginPw" required placeholder="මුරපදය ඇතුලත් කරන්න" autocomplete="current-password">
                        <button type="button" onclick="togglePw('loginPw', this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i> පිවිසෙන්න
                </button>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; }
    else { input.type = 'password'; btn.innerHTML = '<i class="fas fa-eye"></i>'; }
}
</script>
</body>
</html>
