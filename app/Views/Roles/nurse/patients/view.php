<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <!-- Patient List Section -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Patient Records</h2>
        <div class="search-container">
          <input type="text" id="searchInput" class="search-input" placeholder="Search patients..." onkeyup="if(event.key === 'Enter') filterPatients()">
          <button id="searchButton" class="search-button" onclick="filterPatients()">Search</button>
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
        <table class="mb-5">
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
        <table>
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
          <p><b>Emergency Contact:</b> <span id="ehrEmergencyContact">-</span></p>
          <p><b>Medical History:</b> <span id="ehrAilment">-</span></p>
          <p><b>Date Recorded:</b> <span id="ehrDate">-</span></p>
        </div>
        <div class="ehr-tabs">
          <div class="tabs">
            <button class="tab-btn active" onclick="openTab(event,'prescription')">Prescription</button>
            <button class="tab-btn" onclick="openTab(event,'vitals')">Vitals</button>
            <button class="tab-btn" onclick="openTab(event,'lab')">Lab Records</button>
          </div>
          <div id="prescription" class="tab-content">
            <div id="ehrPrescriptionContent" style="min-height:120px; padding:10px; border:1px solid #e5e7eb; border-radius:6px; color:#2c3e50; font-size:14px;">
              Prescription details will appear here...
            </div>
          </div>
          <div id="vitals" class="tab-content" style="display:none;">
            <div class="vitals-section" style="font-size:14px; color:#2c3e50;">
              <h6 style="font-weight:600; margin-bottom:8px;">
                <i class="fas fa-heartbeat me-1"></i> Vitals
              </h6>
              <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:8px 16px;">
                <div><strong>Blood Pressure:</strong> <span id="ehrVitalsBp">-</span></div>
                <div><strong>Heart Rate (bpm):</strong> <span id="ehrVitalsHr">-</span></div>
                <div><strong>Temperature (°C):</strong> <span id="ehrVitalsTemp">-</span></div>
                <div><strong>Last Updated:</strong> <span id="ehrVitalsUpdated">-</span></div>
              </div>
            </div>
          </div>
          <div id="lab" class="tab-content" style="display:none;">
            <p>Lab records will appear here...</p>
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
      document.getElementById("ehrEmergencyContact").innerText = emergencyContact;

      document.getElementById("ehrModal").style.display = "flex";

      // Load latest prescription and vitals for this patient
      loadPrescription(patientId);
      loadVitals(patientId);
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
      if (tabName === 'vitals' && pid) {
        loadVitals(pid);
      }
      if (tabName === 'prescription' && pid) {
        loadPrescription(pid);
      }
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

    async function loadPrescription(patientId) {
      const content = document.getElementById('ehrPrescriptionContent');
      if (!content) return;
      content.textContent = 'Loading prescription...';

      try {
        const res = await fetch(`<?= base_url('doctor/prescription') ?>?patient_id=${encodeURIComponent(patientId)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data || !data.success) {
          content.textContent = (data && data.message) ? data.message : 'Failed to load prescription.';
          return;
        }
        const note = data.note ? data.note.trim() : '';
        content.textContent = note !== '' ? note : 'No prescription note recorded yet.';
      } catch (e) {
        content.textContent = 'Error loading prescription.';
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
