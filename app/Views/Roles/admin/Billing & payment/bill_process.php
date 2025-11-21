<?php $this->extend('partials/header') ?>
<?php $hmoProviders = $hmoProviders ?? []; ?>

<?= $this->section('title') ?>Bill Process<?= $this->endSection() ?>

<?= $this->section('content') ?>


<div class="bill-container">
    <!-- Main Content -->
    <div class="bill-main">
        <form id="billForm" method="post" action="<?= isset($bill['id']) ? base_url('billing/update/' . (int)$bill['id']) : base_url('billing/store-with-items') ?>">
            <div class="form-section">
                <h2 class="section-header"><?= isset($bill['id']) ? 'Edit Bill' : 'Create New Bill' ?></h2>
                
                <!-- Patient Information -->
                <div class="form-section" style="background-color: #f0f7ff; position: relative;">
                    <h3 class="section-header" style="color: #2980b9;">Patient Information</h3>
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px;">
                        <div class="form-group" style="position: relative;">
                            <label for="patientName">Patient Name</label>
                            <input type="text" id="patientName" class="form-control" placeholder="Enter patient name" autocomplete="off" value="<?= esc($bill['patient_name'] ?? '') ?>" <?= isset($bill['id']) ? 'readonly style="background:#f3f4f6;color:#6b7280;"' : '' ?> >
                            <input type="hidden" id="patientID" name="patient_id" value="<?= esc($bill['patient_id'] ?? '') ?>">
                            <div id="patientList" class="list-group" style="position:absolute; z-index:1000; top:58px; left:0; right:0;"></div>
                        </div>
                        <div class="form-group">
                            <label for="bill_date">Date</label>
                            <input type="date" id="bill_date" name="bill_date" class="form-control" value="<?= esc($bill['bill_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-control">
                                <?php $pm = strtolower($bill['payment_method'] ?? 'cash'); ?>
                                <option value="cash" <?= $pm==='cash'?'selected':'' ?>>Cash</option>
                                <option value="insurance" <?= $pm==='insurance'?'selected':'' ?>>Insurance</option>
                                <option value="hmo" <?= $pm==='hmo'?'selected':'' ?>>HMO</option>
                            </select>
                        </div>
                    </div>
                    <div id="payment_details" class="mt-2"></div>
                </div>

                <div id="hmoSection" class="form-section" style="margin-top:15px; background:#fef6f0; border:1px solid #fcd9c1; border-radius:8px; padding:15px; display: <?= (isset($bill['payment_method']) && strtolower($bill['payment_method']) === 'hmo') ? 'block' : 'none' ?>;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="section-header" style="color:#d97706; margin:0;">HMO Details</h3>
                        <small style="color:#92400e;">Only required when payment method is set to HMO</small>
                    </div>
                    <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px; margin-top:12px;">
                        <div class="form-group">
                            <label for="hmo_provider_id">HMO Provider</label>
                            <select id="hmo_provider_id" name="hmo_provider_id" class="form-control">
                                <option value="">Select Provider</option>
                                <?php $selectedProvider = $bill['hmo_provider_id'] ?? ''; ?>
                                <?php foreach ($hmoProviders as $provider): ?>
                                    <option value="<?= esc($provider['id']) ?>" <?= (string)$selectedProvider === (string)$provider['id'] ? 'selected' : '' ?>>
                                        <?= esc($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hmo_member_no">Member Number</label>
                            <input type="text" id="hmo_member_no" name="hmo_member_no" class="form-control" placeholder="e.g., MAXI-123456" value="<?= esc($bill['hmo_member_no'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_valid_from">Coverage Valid From</label>
                            <input type="date" id="hmo_valid_from" name="hmo_valid_from" class="form-control" value="<?= esc($bill['hmo_valid_from'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_valid_to">Coverage Valid To</label>
                            <input type="date" id="hmo_valid_to" name="hmo_valid_to" class="form-control" value="<?= esc($bill['hmo_valid_to'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_loa_number">Letter of Authorization (LOA) #</label>
                            <input type="text" id="hmo_loa_number" name="hmo_loa_number" class="form-control" placeholder="Enter LOA number" value="<?= esc($bill['hmo_loa_number'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_coverage_limit">Coverage Limit (₱)</label>
                            <input type="number" step="0.01" min="0" id="hmo_coverage_limit" name="hmo_coverage_limit" class="form-control" value="<?= esc($bill['hmo_coverage_limit'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_approved_amount">Approved Amount (₱)</label>
                            <input type="number" step="0.01" min="0" id="hmo_approved_amount" name="hmo_approved_amount" class="form-control" value="<?= esc($bill['hmo_approved_amount'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_patient_share">Patient Share (₱)</label>
                            <input type="number" step="0.01" min="0" id="hmo_patient_share" name="hmo_patient_share" class="form-control" value="<?= esc($bill['hmo_patient_share'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="hmo_status">HMO Status</label>
                            <?php $hmoStatus = strtolower($bill['hmo_status'] ?? 'pending'); ?>
                            <select id="hmo_status" name="hmo_status" class="form-control">
                                <?php $statuses = [
                                    'pending' => 'Pending Pre-Auth',
                                    'submitted' => 'Submitted to HMO',
                                    'approved' => 'Approved',
                                    'denied' => 'Denied',
                                    'paid' => 'Paid'
                                ]; ?>
                                <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $hmoStatus === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column:1 / -1;">
                            <label for="hmo_notes">HMO Notes</label>
                            <textarea id="hmo_notes" name="hmo_notes" class="form-control" rows="2" placeholder="Add reminders, denial reasons, coordination notes..."><?= esc($bill['hmo_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
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
                                </tr>
                            </thead>
                            <tbody id="billItems">
                                <?php if (isset($billItems)): ?>
                                    <?php foreach ($billItems as $item): ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="service[]" class="form-control service" required value="<?= esc($item['service']) ?>">
                                                <input type="hidden" name="lab_id[]" class="lab-id" value="<?= esc($item['lab_id']) ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="qty[]" class="form-control qty" min="1" value="<?= esc($item['qty']) ?>" required>
                                            </td>
                                            <td>
                                                <input type="number" name="price[]" class="form-control price" step="0.01" min="0" value="<?= esc($item['price']) ?>" required>
                                            </td>
                                            <td>
                                                <input type="number" name="amount[]" class="form-control amount" readonly value="<?= esc($item['amount']) ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                    <span id="subtotal">₱<?= esc($bill['subtotal'] ?? '0.00') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span>Tax (12%):</span>
                    <span id="tax">₱<?= esc($bill['tax'] ?? '0.00') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em; 
                            border-top: 1px solid #ddd; padding-top: 10px; margin-top: 8px;">
                    <span>Total:</span>
                    <span id="total">₱<?= esc($bill['total'] ?? '0.00') ?></span>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn" style="flex: 1; background: #95a5a6; color: white;" onclick="window.history.back()">
                    Cancel
                </button>
                <input type="hidden" id="final_amount_field" name="final_amount" value="<?= esc($bill['final_amount'] ?? '0') ?>">
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
            <input type="hidden" name="lab_id[]" class="lab-id">
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
    </tr>
</template>

<script>
// Patient autocomplete
const patientInput = document.getElementById('patientName');
const patientList = document.getElementById('patientList');
const patientID = document.getElementById('patientID');

async function loadPatientServices(patientId){
    try {
        const res = await fetch(`<?= base_url('billing/patient-services') ?>?patient_id=${encodeURIComponent(patientId)}`);
        const data = await res.json();
        const items = Array.isArray(data) ? data : (data.items || []);
        if (items.length) {
            setBillItems(items);
        } else {
            // No items: leave table empty
        }
    } catch (err) {
        try { console.error('Failed to load patient services', err); } catch(_) {}
        ensureAtLeastOneRow();
    }
}

function setBillItems(items){
    const tbody = document.getElementById('billItems');
    const tpl = document.getElementById('billItemTemplate');
    // Clear existing
    tbody.innerHTML = '';
    items.forEach(it => {
        const rowFrag = tpl.content.cloneNode(true);
        const row = rowFrag.querySelector('tr');
        row.querySelector('.service').value = it.service || '';
        row.querySelector('.qty').value = it.qty != null ? it.qty : 1;
        row.querySelector('.price').value = (it.price != null ? it.price : 0).toFixed ? it.price : Number(it.price || 0);
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.amount').value = (it.amount != null ? it.amount : qty * price).toFixed ? (it.amount != null ? it.amount : qty * price) : Number(it.amount || qty * price);
        if (it.lab_id) {
            row.querySelector('.lab-id').value = it.lab_id;
            // lock down auto-filled lab items from editing
            const svcInput = row.querySelector('.service');
            const qtyInput = row.querySelector('.qty');
            const priceInput = row.querySelector('.price');
            svcInput.readOnly = true;
            qtyInput.readOnly = true;
            priceInput.readOnly = true;
            row.setAttribute('data-locked', '1');
            // subtle style cue
            svcInput.style.backgroundColor = '#f5f5f5';
            qtyInput.style.backgroundColor = '#f5f5f5';
            priceInput.style.backgroundColor = '#f5f5f5';
            // disable remove button
            const removeBtn = row.querySelector('.remove-item');
            if (removeBtn) {
                removeBtn.disabled = true;
                removeBtn.classList.add('disabled');
                removeBtn.title = 'Linked to laboratory record';
            }
        }
        tbody.appendChild(rowFrag);
    });
    // Normalize numeric display
    document.querySelectorAll('#billItems .price').forEach(i => i.value = (parseFloat(i.value)||0).toFixed(2));
    document.querySelectorAll('#billItems .amount').forEach(i => i.value = (parseFloat(i.value)||0).toFixed(2));
    updateTotals();
}

function ensureAtLeastOneRow(){ /* no-op: do not auto-add blank rows */ }

// Also react if some other script sets patientID
document.getElementById('patientID')?.addEventListener('change', function(){
    const pid = this.value && this.value.trim();
    if (pid) { loadPatientServices(pid); }
});

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
                    // Auto-load patient's services
                    loadPatientServices(p.id);
                    if (paymentMethod?.value === 'insurance' || paymentMethod?.value === 'hmo') { loadPatientInsurance(p.id); }
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
    // Trigger selection on Enter: pick first suggestion automatically
    patientInput.addEventListener('keydown', async (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = patientList.querySelector('a');
            if (first && first.textContent) {
                first.click();
            } else if (!patientID.value) {
                const term = patientInput.value.trim();
                if (term) {
                    try {
                        const res = await fetch(`<?= base_url('patients/search') ?>?term=${encodeURIComponent(term)}`);
                        const data = await res.json();
                        const results = Array.isArray(data) ? data : (data.patients || []);
                        if (results.length) {
                            patientInput.value = results[0].name;
                            patientID.value = results[0].id;
                            patientList.innerHTML = '';
                            patientList.style.display = 'none';
                            loadPatientServices(results[0].id);
                            if (paymentMethod?.value === 'insurance' || paymentMethod?.value === 'hmo') { loadPatientInsurance(results[0].id); }
                        }
                    } catch {}
                }
            }
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

// No row removal – X button removed

function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.amount').forEach(a => subtotal += parseFloat(a.value) || 0);
    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
}

// Payment method details + Insurance autofill
const paymentMethod = document.getElementById('payment_method');
const paymentDetails = document.getElementById('payment_details');
const hmoSection = document.getElementById('hmoSection');

async function loadPatientInsurance(pid){
    if (!pid) return;
    try {
        const res = await fetch(`<?= base_url('patients/get/') ?>${encodeURIComponent(pid)}`);
        const data = await res.json();
        const p = (data && data.patient) ? data.patient : {};
        const prov = (p.insurance_provider || '').toString();
        const num = (p.insurance_number || '').toString();
        const provInput = document.getElementById('insurance_provider');
        const numInput = document.getElementById('insurance_number');
        if (provInput) provInput.value = prov;
        if (numInput) numInput.value = num;

        const hmoProviderSelect = document.getElementById('hmo_provider_id');
        const hmoMemberInput = document.getElementById('hmo_member_no');
        const hmoValidFrom = document.getElementById('hmo_valid_from');
        const hmoValidTo = document.getElementById('hmo_valid_to');
        if (hmoProviderSelect && !hmoProviderSelect.value && p.hmo_provider_id) {
            hmoProviderSelect.value = p.hmo_provider_id;
        }
        if (hmoMemberInput && !hmoMemberInput.value && p.hmo_member_no) {
            hmoMemberInput.value = p.hmo_member_no;
        }
        if (hmoValidFrom && !hmoValidFrom.value && p.hmo_valid_from) {
            hmoValidFrom.value = p.hmo_valid_from;
        }
        if (hmoValidTo && !hmoValidTo.value && p.hmo_valid_to) {
            hmoValidTo.value = p.hmo_valid_to;
        }
    } catch(e) { /* ignore */ }
}

if (paymentMethod && paymentDetails) {
    function renderPaymentDetails(method){
        paymentDetails.innerHTML = '';
        if (method === 'insurance') {
            paymentDetails.innerHTML = `
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label for="insurance_provider">Insurance Provider</label>
                        <input type="text" id="insurance_provider" name="insurance_provider" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="insurance_number">Insurance Number</label>
                        <input type="text" id="insurance_number" name="insurance_number" class="form-control" readonly>
                    </div>
                </div>
            `;
            const pid = document.getElementById('patientID')?.value?.trim();
            if (pid) loadPatientInsurance(pid);
        }
        if (method !== 'hmo' && hmoSection) {
            hmoSection.style.display = 'none';
        }
    }
    paymentMethod.addEventListener('change', function(){
        renderPaymentDetails(this.value);
        toggleHmoSection();
        if (this.value === 'hmo') {
            const pid = document.getElementById('patientID')?.value?.trim();
            if (pid) loadPatientInsurance(pid);
        }
    });
    // Initial render
    renderPaymentDetails(paymentMethod.value);
}

function toggleHmoSection(){
    if (!hmoSection || !paymentMethod) return;
    hmoSection.style.display = paymentMethod.value === 'hmo' ? 'block' : 'none';
}
toggleHmoSection();

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
    // Require insurance details when needed
    const method = document.getElementById('payment_method')?.value;
    if (method === 'insurance') {
        const provider = document.getElementById('insurance_provider')?.value?.trim();
        if (!provider) {
            e.preventDefault();
            alert('Please select an insurance provider.');
            document.getElementById('insurance_provider')?.focus();
            return;
        }
    }
});

// Do not auto-add initial row

</script>

<?= $this->endSection() ?>