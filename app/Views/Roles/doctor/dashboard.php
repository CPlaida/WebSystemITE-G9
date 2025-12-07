<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Doctor Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Initialize doctor-specific metrics
$appointmentsCount = 0;
$patientsSeenToday = 0;
$pendingLabResults = 0;
$prescriptionsCount = 0;

try {
    $db = \Config\Database::connect();
    $userId = session()->get('user_id');
    $doctorId = null;
    
    // Get doctor ID from user_id
    if ($userId && $db->tableExists('doctors')) {
        $doctorRow = $db->table('doctors')->select('id')->where('user_id', $userId)->get()->getRowArray();
        if ($doctorRow) {
            $doctorId = $doctorRow['id'];
        }
    }

    // Today's appointments for this doctor only
    if ($db->tableExists('appointments') && $doctorId) {
        $today = date('Y-m-d');
        $fields = $db->getFieldData('appointments');
        $dateCol = null; $statusCol = null; $doctorCol = null;
        foreach ($fields as $f) {
            $n = strtolower($f->name ?? '');
            if (in_array($n, ['date', 'appointment_date', 'scheduled_at'])) $dateCol = $f->name;
            if ($n === 'status') $statusCol = $f->name;
            if (in_array($n, ['doctor_id', 'doctor', 'physician_id'])) $doctorCol = $f->name;
        }
        if ($dateCol && $doctorCol) {
            $start = date('Y-m-d');
            $end = date('Y-m-d', strtotime('+1 day'));
            $qbA = $db->table('appointments')->select('COUNT(*) AS c')
                      ->where($doctorCol, $doctorId)
                      ->where("$dateCol >=", $start)
                      ->where("$dateCol <", $end);
            if ($statusCol) {
                $qbA->whereNotIn($statusCol, ['cancelled','no_show']);
            }
            $row = $qbA->get()->getRowArray();
            $appointmentsCount = (int)($row['c'] ?? 0);
            if ($appointmentsCount === 0) {
                $qbB = $db->table('appointments')->select('COUNT(*) AS c')
                          ->where($doctorCol, $doctorId)
                          ->where($dateCol, $today);
                if ($statusCol) {
                    $qbB->whereNotIn($statusCol, ['cancelled','no_show']);
                }
                $rowB = $qbB->get()->getRowArray();
                $appointmentsCount = (int)($rowB['c'] ?? 0);
            }
        }
    }

    // Patients seen today (appointments completed/confirmed/in_progress)
    if ($doctorId && $db->tableExists('appointments')) {
        $today = date('Y-m-d');
        $fields = $db->getFieldData('appointments');
        $dateCol = null; $statusCol = null; $doctorCol = null;
        foreach ($fields as $f) {
            $n = strtolower($f->name ?? '');
            if (in_array($n, ['date', 'appointment_date', 'scheduled_at'])) $dateCol = $f->name;
            if ($n === 'status') $statusCol = $f->name;
            if (in_array($n, ['doctor_id', 'doctor', 'physician_id'])) $doctorCol = $f->name;
        }
        if ($dateCol && $doctorCol) {
            $qb = $db->table('appointments')->select('COUNT(*) AS c')
                      ->where($doctorCol, $doctorId)
                      ->where("DATE($dateCol)", $today);
            if ($statusCol) {
                $qb->whereIn($statusCol, ['completed', 'confirmed', 'in_progress']);
            }
            $row = $qb->get()->getRowArray();
            $patientsSeenToday = (int)($row['c'] ?? 0);
        }
    }

    // Pending lab results for doctor
    if ($doctorId && $db->tableExists('laboratory_requests')) {
        $fields = $db->getFieldData('laboratory_requests');
        $statusCol = null; $doctorCol = null;
        foreach ($fields as $f) {
            $n = strtolower($f->name ?? '');
            if ($n === 'status') $statusCol = $f->name;
            if (in_array($n, ['doctor_id', 'requested_by', 'physician_id'])) $doctorCol = $f->name;
        }
        if ($statusCol && $doctorCol) {
            $qb = $db->table('laboratory_requests')->select('COUNT(*) AS c')
                      ->where($doctorCol, $doctorId)
                      ->whereIn($statusCol, ['pending', 'in_progress']);
            $row = $qb->get()->getRowArray();
            $pendingLabResults = (int)($row['c'] ?? 0);
        }
    }

    // Prescriptions count for doctor
    if ($doctorId && $db->tableExists('prescriptions')) {
        $fields = $db->getFieldData('prescriptions');
        $doctorCol = null;
        foreach ($fields as $f) {
            $n = strtolower($f->name ?? '');
            if (in_array($n, ['doctor_id', 'prescribed_by', 'physician_id'])) $doctorCol = $f->name;
        }
        if ($doctorCol) {
            $qb = $db->table('prescriptions')->select('COUNT(*) AS c')->where($doctorCol, $doctorId);
            $row = $qb->get()->getRowArray();
            $prescriptionsCount = (int)($row['c'] ?? 0);
        }
    }
} catch (\Throwable $e) { /* ignore */ }
?>

<div class="container-fluid py-4">
    <div class="composite-card billing-card dashboard-overview" style="margin-top:0; margin-bottom: 1.5rem;">
        <div class="composite-header">
            <div class="composite-title">Dashboard Overview</div>
        </div>
        <div class="admin-grid" style="padding: 1.25rem;">
            <div class="kpi-card span-3">
                <div class="kpi-title">Today's Appointments</div>
                <div class="kpi-value"><?= $appointmentsCount ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Patients Seen</div>
                <div class="kpi-value"><?= $patientsSeenToday ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Pending Results</div>
                <div class="kpi-value"><?= $pendingLabResults ?? '0' ?></div>
            </div>
            
            <div class="kpi-card span-3">
                <div class="kpi-title">Prescriptions</div>
                <div class="kpi-value"><?= $prescriptionsCount ?? '0' ?></div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?= $this->endSection() ?>
