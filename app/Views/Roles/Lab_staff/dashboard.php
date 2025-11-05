<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>
    Laboratory Staff Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-4">
                <div class="kpi-title">Pending Tests</div>
                <div class="kpi-value"><?= is_array($pendingTests) ? count($pendingTests) : (int)$pendingTests ?></div>
            </div>
            
            <div class="kpi-card span-4">
                <div class="kpi-title">Completed Today</div>
                <div class="kpi-value"><?= is_array($completedToday) ? count($completedToday) : (int)$completedToday ?></div>
            </div>
            
            <div class="kpi-card span-4">
                <div class="kpi-title">Total Tests This Month</div>
                <div class="kpi-value"><?= is_array($monthlyTests) ? count($monthlyTests) : (int)$monthlyTests ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
