<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>වාර්තා – TeachFlow</title>
    <meta name="description" content="ඔබේ ගෘහ මුදල් වාර්තා විශ්ලේෂණය කරන්න.">
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
                <h1>මුදල් වාර්තා</h1>
                <p>ඔබේ ආදායම සහ වියදම් රටා විශ්ලේෂණය කරන්න</p>
            </div>
            <div class="topbar-right">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>සිට:</label>
                        <input type="date" id="reportFrom" value="<?= date('Y-m-01') ?>" onchange="loadReport()">
                    </div>
                    <div class="filter-group">
                        <label>දක්වා:</label>
                        <input type="date" id="reportTo" value="<?= date('Y-m-d') ?>" onchange="loadReport()">
                    </div>
                    <button class="btn-secondary btn-sm" onclick="setQuickRange('month')">මෙම මාසය</button>
                    <button class="btn-secondary btn-sm" onclick="setQuickRange('quarter')">මාස 3ක්</button>
                    <button class="btn-secondary btn-sm" onclick="setQuickRange('year')">මෙම වසර</button>
                </div>
            </div>
        </div>

        <div class="report-summary">
            <div class="report-item">
                <div class="report-label">මුළු ආදායම</div>
                <div class="report-val text-green" id="rpIncome">Rs. 0.00</div>
            </div>
            <div class="report-item">
                <div class="report-label">මුළු වියදම්</div>
                <div class="report-val text-red" id="rpExpense">Rs. 0.00</div>
            </div>
            <div class="report-item">
                <div class="report-label">ශුද්ධ ඉතිරිය</div>
                <div class="report-val" id="rpSavings">Rs. 0.00</div>
            </div>
            <div class="report-item">
                <div class="report-label">ඉතිරි අනුපාතය</div>
                <div class="report-val text-blue" id="rpRate">0%</div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-chart-bar"></i> දෛනික ආදායම vs වියදම්</h2></div>
                <div class="card-body"><div class="chart-container"><canvas id="dailyChart"></canvas></div></div>
            </div>
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-chart-pie"></i> වියදම් බෙදීම</h2></div>
                <div class="card-body"><div class="chart-container"><canvas id="expPieChart"></canvas></div></div>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-arrow-up text-green"></i> ආදායම් විස්තරය</h2></div>
                <div class="card-body no-pad">
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>කාණ්ඩය</th><th style="text-align:right;">මුදල</th><th style="text-align:right;">කොටස</th></tr></thead>
                            <tbody id="incomeBreakdown"><tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">පූරණය වෙමින්...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-arrow-down text-red"></i> වියදම් විස්තරය</h2></div>
                <div class="card-body no-pad">
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>කාණ්ඩය</th><th style="text-align:right;">මුදල</th><th style="text-align:right;">කොටස</th></tr></thead>
                            <tbody id="expenseBreakdown"><tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">පූරණය වෙමින්...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="assets/js/main.js"></script>
<script>
let dailyChart, expPieChart;
const colors = ['#7c5cfc','#2dd4a8','#ff5c7c','#fbbf24','#38bdf8','#a78bfa','#fb923c','#ec4899','#14b8a6','#f97316','#8b5cf6','#06b6d4'];

document.addEventListener('DOMContentLoaded', loadReport);

function setQuickRange(range) {
    const now = new Date();
    let from;
    if (range === 'month') from = new Date(now.getFullYear(), now.getMonth(), 1);
    else if (range === 'quarter') from = new Date(now.getFullYear(), now.getMonth() - 2, 1);
    else from = new Date(now.getFullYear(), 0, 1);
    document.getElementById('reportFrom').value = from.toISOString().split('T')[0];
    document.getElementById('reportTo').value = now.toISOString().split('T')[0];
    loadReport();
}

function loadReport() {
    const from = document.getElementById('reportFrom').value;
    const to = document.getElementById('reportTo').value;

    ajaxGet(`api/transactions.php?action=report&date_from=${from}&date_to=${to}`).then(res => {
        if (res.success) {
            const inc = parseFloat(res.totalIncome), exp = parseFloat(res.totalExpense), sav = parseFloat(res.savings);
            const rate = inc > 0 ? ((sav / inc) * 100).toFixed(1) : 0;
            document.getElementById('rpIncome').textContent = formatLKR(inc);
            document.getElementById('rpExpense').textContent = formatLKR(exp);
            document.getElementById('rpSavings').textContent = formatLKR(sav);
            document.getElementById('rpSavings').style.color = sav >= 0 ? 'var(--green)' : 'var(--red)';
            document.getElementById('rpRate').textContent = rate + '%';
            renderDailyChart(res.dailyBreakdown);
            renderExpPie(res.expenseByCategory);
            renderBreakdown('incomeBreakdown', res.incomeByCategory, inc);
            renderBreakdown('expenseBreakdown', res.expenseByCategory, exp);
        }
    });
}

function renderDailyChart(data) {
    const dates = [...new Set(data.map(d => d.date))].sort();
    const incData = dates.map(dt => { const i = data.find(d => d.date === dt && d.type === 'income'); return i ? parseFloat(i.total) : 0; });
    const expData = dates.map(dt => { const i = data.find(d => d.date === dt && d.type === 'expense'); return i ? parseFloat(i.total) : 0; });
    const labels = dates.map(d => new Date(d).toLocaleDateString('si-LK', { month: 'short', day: 'numeric' }));

    if (dailyChart) dailyChart.destroy();
    dailyChart = new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: { labels, datasets: [
            { label: 'ආදායම', data: incData, backgroundColor: 'rgba(45,212,168,0.7)', borderRadius: 4 },
            { label: 'වියදම්', data: expData, backgroundColor: 'rgba(255,92,124,0.7)', borderRadius: 4 }
        ]},
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#9a9ab8', font: { family: 'Noto Sans Sinhala' } } }, tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rs. ' + ctx.parsed.y.toLocaleString() } } },
            scales: { x: { ticks: { color: '#6a6a88', maxRotation: 45 }, grid: { color: 'rgba(255,255,255,0.04)' } }, y: { ticks: { color: '#6a6a88', callback: v => 'Rs.' + (v/1000).toFixed(0) + 'K' }, grid: { color: 'rgba(255,255,255,0.04)' } } }
        }
    });
}

function renderExpPie(data) {
    if (expPieChart) expPieChart.destroy();
    if (!data || data.length === 0) { document.getElementById('expPieChart').parentElement.innerHTML = '<div class="empty-state"><i class="fas fa-chart-pie"></i><p>වියදම් දත්ත නැත</p></div>'; return; }
    expPieChart = new Chart(document.getElementById('expPieChart'), {
        type: 'doughnut',
        data: { labels: data.map(d => d.name), datasets: [{ data: data.map(d => parseFloat(d.total)), backgroundColor: colors.slice(0, data.length), borderWidth: 0, hoverOffset: 8 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'right', labels: { color: '#9a9ab8', font: { family: 'Noto Sans Sinhala', size: 11 }, padding: 10, usePointStyle: true } }, tooltip: { callbacks: { label: ctx => ctx.label + ': Rs. ' + ctx.parsed.toLocaleString() } } } }
    });
}

function renderBreakdown(id, data, total) {
    const tbody = document.getElementById(id);
    if (!data || data.length === 0) { tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">මෙම කාලය සඳහා දත්ත නැත</td></tr>'; return; }
    let html = '';
    data.forEach((item, i) => {
        const amt = parseFloat(item.total);
        const share = total > 0 ? ((amt / total) * 100).toFixed(1) : 0;
        html += `<tr><td><span class="cat-icon" style="background:${colors[i%colors.length]}22;color:${colors[i%colors.length]};"><i class="fas ${item.icon||'fa-tag'}"></i></span> ${item.name}</td><td style="text-align:right;font-weight:600;">${formatLKR(amt)}</td><td style="text-align:right;color:var(--text-muted);">${share}%</td></tr>`;
    });
    tbody.innerHTML = html;
}
</script>
</body>
</html>
