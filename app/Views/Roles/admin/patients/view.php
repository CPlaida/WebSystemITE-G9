<?= $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Records<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $filters = [
        'inpatient' => ['label' => 'Inpatients', 'icon' => 'fa-procedures'],
        'outpatient' => ['label' => 'Outpatients', 'icon' => 'fa-user-check'],
        'admitted' => ['label' => 'Admitted Patients', 'icon' => 'fa-hospital-user'],
    ];
    $currentFilter = $currentFilter ?? 'inpatient';
    $isAdmitted = $currentFilter === 'admitted';
    $stats = $stats ?? ['total' => 0, 'inpatients' => 0, 'outpatients' => 0, 'admitted' => 0];
?>

<div class="main-content" id="mainContent">
    <div class="container">
        <div class="header">
            <h1 class="page-title">Patient Records</h1>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
            </div>
        <?php endif; ?>

        <div class="card patient-card">
            <div class="card-header organized-header flex-column flex-md-row">
                <div class="filter-buttons mb-3 mb-md-0">
                    <?php foreach ($filters as $key => $info): ?>
                        <a href="<?= base_url('admin/patients?filter=' . $key) ?>"
                           class="btn btn-sm <?= $currentFilter === $key ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas <?= esc($info['icon']) ?> me-1"></i> <?= esc($info['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="search-wrapper-inline">
                    <i class="fas fa-search search-icon-inline"></i>
                    <input type="text" id="searchInput" class="search-input-inline"
                           placeholder="Search by name, patient ID, ward, or status...">
                    <button type="button" class="search-clear-btn-inline" id="clearSearch" style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($patients)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No patients found for the selected filter.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" id="patientsTable">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th><?= $isAdmitted ? 'Ward / Room / Bed' : 'Address' ?></th>
                                    <th><?= $isAdmitted ? 'Physician' : 'Gender' ?></th>
                                    <th><?= $isAdmitted ? 'Admission Date' : 'DOB' ?></th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <?php
                                        $fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''));
                                        $contact = $patient['phone'] ?? '-';
                                        $address = $patient['address'] ?? 'N/A';
                                        $gender = isset($patient['gender']) ? ucfirst($patient['gender']) : '-';
                                        $dob = isset($patient['date_of_birth']) ? date('M d, Y', strtotime($patient['date_of_birth'])) : '-';
                                        $status = $isAdmitted ? ($patient['admission_status'] ?? 'admitted') : ($patient['status'] ?? 'active');
                                        $statusClass = in_array(strtolower($status), ['admitted', 'active'], true) ? 'badge-success' : 'badge-secondary';
                                        $wardRoom = trim(trim(($patient['admission_ward'] ?? $patient['bed_ward'] ?? '') . ' / ' . ($patient['admission_room'] ?? $patient['bed_room'] ?? '') . ' / ' . ($patient['bed_label'] ?? '')), ' /');
                                        $physician = $patient['physician_name'] ?? '—';
                                        $admissionDate = isset($patient['admission_date']) ? date('M d, Y', strtotime($patient['admission_date'])) : '-';
                                        $payload = [
                                            'id' => $patient['id'] ?? $patient['admission_patient_id'] ?? '-',
                                            'full_name' => $fullName ?: '—',
                                            'phone' => $contact,
                                            'email' => $patient['email'] ?? 'N/A',
                                            'address' => $address,
                                            'dob' => $dob,
                                            'gender' => $gender,
                                            'blood_type' => $patient['blood_type'] ?? 'N/A',
                                            'medical_history' => $patient['medical_history'] ?? 'No medical history recorded.',
                                            'insurance_provider' => $patient['insurance_provider'] ?? 'N/A',
                                            'insurance_number' => $patient['insurance_number'] ?? 'N/A',
                                            'ward' => $patient['admission_ward'] ?? $patient['bed_ward'] ?? '—',
                                            'room' => $patient['admission_room'] ?? $patient['bed_room'] ?? '—',
                                            'bed' => $patient['bed_label'] ?? '—',
                                            'physician' => $physician,
                                            'admission_type' => $patient['admission_type'] ?? '—',
                                            'admission_date' => $admissionDate,
                                        ];
                                    ?>
                                    <tr>
                                        <td><strong><?= esc($payload['id']) ?></strong></td>
                                        <td><?= esc($fullName ?: '—') ?></td>
                                        <td><?= esc($contact) ?></td>
                                        <td><?= $isAdmitted ? esc($wardRoom ?: '—') : esc($address) ?></td>
                                        <td><?= $isAdmitted ? esc($physician) : esc($gender) ?></td>
                                        <td><?= $isAdmitted ? esc($admissionDate) : esc($dob) ?></td>
                                        <td>
                                            <span class="badge <?= esc($statusClass) ?> px-3 py-2">
                                                <?= esc(ucfirst($status)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary view-patient-btn"
                                                data-patient='<?= esc(json_encode($payload, JSON_HEX_APOS | JSON_HEX_QUOT), 'attr') ?>'>
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="patientModal" style="display: none;">
    <div class="modal-content patient-modal-content">
        <div class="modal-header patient-modal-header">
            <div class="modal-title-wrapper">
                <i class="fas fa-user-circle modal-title-icon"></i>
                <div>
                    <h5 class="modal-title-main">Patient Snapshot</h5>
                    <p class="modal-subtitle">Demographics • Admission • Insurance</p>
                </div>
            </div>
            <button type="button" class="close-btn" onclick="closePatientModal()" aria-label="Close">×</button>
        </div>
        <div class="modal-body patient-modal-body">
            <div class="info-section-card">
                <h6><i class="fas fa-id-card"></i> Personal Information</h6>
                <div class="info-grid">
                    <div class="info-field"><label>Patient ID</label><p id="patient-id">-</p></div>
                    <div class="info-field"><label>Full Name</label><p id="patient-name">-</p></div>
                    <div class="info-field"><label>Gender</label><p id="patient-gender">-</p></div>
                    <div class="info-field"><label>Date of Birth</label><p id="patient-dob">-</p></div>
                    <div class="info-field"><label>Blood Type</label><p id="patient-blood-type">-</p></div>
                </div>
            </div>

            <div class="info-section-card">
                <h6><i class="fas fa-address-book"></i> Contact Details</h6>
                <div class="info-grid">
                    <div class="info-field"><label>Phone</label><p id="patient-contact">-</p></div>
                    <div class="info-field"><label>Address</label><p id="patient-address">-</p></div>
                </div>
            </div>

            <?php if ($isAdmitted): ?>
            <div class="info-section-card">
                <h6><i class="fas fa-hospital"></i> Admission Overview</h6>
                <div class="info-grid">
                    <div class="info-field"><label>Ward</label><p id="patient-ward">-</p></div>
                    <div class="info-field"><label>Room</label><p id="patient-room">-</p></div>
                    <div class="info-field"><label>Bed</label><p id="patient-bed">-</p></div>
                    <div class="info-field"><label>Physician</label><p id="patient-physician">-</p></div>
                    <div class="info-field"><label>Admission Type</label><p id="patient-admission-type">-</p></div>
                    <div class="info-field"><label>Admission Date</label><p id="patient-admission-date">-</p></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="info-section-card">
                <h6><i class="fas fa-file-invoice"></i> Insurance</h6>
                <div class="info-grid">
                    <div class="info-field"><label>Provider</label><p id="patient-insurance-provider">-</p></div>
                    <div class="info-field"><label>Policy No.</label><p id="patient-insurance-number">-</p></div>
                </div>
            </div>

            <div class="info-section-card">
                <h6><i class="fas fa-notes-medical"></i> Medical History</h6>
                <div class="info-field-full">
                    <p id="patient-medical-history">-</p>
                </div>
            </div>
        </div>
        <div class="modal-footer patient-modal-footer">
            <button type="button" class="btn-close-modal" onclick="closePatientModal()">
                <i class="fas fa-times me-2"></i>Close
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.view-patient-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const payload = this.getAttribute('data-patient');
                if (!payload) {
                    alert('No patient data available');
                    return;
                }
                try {
                    const patient = JSON.parse(payload);
                    fillPatientModal(patient);
                    openPatientModal();
                } catch (e) {
                    console.error('Failed to parse patient payload', e);
                    alert('Unable to load patient details');
                }
            });
        });
    });

    function fillPatientModal(data) {
        document.getElementById('patient-id').innerText = data.id || '-';
        document.getElementById('patient-name').innerText = data.full_name || '-';
        document.getElementById('patient-contact').innerText = data.phone || '-';
        document.getElementById('patient-address').innerText = data.address || '-';
        document.getElementById('patient-dob').innerText = data.dob || '-';
        document.getElementById('patient-gender').innerText = data.gender || '-';
        document.getElementById('patient-blood-type').innerText = data.blood_type || '-';
        document.getElementById('patient-medical-history').innerText = data.medical_history || '-';
        document.getElementById('patient-insurance-provider').innerText = data.insurance_provider || '-';
        document.getElementById('patient-insurance-number').innerText = data.insurance_number || '-';
        <?php if ($isAdmitted): ?>
            document.getElementById('patient-ward').innerText = data.ward || '-';
            document.getElementById('patient-room').innerText = data.room || '-';
            document.getElementById('patient-bed').innerText = data.bed || '-';
            document.getElementById('patient-physician').innerText = data.physician || '-';
            document.getElementById('patient-admission-type').innerText = data.admission_type || '-';
            document.getElementById('patient-admission-date').innerText = data.admission_date || '-';
        <?php endif; ?>
    }

    function openPatientModal() {
        const modal = document.getElementById('patientModal');
        if (modal) modal.style.display = 'flex';
    }

    function closePatientModal() {
        const modal = document.getElementById('patientModal');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('patientModal');
        if (event.target === modal) {
            closePatientModal();
        }
    });
</script>
<?= $this->endSection() ?>
