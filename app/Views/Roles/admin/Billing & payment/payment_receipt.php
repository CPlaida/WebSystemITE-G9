<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #<?= esc($payment['id'] ?? 'N/A') ?> - St. Peter Hospital</title>
    <link rel="stylesheet" href="<?= base_url('css/main.css') ?>">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 32px 16px;
            color: #111;
            background: #f2f4f8;
        }
        .receipt-wrapper {
            width: 100%;
            max-width: 880px;
            margin: 0 auto 40px;
            background: #fff;
            padding: 32px 40px;
            border: 2px solid #0f2a5f;
            box-shadow: 0 10px 30px rgba(15,42,95,0.12);
        }
        .no-print { margin-bottom: 16px; display: flex; gap: 12px; }
        .no-print a,.no-print button {
            border: 1px solid #0f2a5f;
            background: #0f2a5f;
            color: #fff;
            padding: 8px 18px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #0f2a5f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .hospital-heading h1 { margin: 0; font-size: 28px; letter-spacing: 1px; }
        .hospital-heading p { margin: 2px 0; font-size: 13px; }
        .receipt-meta { text-align: right; font-size: 13px; }
        .section-title {
            text-transform: uppercase;
            font-weight: 700;
            color: #0f2a5f;
            margin: 26px 0 8px;
            border-bottom: 1px solid #0f2a5f;
            padding-bottom: 4px;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table thead th {
            background: #0f2a5f;
            color: #fff;
            padding: 8px;
            text-transform: uppercase;
            font-size: 11px;
        }
        table tbody td { padding: 9px 8px; border-bottom: 1px solid #d5d7e0; }
        .info-table td { border: 1px solid #d5d7e0; padding: 8px 10px; }
        .info-table td.label { width: 22%; font-weight: 600; background: #f3f4f8; }
        .summary-table td { border: 1px solid #d5d7e0; }
        .summary-table tr:last-child td { font-weight: 700; background: #fafbff; }
        .payment-amount { font-size: 24px; font-weight: 700; color: #059669; }
        @media print {
            body { padding: 0; background: #fff; }
            .receipt-wrapper { border: none; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <?php
        $paymentId = $payment['id'] ?? 'N/A';
        $paymentDate = $payment['payment_date'] ?? date('Y-m-d H:i:s');
        $formattedDate = date('M d, Y h:i A', strtotime($paymentDate));
        $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
        if ($patientName === '') {
            $patientName = $patient['patient_name'] ?? $patient['name'] ?? 'N/A';
        }
        $patientAddress = $patient['address'] ?? 'Not Provided';
        $patientPhone = $patient['phone'] ?? 'Not Provided';
        $patientId = !empty($patient['id']) ? 'PT-' . str_pad((string)$patient['id'], 6, '0', STR_PAD_LEFT) : '—';
        $billNumber = 'INV-' . str_pad((string)$bill['id'], 6, '0', STR_PAD_LEFT);
        $paymentAmount = (float)($payment['amount'] ?? 0);
        $paymentMethod = strtoupper($payment['payment_method'] ?? 'cash');
        $notes = $payment['notes'] ?? '';
        
        $billTotal = (float)($bill['final_amount'] ?? 0);
        $totalPaid = (float)($totalPaid ?? 0);
        $remainingBalance = (float)($remainingBalance ?? 0);
    ?>
    <div class="receipt-wrapper">
        <div class="no-print">
            <a href="javascript:window.print()">Print Receipt</a>
            <a href="<?= base_url('billing') ?>">Back to Billing</a>
        </div>
        
        <header>
            <div class="hospital-heading">
                <h1>ST. PETER HOSPITAL</h1>
                <p>Payment Receipt</p>
            </div>
            <div class="receipt-meta">
                <div><strong>Receipt #:</strong> PAY-<?= str_pad((string)$paymentId, 6, '0', STR_PAD_LEFT) ?></div>
                <div><strong>Date:</strong> <?= esc($formattedDate) ?></div>
            </div>
        </header>

        <div class="section-title">Patient Information</div>
        <table class="info-table">
            <tr>
                <td class="label">Patient Name:</td>
                <td><?= esc($patientName) ?></td>
                <td class="label">Patient ID:</td>
                <td><?= esc($patientId) ?></td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td><?= esc($patientAddress) ?></td>
                <td class="label">Phone:</td>
                <td><?= esc($patientPhone) ?></td>
            </tr>
        </table>

        <div class="section-title">Payment Details</div>
        <table class="info-table">
            <tr>
                <td class="label">Bill Number:</td>
                <td><?= esc($billNumber) ?></td>
                <td class="label">Payment Method:</td>
                <td><?= esc($paymentMethod) ?></td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td><?= esc($formattedDate) ?></td>
                <td class="label"></td>
                <td></td>
            </tr>
        </table>

        <?php
        $philhealthAmount = (float)($philhealthAmount ?? $bill['philhealth_approved_amount'] ?? 0);
        $hmoAmount = (float)($hmoAmount ?? $bill['hmo_approved_amount'] ?? 0);
        $patientShare = (float)($patientShare ?? ($billTotal - $philhealthAmount - $hmoAmount));
        
        // Ensure remaining balance is 0 if fully paid
        if ($remainingBalance < 0.01) {
            $remainingBalance = 0.0;
        }
        ?>
        
        <div class="section-title">Payment Summary</div>
        <table class="summary-table">
            <tr>
                <td style="text-align:right; padding-right:20px;">Gross Bill Total:</td>
                <td style="text-align:right; font-weight:600;">₱<?= number_format($billTotal, 2) ?></td>
            </tr>
            <?php if ($philhealthAmount > 0): ?>
            <tr>
                <td style="text-align:right; padding-right:20px; color:#065f46;">Less: PhilHealth Benefit</td>
                <td style="text-align:right; color:#065f46;">-₱<?= number_format($philhealthAmount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($hmoAmount > 0): ?>
            <tr>
                <td style="text-align:right; padding-right:20px; color:#1e40af;">Less: HMO / Insurance Coverage</td>
                <td style="text-align:right; color:#1e40af;">-₱<?= number_format($hmoAmount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr style="background:#fafbff;">
                <td style="text-align:right; padding-right:20px; font-weight:700;">Patient Balance / Payable:</td>
                <td style="text-align:right; font-weight:700;">₱<?= number_format($patientShare, 2) ?></td>
            </tr>
            <tr>
                <td style="text-align:right; padding-right:20px;">Total Paid (Before This Payment):</td>
                <td style="text-align:right;">₱<?= number_format($totalPaid - $paymentAmount, 2) ?></td>
            </tr>
            <tr style="background:#ecfdf5;">
                <td style="text-align:right; padding-right:20px; font-weight:700; color:#059669;">This Payment Amount:</td>
                <td style="text-align:right;" class="payment-amount">₱<?= number_format($paymentAmount, 2) ?></td>
            </tr>
            <tr>
                <td style="text-align:right; padding-right:20px;">Total Paid (After This Payment):</td>
                <td style="text-align:right; font-weight:600;">₱<?= number_format($totalPaid, 2) ?></td>
            </tr>
            <?php if ($remainingBalance > 0): ?>
            <tr class="grand-total-row">
                <td style="text-align:right; padding-right:20px; font-size:15px; color:#dc2626;">
                    Remaining Balance
                </td>
                <td style="text-align:right; font-size:15px; font-weight:700; color:#dc2626;">
                    ₱<?= number_format($remainingBalance, 2) ?>
                </td>
            </tr>
            <?php else: ?>
            <tr class="grand-total-row">
                <td style="text-align:right; padding-right:20px; font-size:15px; color:#059669;">
                    Payment Status
                </td>
                <td style="text-align:right; font-size:15px; font-weight:700; color:#059669;">
                    ✓ Paid
                </td>
            </tr>
            <?php endif; ?>
        </table>

        <?php if (!empty($notes)): ?>
        <div class="section-title">Notes</div>
        <p style="font-size:13px; color:#6b7280; margin:8px 0;"><?= esc($notes) ?></p>
        <?php endif; ?>

        <?php if (count($allPayments) > 1): ?>
        <div class="section-title">Payment History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th style="text-align:right;">Amount</th>
                    <th style="text-align:center;">Method</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allPayments as $p): ?>
                <tr style="<?= $p['id'] == $paymentId ? 'background:#ecfdf5; font-weight:600;' : '' ?>">
                    <td><?= date('M d, Y h:i A', strtotime($p['payment_date'] ?? $p['created_at'])) ?></td>
                    <td style="text-align:right;">₱<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                    <td style="text-align:center;"><?= strtoupper($p['payment_method'] ?? 'cash') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div style="margin-top:40px; padding-top:20px; border-top:2px solid #0f2a5f; text-align:center; font-size:12px; color:#6b7280;">
            <p style="margin:4px 0;">This is a computer-generated receipt.</p>
            <p style="margin:4px 0;">Thank you for your payment!</p>
        </div>
    </div>
</body>
</html>

