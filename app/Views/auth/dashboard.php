<?= $this->extend('partials/header') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$role = session('role') ?? null;
$name = session('name') ?? 'User';

switch ($role) {
    case 'admin':
        echo view('Roles/admin/dashboard', ['name' => $name]);
        break;
    case 'doctor':
        echo view('Roles/doctor/dashboard', ['name' => $name]);
        break;
    case 'nurse':
        echo view('Roles/nurse/dashboard', ['name' => $name]);
        break;
    case 'receptionist':
        echo view('Roles/reception/dashboard', ['name' => $name]);
        break;
    case 'accounting':
        echo view('Roles/Accountant/dashboard', ['name' => $name]);
        break;
    case 'pharmacist':
        echo view('Roles/pharmacy/dashboard', ['name' => $name]);
        break;
    case 'labstaff':
        echo view('Roles/lab_staff/dashboard', ['name' => $name]);
        break;
    case 'itstaff':
        echo view('Roles/IT_Staff/dashboard', ['name' => $name]);
        break;
    default:
        echo view('errors/html/error_403');
        break;
}
?>
<?= $this->endSection() ?>