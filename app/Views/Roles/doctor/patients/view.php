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
                  <td><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
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
                      <button class="btn-view" onclick="viewPatient('<?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">
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
                  <td><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
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
                      <button class="btn-view" onclick="viewPatient('<?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">
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
            <div style="display:flex; flex-direction:column; gap:10px;">
              <textarea id="prescriptionNote" placeholder="Type prescription or result note here..." style="width:100%; min-height:140px; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
              <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button id="savePrescriptionBtn" class="btn" style="background:#2563eb; color:#fff; border-radius:6px; padding:8px 14px;">Save</button>
              </div>
              <small id="prescriptionStatus" style="color:#6b7280;"></small>
            </div>
          </div>
          <div id="vitals" class="tab-content" style="display:none;">
            <p>Vitals records will appear here...</p>
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

      // Load existing prescription note for this patient
      loadPrescription(patientId);
      // Bind save handler
      const saveBtn = document.getElementById('savePrescriptionBtn');
      if (saveBtn) {
        saveBtn.onclick = () => savePrescription(patientId);
      }
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
    }

    async function loadPrescription(patientId){
      const status = document.getElementById('prescriptionStatus');
      try {
        status.textContent = 'Loading prescription...';
        const res = await fetch(`<?= base_url('doctor/prescription') ?>?patient_id=${encodeURIComponent(patientId)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data && data.success){
          document.getElementById('prescriptionNote').value = data.note || '';
          status.textContent = data.note ? 'Loaded latest note.' : 'No note yet.';
        } else {
          status.textContent = (data && data.message) ? data.message : 'Failed to load note';
        }
      } catch(e){
        status.textContent = 'Error loading note';
      }
    }

    async function savePrescription(patientId){
      const note = document.getElementById('prescriptionNote').value.trim();
      const status = document.getElementById('prescriptionStatus');
      status.textContent = 'Saving...';
      const form = new URLSearchParams();
      form.append('patient_id', patientId);
      form.append('note', note);
      form.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
      try {
        const res = await fetch(`<?= base_url('doctor/prescription/save') ?>`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
          body: form.toString()
        });
        const data = await res.json();
        status.textContent = (data && data.success) ? 'Saved.' : ((data && data.message) || 'Failed to save');
      } catch(e){
        status.textContent = 'Error saving note';
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
