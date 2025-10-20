<?php $this->extend('partials/header') ?>

<div class="dashboard-summary">
    <div class="mini-card">
        <div class="">Today's Appointments</div>
        <div class="mini-value"><?= $appointmentsCount ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Total Patients</div>
        <div class="mini-value"><?= $patientsCount ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Active Cases</div>
        <div class="mini-value"><?= $activeCases ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">New Patients Today</div>
        <div class="mini-value"><?= $newPatientsToday ?? '0' ?></div>
    </div>

    <div class="composite-card billing-card">
        <div class="composite-header">
            <div class="composite-title">Billing & Payments</div>
        </div>
        <div class="metric-grid">
            <div class="metric-item">
                <div class="metric-title">Today's Revenue</div>
                <div class="metric-value">₱<?= number_format($todayRevenue ?? 0, 2) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Paid This Month</div>
                <div class="metric-value">₱<?= number_format($paidThisMonth ?? 0, 2) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Outstanding</div>
                <div class="metric-value">₱<?= number_format($outstanding ?? 0, 2) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-title">Pending Bills</div>
                <div class="metric-value"><?= $pendingBills ?? '0' ?></div>
            </div>
        </div>
    </div>

    <div class="mini-card">
        <div class="mini-title">Pending Prescriptions</div>
        <div class="mini-value"><?= $prescriptionsCount ?? '0' ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Pending Lab Tests</div>
        <div class="mini-value"><?= ($labStats['pending'] ?? null) !== null ? $labStats['pending'] : ($labTestsCount ?? 0) ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">Users Total</div>
        <div class="mini-value"><?= isset($userCounts) ? array_sum($userCounts) : 0 ?></div>
    </div>
    
    <div class="mini-card">
        <div class="mini-title">System Status</div>
        <div class="mini-value">
            <span class="status-badge status-online">
                <span class="status-dot"></span>
                <?= $systemStatus ?? 'Online' ?>
            </span>
        </div>
    </div>
</div>