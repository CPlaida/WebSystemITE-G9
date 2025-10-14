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
            <div class="dashboard-summary">
                <div class="mini-card"><div class="mini-title">Today's Appointments</div><div class="mini-value"><?= $appointmentsCount ?? '0' ?></div></div>
                <div class="mini-card"><div class="mini-title">Total Patients</div><div class="mini-value"><?= $patientsCount ?? '0' ?></div></div>
                <div class="mini-card"><div class="mini-title">Active Cases</div><div class="mini-value"><?= $activeCases ?? '0' ?></div></div>
                <div class="mini-card"><div class="mini-title">New Patients Today</div><div class="mini-value"><?= $newPatientsToday ?? 0 ?></div></div>

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

                <div class="mini-card"><div class="mini-title">Pending Prescriptions</div><div class="mini-value"><?= $prescriptionsCount ?? '0' ?></div></div>
                <div class="mini-card"><div class="mini-title">Pending Lab Tests</div><div class="mini-value"><?= ($labStats['pending'] ?? null) !== null ? $labStats['pending'] : ($labTestsCount ?? 0) ?></div></div>

                

                <div class="mini-card"><div class="mini-title">Users Total</div><div class="mini-value"><?= isset($userCounts) ? array_sum($userCounts) : 0 ?></div></div>
                <div class="mini-card"><div class="mini-title">System Status</div><div class="mini-value"><span class="status-badge status-online"><span class="status-dot"></span><?= $systemStatus ?? 'Online' ?></span></div></div>
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
        .dashboard-header { margin-bottom: 16px; }

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

        /* One-frame compact dashboard */
        .overview-grid { display: block; }
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            align-items: stretch;
        }
        @media (max-width: 1600px) {
            .dashboard-summary { grid-template-columns: repeat(5, 1fr); }
        }
        @media (max-width: 1366px) {
            .dashboard-summary { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 1200px) {
            .dashboard-summary { grid-template-columns: repeat(3, 1fr); }
        }
        .mini-card {
            background: #fff;
            border: 1px solid #e6ebf0;
            border-radius: 8px;
            padding: 12px 14px;
            box-shadow: 0 1px 2px rgba(16,24,40,.04);
        }
        .mini-title { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mini-value { font-size: 22px; color: #0f172a; font-weight: 800; line-height: 1.1; }
        /* Schedule mini-card */
        .schedule-card { padding: 10px 12px; }
        .schedule-list { display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }
        .schedule-item { display: grid; grid-template-columns: 64px 1fr 10px; align-items: center; gap: 10px; padding: 6px 6px; border-radius: 6px; }
        .schedule-link { text-decoration: none; border: 1px solid transparent; }
        .schedule-link:hover { background: #f7fafc; border-color: #e6ebf0; }
        .schedule-time { font-size: 12px; font-weight: 800; color: #0f172a; }
        .schedule-info { display: flex; flex-direction: column; line-height: 1.1; }
        .schedule-doctor { font-size: 12px; font-weight: 700; color: #1f2d3d; }
        .schedule-patient { font-size: 11px; color: #6b7280; }
        .schedule-status { width: 8px; height: 8px; border-radius: 50%; }
        .schedule-status.ok { background: #2ecc71; }
        .schedule-status.warn { background: #f59f00; }
        .schedule-empty { font-size: 12px; color: #6b7280; }
        .mini-link { float: right; font-weight: 600; font-size: 11px; color: #2563eb; text-decoration: none; }
        .mini-link:hover { text-decoration: underline; }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; border: 1px solid transparent; }
        .status-badge .status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        .status-online { background: #e8f7ef; color: #117a37; border-color: #b9e6c9; }
        .status-degraded { background: #fff4e5; color: #b26a00; border-color: #ffd8a8; }
        .status-offline { background: #fde8e8; color: #c02026; border-color: #f5c2c7; }

        /* Composite card for grouped metrics */
        .composite-card {
            background: #fff;
            border: 1px solid #e6ebf0;
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 1px 2px rgba(16,24,40,.04);
            grid-column: span 2;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .composite-header { display: flex; align-items: center; justify-content: space-between; }
        .composite-title { font-size: 14px; font-weight: 700; color: #1f2d3d; letter-spacing: .2px; }
        .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap: 10px; }
        .metric-item { background: #fafbfc; border: 1px dashed #e6ebf0; border-radius: 8px; padding: 10px; }
        .metric-title { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; font-weight: 600; }
        .metric-value { font-size: 20px; font-weight: 800; color: #0f172a; }

        @media (max-width: 1366px) { .composite-card { grid-column: span 2; } .metric-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 900px) { .composite-card { grid-column: span 3; } }
        @media (max-width: 768px) { .composite-card { grid-column: span 1; } .metric-grid { grid-template-columns: 1fr 1fr; } }

        .list-card { background: #fff; border: 1px solid #e6ebf0; border-radius: 10px; padding: 8px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
        .list-item { display: grid; grid-template-columns: 90px 1fr auto; align-items: center; gap: 10px; padding: 10px 8px; border-bottom: 1px solid #f1f3f5; }
        .list-item:last-child { border-bottom: 0; }
        .list-time { font-weight: 700; color: #0f172a; font-size: 14px; }
        .list-main { display: flex; flex-direction: column; }
        .list-title { font-size: 14px; font-weight: 700; color: #1f2d3d; }
        .list-sub { font-size: 12px; color: #6b7280; }
        .list-status { justify-self: end; }
        .empty-state { padding: 16px; color: #6b7280; font-size: 14px; text-align: center; }

        /* Simple card container */
        .simple-card { background: #fff; border: 1px solid #e6ebf0; border-radius: 10px; box-shadow: 0 1px 2px rgba(16,24,40,.04); overflow: hidden; }
        .simple-card-header { padding: 12px 14px; border-bottom: 1px solid #eef2f7; background: #fafbfc; }
        .simple-card-title { margin: 0; font-size: 16px; color: #1f2d3d; font-weight: 700; }
        .simple-card-body { padding: 4px 8px; }

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

        /* Sections */
        .section { margin-bottom: 28px; }
        .section-title { margin: 0 0 12px 0; font-size: 18px; color: #1f2d3d; font-weight: 600; }
        .section-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }

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