<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pharmacy Receipt</title>
  <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
</head>
<body>
  <div class="receipt-page">
    <div class="receipt-content">
<?php if (isset($transaction) && is_array($transaction)): ?>
  <div class="header">
    <h1>St. Peter Hospital</h1>
    <p>G,S,C, Philippines · Phone: (63) 9938150583 · Email: Rmmc@stpeters.com</p>
  </div>

  <div class="receipt-info">
    <div>
      <p><strong>Transaction #:</strong> <?= esc($transaction['transaction_number'] ?? '-') ?></p>
      <p><strong>Patient:</strong> <?= esc($transaction['patient_name'] ?? $transaction['patient_id'] ?? '-') ?></p>
    </div>
    <div>
      <p><strong>Date:</strong> <?= esc($transaction['date'] ?? '-') ?></p>
      <p><strong>Total:</strong> ₱<?= number_format((float)($transaction['total_amount'] ?? 0), 2) ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Medicine</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($transaction['items'])): ?>
        <?php foreach ($transaction['items'] as $it): ?>
          <tr>
            <td><?= esc($it['medicine_name'] ?? $it['medication_id']) ?></td>
            <td><?= esc($it['quantity']) ?></td>
            <td>₱<?= number_format((float)($it['unit_price'] ?? 0), 2) ?></td>
            <td>₱<?= number_format((float)($it['total_price'] ?? 0), 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" style="text-align:center;color:#666;">No items</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="total">
    <p>Subtotal: ₱<?= number_format((float)($transaction['subtotal'] ?? 0), 2) ?></p>
    <p>Tax (12%): ₱<?= number_format((float)($transaction['tax'] ?? 0), 2) ?></p>
    <p>Total: ₱<?= number_format((float)($transaction['total_amount'] ?? 0), 2) ?></p>
  </div>

  <div class="footer">
    <p>Thank you for your purchase!</p>
    <p>For any inquiries, please contact our pharmacy.</p>
  </div>

  <div class="no-print">
    <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Print</button>
  </div>

  <script>setTimeout(()=>window.print(), 250);</script>
  </div></div></body></html>
  <?php return; ?>
<?php endif; ?>
</body>
</html>