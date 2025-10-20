<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>
    Accountant Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Accountant Dashboard</h1>
        <p class="dashboard-subtitle">Financial Management & Billing</p>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Today's Revenue</h3>
                <div class="card-value">₱<?= number_format($todayRevenue, 2) ?></div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Pending Bills</h3>
                <div class="card-value"><?= is_array($pendingBills) ? count($pendingBills) : (int)$pendingBills ?></div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Insurance Claims</h3>
                <div class="card-value"><?= is_array($insuranceClaims) ? count($insuranceClaims) : (int)$insuranceClaims ?></div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Outstanding Balance</h3>
                <div class="card-value">₱<?= number_format($outstandingBalance, 2) ?></div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
