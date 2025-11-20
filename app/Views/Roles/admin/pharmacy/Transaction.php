<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Pharmacy Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>


<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Pharmacy Transactions</h1>
            <div class="search-container">
                <form id="trxSearchForm" style="display:flex; gap:10px; width:100%">
                    <input type="text" id="searchInput" name="q" value="<?= esc($query ?? '') ?>" class="search-input" placeholder="Search by Transaction #...">
                    <button id="searchButton" class="search-button" type="submit">Search</button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:12px;">Transaction #</th>
                        <th style="text-align:left; padding:12px;">Date</th>
                        <th style="text-align:left; padding:12px;">Items</th>
                        <th style="text-align:right; padding:12px;">Total Amount</th>
                        <th style="text-align:center; padding:12px; width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody">
                    <tr><td colspan="5" style="text-align:center; color:#666; padding:20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.transactions-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.transactions-table th,
.transactions-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e3e6f0;
}
.transactions-table th {
    background-color: #f8f9fc;
    color: #4e73df;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}
.transactions-table th:nth-child(4),
.transactions-table td:nth-child(4) {
    text-align: right;
    font-variant-numeric: tabular-nums;
    width: 150px;
}
.transactions-table th:nth-child(5),
.transactions-table td:nth-child(5) {
    text-align: center;
    width: 140px;
}
.transactions-table tbody tr:hover {
    background-color: rgba(78, 115, 223, 0.05);
}
@media (max-width: 768px) {
    .transactions-table th,
    .transactions-table td {
        padding: 10px 12px;
    }
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
                <td style="padding:12px;">${r.transaction_number}</td>
                <td style="padding:12px;">${r.date}</td>
                <td style="padding:12px;">${itemCount} item${itemCount === 1 ? '' : 's'}</td>
                <td style="padding:12px; text-align:right;">${peso(r.total_amount)}</td>
                <td style="padding:12px; text-align:center;" class="action-buttons">
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
<?= $this->endSection() ?>