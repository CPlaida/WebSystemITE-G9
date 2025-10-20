<?php $this->extend('partials/header') ?>

<?= $this->section('content') ?>
<div class="dashboard-summary">
    <div class="mini-card">
        <div class="mini-title">Today's Appointments</div>
        <div class="mini-value">24</div>
        <div class="mini-subtext">+3 from yesterday</div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Waiting Patients</div>
        <div class="mini-value">8</div>
        <div class="mini-subtext">In queue</div>
    </div>

    <div class="mini-card">
        <div class="mini-title">New Registrations</div>
        <div class="mini-value">5</div>
        <div class="mini-subtext">Today</div>
    </div>

    <div class="mini-card">
        <div class="mini-title">Pending Payments</div>
        <div class="mini-value">₱12,500</div>
        <div class="mini-subtext">3 invoices</div>
    </div>
</div>
<?= $this->endSection() ?>