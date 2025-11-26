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
                    <li><a href="<?= base_url('admin/patients/admission') ?>"><span class="text">Patient Admission</span></a></li>
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
                    <li><a href="<?= site_url('doctor/schedule') ?>"><span class="text">Doctor Schedule</span></a></li>
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
            <li class="nav-item expandable">
                <a href="<?= site_url('admin/inventory/medicine') ?>">
                    <span class="text">Inventory Management</span>
                    <span class="arrow">›</span>
                </a>
            </li>

            <!-- Hospital Patient Rooms -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Hospital Patient Rooms</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="<?= base_url('admin/rooms/general-inpatient') ?>" onclick="event.stopPropagation();">
                            <span class="text">General Inpatient Rooms</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/rooms/critical-care') ?>" onclick="event.stopPropagation();">
                            <span class="text">Critical Care Units</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/rooms/specialized') ?>" onclick="event.stopPropagation();">
                            <span class="text">Specialized Patient Rooms</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Administration -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Administration</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= base_url('admin/Administration/ManageUser') ?>"><span class="text">User Management</span></a></li>
                    <li><a href="<?= base_url('admin/Administration/StaffManagement') ?>"><span class="text">Staff Management</span></a></li>
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
                    <li><a href="<?= site_url('doctor/appointments/list') ?>"><span class="text">Appointment List</span></a></li>
                   <li><a href="<?= site_url('doctor/my-schedule') ?>"><span class="text">Schedule</span></a></li>
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
            <!-- Hospital Patient Rooms (Receptionist view) -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Hospital Patient Rooms</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="<?= site_url('receptionist/rooms/general-inpatient') ?>" onclick="event.stopPropagation();">
                            <span class="text">General Inpatient Rooms</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('receptionist/rooms/critical-care') ?>" onclick="event.stopPropagation();">
                            <span class="text">Critical Care Units</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('receptionist/rooms/specialized') ?>" onclick="event.stopPropagation();">
                            <span class="text">Specialized Patient Rooms</span>
                        </a>
                    </li>
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

        <?php if ($role === 'accounting'): ?>
            <li class="nav-item">
                <a href="<?= site_url('accountant/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>

            <!-- Billing -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Billing & Payment</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('accountant/billing/process') ?>"><span class="text">Bill Process</span></a></li>
                    <li><a href="<?= base_url('accountant/billing/create') ?>"><span class="text">Bill Management</span></a></li>
                </ul>
            </li>

        <?php endif; ?>

        <?php if ($role === 'pharmacist'): ?>
            <li class="nav-item">
                <a href="<?= site_url('pharmacist/dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>

            <!-- Prescriptions -->
            <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Prescriptions</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?= site_url('admin/pharmacy/prescription-dispensing') ?>"><span class="text">New Prescription</span></a></li>
                </ul>
            </li>

            <!-- Inventory -->
            <li class="nav-item">
                <a href="<?= site_url('pharmacist/inventory') ?>">
                    <span class="text">Inventory</span>
                </a>
            </li>

            <!-- Transactions -->
            <li class="nav-item">
                <a href="<?= site_url('pharmacist/transactions') ?>">
                    <span class="text">Transactions</span>
                </a>
            </li>

        <?php endif; ?>

        <?php if ($role === 'labstaff' || $role === 'Lab_staff'): ?>
            <li class="nav-item">
                <a href="<?= site_url('dashboard') ?>">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('labstaff/laboratory/request') ?>">
                    <span class="text">Lab Request</span>
                </a>
            </li>

            <!-- Patients -->
            <li class="nav-item">
                <a href="<?= site_url('labstaff/laboratory/testresult') ?>">
                    <span class="text">Test Result</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Common menu items for all users -->
        <li class="nav-item" style="margin-top: auto;">
            <a href="<?= site_url('auth/logout') ?>">
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</div>
       