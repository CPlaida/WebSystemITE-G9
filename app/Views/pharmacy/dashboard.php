<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Pharmacy Dashboard
<?= $this->endSection() ?>

<?= $this->section('sidebar') ?>
    <li class="nav-item active">
        <a href="#"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-prescription-bottle-alt"></i> <span>Prescriptions</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-pills"></i> <span>Medication Inventory</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-exclamation-triangle"></i> <span>Low Stock Alerts</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-user-md"></i> <span>Drug Interactions</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-chart-line"></i> <span>Sales Reports</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-truck"></i> <span>Suppliers</span></a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Pharmacy Dashboard</h1>
        <p>Welcome, <?= $user ?? 'Pharmacist' ?>! Manage prescriptions, inventory, and medication dispensing.</p>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="action-btn primary">
            <i class="fas fa-prescription-bottle-alt"></i>
            <span>Process Prescription</span>
        </button>
        <button class="action-btn secondary">
            <i class="fas fa-pills"></i>
            <span>Add Medication</span>
        </button>
        <button class="action-btn info">
            <i class="fas fa-search"></i>
            <span>Search Inventory</span>
        </button>
        <button class="action-btn warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Stock Alerts</span>
        </button>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Pending Prescriptions</h3>
                <div class="card-value">12</div>
                <div class="card-trend">Awaiting processing</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-prescription-bottle-alt"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Low Stock Items</h3>
                <div class="card-value">8</div>
                <div class="card-trend">Need reorder</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Today's Sales</h3>
                <div class="card-value">₱18,750</div>
                <div class="card-trend">45 transactions</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-cash-register"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Medications in Stock</h3>
                <div class="card-value">1,247</div>
                <div class="card-trend">Different items</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-pills"></i>
            </div>
        </div>
    </div>

    <!-- Pending Prescriptions -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-prescription-bottle-alt"></i> Pending Prescriptions</h2>
            <button class="btn">View All</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Medications</th>
                        <th>Priority</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>RX-2025-001</td>
                        <td>Maria Santos</td>
                        <td>Dr. Cruz</td>
                        <td>Amoxicillin 500mg, Paracetamol 500mg</td>
                        <td><span class="priority-high">High</span></td>
                        <td>
                            <button class="btn-sm">Process</button>
                            <button class="btn-sm btn-secondary">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td>RX-2025-002</td>
                        <td>Juan Dela Cruz</td>
                        <td>Dr. Reyes</td>
                        <td>Metformin 500mg, Losartan 50mg</td>
                        <td><span class="priority-normal">Normal</span></td>
                        <td>
                            <button class="btn-sm">Process</button>
                            <button class="btn-sm btn-secondary">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td>RX-2025-003</td>
                        <td>Ana Garcia</td>
                        <td>Dr. Lopez</td>
                        <td>Cetirizine 10mg</td>
                        <td><span class="priority-low">Low</span></td>
                        <td>
                            <button class="btn-sm">Process</button>
                            <button class="btn-sm btn-secondary">View Details</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h2>
            <button class="btn">Reorder All</button>
        </div>
        <div class="stock-alerts">
            <div class="alert-item critical">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-info">
                    <h4>Paracetamol 500mg</h4>
                    <p>Current Stock: 15 units</p>
                    <span class="alert-level">Critical - Reorder immediately</span>
                </div>
                <div class="alert-actions">
                    <button class="btn-sm">Reorder</button>
                </div>
            </div>
            <div class="alert-item warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-info">
                    <h4>Amoxicillin 500mg</h4>
                    <p>Current Stock: 45 units</p>
                    <span class="alert-level">Low - Consider reordering</span>
                </div>
                <div class="alert-actions">
                    <button class="btn-sm btn-secondary">Reorder</button>
                </div>
            </div>
            <div class="alert-item warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-info">
                    <h4>Metformin 500mg</h4>
                    <p>Current Stock: 38 units</p>
                    <span class="alert-level">Low - Consider reordering</span>
                </div>
                <div class="alert-actions">
                    <button class="btn-sm btn-secondary">Reorder</button>
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

        .dashboard-header p {
            color: #666;
            margin: 0;
            font-size: 16px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .action-btn.primary { background: #28a745; }
        .action-btn.secondary { background: #6c757d; }
        .action-btn.info { background: #17a2b8; }
        .action-btn.warning { background: #ffc107; color: #333; }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .overview-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: box-shadow 0.3s ease;
        }

        .overview-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-content h3 {
            font-size: 14px;
            color: #666;
            margin: 0 0 10px 0;
            font-weight: 500;
            text-transform: uppercase;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0 0 5px 0;
        }

        .card-trend {
            font-size: 12px;
            color: #28a745;
            margin: 0;
        }

        .card-icon {
            font-size: 36px;
            color: #28a745;
            opacity: 0.7;
        }

        .stock-alerts {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .alert-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert-item.critical {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .alert-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
        }

        .alert-item.critical .alert-icon {
            background: #dc3545;
            color: white;
        }

        .alert-item.warning .alert-icon {
            background: #ffc107;
            color: #333;
        }

        .alert-info {
            flex: 1;
        }

        .alert-info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }

        .alert-info p {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }

        .alert-level {
            font-size: 12px;
            font-weight: 500;
        }

        .alert-item.critical .alert-level {
            color: #dc3545;
        }

        .alert-item.warning .alert-level {
            color: #856404;
        }

        .alert-actions {
            display: flex;
            gap: 10px;
        }

        .priority-high { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 500;
        }
        
        .priority-normal { 
            background: #d4edda; 
            color: #155724; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 500;
        }
        
        .priority-low { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?= $this->endSection() ?>
