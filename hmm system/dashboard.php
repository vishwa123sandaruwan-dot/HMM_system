<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>මුල් පිටුව – TeachFlow</title>
    <meta name="description" content="ඔබේ ගෘහ මුදල් සාරාංශය බලන්න - ආදායම, වියදම් සහ ඉතිරිකිරීම්.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>body{font-family:'Noto Sans Sinhala','Inter',sans-serif;}</style>
</head>
<body>
<div class="app-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h1>මුල් පිටුව</h1>
                <p>ආයුබෝවන් <?= htmlspecialchars($_SESSION['username']) ?>! මෙන්න ඔබේ මුදල් සාරාංශය.</p>
            </div>
            <div class="topbar-right">
                <div class="filter-group">
                    <label>මාසය:</label>
                    <input type="month" id="dashMonth" value="<?= date('Y-m') ?>" onchange="loadDashboard()">
                </div>
                <button class="btn-primary" onclick="openModal('addTransactionModal')">
                    <i class="fas fa-plus"></i> ගනුදෙනුවක් එකතු කරන්න
                </button>
            </div>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="stat-box">
                <div class="icon green"><i class="fas fa-arrow-up"></i></div>
                <div class="label">මාසික ආදායම</div>
                <div class="value" id="statIncome">Rs. 0.00</div>
            </div>
            <div class="stat-box">
                <div class="icon red"><i class="fas fa-arrow-down"></i></div>
                <div class="label">මාසික වියදම්</div>
                <div class="value" id="statExpense">Rs. 0.00</div>
            </div>
            <div class="stat-box">
                <div class="icon blue"><i class="fas fa-piggy-bank"></i></div>
                <div class="label">මාසික ඉතිරිය</div>
                <div class="value" id="statSavings">Rs. 0.00</div>
            </div>
            <div class="stat-box">
                <div class="icon purple"><i class="fas fa-chart-line"></i></div>
                <div class="label">මුළු ශේෂය</div>
                <div class="value" id="statBalance">Rs. 0.00</div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-area"></i> ආදායම vs වියදම් ප්‍රවණතාව</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-pie"></i> කාණ්ඩ අනුව වියදම්</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="pieChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> මෑත ගනුදෙනු</h2>
                <a href="transactions.php" class="btn-secondary btn-sm">සියල්ල බලන්න <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body no-pad" id="recentTransactions">
                <div class="empty-state"><div class="spinner"></div><p>පූරණය වෙමින්...</p></div>
            </div>
        </div>
    </main>
</div>

<!-- Add Transaction Modal -->
<div class="modal-overlay" id="addTransactionModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> ගනුදෙනුවක් එකතු කරන්න</h3>
            <button class="modal-close" onclick="closeModal('addTransactionModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addTxForm" onsubmit="return addTransaction(event)">
                <div class="type-selector">
                    <button type="button" class="type-btn active-income" data-type="income" onclick="selectType(this, 'income')">
                        <i class="fas fa-arrow-up"></i> ආදායම
                    </button>
                    <button type="button" class="type-btn" data-type="expense" onclick="selectType(this, 'expense')">
                        <i class="fas fa-arrow-down"></i> වියදම
                    </button>
                </div>
                <input type="hidden" name="type" id="txType" value="income">

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-money-bill"></i> මුදල (LKR)</label>
                        <input type="number" name="amount" id="txAmount" step="0.01" min="0.01" required placeholder="0.00" oninput="handleAmountInput(this)">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> දිනය</label>
                        <input type="date" name="transaction_date" id="txDate" required data-today>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> කාණ්ඩය</label>
                    <select name="category_id" id="txCategory" required>
                        <option value="">කාණ්ඩයක් තෝරන්න...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-pencil-alt"></i> විස්තරය</label>
                    <textarea name="description" id="txDesc" placeholder="විස්තරයක් ඇතුලත් කරන්න (අවශ්‍ය නැත)" rows="2"></textarea>
                </div>

                <button type="submit" class="btn-primary btn-full" id="addTxBtn">
                    <i class="fas fa-check"></i> එකතු කරන්න
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-body">
            <div class="confirm-dialog">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>ඔබට විශ්වාසද?</h3>
                <p id="deleteMessage">මෙම ක්‍රියාව ආපසු හැරවිය නොහැක.</p>
                <div class="confirm-actions">
                    <button class="btn-secondary" onclick="closeModal('deleteModal')">අවලංගු කරන්න</button>
                    <button class="btn-danger" id="confirmDeleteBtn">මකන්න</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
let trendChart, pieChart;
const chartColors = ['#7c5cfc','#2dd4a8','#ff5c7c','#fbbf24','#38bdf8','#a78bfa','#fb923c','#ec4899','#14b8a6','#f97316','#8b5cf6','#06b6d4'];

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
    loadCategories('income');
    const dateInput = document.getElementById('txDate');
    if (dateInput && !dateInput.value) dateInput.value = getToday();
});

function selectType(btn, type) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active-income', 'active-expense'));
    btn.classList.add(type === 'income' ? 'active-income' : 'active-expense');
    document.getElementById('txType').value = type;
    loadCategories(type);
}

function loadCategories(type) {
    ajaxGet('api/categories.php?action=list&type=' + type).then(res => {
        if (res.success) {
            const select = document.getElementById('txCategory');
            select.innerHTML = '<option value="">කාණ්ඩයක් තෝරන්න...</option>';
            res.data.forEach(c => { select.innerHTML += `<option value="${c.id}">${c.name}</option>`; });
        }
    });
}

function loadDashboard() {
    const month = document.getElementById('dashMonth').value;
    ajaxGet('api/transactions.php?action=dashboard&month=' + month).then(res => {
        if (res.success) {
            document.getElementById('statIncome').textContent = formatLKR(res.monthlyIncome);
            document.getElementById('statExpense').textContent = formatLKR(res.monthlyExpense);
            document.getElementById('statSavings').textContent = formatLKR(res.savings);
            document.getElementById('statBalance').textContent = formatLKR(res.totalSavings);
            document.getElementById('statSavings').style.color = res.savings >= 0 ? 'var(--green)' : 'var(--red)';
            document.getElementById('statBalance').style.color = res.totalSavings >= 0 ? 'var(--green)' : 'var(--red)';
            renderTrendChart(res.monthlyTrend);
            renderPieChart(res.expenseByCategory);
            renderRecentTransactions(res.recent);
        }
    });
}

function renderTrendChart(data) {
    const months = [...new Set(data.map(d => d.month))].sort();
    const incomeData = months.map(m => { const i = data.find(d => d.month === m && d.type === 'income'); return i ? parseFloat(i.total) : 0; });
    const expenseData = months.map(m => { const i = data.find(d => d.month === m && d.type === 'expense'); return i ? parseFloat(i.total) : 0; });
    const labels = months.map(m => { const [y, mo] = m.split('-'); return new Date(y, mo - 1).toLocaleDateString('si-LK', { month: 'short', year: '2-digit' }); });

    if (trendChart) trendChart.destroy();
    trendChart = new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'ආදායම', data: incomeData, borderColor: '#2dd4a8', backgroundColor: 'rgba(45,212,168,0.1)', fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#2dd4a8' },
                { label: 'වියදම්', data: expenseData, borderColor: '#ff5c7c', backgroundColor: 'rgba(255,92,124,0.1)', fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#ff5c7c' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#9a9ab8', font: { family: 'Noto Sans Sinhala' } } }, tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rs. ' + ctx.parsed.y.toLocaleString() } } },
            scales: { x: { ticks: { color: '#6a6a88' }, grid: { color: 'rgba(255,255,255,0.04)' } }, y: { ticks: { color: '#6a6a88', callback: v => 'Rs.' + (v/1000).toFixed(0) + 'K' }, grid: { color: 'rgba(255,255,255,0.04)' } } }
        }
    });
}

function renderPieChart(data) {
    if (pieChart) pieChart.destroy();
    if (!data || data.length === 0) {
        document.getElementById('pieChart').parentElement.innerHTML = '<div class="empty-state"><i class="fas fa-chart-pie"></i><h3>වියදම් නැත</h3><p>වියදම් ගනුදෙනු එකතු කරන්න.</p></div>';
        return;
    }
    pieChart = new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: { labels: data.map(d => d.name), datasets: [{ data: data.map(d => parseFloat(d.total)), backgroundColor: chartColors.slice(0, data.length), borderWidth: 0, hoverOffset: 8 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right', labels: { color: '#9a9ab8', font: { family: 'Noto Sans Sinhala', size: 11 }, padding: 12, usePointStyle: true } }, tooltip: { callbacks: { label: ctx => ctx.label + ': Rs. ' + ctx.parsed.toLocaleString() } } } }
    });
}

function renderRecentTransactions(transactions) {
    const container = document.getElementById('recentTransactions');
    if (!transactions || transactions.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-receipt"></i><h3>ගනුදෙනු නැත</h3><p>ඔබේ පළමු ආදායම හෝ වියදම එකතු කරන්න.</p></div>';
        return;
    }
    let html = '';
    transactions.forEach(tx => {
        const isIncome = tx.type === 'income';
        const dateStr = new Date(tx.transaction_date).toLocaleDateString('si-LK', { month: 'short', day: 'numeric' });
        html += `
        <div class="transaction-item">
            <div class="tx-icon ${tx.type}"><i class="fas ${tx.category_icon || 'fa-tag'}"></i></div>
            <div class="tx-details">
                <div class="tx-cat">${tx.category_name || 'නොදන්නා'}</div>
                <div class="tx-desc">${tx.description || '—'}${tx.added_by ? ' <span style="color:var(--text-muted);font-size:11px;">(' + tx.added_by + ')</span>' : ''}</div>
            </div>
            <div class="tx-meta">
                <div class="tx-amount ${isIncome ? 'amount-income' : 'amount-expense'}">${isIncome ? '+' : '-'} ${formatLKR(tx.amount)}</div>
                <div class="tx-date">${dateStr}</div>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function addTransaction(e) {
    e.preventDefault();
    const btn = document.getElementById('addTxBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> එකතු වෙමින්...';

    ajaxPost('api/transactions.php', {
        action: 'add', type: document.getElementById('txType').value,
        category_id: document.getElementById('txCategory').value,
        amount: document.getElementById('txAmount').value,
        description: document.getElementById('txDesc').value,
        transaction_date: document.getElementById('txDate').value
    }).then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න';
        if (res.success) {
            showToast('ගනුදෙනුව සාර්ථකව එකතු කරන ලදී!', 'success');
            closeModal('addTransactionModal');
            document.getElementById('addTxForm').reset();
            document.getElementById('txDate').value = getToday();
            document.getElementById('txType').value = 'income';
            document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active-income', 'active-expense'));
            document.querySelector('.type-btn[data-type="income"]').classList.add('active-income');
            loadCategories('income');
            loadDashboard();
        } else { showToast(res.message, 'error'); }
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න'; showToast('ජාල දෝෂයක්. නැවත උත්සාහ කරන්න.', 'error'); });
    return false;
}
</script>
</body>
</html>
