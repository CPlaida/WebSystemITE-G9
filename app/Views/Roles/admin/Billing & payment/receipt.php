<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= esc($bill['bill_number'] ?? 'N/A') ?> - St. Peter Hospital</title>
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
        .totals-note { font-size: 12px; margin-top: 10px; }
        .grand-total-row td { font-weight: 700; background: #f5f8ff; }
        @media print {
            body { padding: 0; background: #fff; }
            .receipt-wrapper { border: none; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <?php
        $billNumber = $bill['bill_number'] ?? 'N/A';
        $issueDate = $bill['date_issued'] ?? date('Y-m-d');
        $formattedDate = date('M d, Y', strtotime($issueDate));
        $patientName = $bill['patient_name'] ?? 'N/A';
        $patientAddress = $bill['patient_address'] ?? 'Not Provided';
        $patientPhone = $bill['patient_phone'] ?? 'Not Provided';
        $patientId = !empty($bill['patient_id']) ? 'PT-' . str_pad((string)$bill['patient_id'], 6, '0', STR_PAD_LEFT) : '—';
        $serviceName = $bill['service_name'] ?? ($bill['items'][0]['description'] ?? 'Hospital Services');
        $admissionDate = $bill['admission_date'] ?? ($bill['bill_date'] ?? $issueDate);
        $physician = $bill['consulting_doctor'] ?? ($bill['notes'] ?? 'Not Specified');
        $status = ucfirst(strtolower($bill['status'] ?? ($bill['payment_status'] ?? 'Pending')));
        $subtotal = (float)($bill['subtotal'] ?? ($bill['total_amount'] ?? 0));
        $discount = (float)($bill['discount'] ?? 0);
        $tax = (float)($bill['tax'] ?? 0);
        $total = (float)($bill['total'] ?? ($bill['final_amount'] ?? ($subtotal - $discount + $tax)));
        $philhealth = (float)($bill['philhealth_approved_amount'] ?? 0);
        $hmo = (float)($bill['hmo_approved_amount'] ?? 0);
        $patientShare = max($total - $philhealth - $hmo, 0);
        
        // HMO details
        $hmoProviderName = $bill['hmo_provider_name'] ?? '';
        $hmoMemberNo = $bill['hmo_member_no'] ?? '';
        $hmoLoaNumber = $bill['hmo_loa_number'] ?? '';
        $hmoValidFrom = $bill['hmo_valid_from'] ?? '';
        $hmoValidTo = $bill['hmo_valid_to'] ?? '';
        $hasHmo = !empty($hmoProviderName) || !empty($hmoMemberNo) || $hmo > 0;
        
        // PhilHealth details
        $phRvsCode = $bill['primary_rvs_code'] ?? '';
        $phIcdCode = $bill['primary_icd10_code'] ?? '';
        $hasPhilhealth = $philhealth > 0 || !empty($phRvsCode) || !empty($phIcdCode);

        $componentMap = [
            'Room & Nursing Charges' => (float)($bill['consultation_fee'] ?? 0),
            'Medication Charges' => (float)($bill['medication_cost'] ?? 0),
            'Laboratory / Diagnostics' => (float)($bill['lab_tests_cost'] ?? 0),
            'Other Professional Fees' => (float)($bill['other_charges'] ?? 0),
        ];
        $componentMapFiltered = array_filter($componentMap, fn($amt) => $amt > 0);
        if (empty($componentMapFiltered)) {
            $componentMapFiltered['Hospital Charges'] = $subtotal > 0 ? $subtotal : $total;
        }
    ?>
    <div class="receipt-wrapper">
        <div class="no-print">
            <a href="<?= base_url('billing') ?>">&larr; Back to Billing</a>
            <button onclick="window.print()">Print Receipt</button>
        </div>

        <header>
            <div class="hospital-heading">
                <h1>St. Peter Hospital</h1>
                <p>Reg. No. SPH-00923</p>
                <p>Purok San Roque, Glan, Sarangani Province</p>
                <p>Tel: (63) 993-815-0583 · Email: billing@stpeters.com</p>
                <p><em>Available 24 hours · Compassionate care for every patient</em></p>
            </div>
            <div class="receipt-meta">
                <strong>Receipt / Bill #: <?= esc($billNumber) ?></strong><br>
                Date Issued: <?= esc($formattedDate) ?><br>
                Payment Status: <?= esc($status) ?><br>
                Payment Method: <?= esc(strtoupper($bill['payment_method'] ?? 'Cash')) ?>
            </div>
        </header>

        <h3 class="section-title">Patient & Admission Details</h3>
        <table class="info-table" style="margin-bottom:18px;">
            <tr>
                <td class="label">Patient ID</td>
                <td><?= esc($patientId) ?></td>
                <td class="label">Patient Name</td>
                <td><?= esc($patientName) ?></td>
            </tr>
            <tr>
                <td class="label">Address</td>
                <td><?= esc($patientAddress) ?></td>
                <td class="label">Contact</td>
                <td><?= esc($patientPhone) ?></td>
            </tr>
            <tr>
                <td class="label">Admission Date</td>
                <td><?= esc($admissionDate ? date('M d, Y', strtotime($admissionDate)) : '—') ?></td>
                <td class="label">Consulting Doctor</td>
                <td><?= esc($physician) ?></td>
            </tr>
        </table>

        <h3 class="section-title">Provisional Bill Summary</h3>
        <table class="summary-table" style="margin-bottom:18px;">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th style="width:25%; text-align:right;">Amount (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($componentMapFiltered as $label => $amount): ?>
                    <tr>
                        <td><?= esc($label) ?></td>
                        <td style="text-align:right;"><?= number_format($amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="grand-total-row">
                    <td>Total Bill Amount</td>
                    <td style="text-align:right;"><?= number_format($total, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <h3 class="section-title">Detailed Breakdown</h3>
        <table style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th style="width:6%;">#</th>
                    <th style="width:30%;">Particulars</th>
                    <th style="width:18%;">Date</th>
                    <th style="width:12%; text-align:right;">Rate</th>
                    <th style="width:12%; text-align:center;">Units</th>
                    <th style="width:12%; text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bill['items'])): ?>
                    <?php
                    // Group items by category
                    $groupedItems = [];
                    foreach ($bill['items'] as $item) {
                        $category = $item['category'] ?? 'general';
                        if (!isset($groupedItems[$category])) {
                            $groupedItems[$category] = [];
                        }
                        $groupedItems[$category][] = $item;
                    }
                    
                    // Category display names
                    $categoryNames = [
                        'laboratory' => 'Laboratory',
                        'pharmacy' => 'Pharmacy',
                        'consultation' => 'Appointment',
                        'room' => 'Room & Bed',
                        'general' => 'Other Services'
                    ];
                    
                    // Display order
                    $categoryOrder = ['laboratory', 'pharmacy', 'consultation', 'room', 'general'];
                    
                    $itemNumber = 0;
                    foreach ($categoryOrder as $cat) {
                        if (!isset($groupedItems[$cat]) || empty($groupedItems[$cat])) {
                            continue;
                        }
                        
                        // Add category header
                        $categoryName = $categoryNames[$cat] ?? ucfirst($cat);
                        ?>
                        <tr style="background-color: #e3f2fd; font-weight: bold;">
                            <td colspan="6" style="padding: 10px; border-bottom: 2px solid #2196f3;">
                                <?= esc($categoryName) ?>
                            </td>
                        </tr>
                        <?php
                        
                        // Add items for this category
                        foreach ($groupedItems[$cat] as $item): 
                            $itemNumber++;
                            ?>
                            <tr>
                                <td><?= $itemNumber ?></td>
                                <td><?= esc($item['description'] ?? '—') ?></td>
                                <td><?= esc($bill['bill_date'] ?? $issueDate) ?></td>
                                <td style="text-align:right;">₱<?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                                <td style="text-align:center;"><?= esc($item['quantity'] ?? 1) ?></td>
                                <td style="text-align:right;">₱<?= number_format((float)($item['amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach;
                    }
                    
                    // Handle any remaining categories not in the order list
                    foreach ($groupedItems as $cat => $catItems) {
                        if (in_array($cat, $categoryOrder)) {
                            continue; // Already processed
                        }
                        $categoryName = $categoryNames[$cat] ?? ucfirst($cat);
                        ?>
                        <tr style="background-color: #e3f2fd; font-weight: bold;">
                            <td colspan="6" style="padding: 10px; border-bottom: 2px solid #2196f3;">
                                <?= esc($categoryName) ?>
                            </td>
                        </tr>
                        <?php
                        foreach ($catItems as $item): 
                            $itemNumber++;
                            ?>
                            <tr>
                                <td><?= $itemNumber ?></td>
                                <td><?= esc($item['description'] ?? '—') ?></td>
                                <td><?= esc($bill['bill_date'] ?? $issueDate) ?></td>
                                <td style="text-align:right;">₱<?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                                <td style="text-align:center;"><?= esc($item['quantity'] ?? 1) ?></td>
                                <td style="text-align:right;">₱<?= number_format((float)($item['amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach;
                    }
                    ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; font-style:italic;">No itemized charges were recorded for this bill.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($hasPhilhealth): ?>
        <h3 class="section-title">PhilHealth Coverage</h3>
        <table class="info-table" style="margin-bottom:18px;">
            <tr>
                <td class="label">RVS Code</td>
                <td><?= esc($phRvsCode ?: '—') ?></td>
                <td class="label">ICD-10 Code</td>
                <td><?= esc($phIcdCode ?: '—') ?></td>
            </tr>
            <tr>
                <td class="label">Approved Amount</td>
                <td colspan="3" style="font-weight:600; color:#065f46;">₱<?= number_format($philhealth, 2) ?></td>
            </tr>
        </table>
        <?php endif; ?>
        
        <?php if ($hasHmo): ?>
        <h3 class="section-title">HMO Coverage</h3>
        <table class="info-table" style="margin-bottom:18px;">
            <tr>
                <td class="label">HMO Provider</td>
                <td><?= esc($hmoProviderName ?: '—') ?></td>
                <td class="label">Member Number</td>
                <td><?= esc($hmoMemberNo ?: '—') ?></td>
            </tr>
            <tr>
                <td class="label">LOA Number</td>
                <td><?= esc($hmoLoaNumber ?: '—') ?></td>
                <td class="label">Coverage Period</td>
                <td>
                    <?php if ($hmoValidFrom && $hmoValidTo): ?>
                        <?= esc(date('M d, Y', strtotime($hmoValidFrom))) ?> - <?= esc(date('M d, Y', strtotime($hmoValidTo))) ?>
                    <?php elseif ($hmoValidFrom): ?>
                        From: <?= esc(date('M d, Y', strtotime($hmoValidFrom))) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Approved Amount</td>
                <td colspan="3" style="font-weight:600; color:#1e40af;">₱<?= number_format($hmo, 2) ?></td>
            </tr>
        </table>
        <?php endif; ?>
        
        <h3 class="section-title">Coverage & Balances</h3>
        <table class="summary-table">
            <tbody>
                <tr>
                    <td style="font-weight:600;">Gross Hospital Charges</td>
                    <td style="text-align:right; font-weight:600;">₱<?= number_format($total, 2) ?></td>
                </tr>
                <?php if ($philhealth > 0): ?>
                <tr>
                    <td>Less: PhilHealth Benefit</td>
                    <td style="text-align:right; color:#065f46;">-₱<?= number_format($philhealth, 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($hmo > 0): ?>
                <tr>
                    <td>Less: HMO / Insurance Coverage</td>
                    <td style="text-align:right; color:#1e40af;">-₱<?= number_format($hmo, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="grand-total-row">
                    <td style="font-size:15px;">Patient Balance / Payable</td>
                    <td style="text-align:right; font-size:15px; color:#92400e;">₱<?= number_format($patientShare, 2) ?></td>
                </tr>
            </tbody>
        </table>
        
        <?php
        // Get payment information
        $totalPaid = (float)($bill['total_paid'] ?? 0);
        $remainingBalance = (float)($bill['remaining_balance'] ?? $patientShare);
        $payments = $bill['payments'] ?? [];
        $paymentStatus = strtolower($bill['payment_status'] ?? $bill['status'] ?? 'pending');
        
        // If payment status is 'paid', ensure remaining balance is 0
        if ($paymentStatus === 'paid') {
            $remainingBalance = 0.0;
        }
        
        // Ensure remaining balance is 0 if fully paid (handle rounding)
        if ($remainingBalance < 0.01) {
            $remainingBalance = 0.0;
        }
        
        // If total paid equals or exceeds patient share, it's fully paid
        if ($totalPaid >= $patientShare && $patientShare > 0) {
            $remainingBalance = 0.0;
        }
        ?>
        
        <?php if (!empty($payments) || $totalPaid > 0): ?>
        <h3 class="section-title">Payment Summary</h3>
        <table class="summary-table" style="margin-bottom:18px;">
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>
                                Payment on <?= esc(date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'] ?? date('Y-m-d')))) ?>
                                <?php if (!empty($payment['payment_method'])): ?>
                                    (<?= esc(strtoupper($payment['payment_method'])) ?>)
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; color:#059669;">₱<?= number_format((float)($payment['amount'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>Total Amount Paid</td>
                        <td style="text-align:right; color:#059669;">₱<?= number_format($totalPaid, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="background-color: #f0fdf4; font-weight: 600;">
                    <td>Total Paid</td>
                    <td style="text-align:right; color:#059669;">₱<?= number_format($totalPaid, 2) ?></td>
                </tr>
                <?php if ($remainingBalance > 0): ?>
                <tr class="grand-total-row">
                    <td style="font-size:15px; color:#dc2626;">
                        Remaining Balance
                    </td>
                    <td style="text-align:right; font-size:15px; color:#dc2626; font-weight:700;">
                        ₱<?= number_format($remainingBalance, 2) ?>
                    </td>
                </tr>
                <?php else: ?>
                <tr class="grand-total-row">
                    <td style="font-size:15px; color:#059669;">
                        Payment Status
                    </td>
                    <td style="text-align:right; font-size:15px; color:#059669; font-weight:700;">
                        ✓ Paid
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <p class="totals-note" style="margin-top:20px; padding-top:20px; border-top:2px solid #0f2a5f;">
            <strong>Amount paid in words:</strong> ____________________________<br><br>
            <strong>Authorized Signature:</strong> ____________________________<br>
            <span style="font-size:11px; color:#666;">Date: <?= esc($formattedDate) ?></span>
        </p>

        <p style="text-align:center; margin-top:26px; font-size:12px;">
            Thank you for choosing St. Peter Hospital. For billing inquiries, please contact our Accounts Department within five (5) working days.
        </p>
    </div>
</body>
</html>
