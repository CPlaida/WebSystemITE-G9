<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Nurse Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome, Nurse</h2>
        <p>Patient Care & Monitoring Overview</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card urgent">
                <div class="stat-title">Critical Patients</div>
                <div class="stat-value">3</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Patients Under Care</div>
                <div class="stat-value">24</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Medications Due</div>
                <div class="stat-value">8</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Vitals Pending</div>
                <div class="stat-value">12</div>
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
