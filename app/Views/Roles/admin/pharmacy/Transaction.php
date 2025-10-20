<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Pharmacy Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .box {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #3498db;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .box h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .box p {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 20px 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 0 1px #e0e0e0;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    .search-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .search-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .search-button {
        background-color: #2c3e50;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0 20px;
        cursor: pointer;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-view {
        background-color: #2ecc71;
        color: white;
    }

    .btn-print {
        background-color: #6c757d;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>

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
                            <button onclick="viewTransaction('TRX-001')" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
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
                            <button onclick="viewTransaction('TRX-002')" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
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