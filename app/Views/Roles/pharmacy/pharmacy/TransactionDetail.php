<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Transaction Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="lab-receipt">
  <div class="rc-header">
    <div>
      <h2 class="rc-title">Pharmacy Transaction</h2>
      <div class="rc-meta">
        <div class="k">Transaction #</div><div class="v" id="trxNo">…</div>
        <div class="k">Date</div><div class="v" id="trxDate">…</div>
        <div class="k">Total</div><div class="v" id="trxTotal">…</div>
      </div>
    </div>
    <div class="no-print">
      <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>

  <div class="rc-body">
    <div class="rc-section-title">Items</div>
    <table class="rc-table">
      <thead>
        <tr>
          <th>Medicine</th>
          <th style="width:100px; text-align:right;">Qty</th>
          <th style="width:160px; text-align:right;">Unit Price</th>
          <th style="width:160px; text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody id="trxItems">
        <tr><td colspan="4" style="text-align:center;color:#666;">Loading...</td></tr>
      </tbody>
    </table>

    <div class="rc-footer">
      <div class="sig">
        <div class="line"></div>
        <div>Released By: St. Peter Hospital</div>
      </div>
    </div>
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

loadDetails();
</script>

<?= $this->endSection() ?>