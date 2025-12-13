<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Process Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .hospital-billing-container {
        background: #f8fafc;
        min-height: 100vh;
        padding: 24px;
    }
    
    .billing-header {
        background: #3b82f6;
        color: white;
        padding: 28px 32px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .billing-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .billing-header p {
        margin: 8px 0 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .back-btn {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .back-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-1px);
    }
    
    .modal-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .section-number {
        width: 36px;
        height: 36px;
        background: #3b82f6;
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .form-input {
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
        background: white;
        font-family: inherit;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    
    .form-input:disabled {
        background: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }
    
    .payment-summary-box {
        background: #fef3c7;
        border: 3px solid #f59e0b;
        border-radius: 12px;
        padding: 28px;
        margin-top: 20px;
    }
    
    .summary-label {
        font-size: 14px;
        font-weight: 600;
        color: #92400e;
        margin-bottom: 8px;
    }
    
    .summary-amount {
        font-size: 42px;
        font-weight: 900;
        color: #78350f;
        line-height: 1;
    }
    
    .payment-entry-card {
        background: #f0f9ff;
        border: 2px solid #3b82f6;
        border-radius: 12px;
        padding: 24px;
    }
    
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    
    .payment-history-table th {
        background: #f9fafb;
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .payment-history-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        color: #374151;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }
    
    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59,130,246,0.3);
    }
    
    .btn-primary:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }
    
    .toggle-section {
        background: #f9fafb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .toggle-section:hover {
        background: #f3f4f6;
    }
    
    .toggle-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .toggle-btn {
        background: #e5e7eb;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }
    
    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>

<div class="hospital-billing-container">
    <!-- Header -->
    <div class="billing-header">
            <div>
                <h1><i class="fas fa-money-bill-wave"></i> Process Payment</h1>
                <p>Invoice #<?= 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT) ?> - <?= esc($bill['patient_name'] ?? 'Unknown Patient') ?></p>
            </div>
        </div>
    
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    
    <form id="paymentForm" method="POST" action="<?= base_url('billing/update/' . $bill['id']) ?>">
        <!-- Bill Information Section -->
        <div class="modal-section">
            <div class="section-header">
                <div class="section-number">1</div>
                <h3 class="section-title">Bill Information</h3>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" class="form-input" value="<?= 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Bill Date</label>
                    <input id="em_bill_date" name="bill_date" type="date" class="form-input" value="<?= esc($bill['bill_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Patient Name</label>
                    <input type="text" class="form-input" value="<?= esc($bill['patient_name'] ?? 'N/A') ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Amount (₱)</label>
                    <input id="em_final_amount" name="final_amount" type="text" class="form-input" value="<?= number_format($bill['final_amount'] ?? 0, 2) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select id="em_payment_method" name="payment_method" class="form-input">
                        <option value="cash" <?= (strtolower($bill['payment_method'] ?? 'cash') === 'cash') ? 'selected' : '' ?>>CASH</option>
                        <option value="credit" <?= (strtolower($bill['payment_method'] ?? '') === 'credit') ? 'selected' : '' ?>>CREDIT CARD</option>
                        <option value="debit" <?= (strtolower($bill['payment_method'] ?? '') === 'debit') ? 'selected' : '' ?>>DEBIT CARD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select id="em_payment_status" name="payment_status" class="form-input" disabled>
                        <option value="pending" <?= (strtolower($bill['payment_status'] ?? '') === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="partial" <?= (strtolower($bill['payment_status'] ?? '') === 'partial') ? 'selected' : '' ?>>Partial</option>
                        <option value="paid" <?= (strtolower($bill['payment_status'] ?? '') === 'paid') ? 'selected' : '' ?>>Paid</option>
                    </select>
                    <small style="color:#6b7280; font-size:11px; margin-top:4px; display:block;">Auto-updated based on payments</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount Paid (₱)</label>
                    <input id="em_amount_paid" type="text" class="form-input" value="<?= number_format($bill['total_paid'] ?? $bill['amount_paid'] ?? 0, 2) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Remaining Balance (₱)</label>
                    <input id="em_remaining_balance" type="text" class="form-input" value="<?= number_format($bill['remaining_balance'] ?? 0, 2) ?>" disabled style="color:<?= ($bill['remaining_balance'] ?? 0) > 0 ? '#dc2626' : '#059669' ?>; font-weight:700;">
                </div>
            </div>
        </div>

        <!-- PhilHealth Section -->
        <div class="modal-section">
            <div class="toggle-section" onclick="toggleSection('ph_section')">
                <div class="toggle-section-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="section-number" style="background:#10b981;">2</div>
                        <h3 class="section-title" style="margin:0;">PhilHealth Coverage</h3>
                    </div>
                    <button type="button" class="toggle-btn" id="ph_toggle_btn">Show</button>
                </div>
            </div>
            <div id="ph_section" style="display:none;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding:16px; background:#ecfdf5; border-radius:10px; border:2px solid #10b981;">
                    <input id="em_ph_member" name="philhealth_member" type="checkbox" value="1" style="width:20px; height:20px; cursor:pointer; accent-color:#10b981;" <?= (($bill['philhealth_member'] ?? 0) == 1) ? 'checked' : '' ?>>
                    <div>
                        <label for="em_ph_member" style="margin:0; font-weight:700; color:#065f46; font-size:15px; cursor:pointer;">PhilHealth Member</label>
                        <p style="margin:4px 0 0; color:#047857; font-size:12px;">Check if patient is eligible for PhilHealth benefits</p>
                    </div>
                </div>
                <div id="ph_details" style="background:#f0fdf4; border:2px solid #10b981; border-radius:12px; padding:20px; display:none;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Admission Date</label>
                            <input id="em_admission_date" name="admission_date" type="date" class="form-input" value="<?= esc($bill['admission_date'] ?? $bill['bill_date'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Approved Deduction (₱)</label>
                            <input id="em_ph_approved" name="philhealth_approved_amount" type="number" step="0.01" placeholder="Enter amount or select case rate" class="form-input" value="<?= esc($bill['philhealth_approved_amount'] ?? '') ?>">
                            <small style="color:#047857; font-size:11px; margin-top:4px; display:block;">Enter amount manually or select case rate(s) above</small>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Select Case Rate(s)</label>
                            <select id="em_ph_rate_select" multiple size="4" class="form-input" style="min-height:140px;">
                            </select>
                            <input type="hidden" id="em_ph_rate_id" name="philhealth_selected_rate_id">
                            <input type="hidden" id="em_ph_rate_amount" name="philhealth_selected_amount">
                            <small style="color:#047857; font-size:11px; margin-top:8px; display:block; font-style:italic;">Hold Ctrl/Cmd to select multiple case rates</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Primary RVS Code</label>
                            <input id="em_primary_rvs" name="primary_rvs_code" type="text" placeholder="e.g., 48010" class="form-input" value="<?= esc($bill['primary_rvs_code'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Remaining Balance (₱)</label>
                            <input id="em_remaining" type="text" class="form-input" value="<?= number_format(max(0, ($bill['final_amount'] ?? 0) - ($bill['total_paid'] ?? 0) - ($bill['philhealth_approved_amount'] ?? 0)), 2) ?>" disabled>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Reason / Note</label>
                            <textarea id="em_ph_note" name="philhealth_note" rows="3" placeholder="Required if codes missing or approved amount is less than suggested" class="form-input" style="resize:vertical;"><?= esc($bill['philhealth_note'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HMO Section -->
        <div class="modal-section">
            <div class="toggle-section" onclick="toggleSection('hmo_section')">
                <div class="toggle-section-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="section-number" style="background:#3b82f6;">3</div>
                        <h3 class="section-title" style="margin:0;">HMO Coverage</h3>
                    </div>
                    <button type="button" class="toggle-btn" id="hmo_toggle_btn">Show</button>
                </div>
            </div>
            <div id="hmo_section" style="display:none;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding:16px; background:#eff6ff; border-radius:10px; border:2px solid #3b82f6;">
                    <input id="em_hmo_enabled" name="use_hmo" type="checkbox" value="1" style="width:20px; height:20px; cursor:pointer; accent-color:#3b82f6;">
                    <div>
                        <label for="em_hmo_enabled" style="margin:0; font-weight:700; color:#1e40af; font-size:15px; cursor:pointer;">Use HMO Coverage</label>
                        <p style="margin:4px 0 0; color:#1d4ed8; font-size:12px;">Check if patient has an approved HMO Letter of Authorization (LOA)</p>
                    </div>
                </div>
                <div id="hmo_details" style="background:#eff6ff; border:2px solid #3b82f6; border-radius:12px; padding:20px; display:none;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">HMO Provider</label>
                            <select id="em_hmo_provider" name="hmo_provider_id" class="form-input" disabled>
                                <option value="">Select HMO Provider</option>
                                <?php foreach ($hmoProviders as $provider): ?>
                                    <option value="<?= $provider['id'] ?>" <?= (($bill['hmo_provider_id'] ?? 0) == $provider['id']) ? 'selected' : '' ?>>
                                        <?= esc($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="em_hmo_provider_hidden" name="hmo_provider_id" value="<?= esc($bill['hmo_provider_id'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Member Number</label>
                            <input id="em_hmo_member_no" name="hmo_member_no" type="text" class="form-input" placeholder="e.g., MAXI-123456" value="<?= esc($bill['hmo_member_no'] ?? '') ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">LOA Number</label>
                            <input id="em_hmo_loa_number" name="hmo_loa_number" type="text" class="form-input" placeholder="LOA Reference" value="<?= esc($bill['hmo_loa_number'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coverage Valid From</label>
                            <input id="em_hmo_valid_from" name="hmo_valid_from" type="date" class="form-input" value="<?= esc($bill['hmo_valid_from'] ?? '') ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coverage Valid To</label>
                            <input id="em_hmo_valid_to" name="hmo_valid_to" type="date" class="form-input" value="<?= esc($bill['hmo_valid_to'] ?? '') ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Approved Amount (₱)</label>
                            <input id="em_hmo_approved_amount" name="hmo_approved_amount" type="number" step="0.01" class="form-input" value="<?= esc($bill['hmo_approved_amount'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Patient Share (₱)</label>
                            <input id="em_hmo_patient_share" name="hmo_patient_share" type="number" step="0.01" class="form-input" value="<?= number_format(max(0, ($bill['final_amount'] ?? 0) - ($bill['philhealth_approved_amount'] ?? 0) - ($bill['hmo_approved_amount'] ?? 0)), 2) ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label">HMO Notes</label>
                        <textarea id="em_hmo_notes" name="hmo_notes" rows="3" class="form-input" style="resize:vertical;" placeholder="Additional details for HMO claim"><?= esc($bill['hmo_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Entry Section -->
        <div class="modal-section">
            <div class="section-header">
                <div class="section-number" style="background:#f59e0b;">4</div>
                <h3 class="section-title">Process Payment</h3>
            </div>
            <div class="payment-entry-card">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Amount (₱)</label>
                        <input id="payment_amount" type="number" step="0.01" min="0.01" placeholder="0.00" class="form-input" style="font-weight:600; font-size:16px;">
                        <small id="payment_amount_helper" style="display:block; margin-top:4px; font-size:12px; color:#6b7280;"></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select id="payment_method_entry" class="form-input">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit Card</option>
                            <option value="debit">Debit Card</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Date & Time</label>
                        <input id="payment_date" type="text" class="form-input" value="" disabled style="background:#f9fafb;">
                        <small style="color:#6b7280; font-size:11px; margin-top:4px; display:block;">Automatically set to current date and time</small>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea id="payment_notes" rows="2" placeholder="Payment notes..." class="form-input" style="resize:vertical;"></textarea>
                </div>
                <div style="margin-top:20px;">
                    <button type="button" id="process_payment_btn" class="btn-primary">
                        <i class="fas fa-money-bill-wave"></i> Process Payment
                    </button>
                </div>
            </div>
            
            <!-- Payment History -->
            <div style="margin-top:24px;">
                <h4 style="margin:0 0 16px; color:#374151; font-weight:700; font-size:16px;">
                    <i class="fas fa-history"></i> Payment History
                </h4>
                <div id="payment_history" style="max-height:300px; overflow-y:auto; background:#f9fafb; border-radius:8px; padding:16px;">
                    <div style="text-align:center; padding:20px; color:#9ca3af;">
                        <i class="fas fa-spinner fa-spin"></i> Loading payment history...
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="modal-section" style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="<?= base_url('billing') ?>" class="btn-primary" style="background:#6b7280; text-decoration:none; text-align:center; width:auto; padding:14px 28px;">
                <i class="fas fa-arrow-left"></i> Back to Billing
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const bill = <?= json_encode($bill) ?>;
    const hmoProviders = <?= json_encode($hmoProviders ?? []) ?>;
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Load payment history
        loadPaymentHistory();
        
        // Initialize payment date field with current date/time and keep it updated
        const updatePaymentDateTime = () => {
            const dateEl = document.getElementById('payment_date');
            if (dateEl) {
                const now = new Date();
                const dateStr = now.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const timeStr = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit'
                });
                dateEl.value = `${dateStr} at ${timeStr}`;
            }
        };
        
        // Update payment date/time on load and keep it current
        updatePaymentDateTime();
        // Update every second to keep it current
        setInterval(updatePaymentDateTime, 1000);
        
        // Setup PhilHealth toggle
        const phMemberEl = document.getElementById('em_ph_member');
        const phDetails = document.getElementById('ph_details');
        if (phMemberEl) {
            phMemberEl.addEventListener('change', function() {
                if (phDetails) {
                    phDetails.style.display = this.checked ? '' : 'none';
                }
                updateRemaining();
            });
            // Trigger on load
            if (phMemberEl.checked && phDetails) {
                phDetails.style.display = '';
            }
        }
        
        // Setup HMO toggle
        const hmoEnabledEl = document.getElementById('em_hmo_enabled');
        const hmoDetails = document.getElementById('hmo_details');
        if (hmoEnabledEl) {
            hmoEnabledEl.addEventListener('change', function() {
                if (hmoDetails) {
                    hmoDetails.style.display = this.checked ? '' : 'none';
                }
                updateRemaining();
            });
        }
        
        // Setup rate loading
        const rvsEl = document.getElementById('em_primary_rvs');
        const adEl = document.getElementById('em_admission_date');
        const rateSel = document.getElementById('em_ph_rate_select');
        
        if (rvsEl && adEl && rateSel) {
            ['change', 'input'].forEach(event => {
                rvsEl.addEventListener(event, loadRates);
                adEl.addEventListener(event, loadRates);
            });
            rateSel.addEventListener('change', applyRateSelectionSummary);
            loadRates();
        }
        
        // Setup amount updates
        const finalAmountEl = document.getElementById('em_final_amount');
        const approvedEl = document.getElementById('em_ph_approved');
        const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
        
        ['input','change'].forEach(evt => {
            if (approvedEl) approvedEl.addEventListener(evt, updateRemaining);
            if (hmoApprovedEl) hmoApprovedEl.addEventListener(evt, updateRemaining);
            if (finalAmountEl) finalAmountEl.addEventListener(evt, updateRemaining);
        });
        
        // Add event listener for payment amount input to update remaining balance in real-time
        const paymentAmountEl = document.getElementById('payment_amount');
        if (paymentAmountEl) {
            ['input', 'change'].forEach(evt => {
                paymentAmountEl.addEventListener(evt, updateRemainingWithPayment);
            });
        }
        
        updateRemaining();
    });
    
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const toggleBtn = document.getElementById(sectionId.replace('_section', '_toggle_btn'));
        if (section && toggleBtn) {
            const isHidden = section.style.display === 'none';
            section.style.display = isHidden ? '' : 'none';
            toggleBtn.textContent = isHidden ? 'Hide' : 'Show';
        }
    }
    
    function updateRemaining() {
        const finalAmountEl = document.getElementById('em_final_amount');
        const approvedEl = document.getElementById('em_ph_approved');
        const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
        const remainingEl = document.getElementById('em_remaining');
        const remainingBalanceEl = document.getElementById('em_remaining_balance');
        const hmoPatientShareEl = document.getElementById('em_hmo_patient_share');
        
        // Helper function to parse number with commas
        const parseNumber = (value) => {
            if (!value) return 0;
            // Remove commas and parse
            const cleaned = String(value).replace(/,/g, '');
            return parseFloat(cleaned) || 0;
        };
        
        const gross = parseNumber(finalAmountEl?.value || bill.final_amount || 0);
        const amountPaidEl = document.getElementById('em_amount_paid');
        const paidValue = amountPaidEl?.value || bill.total_paid || bill.amount_paid || 0;
        const paid = parseNumber(paidValue);
        const phEnabled = document.getElementById('em_ph_member')?.checked || false;
        const phAppr = phEnabled ? parseNumber(approvedEl?.value || 0) : 0;
        const hmoEnabled = document.getElementById('em_hmo_enabled')?.checked || false;
        const hmoAppr = hmoEnabled ? parseNumber(hmoApprovedEl?.value || 0) : 0;
        
        const remainingAfterPh = Math.max(gross - paid - phAppr, 0);
        if (remainingEl) remainingEl.value = remainingAfterPh.toFixed(2);
        
        if (remainingBalanceEl) {
            const remaining = Math.max(gross - paid - phAppr - hmoAppr, 0);
            remainingBalanceEl.value = remaining.toFixed(2);
            remainingBalanceEl.style.color = remaining > 0 ? '#dc2626' : '#059669';
        }
        
        if (hmoPatientShareEl) {
            const base = parseNumber(remainingEl?.value || '0');
            const patientShare = hmoEnabled ? Math.max(base - hmoAppr, 0) : base;
            hmoPatientShareEl.value = patientShare.toFixed(2);
        }
        
        // Update remaining balance considering the payment amount being entered
        updateRemainingWithPayment();
    }
    
    function updateRemainingWithPayment() {
        const paymentAmountEl = document.getElementById('payment_amount');
        const remainingBalanceEl = document.getElementById('em_remaining_balance');
        const paymentAmountHelperEl = document.getElementById('payment_amount_helper');
        
        if (!paymentAmountEl || !remainingBalanceEl) return;
        
        // Helper function to parse number with commas
        const parseNumber = (value) => {
            if (!value) return 0;
            const cleaned = String(value).replace(/,/g, '');
            return parseFloat(cleaned) || 0;
        };
        
        // Get current values
        const finalAmountEl = document.getElementById('em_final_amount');
        const amountPaidEl = document.getElementById('em_amount_paid');
        const approvedEl = document.getElementById('em_ph_approved');
        const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
        
        const gross = parseNumber(finalAmountEl?.value || bill.final_amount || 0);
        const paidValue = amountPaidEl?.value || bill.total_paid || bill.amount_paid || 0;
        const currentPaid = parseNumber(paidValue);
        const phEnabled = document.getElementById('em_ph_member')?.checked || false;
        const phAppr = phEnabled ? parseNumber(approvedEl?.value || 0) : 0;
        const hmoEnabled = document.getElementById('em_hmo_enabled')?.checked || false;
        const hmoAppr = hmoEnabled ? parseNumber(hmoApprovedEl?.value || 0) : 0;
        
        // Calculate patient share
        const patientShare = Math.max(0, gross - phAppr - hmoAppr);
        const remainingPatientShare = Math.max(0, patientShare - currentPaid);
        
        // Get payment amount being entered
        const paymentAmount = parseFloat(paymentAmountEl.value || 0) || 0;
        
        // Calculate remaining after this payment
        const remainingAfterPayment = Math.max(0, remainingPatientShare - paymentAmount);
        
        // Update the remaining balance field to show what it will be after payment
        if (paymentAmount > 0) {
            remainingBalanceEl.value = remainingAfterPayment.toFixed(2);
            remainingBalanceEl.style.color = remainingAfterPayment > 0 ? '#dc2626' : '#059669';
            
            // Update helper text
            if (paymentAmountHelperEl) {
                if (paymentAmount > remainingPatientShare + 0.01) {
                    paymentAmountHelperEl.style.color = '#dc2626';
                    paymentAmountHelperEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> Amount exceeds remaining patient share (₱${remainingPatientShare.toFixed(2)})`;
                } else if (remainingAfterPayment <= 0.01) {
                    paymentAmountHelperEl.style.color = '#059669';
                    paymentAmountHelperEl.innerHTML = `<i class="fas fa-check-circle"></i> This payment will fully settle the bill`;
                } else {
                    paymentAmountHelperEl.style.color = '#6b7280';
                    paymentAmountHelperEl.innerHTML = `Remaining after this payment: ₱${remainingAfterPayment.toFixed(2)}`;
                }
            }
        } else {
            // Reset to current remaining balance if no payment amount
            const currentRemaining = Math.max(0, remainingPatientShare);
            remainingBalanceEl.value = currentRemaining.toFixed(2);
            remainingBalanceEl.style.color = currentRemaining > 0 ? '#dc2626' : '#059669';
            
            if (paymentAmountHelperEl) {
                paymentAmountHelperEl.innerHTML = '';
            }
        }
    }
    
    function applyRateSelectionSummary() {
        const rateSel = document.getElementById('em_ph_rate_select');
        const rateId = document.getElementById('em_ph_rate_id');
        const rateAmt = document.getElementById('em_ph_rate_amount');
        const approvedEl = document.getElementById('em_ph_approved');
        const rvsEl = document.getElementById('em_primary_rvs');
        
        if (!rateSel) return;
        
        const selectedOptions = Array.from(rateSel.selectedOptions || []);
        const ids = selectedOptions.map(opt => opt.value).filter(Boolean);
        if (rateId) rateId.value = JSON.stringify(ids);
        
        const totalAmount = selectedOptions.reduce((sum, opt) => {
            const amt = parseFloat(opt.dataset.amount || '0');
            return sum + (Number.isFinite(amt) ? amt : 0);
        }, 0);
        
        if (rateAmt) {
            rateAmt.value = ids.length ? totalAmount.toFixed(2) : '';
        }
        
        if (approvedEl) {
            if (ids.length) {
                // Auto-fill the approved amount when case rates are selected, but keep field editable
                approvedEl.value = totalAmount.toFixed(2);
            }
            // Don't disable or clear the field - allow manual input
        }
        
        if (selectedOptions.length === 1) {
            const opt = selectedOptions[0];
            const ctype = (opt.dataset.codeType || '').toUpperCase();
            const cval = opt.dataset.code || '';
            if (ctype === 'RVS' && rvsEl) {
                rvsEl.value = cval;
            }
        }
        
        updateRemaining();
    }
    
    async function loadRates() {
        const rateSel = document.getElementById('em_ph_rate_select');
        const rvsEl = document.getElementById('em_primary_rvs');
        const adEl = document.getElementById('em_admission_date');
        
        if (!rateSel) return;
        
        const rvs = (rvsEl?.value || '').trim();
        const ad = (adEl?.value || '').trim();
        
        rateSel.innerHTML = '<option value="" disabled>Loading…</option>';
        
        try {
            const qs = new URLSearchParams();
            if (rvs) qs.append('rvs', rvs);
            if (ad) qs.append('admission', ad);
            const url = '<?= base_url('billing/caseRates') ?>' + (qs.toString() ? ('?' + qs.toString()) : '');
            const res = await fetch(url);
            const data = await res.json();
            
            const grouped = data?.grouped || {};
            const rates = Array.isArray(data?.rates) ? data.rates : [];
            
            rateSel.innerHTML = '';
            
            if (Object.keys(grouped).length > 0) {
                Object.keys(grouped).forEach(category => {
                    const group = grouped[category];
                    const optgroup = document.createElement('optgroup');
                    optgroup.label = group.label || category;
                    
                    (group.rates || []).forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.textContent = r.label;
                        opt.dataset.amount = String(r.amount || '0');
                        opt.dataset.codeType = r.code_type || '';
                        opt.dataset.code = r.code || '';
                        optgroup.appendChild(opt);
                    });
                    
                    if (optgroup.children.length > 0) {
                        rateSel.appendChild(optgroup);
                    }
                });
            } else {
                rates.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.id;
                    opt.textContent = r.label;
                    opt.dataset.amount = String(r.amount || '0');
                    opt.dataset.codeType = r.code_type || '';
                    opt.dataset.code = r.code || '';
                    rateSel.appendChild(opt);
                });
            }
            
            // Restore saved selections
            const savedRateIds = bill.philhealth_rate_ids ? JSON.parse(bill.philhealth_rate_ids) : (bill.philhealth_rate_ids_calc || []);
            if (savedRateIds.length) {
                const savedSet = new Set(savedRateIds.map(id => String(id)));
                const allOptions = rateSel.querySelectorAll('option');
                allOptions.forEach(opt => {
                    if (opt.value && savedSet.has(String(opt.value))) {
                        opt.selected = true;
                    }
                });
                applyRateSelectionSummary();
            }
        } catch(e) {
            console.error('Error loading rates:', e);
            rateSel.innerHTML = '<option value="">Error loading rates</option>';
        }
    }
    
    async function loadPaymentHistory() {
        const paymentHistoryEl = document.getElementById('payment_history');
        if (!paymentHistoryEl || !bill.id) return;
        
        try {
            const res = await fetch(`<?= base_url('billing/getPayments') ?>/${bill.id}`);
            const data = await res.json();
            
            if (data.success && data.data.payments) {
                const payments = data.data.payments || [];
                if (payments.length === 0) {
                    paymentHistoryEl.innerHTML = '<div style="text-align:center; padding:20px; color:#9ca3af;"><i class="fas fa-inbox"></i><br>No payments recorded yet</div>';
                    return;
                }
                
                let html = '<table class="payment-history-table">';
                html += '<thead><tr>';
                html += '<th>Date</th>';
                html += '<th style="text-align:right;">Amount</th>';
                html += '<th style="text-align:center;">Method</th>';
                html += '<th style="text-align:center;">Action</th>';
                html += '</tr></thead><tbody>';
                
                payments.forEach(p => {
                    const date = new Date(p.payment_date || p.created_at);
                    const dateStr = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                    const method = (p.payment_method || 'cash').toUpperCase();
                    
                    html += `<tr>`;
                    html += `<td>${dateStr}</td>`;
                    html += `<td style="text-align:right; font-weight:600; color:#059669;">₱${parseFloat(p.amount || 0).toFixed(2)}</td>`;
                    html += `<td style="text-align:center;">${method}</td>`;
                    html += `<td style="text-align:center;">`;
                    html += `<a href="<?= base_url('billing/paymentReceipt') ?>/${p.id}" target="_blank" style="color:#3b82f6; text-decoration:none; font-size:12px; font-weight:600;">Receipt</a>`;
                    html += `</td>`;
                    html += `</tr>`;
                });
                
                html += '</tbody></table>';
                paymentHistoryEl.innerHTML = html;
            } else {
                paymentHistoryEl.innerHTML = '<div style="text-align:center; padding:20px; color:#9ca3af;"><i class="fas fa-inbox"></i><br>No payments recorded yet</div>';
            }
        } catch(e) {
            console.error('Error loading payment history:', e);
            paymentHistoryEl.innerHTML = '<div style="text-align:center; padding:20px; color:#dc2626;"><i class="fas fa-exclamation-triangle"></i><br>Error loading payment history</div>';
        }
    }
    
    async function processPayment() {
        const amountEl = document.getElementById('payment_amount');
        const methodEl = document.getElementById('payment_method_entry');
        const dateEl = document.getElementById('payment_date');
        const notesEl = document.getElementById('payment_notes');
        const btnEl = document.getElementById('process_payment_btn');
        
        if (!amountEl || !bill.id) return;
        
        const amount = parseFloat(amountEl.value || 0);
        if (amount <= 0) {
            Swal.fire('Error', 'Please enter a valid payment amount', 'error');
            return;
        }
        
        // Helper function to parse number with commas
        const parseNumber = (value) => {
            if (!value) return 0;
            const cleaned = String(value).replace(/,/g, '');
            return parseFloat(cleaned) || 0;
        };
        
        const finalAmount = parseFloat(bill.final_amount || 0);
        const amountPaidValue = document.getElementById('em_amount_paid')?.value || bill.total_paid || bill.amount_paid || 0;
        const currentPaid = parseNumber(amountPaidValue);
        const phAmount = parseNumber(document.getElementById('em_ph_approved')?.value || bill.philhealth_approved_amount || 0);
        const hmoAmount = parseNumber(document.getElementById('em_hmo_approved_amount')?.value || bill.hmo_approved_amount || 0);
        
        // Calculate patient share (total amount minus deductions)
        const patientShare = Math.max(0, finalAmount - phAmount - hmoAmount);
        // Calculate remaining patient share (what patient still needs to pay)
        const remainingPatientShare = Math.max(0, patientShare - currentPaid);
        
        if (amount > remainingPatientShare + 0.01) { // Allow slight overpayment for rounding
            Swal.fire('Error', `Payment amount exceeds remaining patient share. Remaining: ₱${remainingPatientShare.toFixed(2)}`, 'error');
            return;
        }
        
        // Show confirmation dialog with payment summary
        const paymentMethod = methodEl?.value || 'cash';
        const paymentDate = new Date().toLocaleString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const newTotalPaid = currentPaid + amount;
        const newRemainingPatientShare = Math.max(0, remainingPatientShare - amount);
        const willBeFullyPaid = newRemainingPatientShare <= 0.01;
        
        const confirmResult = await Swal.fire({
            title: 'Confirm Payment',
            html: `
                <div style="text-align:left; padding:16px;">
                    <div style="margin-bottom:16px;">
                        <strong>Payment Details:</strong>
                        <div style="margin-top:8px; padding:12px; background:#f3f4f6; border-radius:8px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span>Payment Amount:</span>
                                <strong>₱${amount.toFixed(2)}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span>Payment Method:</span>
                                <strong>${paymentMethod.toUpperCase()}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span>Payment Date:</span>
                                <strong>${paymentDate}</strong>
                            </div>
                            <hr style="margin:12px 0; border-color:#e5e7eb;">
                            <div style="margin-bottom:12px; padding:12px; background:#f0f9ff; border-radius:8px; border:2px solid #3b82f6;">
                                <div style="font-weight:700; color:#1e40af; margin-bottom:8px; font-size:13px;">BILL BREAKDOWN</div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:13px;">
                                    <span>Total Bill Amount:</span>
                                    <strong>₱${finalAmount.toFixed(2)}</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:13px; color:#059669;">
                                    <span>PhilHealth Deduction:</span>
                                    <strong>-₱${phAmount.toFixed(2)}</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:13px; color:#059669;">
                                    <span>HMO Deduction:</span>
                                    <strong>-₱${hmoAmount.toFixed(2)}</strong>
                                </div>
                                <hr style="margin:8px 0; border-color:#bfdbfe;">
                                <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700; color:#1e40af;">
                                    <span>Patient Share (Total Due):</span>
                                    <strong>₱${patientShare.toFixed(2)}</strong>
                                </div>
                            </div>
                            <div style="margin-bottom:12px; padding:12px; background:#fef3c7; border-radius:8px; border:2px solid #f59e0b;">
                                <div style="font-weight:700; color:#92400e; margin-bottom:8px; font-size:13px;">PAYMENT SUMMARY</div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px;">
                                    <span>Amount Already Paid:</span>
                                    <span>₱${currentPaid.toFixed(2)}</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; color:#059669;">
                                    <span>This Payment:</span>
                                    <strong>+₱${amount.toFixed(2)}</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:700;">
                                    <span>Total Paid After This Payment:</span>
                                    <strong style="color:#059669;">₱${newTotalPaid.toFixed(2)}</strong>
                                </div>
                                <hr style="margin:8px 0; border-color:#fde68a;">
                                <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700;">
                                    <span>Remaining Patient Share:</span>
                                    <strong style="color:${newRemainingPatientShare > 0 ? '#dc2626' : '#059669'}; font-size:16px;">₱${newRemainingPatientShare.toFixed(2)}</strong>
                                </div>
                                <div style="margin-top:8px; font-size:12px; color:#92400e;">
                                    Calculation: ₱${patientShare.toFixed(2)} (Patient Share) - ₱${newTotalPaid.toFixed(2)} (Total Paid) = ₱${newRemainingPatientShare.toFixed(2)}
                                </div>
                            </div>
                            ${willBeFullyPaid ? '<div style="margin-top:12px; padding:8px; background:#d1fae5; border-radius:6px; color:#065f46; font-weight:600; text-align:center;">✓ Bill will be fully paid</div>' : ''}
                        </div>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Confirm & Process Payment',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            focusConfirm: false,
            reverseButtons: true
        });
        
        if (!confirmResult.isConfirmed) {
            return; // User cancelled
        }
        
        if (btnEl) btnEl.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('billing_id', bill.id);
            formData.append('amount', amount);
            formData.append('payment_method', methodEl?.value || 'cash');
            // Include current PhilHealth and HMO amounts from form (in case bill hasn't been saved yet)
            const phApprovedEl = document.getElementById('em_ph_approved');
            const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
            if (phApprovedEl && phApprovedEl.value) {
                formData.append('philhealth_approved_amount', phApprovedEl.value);
            }
            if (hmoApprovedEl && hmoApprovedEl.value) {
                formData.append('hmo_approved_amount', hmoApprovedEl.value);
            }
            // Always use current date/time in MySQL format (YYYY-MM-DD HH:mm:ss)
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const paymentDateValue = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            formData.append('payment_date', paymentDateValue);
            if (notesEl?.value) formData.append('notes', notesEl.value);
            
            const res = await fetch(`<?= base_url('billing/processPayment') ?>/${bill.id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await res.json();
            
            if (data.success) {
                const isFullyPaid = data.data.payment_status === 'paid';
                const remaining = data.data.remaining_balance || 0;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Processed Successfully',
                    html: `
                        <div style="text-align:left; padding:16px;">
                            <p style="margin-bottom:16px;">Payment of <strong>₱${amount.toFixed(2)}</strong> has been recorded successfully.</p>
                            <div style="padding:12px; background:#f3f4f6; border-radius:8px; margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <span>Total Paid:</span>
                                    <strong>₱${data.data.amount_paid.toFixed(2)}</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span>Remaining Balance:</span>
                                    <strong style="color:${remaining > 0 ? '#dc2626' : '#059669'};">₱${remaining.toFixed(2)}</strong>
                                </div>
                            </div>
                            ${isFullyPaid ? '<div style="padding:12px; background:#d1fae5; border-radius:8px; color:#065f46; font-weight:600; text-align:center; margin-bottom:16px;">✓ Bill is now fully paid!</div>' : ''}
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: '<i class="fas fa-receipt"></i> View Receipt',
                    showCancelButton: true,
                    cancelButtonText: '<i class="fas fa-check"></i> Done',
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#10b981',
                    focusConfirm: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed && data.data.payment_id) {
                        window.open(`<?= base_url('billing/paymentReceipt') ?>/${data.data.payment_id}`, '_blank');
                    }
                    // Update amount paid field with new value from server
                    const amountPaidEl = document.getElementById('em_amount_paid');
                    if (amountPaidEl && data.data.amount_paid !== undefined) {
                        // Format with commas for display
                        const formatted = parseFloat(data.data.amount_paid).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        amountPaidEl.value = formatted;
                    }
                    // Refresh payment data without full page reload
                    loadPaymentHistory();
                    updateRemaining(); // Update remaining balance display
                    // Clear form (date/time is automatic, no need to reset)
                    if (amountEl) amountEl.value = '';
                    if (notesEl) notesEl.value = '';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: data.message || 'Failed to process payment',
                    confirmButtonText: 'OK'
                });
            }
        } catch(e) {
            console.error('Error processing payment:', e);
            Swal.fire('Error', 'Failed to process payment. Please try again.', 'error');
        } finally {
            if (btnEl) btnEl.disabled = false;
        }
    }
    
    // Attach payment button event
    document.getElementById('process_payment_btn')?.addEventListener('click', processPayment);
</script>
<?= $this->endSection() ?>

