<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>
    Staff Dashboard
<?= $this->endSection() ?>

<?= $this->section('sidebar') ?>
    <li class="nav-item active"><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="nav-item"><a href="#"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
    <li class="nav-item"><a href="#"><i class="fas fa-tasks"></i> My Tasks</a></li>
    <li class="nav-item"><a href="<?= site_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1>Staff Dashboard</h1>
    <!-- Content for Staff -->
<?= $this->endSection() ?>
