<?php $this->extend('partials/header') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3">
                <div class="kpi-title">Today's Appointments</div>
                <div class="kpi-value">24</div>
                <div class="kpi-subtext">+3 from yesterday</div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Waiting Patients</div>
                <div class="kpi-value">8</div>
                <div class="kpi-subtext">In queue</div>
            </div>

            <div class="kpi-card span-3">
                <div class="kpi-title">New Registrations</div>
                <div class="kpi-value">5</div>
                <div class="kpi-subtext">Today</div>
            </div>

            <div class="kpi-card span-3">
                <div class="kpi-title">Pending Payments</div>
                <div class="kpi-value">₱12,500</div>
                <div class="kpi-subtext">3 invoices</div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>