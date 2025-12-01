<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <!-- Patient List Section -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Patient Records</h2>
      </div>
      <div class="unified-search-wrapper">
          <div class="unified-search-row">
              <i class="fas fa-search unified-search-icon"></i>
              <input type="text" id="searchInput" class="unified-search-field" placeholder="Search patients..." onkeyup="if(event.key === 'Enter') filterPatients()">
          </div>
      </div>
      <div class="card-body">
        <!-- Success/Error Messages -->
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

        <?php 
        // Ensure $patients is defined and is an array when rendering directly via routes->view
        $patients = is_array($patients ?? null) ? $patients : [];
        // Group patients by type (assuming there's a 'type' field in the patients table)
        $inpatients = array_filter($patients, function($patient) {
            return ($patient['type'] ?? 'outpatient') === 'inpatient';
        });
        
        $outpatients = array_filter($patients, function($patient) {
            return ($patient['type'] ?? 'outpatient') === 'outpatient';
        });
        ?>

        <!-- Inpatients Section -->
        <h3 class="mt-4 mb-3">Inpatients</h3>
        <table class="data-table mb-5">
          <thead>
            <tr>
              <th>Patient ID</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Gender</th>
              <th>Date of Birth</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="inpatientTable">
            <?php if (!empty($inpatients)): ?>
              <?php foreach ($inpatients as $patient): ?>
                <tr>
                  <td><?= esc($patient['id']) ?></td>
                  <td><?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''))) ?></td>
                  <td><?= esc($patient['phone']) ?></td>
                  <td><?= esc($patient['address'] ?? 'N/A') ?></td>
                  <td><?= esc(ucfirst($patient['gender'])) ?></td>
                  <td><?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?></td>
                  <td>
                    <span class="badge <?= $patient['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                      <?= esc(ucfirst($patient['status'])) ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <?php 
                        $fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''));
                      ?>
                      <button class="btn-view" onclick="viewPatient('<?= esc($fullName) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center">
                  <p class="text-muted">No inpatients found.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Outpatients Section -->
        <h3 class="mt-5 mb-3">Outpatients</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Patient ID</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Gender</th>
              <th>Date of Birth</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="outpatientTable">
            <?php if (!empty($outpatients)): ?>
              <?php foreach ($outpatients as $patient): ?>
                <tr>
                  <td><?= esc($patient['id']) ?></td>
                  <td><?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''))) ?></td>
                  <td><?= esc($patient['phone']) ?></td>
                  <td><?= esc($patient['address'] ?? 'N/A') ?></td>
                  <td><?= esc(ucfirst($patient['gender'])) ?></td>
                  <td><?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?></td>
                  <td>
                    <span class="badge <?= $patient['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                      <?= esc(ucfirst($patient['status'])) ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <?php 
                        $fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''));
                      ?>
                      <button class="btn-view" onclick="viewPatient('<?= esc($fullName) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center">
                  <p class="text-muted">No outpatients found.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Popup Modal -->
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
            <div id="ehrMedicalRecords" style="min-height:140px; padding:8px 0; color:#2c3e50; font-size:14px;">
              <em>Select a patient to load admissions history.</em>
            </div>
          </div>
          <div id="vitals" class="tab-content" style="display:none;">
            <div class="vitals-section" style="font-size:14px; color:#2c3e50; display:flex; flex-direction:column; gap:10px;">
              <div>
                <h6 style="font-weight:600; margin-bottom:8px;">
                  <i class="fas fa-heartbeat" style="margin-right:4px;"></i> Latest Vitals
                </h6>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:8px 16px;">
                  <div><strong>Blood Pressure:</strong> <span id="ehrVitalsBp">-</span></div>
                  <div><strong>Heart Rate (bpm):</strong> <span id="ehrVitalsHr">-</span></div>
                  <div><strong>Temperature (°C):</strong> <span id="ehrVitalsTemp">-</span></div>
                  <div><strong>Last Updated:</strong> <span id="ehrVitalsUpdated">-</span></div>
                </div>
              </div>
            </div>
          </div>
          <div id="lab" class="tab-content" style="display:none;">
            <div id="ehrLabContainer" style="min-height:120px; padding:6px 0; color:#2c3e50; font-size:14px;">
              <em>Loading lab records...</em>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function viewPatient(name, mobile, address, dob, gender, medicalHistory, patientId, email, bloodType, emergencyContact) {
      document.getElementById("ehrName").innerText = name;
      document.getElementById("ehrMobile").innerText = mobile;
      document.getElementById("ehrAddress").innerText = address;
      document.getElementById("ehrDOB").innerText = dob;
      document.getElementById("ehrGender").innerText = gender;
      document.getElementById("ehrAilment").innerText = medicalHistory;
      document.getElementById("ehrDate").innerText = new Date().toLocaleDateString();

      // Add additional patient info
      document.getElementById("ehrPatientId").innerText = patientId;
      document.getElementById("ehrEmail").innerText = email;
      document.getElementById("ehrBloodType").innerText = bloodType;

      document.getElementById("ehrModal").style.display = "flex";

      // Load lab records for this patient
      loadLabRecords(patientId, name);
      // Load vitals for this patient
      loadVitals(patientId);
      // Load admissions/medical records
      loadMedicalRecords(patientId);
    }

    function closeModal() {
      document.getElementById("ehrModal").style.display = "none";
    }

    function openTab(evt, tabName) {
      var content = document.getElementsByClassName("tab-content");
      for (let i = 0; i < content.length; i++) {
        content[i].style.display = "none";
      }
      var btns = document.getElementsByClassName("tab-btn");
      for (let i = 0; i < btns.length; i++) {
        btns[i].classList.remove("active");
      }
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.classList.add("active");

      const pid = document.getElementById('ehrPatientId').innerText.trim();
      const pname = document.getElementById('ehrName').innerText.trim();

      // If opening Lab tab, refresh records for currently viewed patient
      if (tabName === 'lab') {
        loadLabRecords(pid || '', pname);
      }
      // If opening Vitals tab, ensure vitals are refreshed
      if (tabName === 'vitals') {
        loadVitals(pid);
      }
      if (tabName === 'medical-records') {
        loadMedicalRecords(pid);
      }
    }

    function loadLabRecords(patientId, name){
      const cont = document.getElementById('ehrLabContainer');
      if (!cont) return;
      cont.innerHTML = '<em>Loading lab records...</em>';

      const params = new URLSearchParams();
      if (name) params.append('name', name);
      if (patientId) params.append('patient_id', String(patientId));

      fetch('<?= base_url('laboratory/patient/lab-records') ?>?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
        if (!data || !data.success) { cont.innerHTML = '<span style="color:#dc3545">Failed to load lab records.</span>'; return; }
        const rows = Array.isArray(data.records) ? data.records : [];
        if (rows.length === 0) { cont.innerHTML = '<span style="color:#6c757d">No lab records found.</span>'; return; }
        let html = '<div style="overflow:auto"><table style="width:100%; border-collapse:collapse">'+
                   '<thead><tr style="text-align:left; border-bottom:1px solid #e9ecef">'+
                   '<th style="padding:6px 8px">Date</th><th style="padding:6px 8px">Test</th><th style="padding:6px 8px">Status</th><th style="padding:6px 8px">Notes</th><th style="padding:6px 8px">Action</th></tr></thead><tbody>';
        rows.forEach(r => {
          const d = r.test_date ? new Date(r.test_date).toLocaleDateString() : '-';
          const t = r.test_type || '-';
          const n = r.notes ? String(r.notes).substring(0,120) : '—';
          const status = (r.status || 'pending').toLowerCase();
          let statusBadge = '';
          let statusColor = '#6c757d';
          if (status === 'completed') {
            statusColor = '#28a745';
            statusBadge = '<span style="background:#28a745; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:500;">Completed</span>';
          } else if (status === 'in_progress') {
            statusColor = '#007bff';
            statusBadge = '<span style="background:#007bff; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:500;">In Progress</span>';
          } else {
            statusColor = '#ffc107';
            statusBadge = '<span style="background:#ffc107; color:#212529; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:500;">Pending</span>';
          }
          const viewUrl = '<?= base_url('laboratory/testresult/view/') ?>' + (r.id || '');
          const actionBtn = status === 'completed' 
            ? `<a href="${viewUrl}" class="btn btn-sm btn-primary">View</a>`
            : `<span style="color:#6c757d; font-size:12px;">${status === 'in_progress' ? 'Processing...' : 'Pending'}</span>`;
          html += `<tr style="border-bottom:1px solid #f1f3f5"><td style="padding:6px 8px">${d}</td><td style="padding:6px 8px">${t}</td><td style="padding:6px 8px">${statusBadge}</td><td style="padding:6px 8px">${n}</td><td style="padding:6px 8px">${actionBtn}</td></tr>`;
        });
        html += '</tbody></table></div>';
        cont.innerHTML = html;
      })
      .catch(() => { cont.innerHTML = '<span style="color:#dc3545">Error loading lab records.</span>'; });
    }

    async function loadVitals(patientId){
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

        // Pre-fill inputs with latest values
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


    async function loadMedicalRecords(patientId){
      const container = document.getElementById('ehrMedicalRecords');
      if (!container) return;
      container.innerHTML = '<em>Loading admissions history...</em>';

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

        const formatDate = (dateStr, timeStr) => {
          if (!dateStr) return '—';
          const iso = `${dateStr}T${timeStr || '00:00:00'}`;
          const date = new Date(iso);
          if (Number.isNaN(date.getTime())) {
            return dateStr + (timeStr ? ` ${timeStr}` : '');
          }
          return date.toLocaleString();
        };

        const rows = records.map((rec, idx) => {
          const status = (rec.status || 'admitted').toLowerCase();
          let badgeClass = 'badge-secondary';
          if (status === 'admitted') badgeClass = 'badge-success';
          else if (status === 'discharged') badgeClass = 'badge-primary';
          else if (status === 'cancelled') badgeClass = 'badge-danger';

          const admissionDate = formatDate(rec.admission_date, rec.admission_time);
          const dischargeDate = rec.discharge_date ? new Date(rec.discharge_date).toLocaleString() : (status === 'admitted' ? 'Currently admitted' : '—');
          const location = [rec.ward, rec.room, rec.bed].filter(Boolean).join(' / ') || '—';

          return `
            <tr>
              <td>${idx + 1}</td>
              <td>${admissionDate}</td>
              <td>${dischargeDate}</td>
              <td>${rec.admission_type ? rec.admission_type.charAt(0).toUpperCase() + rec.admission_type.slice(1) : '—'}</td>
              <td>${rec.physician || '—'}</td>
              <td>${location}</td>
              <td>${rec.diagnosis || '—'}</td>
              <td>${rec.reason || '—'}</td>
              <td><span class="badge ${badgeClass}" style="text-transform:capitalize;">${status}</span></td>
            </tr>`;
        }).join('');

        container.innerHTML = `
          <div style="overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
              <thead>
                <tr style="text-align:left; border-bottom:1px solid #e9ecef;">
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
            </table>
          </div>`;
      } catch (e) {
        container.innerHTML = '<span style="color:#dc3545">Error loading medical records.</span>';
      }
    }

    // Close modal on outside click
    window.onclick = function(event) {
      let modal = document.getElementById("ehrModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }

    function filterPatients() {
      const searchInput = document.getElementById('searchInput');
      const searchTerm = searchInput.value.toLowerCase();
      const tableRows = document.querySelectorAll('tbody tr');
      
      tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Add event listener for input changes (real-time search)
    document.getElementById('searchInput').addEventListener('input', filterPatients);
  </script>
<?= $this->endSection() ?>
