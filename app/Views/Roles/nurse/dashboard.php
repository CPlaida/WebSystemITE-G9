<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Nurse Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3 urgent">
                <div class="kpi-title">Critical Patients</div>
                <div class="kpi-value">3</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Patients Under Care</div>
                <div class="kpi-value">24</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Medications Due</div>
                <div class="kpi-value">8</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Vitals Pending</div>
                <div class="kpi-value">12</div>
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
