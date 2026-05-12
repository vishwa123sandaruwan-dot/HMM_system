<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>කාණ්ඩ – TeachFlow</title>
    <meta name="description" content="ඔබේ ආදායම සහ වියදම් කාණ්ඩ කළමනාකරණය කරන්න.">
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
                <h1>කාණ්ඩ</h1>
                <p>ඔබේ ආදායම සහ වියදම් කාණ්ඩ කළමනාකරණය කරන්න</p>
            </div>
            <div class="topbar-right">
                <button class="btn-primary" onclick="openModal('addCategoryModal')">
                    <i class="fas fa-plus"></i> කාණ්ඩයක් එකතු කරන්න
                </button>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"><h2><i class="fas fa-arrow-up text-green"></i> ආදායම් කාණ්ඩ</h2></div>
            <div class="card-body"><div class="categories-grid" id="incomeCategories"><div class="spinner"></div></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h2><i class="fas fa-arrow-down text-red"></i> වියදම් කාණ්ඩ</h2></div>
            <div class="card-body"><div class="categories-grid" id="expenseCategories"><div class="spinner"></div></div></div>
        </div>
    </main>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="addCategoryModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-tag"></i> කාණ්ඩයක් එකතු කරන්න</h3>
            <button class="modal-close" onclick="closeModal('addCategoryModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addCatForm" onsubmit="return addCategory(event)">
                <div class="form-group">
                    <label><i class="fas fa-pencil-alt"></i> කාණ්ඩ නම</label>
                    <input type="text" name="name" id="catName" required placeholder="උදා: අමතර ආදායම, ඇඳුම්">
                </div>

                <div class="type-selector">
                    <button type="button" class="type-btn active-income" data-type="income" onclick="selectCatType(this, 'income')">
                        <i class="fas fa-arrow-up"></i> ආදායම
                    </button>
                    <button type="button" class="type-btn" data-type="expense" onclick="selectCatType(this, 'expense')">
                        <i class="fas fa-arrow-down"></i> වියදම
                    </button>
                </div>
                <input type="hidden" name="type" id="catType" value="income">

                <div class="form-group">
                    <label><i class="fas fa-icons"></i> අයිකනය</label>
                    <select name="icon" id="catIcon">
                        <option value="fa-tag">🏷 ලේබලය</option>
                        <option value="fa-wallet">💰 පසුම්බිය</option>
                        <option value="fa-briefcase">💼 ව්‍යාපාරය</option>
                        <option value="fa-laptop-code">💻 ෆ්‍රීලාන්ස්</option>
                        <option value="fa-gift">🎁 තෑග්ග</option>
                        <option value="fa-money-bill-wave">💵 මුදල්</option>
                        <option value="fa-shopping-basket">🛒 සිල්ලර</option>
                        <option value="fa-bolt">⚡ විදුලිය</option>
                        <option value="fa-faucet">🚰 ජලය</option>
                        <option value="fa-wifi">📶 අන්තර්ජාලය</option>
                        <option value="fa-home">🏠 ගෙදර</option>
                        <option value="fa-gas-pump">⛽ ඉන්ධන</option>
                        <option value="fa-graduation-cap">🎓 අධ්‍යාපනය</option>
                        <option value="fa-heartbeat">❤ සෞඛ්‍ය</option>
                        <option value="fa-shield-alt">🛡 රක්ෂණය</option>
                        <option value="fa-film">🎬 විනෝදය</option>
                        <option value="fa-utensils">🍽 අවන්හල්</option>
                        <option value="fa-tshirt">👕 ඇඳුම්</option>
                        <option value="fa-phone">📱 දුරකථනය</option>
                        <option value="fa-tools">🔧 අළුත්වැඩියා</option>
                        <option value="fa-baby">👶 දරුවන්</option>
                        <option value="fa-paw">🐾 සතුන්</option>
                        <option value="fa-mosque">🕌 ආගමික</option>
                        <option value="fa-hand-holding-heart">💝 පරිත්‍යාග</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-full" id="addCatBtn">
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
                <p id="deleteMessage">මෙම කාණ්ඩය මකා දැමේ.</p>
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
document.addEventListener('DOMContentLoaded', loadCategories);

function selectCatType(btn, type) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active-income', 'active-expense'));
    btn.classList.add(type === 'income' ? 'active-income' : 'active-expense');
    document.getElementById('catType').value = type;
}

function loadCategories() {
    ajaxGet('api/categories.php?action=list').then(res => {
        if (res.success) {
            renderCategories('incomeCategories', res.data.filter(c => c.type === 'income'), 'income');
            renderCategories('expenseCategories', res.data.filter(c => c.type === 'expense'), 'expense');
        }
    });
}

function renderCategories(id, data, type) {
    const container = document.getElementById(id);
    if (!data || data.length === 0) { container.innerHTML = '<div class="empty-state"><i class="fas fa-tag"></i><p>කාණ්ඩ නැත</p></div>'; return; }
    let html = '';
    data.forEach(c => {
        html += `<div class="category-card">
            <span class="cat-icon ${type==='income'?'income-cat':'expense-cat'}"><i class="fas ${c.icon||'fa-tag'}"></i></span>
            <div><div class="cat-name">${c.name}</div><div class="cat-type">${c.type==='income'?'ආදායම':'වියදම'}</div></div>
            <div class="cat-actions"><button class="btn-danger btn-sm btn-icon" onclick="deleteCategory(${c.id}, '${c.name}')" title="මකන්න"><i class="fas fa-trash"></i></button></div>
        </div>`;
    });
    container.innerHTML = html;
}

function addCategory(e) {
    e.preventDefault();
    const btn = document.getElementById('addCatBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> එකතු වෙමින්...';
    ajaxPost('api/categories.php', { action: 'add', name: document.getElementById('catName').value, type: document.getElementById('catType').value, icon: document.getElementById('catIcon').value }).then(res => {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න';
        if (res.success) { showToast('කාණ්ඩය එකතු කරන ලදී!', 'success'); closeModal('addCategoryModal'); document.getElementById('addCatForm').reset(); document.getElementById('catType').value = 'income'; document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active-income','active-expense')); document.querySelector('.type-btn[data-type="income"]').classList.add('active-income'); loadCategories(); }
        else { showToast(res.message, 'error'); }
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න'; showToast('ජාල දෝෂයක්.', 'error'); });
    return false;
}

function deleteCategory(id, name) {
    confirmDelete(`"${name}" කාණ්ඩය මකන්නද? ගනුදෙනු තිබේ නම් මකා දැමිය නොහැක.`, function() {
        ajaxPost('api/categories.php', { action: 'delete', id: id }).then(res => {
            if (res.success) { showToast('කාණ්ඩය මකා දැමීය.', 'success'); loadCategories(); }
            else { showToast(res.message, 'error'); }
        });
    });
}
</script>
</body>
</html>
