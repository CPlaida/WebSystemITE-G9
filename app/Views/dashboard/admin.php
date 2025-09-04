<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Admin Dashboard
<?= $this->endSection() ?>

<?php $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin Dashboard</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Quick Action
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Patients</h6>
                            <h3 class="mb-0">1,248</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-hospital-user text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-success"><i class="fas fa-arrow-up"></i> 12.5%</span>
                        <span class="text-muted ms-2">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Today's Appointments</h6>
                            <h3 class="mb-0">24</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-check text-warning"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> 2.5%</span>
                        <span class="text-muted ms-2">vs yesterday</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Revenue</h6>
                            <h3 class="mb-0">₱124,890</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave text-success"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-success"><i class="fas fa-arrow-up"></i> 8.2%</span>
                        <span class="text-muted ms-2">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Staff</h6>
                            <h3 class="mb-0">18</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-md text-info"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-success"><i class="fas fa-arrow-up"></i> 2.1%</span>
                        <span class="text-muted ms-2">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Recent Appointments</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="Patient" width="32">
                                            <div>
                                                <h6 class="mb-0">Juan Dela Cruz</h6>
                                                <small class="text-muted">#PAT-001</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Dr. Maria Santos</td>
                                    <td>10:00 AM</td>
                                    <td><span class="badge bg-success">Confirmed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                                <!-- More rows can be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('patients/register') ?>" class="btn btn-outline-primary text-start">
                            <i class="fas fa-user-plus me-2"></i> Register New Patient
                        </a>
                        <a href="<?= site_url('appointments/book') ?>" class="btn btn-outline-primary text-start">
                            <i class="fas fa-calendar-plus me-2"></i> Book Appointment
                        </a>
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Create Invoice
                        </a>
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="fas fa-pills me-2"></i> Manage Inventory
                        </a>
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="fas fa-chart-line me-2"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
            <li><a href="<?= site_url('patients/active') ?>"><i class="fas fa-user-check"></i> Active Patients</a></li>
            <li><a href="<?= site_url('patients/records') ?>"><i class="fas fa-notes-medical"></i> Medical Records</a></li>
        </ul>
    </li>

    <!-- Appointments -->
    <li class="nav-item expandable">
        <a href="#" class="nav-link">
            <i class="fas fa-calendar-check"></i> 
            <span>Appointments</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?= site_url('appointments/book') ?>"><i class="fas fa-plus-circle"></i> New Appointment</a></li>
            <li><a href="<?= site_url('appointments/today') ?>"><i class="fas fa-calendar-day"></i> Today's Appointments</a></li>
            <li><a href="<?= site_url('appointments/upcoming') ?>"><i class="fas fa-calendar-week"></i> Upcoming</a></li>
            <li><a href="<?= site_url('appointments/pending') ?>"><i class="fas fa-clock"></i> Pending Approval</a></li>
            <li><a href="<?= site_url('appointments/history') ?>"><i class="fas fa-history"></i> History</a></li>
        </ul>
    </li>

    <!-- Billing & Payments -->
    <li class="nav-item expandable">
        <a href="#" class="nav-link">
            <i class="fas fa-file-invoice-dollar"></i> 
            <span>Billing & Payments</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?= site_url('billing/create') ?>"><i class="fas fa-file-invoice"></i> Create Bill</a></li>
            <li><a href="<?= site_url('billing/pending') ?>"><i class="fas fa-clock"></i> Pending Payments</a></li>
            <li><a href="<?= site_url('billing/paid') ?>"><i class="fas fa-check-circle"></i> Paid Bills</a></li>
            <li><a href="<?= site_url('billing/reports') ?>"><i class="fas fa-chart-bar"></i> Financial Reports</a></li>
            <li><a href="<?= site_url('billing/refunds') ?>"><i class="fas fa-undo"></i> Refunds</a></li>
        </ul>
    </li>
    </ul> <!-- Close nav-menu -->

    <!-- Laboratory -->
    <li class="nav-item expandable">
        <a href="#" onclick="toggleSubmenu(this)">
            <i class="fas fa-flask"></i> <span>Laboratory</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?= base_url('lab/tests') ?>"><i class="fas fa-vial"></i> Lab Tests</a></li>
            <li><a href="<?= base_url('lab/requests') ?>"><i class="fas fa-clipboard-list"></i> Test Requests</a></li>
            <li><a href="<?= base_url('lab/results') ?>"><i class="fas fa-file-medical-alt"></i> Test Results</a></li>
            <li><a href="<?= base_url('lab/inventory') ?>"><i class="fas fa-boxes"></i> Lab Inventory</a></li>
            <li><a href="#"><i class="fas fa-microscope"></i> Lab Request</a></li>
            <li><a href="#"><i class="fas fa-vial"></i> Test Results</a></li>
        </ul>
    </li>

    <!-- Pharmacy -->
    <li class="nav-item expandable">
        <a href="#" onclick="toggleSubmenu(this)">
            <i class="fas fa-pills"></i> <span>Pharmacy</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <ul class="submenu">
            <li><a href="<?= base_url('pharmacy/inventory') ?>"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="<?= base_url('pharmacy/medicines') ?>"><i class="fas fa-pills"></i> Medicines</a></li>
            <li><a href="<?= base_url('pharmacy/prescriptions') ?>"><i class="fas fa-prescription"></i> Prescriptions</a></li>
            <li><a href="<?= base_url('pharmacy/suppliers') ?>"><i class="fas fa-truck"></i> Suppliers</a></li>
            <li><a href="<?= base_url('pharmacy/expired') ?>"><i class="fas fa-exclamation-triangle"></i> Expired Items</a></li>
            <li><a href="#"><i class="fas fa-boxes"></i> Inventory Management</a></li>
            <li><a href="#"><i class="fas fa-prescription"></i> New Prescription</a></li>
            <li><a href="#"><i class="fas fa-pills"></i> Medicines</a></li>
        </ul>
    </li>

    <!-- Administration -->
    <li class="nav-item expandable">
        <a href="#">
            <i class="fas fa-cog"></i> 
            <span>Administration</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <ul class="submenu">
            <li><a href="#"><i class="fas fa-users"></i> User Management</a></li>
            <li><a href="#"><i class="fas fa-user-md"></i> Doctors</a></li>
            <li><a href="#"><i class="fas fa-sliders-h"></i> System Settings</a></li>
        </ul>
    </li>
<?php $this->endSection() ?>

<?php $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
        <div class="header-actions">
            <button class="btn btn-primary"><i class="fas fa-plus"></i> Quick Action</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <div class="stat-icon">
                    <i class="fas fa-hospital-user"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Patients</h3>
                    <div class="stat-value">1,247</div>
                    <div class="stat-trend text-success">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3>Today's Appointments</h3>
                    <div class="stat-value">42</div>
                    <div class="stat-trend text-warning">
                        <i class="fas fa-arrow-up"></i> 3 pending
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3>Monthly Revenue</h3>
                    <div class="stat-value">₱850,000</div>
                    <div class="stat-trend text-danger">
                        <i class="fas fa-arrow-down"></i> 5% from last month
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info">
                <div class="stat-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-content">
                    <h3>Active Staff</h3>
                    <div class="stat-value">89</div>
                    <div class="stat-trend text-success">
                        <i class="fas fa-arrow-up"></i> 5 new this month
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Appointments -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Appointments</h3>
                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan Dela Cruz</td>
                                    <td>Dr. Maria Santos</td>
                                    <td>Today, 10:00 AM</td>
                                    <td><span class="badge bg-success">Confirmed</span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Maria Clara</td>
                                    <td>Dr. Jose Rizal</td>
                                    <td>Today, 11:30 AM</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Andres Bonifacio</td>
                                    <td>Dr. Gregorio Del Pilar</td>
                                    <td>Today, 2:00 PM</td>
                                    <td><span class="badge bg-danger">Cancelled</span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="#" class="quick-action">
                            <div class="action-icon bg-primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span>Add Patient</span>
                        </a>
                        <a href="#" class="quick-action">
                            <div class="action-icon bg-success">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <span>New Appointment</span>
                        </a>
                        <a href="#" class="quick-action">
                            <div class="action-icon bg-warning">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <span>Create Bill</span>
                        </a>
                        <a href="#" class="quick-action">
                            <div class="action-icon bg-info">
                                <i class="fas fa-flask"></i>
                            </div>
                            <span>Lab Request</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card">
                <div class="card-header">
                    <h3>System Status</h3>
                </div>
                <div class="card-body">
                    <div class="system-status">
                        <div class="status-item">
                            <span class="status-indicator bg-success"></span>
                            <span>Database</span>
                            <span class="ms-auto">Online</span>
                        </div>
                        <div class="status-item">
                            <span class="status-indicator bg-success"></span>
                            <span>Server Load</span>
                            <span class="ms-auto">24%</span>
                        </div>
                        <div class="status-item">
                            <span class="status-indicator bg-success"></span>
                            <span>Storage</span>
                            <span class="ms-auto">45% Used</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 28px;
            color: #333;
            margin: 0;
            font-weight: 600;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 30px 25px;
            text-align: left;
            transition: box-shadow 0.3s ease;
        }

        .overview-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-content h3 {
            font-size: 16px;
            color: #666;
            margin: 0 0 15px 0;
            font-weight: 500;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        @media (max-width: 768px) {
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

<?= $this->endSection() ?>
