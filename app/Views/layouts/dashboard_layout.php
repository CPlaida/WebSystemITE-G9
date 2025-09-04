<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - St. Peter Hospital</title>
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            line-height: 1.5;
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
            overflow-y: auto;
        }

        .sidebar.closed {
            width: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.1);
            height: 70px;
        }

        .logo h2 {
            font-size: 1.25rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .toggle-btn {
            font-size: 1.25rem;
            cursor: pointer;
            color: #fff;
            background: none;
            border: none;
            padding: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .nav-menu li {
            margin: 5px 0;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: background 0.3s;
            cursor: pointer;
        }

        .nav-menu li a:hover,
        .nav-menu li.active a {
            background: #005f8a;
        }

        .nav-menu li a i {
            margin-right: 12px;
            font-size: 1.125rem;
            min-width: 24px;
            text-align: center;
        }

        /* Expandable menu styling */
        .nav-item.expandable > a {
            position: relative;
        }

        .nav-item.expandable > a .arrow {
            position: absolute;
            right: 15px;
            font-size: 0.75rem;
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
            max-height: 1000px;
        }

        .submenu li {
            margin: 0;
        }

        .submenu li a {
            padding: 8px 20px 8px 50px;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .submenu li a:hover {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        .submenu li a i {
            font-size: 0.875rem;
            margin-right: 8px;
        }

        /* Collapsed sidebar styles */
        .sidebar.closed .nav-menu li a {
            justify-content: center;
            padding: 12px 0;
        }
        
        .sidebar.closed .nav-menu li span.text {
            display: none;
        }
        
        .nav-menu li.nav-item > span.text {
            display: inline-block;
            padding: 12px 20px;
            width: 100%;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .sidebar.closed .logo h2,
        .sidebar.closed .nav-menu li a .text {
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
            min-height: 100vh;
            overflow-y: auto;
        }

        .main-content.zoomed {
            margin-left: 70px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .main-content.zoomed {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>St. Peter Hospital</h2>
            <div class="toggle-btn"><i class="fas fa-bars"></i></div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><span class="text">Dashboard</span></li>

            <?php $role = session()->get('role'); ?>

            <?php if ($role === 'admin'): ?>
                <!-- Patient Management -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Patients</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('patients/register') ?>"><span class="text">Register Patient</span></a></li>
                        <li><a href="<?= base_url('patients/view') ?>"><span class="text">View Patient</span></a></li>
                    </ul>
                </li>

                <!-- Appointments -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Appointments</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('appointments/book') ?>"><span class="text">Book Appointment</span></a></li>
                        <li><a href="<?= site_url('appointments/list') ?>"><span class="text">Appointment List</span></a></li>
                        <li><a href="<?= site_url('appointments/schedule') ?>"><span class="text">Staff Schedule</span></a></li>
                    </ul>
                </li>

                <!-- Billing -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Billing and Payment</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('billing') ?>"><span class="text">Bill Management</span></a></li>
                    </ul>
                </li>

                <!-- Laboratory -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Laboratory</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= base_url('laboratory/request') ?>"><span class="text">Lab Request</span></a></li>
                        <li><a href="<?= base_url('laboratory/testresult') ?>"><span class="text">Test Results</span></a></li>
                    </ul>
                </li>

                <!-- Pharmacy -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Pharmacy</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#"><span class="text">Inventory Management</span></a></li>
                        <li><a href="#"><span class="text">New Prescription</span></a></li>
                        <li><a href="#"><span class="text">Medicines</span></a></li>
                    </ul>
                </li>

                <!-- Administration -->
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Administration</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#"><span class="text">User Management</span></a></li>
                        <li><a href="#"><span class="text">Doctors</span></a></li>
                        <li><a href="#"><span class="text">System Settings</span></a></li>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- Common menu items for all users -->
            <li class="nav-item"><a href="<?= site_url('auth/logout') ?>"><span class="text">Logout</span></a></li>
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
