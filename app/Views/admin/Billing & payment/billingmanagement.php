<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Billing Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .box {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #3498db;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .box h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .box p {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 20px 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 0 1px #e0e0e0;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-paid {
        background-color: #d4edda;
        color: #155724;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background-color: #3498db;
        color: white;
    }


    .btn-view {
        background-color: #2ecc71;
        color: white;
    }

    .btn-receipt {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-receipt:hover {
        background-color: #5a6268;
        color: white;
    }

    .search-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .search-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .search-button {
        background-color: #2c3e50;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0 20px;
        cursor: pointer;
        font-weight: 500;
    }
</style>

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
