<?php $this->extend('partials/header') ?>

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
            <?php $errorFlash = session()->getFlashdata('error'); ?>
            <?php $errorMsg = session()->getFlashdata('error') ?? ''; ?>
            <!-- Error modal is shown later after DOMContentLoaded using ERROR_MSG -->
            <div class="card-container">
                <div class="card">
                    <h3>Total Items</h3>
                    <div class="value" id="totalItems"><?php if (isset($total)) { echo (int)$total; } ?></div>
                </div>
                <div class="card">
                    <h3>Low Stock</h3>
                    <div class="value" id="lowStock"><?php if (isset($low_stock)) { echo (int)$low_stock; } ?></div>
                </div>
                <div class="card">
                    <h3>Out of Stock</h3>
                    <div class="value" id="outOfStock"><?php if (isset($out_stock)) { echo (int)$out_stock; } ?></div>
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
                        <?php if (!empty($medicines)) : ?>
                            <?php foreach ($medicines as $m): ?>
                                <tr>
                                    <td><?= esc($m['medicine_id']) ?></td>
                                    <td><?= esc($m['name']) ?></td>
                                    <td><?= esc($m['brand']) ?></td>
                                    <td><?= esc($m['category']) ?></td>
                                    <td class="<?= ((int)$m['stock'] === 0 ? 'out-of-stock' : 'in-stock') ?>"><?= (int)$m['stock'] ?></td>
                                    <td>₱<?= number_format((float)$m['price'], 2) ?></td>
                                    <td><?= esc($m['expiry_date']) ?></td>
                                    <td class="actions">
                                        <a class="btn-edit" href="<?= base_url('medicines/edit/' . $m['id']) ?>">Edit</a>
                                        <a class="btn-delete" href="<?= base_url('medicines/delete/' . $m['id']) ?>" onclick="return confirm('Delete this medicine?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal" id="errorModal" role="dialog" aria-modal="true" aria-labelledby="errorTitle" style="z-index: 2000;">
    <div class="modal-content" style="max-width: 420px; text-align: center; padding: 30px 24px;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #ffe5e8; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2" fill="none"/>
                <path d="M8 8l8 8M16 8l-8 8" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <h3 id="errorTitle" style="margin: 0 0 10px; font-size: 22px; color: #2c3e50;">Error</h3>
        <div id="errorModalBody" style="color: #6b7280; font-size: 14px; margin-bottom: 18px;"></div>
        <button type="button" id="errorOkBtn" style="background: #e74c3c; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-weight: 600; cursor: pointer;">OK</button>
    </div>
</div>

<!-- Add/Edit Form Modal -->
    <?php $isEdit = isset($edit_medicine) && is_array($edit_medicine); ?>
    <div class="modal" id="medicineForm">
        <div class="modal-content" style="width: 95%; max-width: 1000px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 1.5rem; color: #333;">
                    <?= $isEdit ? 'Edit Medicine' : 'Add New Medicine(s)' ?>
                </h2>
                <span class="close" onclick="closeForm()" style="font-size: 24px; cursor: pointer; color: #666;">&times;</span>
            </div>
            
            <form id="medicineFormElement" style="margin: 0;" method="post" action="<?= $isEdit ? base_url('medicines/update/' . $edit_medicine['id']) : base_url('medicines/store') ?>">
                <?= csrf_field() ?>
            <!-- Autocomplete suggestion lists -->
            <datalist id="medicineNamesList"></datalist>
            <datalist id="brandList"></datalist>
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
                            <input type="text" name="<?= $isEdit ? 'name' : 'name[]' ?>" value="<?= $isEdit ? esc($edit_medicine['name']) : '' ?>" list="medicineNamesList" autocomplete="off" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div>
                            <input type="text" name="<?= $isEdit ? 'brand' : 'brand[]' ?>" value="<?= $isEdit ? esc($edit_medicine['brand']) : '' ?>" list="brandList" autocomplete="off" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div>
                            <select name="<?= $isEdit ? 'category' : 'category[]' ?>" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                <option value="">Select</option>
                                <?php $categories = ['Pain Relief','Antibiotics','Vitamins','Antihistamines']; ?>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c ?>" <?= $isEdit && $edit_medicine['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input type="number" name="<?= $isEdit ? 'stock' : 'stock[]' ?>" value="<?= $isEdit ? (int)$edit_medicine['stock'] : '' ?>" min="0" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; text-align: center;">
                        </div>
                        <div>
                            <input type="number" name="<?= $isEdit ? 'price' : 'price[]' ?>" value="<?= $isEdit ? (float)$edit_medicine['price'] : '' ?>" min="0" step="0.01" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; text-align: right;">
                        </div>
                        <div>
                            <input type="date" name="<?= $isEdit ? 'expiry_date' : 'expiry_date[]' ?>" value="<?= $isEdit ? esc($edit_medicine['expiry_date']) : '' ?>" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div style="display: flex; justify-content: center;">
                            <button type="button" onclick="removeMedicineEntry(this)" style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 18px; padding: 0 8px;" <?= $isEdit ? 'disabled style="opacity:.3;cursor:not-allowed;background:none;border:none;color:#ccc;"' : '' ?>>
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                <?php if (!$isEdit): ?>
                <button type="button" onclick="addMoreMedicine()" style="background: #6c757d; color: white; border: none; border-radius: 4px; padding: 8px 15px; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-plus" style="font-size: 12px;"></i> Add More
                </button>
                <?php endif; ?>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeForm()" style="background: #6c757d; color: white; border: none; border-radius: 4px; padding: 8px 20px; cursor: pointer; font-size: 14px;">
                        Cancel
                    </button>
                    <button type="submit" style="background: #007bff; color: white; border: none; border-radius: 4px; padding: 8px 20px; cursor: pointer; font-size: 14px;">
                        <?= $isEdit ? 'Update' : 'Save All' ?>
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
        border: none;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 6px;
        transition: background-color 0.2s ease;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
    }
    .btn-edit {
        background-color: #3498db; /* blue */
    }
    .btn-edit:hover {
        background-color: #2980b9;
    }
    .btn-delete {
        background-color: #e74c3c; /* red */
    }
    .btn-delete:hover {
        background-color: #c0392b;
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
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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
    // Page mode flags from PHP (edit vs add)
    const IS_EDIT = <?= isset($isEdit) && $isEdit ? 'true' : 'false' ?>;
    const OPEN_ADD = <?= isset($open_add_modal) && $open_add_modal ? 'true' : 'false' ?>;
    const ADD_URL = '<?= base_url('medicines?add=1') ?>';
    const LIST_URL = '<?= base_url('medicines') ?>';
    const ERROR_MSG = <?= json_encode(isset($errorFlash) ? (string)$errorFlash : '') ?>;

    // Toggle form visibility
    function toggleForm() {
        // If current page was rendered in edit mode, reload in add mode to reset DOM
        if (IS_EDIT) {
            window.location.href = ADD_URL;
            return;
        }
        const form = document.getElementById('medicineForm');
        form.style.display = 'flex';
        // Reset form when opening
        const formEl = document.getElementById('medicineFormElement');
        if (formEl) formEl.reset();
        // Reset to one entry
        const entries = document.getElementById('medicineEntries');
        if (entries) {
            const firstEntry = entries.querySelector('.medicine-entry');
            if (firstEntry) {
                entries.innerHTML = '';
                entries.appendChild(firstEntry);
            }
        }
        // Refresh suggestions each time modal opens
        if (typeof refreshAutocompleteLists === 'function') {
            refreshAutocompleteLists();
        }
    }

    function closeForm() {
        const form = document.getElementById('medicineForm');
        if (IS_EDIT) {
            // Leaving edit mode should clear the edit state
            window.location.href = LIST_URL;
            return;
        }
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

    // Let the form submit normally to the backend. No client-side interception.
    
    // Stats are rendered from PHP variables on page load.
    
    // --- Autocomplete helpers ---
    const defaultMedicineNames = [
        'Paracetamol 500mg','Amoxicillin 500mg','Vitamin C 500mg','Loratadine 10mg',
        'Ibuprofen 200mg','Metformin 500mg','Amlodipine 5mg','Omeprazole 20mg'
    ];
    const defaultBrands = [
        'Biogesic','Amoxil','Vitacare','Loratin','Pfizer','Unilab','RiteMed','GSK'
    ];

    function uniqueSorted(arr) {
        return Array.from(new Set(arr.filter(Boolean))).sort((a,b)=>a.localeCompare(b));
    }

    function gatherFromTable() {
        const rows = document.querySelectorAll('#medicineTableBody tr');
        const names = [];
        const brands = [];
        rows.forEach(r => {
            const tds = r.querySelectorAll('td');
            if (tds[1]) names.push(tds[1].textContent.trim());
            if (tds[2]) brands.push(tds[2].textContent.trim());
        });
        return { names: uniqueSorted(names), brands: uniqueSorted(brands) };
    }

    function renderDatalist(listId, values) {
        const dl = document.getElementById(listId);
        if (!dl) return;
        dl.innerHTML = values.map(v => `<option value="${v.replace(/"/g,'&quot;')}"></option>`).join('');
    }

    function refreshAutocompleteLists() {
        const fromTable = gatherFromTable();
        const names = uniqueSorted(defaultMedicineNames.concat(fromTable.names));
        const brands = uniqueSorted(defaultBrands.concat(fromTable.brands));
        renderDatalist('medicineNamesList', names);
        renderDatalist('brandList', brands);
    }
    
    // Initialize autocomplete options after table is populated
    document.addEventListener('DOMContentLoaded', function() {
        refreshAutocompleteLists();

        <?php if (isset($isEdit) && $isEdit): ?>
            const form = document.getElementById('medicineForm');
            form.style.display = 'flex';
        <?php endif; ?>
        <?php if (isset($open_add_modal) && $open_add_modal): ?>
            const formAdd = document.getElementById('medicineForm');
            formAdd.style.display = 'flex';
        <?php endif; ?>

        if (ERROR_MSG) {
            const modal = document.getElementById('errorModal');
            const body = document.getElementById('errorModalBody');
            const ok = document.getElementById('errorOkBtn');
            body.innerHTML = 'Please fix the following errors:<br><ul style="text-align:left;margin:8px 0 0 18px;"><li>' + ERROR_MSG + '</li></ul>';
            // Ensure modal is above any other overlay and visible
            modal.style.display = 'flex';
            modal.style.zIndex = 2000;
            ok.onclick = function() { modal.style.display = 'none'; };
        }
    });
</script>
<?= $this->endSection() ?>