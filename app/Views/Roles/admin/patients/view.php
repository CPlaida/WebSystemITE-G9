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

<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Patient Records</h1>
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

        <div class="card-body">
            <div class="organized-header flex-column flex-md-row" style="margin-bottom: 1.5rem;">
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
                                            <div class="action-buttons-wrapper">
                                                <button type="button" class="btn btn-primary view-patient-btn"
                                                    data-patient='<?= esc(json_encode($payload, JSON_HEX_APOS | JSON_HEX_QUOT), 'attr') ?>'>
                                                    <i class="fas fa-notes-medical me-2"></i>View
                                                </button>
                                                <?php if ($isAdmitted && !empty($patient['admission_id'])): ?>
                                                    <button type="button"
                                                        class="btn btn-outline-danger discharge-btn"
                                                        data-admission-id="<?= esc($patient['admission_id']) ?>"
                                                        data-patient-name="<?= esc($fullName ?: 'this patient') ?>">
                                                        <i class="fas fa-sign-out-alt me-2"></i>Discharge
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


<div class="modal" id="ehrModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Electronic Health Records</h5>
            <button class="close-btn" onclick="closeModal()">×</button>
        </div>
        <div class="ehr-container">
            <div class="ehr-info">
                <p><b>Patient ID:</b> <span id="ehrPatientId">-</span></p>
                <p><b>Full Name:</b> <span id="ehrName">-</span></p>
                <p><b>Mobile:</b> <span id="ehrMobile">-</span></p>
                <p><b>Email:</b> <span id="ehrEmail">-</span></p>
                <p><b>Address:</b> <span id="ehrAddress">-</span></p>
                <p><b>Date of Birth:</b> <span id="ehrDOB">-</span></p>
                <p><b>Gender:</b> <span id="ehrGender">-</span></p>
                <p><b>Blood Type:</b> <span id="ehrBloodType">-</span></p>
                <p><b>Emergency Contact:</b> <span id="ehrEmergencyContact">-</span></p>
                <p><b>Medical History:</b> <span id="ehrAilment">-</span></p>
                <p><b>Date Recorded:</b> <span id="ehrDate">-</span></p>
            </div>
            <div class="ehr-tabs">
                <div class="tabs">
                    <button class="tab-btn active" onclick="openTab(event,'medical-records')">Medical Records</button>
                    <button class="tab-btn" onclick="openTab(event,'vitals')">Vitals</button>
                    <button class="tab-btn" onclick="openTab(event,'lab')">Lab Records</button>
                </div>
                <div id="medical-records" class="tab-content">
                    <div id="ehrMedicalRecords" style="padding:8px 0; color:#2c3e50; font-size:14px;">
                        <em>Select a patient to load admissions history.</em>
                    </div>
                </div>
                <div id="vitals" class="tab-content" style="display:none;">
                    <div class="vitals-section">
                        <h6>
                            <i class="fas fa-heartbeat"></i> Vitals
                        </h6>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px 20px;">
                            <div><strong>Blood Pressure:</strong> <span id="ehrVitalsBp">-</span></div>
                            <div><strong>Heart Rate (bpm):</strong> <span id="ehrVitalsHr">-</span></div>
                            <div><strong>Temperature (°C):</strong> <span id="ehrVitalsTemp">-</span></div>
                            <div><strong>Last Updated:</strong> <span id="ehrVitalsUpdated">-</span></div>
                        </div>
                    </div>
                </div>
                <div id="lab" class="tab-content" style="display:none;">
                    <div id="ehrLabContainer" style="min-height:120px; padding:6px 0; color:#2c3e50; font-size:14px;">
                        <em>Select a patient to load lab records.</em>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');

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
        if (!dateStr || dateStr === '—' || dateStr === '-') return '—';
        
        // Handle if dateStr is just a number (like "04")
        if (/^\d{1,2}$/.test(dateStr.trim())) {
            return dateStr; // Return as-is if it's just a day number
        }
        
        // Try to parse the date
        let date;
        if (timeStr) {
            const iso = `${dateStr}T${timeStr}`;
            date = new Date(iso);
        } else {
            date = new Date(dateStr);
        }
        
        if (Number.isNaN(date.getTime())) {
            // If parsing fails, return the original string
            return escapeHtml(`${dateStr}${timeStr ? ` ${timeStr}` : ''}`);
        }
        
        // Format as: "MMM DD, YYYY HH:MM AM/PM"
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        return date.toLocaleString('en-US', options);
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
        cont.innerHTML = '<div class="text-center text-muted py-4">Loading lab records...</div>';

        const params = new URLSearchParams();
        if (name) params.append('name', name);
        if (patientId) params.append('patient_id', String(patientId));

        fetch('<?= base_url('laboratory/patient/lab-records') ?>?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    cont.innerHTML = '<span style="color:#dc3545;">Failed to load lab records.</span>';
                    return;
                }
                const rows = Array.isArray(data.records) ? data.records : [];
                if (rows.length === 0) {
                    cont.innerHTML = '<span style="color:#6c757d">No lab records found.</span>';
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
                    let badgeClass = 'badge-secondary';
                    if (statusKey === 'completed') badgeClass = 'badge-success';
                    else if (statusKey === 'in_progress') badgeClass = 'badge-primary';
                    const badge = `<span class="badge ${badgeClass}" style="text-transform:capitalize;">${statusLabels[statusKey]?.label || 'Pending'}</span>`;
                    const viewUrl = '<?= base_url('laboratory/testresult/view/') ?>' + (r.id || '');
                    const actionBtn = statusKey === 'completed'
                        ? `<a href="${viewUrl}" class="btn btn-sm btn-primary">View</a>`
                        : '<span style="color:#6c757d; font-size:13px;">Awaiting results</span>';

                    return `<tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:12px; vertical-align:middle; white-space:nowrap; font-size:13px; width:200px;">${date}</td>
                        <td style="padding:12px; vertical-align:middle; width:180px;">${test}</td>
                        <td style="padding:12px; vertical-align:middle; width:130px;">${badge}</td>
                        <td style="padding:12px; vertical-align:middle; width:250px; word-wrap:break-word;">${escapeHtml(cappedNotes)}</td>
                        <td style="padding:12px; vertical-align:middle; width:120px;">${actionBtn}</td>
                    </tr>`;
                }).join('');

                cont.innerHTML = `
                    <div class="table-responsive" style="overflow-x:auto; width:100%;">
                        <table class="table table-bordered table-striped" style="width:100%; border-collapse:collapse; font-size:13px; table-layout:auto;">
                            <thead style="background:#0d6efd; color:#fff;">
                                <tr>
                                    <th style="padding:12px; text-align:left; white-space:nowrap; width:200px;">Test Date</th>
                                    <th style="padding:12px; text-align:left; white-space:nowrap; width:180px;">Test Type</th>
                                    <th style="padding:12px; text-align:left; white-space:nowrap; width:130px;">Status</th>
                                    <th style="padding:12px; text-align:left; width:250px;">Notes</th>
                                    <th style="padding:12px; text-align:left; white-space:nowrap; width:120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>${htmlRows}</tbody>
                        </table>
                    </div>`;
            })
            .catch(() => { cont.innerHTML = '<span style="color:#dc3545">Error loading lab records.</span>'; });
    }

    async function loadVitals(patientId) {
        const bpEl = document.getElementById('ehrVitalsBp');
        const hrEl = document.getElementById('ehrVitalsHr');
        const tempEl = document.getElementById('ehrVitalsTemp');
        const updatedEl = document.getElementById('ehrVitalsUpdated');

        if (!bpEl || !hrEl || !tempEl || !updatedEl) return;

        bpEl.innerText = hrEl.innerText = tempEl.innerText = '...';
        updatedEl.innerText = 'Loading...';

        try {
            const res = await fetch(`<?= base_url('doctor/vitals') ?>?patient_id=${encodeURIComponent(patientId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                bpEl.innerText = hrEl.innerText = tempEl.innerText = '-';
                updatedEl.innerText = data && data.message ? data.message : 'No vitals data';
                return;
            }
            const v = data.vitals || null;
            bpEl.innerText = v && v.blood_pressure ? v.blood_pressure : '-';
            hrEl.innerText = v && v.heart_rate ? v.heart_rate : '-';
            tempEl.innerText = v && v.temperature ? v.temperature : '-';
            updatedEl.innerText = v && v.created_at ? (new Date(v.created_at)).toLocaleString() : '-';
        } catch (e) {
            bpEl.innerText = hrEl.innerText = tempEl.innerText = '-';
            updatedEl.innerText = 'Error loading vitals';
        }
    }

    async function loadMedicalRecords(patientId) {
        const container = document.getElementById('ehrMedicalRecords');
        if (!container) return;
        container.innerHTML = '<div class="text-center text-muted py-4">Loading admissions history...</div>';

        try {
            const res = await fetch(`<?= base_url('doctor/medical-records') ?>?patient_id=${encodeURIComponent(patientId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                container.innerHTML = `<span style="color:#dc3545;">${(data && data.message) || 'Failed to load medical records.'}</span>`;
                return;
            }

            const records = Array.isArray(data.records) ? data.records : [];
            if (records.length === 0) {
                container.innerHTML = '<span style="color:#6c757d">No admissions recorded for this patient.</span>';
                return;
            }
            const statusLabels = {
                admitted: { label: 'Admitted', className: 'admitted' },
                discharged: { label: 'Discharged', className: 'discharged' },
                cancelled: { label: 'Cancelled', className: 'cancelled' },
                default: { label: 'Admitted', className: 'admitted' },
            };

            const formatDate = (dateStr, timeStr) => {
                if (!dateStr || dateStr === '—' || dateStr === '-') return '—';
                
                // Handle if dateStr is just a number (like "04")
                if (/^\d{1,2}$/.test(dateStr.trim())) {
                    return dateStr; // Return as-is if it's just a day number
                }
                
                // Try to parse the date
                let date;
                if (timeStr) {
                    const iso = `${dateStr}T${timeStr}`;
                    date = new Date(iso);
                } else {
                    date = new Date(dateStr);
                }
                
                if (Number.isNaN(date.getTime())) {
                    // If parsing fails, try to format the string
                    if (dateStr.includes('-') || dateStr.includes('/')) {
                        return dateStr + (timeStr ? ` ${timeStr}` : '');
                    }
                    return dateStr;
                }
                
                // Format as: "MMM DD, YYYY HH:MM AM/PM"
                const options = { 
                    year: 'numeric', 
                    month: 'short', 
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };
                return date.toLocaleString('en-US', options);
            };

            const rows = records.map((rec, idx) => {
                const status = (rec.status || 'admitted').toLowerCase();
                let badgeClass = 'badge-secondary';
                if (status === 'admitted') badgeClass = 'badge-success';
                else if (status === 'discharged') badgeClass = 'badge-primary';
                else if (status === 'cancelled') badgeClass = 'badge-danger';

                const admissionDate = formatDate(rec.admission_date, rec.admission_time);
                let dischargeDate = '—';
                if (rec.discharge_date) {
                    // Handle if discharge_date is just a number
                    if (/^\d{1,2}$/.test(String(rec.discharge_date).trim())) {
                        dischargeDate = rec.discharge_date;
                    } else {
                        const discharge = new Date(rec.discharge_date);
                        if (!Number.isNaN(discharge.getTime())) {
                            const options = { 
                                year: 'numeric', 
                                month: 'short', 
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            };
                            dischargeDate = discharge.toLocaleString('en-US', options);
                        } else {
                            dischargeDate = rec.discharge_date;
                        }
                    }
                } else if (status === 'admitted') {
                    dischargeDate = 'Currently admitted';
                }
                const location = [rec.ward, rec.room, rec.bed].filter(Boolean).join(' / ') || '—';

                return `
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:12px; vertical-align:middle; text-align:center; width:50px;">${idx + 1}</td>
                        <td style="padding:12px; vertical-align:middle; white-space:nowrap; font-size:13px; width:200px;">${admissionDate}</td>
                        <td style="padding:12px; vertical-align:middle; white-space:nowrap; font-size:13px; width:200px;">${dischargeDate}</td>
                        <td style="padding:12px; vertical-align:middle; width:120px;">${rec.admission_type ? rec.admission_type.charAt(0).toUpperCase() + rec.admission_type.slice(1) : '—'}</td>
                        <td style="padding:12px; vertical-align:middle; width:180px;">${rec.physician || '—'}</td>
                        <td style="padding:12px; vertical-align:middle; width:180px;">${location}</td>
                        <td style="padding:12px; vertical-align:middle; width:150px; word-wrap:break-word;">${rec.diagnosis || '—'}</td>
                        <td style="padding:12px; vertical-align:middle; width:150px; word-wrap:break-word;">${rec.reason || '—'}</td>
                        <td style="padding:12px; vertical-align:middle; width:120px;"><span class="badge ${badgeClass}" style="text-transform:capitalize; padding:6px 12px;">${status}</span></td>
                    </tr>`;
            }).join('');

            container.innerHTML = `
                <div class="table-responsive" style="overflow-x:auto; overflow-y:auto; width:100%; height:100%;">
                    <table class="table table-bordered table-striped" style="width:100%; border-collapse:collapse; font-size:13px; table-layout:auto;">
                        <thead style="background:#0d6efd; color:#fff; position:sticky; top:0; z-index:10;">
                            <tr>
                                <th style="padding:12px; text-align:center; white-space:nowrap; width:50px;">#</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:200px;">Admission Date</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:200px;">Discharge Date</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:120px;">Type</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:180px;">Physician</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:180px;">Ward / Room / Bed</th>
                                <th style="padding:12px; text-align:left; width:150px;">Diagnosis</th>
                                <th style="padding:12px; text-align:left; width:150px;">Reason</th>
                                <th style="padding:12px; text-align:left; white-space:nowrap; width:120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        } catch (e) {
            container.innerHTML = '<span style="color:#dc3545">Error loading medical records.</span>';
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
