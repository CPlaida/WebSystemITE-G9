<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .alert {
      padding: 1rem;
      margin-bottom: 1rem;
      border: 1px solid transparent;
      border-radius: 0.375rem;
    }

    .alert-success {
      color: #0f5132;
      background-color: #d1e7dd;
      border-color: #badbcc;
    }

    .alert-danger {
      color: #842029;
      background-color: #f8d7da;
      border-color: #f5c2c7;
    }

    .btn-close {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      opacity: 0.5;
      float: right;
    }

    .btn-close:hover {
      opacity: 1;
    }

    .badge {
      padding: 0.25em 0.5em;
      font-size: 0.75em;
      font-weight: 700;
      border-radius: 0.25rem;
    }

    .badge-success {
      color: #fff;
      background-color: #198754;
    }

    .badge-secondary {
      color: #fff;
      background-color: #6c757d;
    }

    .text-center {
      text-align: center;
    }

    .text-muted {
      color: #6c757d;
    }

    .btn-primary {
      background-color: #0d6efd;
      border-color: #0d6efd;
      color: #fff;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
      border-color: #0a58ca;
    }

    .container {
      max-width: 1200px;
      margin: auto;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      border: 1px solid #ddd;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 20px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 24px;
      background-color: #f8f9fc;
      border-bottom: 1px solid #e3e6f0;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .card-title {
      color: #4e73df;
      font-weight: 600;
      margin: 0;
      font-size: 1.25rem;
    }
    
    .search-container {
      display: flex;
      gap: 10px;
    }

    .search-input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      min-width: 200px;
    }

    .search-button {
      background-color: #2c3e50;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 0 20px;
      cursor: pointer;
      font-weight: 500;
    }
    
    @media (max-width: 768px) {
      .search-container {
        flex-direction: column;
        width: 100%;
      }
      
      .search-input {
        width: 100%;
        min-width: 0;
      }
    }

    .ehr-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .ehr-info {
      flex: 1 1 30%;
      background: #f8f9fa;
      padding: 15px;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
    }

    .ehr-info p {
      margin: 10px 0;
      font-size: 14px;
    }

    .ehr-tabs {
      flex: 1 1 65%;
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .tabs button {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      background: #f8f9fa;
      cursor: pointer;
      border-radius: 4px;
      font-weight: 500;
    }

    .tabs button.active {
      background: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }

    .tab-content {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 20px;
      background: #fff;
      min-height: 150px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table th, table td {
      border: 1px solid #dee2e6;
      padding: 10px;
      text-align: left;
    }

    table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      border-radius: 8px;
      max-width: 900px;
      width: 95%;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.9);}
      to {opacity: 1; transform: scale(1);}
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .close-btn {
      cursor: pointer;
      font-size: 20px;
      color: #dc3545;
      border: none;
      background: none;
    }

    .badge {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.35em 0.65em;
      border-radius: 50rem;
    }

    .badge-success {
      background-color: #d1e7dd;
      color: #0f5132;
    }

    .badge-secondary {
      background-color: #f8f9fa;
      color: #6c757d;
    }

    .action-buttons {
      display: flex;
      gap: 8px;
    }

    .btn-view {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
      font-weight: 500;
      line-height: 1.5;
      color: #fff;
      background-color: #0d6efd;
      border: 1px solid #0d6efd;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s ease-in-out;
    }

    .btn-view:hover {
      background-color: #0b5ed7;
      border-color: #0a58ca;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-view i {
      font-size: 0.9em;
    }
  </style>
</head>
<body>
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
                  <td><?= esc($patient['patient_id']) ?></td>
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
                  <td><?= esc($patient['patient_id'] ?? $patient['id']) ?></td>
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
            <p>Prescription details will appear here...</p>
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
