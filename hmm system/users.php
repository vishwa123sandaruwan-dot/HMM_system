<?php
require_once 'db.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>පරිශීලකයන් – TeachFlow</title>
    <meta name="description" content="පරිශීලකයන් කළමනාකරණය කරන්න - පරිපාලක පමණි.">
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
                <h1><i class="fas fa-users-cog" style="color: var(--accent);"></i> පරිශීලකයන් කළමනාකරණය</h1>
                <p>පරිශීලකයන් එකතු කරන්න, මකන්න හෝ මුරපද යළි සකසන්න (උපරිම 3ක්)</p>
            </div>
            <div class="topbar-right">
                <span id="userCount" class="topbar-date"><i class="fas fa-users"></i> 0/3 පරිශීලකයන්</span>
                <button class="btn-primary" id="addUserBtn" onclick="openModal('addUserModal')">
                    <i class="fas fa-user-plus"></i> පරිශීලකයෙකු එකතු කරන්න
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> සියලුම පරිශීලකයන්</h2>
            </div>
            <div class="card-body no-pad">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>පරිශීලකයා</th>
                                <th>ඊමේල්</th>
                                <th>තනතුර</th>
                                <th>එකතු කළ දිනය</th>
                                <th style="text-align:center;">ක්‍රියා</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr><td colspan="5"><div class="empty-state"><div class="spinner"></div><p>පූරණය වෙමින්...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> පරිශීලකයෙකු එකතු කරන්න</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addUserForm" onsubmit="return addUser(event)">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> පරිශීලක නාමය</label>
                    <input type="text" name="username" id="newUsername" required placeholder="පරිශීලක නාමයක් ඇතුලත් කරන්න">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> ඊමේල්</label>
                    <input type="email" name="email" id="newEmail" required placeholder="ඊමේල් ඇතුලත් කරන්න">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> මුරපදය</label>
                    <input type="password" name="password" id="newPassword" required placeholder="අවම අක්ෂර 6ක්" minlength="6">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> තනතුර</label>
                    <select name="role" id="newRole">
                        <option value="member">සාමාජික (Member)</option>
                        <option value="admin">පරිපාලක (Admin)</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary btn-full" id="submitAddUser">
                    <i class="fas fa-check"></i> එකතු කරන්න
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal-overlay" id="resetPwModal">
    <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> මුරපදය යළි සකසන්න</h3>
            <button class="modal-close" onclick="closeModal('resetPwModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-secondary); margin-bottom:16px;" id="resetPwUser"></p>
            <form id="resetPwForm" onsubmit="return resetPassword(event)">
                <input type="hidden" id="resetUserId">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> නව මුරපදය</label>
                    <input type="password" id="resetNewPw" required placeholder="නව මුරපදය ඇතුලත් කරන්න" minlength="6">
                </div>
                <button type="submit" class="btn-primary btn-full" id="submitResetPw">
                    <i class="fas fa-key"></i> මුරපදය සුරකින්න
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
                <p id="deleteMessage">මෙම පරිශීලකයා සහ ඔවුන්ගේ සියලුම දත්ත මකා දැමේ.</p>
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
const currentUserId = <?= $_SESSION['user_id'] ?>;

document.addEventListener('DOMContentLoaded', loadUsers);

function loadUsers() {
    ajaxGet('api/users.php?action=list').then(res => {
        if (res.success) {
            renderUsers(res.data);
            document.getElementById('userCount').innerHTML = `<i class="fas fa-users"></i> ${res.data.length}/3 පරිශීලකයන්`;
            const addBtn = document.getElementById('addUserBtn');
            if (res.data.length >= 3) {
                addBtn.disabled = true;
                addBtn.style.opacity = '0.5';
                addBtn.title = 'උපරිම 3ක් සපුරා ඇත';
            } else {
                addBtn.disabled = false;
                addBtn.style.opacity = '1';
            }
        }
    });
}

function renderUsers(data) {
    const tbody = document.getElementById('usersTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-users"></i><h3>පරිශීලකයන් නැත</h3></div></td></tr>';
        return;
    }
    let html = '';
    data.forEach(u => {
        const isCurrentUser = parseInt(u.id) === currentUserId;
        const isAdmin = u.role === 'admin';
        const dateStr = new Date(u.created_at).toLocaleDateString('si-LK', { year: 'numeric', month: 'short', day: 'numeric' });

        html += `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="user-avatar" style="width:36px;height:36px;border-radius:10px;font-size:14px;">${u.username.charAt(0).toUpperCase()}</div>
                    <div>
                        <div style="font-weight:600;">${u.username} ${isCurrentUser ? '<span style="font-size:11px;color:var(--accent);">(ඔබ)</span>' : ''}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-secondary);">${u.email}</td>
            <td>
                <span class="badge ${isAdmin ? 'badge-income' : 'badge-expense'}" style="font-size:11px;">
                    <i class="fas ${isAdmin ? 'fa-crown' : 'fa-user'}"></i>
                    ${isAdmin ? 'පරිපාලක' : 'සාමාජික'}
                </span>
            </td>
            <td style="color:var(--text-muted);font-size:13px;">${dateStr}</td>
            <td style="text-align:center;">
                <div style="display:flex;gap:6px;justify-content:center;">
                    <button class="btn-secondary btn-sm btn-icon" onclick="openResetPw(${u.id}, '${u.username}')" title="මුරපදය යළි සකසන්න">
                        <i class="fas fa-key"></i>
                    </button>
                    ${!isCurrentUser ? `<button class="btn-danger btn-sm btn-icon" onclick="deleteUser(${u.id}, '${u.username}')" title="මකන්න"><i class="fas fa-trash"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function addUser(e) {
    e.preventDefault();
    const btn = document.getElementById('submitAddUser');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> එකතු වෙමින්...';

    ajaxPost('api/users.php', {
        action: 'add',
        username: document.getElementById('newUsername').value,
        email: document.getElementById('newEmail').value,
        password: document.getElementById('newPassword').value,
        role: document.getElementById('newRole').value
    }).then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න';
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('addUserModal');
            document.getElementById('addUserForm').reset();
            loadUsers();
        } else {
            showToast(res.message, 'error');
        }
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> එකතු කරන්න';
        showToast('ජාල දෝෂයක්.', 'error');
    });
    return false;
}

function openResetPw(id, username) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('resetPwUser').textContent = `"${username}" සඳහා නව මුරපදයක් සකසන්න:`;
    document.getElementById('resetNewPw').value = '';
    openModal('resetPwModal');
}

function resetPassword(e) {
    e.preventDefault();
    const btn = document.getElementById('submitResetPw');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> සුරැකෙමින්...';

    ajaxPost('api/users.php', {
        action: 'reset_password',
        id: document.getElementById('resetUserId').value,
        new_password: document.getElementById('resetNewPw').value
    }).then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key"></i> මුරපදය සුරකින්න';
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('resetPwModal');
        } else {
            showToast(res.message, 'error');
        }
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key"></i> මුරපදය සුරකින්න';
        showToast('ජාල දෝෂයක්.', 'error');
    });
    return false;
}

function deleteUser(id, username) {
    confirmDelete(`"${username}" පරිශීලකයා සහ ඔවුන්ගේ සියලුම දත්ත මකා දැමේ.`, function() {
        ajaxPost('api/users.php', { action: 'delete', id: id }).then(res => {
            if (res.success) { showToast(res.message, 'success'); loadUsers(); }
            else { showToast(res.message, 'error'); }
        });
    });
}
</script>
</body>
</html>
