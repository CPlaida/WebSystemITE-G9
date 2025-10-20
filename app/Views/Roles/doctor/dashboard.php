<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Doctor Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome back, Dr. <?= esc($name ?? 'Doctor') ?></h2>
        <p>Here's what's happening with your patients today</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Today's Appointments</div>
                <div class="stat-value"><?= $appointmentsCount ?? '0' ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Patients Seen</div>
                <div class="stat-value"><?= $patientsSeenToday ?? '0' ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Pending Results</div>
                <div class="stat-value"><?= $pendingLabResults ?? '0' ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Prescriptions</div>
                <div class="stat-value"><?= $prescriptionsCount ?? '0' ?></div>
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
