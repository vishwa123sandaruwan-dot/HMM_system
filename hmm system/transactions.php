<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ගනුදෙනු – TeachFlow</title>
    <meta name="description" content="ඔබේ සියලුම ආදායම සහ වියදම් ගනුදෙනු බලන්න, එකතු කරන්න සහ කළමනාකරණය කරන්න.">
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
                <h1>ගනුදෙනු</h1>
                <p>ඔබේ සියලුම ආදායම සහ වියදම් වාර්තා කළමනාකරණය කරන්න</p>
            </div>
            <div class="topbar-right">
                <button class="btn-primary" onclick="openModal('addTransactionModal')">
                    <i class="fas fa-plus"></i> ගනුදෙනුවක් එකතු කරන්න
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>වර්ගය:</label>
                        <select id="filterType" onchange="loadTransactions()">
                            <option value="">සියල්ල</option>
                            <option value="income">ආදායම</option>
                            <option value="expense">වියදම</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>කාණ්ඩය:</label>
                        <select id="filterCategory" onchange="loadTransactions()">
                            <option value="">සියලු කාණ්ඩ</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>සිට:</label>
                        <input type="date" id="filterFrom" onchange="loadTransactions()">
                    </div>
                    <div class="filter-group">
                        <label>දක්වා:</label>
                        <input type="date" id="filterTo" onchange="loadTransactions()">
                    </div>
                    <button class="btn-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> මකන්න
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> සියලුම ගනුදෙනු</h2>
                <span id="txCount" style="font-size: 13px; color: var(--text-muted);">වාර්තා 0ක්</span>
            </div>
            <div class="card-body no-pad">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>දිනය</th>
                                <th>කාණ්ඩය</th>
                                <th>විස්තරය</th>
                                <th>වර්ගය</th>
                                <th>එකතු කළේ</th>
                                <th style="text-align:right;">මුදල</th>
                                <th style="text-align:center;">ක්‍රියා</th>
                            </tr>
                        </thead>
                        <tbody id="txTableBody">
                            <tr><td colspan="7"><div class="empty-state"><div class="spinner"></div><p>පූරණය වෙමින්...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pagination" id="txPagination"></div>
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
                        <input type="number" name="amount" id="txAmount" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> දිනය</label>
                        <input type="date" name="transaction_date" id="txDate" required>
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
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('txDate').value = getToday();
    loadAllCategories();
    loadModalCategories('income');
    loadTransactions();
});

function selectType(btn, type) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active-income', 'active-expense'));
    btn.classList.add(type === 'income' ? 'active-income' : 'active-expense');
    document.getElementById('txType').value = type;
    loadModalCategories(type);
}

function loadModalCategories(type) {
    ajaxGet('api/categories.php?action=list&type=' + type).then(res => {
        if (res.success) {
            const select = document.getElementById('txCategory');
            select.innerHTML = '<option value="">කාණ්ඩයක් තෝරන්න...</option>';
            res.data.forEach(c => { select.innerHTML += `<option value="${c.id}">${c.name}</option>`; });
        }
    });
}

function loadAllCategories() {
    ajaxGet('api/categories.php?action=list').then(res => {
        if (res.success) {
            const select = document.getElementById('filterCategory');
            select.innerHTML = '<option value="">සියලු කාණ්ඩ</option>';
            res.data.forEach(c => { select.innerHTML += `<option value="${c.id}">[${c.type === 'income' ? 'ආදායම' : 'වියදම'}] ${c.name}</option>`; });
        }
    });
}

function loadTransactions(page) {
    if (page) currentPage = page;
    const type = document.getElementById('filterType').value;
    const category = document.getElementById('filterCategory').value;
    const dateFrom = document.getElementById('filterFrom').value;
    const dateTo = document.getElementById('filterTo').value;

    let url = `api/transactions.php?action=list&page=${currentPage}`;
    if (type) url += `&type=${type}`;
    if (category) url += `&category_id=${category}`;
    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;

    ajaxGet(url).then(res => {
        if (res.success) { renderTable(res.data); renderPagination(res.page, res.pages, res.total); }
    });
}

function renderTable(data) {
    const tbody = document.getElementById('txTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fas fa-receipt"></i><h3>ගනුදෙනු හමු නොවීය</h3><p>ඔබේ පෙරහන් වෙනස් කරන්න හෝ නව ගනුදෙනුවක් එකතු කරන්න.</p></div></td></tr>';
        return;
    }
    let html = '';
    data.forEach(tx => {
        const isIncome = tx.type === 'income';
        const dateStr = new Date(tx.transaction_date).toLocaleDateString('si-LK', { year: 'numeric', month: 'short', day: 'numeric' });
        html += `
        <tr>
            <td>${dateStr}</td>
            <td><span class="cat-icon ${isIncome ? 'income-cat' : 'expense-cat'}"><i class="fas ${tx.category_icon || 'fa-tag'}"></i></span> ${tx.category_name || 'නොදන්නා'}</td>
            <td style="color: var(--text-secondary);">${tx.description || '—'}</td>
            <td><span class="badge badge-${tx.type}"><i class="fas fa-arrow-${isIncome ? 'up' : 'down'}"></i> ${isIncome ? 'ආදායම' : 'වියදම'}</span></td>
            <td style="font-size:12px; color: var(--text-muted);">${tx.added_by || '—'}</td>
            <td style="text-align:right;" class="${isIncome ? 'amount-income' : 'amount-expense'}">${isIncome ? '+' : '-'} ${formatLKR(tx.amount)}</td>
            <td style="text-align:center;"><button class="btn-danger btn-sm btn-icon" onclick="deleteTx(${tx.id})" title="මකන්න"><i class="fas fa-trash"></i></button></td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function renderPagination(page, pages, total) {
    document.getElementById('txCount').textContent = 'වාර්තා ' + total + 'ක්';
    const container = document.getElementById('txPagination');
    if (pages <= 1) { container.innerHTML = ''; return; }
    let html = '';
    if (page > 1) html += `<button class="page-btn" onclick="loadTransactions(${page-1})"><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= pages; i++) {
        if (i === 1 || i === pages || (i >= page-2 && i <= page+2)) { html += `<button class="page-btn ${i===page?'active':''}" onclick="loadTransactions(${i})">${i}</button>`; }
        else if (i === page-3 || i === page+3) { html += '<span style="color:var(--text-muted)">...</span>'; }
    }
    if (page < pages) html += `<button class="page-btn" onclick="loadTransactions(${page+1})"><i class="fas fa-chevron-right"></i></button>`;
    container.innerHTML = html;
}

function clearFilters() {
    document.getElementById('filterType').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterFrom').value = '';
    document.getElementById('filterTo').value = '';
    currentPage = 1;
    loadTransactions();
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
            loadModalCategories('income');
            loadTransactions();
        } else { showToast(res.message, 'error'); }
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න'; showToast('ජාල දෝෂයක්.', 'error'); });
    return false;
}

function deleteTx(id) {
    confirmDelete('මෙම ගනුදෙනුව ස්ථිරවම මකා දැමෙනු ඇත.', function() {
        ajaxPost('api/transactions.php', { action: 'delete', id: id }).then(res => {
            if (res.success) { showToast('ගනුදෙනුව මකා දැමීය.', 'success'); loadTransactions(); }
            else { showToast(res.message, 'error'); }
        });
    });
}
</script>
</body>
</html>
