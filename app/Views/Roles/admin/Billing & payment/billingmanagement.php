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
                                    <button type="button" class="btn btn-edit" data-action="edit">Edit</button>
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
                return fetch('<?= base_url('billing/update/') ?>' + bill.id, { method: 'POST', body: fd })
                    .then(r => r.ok ? r.json() : r.json().then(err => Promise.reject(err)))
                    .catch(err => { Swal.showValidationMessage(err.errors ? Object.values(err.errors).join('<br>') : 'Update failed'); });
            }
        }).then(res => {
            if (res.isConfirmed) location.reload();
        });
    }
</script>
<?= $this->endSection() ?>
