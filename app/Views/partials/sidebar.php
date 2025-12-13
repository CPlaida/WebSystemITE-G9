<?php
// Unified sidebar for all roles with permission-based menu items
$role = session()->get('role');
$userName = session()->get('username') ?? 'User';
$roleName = getRoleName();
?>

<div class="sidebar">
    <div class="logo">
        <h2>St. Peter Hospital</h2>
        <div class="toggle-btn"><i class="fas fa-bars"></i></div>
    </div>
    
    <!-- User Info -->
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-details">
            <div class="user-name"><?= esc($userName) ?></div>
            <div class="user-role"><?= esc($roleName) ?></div>
        </div>
    </div>
    
    <ul class="nav-menu">
        <!-- Dashboard - All roles -->
        <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>">
                <span class="text">Dashboard</span>
            </a>
        </li>

        <!-- Patients - Admin, Doctor, Nurse, Receptionist -->
        <?php if (hasPermission('patients')): ?>
        <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Patients</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                <?php if ($role !== 'doctor'): ?>
                    <?php if (hasPermission(['patients', 'administration'])): ?>
                    <li><a href="<?= base_url('patients/register') ?>"><span class="text">Register Outpatient</span></a></li>
                    <li><a href="<?= base_url('patients/inpatient') ?>"><span class="text">Register Inpatient</span></a></li>
                    <?php endif; ?>
                    <?php if (hasPermission(['patients', 'admissions'])): ?>
                    <li><a href="<?= base_url('admissions/create') ?>"><span class="text">Patient Admission</span></a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="<?= base_url('patients/view') ?>"><span class="text">View Patients</span></a></li>
                </ul>
        </li>
        <?php endif; ?>

        <!-- Appointments - Admin, Doctor, Nurse, Receptionist -->
        <?php if (hasPermission('appointments')): ?>
        <li class="nav-item expandable">
                <a href="#" onclick="toggleSubmenu(this)">
                    <span class="text">Appointments</span>
                    <span class="arrow">›</span>
                </a>
                <ul class="submenu">
                <?php if ($role !== 'doctor'): ?>
                    <?php if (hasPermission(['appointments', 'administration'])): ?>
                    <li><a href="<?= base_url('appointments/book') ?>"><span class="text">Book Appointment</span></a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="<?= base_url('appointments/list') ?>"><span class="text">Appointment List</span></a></li>
                    <?php if (hasPermission(['schedule', 'administration'])): ?>
                    <li><a href="<?= base_url('doctor/schedule') ?>"><span class="text">Doctor Schedule</span></a></li>
                    <?php endif; ?>
                </ul>
        </li>
        <?php endif; ?>


        <!-- Billing - Admin, Accounting only -->
        <?php if (hasPermission('billing') && !in_array($role, ['nurse', 'doctor'])): ?>
        <li class="nav-item expandable">
            <a href="#" onclick="toggleSubmenu(this)">
                <span class="text">Billing & Payment</span>
                <span class="arrow">›</span>
            </a>
            <ul class="submenu">
                <li><a href="<?= base_url('billing') ?>"><span class="text">Bill Management</span></a></li>
                <li><a href="<?= base_url('billing/process') ?>"><span class="text">Process Bill</span></a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Laboratory - Admin, Doctor, Nurse, Lab Staff -->
        <?php if (hasPermission('laboratory')): ?>
                <li class="nav-item expandable">
                    <a href="#" onclick="toggleSubmenu(this)">
                        <span class="text">Laboratory</span>
                        <span class="arrow">›</span>
                    </a>
                    <ul class="submenu">
                <?php if ($role !== 'doctor'): ?>
                        <li><a href="<?= base_url('laboratory/request') ?>"><span class="text">Lab Request</span></a></li>
                <?php endif; ?>
                        <li><a href="<?= base_url('laboratory/testresult') ?>"><span class="text">Test Results</span></a></li>
                    </ul>
                </li>
        <?php endif; ?>

        <!-- Pharmacy - Admin, Pharmacist only -->
        <?php if (hasPermission('pharmacy') && !in_array($role, ['nurse', 'doctor'])): ?>
        <li class="nav-item expandable">
            <a href="#" onclick="toggleSubmenu(this)">
                <span class="text">Pharmacy</span>
                <span class="arrow">›</span>
            </a>
            <ul class="submenu">
                <li><a href="<?= base_url('pharmacy/prescription') ?>"><span class="text">Prescription Dispensing</span></a></li>
                <li><a href="<?= base_url('pharmacy/transactions') ?>"><span class="text">Transactions</span></a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Inventory - Admin, Pharmacist only -->
        <?php if (hasPermission('inventory') && !in_array($role, ['nurse', 'doctor'])): ?>
            <li class="nav-item expandable">
            <a href="<?= base_url('pharmacy/medicine') ?>">
                <span class="text">Inventory Management</span>
                <span class="arrow">›</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Rooms - Admin, Nurse, Receptionist -->
        <?php if (hasPermission('rooms')): ?>
        <li class="nav-item expandable">
            <a href="#" onclick="toggleSubmenu(this)">
                <span class="text">Hospital Rooms</span>
                <span class="arrow">›</span>
            </a>
            <ul class="submenu">
                <li><a href="<?= base_url('rooms/general-inpatient') ?>"><span class="text">General Inpatient</span></a></li>
                <li><a href="<?= base_url('rooms/critical-care') ?>"><span class="text">Critical Care Units</span></a></li>
                <li><a href="<?= base_url('rooms/specialized') ?>"><span class="text">Specialized Rooms</span></a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Administration - Admin, IT Staff only -->
        <?php if (hasPermission('administration') && $role !== 'nurse'): ?>
        <li class="nav-item expandable">
            <a href="#" onclick="toggleSubmenu(this)">
                <span class="text">Administration</span>
                <span class="arrow">›</span>
            </a>
            <ul class="submenu">
                <li><a href="<?= base_url('admin/Administration/ManageUser') ?>"><span class="text">Manage Users</span></a></li>
                <li><a href="<?= base_url('admin/Administration/StaffManagement') ?>"><span class="text">Staff Management</span></a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Reports - All roles (with role-based filtering) -->
        <?php 
        // All authenticated roles can access reports, but with filtered report types
        $allowedRolesForReports = ['admin', 'accounting', 'accountant', 'itstaff', 'doctor', 'nurse', 'receptionist', 'labstaff', 'pharmacist'];
        if (in_array($role, $allowedRolesForReports)): ?>
            <li class="nav-item">
                <a href="<?= base_url('reports') ?>">
                    <span class="text">Reports</span>
                    <span class="arrow">›</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Logout -->
        <li class="nav-item">
            <a href="<?= base_url('logout') ?>">
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<script>
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
</script>
