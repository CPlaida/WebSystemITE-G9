<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Doctor Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
        <div class="kpi-card span-3">
            <div class="kpi-title">Today's Appointments</div>
            <div class="kpi-value"><?= $appointmentsCount ?? '0' ?></div>
        </div>
        
        <div class="kpi-card span-3">
            <div class="kpi-title">Patients Seen</div>
            <div class="kpi-value"><?= $patientsSeenToday ?? '0' ?></div>
        </div>
        
        <div class="kpi-card span-3">
            <div class="kpi-title">Pending Results</div>
            <div class="kpi-value"><?= $pendingLabResults ?? '0' ?></div>
        </div>
        
        <div class="kpi-card span-3">
            <div class="kpi-title">Prescriptions</div>
            <div class="kpi-value"><?= $prescriptionsCount ?? '0' ?></div>
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
