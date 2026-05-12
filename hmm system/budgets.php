<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>අයවැය – TeachFlow</title>
    <meta name="description" content="ඔබේ ගෙදර වියදම් කාණ්ඩ සඳහා මාසික අයවැය සකසන්න සහ නිරීක්ෂණය කරන්න.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Noto Sans Sinhala','Inter',sans-serif;}</style>
</head>
<body>
<div class="app-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h1>මාසික අයවැය</h1>
                <p>වියදම් කාණ්ඩ සඳහා වියදම් සීමා සකසන්න</p>
            </div>
            <div class="topbar-right">
                <div class="filter-group">
                    <label>මාසය:</label>
                    <input type="month" id="budgetMonth" value="<?= date('Y-m') ?>" onchange="loadBudgets()">
                </div>
                <button class="btn-primary" onclick="openModal('addBudgetModal')">
                    <i class="fas fa-plus"></i> අයවැය සකසන්න
                </button>
            </div>
        </div>

        <div class="report-summary">
            <div class="report-item">
                <div class="report-label">මුළු අයවැය</div>
                <div class="report-val text-blue" id="totalBudgeted">Rs. 0.00</div>
            </div>
            <div class="report-item">
                <div class="report-label">මුළු වැය කළ</div>
                <div class="report-val text-red" id="totalSpent">Rs. 0.00</div>
            </div>
            <div class="report-item">
                <div class="report-label">ඉතිරිය</div>
                <div class="report-val text-green" id="totalRemaining">Rs. 0.00</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2><i class="fas fa-bullseye"></i> අයවැය ප්‍රගතිය</h2></div>
            <div class="card-body" id="budgetList">
                <div class="empty-state"><div class="spinner"></div><p>පූරණය වෙමින්...</p></div>
            </div>
        </div>
    </main>
</div>

<!-- Add Budget Modal -->
<div class="modal-overlay" id="addBudgetModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-bullseye"></i> අයවැය සකසන්න</h3>
            <button class="modal-close" onclick="closeModal('addBudgetModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addBudgetForm" onsubmit="return saveBudget(event)">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> වියදම් කාණ්ඩය</label>
                    <select name="category_id" id="budgetCategory" required>
                        <option value="">කාණ්ඩයක් තෝරන්න...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> අයවැය මුදල (LKR)</label>
                    <input type="number" name="amount" id="budgetAmount" step="0.01" min="1" required placeholder="වියදම් සීමාව ඇතුලත් කරන්න">
                </div>
                <button type="submit" class="btn-primary btn-full" id="saveBudgetBtn">
                    <i class="fas fa-check"></i> සුරකින්න
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
                <p id="deleteMessage">මෙම අයවැය ඉවත් කෙරේ.</p>
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
document.addEventListener('DOMContentLoaded', function() { loadExpenseCategories(); loadBudgets(); });

function loadExpenseCategories() {
    ajaxGet('api/categories.php?action=list&type=expense').then(res => {
        if (res.success) {
            const select = document.getElementById('budgetCategory');
            select.innerHTML = '<option value="">කාණ්ඩයක් තෝරන්න...</option>';
            res.data.forEach(c => { select.innerHTML += `<option value="${c.id}">${c.name}</option>`; });
        }
    });
}

function loadBudgets() {
    const month = document.getElementById('budgetMonth').value;
    ajaxGet('api/budgets.php?action=list&month=' + month).then(res => { if (res.success) renderBudgets(res.data); });
}

function renderBudgets(data) {
    const container = document.getElementById('budgetList');
    if (!data || data.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-bullseye"></i><h3>අයවැය සකසා නැත</h3><p>ඔබේ වියදම් කාණ්ඩ සඳහා වියදම් සීමා එකතු කිරීමට "අයවැය සකසන්න" ක්ලික් කරන්න.</p></div>';
        document.getElementById('totalBudgeted').textContent = 'Rs. 0.00';
        document.getElementById('totalSpent').textContent = 'Rs. 0.00';
        document.getElementById('totalRemaining').textContent = 'Rs. 0.00';
        return;
    }
    let totalBudget = 0, totalSpent = 0, html = '';
    data.forEach(b => {
        const budget = parseFloat(b.amount), spent = parseFloat(b.spent), remaining = budget - spent;
        const pct = budget > 0 ? Math.min((spent / budget) * 100, 100) : 0;
        const barColor = pct >= 90 ? 'red' : pct >= 70 ? 'yellow' : 'green';
        totalBudget += budget; totalSpent += spent;
        html += `
        <div class="budget-bar">
            <div class="budget-info">
                <span class="cat-name"><span class="cat-icon expense-cat" style="display:inline-flex;width:24px;height:24px;font-size:11px;margin-right:6px;"><i class="fas ${b.category_icon||'fa-tag'}"></i></span> ${b.category_name}</span>
                <span class="budget-amt">
                    ${formatLKR(spent)} / ${formatLKR(budget)}
                    <span style="margin-left:8px;color:${remaining>=0?'var(--green)':'var(--red)'};">(${remaining>=0?formatLKR(remaining)+' ඉතිරිය':formatLKR(Math.abs(remaining))+' ඉක්මවා ඇත!'})</span>
                    <button class="btn-danger btn-sm btn-icon" style="margin-left:8px;" onclick="deleteBudget(${b.id})" title="ඉවත් කරන්න"><i class="fas fa-trash"></i></button>
                </span>
            </div>
            <div class="progress-bar"><div class="progress-fill ${barColor}" style="width:${pct}%;"></div></div>
        </div>`;
    });
    container.innerHTML = html;
    document.getElementById('totalBudgeted').textContent = formatLKR(totalBudget);
    document.getElementById('totalSpent').textContent = formatLKR(totalSpent);
    const rem = totalBudget - totalSpent;
    document.getElementById('totalRemaining').textContent = formatLKR(Math.abs(rem));
    document.getElementById('totalRemaining').style.color = rem >= 0 ? 'var(--green)' : 'var(--red)';
}

function saveBudget(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBudgetBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> සුරැකෙමින්...';
    ajaxPost('api/budgets.php', { action: 'set', category_id: document.getElementById('budgetCategory').value, amount: document.getElementById('budgetAmount').value, month: document.getElementById('budgetMonth').value }).then(res => {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> සුරකින්න';
        if (res.success) { showToast('අයවැය සුරකින ලදී!', 'success'); closeModal('addBudgetModal'); document.getElementById('addBudgetForm').reset(); loadBudgets(); }
        else { showToast(res.message, 'error'); }
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> සුරකින්න'; showToast('ජාල දෝෂයක්.', 'error'); });
    return false;
}

function deleteBudget(id) {
    confirmDelete('මෙම අයවැය ඉවත් කෙරේ.', function() {
        ajaxPost('api/budgets.php', { action: 'delete', id: id }).then(res => {
            if (res.success) { showToast('අයවැය ඉවත් කරන ලදී.', 'success'); loadBudgets(); }
            else { showToast(res.message, 'error'); }
        });
    });
}
</script>
</body>
</html>
