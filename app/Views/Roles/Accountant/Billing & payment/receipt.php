<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $bill['bill_number'] ?? 'N/A' ?> - St. Peter Hospital</title>
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="receipt-page">
        <div class="action-buttons no-print">
            <a href="<?= base_url('billing') ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Billing
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
</body>
</html>
