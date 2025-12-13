<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* Billing-specific styles */
    .bill-number {
        font-weight: 700;
        color: #1e40af;
        font-family: 'Courier New', monospace;
    }
    
    .patient-name {
        font-weight: 600;
        color: #111827;
    }
    
    .bill-date {
        color: #6b7280;
    }
    
    .bill-amount {
        font-weight: 700;
        color: #111827;
        font-size: 15px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-partial {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-pending {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-view {
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .btn-view:hover {
        background: #c7d2fe;
    }
    
    .btn-payment {
        background: #3b82f6;
        color: white;
    }
    
    .btn-payment:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59,130,246,0.3);
    }
    
    .btn-history {
        background: #10b981;
        color: white;
    }
    
    .btn-history:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16,185,129,0.3);
    }
    
    /* Payment History Modal */
    .payment-history-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    
    .payment-history-modal.active {
        display: flex;
    }
    
    .payment-history-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }
    
    .payment-history-header {
        padding: 24px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .payment-history-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }
    
    .payment-history-close {
        background: #f3f4f6;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 18px;
        transition: all 0.2s;
    }
    
    .payment-history-close:hover {
        background: #e5e7eb;
        color: #111827;
    }
    
    .payment-history-body {
        padding: 24px;
    }
    
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-history-table th {
        background: #f9fafb;
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .payment-history-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #374151;
    }
    
    .payment-history-table tr:hover {
        background: #f9fafb;
    }
    
    .payment-history-summary {
        background: #f0f9ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .payment-history-summary h4 {
        margin: 0 0 12px;
        font-size: 14px;
        font-weight: 600;
        color: #1e40af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .payment-history-summary .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .payment-history-summary .summary-row:last-child {
        margin-bottom: 0;
        padding-top: 8px;
        border-top: 1px solid #bfdbfe;
        font-weight: 700;
        font-size: 16px;
        color: #1e40af;
    }
    
    /* Payment Modal Styles */
    .hospital-modal {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
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
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
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
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="header-left">
            <h1 class="page-title">Billing & Payment Management</h1>
        </div>
        <div class="header-right" style="display: flex; gap: 10px;">
            <!-- Add action buttons here if needed -->
        </div>
    </div>

    <div class="content">
        <!-- Statistics Cards -->
        <div class="card-container">
            <div class="card">
                <h3>Total Revenue</h3>
                <div class="value">₱<?= number_format($totals['totalRevenue'] ?? 0, 2) ?></div>
            </div>
            <div class="card">
                <h3>Pending Bills</h3>
                <div class="value"><?= (int)($totals['pendingCount'] ?? 0) ?></div>
            </div>
            <div class="card">
                <h3>Paid This Month</h3>
                <div class="value">₱<?= number_format($totals['paidThisMonth'] ?? 0, 2) ?></div>
            </div>
            <div class="card">
                <h3>Outstanding Balance</h3>
                <div class="value">₱<?= number_format($totals['outstanding'] ?? 0, 2) ?></div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="medicine-search-wrapper">
            <div class="medicine-search-row">
                <i class="fas fa-search medicine-search-icon"></i>
                <input type="text" id="searchInput" class="medicine-search-field" placeholder="Search by Invoice Number, Patient Name, or Date..." value="<?= esc($query ?? '') ?>" autocomplete="off">
                <button type="button" id="clearSearch" class="medicine-search-clear">Clear</button>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="table-responsive">
            <table class="data-table">
                    <thead>
                        <tr>
                        <th>Invoice #</th>
                            <th>Patient Name</th>
                        <th>Bill Date</th>
                        <th>Service Type</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="billingTableBody">
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <?php 
                                $billNumber = 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT);
                                $patientName = esc($bill['patient_name'] ?? 'N/A');
                                $billDate = esc($bill['bill_date'] ?? '');
                                $serviceName = esc($bill['service_name'] ?? '—');
                                $searchableText = strtolower($billNumber . ' ' . $patientName . ' ' . $billDate . ' ' . $serviceName);
                                $ps = strtolower($bill['payment_status'] ?? 'pending');
                            ?>
                            <tr data-id="<?= (int)$bill['id'] ?>" data-search="<?= htmlspecialchars($searchableText) ?>">
                                <td><span class="bill-number">#<?= $billNumber ?></span></td>
                                <td><span class="patient-name"><?= $patientName ?></span></td>
                                <td><span class="bill-date"><?= $billDate ?></span></td>
                                <td><?= $serviceName ?></td>
                                <td><span class="bill-amount">₱<?= number_format((float)($bill['final_amount'] ?? 0), 2) ?></span></td>
                                <td>
                                    <span class="status-badge status-<?= $ps ?>">
                                        <?= ucfirst($ps) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= base_url('billing/show/' . (int)$bill['id']) ?>" class="btn-action btn-view" target="_blank">
                                            <i class="fas fa-file-invoice"></i> SOA
                                        </a>
                                    <?php if ($ps !== 'paid'): ?>
                                            <a href="<?= base_url('billing/payment/' . (int)$bill['id']) ?>" class="btn-action btn-payment">
                                                <i class="fas fa-money-bill-wave"></i> Process Payment
                                            </a>
                                    <?php endif; ?>
                                        <?php 
                                            $hasPayments = ($ps === 'paid' || $ps === 'partial') || ((float)($bill['amount_paid'] ?? 0) > 0);
                                        ?>
                                        <?php if ($hasPayments): ?>
                                            <button type="button" class="btn-action btn-history" onclick="showPaymentHistory(<?= (int)$bill['id'] ?>)">
                                                <i class="fas fa-history"></i> Payment History
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-results-row">
                            <td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">
                                <i class="fas fa-inbox" style="font-size:48px; margin-bottom:16px; display:block; opacity:0.5;"></i>
                                No bills found
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.hmoProviders = <?= json_encode($hmoProviders ?? []) ?>;
</script>

<script>
    // Payment processing is now handled on a separate page at /billing/payment/{id}
    // All payment functionality has been moved to payment_process.php
            <div class="hospital-modal" style="text-align:left;">
                <!-- Bill Information Section -->
                <div class="modal-section">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h3 class="section-title">Bill Information</h3>
                        </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-input" value="${('INV-' + String(bill.id).padStart(6, '0'))}" disabled>
                    </div>
                        <div class="form-group">
                            <label class="form-label">Bill Date</label>
                            <input id="em_bill_date" type="date" class="form-input" value="${bill.bill_date || ''}">
                </div>
                        <div class="form-group">
                            <label class="form-label">Patient Name</label>
                            <input type="text" class="form-input" value="${bill.patient_name || 'N/A'}" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Amount (₱)</label>
                            <input id="em_final_amount" type="number" step="0.01" class="form-input" value="${bill.final_amount || 0}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select id="em_payment_method" class="form-input">
                                ${['cash','credit','debit'].map(m => `<option value="${m}" ${String(bill.payment_method||'cash').toLowerCase()===m?'selected':''}>${m === 'cash' ? 'CASH' : (m === 'credit' ? 'CREDIT CARD' : 'DEBIT CARD')}</option>`).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Status</label>
                            <select id="em_payment_status" class="form-input" disabled>
                                ${['pending','partial','paid'].map(s => `<option value="${s}" ${String(bill.payment_status||'').toLowerCase()===s?'selected':''}>${s.charAt(0).toUpperCase()+s.slice(1)}</option>`).join('')}
                            </select>
                            <small style="color:#6b7280; font-size:11px; margin-top:4px; display:block;">Auto-updated based on payments</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount Paid (₱)</label>
                            <input id="em_amount_paid" type="text" class="form-input" value="${(bill.total_paid || bill.amount_paid || 0).toFixed(2)}" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Remaining Balance (₱)</label>
                            <input id="em_remaining_balance" type="text" class="form-input" value="${(() => {
                                const gross = +bill.final_amount || 0;
                                const paid = +bill.total_paid || +bill.amount_paid || 0;
                                const ph = +bill.philhealth_approved_amount || 0;
                                const hmo = +bill.hmo_approved_amount || 0;
                                return Math.max(gross - paid - ph - hmo, 0).toFixed(2);
                            })()}" disabled style="color:${(() => {
                                const gross = +bill.final_amount || 0;
                                const paid = +bill.total_paid || +bill.amount_paid || 0;
                                const ph = +bill.philhealth_approved_amount || 0;
                                const hmo = +bill.hmo_approved_amount || 0;
                                const remaining = Math.max(gross - paid - ph - hmo, 0);
                                return remaining > 0 ? '#dc2626' : '#059669';
                            })()}; font-weight:700;">
                        </div>
                    </div>
                </div>

                <!-- Payment Entry Section -->
                <div class="modal-section">
                    <div class="section-header">
                        <div class="section-number" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">💰</div>
                        <h3 class="section-title">Process Payment</h3>
                        </div>
                    <div class="payment-entry-card">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Payment Amount (₱)</label>
                                <input id="payment_amount" type="number" step="0.01" min="0.01" placeholder="0.00" class="form-input" style="font-weight:600; font-size:16px;">
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
                                <input id="payment_date" type="datetime-local" class="form-input" value="${new Date().toISOString().slice(0, 16)}">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:16px;">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea id="payment_notes" rows="2" placeholder="Payment notes..." class="form-input" style="resize:vertical;"></textarea>
                        </div>
                        <button type="button" id="process_payment_btn" class="btn-primary" style="margin-top:20px;">
                            <i class="fas fa-money-bill-wave"></i> Process Payment
                        </button>
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

                <!-- PhilHealth Section -->
                <div class="modal-section">
                    <div class="toggle-section" onclick="toggleSection('ph_section')">
                        <div class="toggle-section-header">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="section-number" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);">2</div>
                                <h3 class="section-title" style="margin:0;">PhilHealth Coverage</h3>
                            </div>
                            <button type="button" class="toggle-btn" id="ph_toggle_btn">Show</button>
                        </div>
                    </div>
                    <div id="ph_section" style="display:none;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding:16px; background:#ecfdf5; border-radius:10px; border:2px solid #10b981;">
                            <input id="em_ph_member" type="checkbox" style="width:20px; height:20px; cursor:pointer; accent-color:#10b981;" ${String(bill.philhealth_member||'0')==='1' ? 'checked' : ''}>
                            <div>
                                <label for="em_ph_member" style="margin:0; font-weight:700; color:#065f46; font-size:15px; cursor:pointer;">PhilHealth Member</label>
                                <p style="margin:4px 0 0; color:#047857; font-size:12px;">Check if patient is eligible for PhilHealth benefits</p>
                            </div>
                        </div>
                        <div id="ph_details" style="background:#f0fdf4; border:2px solid #10b981; border-radius:12px; padding:20px; display:none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Admission Date</label>
                                    <input id="em_admission_date" type="date" class="form-input" value="${bill.admission_date || bill.bill_date || ''}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Approved Deduction (₱)</label>
                                    <input id="em_ph_approved" type="number" step="0.01" placeholder="Select case rate first" class="form-input" value="${bill.philhealth_approved_amount || ''}" disabled>
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">Select Case Rate(s)</label>
                                    <select id="em_ph_rate_select" multiple size="4" class="form-input" style="min-height:140px;">
                                    </select>
                                    <input type="hidden" id="em_ph_rate_id">
                                    <input type="hidden" id="em_ph_rate_amount">
                                    <small style="color:#047857; font-size:11px; margin-top:8px; display:block; font-style:italic;">Hold Ctrl/Cmd to select multiple case rates</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Primary RVS Code</label>
                                    <input id="em_primary_rvs" type="text" placeholder="e.g., 48010" class="form-input" value="${bill.primary_rvs_code || ''}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Remaining Balance (₱)</label>
                                    <input id="em_remaining" type="text" class="form-input" value="${(() => {
                                        const gross = +bill.final_amount || 0;
                                        const paid = +bill.total_paid || +bill.amount_paid || 0;
                                        const ph = +bill.philhealth_approved_amount || 0;
                                        return Math.max(gross - paid - ph, 0).toFixed(2);
                                    })()}" disabled>
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">Reason / Note</label>
                                    <textarea id="em_ph_note" rows="3" placeholder="Required if codes missing or approved amount is less than suggested" class="form-input" style="resize:vertical;">${bill.philhealth_note || ''}</textarea>
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
                                <div class="section-number" style="background:linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">3</div>
                                <h3 class="section-title" style="margin:0;">HMO Coverage</h3>
                        </div>
                            <button type="button" class="toggle-btn" id="hmo_toggle_btn">Show</button>
                    </div>
                </div>
                    <div id="hmo_section" style="display:none;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding:16px; background:#eff6ff; border-radius:10px; border:2px solid #3b82f6;">
                            <input id="em_hmo_enabled" type="checkbox" style="width:20px; height:20px; cursor:pointer; accent-color:#3b82f6;">
                            <div>
                                <label for="em_hmo_enabled" style="margin:0; font-weight:700; color:#1e40af; font-size:15px; cursor:pointer;">Use HMO Coverage</label>
                                <p style="margin:4px 0 0; color:#1d4ed8; font-size:12px;">Check if patient has an approved HMO Letter of Authorization (LOA)</p>
                            </div>
                        </div>
                        <div id="hmo_details" style="background:#eff6ff; border:2px solid #3b82f6; border-radius:12px; padding:20px; display:none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">HMO Provider</label>
                                    <select id="em_hmo_provider" class="form-input" disabled>
                                        <option value="">Select HMO Provider</option>
                                    </select>
                                    <input type="hidden" id="em_hmo_provider_hidden" value="${bill.hmo_provider_id || ''}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Member Number</label>
                                    <input id="em_hmo_member_no" type="text" class="form-input" placeholder="e.g., MAXI-123456" value="${bill.hmo_member_no || ''}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">LOA Number</label>
                                    <input id="em_hmo_loa_number" type="text" class="form-input" placeholder="LOA Reference" value="${bill.hmo_loa_number || ''}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Coverage Valid From</label>
                                    <input id="em_hmo_valid_from" type="date" class="form-input" value="${bill.hmo_valid_from || ''}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Coverage Valid To</label>
                                    <input id="em_hmo_valid_to" type="date" class="form-input" value="${bill.hmo_valid_to || ''}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Approved Amount (₱)</label>
                                    <input id="em_hmo_approved_amount" type="number" step="0.01" class="form-input" value="${bill.hmo_approved_amount || ''}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Patient Share (₱)</label>
                                    <input id="em_hmo_patient_share" type="number" step="0.01" class="form-input" value="${(() => {
                                        const gross = +bill.final_amount || 0;
                                        const ph = +bill.philhealth_approved_amount || 0;
                                        const hmo = +bill.hmo_approved_amount || 0;
                                        const afterPh = Math.max(gross - ph, 0);
                                        const remaining = Math.max(afterPh - hmo, 0);
                                        return remaining.toFixed(2);
                                    })()}" readonly>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top:16px;">
                                <label class="form-label">HMO Notes</label>
                                <textarea id="em_hmo_notes" rows="3" class="form-input" style="resize:vertical;" placeholder="Additional details for HMO claim">${bill.hmo_notes || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="modal-section">
                    <div class="section-header">
                        <div class="section-number" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">4</div>
                        <h3 class="section-title">Payment Summary</h3>
                        </div>
                    <div class="payment-summary-box">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:24px; flex-wrap:wrap;">
                            <div>
                                <div class="summary-label">Remaining Balance to Collect</div>
                                        <p style="margin:6px 0 0; color:#78350f; font-size:13px; opacity:0.9;">After PhilHealth & HMO deductions</p>
                            </div>
                            <div style="text-align:right; background:white; padding:20px 28px; border-radius:12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width:200px;">
                                <span class="summary-amount">₱<span id="em_total_billing_display">${(() => {
                                    const gross = +bill.final_amount || 0;
                                    const ph = +bill.philhealth_approved_amount || 0;
                                    const hmo = +bill.hmo_approved_amount || 0;
                                    const afterPh = Math.max(gross - ph, 0);
                                    const remaining = Math.max(afterPh - hmo, 0);
                                    return remaining.toFixed(2);
                                })()}</span></span>
                                <div style="color:#78350f; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">Patient Share</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

        window.currentRateInputs = {
            rvsEl: null,
            adEl: null,
            rateSel: null
        };
        
        Swal.fire({
            title: '<div style="font-size:24px; font-weight:700; color:#1f2937; margin-bottom:8px;">Hospital Billing Payment</div>',
            html: content,
            width: 1000,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            focusConfirm: false,
            customClass: {
                popup: 'billing-payment-modal',
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            },
            preConfirm: () => {
                try {
                    console.log('[EditBill] preConfirm started');
                    const fd = new FormData();
                    fd.append('final_amount', document.getElementById('em_final_amount').value);
                    fd.append('payment_status', document.getElementById('em_payment_status').value);
                    fd.append('bill_date', document.getElementById('em_bill_date').value);
                    const paymentMethod = document.getElementById('em_payment_method').value;
                    fd.append('payment_method', paymentMethod);
                    const hmoEnabled = document.getElementById('em_hmo_enabled')?.checked ? '1' : '0';
                    fd.append('use_hmo', hmoEnabled);
                    const hmoProviderValue = document.getElementById('em_hmo_provider_hidden')?.value || document.getElementById('em_hmo_provider').value;
                    fd.append('hmo_provider_id', hmoProviderValue);
                    fd.append('hmo_member_no', document.getElementById('em_hmo_member_no').value);
                    fd.append('hmo_loa_number', document.getElementById('em_hmo_loa_number').value);
                    fd.append('hmo_valid_from', document.getElementById('em_hmo_valid_from').value);
                    fd.append('hmo_valid_to', document.getElementById('em_hmo_valid_to').value);
                    fd.append('hmo_approved_amount', document.getElementById('em_hmo_approved_amount').value);
                    fd.append('hmo_patient_share', document.getElementById('em_hmo_patient_share').value);
                    fd.append('hmo_notes', document.getElementById('em_hmo_notes').value);

                    const phMember = document.getElementById('em_ph_member').checked ? '1' : '0';
                    fd.append('philhealth_member', phMember);
                    fd.append('admission_date', document.getElementById('em_admission_date').value);
                    fd.append('primary_rvs_code', document.getElementById('em_primary_rvs').value);
                    const approved = document.getElementById('em_ph_approved').value;

                    if (phMember === '1') {
                        const selId = document.getElementById('em_ph_rate_id').value || '';
                        const selAmtStr = document.getElementById('em_ph_rate_amount').value || '';
                        const selAmt = parseFloat(selAmtStr || '0');

                        if (approved) {
                            fd.append('philhealth_approved_amount', approved);
                        }
                        fd.append('philhealth_note', document.getElementById('em_ph_note').value);
                        if (selId) fd.append('philhealth_selected_rate_id', selId);
                        if (!isNaN(selAmt)) fd.append('philhealth_selected_amount', String(selAmt));
                    }

                    if (hmoEnabled === '1') {
                        const requiredHmoFields = [
                            { id: 'em_hmo_provider_hidden', label: 'HMO Provider', fallback: 'em_hmo_provider' },
                            { id: 'em_hmo_member_no', label: 'HMO Member Number' },
                            { id: 'em_hmo_loa_number', label: 'HMO LOA Number' },
                            { id: 'em_hmo_approved_amount', label: 'HMO Approved Amount' }
                        ];
                        for (const field of requiredHmoFields) {
                            let el = document.getElementById(field.id);
                            if ((!el || !String(el.value || '').trim()) && field.fallback) {
                                el = document.getElementById(field.fallback);
                            }
                            if (!el || !String(el.value || '').trim()) {
                                Swal.showValidationMessage(field.label + ' is required when HMO coverage is enabled.');
                                return false;
                            }
                        }
                    }

                    console.log('[EditBill] Submitting update for bill', bill.id, 'with data:', Object.fromEntries(fd.entries()));

                    return fetch('<?= base_url('billing/update/') ?>' + bill.id, { method: 'POST', body: fd })
                        .then(async r => {
                            const payload = await r.json().catch(() => null);
                            console.log('[EditBill] Update response status', r.status, 'payload:', payload);
                            if (!r.ok) {
                                if (payload && payload.errors) {
                                    Swal.showValidationMessage(Object.values(payload.errors).join('<br>'));
                                } else {
                                    Swal.showValidationMessage('Update failed (HTTP ' + r.status + ').');
                                }
                                return false;
                            }
                            return payload;
                        })
                        .catch(err => {
                            console.error('[EditBill] Fetch error during update', err);
                            Swal.showValidationMessage('Update failed: ' + (err?.message || 'network or server error'));
                            return false;
                        });
                } catch (e) {
                    console.error('[EditBill] preConfirm JS error', e);
                    Swal.showValidationMessage('Update failed due to a script error: ' + (e?.message || 'Unknown error'));
                    return false;
                }
            }
        }).then(res => {
            cleanUp();
            window.currentRateInputs = null;
            if (res.isConfirmed) location.reload();
        });
        
        // Get references to input elements
        const rvsEl = document.getElementById('em_primary_rvs');
        const adEl = document.getElementById('em_admission_date');
        const rateSel = document.getElementById('em_ph_rate_select');
        const rateId = document.getElementById('em_ph_rate_id');
        const rateAmt = document.getElementById('em_ph_rate_amount');
        const phDetails = document.getElementById('ph_details');
        const phMemberEl = document.getElementById('em_ph_member');
        const approvedEl = document.getElementById('em_ph_approved');
        const paymentMethodEl = document.getElementById('em_payment_method');
        const hmoDetails = document.getElementById('hmo_details');
        const hmoEnabledEl = document.getElementById('em_hmo_enabled');
        const finalAmountEl = document.getElementById('em_final_amount');
        const remainingEl = document.getElementById('em_remaining');
        const hmoProviderEl = document.getElementById('em_hmo_provider');
        const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
        const hmoPatientShareEl = document.getElementById('em_hmo_patient_share');
        const totalBillingDisplayEl = document.getElementById('em_total_billing_display');
        const phNoteEl = document.getElementById('em_ph_note');

        const parseRateIds = raw => {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw;
            if (typeof raw === 'string') {
                try {
                    const parsed = JSON.parse(raw);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }
            return [];
        };
        const savedRateIds = (() => {
            const stored = parseRateIds(bill.philhealth_rate_ids);
            if (stored.length) return stored;
            return parseRateIds(bill.philhealth_rate_ids_calc);
        })();
        if (rateId) rateId.value = JSON.stringify(savedRateIds);
        if (phNoteEl && bill.philhealth_note) phNoteEl.value = bill.philhealth_note;

        const getHmoEnabled = () => !!hmoEnabledEl?.checked;

        const updateRemaining = () => {
            if (!remainingEl) return;
            const gross = parseFloat(finalAmountEl?.value || bill.final_amount || 0) || 0;
            const paid = parseFloat(document.getElementById('em_amount_paid')?.value || bill.total_paid || bill.amount_paid || 0) || 0;
            const phAppr = parseFloat(approvedEl?.value || 0) || 0;
            const hmoEnabled = getHmoEnabled();
            const hmoAppr = hmoEnabled ? (parseFloat(hmoApprovedEl?.value || 0) || 0) : 0;
            const remainingAfterPh = Math.max(gross - paid - phAppr, 0);
            remainingEl.value = remainingAfterPh.toFixed(2);
            
            const remainingBalanceEl = document.getElementById('em_remaining_balance');
            if (remainingBalanceEl) {
                const remaining = Math.max(gross - paid - phAppr - hmoAppr, 0);
                remainingBalanceEl.value = remaining.toFixed(2);
                remainingBalanceEl.style.color = remaining > 0 ? '#dc2626' : '#059669';
            }
            
            if (hmoPatientShareEl) {
                const base = parseFloat(remainingEl.value || '0') || 0;
                const patientShare = hmoEnabled ? Math.max(base - hmoAppr, 0) : base;
                hmoPatientShareEl.value = patientShare.toFixed(2);
            }
            if (totalBillingDisplayEl) {
                const base = parseFloat(remainingEl.value || '0') || 0;
                const patientShare = hmoEnabled ? Math.max(base - hmoAppr, 0) : base;
                totalBillingDisplayEl.textContent = patientShare.toFixed(2);
            }
        };

        const applyRateSelectionSummary = () => {
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
                    approvedEl.disabled = false;
                    approvedEl.value = totalAmount.toFixed(2);
                } else {
                    approvedEl.disabled = true;
                    approvedEl.value = '';
                }
            }
            if (selectedOptions.length === 1) {
                const opt = selectedOptions[0];
                const ctype = (opt.dataset.codeType || '').toUpperCase();
                const cval = opt.dataset.code || '';
                if (ctype === 'RVS') {
                    if (rvsEl) rvsEl.value = cval;
                } else if (ctype === 'ICD') {
                    if (rvsEl) rvsEl.value = '';
                }
            }
            updateRemaining();
        };

        const toggleHmo = () => {
            const enabled = getHmoEnabled();
            if (hmoDetails) hmoDetails.style.display = enabled ? '' : 'none';
            updateRemaining();
        };

        const populateHmoProviders = () => {
            if (!hmoProviderEl) return;
            const providers = Array.isArray(window.hmoProviders) ? window.hmoProviders : [];
            const hiddenField = document.getElementById('em_hmo_provider_hidden');
            const selected = String(bill.hmo_provider_id ?? (hiddenField?.value || '') ?? '');
            hmoProviderEl.innerHTML = '<option value="">Select HMO Provider</option>';
            providers.forEach(provider => {
                const opt = document.createElement('option');
                opt.value = String(provider.id ?? provider.provider_id ?? '');
                opt.textContent = provider.name ?? provider.provider_name ?? 'Unnamed Provider';
                if (opt.value && opt.value === selected) {
                    opt.selected = true;
                    if (hiddenField) {
                        hiddenField.value = opt.value;
                    }
                }
                hmoProviderEl.appendChild(opt);
            });
            if (selected && hiddenField) {
                hiddenField.value = selected;
            }
        };

        const loadRates = async () => {
            if (!rateSel) return;
            const rvs = (rvsEl?.value || '').trim();
            const ad = (adEl?.value || '').trim();
            console.log('Loading rates with:', { rvs, ad });
            rateSel.innerHTML = '<option value="" disabled>Loading…</option>';
            try {
                const qs = new URLSearchParams();
                if (rvs) qs.append('rvs', rvs);
                if (ad) qs.append('admission', ad);
                const url = '<?= base_url('billing/caseRates') ?>' + (qs.toString() ? ('?' + qs.toString()) : '');
                console.log('Fetching from URL:', url);
                const res = await fetch(url);
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response data:', data);
                
                const grouped = data?.grouped || {};
                const rates = Array.isArray(data?.rates) ? data.rates : [];
                
                rateSel.innerHTML = '';
                
                if (Object.keys(grouped).length > 0) {
                    Object.keys(grouped).forEach(category => {
                        const group = grouped[category];
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = group.label || category;
                        optgroup.dataset.category = category;
                        
                        (group.rates || []).forEach(r => {
                            const opt = document.createElement('option');
                            opt.value = r.id;
                            opt.textContent = r.label;
                            opt.dataset.amount = String(r.amount || '0');
                            opt.dataset.codeType = r.code_type || '';
                            opt.dataset.code = r.code || '';
                            opt.dataset.category = r.category || category;
                            optgroup.appendChild(opt);
                        });
                        
                        if (optgroup.children.length > 0) {
                            rateSel.appendChild(optgroup);
                        }
                    });
                } else {
                    console.log('Parsed rates:', rates);
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
                if (savedRateIds.length) {
                    const savedSet = new Set(savedRateIds.map(id => String(id)));
                    const allOptions = rateSel.querySelectorAll('option');
                    allOptions.forEach(opt => {
                        if (opt.value && savedSet.has(String(opt.value))) {
                            opt.selected = true;
                        }
                    });
                }
                
                const hasRates = Object.keys(grouped).length > 0 || rates.length > 0;
                if (!hasRates) {
                    console.warn('No rates found for the given criteria');
                    rateSel.innerHTML = '<option value="" disabled>No matching rates found</option>';
                }
            } catch(e) {
                console.error('Error loading rates:', e);
                rateSel.innerHTML = '<option value="">Error loading rates</option>';
            }
        };

        const togglePh = () => {
            const on = !!phMemberEl?.checked;
            if (phDetails) phDetails.style.display = on ? '' : 'none';
            if (!on && approvedEl) {
                approvedEl.value = '';
            }
            updateRemaining();
        };

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const toggleBtn = document.getElementById(sectionId.replace('_section', '_toggle_btn'));
            if (section && toggleBtn) {
                const isHidden = section.style.display === 'none';
                section.style.display = isHidden ? '' : 'none';
                toggleBtn.textContent = isHidden ? 'Hide' : 'Show';
            }
        }

        ['change', 'input'].forEach(event => {
            rvsEl?.addEventListener(event, loadRates);
            adEl?.addEventListener(event, loadRates);
        });
        rateSel?.addEventListener('change', applyRateSelectionSummary);
        ['input','change'].forEach(evt => {
            approvedEl?.addEventListener(evt, updateRemaining);
            hmoApprovedEl?.addEventListener(evt, updateRemaining);
            finalAmountEl?.addEventListener(evt, updateRemaining);
        });
        phMemberEl?.addEventListener('change', togglePh);
        paymentMethodEl?.addEventListener('change', toggleHmo);
        hmoEnabledEl?.addEventListener('change', toggleHmo);

        if (hmoEnabledEl) {
            hmoEnabledEl.checked = false;
        }
        
        populateHmoProviders();
        togglePh();
        toggleHmo();
        updateRemaining();
        loadRates()?.then(applyRateSelectionSummary);

        // Payment functionality
        const loadPaymentHistory = async () => {
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
                        html += `<a href="<?= base_url('billing/paymentReceipt') ?>/${p.id}" target="_blank" style="color:#3b82f6; text-decoration:none; font-size:12px; font-weight:600;">SOA</a>`;
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
        };
        
        const processPayment = async () => {
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
            
            const finalAmount = parseFloat(bill.final_amount || 0);
            const currentPaid = parseFloat(document.getElementById('em_amount_paid')?.value || bill.total_paid || bill.amount_paid || 0);
            const remaining = finalAmount - currentPaid;
            
            if (amount > remaining) {
                Swal.fire('Error', `Payment amount exceeds remaining balance. Remaining: ₱${remaining.toFixed(2)}`, 'error');
                return;
            }
            
            if (btnEl) btnEl.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('billing_id', bill.id);
                formData.append('amount', amount);
                formData.append('payment_method', methodEl?.value || 'cash');
                formData.append('payment_date', dateEl?.value ? new Date(dateEl.value).toISOString() : new Date().toISOString());
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Processed',
                        text: `Payment of ₱${amount.toFixed(2)} has been recorded successfully.`,
                        showConfirmButton: true,
                        confirmButtonText: 'View Receipt',
                        showCancelButton: true,
                        cancelButtonText: 'Close'
                    }).then((result) => {
                        if (result.isConfirmed && data.data.payment_id) {
                            window.open(`<?= base_url('billing/paymentReceipt') ?>/${data.data.payment_id}`, '_blank');
                        }
                        loadPaymentHistory();
                        setTimeout(() => location.reload(), 1000);
                    });
                    
                    if (amountEl) amountEl.value = '';
                    if (notesEl) notesEl.value = '';
                    if (dateEl) dateEl.value = new Date().toISOString().slice(0, 16);
                } else {
                    Swal.fire('Error', data.message || 'Failed to process payment', 'error');
                }
            } catch(e) {
                console.error('Error processing payment:', e);
                Swal.fire('Error', 'Failed to process payment. Please try again.', 'error');
            } finally {
                if (btnEl) btnEl.disabled = false;
            }
        };
        
        const paymentBtn = document.getElementById('process_payment_btn');
        if (paymentBtn) {
            paymentBtn.addEventListener('click', processPayment);
        }
        
        if (bill.id) {
            loadPaymentHistory();
        }
    }

    // Real-time search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearch');
        const tableBody = document.getElementById('billingTableBody');
        
        // Clear search button functionality
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = '';
                    clearBtn.classList.remove('show');
                    filterBillingTable();
                }
            });
        }
        
        if (searchInput && tableBody) {
            function filterBillingTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const rows = tableBody.querySelectorAll('tr[data-search]');
                let hasVisibleRows = false;
                
                rows.forEach(function(row) {
                    const searchableText = row.getAttribute('data-search') || '';
                    if (searchableText.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                let noResultsRow = tableBody.querySelector('.no-results-row');
                if (!hasVisibleRows && searchTerm !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results-row';
                        noResultsRow.innerHTML = '<td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;"><i class="fas fa-inbox" style="font-size:48px; margin-bottom:16px; display:block; opacity:0.5;"></i>No bills found</td>';
                        tableBody.appendChild(noResultsRow);
                    }
                    noResultsRow.style.display = '';
                } else if (noResultsRow) {
                    noResultsRow.style.display = 'none';
                }
            }
            
            searchInput.addEventListener('input', function() {
                // Show/hide clear button
                if (clearBtn) {
                    if (this.value.trim().length > 0) {
                        clearBtn.classList.add('show');
                    } else {
                        clearBtn.classList.remove('show');
                    }
                }
                filterBillingTable();
            });
            
            // Handle Escape key to clear search
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    if (clearBtn) clearBtn.classList.remove('show');
                    filterBillingTable();
                }
            });
            
            if (searchInput.value) {
                filterBillingTable();
                if (clearBtn && searchInput.value.trim().length > 0) {
                    clearBtn.classList.add('show');
                }
            }
        }
    });
</script>
<style>
    .billing-payment-modal {
        border-radius: 16px !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
    }
    .swal-confirm-btn {
        border-radius: 8px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.2s !important;
    }
    .swal-confirm-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(59,130,246,0.4) !important;
    }
    .swal-cancel-btn {
        border-radius: 8px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
    }
    
    .btn-history {
        background: #10b981;
        color: white;
    }
    
    .btn-history:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16,185,129,0.3);
    }
    
    /* Payment History Modal */
    .payment-history-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    
    .payment-history-modal.active {
        display: flex;
    }
    
    .payment-history-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }
    
    .payment-history-header {
        padding: 24px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .payment-history-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }
    
    .payment-history-close {
        background: #f3f4f6;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 18px;
        transition: all 0.2s;
    }
    
    .payment-history-close:hover {
        background: #e5e7eb;
        color: #111827;
    }
    
    .payment-history-body {
        padding: 24px;
    }
    
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-history-table th {
        background: #f9fafb;
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .payment-history-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #374151;
    }
    
    .payment-history-table tr:hover {
        background: #f9fafb;
    }
    
    .payment-history-summary {
        background: #f0f9ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .payment-history-summary h4 {
        margin: 0 0 12px;
        font-size: 14px;
        font-weight: 600;
        color: #1e40af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .payment-history-summary .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .payment-history-summary .summary-row:last-child {
        margin-bottom: 0;
        padding-top: 8px;
        border-top: 1px solid #bfdbfe;
        font-weight: 700;
        font-size: 16px;
        color: #1e40af;
    }
</style>

<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="payment-history-modal">
    <div class="payment-history-content">
        <div class="payment-history-header">
            <h3><i class="fas fa-history"></i> Payment History</h3>
            <button type="button" class="payment-history-close" onclick="closePaymentHistory()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="payment-history-body">
            <div id="paymentHistoryLoading" style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-spin" style="font-size:24px; color:#6b7280;"></i>
                <p style="margin-top:16px; color:#6b7280;">Loading payment history...</p>
            </div>
            <div id="paymentHistoryContent" style="display:none;"></div>
        </div>
    </div>
</div>

<script>
    let currentBillingId = null;
    
    async function showPaymentHistory(billingId) {
        currentBillingId = billingId;
        const modal = document.getElementById('paymentHistoryModal');
        const loading = document.getElementById('paymentHistoryLoading');
        const content = document.getElementById('paymentHistoryContent');
        
        modal.classList.add('active');
        loading.style.display = 'block';
        content.style.display = 'none';
        
        try {
            const res = await fetch(`<?= base_url('billing/getPayments') ?>/${billingId}`);
            const data = await res.json();
            
            if (data.success && data.data.payments) {
                const payments = data.data.payments || [];
                const totalPaid = data.data.total_paid || 0;
                
                if (payments.length === 0) {
                    content.innerHTML = '<div style="text-align:center; padding:40px; color:#9ca3af;"><i class="fas fa-inbox" style="font-size:48px; margin-bottom:16px; display:block; opacity:0.5;"></i><p>No payments recorded yet</p></div>';
                } else {
                    let html = '<div class="payment-history-summary">';
                    html += '<h4>Payment Summary</h4>';
                    html += `<div class="summary-row"><span>Total Payments:</span><span><strong>${payments.length}</strong></span></div>`;
                    html += `<div class="summary-row"><span>Total Amount Paid:</span><span><strong style="color:#059669;">₱${parseFloat(totalPaid).toFixed(2)}</strong></span></div>`;
                    html += '</div>';
                    
                    html += '<table class="payment-history-table">';
                    html += '<thead><tr>';
                    html += '<th>Date & Time</th>';
                    html += '<th style="text-align:right;">Amount</th>';
                    html += '<th style="text-align:center;">Method</th>';
                    html += '<th style="text-align:center;">Action</th>';
                    html += '</tr></thead><tbody>';
                    
                    payments.forEach(p => {
                        const date = new Date(p.payment_date || p.created_at);
                        const dateStr = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric',
                            hour: '2-digit', 
                            minute: '2-digit' 
                        });
                        const method = (p.payment_method || 'cash').toUpperCase();
                        const amount = parseFloat(p.amount || 0).toFixed(2);
                        
                        html += '<tr>';
                        html += `<td>${dateStr}</td>`;
                        html += `<td style="text-align:right; font-weight:600; color:#059669;">₱${amount}</td>`;
                        html += `<td style="text-align:center;">${method}</td>`;
                        html += `<td style="text-align:center;">`;
                        html += `<a href="<?= base_url('billing/paymentReceipt') ?>/${p.id}" target="_blank" style="color:#3b82f6; text-decoration:none; font-size:12px; font-weight:600;"><i class="fas fa-file-invoice"></i> SOA</a>`;
                        html += `</td>`;
                        html += `</tr>`;
                    });
                    
                    html += '</tbody></table>';
                    content.innerHTML = html;
                }
            } else {
                content.innerHTML = '<div style="text-align:center; padding:40px; color:#dc2626;"><i class="fas fa-exclamation-triangle" style="font-size:48px; margin-bottom:16px; display:block;"></i><p>Error loading payment history</p></div>';
            }
        } catch(e) {
            console.error('Error loading payment history:', e);
            content.innerHTML = '<div style="text-align:center; padding:40px; color:#dc2626;"><i class="fas fa-exclamation-triangle" style="font-size:48px; margin-bottom:16px; display:block;"></i><p>Failed to load payment history</p></div>';
        } finally {
            loading.style.display = 'none';
            content.style.display = 'block';
        }
    }
    
    function closePaymentHistory() {
        const modal = document.getElementById('paymentHistoryModal');
        modal.classList.remove('active');
    }
    
    // Close modal when clicking outside
    document.getElementById('paymentHistoryModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePaymentHistory();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePaymentHistory();
        }
    });
</script>
<?= $this->endSection() ?>
