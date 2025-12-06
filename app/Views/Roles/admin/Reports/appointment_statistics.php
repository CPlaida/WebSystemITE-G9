<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Appointment Statistics Report<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Appointment Statistics Report</h1>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="<?= base_url('reports') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="<?= base_url('reports/appointment-statistics') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Start Date</label>
                    <input type="date" name="start_date" value="<?= $filters['start_date'] ?? date('Y-m-01') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">End Date</label>
                    <input type="date" name="end_date" value="<?= $filters['end_date'] ?? date('Y-m-d') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Status</label>
                    <select name="status" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">All Status</option>
                        <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-filter"></i> Apply</button>
                    <a href="<?= base_url('reports/appointment-statistics') ?>" class="btn btn-secondary" style="width: 100%; margin-top: 5px;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <?php if (!empty($reportData['statistics'])): ?>
    <?php $stats = $reportData['statistics']; ?>
    <div class="card-container">
        <div class="card">
            <h3>Total Appointments</h3>
            <div class="value"><?= $stats['total'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>Completed</h3>
            <div class="value"><?= $stats['by_status']['completed'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>Cancelled</h3>
            <div class="value"><?= $stats['by_status']['cancelled'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>No-Show Rate</h3>
            <div class="value"><?= number_format($stats['no_show_rate'] ?? 0, 2) ?>%</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Table -->
    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData['appointments'])): ?>
                            <?php foreach ($reportData['appointments'] as $apt): ?>
                                <tr>
                                    <td><?= $apt['id'] ?? 'N/A' ?></td>
                                    <td><?= ($apt['patient_first_name'] ?? '') . ' ' . ($apt['patient_last_name'] ?? '') ?></td>
                                    <td><?= $apt['doctor_name'] ?? 'N/A' ?></td>
                                    <td><?= date('M d, Y', strtotime($apt['appointment_date'] ?? date('Y-m-d'))) ?></td>
                                    <td><?= date('g:i A', strtotime($apt['appointment_time'] ?? '00:00:00')) ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $apt['appointment_type'] ?? 'N/A')) ?></td>
                                    <td>
                                        <?php $status = strtolower($apt['status'] ?? 'scheduled'); ?>
                                        <span class="<?= $status === 'completed' ? 'status-paid' : ($status === 'cancelled' ? 'status-pending' : 'status-pending') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $apt['status'] ?? 'Scheduled')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No appointment data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

