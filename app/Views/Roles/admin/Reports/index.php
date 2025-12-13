<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Reports<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Reports</h1>
        </div>
        <div class="card-body">
    <!-- Report Filters Section -->
    <div class="card" style="margin-bottom: 20px; box-shadow: none; border: none;">
        <div class="card-body">
            <h2 style="margin-bottom: 24px; font-size: 18px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Report Parameters</h2>
            <form method="GET" action="<?= base_url('reports') ?>" id="reportForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; align-items: end;">
                    <div style="display: flex; flex-direction: column;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Report Type <span style="color: #ef4444;">*</span></label>
                        <select name="type" id="reportType" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;" onchange="document.getElementById('reportForm').submit();">
                            <?php 
                            $allowedReports = $allowedReports ?? [];
                            $reportNames = $reportNames ?? [];
                            
                            if (empty($allowedReports)): ?>
                                <option value="">No reports available for your role</option>
                            <?php else: ?>
                                <?php foreach ($allowedReports as $reportKey): ?>
                                    <?php if (isset($reportNames[$reportKey])): ?>
                                        <option value="<?= esc($reportKey) ?>" <?= ($reportType ?? '') === $reportKey ? 'selected' : '' ?>>
                                            <?= esc($reportNames[$reportKey]) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Dynamic Filters based on report type -->
                    <?php if (in_array($reportType ?? 'revenue', ['revenue', 'outstanding-payments', 'patient-statistics', 'appointment-statistics', 'laboratory-tests', 'prescriptions', 'medicine-sales', 'admissions', 'discharges', 'doctor-performance', 'philhealth-claims', 'hmo-claims'])): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Start Date <span style="color: #ef4444;">*</span></label>
                            <input type="date" name="start_date" value="<?= $filters['start_date'] ?? date('Y-m-01') ?>" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">End Date <span style="color: #ef4444;">*</span></label>
                            <input type="date" name="end_date" value="<?= $filters['end_date'] ?? date('Y-m-d') ?>" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($reportType ?? '') === 'revenue'): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Payment Method</label>
                            <select name="payment_method" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All Methods</option>
                                <option value="cash" <?= ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="insurance" <?= ($filters['payment_method'] ?? '') === 'insurance' ? 'selected' : '' ?>>Insurance</option>
                                <option value="hmo" <?= ($filters['payment_method'] ?? '') === 'hmo' ? 'selected' : '' ?>>HMO</option>
                            </select>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Payment Status</label>
                            <select name="payment_status" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All Status</option>
                                <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($reportType ?? '') === 'outstanding-payments'): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Payment Status</label>
                            <select name="payment_status" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All Status</option>
                                <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                                <option value="overdue" <?= ($filters['payment_status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($reportType ?? '') === 'patient-statistics'): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Patient Type</label>
                            <select name="patient_type" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All Types</option>
                                <option value="inpatient" <?= ($filters['patient_type'] ?? '') === 'inpatient' ? 'selected' : '' ?>>Inpatient</option>
                                <option value="outpatient" <?= ($filters['patient_type'] ?? '') === 'outpatient' ? 'selected' : '' ?>>Outpatient</option>
                            </select>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Gender</label>
                            <select name="gender" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All</option>
                                <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($reportType ?? '') === 'appointment-statistics'): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Status</label>
                            <select name="status" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All Status</option>
                                <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($reportType ?? '') === 'medicine-inventory'): ?>
                        <div style="display: flex; flex-direction: column;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px; letter-spacing: 0.3px;">Stock Status</label>
                            <select name="stock_status" onchange="document.getElementById('reportForm').submit();" style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #374151; transition: border-color 0.2s; font-family: inherit;">
                                <option value="">All</option>
                                <option value="low_stock" <?= ($filters['stock_status'] ?? '') === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                                <option value="out_of_stock" <?= ($filters['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content -->
    <div id="reportContent">
        <?php
        $reportNames = $reportNames ?? [];
        $currentReportTitle = isset($reportType) && isset($reportNames[$reportType]) 
            ? $reportNames[$reportType] 
            : 'Report';
        
        // Show message if no reports available
        if (empty($allowedReports ?? [])): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="alert alert-info" style="margin: 0; padding: 16px; background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 6px; color: #1565c0;">
                        <strong>No Reports Available</strong><br>
                        You do not have permission to access any reports. Please contact your administrator.
                    </div>
                </div>
            </div>
        <?php elseif (empty($reportType)): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="alert alert-info" style="margin: 0; padding: 16px; background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 6px; color: #1565c0;">
                        <strong>Select a Report</strong><br>
                        Please select a report type from the dropdown above to view report data.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Report Header -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div>
                        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 600; color: #2c3e50;"><?= esc($currentReportTitle) ?></h2>
                        <?php 
                        $startDate = $filters['start_date'] ?? date('Y-m-01');
                        $endDate = $filters['end_date'] ?? date('Y-m-d');
                        ?>
                        <p style="margin: 0; font-size: 14px; color: #7f8c8d;">Period: <?= date('F d, Y', strtotime($startDate)) ?> - <?= date('F d, Y', strtotime($endDate)) ?></p>
                    </div>
                </div>
            </div>

            <?php if (($reportType ?? '') === 'revenue'): ?>
            <!-- Revenue Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(2, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Revenue</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_revenue'] ?? 0, 2) ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Bills</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_bills'] ?? 0 ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Transaction Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bill ID</th>
                                    <th>Patient Name</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['bills'])): ?>
                                    <?php foreach ($reportData['bills'] as $bill): ?>
                                        <tr>
                                            <td>#<?= $bill['id'] ?? 'N/A' ?></td>
                                            <td><?= esc($bill['patient_name'] ?? 'N/A') ?></td>
                                            <td><?= date('M d, Y', strtotime($bill['bill_date'] ?? date('Y-m-d'))) ?></td>
                                            <td><?= ucfirst($bill['payment_method'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php $ps = strtolower($bill['payment_status'] ?? 'pending'); ?>
                                                <span class="<?= $ps === 'paid' ? 'status-paid' : 'status-pending' ?>">
                                                    <?= ucfirst($bill['payment_status'] ?? 'Pending') ?>
                                                </span>
                                            </td>
                                            <td style="font-weight: 600;">₱<?= number_format($bill['final_amount'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No transaction data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'outstanding-payments'): ?>
            <!-- Outstanding Payments Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(2, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Outstanding</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_outstanding'] ?? 0, 2) ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Bills</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_bills'] ?? 0 ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Outstanding Bills</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bill ID</th>
                                    <th>Patient Name</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['bills'])): ?>
                                    <?php foreach ($reportData['bills'] as $bill): ?>
                                        <tr>
                                            <td>#<?= $bill['id'] ?? 'N/A' ?></td>
                                            <td><?= esc($bill['patient_name'] ?? 'N/A') ?></td>
                                            <td><?= date('M d, Y', strtotime($bill['bill_date'] ?? date('Y-m-d'))) ?></td>
                                            <td>
                                                <?php $ps = strtolower($bill['payment_status'] ?? 'pending'); ?>
                                                <span class="<?= $ps === 'paid' ? 'status-paid' : 'status-pending' ?>">
                                                    <?= ucfirst($bill['payment_status'] ?? 'Pending') ?>
                                                </span>
                                            </td>
                                            <td style="font-weight: 600;">₱<?= number_format($bill['final_amount'] ?? 0, 2) ?></td>
                                            <td><?= $bill['days_overdue'] ?? 0 ?> days</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No outstanding payments found for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'patient-statistics'): ?>
            <!-- Patient Statistics Report -->
            <?php if (!empty($reportData['statistics'])): ?>
            <?php $stats = $reportData['statistics']; ?>
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(5, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Patients</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['total_patients'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Inpatients</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['inpatient'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Outpatients</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['outpatient'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Active Patients</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['active_patients'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">New Patients</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700; color: #4361ee;"><?= $stats['new_patients'] ?? 0 ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Patient Details</h3>
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
                                            <td><?= esc(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?></td>
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
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No patient data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'appointment-statistics'): ?>
            <!-- Appointment Statistics Report -->
            <?php if (!empty($reportData['statistics'])): ?>
            <?php $stats = $reportData['statistics']; ?>
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(3, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Appointments</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['total'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Completed</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['by_status']['completed'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Cancelled</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['by_status']['cancelled'] ?? 0 ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Appointment Details</h3>
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
                                            <td><?= esc(($apt['patient_first_name'] ?? '') . ' ' . ($apt['patient_last_name'] ?? '')) ?></td>
                                            <td><?= esc($apt['doctor_name'] ?? 'N/A') ?></td>
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
                                        <td colspan="7" style="text-align: center; padding: 40px; color: #7f8c8d;">No appointment data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'laboratory-tests'): ?>
            <!-- Laboratory Tests Report -->
            <?php if (!empty($reportData['statistics'])): ?>
            <?php $stats = $reportData['statistics']; ?>
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(3, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Tests</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['total_tests'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Completed</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['by_status']['completed'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Pending</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700; color: #f8961e;"><?= $stats['by_status']['pending'] ?? 0 ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Test Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Test ID</th>
                                    <th>Test Name</th>
                                    <th>Test Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['tests'])): ?>
                                    <?php foreach ($reportData['tests'] as $test): ?>
                                        <tr>
                                            <td><?= $test['id'] ?? 'N/A' ?></td>
                                            <td><?= esc($test['test_name'] ?? 'N/A') ?></td>
                                            <td><?= esc($test['test_type'] ?? 'N/A') ?></td>
                                            <td><?= date('M d, Y', strtotime($test['test_date'] ?? date('Y-m-d'))) ?></td>
                                            <td>
                                                <?php $status = strtolower($test['status'] ?? 'pending'); ?>
                                                <span class="<?= $status === 'completed' ? 'status-paid' : 'status-pending' ?>">
                                                    <?= ucfirst($test['status'] ?? 'Pending') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">No test data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'prescriptions'): ?>
            <!-- Prescription Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(1, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Prescriptions</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_prescriptions'] ?? 0 ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Prescription Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table prescription-report-table">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Prescription ID</th>
                                    <th style="text-align: center;">Date</th>
                                    <th style="text-align: center;">Medicines</th>
                                    <th style="text-align: center;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['prescriptions'])): ?>
                                    <?php foreach ($reportData['prescriptions'] as $pres): ?>
                                        <tr>
                                            <td style="text-align: center;">#<?= $pres['id'] ?? 'N/A' ?></td>
                                            <td style="text-align: center;"><?= date('M d, Y', strtotime($pres['date'] ?? $pres['created_at'] ?? date('Y-m-d'))) ?></td>
                                            <td style="text-align: center;">
                                                <?php 
                                                // Debug: Check what we have
                                                $hasItems = !empty($pres['items']) && is_array($pres['items']);
                                                $itemsCount = $hasItems ? count($pres['items']) : 0;
                                                ?>
                                                <?php if ($hasItems && $itemsCount > 0): ?>
                                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: center; text-align: center;">
                                                        <?php foreach ($pres['items'] as $item): ?>
                                                            <?php 
                                                            $medicineName = $item['medicine_name'] ?? null;
                                                            $medicationId = $item['medication_id'] ?? '';
                                                            $displayName = !empty($medicineName) ? $medicineName : (!empty($medicationId) ? 'Medicine ID: ' . esc($medicationId) : 'Unknown Medicine');
                                                            ?>
                                                            <div style="font-size: 13px; text-align: center;">
                                                                <strong><?= esc($displayName) ?></strong>
                                                                <?php if (!empty($item['quantity'])): ?>
                                                                    <span style="color: #7f8c8d;">(Qty: <?= $item['quantity'] ?>)</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php elseif (!empty($pres['medicines']) && is_array($pres['medicines']) && count($pres['medicines']) > 0): ?>
                                                    <?= implode(', ', array_map('esc', $pres['medicines'])) ?>
                                                <?php else: ?>
                                                    <span style="color: #7f8c8d;">No medicines found</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center; font-weight: 600;">₱<?= number_format($pres['total_amount'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 40px; color: #7f8c8d;">No prescription data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'medicine-inventory'): ?>
            <!-- Medicine Inventory Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(4, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Medicines</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_medicines'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Stock Value</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_stock_value'] ?? 0, 2) ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Low Stock</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700; color: #f8961e;"><?= count($reportData['low_stock'] ?? []) ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Out of Stock</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= count($reportData['out_of_stock'] ?? []) ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Inventory Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Medicine ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Unit Price</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['medicines'])): ?>
                                    <?php foreach ($reportData['medicines'] as $med): ?>
                                        <tr>
                                            <td><?= $med['id'] ?? 'N/A' ?></td>
                                            <td><?= esc($med['name'] ?? 'N/A') ?></td>
                                            <td><?= esc($med['category'] ?? 'N/A') ?></td>
                                            <td><?= $med['stock'] ?? 0 ?></td>
                                            <td>₱<?= number_format($med['unit_price'] ?? $med['retail_price'] ?? 0, 2) ?></td>
                                            <td><?= !empty($med['expiry_date']) ? date('M d, Y', strtotime($med['expiry_date'])) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No medicine data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'medicine-sales'): ?>
            <!-- Medicine Sales Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(2, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Sales</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_sales'] ?? 0, 2) ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Transactions</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_transactions'] ?? 0 ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Sales Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Medicine</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['transactions'])): ?>
                                    <?php foreach ($reportData['transactions'] as $trans): ?>
                                        <tr>
                                            <td>#<?= $trans['id'] ?? 'N/A' ?></td>
                                            <td><?= esc($trans['medicine_name'] ?? 'N/A') ?></td>
                                            <td><?= date('M d, Y', strtotime($trans['created_at'] ?? date('Y-m-d'))) ?></td>
                                            <td style="font-weight: 600;">₱<?= number_format($trans['total_amount'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 40px; color: #7f8c8d;">No sales data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'admissions'): ?>
            <!-- Admission Report -->
            <?php if (!empty($reportData['statistics'])): ?>
            <?php $stats = $reportData['statistics']; ?>
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(1, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Admissions</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $stats['total_admissions'] ?? 0 ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Admission Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Patient ID</th>
                                    <th>Admission Date</th>
                                    <th>Ward</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['admissions'])): ?>
                                    <?php foreach ($reportData['admissions'] as $adm): ?>
                                        <tr>
                                            <td>#<?= $adm['id'] ?? 'N/A' ?></td>
                                            <td><?= $adm['patient_id'] ?? 'N/A' ?></td>
                                            <td><?= date('M d, Y', strtotime($adm['admission_date'] ?? date('Y-m-d'))) ?></td>
                                            <td><?= esc($adm['ward'] ?? 'N/A') ?></td>
                                            <td><?= esc($adm['room'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php $status = strtolower($adm['status'] ?? 'admitted'); ?>
                                                <span class="<?= $status === 'admitted' ? 'status-paid' : 'status-pending' ?>">
                                                    <?= ucfirst($adm['status'] ?? 'Admitted') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No admission data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'discharges'): ?>
            <!-- Discharge Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(1, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Discharges</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_discharges'] ?? 0 ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Discharge Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Patient ID</th>
                                    <th>Admission Date</th>
                                    <th>Discharge Date</th>
                                    <th>Length of Stay</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['discharges'])): ?>
                                    <?php foreach ($reportData['discharges'] as $dis): ?>
                                        <tr>
                                            <td>#<?= $dis['id'] ?? 'N/A' ?></td>
                                            <td><?= $dis['patient_id'] ?? 'N/A' ?></td>
                                            <td><?= date('M d, Y', strtotime($dis['admission_date'] ?? date('Y-m-d'))) ?></td>
                                            <td><?= !empty($dis['updated_at']) ? date('M d, Y', strtotime($dis['updated_at'])) : 'N/A' ?></td>
                                            <td>
                                                <?php 
                                                if (!empty($dis['admission_date'])) {
                                                    $admit = new \DateTime($dis['admission_date']);
                                                    $discharge = !empty($dis['updated_at']) ? new \DateTime($dis['updated_at']) : new \DateTime();
                                                    $days = $admit->diff($discharge)->days;
                                                    echo $days . ' days';
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">No discharge data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'doctor-performance'): ?>
            <!-- Doctor Performance Report -->
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Performance Metrics</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Appointments</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['performance'])): ?>
                                    <?php foreach ($reportData['performance'] as $perf): ?>
                                        <tr>
                                            <td><?= esc($perf['doctor_name'] ?? 'N/A') ?></td>
                                            <td><?= $perf['appointments'] ?? 0 ?></td>
                                            <td style="font-weight: 600;">₱<?= number_format($perf['revenue'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 40px; color: #7f8c8d;">No performance data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'philhealth-claims'): ?>
            <!-- PhilHealth Claims Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(2, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Claims</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_claims'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Approved Amount</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_approved_amount'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Claims Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Bill ID</th>
                                    <th>Patient ID</th>
                                    <th>Approved Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['claims'])): ?>
                                    <?php foreach ($reportData['claims'] as $claim): ?>
                                        <tr>
                                            <td>#<?= $claim['id'] ?? 'N/A' ?></td>
                                            <td>#<?= $claim['bill_id'] ?? 'N/A' ?></td>
                                            <td><?= $claim['patient_id'] ?? 'N/A' ?></td>
                                            <td style="font-weight: 600;">₱<?= number_format($claim['approved_amount'] ?? 0, 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($claim['created_at'] ?? date('Y-m-d'))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">No claims data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif (($reportType ?? '') === 'hmo-claims'): ?>
            <!-- HMO Claims Report -->
            <div class="card-container" style="margin-bottom: 20px; grid-template-columns: repeat(2, 1fr);">
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Claims</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;"><?= $reportData['total_claims'] ?? 0 ?></div>
                </div>
                <div class="card" style="padding: 35px 30px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Total Approved Amount</h3>
                    <div class="value" style="font-size: 42px; font-weight: 700;">₱<?= number_format($reportData['total_approved_amount'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 16px; font-size: 16px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">Claims Details</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Authorization ID</th>
                                    <th>Billing ID</th>
                                    <th>Patient ID</th>
                                    <th>Provider</th>
                                    <th>Approved Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['claims'])): ?>
                                    <?php foreach ($reportData['claims'] as $claim): ?>
                                        <tr>
                                            <td>#<?= $claim['id'] ?? 'N/A' ?></td>
                                            <td>#<?= $claim['billing_id'] ?? 'N/A' ?></td>
                                            <td><?= $claim['patient_id'] ?? 'N/A' ?></td>
                                            <td><?= esc($claim['provider_id'] ?? 'N/A') ?></td>
                                            <td style="font-weight: 600;">₱<?= number_format($claim['approved_amount'] ?? 0, 2) ?></td>
                                            <td>
                                                <?php $status = strtolower($claim['status'] ?? 'pending'); ?>
                                                <span class="<?= $status === 'approved' ? 'status-paid' : 'status-pending' ?>">
                                                    <?= ucfirst($claim['status'] ?? 'Pending') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">No claims data available for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Placeholder for other report types - keeping structure consistent -->
            <div class="card">
                <div class="card-body">
                    <p style="text-align: center; padding: 40px; color: #7f8c8d; font-size: 16px;">Please select a report type from the dropdown above to generate a report.</p>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Formal styling for form inputs */
    #reportForm select:hover,
    #reportForm input[type="date"]:hover {
        border-color: #9ca3af;
    }
    
    #reportForm select:focus,
    #reportForm input[type="date"]:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    #reportForm select option {
        padding: 8px;
    }
</style>

<?= $this->endSection() ?>

