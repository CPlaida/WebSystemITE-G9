<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

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

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-paid {
        background-color: #d4edda;
        color: #155724;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
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
    }

    .btn-edit {
        background-color: #3498db;
        color: white;
    }

    .btn-delete {
        background-color: #e74c3c;
        color: white;
    }

    .btn-view {
        background-color: #2ecc71;
        color: white;
    }

    .btn-receipt {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-receipt:hover {
        background-color: #5a6268;
        color: white;
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
</style>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Billing Management</h1>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search bills...">
                <button id="searchButton" class="search-button">Search</button>
            </div>
        </div>

        <div class="summary">
            <div class="box">
                <h3>Total Revenue</h3>
                <p>₱45,890.00</p>
            </div>
            <div class="box">
                <h3>Pending Bills</h3>
                <p>12</p>
            </div>
            <div class="box">
                <h3>Paid This Month</h3>
                <p>₱28,750.00</p>
            </div>
            <div class="box">
                <h3>Outstanding</h3>
                <p>₱5,340.00</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Patient Name</th>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#INV-2023-001</td>
                        <td>Juan Dela Cruz</td>
                        <td>2023-11-15</td>
                        <td>Consultation</td>
                        <td>₱500.00</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('admin/billing/receipt/1') ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                            <a href="#" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#INV-2023-002</td>
                        <td>Maria Santos</td>
                        <td>2023-11-16</td>
                        <td>Consultation</td>
                        <td>₱500.00</td>
                        <td><span class="status-pending">Pending</span></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('admin/billing/receipt/2') ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                            <a href="#" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#INV-2023-003</td>
                        <td>Pedro Reyes</td>
                        <td>2023-11-17</td>
                        <td>Consultation</td>
                        <td>₱500.00</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('admin/billing/receipt/3') ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                            <a href="#" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#INV-2023-004</td>
                        <td>Ana Martinez</td>
                        <td>2023-11-18</td>
                        <td>Consultation</td>
                        <td>₱500.00</td>
                        <td><span class="status-pending">Pending</span></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('admin/billing/receipt/4') ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                            <a href="#" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#INV-2023-005</td>
                        <td>Jose Garcia</td>
                        <td>2023-11-19</td>
                        <td>Consultation</td>
                        <td>₱500.00</td>
                        <td><span class="status-paid">Paid</span></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('admin/billing/receipt/5') ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                            <a href="#" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Search functionality
    document.getElementById('searchButton').addEventListener('click', filterBills);
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            filterBills();
        }
    });

    function filterBills() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }

    // Handle edit actions
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit')) {
            editBill(e.target.closest('.btn-edit'));
        }
    });

    function editBill(button) {
        const row = button.closest('tr');
        const cells = row.cells;
        const billNumber = cells[0].textContent;
        
        // In a real application, you would open an edit form/modal here
        Swal.fire({
            title: 'Edit Bill',
            text: `Edit bill ${billNumber}`,
            html: `
                <div class="text-left">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Patient Name</label>
                        <input type="text" class="w-full px-3 py-2 border rounded" value="${cells[1].textContent}">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                        <input type="text" class="w-full px-3 py-2 border rounded" value="${cells[4].textContent}">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // In a real application, you would save the changes here
                return new Promise((resolve) => {
                    setTimeout(() => {
                        resolve();
                    }, 1000);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Success!', 'Bill has been updated.', 'success');
            }
        });
    }
</script>
<?= $this->endSection() ?>
