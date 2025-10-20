<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>
Pharmacy Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-summary">
    <div class="mini-card">
        <div class="mini-title">Prescriptions Today</div>
        <div class="mini-value"><?= $prescriptionsToday ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Pending Fulfillment</div>
        <div class="mini-value"><?= $pendingFulfillment ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Low Stock Items</div>
        <div class="mini-value"><?= $lowStockItems ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Total Inventory</div>
        <div class="mini-value"><?= $totalInventory ?? '0' ?></div>
    </div>

    <div class="composite-card inventory-card">
        <div class="composite-header">
            <div class="composite-title">Inventory Status</div>
            <a href="/pharmacy/inventory" class="btn btn-sm">View All</a>
        </div>
        <div class="metric-grid">
            <div class="metric-item">
                <div class="metric-title">Critical Items</div>
                <div class="metric-value text-danger"><?= $criticalItems ?? '0' ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Expiring Soon</div>
                <div class="metric-value text-warning"><?= $expiringSoon ?? '0' ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Out of Stock</div>
                <div class="metric-value"><?= $outOfStock ?? '0' ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Categories</div>
                <div class="metric-value"><?= $categoriesCount ?? '0' ?></div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
