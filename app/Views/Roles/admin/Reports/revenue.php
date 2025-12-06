<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Revenue Report<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Revenue Report</h1>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="<?= base_url('reports') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="<?= base_url('reports/revenue') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Start Date</label>
                    <input type="date" name="start_date" value="<?= $filters['start_date'] ?? date('Y-m-01') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">End Date</label>
                    <input type="date" name="end_date" value="<?= $filters['end_date'] ?? date('Y-m-d') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Payment Method</label>
                    <select name="payment_method" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">All Methods</option>
                        <option value="cash" <?= ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="insurance" <?= ($filters['payment_method'] ?? '') === 'insurance' ? 'selected' : '' ?>>Insurance</option>
                        <option value="hmo" <?= ($filters['payment_method'] ?? '') === 'hmo' ? 'selected' : '' ?>>HMO</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Payment Status</label>
                    <select name="payment_status" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">All Status</option>
                        <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-filter"></i> Apply</button>
                    <a href="<?= base_url('reports/revenue') ?>" class="btn btn-secondary" style="width: 100%; margin-top: 5px;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="card-container">
        <div class="card">
            <h3>Total Revenue</h3>
            <div class="value">₱<?= number_format($reportData['total_revenue'] ?? 0, 2) ?></div>
        </div>
        <div class="card">
            <h3>Total Bills</h3>
            <div class="value"><?= $reportData['total_bills'] ?? 0 ?></div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill ID</th>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData['bills'])): ?>
                            <?php foreach ($reportData['bills'] as $bill): ?>
                                <tr>
                                    <td>#<?= $bill['id'] ?? 'N/A' ?></td>
                                    <td><?= $bill['patient_name'] ?? 'N/A' ?></td>
                                    <td><?= date('M d, Y', strtotime($bill['bill_date'] ?? date('Y-m-d'))) ?></td>
                                    <td><?= ucfirst($bill['payment_method'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php $ps = strtolower($bill['payment_status'] ?? 'pending'); ?>
                                        <span class="<?= $ps === 'paid' ? 'status-paid' : ($ps === 'partial' ? 'status-pending' : 'status-pending') ?>">
                                            <?= ucfirst($bill['payment_status'] ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td>₱<?= number_format($bill['final_amount'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No data available for the selected filters</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

