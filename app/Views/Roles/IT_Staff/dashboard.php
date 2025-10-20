<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>IT Staff Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome, IT Staff</h2>
        <p>System Administration & Security Overview</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">System Uptime</div>
                <div class="stat-value">99.8%</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Active Users</div>
                <div class="stat-value">156</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">System Alerts</div>
                <div class="stat-value">3</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Pending Tasks</div>
                <div class="stat-value">5</div>
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
