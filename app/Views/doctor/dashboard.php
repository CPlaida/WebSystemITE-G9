<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Doctor Dashboard
<?= $this->endSection() ?>


<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Doctor Dashboard</h1>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Today's Appointments</h3>
                <div class="card-value">12</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Patients Seen Today</h3>
                <div class="card-value">8</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Pending Lab Results</h3>
                <div class="card-value">5</div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Prescriptions Written</h3>
                <div class="card-value">15</div>
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

    <!-- Recent Patients Table -->
    <div class="card">
        <div class="card-header">
            <h2>Recent Patients</h2>
            <button class="btn">View All</button>
        </div>
        <!-- Table here -->
    </div>
<?= $this->endSection() ?>
