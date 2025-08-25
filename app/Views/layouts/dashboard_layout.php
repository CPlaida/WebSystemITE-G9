<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - St. Peter Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #0077b6;
            color: #fff;
            transition: all 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .sidebar.closed {
            width: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
        }

        .logo h2 {
            font-size: 18px;
            margin: 0;
        }

        /* Toggle inside logo */
        .toggle-btn {
            font-size: 20px;
            cursor: pointer;
            color: #fff;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .nav-menu li {
            margin: 10px 0;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: background 0.3s;
        }

        .nav-menu li a:hover,
        .nav-menu li.active a {
            background: #005f8a;
        }

        .nav-menu li a i {
            margin-right: 12px;
            font-size: 18px;
            min-width: 20px;
            text-align: center;
        }

        /* Expandable menu styling */
        .nav-item.expandable > a {
            position: relative;
        }

        .nav-item.expandable > a .arrow {
            position: absolute;
            right: 15px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .nav-item.expandable.expanded > a .arrow {
            transform: rotate(90deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .submenu.show {
            max-height: 200px;
        }

        .submenu li {
            margin: 0;
        }

        .submenu li a {
            padding: 8px 20px 8px 50px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .submenu li a:hover {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        .submenu li a i {
            font-size: 14px;
            margin-right: 8px;
        }

        /* kapag collapse icons lang matira */
        .sidebar.closed .nav-menu li a span,
        .sidebar.closed .logo h2 {
            display: none;
        }

        .sidebar.closed .submenu {
            display: none;
        }

        .sidebar.closed .nav-item.expandable > a .arrow {
            display: none;
        }

        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: #f6f8fb;
            transition: all 0.3s ease;
        }

        .main-content.zoomed {
            margin-left: 70px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>St. Peter Hospital</h2>
            <div class="toggle-btn"><i class="fas fa-bars"></i></div>
        </div>
        <ul class="nav-menu">
            <?php if (isset($custom_sidebar) && $custom_sidebar): ?>
                <?= $custom_sidebar ?>
            <?php else: ?>
                <li class="nav-item"><a href="#"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
                <!-- Hospital Administrator - Full control, user management, reports, and branch integration -->
                <?php if (session('user_role') === 'Hospital Administrator'): ?>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)">
                            <i class="fas fa-cogs"></i> <span>Administration</span>
                            <i class="fas fa-chevron-right arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-users-cog"></i> User Management</a></li>
                            <li><a href="#"><i class="fas fa-chart-bar"></i> Reports</a></li>
                            <li><a href="#"><i class="fas fa-building"></i> Branch Integration</a></li>
                            <li><a href="#"><i class="fas fa-shield-alt"></i> System Settings</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Doctors - Access/update patient records, create prescriptions, request tests -->
                <?php if (session('user_role') === 'Doctor'): ?>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)"><i class="fas fa-user-injured"></i> <span>Patient Records</span> <i class="fas fa-chevron-right arrow"></i></a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-list"></i> My Patients</a></li>
                            <li><a href="#"><i class="fas fa-edit"></i> Update Records</a></li>
                            <li><a href="#"><i class="fas fa-history"></i> Medical History</a></li>
                        </ul>
                    </li>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)"><i class="fas fa-prescription-bottle-alt"></i> <span>Prescriptions</span> <i class="fas fa-chevron-right arrow"></i></a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-plus"></i> Create Prescription</a></li>
                            <li><a href="#"><i class="fas fa-list"></i> My Prescriptions</a></li>
                            <li><a href="#"><i class="fas fa-search"></i> Search Prescriptions</a></li>
                        </ul>
                    </li>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)"><i class="fas fa-vials"></i> <span>Lab Tests</span> <i class="fas fa-chevron-right arrow"></i></a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-plus"></i> Request Test</a></li>
                            <li><a href="#"><i class="fas fa-clipboard-list"></i> Test Results</a></li>
                            <li><a href="#"><i class="fas fa-clock"></i> Pending Tests</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Nurses - Patient monitoring, treatment updates -->
                <?php if (session('user_role') === 'Nurse'): ?>
                    <li class="nav-item"><a href="#"><i class="fas fa-heartbeat"></i> <span>Patient Monitoring</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-notes-medical"></i> <span>Treatment Updates</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-calendar-check"></i> <span>Care Schedule</span></a></li>
                <?php endif; ?>

                <!-- Receptionists - Patient registration, appointment booking -->
                <?php if (session('user_role') === 'Receptionist'): ?>
                    <li class="nav-item"><a href="#"><i class="fas fa-user-plus"></i> <span>Patient Registration</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-calendar-alt"></i> <span>Appointment Booking</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-phone"></i> <span>Patient Inquiries</span></a></li>
                <?php endif; ?>

                <!-- Laboratory Staff - Manage test requests, enter results -->
                <?php if (session('user_role') === 'Laboratory Staff'): ?>
                    <li class="nav-item"><a href="#"><i class="fas fa-vials"></i> <span>Test Requests</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-clipboard-list"></i> <span>Enter Results</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-microscope"></i> <span>Lab Equipment</span></a></li>
                <?php endif; ?>

                <!-- Pharmacists - Track and dispense medicines -->
                <?php if (session('user_role') === 'Pharmacist'): ?>
                    <li class="nav-item"><a href="#"><i class="fas fa-pills"></i> <span>Medicine Inventory</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-prescription"></i> <span>Dispense Medicines</span></a></li>
                    <li class="nav-item"><a href="#"><i class="fas fa-truck"></i> <span>Medicine Orders</span></a></li>
                <?php endif; ?>

                <!-- Accountants - Handle billing, payments, and insurance claims -->
                <?php if (session('user_role') === 'Accountant'): ?>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)">
                            <i class="fas fa-calculator"></i> <span>Financial Management</span>
                            <i class="fas fa-chevron-right arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-file-invoice-dollar"></i> Billing</a></li>
                            <li><a href="#"><i class="fas fa-credit-card"></i> Payments</a></li>
                            <li><a href="#"><i class="fas fa-file-medical-alt"></i> Insurance Claims</a></li>
                            <li><a href="#"><i class="fas fa-chart-line"></i> Financial Reports</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- IT Staff - System maintenance, security, and backups -->
                <?php if (session('user_role') === 'IT Staff'): ?>
                    <li class="nav-item expandable">
                        <a href="#" onclick="toggleSubmenu(this)">
                            <i class="fas fa-server"></i> <span>IT Management</span>
                            <i class="fas fa-chevron-right arrow"></i>
                        </a>
                        <ul class="submenu">
                            <li><a href="#"><i class="fas fa-tools"></i> System Maintenance</a></li>
                            <li><a href="#"><i class="fas fa-lock"></i> Security</a></li>
                            <li><a href="#"><i class="fas fa-hdd"></i> Backups</a></li>
                            <li><a href="#"><i class="fas fa-bug"></i> System Logs</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Common menu items for all users -->
                <li class="nav-item"><a href="<?= site_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const toggleBtn = document.querySelector('.toggle-btn');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('zoomed');
        });

        // Submenu toggle function
        function toggleSubmenu(element) {
            const parentLi = element.parentElement;
            const submenu = parentLi.querySelector('.submenu');
            
            // Close other open submenus
            document.querySelectorAll('.nav-item.expandable').forEach(item => {
                if (item !== parentLi) {
                    item.classList.remove('expanded');
                    const otherSubmenu = item.querySelector('.submenu');
                    if (otherSubmenu) {
                        otherSubmenu.classList.remove('show');
                    }
                }
            });
            
            // Toggle current submenu
            parentLi.classList.toggle('expanded');
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

        // Active link highlight
        document.querySelectorAll('.nav-item a').forEach(item => {
            if (item.href === window.location.href) {
                item.parentElement.classList.add('active');
            }
        });
    </script>
</body>
</html>
