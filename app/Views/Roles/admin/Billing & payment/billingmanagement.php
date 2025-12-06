<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Billing Management</h1>
        </div>
        <div class="card-body">
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
            <h3>Outstanding</h3>
            <div class="value">₱<?= number_format($totals['outstanding'] ?? 0, 2) ?></div>
        </div>
    </div>

    <div class="unified-search-wrapper">
        <div class="unified-search-row" style="margin:0;">
            <i class="fas fa-search unified-search-icon"></i>
            <input type="text" id="searchInput" class="unified-search-field" placeholder="Search by Invoice # or Patient..." value="<?= esc($query ?? '') ?>">
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="data-table">
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
                    <tbody id="billingTableBody">
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <?php 
                                $billNumber = 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT);
                                $patientName = esc($bill['patient_name'] ?? 'N/A');
                                $billDate = esc($bill['bill_date'] ?? '');
                                $serviceName = esc($bill['service_name'] ?? '—');
                                $searchableText = strtolower($billNumber . ' ' . $patientName . ' ' . $billDate . ' ' . $serviceName);
                            ?>
                            <tr data-id="<?= (int)$bill['id'] ?>" data-search="<?= htmlspecialchars($searchableText) ?>">
                                <td>#<?= $billNumber ?></td>
                                <td><?= $patientName ?></td>
                                <td><?= $billDate ?></td>
                                <td><?= $serviceName ?></td>
                                <td>₱<?= number_format((float)($bill['final_amount'] ?? 0), 2) ?></td>
                                <td>
                                    <?php $ps = strtolower($bill['payment_status'] ?? 'pending'); ?>
                                    <span class="<?= $ps === 'paid' ? 'status-paid' : 'status-pending' ?>">
                                        <?= ucfirst($ps) ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="<?= base_url('billing/show/' . (int)$bill['id']) ?>" class="btn btn-receipt" target="_blank">View Receipt</a>
                                    <?php if ($ps !== 'paid'): ?>
                                        <button type="button" class="btn btn-edit" data-action="edit">Edit</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="no-results-row"><td colspan="7" style="text-align:center">No bills found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.hmoProviders = <?= json_encode($hmoProviders ?? []) ?>;
</script>

<script>
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('button[data-action="edit"]');
        if (editBtn) {
            const tr = editBtn.closest('tr');
            const id = tr?.dataset?.id;
            if (!id) return;
            fetch('<?= base_url('billing/edit/') ?>' + id)
                .then(r => r.json())
                .then(data => openEditModal(data))
                .catch(() => Swal.fire('Error', 'Failed to load bill', 'error'));
        }
    });

    function openEditModal(bill) {
        // Clean up any existing event listeners first
        const cleanUp = () => {
            if (!window.currentRateInputs) return;
            const safeReplace = el => {
                if (!el || !el.parentNode) return null;
                const clone = el.cloneNode(true);
                el.parentNode.replaceChild(clone, el);
                return clone;
            };
            window.currentRateInputs.rvsEl = safeReplace(window.currentRateInputs.rvsEl);
            window.currentRateInputs.icdEl = safeReplace(window.currentRateInputs.icdEl);
            window.currentRateInputs.adEl = safeReplace(window.currentRateInputs.adEl);
            window.currentRateInputs.rateSel = safeReplace(window.currentRateInputs.rateSel);
        };
        
        // Call cleanup before setting up new listeners
        cleanUp();
        
        const content = `
            <div style="text-align:left;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h3 style=\"margin:0; color:#111827; font-weight:700;\">Step 1 — Bill Details</h3>
                    <button type="button" class="step-toggle" data-target="step1_body" style="border:none; background:#e5e7eb; color:#374151; padding:6px 10px; border-radius:6px; font-size:12px; cursor:pointer;">Hide</button>
                </div>
                <div id="step1_body" class="step-body" style="margin-top:12px; display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Invoice #</label>
                        <input type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${('INV-' + String(bill.id).padStart(6, '0'))}" disabled>
                    </div>
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Bill Date</label>
                        <input id="em_bill_date" type="date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.bill_date || ''}">
                    </div>
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Patient</label>
                        <input type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${bill.patient_name || 'N/A'}" disabled>
                    </div>
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Amount</label>
                        <input id="em_final_amount" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.final_amount || 0}">
                    </div>
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Payment Method</label>
                        <select id="em_payment_method" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
                            ${['cash','credit','debit'].map(m => `<option value="${m}" ${String(bill.payment_method||'cash').toLowerCase()===m?'selected':''}>${m === 'cash' ? 'CASH' : (m === 'credit' ? 'CREDIT CARD' : 'DEBIT CARD')}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label style="margin:0; color:#374151; font-weight:600;">Status</label>
                        <select id="em_payment_status" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
                            ${['pending','partial','paid','overdue'].map(s => `<option value="${s}" ${String(bill.payment_status||'').toLowerCase()===s?'selected':''}>${s.charAt(0).toUpperCase()+s.slice(1)}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <div style="height:1px; background:#e5e7eb; margin:16px 0;"></div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h3 style="margin:0; color:#065f46; font-weight:700;">Step 2 — PhilHealth (if member)</h3>
                    <button type="button" class="step-toggle" data-target="step2_body" style="border:none; background:#d1fae5; color:#065f46; padding:6px 10px; border-radius:6px; font-size:12px; cursor:pointer;">Hide</button>
                </div>
                <div id="step2_body" class="step-body" style="margin-top:12px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <label for="em_ph_member" style="margin:0; font-weight:600; min-width:160px;">PhilHealth Member</label>
                    <input id="em_ph_member" type="checkbox" ${String(bill.philhealth_member||'0')==='1' ? 'checked' : ''}>
                    <span style="font-size:12px; color:#6b7280;">Check if eligible for PhilHealth</span>
                </div>
                <div id="ph_section" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:12px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Admission Date</label>
                            <input id="em_admission_date" type="date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.admission_date || bill.bill_date || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Approved Deduction (Final)</label>
                            <input id="em_ph_approved" type="number" step="0.01" placeholder="Select a case rate first" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.philhealth_approved_amount || ''}" disabled>
                        </div>
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Select Case Rate(s)</label>
                            <select id="em_ph_rate_select" multiple size="4" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; min-height:140px;">
                            </select>
                            <input type="hidden" id="em_ph_rate_id">
                            <input type="hidden" id="em_ph_rate_amount">
                            <div id="em_ph_rate_hint" style="font-size:12px; color:#6b7280; margin-top:6px;">Hold Ctrl/Cmd to pick multiple case rates (filtered by RVS/ICD + admission date)</div>
                        </div>
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Primary RVS Code</label>
                            <input id="em_primary_rvs" type="text" placeholder="e.g., 48010" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.primary_rvs_code || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Primary ICD-10 Code</label>
                            <input id="em_primary_icd" type="text" placeholder="e.g., J18.9" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.primary_icd10_code || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#374151; font-weight:600;">Remaining Balance</label>
                            <input id="em_remaining" type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${(() => {
                                const gross = +bill.final_amount || 0;
                                const ph = +bill.philhealth_approved_amount || 0;
                                return Math.max(gross - ph, 0).toFixed(2);
                            })()}" disabled>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="margin:0; color:#374151; font-weight:600;">Reason / Note</label>
                            <textarea id="em_ph_note" rows="2" placeholder="Required if codes missing or approved < suggested" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">${bill.philhealth_note || ''}</textarea>
                        </div>
                    </div>
                </div>
                </div>

                <div style="height:1px; background:#e5e7eb; margin:16px 0;"></div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h3 style="margin:0 0 10px; color:#1d4ed8; font-weight:700;">Step 3 — HMO (if applicable)</h3>
                    <button type="button" class="step-toggle" data-target="step3_body" style="border:none; background:#dbeafe; color:#1d4ed8; padding:6px 10px; border-radius:6px; font-size:12px; cursor:pointer;">Hide</button>
                </div>
                <div id="step3_body" class="step-body" style="margin-top:12px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <label for="em_hmo_enabled" style="margin:0; font-weight:600; min-width:180px; color:#1d4ed8;">Use HMO Coverage</label>
                    <input id="em_hmo_enabled" type="checkbox">
                    <span style="font-size:12px; color:#6b7280;">Check if patient has an approved HMO LOA</span>
                </div>
                <div id="hmo_section" style="background:#eff6ff; border:1px solid #dbeafe; border-radius:6px; padding:12px;">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">HMO Provider</label>
                            <select id="em_hmo_provider" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                                <option value="">Select HMO Provider</option>
                            </select>
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Member Number</label>
                            <input id="em_hmo_member_no" type="text" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="e.g., MAXI-123456" value="${bill.hmo_member_no || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">LOA Number</label>
                            <input id="em_hmo_loa_number" type="text" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="LOA Reference" value="${bill.hmo_loa_number || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Valid From</label>
                            <input id="em_hmo_valid_from" type="date" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" value="${bill.hmo_valid_from || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Valid To</label>
                            <input id="em_hmo_valid_to" type="date" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" value="${bill.hmo_valid_to || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Limit (₱)</label>
                            <input id="em_hmo_coverage_limit" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" value="${bill.hmo_coverage_limit || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Approved Amount (₱)</label>
                            <input id="em_hmo_approved_amount" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" value="${bill.hmo_approved_amount || ''}">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Patient Share (₱)</label>
                            <input id="em_hmo_patient_share" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${(() => {
                                const gross = +bill.final_amount || 0;
                                const ph = +bill.philhealth_approved_amount || 0;
                                const hmo = +bill.hmo_approved_amount || 0;
                                const afterPh = Math.max(gross - ph, 0);
                                const remaining = Math.max(afterPh - hmo, 0);
                                return remaining.toFixed(2);
                            })()}" readonly>
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">HMO Status</label>
                            <select id="em_hmo_status" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                                ${['pending','for-approval','approved','submitted','denied','paid'].map(s => `<option value="${s}" ${String(bill.hmo_status||'').toLowerCase()===s?'selected':''}>${s.replace('-', ' ').replace(/\b\w/g, c => c.toUpperCase())}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <label style="margin:0; color:#1e3a8a; font-weight:600;">HMO Notes</label>
                        <textarea id="em_hmo_notes" rows="2" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="Additional details for HMO claim">${bill.hmo_notes || ''}</textarea>
                    </div>
                </div>
                </div>

                <div style="height:1px; background:#e5e7eb; margin:24px 0 12px;"></div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h3 style="margin:0 0 10px; color:#92400e; font-weight:700;">Step 4 — Total Billing Summary</h3>
                    <button type="button" class="step-toggle" data-target="step4_body" style="border:none; background:#fde68a; color:#92400e; padding:6px 10px; border-radius:6px; font-size:12px; cursor:pointer;">Hide</button>
                </div>
                <div id="step4_body" class="step-body" style="margin-top:12px;">
                <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:18px; display:flex; justify-content:space-between; align-items:center; gap:16px;">
                    <div>
                        <p style="margin:0; color:#92400e; font-weight:600;">Remaining Balance to Collect</p>
                        <p style="margin:4px 0 0; color:#7c2d12; font-size:14px;">(After PhilHealth & HMO deductions)</p>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:block; font-size:30px; font-weight:800; color:#92400e;">₱<span id="em_total_billing_display">${(() => {
                            const gross = +bill.final_amount || 0;
                            const ph = +bill.philhealth_approved_amount || 0;
                            const hmo = +bill.hmo_approved_amount || 0;
                            const afterPh = Math.max(gross - ph, 0);
                            const remaining = Math.max(afterPh - hmo, 0);
                            return remaining.toFixed(2);
                        })()}</span></span>
                        <small style="color:#92400e;">Patient share to collect</small>
                    </div>
                </div>
                </div>
            </div>`;

        // Store references to input elements for cleanup
        window.currentRateInputs = {
            rvsEl: null,
            icdEl: null,
            adEl: null,
            rateSel: null
        };
        
        Swal.fire({
            title: 'Edit Bill',
            html: content,
            width: 860,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            focusConfirm: false,
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
                    fd.append('hmo_provider_id', document.getElementById('em_hmo_provider').value);
                    fd.append('hmo_member_no', document.getElementById('em_hmo_member_no').value);
                    fd.append('hmo_loa_number', document.getElementById('em_hmo_loa_number').value);
                    fd.append('hmo_valid_from', document.getElementById('em_hmo_valid_from').value);
                    fd.append('hmo_valid_to', document.getElementById('em_hmo_valid_to').value);
                    fd.append('hmo_coverage_limit', document.getElementById('em_hmo_coverage_limit').value);
                    fd.append('hmo_approved_amount', document.getElementById('em_hmo_approved_amount').value);
                    fd.append('hmo_patient_share', document.getElementById('em_hmo_patient_share').value);
                    fd.append('hmo_status', document.getElementById('em_hmo_status').value);
                    fd.append('hmo_notes', document.getElementById('em_hmo_notes').value);

                    // PhilHealth fields
                    const phMember = document.getElementById('em_ph_member').checked ? '1' : '0';
                    fd.append('philhealth_member', phMember);
                    fd.append('admission_date', document.getElementById('em_admission_date').value);
                    fd.append('primary_rvs_code', document.getElementById('em_primary_rvs').value);
                    fd.append('primary_icd10_code', document.getElementById('em_primary_icd').value);
                    const approved = document.getElementById('em_ph_approved').value;

                    if (phMember === '1') {
                        // PhilHealth fields (let backend enforce rules to avoid blocking the request here)
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
                            { id: 'em_hmo_provider', label: 'HMO Provider' },
                            { id: 'em_hmo_member_no', label: 'HMO Member Number' },
                            { id: 'em_hmo_loa_number', label: 'HMO LOA Number' },
                            { id: 'em_hmo_approved_amount', label: 'HMO Approved Amount' }
                        ];
                        for (const field of requiredHmoFields) {
                            const el = document.getElementById(field.id);
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
                                // Surface backend validation nicely
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
            // Clean up when modal is closed
            cleanUp();
            window.currentRateInputs = null;
            
            if (res.isConfirmed) location.reload();
        });
        // Get references to input elements
        const rvsEl = document.getElementById('em_primary_rvs');
        const icdEl = document.getElementById('em_primary_icd');
        const adEl = document.getElementById('em_admission_date');
        const rateSel = document.getElementById('em_ph_rate_select');
        const rateId = document.getElementById('em_ph_rate_id');
        const rateAmt = document.getElementById('em_ph_rate_amount');
        const phSection = document.getElementById('ph_section');
        const phMemberEl = document.getElementById('em_ph_member');
        const approvedEl = document.getElementById('em_ph_approved');
        const paymentMethodEl = document.getElementById('em_payment_method');
        const hmoSection = document.getElementById('hmo_section');
        const hmoEnabledEl = document.getElementById('em_hmo_enabled');
        const finalAmountEl = document.getElementById('em_final_amount');
        const remainingEl = document.getElementById('em_remaining');
        const hmoProviderEl = document.getElementById('em_hmo_provider');
        const hmoApprovedEl = document.getElementById('em_hmo_approved_amount');
        const hmoPatientShareEl = document.getElementById('em_hmo_patient_share');
        const totalBillingDisplayEl = document.getElementById('em_total_billing_display');
        const totalBillingEl = document.getElementById('em_total_billing');
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
            const phAppr = parseFloat(approvedEl?.value || 0) || 0;
            const hmoEnabled = getHmoEnabled();
            const hmoAppr = hmoEnabled ? (parseFloat(hmoApprovedEl?.value || 0) || 0) : 0;
            const remainingAfterPh = Math.max(gross - phAppr, 0);
            remainingEl.value = remainingAfterPh.toFixed(2);
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
            if (totalBillingEl) {
                totalBillingEl.value = gross.toFixed(2);
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
                    if (icdEl) icdEl.value = '';
                } else if (ctype === 'ICD') {
                    if (icdEl) icdEl.value = cval;
                    if (rvsEl) rvsEl.value = '';
                }
            }
            updateRemaining();
        };

        const toggleHmo = () => {
            const enabled = getHmoEnabled();
            if (hmoSection) hmoSection.style.display = enabled ? '' : 'none';
            updateRemaining();
        };

        const populateHmoProviders = () => {
            if (!hmoProviderEl) return;
            const providers = Array.isArray(window.hmoProviders) ? window.hmoProviders : [];
            const selected = String(bill.hmo_provider_id ?? '');
            hmoProviderEl.innerHTML = '<option value="">Select HMO Provider</option>';
            providers.forEach(provider => {
                const opt = document.createElement('option');
                opt.value = String(provider.id ?? provider.provider_id ?? '');
                opt.textContent = provider.name ?? provider.provider_name ?? 'Unnamed Provider';
                if (opt.value && opt.value === selected) {
                    opt.selected = true;
                }
                hmoProviderEl.appendChild(opt);
            });
        };

        const loadRates = async () => {
            if (!rateSel) return;
            const rvs = (rvsEl?.value || '').trim();
            const icd = (icdEl?.value || '').trim();
            const ad = (adEl?.value || '').trim();
            console.log('Loading rates with:', { rvs, icd, ad });
            rateSel.innerHTML = '<option value="" disabled>Loading…</option>';
            try {
                const qs = new URLSearchParams();
                if (rvs) qs.append('rvs', rvs);
                if (icd) qs.append('icd', icd);
                if (ad) qs.append('admission', ad);
                const url = '<?= base_url('billing/caseRates') ?>' + (qs.toString() ? ('?' + qs.toString()) : '');
                console.log('Fetching from URL:', url);
                const res = await fetch(url);
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response data:', data);
                const rates = Array.isArray(data?.rates) ? data.rates : [];
                console.log('Parsed rates:', rates);
                rateSel.innerHTML = '';
                rates.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.id;
                    opt.textContent = r.label;
                    opt.dataset.amount = String(r.amount || '0');
                    opt.dataset.codeType = r.code_type || '';
                    opt.dataset.code = r.code || '';
                    rateSel.appendChild(opt);
                });
                if (savedRateIds.length) {
                    const savedSet = new Set(savedRateIds.map(id => String(id)));
                    Array.from(rateSel.options).forEach(opt => {
                        opt.selected = savedSet.has(String(opt.value));
                    });
                }
                if (rates.length === 0) {
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
            if (phSection) phSection.style.display = on ? '' : 'none';
            if (!on && approvedEl) {
                approvedEl.value = '';
            }
            updateRemaining();
        };

        ['change', 'input'].forEach(event => {
            rvsEl?.addEventListener(event, loadRates);
            icdEl?.addEventListener(event, loadRates);
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

        populateHmoProviders();
        togglePh();
        toggleHmo();
        updateRemaining();
        loadRates()?.then(applyRateSelectionSummary);

        // Step toggle buttons
        document.querySelectorAll('.step-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const body = targetId ? document.getElementById(targetId) : null;
                if (!body) return;
                const isHidden = body.style.display === 'none';
                body.style.display = isHidden ? '' : 'none';
                btn.textContent = isHidden ? 'Hide' : 'Show';
            });
        });
    }

    // Real-time search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('billingTableBody');
        
        if (searchInput && tableBody) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
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
                
                // Show/hide "No results" message
                let noResultsRow = tableBody.querySelector('.no-results-row');
                if (!hasVisibleRows && searchTerm !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results-row';
                        noResultsRow.innerHTML = '<td colspan="7" style="text-align:center">No bills found</td>';
                        tableBody.appendChild(noResultsRow);
                    }
                    noResultsRow.style.display = '';
                } else if (noResultsRow) {
                    noResultsRow.style.display = 'none';
                }
            });
            
            // Trigger search on page load if there's a value
            if (searchInput.value) {
                searchInput.dispatchEvent(new Event('input'));
            }
        }
    });
</script>
<?= $this->endSection() ?>
