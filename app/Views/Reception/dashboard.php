<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Reception Dashboard
<?= $this->endSection() ?>

<?= $this->section('sidebar') ?>
    <li class="nav-item active">
        <a href="#"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-user-plus"></i> <span>Patient Registration</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-calendar-check"></i> <span>Appointments</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-phone"></i> <span>Phone Directory</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-clipboard-list"></i> <span>Patient Queue</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-file-medical"></i> <span>Medical Records</span></a>
    </li>
    <li class="nav-item">
        <a href="#"><i class="fas fa-credit-card"></i> <span>Billing</span></a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-header">
        <h1>Reception Dashboard</h1>
        <p>Welcome, <?= $user ?? 'Receptionist' ?>! Manage patient registration, appointments, and front desk operations.</p>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="action-btn primary">
            <i class="fas fa-user-plus"></i>
            <span>Register New Patient</span>
        </button>
        <button class="action-btn secondary">
            <i class="fas fa-calendar-plus"></i>
            <span>Schedule Appointment</span>
        </button>
        <button class="action-btn info">
            <i class="fas fa-search"></i>
            <span>Search Patient</span>
        </button>
        <button class="action-btn warning">
            <i class="fas fa-phone"></i>
            <span>Emergency Call</span>
        </button>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-content">
                <h3>Today's Appointments</h3>
                <div class="card-value">24</div>
                <div class="card-trend">+3 from yesterday</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Waiting Patients</h3>
                <div class="card-value">8</div>
                <div class="card-trend">Current queue</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>New Registrations</h3>
                <div class="card-value">5</div>
                <div class="card-trend">Today</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
        </div>
        <div class="overview-card">
            <div class="card-content">
                <h3>Pending Payments</h3>
                <div class="card-value">₱12,500</div>
                <div class="card-trend">3 invoices</div>
            </div>
            <div class="card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <!-- Today's Schedule -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-alt"></i> Today's Appointments</h2>
            <button class="btn">View All</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>09:00 AM</td>
                        <td>Maria Santos</td>
                        <td>Dr. Cruz</td>
                        <td>Cardiology</td>
                        <td><span class="status-confirmed">Confirmed</span></td>
                        <td>
                            <button class="btn-sm">Check-in</button>
                            <button class="btn-sm btn-secondary">Reschedule</button>
                        </td>
                    </tr>
                    <tr>
                        <td>09:30 AM</td>
                        <td>Juan Dela Cruz</td>
                        <td>Dr. Reyes</td>
                        <td>General Medicine</td>
                        <td><span class="status-waiting">Waiting</span></td>
                        <td>
                            <button class="btn-sm">Check-in</button>
                            <button class="btn-sm btn-secondary">Reschedule</button>
                        </td>
                    </tr>
                    <tr>
                        <td>10:00 AM</td>
                        <td>Ana Garcia</td>
                        <td>Dr. Lopez</td>
                        <td>Pediatrics</td>
                        <td><span class="status-pending">Pending</span></td>
                        <td>
                            <button class="btn-sm">Confirm</button>
                            <button class="btn-sm btn-danger">Cancel</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Patient Queue -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Current Patient Queue</h2>
            <button class="btn">Refresh</button>
        </div>
        <div class="queue-list">
            <div class="queue-item">
                <div class="queue-number">1</div>
                <div class="queue-info">
                    <h4>Roberto Martinez</h4>
                    <p>Consultation - Dr. Santos</p>
                    <span class="queue-time">Waiting: 15 mins</span>
                </div>
                <div class="queue-actions">
                    <button class="btn-sm">Call Next</button>
                </div>
            </div>
            <div class="queue-item">
                <div class="queue-number">2</div>
                <div class="queue-info">
                    <h4>Lisa Chen</h4>
                    <p>Follow-up - Dr. Kim</p>
                    <span class="queue-time">Waiting: 8 mins</span>
                </div>
                <div class="queue-actions">
                    <button class="btn-sm btn-secondary">Skip</button>
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

        .action-btn.primary { background: #007bff; }
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
            color: #007bff;
            opacity: 0.7;
        }

        .queue-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .queue-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .queue-number {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        .queue-info {
            flex: 1;
        }

        .queue-info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }

        .queue-info p {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }

        .queue-time {
            font-size: 12px;
            color: #ffc107;
            font-weight: 500;
        }

        .queue-actions {
            display: flex;
            gap: 10px;
        }

        .status-confirmed { 
            background: #d4edda; 
            color: #155724; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
        }
        
        .status-waiting { 
            background: #fff3cd; 
            color: #856404; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
        }
        
        .status-pending { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
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
