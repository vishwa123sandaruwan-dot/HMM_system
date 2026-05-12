<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">
            <div class="logo-icon"><i class="fas fa-coins"></i></div>
            <div>
                <h1>TeachFlow</h1>
                <p>ගෘහ මුදල් කළමනාකරණය</p>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">ප්‍රධාන</span>
        <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> මුල් පිටුව
        </a>
        <a href="transactions.php" class="nav-item <?= $currentPage === 'transactions' ? 'active' : '' ?>">
            <i class="fas fa-exchange-alt"></i> ගනුදෙනු
        </a>

        <span class="nav-label">විශ්ලේෂණය</span>
        <a href="reports.php" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> වාර්තා
        </a>
        <a href="budgets.php" class="nav-item <?= $currentPage === 'budgets' ? 'active' : '' ?>">
            <i class="fas fa-bullseye"></i> අයවැය
        </a>

        <span class="nav-label">සැකසුම්</span>
        <a href="categories.php" class="nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> කාණ්ඩ
        </a>

        <?php if (isAdmin()): ?>
        <span class="nav-label">පරිපාලක</span>
        <a href="users.php" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> පරිශීලකයන්
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                <div class="user-role"><?= isAdmin() ? 'පරිපාලක (Admin)' : 'ගෘහ සාමාජික' ?></div>
            </div>
        </div>
        <a href="logout.php" class="nav-item" style="margin-top: 8px; color: var(--red);">
            <i class="fas fa-sign-out-alt"></i> පිටවන්න
        </a>
    </div>
</aside>
