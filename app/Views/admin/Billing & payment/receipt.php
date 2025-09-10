<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $bill['bill_number'] ?? 'N/A' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .hospital-info {
            text-align: left;
        }
        .bill-info {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
        }
        .btn-print {
            background-color: #0d6efd;
            color: white;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        @media print {
            .no-print, .action-buttons {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print">
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

        <table>
            <thead>
                <tr>
                    <th>Description</th>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
