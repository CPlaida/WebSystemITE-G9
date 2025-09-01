<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Nurse Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Nurse Dashboard</h1>
        <p class="dashboard-subtitle">Patient Care & Monitoring</p>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card urgent">
            <div class="card-content">
                <div class="card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-info">
                    <h3>Critical Patients</h3>
                    <div class="card-value">3</div>
                </div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <div class="card-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="card-info">
                    <h3>Patients Under Care</h3>
                    <div class="card-value">24</div>
                </div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <div class="card-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="card-info">
                    <h3>Medications Due</h3>
                    <div class="card-value">8</div>
                </div>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <div class="card-icon">
                    <i class="fas fa-thermometer-half"></i>
                </div>
                <div class="card-info">
                    <h3>Vitals Pending</h3>
                    <div class="card-value">12</div>
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
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .dashboard-subtitle {
            color: #666;
            font-size: 16px;
            margin: 0;
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

        .overview-card.urgent {
            border-left: 4px solid #dc3545;
        }

        .card-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            font-size: 24px;
            color: #0077b6;
            min-width: 40px;
        }

        .overview-card.urgent .card-icon {
            color: #dc3545;
        }

        .card-info h3 {
            font-size: 14px;
            color: #666;
            margin: 0 0 8px 0;
            font-weight: 500;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 18px;
            color: #333;
            margin: 0;
            font-weight: 600;
        }

        .card-header h2 i {
            margin-right: 8px;
            color: #0077b6;
        }

        .card-body {
            padding: 20px;
        }

        .alert-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .alert-item.critical {
            background: #fff5f5;
            border-left: 4px solid #dc3545;
        }

        .alert-item.warning {
            background: #fffbf0;
            border-left: 4px solid #ffc107;
        }

        .alert-item.info {
            background: #f0f8ff;
            border-left: 4px solid #17a2b8;
        }

        .alert-icon {
            font-size: 20px;
            min-width: 30px;
        }

        .alert-item.critical .alert-icon {
            color: #dc3545;
        }

        .alert-item.warning .alert-icon {
            color: #ffc107;
        }

        .alert-item.info .alert-icon {
            color: #17a2b8;
        }

        .alert-content h4 {
            font-size: 16px;
            color: #333;
            margin: 0 0 5px 0;
        }

        .alert-content p {
            color: #666;
            margin: 0 0 5px 0;
            font-size: 14px;
        }

        .alert-time {
            font-size: 12px;
            color: #999;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .schedule-time {
            font-weight: 600;
            color: #0077b6;
            min-width: 60px;
            font-size: 14px;
        }

        .schedule-content {
            flex: 1;
        }

        .schedule-content h4 {
            font-size: 16px;
            color: #333;
            margin: 0 0 5px 0;
        }

        .schedule-content p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .schedule-status {
            font-size: 18px;
        }

        .schedule-item.completed .schedule-status {
            color: #28a745;
        }

        .schedule-item.active .schedule-status {
            color: #ffc107;
        }

        .schedule-item .schedule-status {
            color: #dee2e6;
        }

        .patient-table {
            grid-column: 1 / -1;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: #0077b6;
            color: white;
        }

        .btn-primary:hover {
            background: #005f8a;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .monitoring-table {
            width: 100%;
            border-collapse: collapse;
        }

        .monitoring-table th,
        .monitoring-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .monitoring-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .monitoring-table tr.critical {
            background: #fff5f5;
        }

        .patient-info strong {
            display: block;
            color: #333;
        }

        .patient-info small {
            color: #666;
        }

        .vitals {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .vital-item {
            font-size: 12px;
            color: #333;
        }

        .vital-time {
            font-size: 11px;
            color: #999;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.critical {
            background: #dc3545;
            color: white;
        }

        .status-badge.stable {
            background: #ffc107;
            color: #333;
        }

        .status-badge.good {
            background: #28a745;
            color: white;
        }

        .btn-icon {
            background: none;
            border: none;
            color: #0077b6;
            cursor: pointer;
            padding: 5px;
            margin: 0 2px;
            border-radius: 3px;
        }

        .btn-icon:hover {
            background: #f0f8ff;
        }

        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?= $this->endSection() ?>
