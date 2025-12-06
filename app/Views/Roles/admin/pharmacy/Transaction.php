<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Pharmacy Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Pharmacy Transactions</h1>
        </div>
        <div class="card-body">
            <div class="unified-search-wrapper" style="margin-bottom: 1.5rem;">
                <form id="trxSearchForm" class="unified-search-row" style="margin:0;">
                    <i class="fas fa-search unified-search-icon"></i>
                    <input type="text" id="searchInput" name="q" value="<?= esc($query ?? '') ?>" class="unified-search-field" placeholder="Search by Transaction #...">
                </form>
            </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody">
                    <tr><td colspan="5" style="text-align:center; color:#666; padding:20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>

<style>
.data-table th:nth-child(4),
.data-table td:nth-child(4) {
    text-align: left !important;
    font-variant-numeric: tabular-nums;
}

.data-table th:nth-child(5),
.data-table td:nth-child(5) {
    text-align: center !important;
    vertical-align: middle !important;
    width: 140px;
}

.data-table td.action-buttons {
    text-align: center !important;
    vertical-align: middle !important;
    padding: 1rem 1.25rem !important;
}

.data-table td.action-buttons .btn {
    display: inline-block;
    margin: 0;
    vertical-align: middle;
}
</style>

<script>
const API_BASE = '<?= site_url('api/pharmacy') ?>';

function peso(n) {
    const v = parseFloat(n || 0).toFixed(2);
    return '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function loadTransactions(search = '') {
    const body = document.getElementById('transactionsBody');
    body.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#666;">Loading...</td></tr>';

    try {
        const url = new URL(API_BASE + '/transactions', window.location.origin);
        if (search) url.searchParams.set('search', search);
        const res = await fetch(url);
        const json = await res.json();
        const rows = (json && json.success && Array.isArray(json.data)) ? json.data : [];

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#666;">No transactions found</td></tr>';
            return;
        }

        body.innerHTML = '';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            const itemCount = Number(r.items_count ?? 0);
            tr.innerHTML = `
                <td>${r.transaction_number}</td>
                <td>${r.date}</td>
                <td>${itemCount} item${itemCount === 1 ? '' : 's'}</td>
                <td>${peso(r.total_amount)}</td>
                <td class="action-buttons">
                    <button onclick="viewTransaction(${r.id})" class="btn btn-view" style="background:#2ecc71; color:#fff; border:none; padding:6px 16px; border-radius:4px; cursor:pointer; font-weight:500; transition:all 0.2s;">View</button>
                </td>
            `;
            body.appendChild(tr);
        });
    } catch (e) {
        console.error('Load transactions error:', e);
        body.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#c00;">Failed to load transactions</td></tr>';
    }
}

function viewTransaction(id) {
    window.location.href = '<?= site_url('admin/pharmacy/transaction/') ?>' + id;
}

// Search handler (submit)
const form = document.getElementById('trxSearchForm');
const input = document.getElementById('searchInput');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    const q = input.value.trim();
    loadTransactions(q);
});

// Debounced type-to-search
let tSearch;
input.addEventListener('input', function(){
    clearTimeout(tSearch);
    const q = this.value.trim();
    tSearch = setTimeout(()=> loadTransactions(q), 250);
});

// Initial load uses prefilled value (if any)
(function init(){
    const initial = (input?.value || '').trim();
    loadTransactions(initial);
})();
</script>
        </div>
    </div>
</div>
<?= $this->endSection() ?>