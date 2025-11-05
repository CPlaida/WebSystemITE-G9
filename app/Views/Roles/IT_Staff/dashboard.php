<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>IT Staff Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3">
                <div class="kpi-title">System Uptime</div>
                <div class="kpi-value">99.8%</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Active Users</div>
                <div class="kpi-value">156</div>
            </div>
            
            <div class="kpi-card span-3 text-warning">
                <div class="kpi-title">System Alerts</div>
                <div class="kpi-value">3</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Pending Tasks</div>
                <div class="kpi-value">5</div>
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
