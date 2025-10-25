<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Receipt #<?= $bill['bill_number'] ?? 'N/A' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="receipt-page">
        <div class="action-buttons no-print" style="margin-bottom: 10px;">
            <a href="<?= base_url('billing') ?>" class="btn btn-back">Back to Billing</a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
        </div>

        <div class="receipt-content">
            <div class="header">
                <h1>St. Peter Hospital</h1>
                <p>G,S,C, Phillipines<br>
                Phone: (63) 9938150583 | Email: Rmmc@stpeters.com</p>
            </div>

            <div class="receipt-info">
                <div class="hospital-info">
                    <p><strong>Patient:</strong> <?= $bill['patient_name'] ?? 'N/A' ?></p>
                    <p><strong>Address:</strong> <?= $bill['patient_address'] ?? 'N/A' ?></p>
                    <p><strong>Contact:</strong> <?= $bill['patient_phone'] ?? 'N/A' ?></p>
                </div>
                <div class="bill-info">
                    <p><strong>Receipt #:</strong> <?= $bill['bill_number'] ?? 'N/A' ?></p>
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($bill['date_issued'] ?? 'now')) ?></p>
                    <p><strong>Status:</strong> <span style="color: #27ae60"><?= $bill['status'] ?? 'Paid' ?></span></p>
                </div>
            </div>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bill['items'])): ?>
                        <?php foreach ($bill['items'] as $item): ?>
                        <tr>
                            <td><?= $item['description'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                            <td>₱<?= number_format($item['amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="total">
                <p>Subtotal: ₱<?= number_format($bill['subtotal'] ?? 0, 2) ?></p>
                <p>Tax (12%): ₱<?= number_format($bill['tax'] ?? 0, 2) ?></p>
                <p>Total: ₱<?= number_format($bill['total'] ?? 0, 2) ?></p>
            </div>

            <div class="footer">
                <p>Thank you for choosing our hospital!</p>
                <p>For any inquiries, please contact our billing department.</p>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
