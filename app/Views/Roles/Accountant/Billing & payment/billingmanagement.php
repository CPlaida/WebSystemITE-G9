<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="header">
        <h1 class="page-title">Billing Management</h1>
    </div>

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

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        const content = `
            <div style="display:grid; grid-template-columns: 160px 1fr; gap: 12px 16px; align-items: center; text-align:left;">
                <label style="margin:0; color:#374151; font-weight:600;">Invoice #</label>
                <input type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${('INV-' + String(bill.id).padStart(6, '0'))}" disabled>

                <label style="margin:0; color:#374151; font-weight:600;">Patient</label>
                <div>
                    <input id="em_patient_id" type="number" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${bill.patient_id || ''}" disabled title="Patient is fixed for this bill">
                    <div style="font-size:12px; color:#6b7280; margin-top:6px;">Current: ${bill.patient_name || 'N/A'}</div>
                </div>

                <label style="margin:0; color:#374151; font-weight:600;">Amount</label>
                <input id="em_final_amount" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.final_amount || 0}">

                <label style="margin:0; color:#374151; font-weight:600;">Status</label>
                <select id="em_payment_status" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
                    ${['pending','partial','paid','overdue'].map(s => `<option value="${s}" ${String(bill.payment_status||'').toLowerCase()===s?'selected':''}>${s.charAt(0).toUpperCase()+s.slice(1)}</option>`).join('')}
                </select>

                <label style="margin:0; color:#374151; font-weight:600;">Bill Date</label>
                <input id="em_bill_date" type="date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.bill_date || ''}">

                <div style="grid-column: 1 / -1; height:1px; background:#e5e7eb; margin:8px 0;"></div>

                <label style="margin:0; color:#111827; font-weight:700;">PhilHealth Member</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input id="em_ph_member" type="checkbox" ${String(bill.philhealth_member||'0')==='1' ? 'checked' : ''}>
                    <span style="font-size:12px; color:#6b7280;">Check if patient is eligible for PhilHealth</span>
                </div>

                <label style="margin:0; color:#374151; font-weight:600;">Admission Date</label>
                <input id="em_admission_date" type="date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.admission_date || bill.bill_date || ''}">

                <label style="margin:0; color:#374151; font-weight:600;">Primary RVS Code</label>
                <input id="em_primary_rvs" type="text" placeholder="e.g., 48010" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.primary_rvs_code || ''}">

                <label style="margin:0; color:#374151; font-weight:600;">Primary ICD-10 Code</label>
                <input id="em_primary_icd" type="text" placeholder="e.g., A04.7" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.primary_icd10_code || ''}">

                <label style="margin:0; color:#374151; font-weight:600;">Suggested Deduction</label>
                <input id="em_ph_suggested" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${bill.philhealth_suggested_amount_calc || 0}" disabled>

                <label style="margin:0; color:#374151; font-weight:600;">Approved Deduction (Final)</label>
                <input id="em_ph_approved" type="number" step="0.01" placeholder="Enter approved amount" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" value="${bill.philhealth_approved_amount || ''}">

                <label style="margin:0; color:#374151; font-weight:600;">Reason / Note</label>
                <textarea id="em_ph_note" rows="2" placeholder="Required if codes missing or approved < suggested" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"></textarea>

                <label style="margin:0; color:#374151; font-weight:600;">Remaining Balance</label>
                <input id="em_remaining" type="text" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#f3f4f6; color:#6b7280;" value="${(() => { const gross=(+bill.final_amount||0); const ph=(+bill.philhealth_approved_amount||0); const net=Math.max(gross - ph,0); return net.toFixed(2); })()}" disabled>

            </div>`;

        Swal.fire({
            title: 'Edit Bill',
            html: content,
            width: 650,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            focusConfirm: false,
            preConfirm: () => {
                const fd = new FormData();
                fd.append('final_amount', document.getElementById('em_final_amount').value);
                fd.append('payment_status', document.getElementById('em_payment_status').value);
                fd.append('bill_date', document.getElementById('em_bill_date').value);
                // PhilHealth
                const phMember = document.getElementById('em_ph_member').checked ? '1' : '0';
                fd.append('philhealth_member', phMember);
                fd.append('admission_date', document.getElementById('em_admission_date').value);
                fd.append('primary_rvs_code', document.getElementById('em_primary_rvs').value);
                fd.append('primary_icd10_code', document.getElementById('em_primary_icd').value);
                const approved = document.getElementById('em_ph_approved').value;
                if (phMember === '1') {
                    fd.append('philhealth_approved_amount', approved);
                    fd.append('philhealth_note', document.getElementById('em_ph_note').value);
                }
                return fetch('<?= base_url('billing/update/') ?>' + bill.id, { method: 'POST', body: fd })
                    .then(r => r.ok ? r.json() : r.json().then(err => Promise.reject(err)))
                    .catch(err => { Swal.showValidationMessage(err.errors ? Object.values(err.errors).join('<br>') : 'Update failed'); });
            }
        }).then(res => {
            if (res.isConfirmed) location.reload();
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
