<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Transaction Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="receipt-page">
    <div class="action-buttons no-print">
        <a href="<?= base_url('admin/pharmacy/transactions') ?>" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Transactions
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    <div class="receipt-content">
        <div class="header">
            <h1>St. Peter Hospital</h1>
            <p>G,S,C, Phillipines<br>
            Phone: (63) 9938150583 | Email: Rmmc@stpeters.com</p>
        </div>

        <div class="receipt-info">
            <div class="bill-info">
                <p><strong>Transaction #:</strong> <span id="trxNo">…</span></p>
                <p><strong>Date:</strong> <span id="trxDate">…</span></p>
            </div>
            <div class="hospital-info">
                <p id="patientInfo" style="display:none;">
                    <strong>Patient:</strong> <span id="patientName">—</span>
                </p>
            </div>
        </div>

        <table class="receipt-table">
      <thead>
        <tr>
          <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
        </tr>
      </thead>
      <tbody id="trxItems">
                <tr><td colspan="4" style="text-align: center;">Loading...</td></tr>
      </tbody>
    </table>

        <div class="total">
            <p>Total: <span id="trxTotal">₱0.00</span></p>
        </div>

        <div class="footer">
            <p>Thank you for choosing our hospital!</p>
            <p>For any inquiries, please contact our pharmacy department.</p>
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
  if (!json.success) { 
    document.getElementById('trxItems').innerHTML = '<tr><td colspan="4" style="text-align:center;color:#c00;">Failed to load transaction details</td></tr>'; 
    return; 
  }

  const d = json.data || {};
  document.getElementById('trxNo').textContent = d.transaction_number || '-';
  document.getElementById('trxDate').textContent = d.date ? new Date(d.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
  
  if (d.patient_name) {
    document.getElementById('patientName').textContent = d.patient_name;
    document.getElementById('patientInfo').style.display = 'block';
  }

  const list = d.items || [];
  const body = document.getElementById('trxItems');
  if (!list.length){
    body.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#666;">No items found</td></tr>';
    document.getElementById('trxTotal').textContent = peso(0);
    return;
  }
  
  // Calculate total from items (sum of all item totals)
  let calculatedTotal = 0;
  body.innerHTML = '';
  list.forEach((it, idx) => {
    const itemTotal = parseFloat((it.total_price ?? (it.unit_price * it.quantity) ?? (it.price * it.quantity)) || 0);
    calculatedTotal += itemTotal;
    
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${it.medicine_name || it.medicine_id || '—'}</td>
      <td>${it.quantity || 0}</td>
      <td>₱${parseFloat(it.unit_price || it.price || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
      <td>₱${itemTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
    `;
    body.appendChild(tr);
  });
  
  // Use calculated total from items (no tax)
  document.getElementById('trxTotal').textContent = peso(calculatedTotal);
}

loadDetails();
</script>

<?= $this->endSection() ?>