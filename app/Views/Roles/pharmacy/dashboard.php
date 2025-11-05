<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>
Pharmacy Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3">
                <div class="kpi-title">Prescriptions Today</div>
                <div class="kpi-value"><?= $prescriptionsToday ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Pending Fulfillment</div>
                <div class="kpi-value"><?= $pendingFulfillment ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3 text-warning">
                <div class="kpi-title">Low Stock Items</div>
                <div class="kpi-value"><?= $lowStockItems ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Total Inventory</div>
                <div class="kpi-value"><?= $totalInventory ?? '0' ?></div>
            </div>
        </div>
    </div>

    <div class="composite-card inventory-card">
        <div class="composite-header">
            <div class="composite-title">Inventory Status</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3 text-danger">
                <div class="kpi-title">Critical Items</div>
                <div class="kpi-value"><?= $criticalItems ?? '0' ?></div>
            </div>
            <div class="kpi-card span-3 text-warning">
                <div class="kpi-title">Expiring Soon</div>
                <div class="kpi-value"><?= $expiringSoon ?? '0' ?></div>
            </div>
            <div class="kpi-card span-3">
                <div class="kpi-title">Out of Stock</div>
                <div class="kpi-value"><?= $outOfStock ?? '0' ?></div>
            </div>
            <div class="kpi-card span-3">
                <div class="kpi-title">Categories</div>
                <div class="kpi-value"><?= $categoriesCount ?? '0' ?></div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
