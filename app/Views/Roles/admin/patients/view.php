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
                      <button class="btn-view" onclick="viewPatient('<?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>','<?= esc($patient['insurance_provider'] ?? 'N/A') ?>','<?= esc($patient['insurance_number'] ?? 'N/A') ?>')">View</button>
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
                      <button class="btn-view" onclick="viewPatient('<?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">View</button>
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
          <p><b>Insurance Provider:</b> <span id="ehrInsuranceProvider">-</span></p>
          <p><b>Insurance No.:</b> <span id="ehrInsuranceNumber">-</span></p>
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
            <p>Prescription details will appear here...</p>
          </div>
          <div id="vitals" class="tab-content" style="display:none;">
            <p>Vitals records will appear here...</p>
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
    function viewPatient(name, mobile, address, dob, gender, medicalHistory, patientId, email, bloodType, emergencyContact, insuranceProvider, insuranceNumber) {
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
      document.getElementById("ehrInsuranceProvider").innerText = insuranceProvider || 'N/A';
      document.getElementById("ehrInsuranceNumber").innerText = insuranceNumber || 'N/A';

      document.getElementById("ehrModal").style.display = "flex";

      // Load lab records for this patient
      loadLabRecords(patientId, name);
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

      // If opening Lab tab, refresh records for currently viewed patient
      if (tabName === 'lab') {
        const pid = document.getElementById('ehrPatientId').innerText.trim();
        const pname = document.getElementById('ehrName').innerText.trim();
        loadLabRecords(pid ? parseInt(pid) : 0, pname);
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
        if (rows.length === 0) { cont.innerHTML = '<span style="color:#6c757d">No completed lab records found.</span>'; return; }
        let html = '<div style="overflow:auto"><table style="width:100%; border-collapse:collapse">'+
                   '<thead><tr style="text-align:left; border-bottom:1px solid #e9ecef">'+
                   '<th style="padding:6px 8px">Date</th><th style="padding:6px 8px">Test</th><th style="padding:6px 8px">Notes</th><th style="padding:6px 8px">Action</th></tr></thead><tbody>';
        rows.forEach(r => {
          const d = r.test_date ? new Date(r.test_date).toLocaleDateString() : '-';
          const t = r.test_type || '-';
          const n = r.notes ? String(r.notes).substring(0,120) : '—';
          const viewUrl = '<?= base_url('laboratory/testresult/view/') ?>' + (r.id || '');
          html += `<tr style="border-bottom:1px solid #f1f3f5"><td style="padding:6px 8px">${d}</td><td style="padding:6px 8px">${t}</td><td style="padding:6px 8px">${n}</td><td style="padding:6px 8px"><a href="${viewUrl}" class="btn btn-sm btn-primary">View</a></td></tr>`;
        });
        html += '</tbody></table></div>';
        cont.innerHTML = html;
      })
      .catch(() => { cont.innerHTML = '<span style="color:#dc3545">Error loading lab records.</span>'; });
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
