<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Pharmacy Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>


<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Pharmacy Transactions</h1>
            <div class="search-container">
                <form method="get" action="<?= base_url('pharmacy/transactions') ?>" style="display:flex; gap:10px; width:100%">
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
                <tbody>
                    <tr>
                        <td>TRX-001</td>
                        <td>2025-10-14</td>
                        <td>Juan Dela Cruz</td>
                        <td>3 items</td>
                        <td>₱1,500.00</td>
                        <td class="action-buttons">
                            <button onclick="viewTransaction('TRX-001')" class="btn btn-view">View</button>

                            <button onclick="printTransaction('TRX-001')" class="btn btn-print">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>TRX-002</td>
                        <td>2025-10-13</td>
                        <td>Maria Santos</td>
                        <td>5 items</td>
                        <td>₱2,300.00</td>
                        <td class="action-buttons">
                            <button onclick="viewTransaction('TRX-002')" class="btn btn-view">View</button>

                            <button onclick="printTransaction('TRX-002')" class="btn btn-print">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// View transaction details
function viewTransaction(transactionId) {
    // You can replace this with a modal or redirect to a detailed view
    window.location.href = `<?= base_url('pharmacy/transaction/') ?>${transactionId}`;
}

// Print transaction receipt
function printTransaction(transactionId) {
    // Open print view in a new window
    const printWindow = window.open(
        `<?= base_url('pharmacy/transaction/print/') ?>${transactionId}`, 
        '_blank',
        'width=800,height=600'
    );
    printWindow.focus();
}
</script>
<?= $this->endSection() ?>