<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Statistics Report<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Patient Statistics Report</h1>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="<?= base_url('reports') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="<?= base_url('reports/patient-statistics') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Start Date</label>
                    <input type="date" name="start_date" value="<?= $filters['start_date'] ?? '' ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">End Date</label>
                    <input type="date" name="end_date" value="<?= $filters['end_date'] ?? date('Y-m-d') ?>" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Patient Type</label>
                    <select name="patient_type" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">All Types</option>
                        <option value="inpatient" <?= ($filters['patient_type'] ?? '') === 'inpatient' ? 'selected' : '' ?>>Inpatient</option>
                        <option value="outpatient" <?= ($filters['patient_type'] ?? '') === 'outpatient' ? 'selected' : '' ?>>Outpatient</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Gender</label>
                    <select name="gender" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">All</option>
                        <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-filter"></i> Apply</button>
                    <a href="<?= base_url('reports/patient-statistics') ?>" class="btn btn-secondary" style="width: 100%; margin-top: 5px;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($reportData['statistics'])): ?>
    <?php $stats = $reportData['statistics']; ?>
    <div class="card-container">
        <div class="card">
            <h3>Total Patients</h3>
            <div class="value"><?= $stats['total_patients'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>Inpatients</h3>
            <div class="value"><?= $stats['inpatient'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>Outpatients</h3>
            <div class="value"><?= $stats['outpatient'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>Active Patients</h3>
            <div class="value"><?= $stats['active_patients'] ?? 0 ?></div>
        </div>
        <div class="card">
            <h3>New Patients</h3>
            <div class="value"><?= $stats['new_patients'] ?? 0 ?></div>
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
                            <th>Patient ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData['patients'])): ?>
                            <?php foreach ($reportData['patients'] as $patient): ?>
                                <tr>
                                    <td><?= $patient['id'] ?? 'N/A' ?></td>
                                    <td><?= ($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') ?></td>
                                    <td><?= ucfirst($patient['type'] ?? 'N/A') ?></td>
                                    <td><?= ucfirst($patient['gender'] ?? 'N/A') ?></td>
                                    <td><?= !empty($patient['date_of_birth']) ? date('M d, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></td>
                                    <td>
                                        <?php $status = strtolower($patient['status'] ?? 'active'); ?>
                                        <span class="<?= $status === 'active' ? 'status-paid' : 'status-pending' ?>">
                                            <?= ucfirst($patient['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No patient data available for the selected filters</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

