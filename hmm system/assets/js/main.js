/* ==========================================
   HMM System - Main JavaScript
   ========================================== */

// Toast Notification System
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(30px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Modal System
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ESC to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => {
            m.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
});

// Mobile Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.classList.toggle('open');
}

// Format currency
function formatLKR(amount) {
    return 'Rs. ' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// AJAX helper
function ajaxPost(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data).toString()
    }).then(res => res.json());
}

function ajaxGet(url) {
    return fetch(url).then(res => res.json());
}

// Confirm delete
function confirmDelete(message, callback) {
    openModal('deleteModal');
    const msgEl = document.getElementById('deleteMessage');
    if (msgEl) msgEl.textContent = message;

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.addEventListener('click', function() {
            callback();
            closeModal('deleteModal');
        });
    }
}

// Auto-resize textarea
document.addEventListener('input', function(e) {
    if (e.target.tagName === 'TEXTAREA') {
        e.target.style.height = 'auto';
        e.target.style.height = e.target.scrollHeight + 'px';
    }
});

// Number input formatting
function handleAmountInput(input) {
    let value = input.value.replace(/[^0-9.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    input.value = value;
}

// Date helper - today
function getToday() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

// Initialize date inputs with today
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="date"][data-today]').forEach(input => {
        if (!input.value) input.value = getToday();
    });
});
