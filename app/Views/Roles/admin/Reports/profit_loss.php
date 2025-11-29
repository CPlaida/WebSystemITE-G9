<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Profit & Loss Statement<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="header">
        <h1 class="page-title">Profit & Loss Statement</h1>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="<?= base_url('reports/financial') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="<?= base_url('reports/profit-loss') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Start Date</label>
                    <input type="date" name="start_date" value="<?= $startDate ?? date('Y-m-01') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">End Date</label>
                    <input type="date" name="end_date" value="<?= $endDate ?? date('Y-m-d') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-filter"></i> Apply</button>
                </div>
            </form>
        </div>
    </div>

    <!-- P&L Summary -->
    <div class="card-container">
        <div class="card">
            <h3>Total Revenue</h3>
            <div class="value">₱<?= number_format($totalRevenue ?? 0, 2) ?></div>
        </div>
        <div class="card">
            <h3>Total Expenses</h3>
            <div class="value">₱<?= number_format($totalExpenses ?? 0, 2) ?></div>
        </div>
        <div class="card" style="border-left: 4px solid <?= ($netProfit ?? 0) >= 0 ? '#10b981' : '#ef4444' ?>;">
            <h3>Net <?= ($netProfit ?? 0) >= 0 ? 'Profit' : 'Loss' ?></h3>
            <div class="value" style="color: <?= ($netProfit ?? 0) >= 0 ? '#10b981' : '#ef4444' ?>;">₱<?= number_format(abs($netProfit ?? 0), 2) ?></div>
        </div>
        <div class="card">
            <h3>Profit Margin</h3>
            <div class="value"><?= number_format($profitMargin ?? 0, 2) ?>%</div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

