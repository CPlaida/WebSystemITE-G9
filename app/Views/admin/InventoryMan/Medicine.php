<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Medicine Inventory<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
// Set the current menu item for highlighting
$currentMenu = 'pharmacy';
$currentSubmenu = 'inventory';
?>

<div class="main-content">
    <div class="content-wrapper">
        <div class="content-header">
            <div class="header-left">
                <h1 class="page-title">Medicine Inventory</h1>
            </div>
            <div class="header-right">
                <button class="btn btn-primary" onclick="toggleForm()">
                    <i class="fas fa-plus"></i> Add New Medicine
                </button>
            </div>
        </div>

        <div class="content">
            <div class="card-container">
                <div class="card">
                    <h3>Total Items</h3>
                    <div class="value" id="totalItems">0</div>
                </div>
                <div class="card">
                    <h3>Low Stock</h3>
                    <div class="value" id="lowStock">0</div>
                </div>
                <div class="card">
                    <h3>Out of Stock</h3>
                    <div class="value" id="outOfStock">0</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicineTableBody">
                        <!-- Rows will be dynamically added here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Form Modal -->
<div class="modal" id="medicineForm">
    <div class="modal-content" style="width: 95%; max-width: 1000px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.5rem; color: #333;">Add New Medicine(s)</h2>
            <span class="close" onclick="closeForm()" style="font-size: 24px; cursor: pointer; color: #666;">&times;</span>
        </div>
        
        <form id="medicineFormElement" style="margin: 0;">
            <div id="medicineEntries">
                <div class="medicine-entry" style="margin-bottom: 20px;">
                    <!-- Header Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 0.5fr; gap: 10px; margin-bottom: 10px; padding: 10px 5px; background: #f8f9fa; border-radius: 6px;">
                        <div style="font-weight: 500; color: #495057;">Medicine Name</div>
                        <div style="font-weight: 500; color: #495057;">Brand</div>
                        <div style="font-weight: 500; color: #495057;">Category</div>
                        <div style="font-weight: 500; color: #495057; text-align: center;">Stock</div>
                        <div style="font-weight: 500; color: #495057; text-align: right;">Price (₱)</div>
                        <div style="font-weight: 500; color: #495057;">Expiry Date</div>
                        <div></div>
                    </div>
                    
                    <!-- Data Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 0.5fr; gap: 10px; align-items: center; padding: 5px;">
                        <div>
                            <input type="text" name="medicineName[]" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div>
                            <input type="text" name="brand[]" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div>
                            <select name="category[]" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                <option value="">Select</option>
                                <option value="Pain Relief">Pain Relief</option>
                                <option value="Antibiotics">Antibiotics</option>
                                <option value="Vitamins">Vitamins</option>
                                <option value="Antihistamines">Antihistamines</option>
                            </select>
                        </div>
                        <div>
                            <input type="number" name="stock[]" min="0" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; text-align: center;">
                        </div>
                        <div>
                            <input type="number" name="price[]" min="0" step="0.01" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; text-align: right;">
                        </div>
                        <div>
                            <input type="date" name="expiryDate[]" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div style="display: flex; justify-content: center;">
                            <button type="button" onclick="removeMedicineEntry(this)" style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 18px; padding: 0 8px;">
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                <button type="button" onclick="addMoreMedicine()" style="background: #6c757d; color: white; border: none; border-radius: 4px; padding: 8px 15px; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-plus" style="font-size: 12px;"></i> Add More
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeForm()" style="background: #6c757d; color: white; border: none; border-radius: 4px; padding: 8px 20px; cursor: pointer; font-size: 14px;">
                        Cancel
                    </button>
                    <button type="submit" style="background: #007bff; color: white; border: none; border-radius: 4px; padding: 8px 20px; cursor: pointer; font-size: 14px;">
                        Save All
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #f4f6f9;
        color: #333;
    }
    .content-wrapper {
        padding: 20px;
    }
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    .page-title {
        margin: 0;
        font-size: 24px;
        color: #2c3e50;
    }
    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    .card {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-3px);
    }
    .card h3 {
        margin: 0 0 10px 0;
        font-size: 16px;
        color: #7f8c8d;
    }
    .card .value {
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
    }
    .table-responsive {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        overflow: hidden;
        padding: 20px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .data-table th,
    .data-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }
    .data-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        padding: 12px 15px;
    }
    .data-table tr:hover {
        background-color: #f8f9fa;
    }
    .in-stock {
        color: #27ae60;
        font-weight: 500;
    }
    .actions {
        display: flex;
        gap: 10px;
    }
    .btn-edit, .btn-delete {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px 8px;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .btn-edit {
        color: #3498db;
    }
    .btn-delete {
        color: #e74c3c;
    }
    .btn-edit:hover, .btn-delete:hover {
        background-color: rgba(0,0,0,0.05);
        transform: scale(1.1);
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    .modal-content {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        width: 100%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }
    .close {
        position: absolute;
        right: 25px;
        top: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #7f8c8d;
        transition: color 0.2s;
    }
    .close:hover {
        color: #2c3e50;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-start;
        margin-bottom: 15px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .form-row:hover {
        background: #f0f0f0;
    }
    .form-row .form-group {
        margin-bottom: 0;
        flex: 1;
        min-width: 120px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
        font-size: 14px;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    .btn i {
        font-size: 13px;
    }
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-1px);
    }
    .btn-secondary {
        background-color: #95a5a6;
        color: white;
    }
    .btn-secondary:hover {
        background-color: #7f8c8d;
        transform: translateY(-1px);
    }
    .btn-danger {
        background-color: #e74c3c;
        color: white;
    }
    .btn-danger:hover {
        background-color: #c0392b;
        transform: translateY(-1px);
    }
    .medicine-entry {
        margin-bottom: 10px;
    }
    .main-content {
        margin-left: 120px;
        padding: 20px 20px;
        transition: all 0.3s;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .modal-content {
            width: 98% !important;
            margin: 10px;
        }
        
        .medicine-entry > div:first-child,
        .medicine-entry > div:last-child {
            grid-template-columns: 1fr 1fr 1fr !important;
        }
        
        .medicine-entry > div:first-child > div:nth-child(6),
        .medicine-entry > div:last-child > div:nth-child(6) {
            grid-column: 1 / span 3;
        }
    }

    @media (max-width: 768px) {
        .medicine-entry > div:first-child,
        .medicine-entry > div:last-child {
            grid-template-columns: 1fr 1fr !important;
        }
        
        .medicine-entry > div:first-child > div,
        .medicine-entry > div:last-child > div {
            grid-column: auto !important;
        }
    }
</style>

<script>
    // Toggle form visibility
    function toggleForm() {
        const form = document.getElementById('medicineForm');
        form.style.display = 'flex';
        // Reset form when opening
        document.getElementById('medicineFormElement').reset();
        // Reset to one entry
        const entries = document.getElementById('medicineEntries');
        const firstEntry = entries.querySelector('.medicine-entry');
        entries.innerHTML = '';
        entries.appendChild(firstEntry);
    }

    function closeForm() {
        const form = document.getElementById('medicineForm');
        form.style.display = 'none';
    }

    // Add more medicine entry
    function addMoreMedicine() {
        const entries = document.getElementById('medicineEntries');
        const newEntry = entries.querySelector('.medicine-entry').cloneNode(true);
        // Clear input values in the new entry
        newEntry.querySelectorAll('input').forEach(input => input.value = '');
        newEntry.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        entries.appendChild(newEntry);
    }

    // Remove medicine entry
    function removeMedicineEntry(button) {
        const entries = document.getElementById('medicineEntries');
        if (entries.children.length > 1) {
            button.closest('.medicine-entry').remove();
        } else {
            // If it's the last entry, just clear the inputs
            const entry = button.closest('.medicine-entry');
            entry.querySelectorAll('input').forEach(input => input.value = '');
            entry.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        }
    }

    // Form submission
    document.getElementById('medicineFormElement').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get all form data
        const formData = new FormData(this);
        const medicines = [];
        
        // Group form data by entry
        const entries = formData.getAll('medicineName').length;
        for (let i = 0; i < entries; i++) {
            medicines.push({
                name: formData.getAll('medicineName')[i],
                brand: formData.getAll('brand')[i],
                category: formData.getAll('category')[i],
                stock: formData.getAll('stock')[i],
                price: formData.getAll('price')[i],
                expiryDate: formData.getAll('expiryDate')[i]
            });
        }
        
        // Here you would typically send the data to the server
        console.log('Medicines to save:', medicines);
        
        // For demo, just add to the table
        const tbody = document.getElementById('medicineTableBody');
        
        medicines.forEach(med => {
            if (med.name) { // Only add if name is not empty
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>MED${Math.floor(1000 + Math.random() * 9000)}</td>
                    <td>${med.name}</td>
                    <td>${med.brand}</td>
                    <td>${med.category}</td>
                    <td class="in-stock">${med.stock}</td>
                    <td>₱${parseFloat(med.price).toFixed(2)}</td>
                    <td>${med.expiryDate}</td>
                    <td class="actions">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            }
        });
        
        // Update stats
        updateStats();
        
        // Close the form
        closeForm();
        
        // Show success message
        alert('Medicines saved successfully!');
    });
    
    // Update dashboard stats
    function updateStats() {
        const rows = document.querySelectorAll('#medicineTableBody tr');
        let totalItems = rows.length;
        let lowStock = 0;
        let outOfStock = 0;
        
        rows.forEach(row => {
            const stock = parseInt(row.cells[4].textContent);
            if (stock === 0) outOfStock++;
            else if (stock < 10) lowStock++;
        });
        
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('lowStock').textContent = lowStock;
        document.getElementById('outOfStock').textContent = outOfStock;
    }
    
    // Initialize with some sample data
    document.addEventListener('DOMContentLoaded', function() {
        // Add sample data
        const sampleData = [
            { id: 'MED1001', name: 'Paracetamol 500mg', brand: 'Biogesic', category: 'Pain Relief', stock: 150, price: 5.00, expiry: '2025-12-31' },
            { id: 'MED1002', name: 'Amoxicillin 500mg', brand: 'Amoxil', category: 'Antibiotics', stock: 45, price: 8.50, expiry: '2024-11-30' },
            { id: 'MED1003', name: 'Vitamin C 500mg', brand: 'Vitacare', category: 'Vitamins', stock: 3, price: 3.75, expiry: '2024-10-15' },
            { id: 'MED1004', name: 'Loratadine 10mg', brand: 'Loratin', category: 'Antihistamines', stock: 0, price: 12.99, expiry: '2025-05-20' }
        ];
        
        const tbody = document.getElementById('medicineTableBody');
        sampleData.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id}</td>
                <td>${item.name}</td>
                <td>${item.brand}</td>
                <td>${item.category}</td>
                <td class="${item.stock === 0 ? 'out-of-stock' : 'in-stock'}">${item.stock}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td>${item.expiry}</td>
                <td class="actions">
                    <button class="btn-edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-delete"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        // Update stats
        updateStats();
    });
</script>
<?= $this->endSection() ?>
