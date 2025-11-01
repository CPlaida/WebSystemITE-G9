<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Pharmacy Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>


<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Pharmacy Transactions</h1>
            <div class="search-container">
                <form id="trxSearchForm" style="display:flex; gap:10px; width:100%">
                    <input type="text" id="searchInput" name="q" value="<?= esc($query ?? '') ?>" class="search-input" placeholder="Search by Transaction # or Patient...">
                    <button id="searchButton" class="search-button" type="submit">Search</button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Date</th>
                        <th>Patient Name</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody">
                    <tr><td colspan="6" style="text-align:center; color:#666;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const API_BASE = '<?= site_url('api/pharmacy') ?>';

function peso(n) {
    const v = parseFloat(n || 0).toFixed(2);
    return '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function loadTransactions(search = '') {
    const body = document.getElementById('transactionsBody');
    body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#666;">Loading...</td></tr>';

    try {
        const url = new URL(API_BASE + '/transactions', window.location.origin);
        if (search) url.searchParams.set('search', search);
        const res = await fetch(url);
        const json = await res.json();
        const rows = (json && json.success && Array.isArray(json.data)) ? json.data : [];

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#666;">No transactions found</td></tr>';
            return;
        }

        body.innerHTML = '';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.transaction_number}</td>
                <td>${r.date}</td>
                <td>${r.patient_name || '-'}</td>
                <td>${(r.items_count || 0)} item${(r.items_count || 0) === 1 ? '' : 's'}</td>
                <td>${peso(r.total_amount)}</td>
                <td class="action-buttons">
                    <button onclick="viewTransaction(${r.id})" class="btn btn-view">View</button>
                </td>
            `;
            body.appendChild(tr);
        });
    } catch (e) {
        console.error('Load transactions error:', e);
        body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#c00;">Failed to load transactions</td></tr>';
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