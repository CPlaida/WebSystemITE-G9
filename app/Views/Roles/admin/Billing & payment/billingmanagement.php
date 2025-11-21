<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Billing Management</h1>
            <div class="search-container">
                <form method="get" action="<?= base_url('billing') ?>" style="display:flex; gap:10px; width:100%">
                    <input type="text" id="searchInput" name="q" value="<?= esc($query ?? '') ?>" class="search-input" placeholder="Search by Invoice # or Patient...">
                    <button id="searchButton" class="search-button" type="submit">Search</button>
                </form>
            </div>
        </div>

        <div class="summary">
            <div class="box">
                <h3>Total Revenue</h3>
                <p>₱<?= number_format($totals['totalRevenue'] ?? 0, 2) ?></p>
            </div>
            <div class="box">
                <h3>Pending Bills</h3>
                <p><?= (int)($totals['pendingCount'] ?? 0) ?></p>
            </div>
            <div class="box">
                <h3>Paid This Month</h3>
                <p>₱<?= number_format($totals['paidThisMonth'] ?? 0, 2) ?></p>
            </div>
            <div class="box">
                <h3>Outstanding</h3>
                <p>₱<?= number_format($totals['outstanding'] ?? 0, 2) ?></p>
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
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr data-id="<?= (int)$bill['id'] ?>">
                                <td>#<?= 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= esc($bill['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($bill['bill_date'] ?? '') ?></td>
                                <td><?= esc($bill['service_name'] ?? '—') ?></td>
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
                        <tr><td colspan="7" style="text-align:center">No bills found</td></tr>
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
                <h3 style=\"margin:0 0 10px; color:#111827; font-weight:700;\">Step 1 — Bill Details</h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
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
                            ${['cash','insurance','hmo'].map(m => `<option value="${m}" ${String(bill.payment_method||'cash').toLowerCase()===m?'selected':''}>${m.toUpperCase()}</option>`).join('')}
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
                <h3 style="margin:0 0 10px; color:#1d4ed8; font-weight:700;">Step 2 — HMO (if applicable)</h3>
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
                            <input id="em_hmo_member_no" type="text" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="e.g., MAXI-123456">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">LOA Number</label>
                            <input id="em_hmo_loa_number" type="text" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="LOA Reference">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Valid From</label>
                            <input id="em_hmo_valid_from" type="date" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Valid To</label>
                            <input id="em_hmo_valid_to" type="date" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Coverage Limit (₱)</label>
                            <input id="em_hmo_coverage_limit" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Approved Amount (₱)</label>
                            <input id="em_hmo_approved_amount" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">Patient Share (₱)</label>
                            <input id="em_hmo_patient_share" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                        </div>
                        <div>
                            <label style="margin:0; color:#1e3a8a; font-weight:600;">HMO Status</label>
                            <select id="em_hmo_status" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;">
                                ${['pending','for-approval','approved','submitted','denied','paid'].map(s => `<option value="${s}">${s.replace('-', ' ').replace(/\b\w/g, c => c.toUpperCase())}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <label style="margin:0; color:#1e3a8a; font-weight:600;">HMO Notes</label>
                        <textarea id="em_hmo_notes" rows="2" style="width:100%; padding:10px; border:1px solid #cbd5f5; border-radius:6px;" placeholder="Additional details for HMO claim"></textarea>
                    </div>
                </div>

                <div style="height:1px; background:#e5e7eb; margin:16px 0;"></div>
                <h3 style="margin:0 0 10px; color:#065f46; font-weight:700;">Step 3 — PhilHealth (if member)</h3>
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
                            <label style="margin:0; color:#374151; font-weight:600;">Select Case Rate</label>
                            <select id="em_ph_rate_select" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
                                <option value="">— Select Case Rate —</option>
                            </select>
                            <input type="hidden" id="em_ph_rate_id">
                            <input type="hidden" id="em_ph_rate_amount">
                            <div id="em_ph_rate_hint" style="font-size:12px; color:#6b7280; margin-top:6px;">Options are filtered by RVS/ICD and Admission Date</div>
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
                            <input id="em_remaining" type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${(() => { const gross=(+bill.final_amount||0); const ph=(+bill.philhealth_approved_amount||0); const net=Math.max(gross - ph,0); return net.toFixed(2); })()}" disabled>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="margin:0; color:#374151; font-weight:600;">Reason / Note</label>
                            <textarea id="em_ph_note" rows="2" placeholder="Required if codes missing or approved < suggested" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"></textarea>
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

                function applySelected(){
                    const opt = rateSel?.selectedOptions?.[0];
                    const amt = opt ? parseFloat(opt.dataset.amount || '0') : 0;
                    if (rateId && rateSel) rateId.value = rateSel.value || '';
                    if (rateAmt) rateAmt.value = isFinite(amt) ? amt.toFixed(2) : '';
                    // Autofill codes based on selected rate
                    const ctype = opt ? (opt.dataset.codeType || '').toUpperCase() : '';
                    const cval = opt ? (opt.dataset.code || '') : '';
                    if (ctype === 'RVS') {
                        if (document.getElementById('em_primary_rvs')) document.getElementById('em_primary_rvs').value = cval;
                        if (document.getElementById('em_primary_icd')) document.getElementById('em_primary_icd').value = '';
                    } else if (ctype === 'ICD') {
                        if (document.getElementById('em_primary_icd')) document.getElementById('em_primary_icd').value = cval;
                        if (document.getElementById('em_primary_rvs')) document.getElementById('em_primary_rvs').value = '';
                    }
                    // Prefill Approved with selected amount (editable)
                    if (approvedEl && isFinite(amt)) {
                        approvedEl.value = amt.toFixed(2);
                    }
                    // Enable Approved only when a rate is selected
                    if (approvedEl) approvedEl.disabled = !(rateSel && rateSel.value);
        }

        function updateRemaining(){
            if (!remainingEl) return;
            const gross = parseFloat(finalAmountEl?.value || bill.final_amount || 0) || 0;
            const appr = parseFloat(approvedEl?.value || 0) || 0;
            const net = Math.max(gross - appr, 0);
            remainingEl.value = net.toFixed(2);
        }

        function toggleHmo(){
            const method = (paymentMethodEl?.value || '').toLowerCase();
            if (hmoSection) hmoSection.style.display = method === 'hmo' ? '' : 'none';
        }
        paymentMethodEl?.addEventListener('change', toggleHmo);
        toggleHmo();

        async function loadRates(){
            if (!rateSel) return;
            const rvs = (rvsEl?.value || '').trim();
            const icd = (icdEl?.value || '').trim();
            const ad = (adEl?.value || '').trim();
            console.log('Loading rates with:', { rvs, icd, ad });
            rateSel.innerHTML = '<option value="">Loading…</option>';
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
                rateSel.innerHTML = '<option value="">— Select Case Rate —</option>';
                rates.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.id;
                    opt.textContent = r.label;
                    opt.dataset.amount = String(r.amount || '0');
                    opt.dataset.codeType = r.code_type || '';
                    opt.dataset.code = r.code || '';
                    rateSel.appendChild(opt);
                });
                if (rates.length === 0) {
                    console.warn('No rates found for the given criteria');
                    rateSel.innerHTML = '<option value="">No matching rates found</option>';
                }
            } catch(e) {
                console.error('Error loading rates:', e);
                rateSel.innerHTML = '<option value="">Error loading rates</option>';
            }
        }

        function applySelected(){
            const opt = rateSel?.selectedOptions?.[0];
            const amt = opt ? parseFloat(opt.dataset.amount || '0') : 0;
            if (rateId && rateSel) rateId.value = rateSel.value || '';
            if (rateAmt) rateAmt.value = isFinite(amt) ? amt.toFixed(2) : '';
            // Autofill codes based on selected rate
            const ctype = opt ? (opt.dataset.codeType || '').toUpperCase() : '';
            const cval = opt ? (opt.dataset.code || '') : '';
            if (ctype === 'RVS') {
                if (document.getElementById('em_primary_rvs')) document.getElementById('em_primary_rvs').value = cval;
                if (document.getElementById('em_primary_icd')) document.getElementById('em_primary_icd').value = '';
            } else if (ctype === 'ICD') {
                if (document.getElementById('em_primary_icd')) document.getElementById('em_primary_icd').value = cval;
                if (document.getElementById('em_primary_rvs')) document.getElementById('em_primary_rvs').value = '';
            }
            // Prefill Approved with selected amount (editable)
            if (approvedEl && isFinite(amt)) {
                approvedEl.value = amt.toFixed(2);
            }
            // Enable Approved only when a rate is selected
            if (approvedEl) approvedEl.disabled = !(rateSel && rateSel.value);

            // Recompute remaining balance whenever a rate is applied
            updateRemaining();
        }

        // Add both 'change' and 'input' event listeners for better responsiveness
        ['change', 'input'].forEach(event => {
            rvsEl?.addEventListener(event, loadRates);
            icdEl?.addEventListener(event, loadRates);
            adEl?.addEventListener(event, loadRates);
        });
        rateSel?.addEventListener('change', applySelected);

        // When user edits Approved Deduction or Final Amount, recompute remaining balance
        approvedEl?.addEventListener('input', updateRemaining);
        finalAmountEl?.addEventListener('input', updateRemaining);
        // Toggle PhilHealth section visibility
        function togglePh(){
            const on = !!phMemberEl?.checked;
            if (phSection) phSection.style.display = on ? '' : 'none';
            if (!on) {
                // If PhilHealth is turned off, clear deduction and reset remaining to full amount
                if (approvedEl) approvedEl.value = '';
                updateRemaining();
            } else {
                updateRemaining();
            }
        }
        phMemberEl?.addEventListener('change', togglePh);
        togglePh();
        // initial load
        loadRates().then(applySelected);
    }
</script>
<?= $this->endSection() ?>
