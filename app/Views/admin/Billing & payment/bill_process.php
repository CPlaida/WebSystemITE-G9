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
        <div class="form-section">
            <h2 class="section-header">Create New Bill</h2>
            
            <!-- Patient Information -->
            <div class="form-section" style="background-color: #f0f7ff;">
                <h3 class="section-header" style="color: #2980b9;">Patient Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label for="patient_id">Patient ID</label>
                        <input type="text" id="patient_id" name="patient_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="patient_name">Patient Name</label>
                        <input type="text" id="patient_name" name="patient_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Bill Items -->
            <div class="form-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3 class="section-header" style="margin: 0;">Bill Items</h3>
                    <button type="button" id="addItemBtn" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="table">
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
    </div>

    <!-- Payment Sidebar -->
    <div class="bill-sidebar">
        <div class="form-section">
            <h3 class="section-header" style="color: #27ae60;">Payment Information</h3>
            
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="">Select payment method</option>
                    <option value="cash">Cash</option>
                    <option value="insurance">Insurance</option>
                </select>
            </div>
            
            <div id="payment_details"></div>
            
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
                <button type="button" class="btn" style="flex: 1; background: #95a5a6; color: white;">
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
    <tr class="bill-item">
        <td>
            <input type="text" name="description[]" class="form-control form-control-sm item-description" required>
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control form-control-sm item-quantity" min="1" value="1" required>
        </td>
        <td>
            <input type="number" name="unit_price[]" class="form-control form-control-sm item-price" step="0.01" min="0" required>
        </td>
        <td class="item-amount">₱0.00</td>
        <td class="text-right">
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const billItems = document.getElementById('billItems');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemTemplate = document.getElementById('billItemTemplate');
    const paymentMethod = document.getElementById('payment_method');
    const paymentDetails = document.getElementById('payment_details');
    
    // Add first item by default
    function addNewItem() {
        if (!billItems || !itemTemplate) return;
        
        const newRow = itemTemplate.content.cloneNode(true);
        billItems.appendChild(newRow);
        
        // Focus on the description field of the new item
        const newRowElement = billItems.lastElementChild;
        const descInput = newRowElement.querySelector('.item-description');
        if (descInput) descInput.focus();
        
        // Initialize amount
        updateAmount(newRowElement);
        calculateTotals();
    }
    
    // Add new bill item
    if (addItemBtn) {
        addItemBtn.addEventListener('click', addNewItem);
    }
    
    // Add first item by default
    if (billItems && itemTemplate) {
        addNewItem();
    }
    
    // Handle payment method change
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
        });
    }
    
    // Calculate amounts when inputs change
    document.addEventListener('input', function(e) {
        if (e.target.matches('.item-quantity, .item-price')) {
            updateAmount(e.target.closest('tr'));
            calculateTotals();
        }
    });
    
    // Handle remove item button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            if (row) {
                row.remove();
                calculateTotals();
            }
        }
    });
    
    function updateAmount(row) {
        if (!row) return;
        
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        const amountCell = row.querySelector('.item-amount');
        
        if (!quantityInput || !priceInput || !amountCell) return;
        
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(priceInput.value) || 0;
        const amount = quantity * unitPrice;
        amountCell.textContent = '₱' + amount.toFixed(2);
    }
    
    function calculateTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.bill-item');
        
        rows.forEach(row => {
            const amountText = row.querySelector('.item-amount')?.textContent || '₱0.00';
            const amount = parseFloat(amountText.replace(/[^\d.-]/g, '')) || 0;
            subtotal += amount;
        });
        
        const tax = subtotal * 0.12;
        const total = subtotal + tax;
        
        const subtotalElement = document.getElementById('subtotal');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        
        if (subtotalElement) subtotalElement.textContent = '₱' + subtotal.toFixed(2);
        if (taxElement) taxElement.textContent = '₱' + tax.toFixed(2);
        if (totalElement) totalElement.textContent = '₱' + total.toFixed(2);
    }
});
</script>

<?= $this->endSection() ?>