<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Admin Dashboard
<?= $this->endSection() ?>

<?= $this->section('sidebar') ?>
    <li class="nav-item active">
        <a href="#"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-user"></i> <span>Patient</span> <i class="fas fa-chevron-right"></i></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-calendar-alt"></i> <span>Appointments</span> <i class="fas fa-chevron-right"></i></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-credit-card"></i> <span>Billing & Payment</span> <i class="fas fa-chevron-right"></i></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-flask"></i> <span>Laboratory</span> <i class="fas fa-chevron-right"></i></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-pills"></i> <span>Pharmacy</span> <i class="fas fa-chevron-right"></i></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-cogs"></i> <span>Administration</span> <i class="fas fa-chevron-right"></i></a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Total Patients</h3>
                <div class="card-value">1,247</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Today's Appointments</h3>
                <div class="card-value">32</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Monthly Revenue</h3>
                <div class="card-value">₱850,000</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Active Staff</h3>
                <div class="card-value">89</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Critical Patients</h3>
                <div class="card-value">7</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Bed Occupancy</h3>
                <div class="card-value">78%</div>
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
