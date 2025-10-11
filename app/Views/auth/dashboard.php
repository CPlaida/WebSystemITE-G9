<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    <?= ucfirst($userRole) ?> Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1><?= ucfirst($userRole) ?> Dashboard</h1>
        <p class="dashboard-subtitle">Welcome back, <?= session()->get('first_name') ?? 'User' ?>!</p>
    </div>

    <!-- Role-based Dashboard Sections -->
    <div class="overview-grid">
        <?php if ($userRole === 'admin') : ?>
            <!-- Admin Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Appointments</h3>
                    <div class="card-value"><?= $appointmentsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Total Patients</h3>
                    <div class="card-value"><?= $patientsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Active Cases</h3>
                    <div class="card-value"><?= $activeCases ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Revenue</h3>
                    <div class="card-value">₱<?= number_format($todayRevenue ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Pending Bills</h3>
                    <div class="card-value"><?= $pendingBills ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>System Status</h3>
                    <div class="card-value"><?= $systemStatus ?? 'Online' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'doctor') : ?>
            <!-- Doctor Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Appointments</h3>
                    <div class="card-value"><?= $appointmentsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Total Patients</h3>
                    <div class="card-value"><?= $patientsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Active Cases</h3>
                    <div class="card-value"><?= $activeCases ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'nurse') : ?>
            <!-- Nurse Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Appointments</h3>
                    <div class="card-value"><?= $appointmentsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Total Patients</h3>
                    <div class="card-value"><?= $patientsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Active Cases</h3>
                    <div class="card-value"><?= $activeCases ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'receptionist') : ?>
            <!-- Receptionist Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Appointments</h3>
                    <div class="card-value"><?= $appointmentsCount ?? '0' ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Total Patients</h3>
                    <div class="card-value"><?= $patientsCount ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'accounting') : ?>
            <!-- Accounting Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Today's Revenue</h3>
                    <div class="card-value">₱<?= number_format($todayRevenue ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-content">
                    <h3>Pending Bills</h3>
                    <div class="card-value"><?= $pendingBills ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'pharmacist') : ?>
            <!-- Pharmacist Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Prescriptions</h3>
                    <div class="card-value"><?= $prescriptionsCount ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'labstaff') : ?>
            <!-- Lab Staff Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Lab Tests</h3>
                    <div class="card-value"><?= $labTestsCount ?? '0' ?></div>
                </div>
            </div>

        <?php elseif ($userRole === 'itstaff') : ?>
            <!-- IT Staff Dashboard -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>System Status</h3>
                    <div class="card-value"><?= $systemStatus ?? 'Online' ?></div>
                </div>
            </div>

        <?php else : ?>
            <!-- Default Dashboard for other roles -->
            <div class="overview-card">
                <div class="card-content">
                    <h3>Welcome</h3>
                    <div class="card-value"><?= session()->get('first_name') ?? 'User' ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .dashboard-subtitle {
            color: #7f8c8d;
            margin: 0;
            font-size: 16px;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .overview-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-content h3 {
            font-size: 16px;
            color: #666;
            margin: 0 0 10px 0;
            font-weight: 500;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .recent-activity {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .recent-activity h2 {
            font-size: 20px;
            color: #2c3e50;
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #e6f7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #1890ff;
            font-size: 16px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            margin: 0 0 5px 0;
            font-size: 15px;
            color: #2c3e50;
        }

        .activity-time {
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Role-specific colors */
        .role-admin { border-left: 4px solid #e74c3c; }
        .role-doctor { border-left: 4px solid #2ecc71; }
        .role-nurse { border-left: 4px solid #3498db; }
        .role-accounting { border-left: 4px solid #9b59b6; }
        .role-itstaff { border-left: 4px solid #f39c12; }
        .role-labstaff { border-left: 4px solid #1abc9c; }
        .role-pharmacist { border-left: 4px solid #e67e22; }
        .role-receptionist { border-left: 4px solid #7f8c8d; }

        @media (max-width: 768px) {
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?= $this->endSection() ?>