<div class="sidebar">
    <div class="logo">
        <h2>St. Peter Hospital</h2>
        <div class="toggle-btn"><i class="fas fa-bars"></i></div>
    </div>
    <ul class="nav-menu">
        <?php $role = session()->get('role'); ?>

        <?php if ($role === 'admin'): ?>
            <li class="nav-item">
                <a href="<?= base_url('admin/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- Patient Management -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Patients</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/patients/register') ?>"><span class="text">Register Out Patient</span></a></li>
                    <li><a href="<?= base_url('admin/patients/inpatient') ?>"><span class="text">Register In Patient</span></a></li>
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
                    <li><a href="<?= site_url('doctor/schedule') ?>"><span class="text">Staff Schedule</span></a></li>
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
                    <li><a href="<?= base_url('billing/process') ?>"><span class="text">Bill Process</span></a></li>
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
                    <li><a href="<?= base_url('admin/InventoryMan/PrescriptionDispencing') ?>"><span class="text">New Prescription</span></a></li>
                    <li><a href="<?= site_url('admin/pharmacy/transactions') ?>"><span class="text">Transactions</span></a></li>
                </ul>
            </li>

            <!-- Inventory Management -->
            <li class="nav-item">
                <a href="<?= site_url('admin/inventory/medicine') ?>">
                    <span class="text">Inventory Management</span>
                    <span class="arrow">›</span>
                </a>
            </li>

            <!-- Administration -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Administration</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/Administration/ManageUser') ?>"><span class="text">User Management</span></a></li>
                    <li><a href="<?= base_url('admin/Administration/RoleManagement') ?>"><span class="text">Role Management</span></a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($role === 'doctor'): ?>
            <li class="nav-item">
                <a href="<?= site_url('doctor/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- Patients -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Patients</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('doctor/patients/view') ?>"><span class="text">View Patient</span></a></li>
                </ul>
            </li>
            <!-- Appointments -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Appointments</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('doctor/appointments') ?>"><span class="text">Appointment List</span></a></li>
                    <li><a href="<?= site_url('doctor/schedule') ?>"><span class="text">Staff Schedule</span></a></li>
                </ul>
            </li>
            <!-- Laboratory (Doctor view) -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Laboratory</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('doctor/laboratory/testresult') ?>"><span class="text">Test Results</span></a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($role === 'nurse'): ?>
            <li class="nav-item">
                <a href="<?= site_url('nurse/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- Patients -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Patients</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('nurse/patients/view') ?>"><span class="text">View Patient</span></a></li>
                </ul>
            </li>
            <!-- Appointments -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Appointments</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('nurse/appointments/list') ?>"><span class="text">Appointment List</span></a></li>
                </ul>
            </li>
            <!-- Laboratory -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Laboratory</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('nurse/laboratory/request') ?>"><span class="text">Lab Request</span></a></li>
                    <li><a href="<?= site_url('nurse/laboratory/testresult') ?>"><span class="text">Test Results</span></a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($role === 'receptionist'): ?>
            <li class="nav-item">
                <a href="<?= site_url('receptionist/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- Patients -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Patients</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('receptionist/patients/register') ?>"><span class="text">Register Out Patient</span></a></li>
                    <li><a href="<?= site_url('receptionist/patients/inpatient') ?>"><span class="text">Register In Patient</span></a></li>
                    <li><a href="<?= site_url('receptionist/patients/view') ?>"><span class="text">View Patient</span></a></li>
                </ul>
            </li>
            <!-- Appointments -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Appointments</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('receptionist/appointments/book') ?>"><span class="text">Book Appointment</span></a></li>
                    <li><a href="<?= site_url('receptionist/appointments/list') ?>"><span class="text">Appointment List</span></a></li>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Common menu items for all users -->
        <li class="nav-item"><a href="<?= site_url('auth/logout') ?>"><span class="text">Logout</span></a></li>
    </ul>
</div>
