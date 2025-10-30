<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Transaction Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow p-6">
  <h2 class="text-lg font-bold mb-4">Transaction Details</h2>

  <div id="trxMeta" class="mb-4" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
    <div><strong>Transaction #:</strong> <span id="trxNo">…</span></div>
    <div><strong>Date:</strong> <span id="trxDate">…</span></div>
    <div><strong>Patient:</strong> <span id="trxPatient">…</span></div>
    <div><strong>Total:</strong> <span id="trxTotal">…</span></div>
  </div>

  <div class="overflow-x-auto">
    <table>
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody id="trxItems">
        <tr><td colspan="4" style="text-align:center;color:#666;">Loading...</td></tr>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <a class="btn btn-print" id="printBtn"><i class="fas fa-print"></i> Print</a>
  </div>
</div>

<script>
const API_BASE = '<?= site_url('api/pharmacy') ?>';
const trxId = '<?= esc($transactionId ?? '') ?>';

function peso(v){ v = parseFloat(v||0); return '₱' + v.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }

async function loadDetails() {
  const res = await fetch(API_BASE + '/transaction/' + trxId);
  const json = await res.json();
  if (!json.success) { document.getElementById('trxItems').innerHTML = '<tr><td colspan="4" style="text-align:center;color:#c00;">Failed to load</td></tr>'; return; }

  const d = json.data || {};
  document.getElementById('trxNo').textContent = d.transaction_number || '-';
  document.getElementById('trxDate').textContent = d.date || '-';
  document.getElementById('trxPatient').textContent = d.patient_name || d.patient_id || '-';
  document.getElementById('trxTotal').textContent = peso(d.total_amount || 0);

  const list = d.items || [];
  const body = document.getElementById('trxItems');
  if (!list.length){
    body.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#666;">No items</td></tr>';
    return;
  }
  body.innerHTML = '';
  list.forEach(it=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${it.medicine_name || it.medicine_id}</td>
      <td>${it.quantity}</td>
      <td>${peso(it.unit_price || it.price || 0)}</td>
      <td>${peso((it.total_price ?? (it.price*it.quantity)) || 0)}</td>
    `;
    body.appendChild(tr);
  });
}

document.getElementById('printBtn').href = '<?= site_url('admin/pharmacy/transaction/print/') ?>' + trxId;
loadDetails();
</script>

<?= $this->endSection() ?>