<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Accountant Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome back, <?= esc($name ?? 'Accountant') ?></h2>
        <p>Here's the financial overview for today</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Today's Revenue</div>
                <div class="stat-value">₱<?= number_format($todayRevenue ?? 0, 2) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Pending Bills</div>
                <div class="stat-value"><?= is_array($pendingBills) ? count($pendingBills) : (int)($pendingBills ?? 0) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Insurance Claims</div>
                <div class="stat-value"><?= is_array($insuranceClaims) ? count($insuranceClaims) : (int)($insuranceClaims ?? 0) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Outstanding Balance</div>
                <div class="stat-value">₱<?= number_format($outstandingBalance ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?= $this->endSection() ?>
