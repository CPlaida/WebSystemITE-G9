<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Accountant Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Financial Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3">
                <div class="kpi-title">Today's Revenue</div>
                <div class="kpi-value">₱<?= number_format($todayRevenue ?? 0, 2) ?></div>
                <div class="kpi-subtext">From all services</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Pending Bills</div>
                <div class="kpi-value"><?= is_array($pendingBills) ? count($pendingBills) : (int)($pendingBills ?? 0) ?></div>
                <div class="kpi-subtext">Requiring attention</div>
            </div>

            <div class="kpi-card span-3">
                <div class="kpi-title">Insurance Claims</div>
                <div class="kpi-value"><?= is_array($insuranceClaims) ? count($insuranceClaims) : (int)($insuranceClaims ?? 0) ?></div>
                <div class="kpi-subtext">To process</div>
            </div>

            <div class="kpi-card span-3">
                <div class="kpi-title">Outstanding Balance</div>
                <div class="kpi-value">₱<?= number_format($outstandingBalance ?? 0, 2) ?></div>
                <div class="kpi-subtext">Total receivables</div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
