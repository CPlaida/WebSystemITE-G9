<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Bill Process<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
        .bill-container { 
            display: flex; 
            gap: 20px; 
        }

        .bill-main { 
            flex: 2; 
        }

        .bill-sidebar { 
            flex: 1; 
            min-width: 300px; 
        }

        .form-section { 
            background: #fff; 
            border-radius: 8px; 
            padding: 15px; 
            margin-bottom: 15px; 
        }

        .section-header { 
            font-size: 1.1rem; 
            margin: 0 0 12px 0; 
            padding-bottom: 8px; 
            border-bottom: 1px solid #eee; 
        }

        .form-group { 
            margin-bottom: 10px; 
        }

        .form-group label { 
            display: block; 
            margin-bottom: 3px; 
            font-size: 0.9rem; 
        }

        .form-control { 
            width: 100%; 
            padding: 6px 10px; 
            font-size: 0.9rem; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }

        #patientList {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-height: 220px;
            overflow-y: auto;
            z-index: 1000; /* ensure it stays above */
            display: none; /* hidden until we have results */
        }
        #patientList .list-group-item {
            background: #ffffff;
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
            display: block; /* stack items top to bottom */
            width: 100%;
            color: #333;
            text-decoration: none;
        }
        #patientList .list-group-item:last-child {
            border-bottom: none;
        }
        #patientList .list-group-item:hover {
            background: #f7fbff;
        }

        .table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        .table th, 
        .table td { 
            padding: 8px 10px; 
            border-bottom: 1px solid #eee; 
            text-align: left; 
        }

        .table th { 
            background: #f8f9fa; 
            font-weight: 600; 
        }

        .btn { 
            padding: 6px 12px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 0.9rem; 
        }

        .btn-primary { 
            background: #3498db; 
            color: white; 
        }

        .btn-success { 
            background: #2ecc71; 
            color: white; 
        }

        .btn-danger { 
            background: #e74c3c; 
            color: white; 
        }

        .btn-sm { 
            padding: 3px 6px; 
            font-size: 0.8rem; 
        }

        .text-right { 
            text-align: right; 
        }

        .mb-3 { 
            margin-bottom: 15px; 
        }
</style>

<div class="bill-container">
    <!-- Main Content -->
    <div class="bill-main">
        <form id="billForm" method="post" action="<?= base_url('billing/store-with-items') ?>">
            <div class="form-section">
                <h2 class="section-header">Create New Bill</h2>
                
                <!-- Patient Information -->
                <div class="form-section" style="background-color: #f0f7ff; position: relative;">
                    <h3 class="section-header" style="color: #2980b9;">Patient Information</h3>
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px;">
                        <div class="form-group" style="position: relative;">
                            <label for="patientName">Patient Name</label>
                            <input type="text" id="patientName" class="form-control" placeholder="Enter patient name" autocomplete="off">
                            <input type="hidden" id="patientID" name="patient_id">
                            <div id="patientList" class="list-group" style="position:absolute; z-index:1000; top:58px; left:0; right:0;"></div>
                        </div>
                        <div class="form-group">
                            <label for="bill_date">Date</label>
                            <input type="date" id="bill_date" name="bill_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="insurance">Insurance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select id="payment_status" name="payment_status" class="form-control" required>
                                <option value="pending" selected>Pending</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                    </div>
                    <div id="payment_details" class="mt-2"></div>
                </div>

                <!-- Bill Items -->
                <div class="form-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 class="section-header" style="margin: 0;">Bill Items</h3>
                        <button type="button" id="addItem" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table" id="billItemsTable">
                            <thead style="position: sticky; top: 0; background: #f8f9fa;">
                                <tr>
                                    <th>Service</th>
                                    <th style="width: 80px;">Qty</th>
                                    <th style="width: 120px;">Unit Price</th>
                                    <th style="width: 120px;">Amount</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="billItems">
                                <!-- Bill items will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Payment Sidebar -->
    <div class="bill-sidebar">
        <div class="form-section">
            <h3 class="section-header" style="color: #27ae60;">Summary</h3>
            <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span>Tax (12%):</span>
                    <span id="tax">₱0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em; 
                            border-top: 1px solid #ddd; padding-top: 10px; margin-top: 8px;">
                    <span>Total:</span>
                    <span id="total">₱0.00</span>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn" style="flex: 1; background: #95a5a6; color: white;" onclick="window.history.back()">
                    Cancel
                </button>
                <button type="submit" form="billForm" class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-save"></i> Save Bill
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bill Item Template (Hidden) -->
<template id="billItemTemplate">
    <tr>
        <td>
            <input type="text" name="service[]" class="form-control service" required>
        </td>
        <td>
            <input type="number" name="qty[]" class="form-control qty" min="1" value="1" required>
        </td>
        <td>
            <input type="number" name="price[]" class="form-control price" step="0.01" min="0" required>
        </td>
        <td>
            <input type="number" name="amount[]" class="form-control amount" readonly>
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
// Patient autocomplete
const patientInput = document.getElementById('patientName');
const patientList = document.getElementById('patientList');
const patientID = document.getElementById('patientID');

if (patientInput) {
    patientInput.addEventListener('input', async function() {
        // Clear stale selection when user types
        patientID.value = '';
        const term = this.value.trim();
        if (term.length < 1) {
            patientList.innerHTML = '';
            patientList.style.display = 'none';
            return;
        }
        try {
            const res = await fetch(`<?= base_url('patients/search') ?>?term=${encodeURIComponent(term)}`);
            const data = await res.json();
            patientList.innerHTML = '';
            const results = Array.isArray(data) ? data : (data.patients || []);
            if (!results.length) {
                patientList.style.display = 'none';
                return;
            }
            patientList.style.display = 'block';
            results.forEach(p => {
                const item = document.createElement('a');
                item.classList.add('list-group-item', 'list-group-item-action');
                item.textContent = p.name;
                item.style.cursor = 'pointer';
                item.onclick = () => {
                    patientInput.value = p.name;
                    patientID.value = p.id;
                    patientList.innerHTML = '';
                    patientList.style.display = 'none';
                };
                patientList.appendChild(item);
            });
        } catch (e) {
            patientList.innerHTML = '';
            patientList.style.display = 'none';
        }
    });
    document.addEventListener('click', (e) => {
        if (!patientList.contains(e.target) && e.target !== patientInput) {
            patientList.innerHTML = '';
            patientList.style.display = 'none';
        }
    });
}

// Dynamic items
document.getElementById('addItem')?.addEventListener('click', function() {
    const table = document.querySelector('#billItems');
    const tpl = document.getElementById('billItemTemplate');
    if (table && tpl) {
        table.appendChild(tpl.content.cloneNode(true));
        updateTotals();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        const row = e.target.closest('tr');
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.amount').value = (qty * price).toFixed(2);
        updateTotals();
    }
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        const row = e.target.closest('tr');
        row?.remove();
        updateTotals();
    }
});

function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.amount').forEach(a => subtotal += parseFloat(a.value) || 0);
    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
}

// Payment method details
const paymentMethod = document.getElementById('payment_method');
const paymentDetails = document.getElementById('payment_details');
if (paymentMethod && paymentDetails) {
    paymentMethod.addEventListener('change', function() {
        const method = this.value;
        paymentDetails.innerHTML = '';
        if (method === 'insurance') {
            paymentDetails.innerHTML = `
                <div class="form-group">
                    <label for="insurance_provider">Insurance Provider</label>
                    <input type="text" id="insurance_provider" name="insurance_provider" class="form-control">
                </div>
            `;
        }
    });
}

// On submit, ensure a selected patient and at least one valid item
document.getElementById('billForm')?.addEventListener('submit', function(e) {
    const pid = document.getElementById('patientID')?.value?.trim();
    if (!pid) {
        e.preventDefault();
        alert('Please select a patient from the suggestions so we can capture their ID.');
        document.getElementById('patientName')?.focus();
        return;
    }
    const rows = Array.from(document.querySelectorAll('#billItems tr'));
    const hasValidRow = rows.some(row => {
        const s = row.querySelector('.service')?.value.trim();
        const q = parseFloat(row.querySelector('.qty')?.value || '0');
        const p = parseFloat(row.querySelector('.price')?.value || '0');
        return s && q > 0 && p >= 0;
    });
    if (!hasValidRow) {
        e.preventDefault();
        alert('Please add at least one bill item (service, quantity, price).');
        return;
    }
});

// Initialize with one row
(function initFirstRow(){
    document.getElementById('addItem')?.click();
})();
</script>

<?= $this->endSection() ?>