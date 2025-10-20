<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>
    Laboratory Staff Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Laboratory Staff Dashboard</h1>
        <p class="dashboard-subtitle">Laboratory Test Management</p>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Pending Tests</h3>
                <div class="card-value"><?= is_array($pendingTests) ? count($pendingTests) : (int)$pendingTests ?></div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Completed Today</h3>
                <div class="card-value"><?= is_array($completedToday) ? count($completedToday) : (int)$completedToday ?></div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Total Tests This Month</h3>
                <div class="card-value"><?= is_array($monthlyTests) ? count($monthlyTests) : (int)$monthlyTests ?></div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
