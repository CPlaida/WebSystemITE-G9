<?= $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Records<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $filters = [
        'inpatient' => ['label' => 'Inpatients', 'icon' => 'fa-procedures'],
        'outpatient' => ['label' => 'Outpatients', 'icon' => 'fa-user-check'],
        'admitted' => ['label' => 'Admitted Patients', 'icon' => 'fa-hospital-user'],
        'discharged' => ['label' => 'Discharged Patients', 'icon' => 'fa-user-check'],
    ];
    $currentFilter = $currentFilter ?? 'inpatient';
    $isAdmitted = $currentFilter === 'admitted';
    $isDischargedView = $currentFilter === 'discharged';
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
                                    <th><?= $isAdmitted || $isDischargedView ? 'Admission Date' : 'DOB' ?></th>
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
                                            'emergency_contact' => $patient['emergency_contact'] ?? 'N/A',
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
                                        <td><?= $isAdmitted || $isDischargedView ? esc($admissionDate) : esc($dob) ?></td>
                                        <td>
                                            <span class="badge <?= esc($statusClass) ?> px-3 py-2">
                                                <?= esc(ucfirst($status)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-primary view-patient-btn"
                                                    data-patient='<?= esc(json_encode($payload, JSON_HEX_APOS | JSON_HEX_QUOT), 'attr') ?>'>
                                                    <i class="fas fa-notes-medical me-1"></i>View
                                                </button>
                                                <?php if ($isAdmitted && !empty($patient['admission_id'])): ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger discharge-btn"
                                                        data-admission-id="<?= esc($patient['admission_id']) ?>"
                                                        data-patient-name="<?= esc($fullName ?: 'this patient') ?>">
                                                        <i class="fas fa-bed me-1"></i>Discharge
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

<style>
    #ehrModal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 1rem;
        z-index: 1100;
        overflow-y: auto;
    }

    #ehrModal .ehr-panel {
        width: min(1100px, 100%);
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
        animation: ehrSlideIn 0.3s ease;
        overflow: hidden;
    }

    @keyframes ehrSlideIn {
        from {
            transform: translateY(24px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    #ehrModal .ehr-panel__header {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    #ehrModal .ehr-panel__eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-size: 0.75rem;
        opacity: 0.85;
        display: block;
    }

    #ehrModal .ehr-panel__title {
        margin: 0.2rem 0 0;
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
    }

    #ehrModal .close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        font-size: 1.1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    #ehrModal .close-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    #ehrModal .ehr-panel__body {
        padding: 1.75rem 2rem 2rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        background: #fff;
    }

    .ehr-summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .ehr-summary-top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .ehr-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: #e0e7ff;
        color: #1d4ed8;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ehr-bloodtype {
        background: #fff;
        border: 1px dashed #c7d2fe;
        border-radius: 14px;
        padding: 0.75rem 1.25rem;
        min-width: 140px;
        text-align: center;
    }

    .ehr-bloodtype p {
        margin: 0;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .ehr-bloodtype strong {
        display: block;
        font-size: 1.75rem;
        color: #0f172a;
    }

    .ehr-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .ehr-detail-item {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-height: 86px;
    }

    .ehr-detail-item.detail-wide {
        grid-column: span 2;
    }

    @media (max-width: 640px) {
        .ehr-detail-item.detail-wide {
            grid-column: span 1;
        }
    }

    .ehr-detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
    }

    .ehr-detail-value {
        margin: 0;
        font-weight: 600;
        color: #0f172a;
    }

    .ehr-detail-value.muted {
        font-weight: 500;
        color: #475569;
    }

    .ehr-tabs {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
    }

    .ehr-tabs .tabs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        padding: 0.35rem;
        background: #f8fafc;
        gap: 0.35rem;
    }

    .ehr-tabs .tab-btn {
        border: none;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        background: transparent;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
    }

    .ehr-tabs .tab-btn.active {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
    }

    .ehr-tabs .tab-content {
        padding: 1.25rem 1.5rem 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .ehr-table-wrapper { width: 100%; overflow-x: auto; }
    .ehr-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
    .ehr-table thead th {
        background: #f1f5f9;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.75rem;
        padding: 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .ehr-table tbody td {
        padding: 0.9rem;
        border-bottom: 1px solid #f1f5f9;
        color: #0f172a;
    }
    .ehr-table tbody tr:hover { background: #f8fafc; }

    .ehr-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .ehr-status-badge.completed { background: #c6f6d5; color: #22543d; }
    .ehr-status-badge.in-progress { background: #dbeafe; color: #1d4ed8; }
    .ehr-status-badge.pending { background: #fef3c7; color: #92400e; }
    .ehr-status-badge.admitted { background: #d1fae5; color: #065f46; }
    .ehr-status-badge.discharged { background: #dbeafe; color: #1e40af; }
    .ehr-status-badge.cancelled { background: #fee2e2; color: #b91c1c; }

    .ehr-empty-state {
        text-align: center;
        padding: 1.5rem 0;
        color: #94a3b8;
        font-weight: 500;
    }
    .ehr-empty-state.error { color: #dc2626; }

    .ehr-vitals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    .ehr-vital-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem;
        background: #fff;
    }
    .ehr-vital-card h4 {
        margin: 0.35rem 0 0;
        font-size: 1.4rem;
        color: #0f172a;
    }
    .ehr-vitals-status {
        margin-top: 1rem;
        font-size: 0.85rem;
        color: #64748b;
    }

    @media (max-width: 768px) {
        #ehrModal .ehr-panel__header { padding: 1.25rem; }
        #ehrModal .ehr-panel__body { padding: 1.25rem; }
    }
</style>

<div class="modal" id="ehrModal">
    <div class="ehr-panel">
        <div class="ehr-panel__header">
            <div>
                <span class="ehr-panel__eyebrow">Electronic Health Record</span>
                <h4 class="ehr-panel__title">Patient overview</h4>
                <div class="mt-2" style="display:flex; flex-wrap:wrap; gap:0.45rem;">
                    <span class="ehr-chip"><i class="fas fa-id-card"></i> <span id="ehrPatientId">-</span></span>
                    <span class="ehr-chip" style="background:#ccfbf1; color:#0f766e;">
                        <i class="fas fa-calendar-day"></i> Recorded <span id="ehrDate">-</span>
                    </span>
                </div>
            </div>
            <button type="button" class="close-btn" onclick="closeModal()">×</button>
        </div>
        <div class="ehr-panel__body">
            <div class="ehr-summary-card">
                <div class="ehr-summary-top" style="width:100%;">
                    <div>
                        <p class="ehr-detail-label" style="margin-bottom:0.35rem;">Primary Details</p>
                        <p class="ehr-detail-value muted" id="ehrName">-</p>
                    </div>
                    <div class="ehr-bloodtype">
                        <p>Blood Type</p>
                        <strong id="ehrBloodType">-</strong>
                    </div>
                </div>
                <div class="ehr-details-grid">
                    <div class="ehr-detail-item">
                        <span class="ehr-detail-label">Mobile</span>
                        <p class="ehr-detail-value" id="ehrMobile">-</p>
                    </div>
                    <div class="ehr-detail-item">
                        <span class="ehr-detail-label">Email</span>
                        <p class="ehr-detail-value" id="ehrEmail">-</p>
                    </div>
                    <div class="ehr-detail-item">
                        <span class="ehr-detail-label">Date of Birth</span>
                        <p class="ehr-detail-value" id="ehrDOB">-</p>
                    </div>
                    <div class="ehr-detail-item">
                        <span class="ehr-detail-label">Gender</span>
                        <p class="ehr-detail-value" id="ehrGender">-</p>
                    </div>
                    <div class="ehr-detail-item detail-wide">
                        <span class="ehr-detail-label">Address</span>
                        <p class="ehr-detail-value" id="ehrAddress">-</p>
                    </div>
                    <div class="ehr-detail-item">
                        <span class="ehr-detail-label">Emergency Contact</span>
                        <p class="ehr-detail-value" id="ehrEmergencyContact">-</p>
                    </div>
                    <div class="ehr-detail-item detail-wide">
                        <span class="ehr-detail-label">Medical History & Allergies</span>
                        <p class="ehr-detail-value muted" id="ehrAilment">-</p>
                    </div>
                </div>
            </div>

            <div class="ehr-tabs">
                <div class="tabs">
                    <button class="tab-btn active" onclick="openTab(event,'medical-records')">
                        <i class="fas fa-file-medical"></i> Medical Records
                    </button>
                    <button class="tab-btn" onclick="openTab(event,'vitals')">
                        <i class="fas fa-heartbeat"></i> Vitals
                    </button>
                    <button class="tab-btn" onclick="openTab(event,'lab')">
                        <i class="fas fa-flask"></i> Lab Records
                    </button>
                </div>
                <div id="medical-records" class="tab-content">
                    <div id="ehrMedicalRecords" class="ehr-table-wrapper">
                        <div class="ehr-empty-state">Select a patient to load admissions history.</div>
                    </div>
                </div>
                <div id="vitals" class="tab-content" style="display:none;">
                    <div class="ehr-vitals-grid">
                        <div class="ehr-vital-card">
                            <span class="ehr-detail-label">Blood Pressure</span>
                            <h4 id="ehrVitalsBp">-</h4>
                        </div>
                        <div class="ehr-vital-card">
                            <span class="ehr-detail-label">Heart Rate (bpm)</span>
                            <h4 id="ehrVitalsHr">-</h4>
                        </div>
                        <div class="ehr-vital-card">
                            <span class="ehr-detail-label">Temperature (°C)</span>
                            <h4 id="ehrVitalsTemp">-</h4>
                        </div>
                        <div class="ehr-vital-card">
                            <span class="ehr-detail-label">Last Updated</span>
                            <h4 id="ehrVitalsUpdated">-</h4>
                        </div>
                    </div>
                    <p id="vitalsStatus" class="ehr-vitals-status">Select a patient to load vitals.</p>
                </div>
                <div id="lab" class="tab-content" style="display:none;">
                    <div id="ehrLabContainer" class="ehr-table-wrapper">
                        <div class="ehr-empty-state">Select a patient to load lab records.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');

        const bindViewButtons = (scope = document) => {
            scope.querySelectorAll('.view-patient-btn').forEach(btn => {
                if (btn.dataset.viewBound === 'true') {
                    return;
                }
                btn.dataset.viewBound = 'true';
                btn.addEventListener('click', function () {
                    const payload = this.getAttribute('data-patient');
                    if (!payload) {
                        alert('No patient data available');
                        return;
                    }
                    try {
                        const patient = JSON.parse(payload);
                        viewPatient(
                            patient.full_name || '-',
                            patient.phone || '-',
                            patient.address || '-',
                            patient.dob || '-',
                            patient.gender || '-',
                            patient.medical_history || '-',
                            patient.id || '-',
                            patient.email || '-',
                            patient.blood_type || '-',
                            patient.emergency_contact || '-'
                        );
                    } catch (e) {
                        console.error('Failed to parse patient payload', e);
                        alert('Unable to load patient details');
                    }
                });
            });
        };

        const bindDischargeButtons = (scope = document) => {
            scope.querySelectorAll('.discharge-btn').forEach(btn => {
                if (btn.dataset.dischargeBound === 'true') {
                    return;
                }
                btn.dataset.dischargeBound = 'true';
                btn.addEventListener('click', () => handleDischarge(btn));
            });
        };

        bindViewButtons();
        bindDischargeButtons();

        if (searchInput) {
            searchInput.addEventListener('input', filterPatients);
        }
        if (clearSearchBtn && searchInput) {
            clearSearchBtn.addEventListener('click', function () {
                searchInput.value = '';
                clearSearchBtn.style.display = 'none';
                filterPatients();
            });
            searchInput.addEventListener('input', function () {
                clearSearchBtn.style.display = searchInput.value ? 'inline-flex' : 'none';
            });
        }
    });

    async function handleDischarge(button) {
        const admissionId = button?.dataset?.admissionId;
        const patientName = button?.dataset?.patientName || 'this patient';
        if (!admissionId) {
            alert('Missing admission information.');
            return;
        }

        if (!confirm(`Discharge ${patientName}?`)) {
            return;
        }

        const url = `<?= base_url('admin/patients/admission') ?>/${encodeURIComponent(admissionId)}/discharge`;
        const row = button.closest('tr');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ admission_id: admissionId })
            });

            const data = await response.json();
            if (!response.ok || !data?.success) {
                throw new Error(data?.message || 'Failed to discharge patient.');
            }

            if (row) {
                row.remove();
            }
            showFlashMessage('success', data.message || 'Patient discharged successfully.');
        } catch (error) {
            console.error('Discharge error:', error);
            showFlashMessage('error', error.message || 'Failed to discharge patient.');
            button.disabled = false;
            button.innerHTML = originalText;
            return;
        }
    }

    function showFlashMessage(type, message) {
        const container = document.createElement('div');
        container.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        container.role = 'alert';
        container.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>`;

        const mainContainer = document.querySelector('.container');
        if (mainContainer) {
            mainContainer.insertBefore(container, mainContainer.firstChild);
            setTimeout(() => container.classList.remove('show'), 4000);
        } else {
            alert(message);
        }
    }

    const escapeHtml = (value) => {
        if (value === null || value === undefined) {
            return '—';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const formatFullDate = (dateStr, timeStr = '') => {
        if (!dateStr) return '—';
        const iso = `${dateStr}${timeStr ? `T${timeStr}` : ''}`;
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) {
            return escapeHtml(`${dateStr}${timeStr ? ` ${timeStr}` : ''}`);
        }
        return date.toLocaleString();
    };

    const buildStatusBadge = (statusKey, labels) => {
        const normalized = (statusKey || '').toLowerCase();
        const meta = labels[normalized] || labels.default;
        return `<span class="ehr-status-badge ${meta.className}">${meta.label}</span>`;
    };

    function viewPatient(name, mobile, address, dob, gender, medicalHistory, patientId, email, bloodType, emergencyContact) {
        document.getElementById('ehrName').innerText = name;
        document.getElementById('ehrMobile').innerText = mobile;
        document.getElementById('ehrAddress').innerText = address;
        document.getElementById('ehrDOB').innerText = dob;
        document.getElementById('ehrGender').innerText = gender;
        document.getElementById('ehrAilment').innerText = medicalHistory;
        document.getElementById('ehrDate').innerText = new Date().toLocaleDateString();

        document.getElementById('ehrPatientId').innerText = patientId;
        document.getElementById('ehrEmail').innerText = email;
        document.getElementById('ehrBloodType').innerText = bloodType;
        document.getElementById('ehrEmergencyContact').innerText = emergencyContact;

        document.getElementById('ehrModal').style.display = 'flex';

        loadLabRecords(patientId, name);
        loadVitals(patientId);
        loadMedicalRecords(patientId);
    }

    function closeModal() {
        document.getElementById('ehrModal').style.display = 'none';
    }

    function openTab(evt, tabName) {
        const content = document.getElementsByClassName('tab-content');
        for (let i = 0; i < content.length; i++) {
            content[i].style.display = 'none';
        }
        const btns = document.getElementsByClassName('tab-btn');
        for (let i = 0; i < btns.length; i++) {
            btns[i].classList.remove('active');
        }
        document.getElementById(tabName).style.display = 'block';
        evt.currentTarget.classList.add('active');

        const pid = document.getElementById('ehrPatientId').innerText.trim();
        const pname = document.getElementById('ehrName').innerText.trim();

        if (tabName === 'lab') {
            loadLabRecords(pid || '', pname);
        }
        if (tabName === 'vitals') {
            loadVitals(pid);
        }
        if (tabName === 'medical-records') {
            loadMedicalRecords(pid);
        }
    }

    function loadLabRecords(patientId, name) {
        const cont = document.getElementById('ehrLabContainer');
        if (!cont) return;
        cont.innerHTML = '<div class="ehr-empty-state">Loading lab records...</div>';

        const params = new URLSearchParams();
        if (name) params.append('name', name);
        if (patientId) params.append('patient_id', String(patientId));

        fetch('<?= base_url('laboratory/patient/lab-records') ?>?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    cont.innerHTML = '<div class="ehr-empty-state error">Failed to load lab records.</div>';
                    return;
                }
                const rows = Array.isArray(data.records) ? data.records : [];
                if (rows.length === 0) {
                    cont.innerHTML = '<div class="ehr-empty-state">No lab records found.</div>';
                    return;
                }

                const statusLabels = {
                    completed: { label: 'Completed', className: 'completed' },
                    in_progress: { label: 'In progress', className: 'in-progress' },
                    pending: { label: 'Pending', className: 'pending' },
                    default: { label: 'Pending', className: 'pending' },
                };

                const htmlRows = rows.map((r) => {
                    const date = r.test_date ? formatFullDate(r.test_date) : '—';
                    const test = escapeHtml(r.test_type || '—');
                    const notesRaw = r.notes ? String(r.notes).trim() : '—';
                    const cappedNotes = notesRaw.length > 140 ? `${notesRaw.substring(0, 137)}…` : notesRaw;
                    const statusKey = (r.status || 'pending').toLowerCase();
                    const badge = buildStatusBadge(statusKey, statusLabels);
                    const viewUrl = '<?= base_url('laboratory/testresult/view/') ?>' + (r.id || '');
                    const actionBtn = statusKey === 'completed'
                        ? `<a href="${viewUrl}" class="btn btn-sm btn-primary">View</a>`
                        : '<span class="ehr-detail-label" style="text-transform:none; color:#94a3b8;">Awaiting results</span>';

                    return `<tr>
                        <td>${date}</td>
                        <td>${test}</td>
                        <td>${badge}</td>
                        <td>${escapeHtml(cappedNotes)}</td>
                        <td>${actionBtn}</td>
                    </tr>`;
                }).join('');

                cont.innerHTML = `
                    <table class="ehr-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Test</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>${htmlRows}</tbody>
                    </table>`;
            })
            .catch(() => { cont.innerHTML = '<div class="ehr-empty-state error">Error loading lab records.</div>'; });
    }

    async function loadVitals(patientId) {
        const status = document.getElementById('vitalsStatus');
        if (status) {
            status.textContent = 'Loading vitals...';
        }
        try {
            const res = await fetch(`<?= base_url('doctor/vitals') ?>?patient_id=${encodeURIComponent(patientId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                if (status) status.textContent = data && data.message ? data.message : 'Failed to load vitals';
                return;
            }
            const v = data.vitals || null;
            document.getElementById('ehrVitalsBp').innerText = v && v.blood_pressure ? v.blood_pressure : '-';
            document.getElementById('ehrVitalsHr').innerText = v && v.heart_rate ? v.heart_rate : '-';
            document.getElementById('ehrVitalsTemp').innerText = v && v.temperature ? v.temperature : '-';
            document.getElementById('ehrVitalsUpdated').innerText = v && v.created_at ? (new Date(v.created_at)).toLocaleString() : '-';

            const bpInput = document.getElementById('vitalBpInput');
            const hrInput = document.getElementById('vitalHrInput');
            const tempInput = document.getElementById('vitalTempInput');
            if (bpInput) bpInput.value = v && v.blood_pressure ? v.blood_pressure : '';
            if (hrInput) hrInput.value = v && v.heart_rate ? v.heart_rate : '';
            if (tempInput) tempInput.value = v && v.temperature ? v.temperature : '';

            if (status) {
                status.textContent = v ? 'Latest vitals loaded.' : 'No vitals recorded yet.';
            }
        } catch (e) {
            if (status) status.textContent = 'Error loading vitals';
        }
    }

    async function loadMedicalRecords(patientId) {
        const container = document.getElementById('ehrMedicalRecords');
        if (!container) return;
        container.innerHTML = '<div class="ehr-empty-state">Loading admissions history...</div>';

        try {
            const res = await fetch(`<?= base_url('doctor/medical-records') ?>?patient_id=${encodeURIComponent(patientId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                container.innerHTML = `<div class="ehr-empty-state error">${(data && data.message) || 'Failed to load medical records.'}</div>`;
                return;
            }

            const records = Array.isArray(data.records) ? data.records : [];
            if (records.length === 0) {
                container.innerHTML = '<div class="ehr-empty-state">No admissions recorded for this patient.</div>';
                return;
            }
            const statusLabels = {
                admitted: { label: 'Admitted', className: 'admitted' },
                discharged: { label: 'Discharged', className: 'discharged' },
                cancelled: { label: 'Cancelled', className: 'cancelled' },
                default: { label: 'Admitted', className: 'admitted' },
            };

            const rows = records.map((rec, idx) => {
                const status = (rec.status || 'admitted').toLowerCase();
                const admissionDate = formatFullDate(rec.admission_date, rec.admission_time || '00:00:00');
                const dischargeDate = rec.discharge_date ? formatFullDate(rec.discharge_date) : (status === 'admitted' ? 'Currently admitted' : '—');
                const locationParts = [rec.ward, rec.room, rec.bed].filter(Boolean).map(escapeHtml);
                const location = locationParts.length ? locationParts.join(' / ') : '—';

                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${admissionDate}</td>
                        <td>${dischargeDate}</td>
                        <td>${escapeHtml(rec.admission_type ? rec.admission_type.charAt(0).toUpperCase() + rec.admission_type.slice(1) : '—')}</td>
                        <td>${escapeHtml(rec.physician || '—')}</td>
                        <td>${location}</td>
                        <td>${escapeHtml(rec.diagnosis || '—')}</td>
                        <td>${escapeHtml(rec.reason || '—')}</td>
                        <td>${buildStatusBadge(status, statusLabels)}</td>
                    </tr>`;
            }).join('');

            container.innerHTML = `
                <table class="ehr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Admission</th>
                            <th>Discharge</th>
                            <th>Type</th>
                            <th>Physician</th>
                            <th>Ward / Room / Bed</th>
                            <th>Diagnosis</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>`;
        } catch (e) {
            container.innerHTML = '<div class="ehr-empty-state error">Error loading medical records.</div>';
        }
    }

    window.onclick = function (event) {
        const modal = document.getElementById('ehrModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    function filterPatients() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;
        const searchTerm = searchInput.value.toLowerCase();
        const tableRows = document.querySelectorAll('#patientsTable tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
</script>
<?= $this->endSection() ?>
