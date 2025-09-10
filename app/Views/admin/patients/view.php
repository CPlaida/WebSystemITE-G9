<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Patient Records<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #3f37c9;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-color: #dee2e6;
            --text-color: #333;
            --white: #ffffff;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --border-radius: 6px;
            --border-radius-lg: 12px;
            --transition: all 0.2s ease;
        }
        
        .main-content {
            padding: 20px;
            width: 100%;
            margin-left: 120px;
            transition: all 0.3s;
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .page-header {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            background: var(--white);
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background-color: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .card-body {
            padding: 1.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid var(--border-color);
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid var(--border-color);
            background-color: var(--light-gray);
            font-weight: 600;
            text-align: left;
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }

        .badge-success {
            color: #fff;
            background-color: var(--success-color);
        }

        .badge-warning {
            color: #000;
            background-color: var(--warning-color);
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            line-height: 1.5;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--dark-gray);
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-gray);
        }

        .btn-danger {
            background-color: var(--error-color);
            border: 1px solid var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
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
            padding: 0.5rem;
            line-height: 1;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            overflow-y: auto;
        }

        .modal-content {
            position: relative;
            margin: 2rem auto;
            max-width: 800px;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            width: 90%;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            opacity: 0.5;
            cursor: pointer;
            padding: 0.5rem;
        }

        .close-btn:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Patient Records</h1>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Patient List</h5>
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search patients..." onkeyup="searchPatients()">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover" id="patientsTable">
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
                        <tbody>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $index => $patient): ?>
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
                                            <button class="btn btn-sm btn-view" onclick="viewPatient('<?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>','<?= esc($patient['phone']) ?>','<?= esc($patient['address'] ?? 'N/A') ?>','<?= esc(date('M d, Y', strtotime($patient['date_of_birth']))) ?>','<?= esc(ucfirst($patient['gender'])) ?>','<?= esc($patient['medical_history'] ?? 'No medical history recorded') ?>','<?= esc($patient['patient_id']) ?>','<?= esc($patient['email'] ?? 'N/A') ?>','<?= esc($patient['blood_type'] ?? 'N/A') ?>','<?= esc($patient['emergency_contact'] ?? 'N/A') ?>')">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <p class="text-muted">No patients registered yet.</p>
                                        <a href="<?= base_url('patients/register') ?>" class="btn btn-primary">Register First Patient</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- EHR Modal -->
    <div class="modal" id="ehrModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Electronic Health Records</h5>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="ehrContent">
                    <!-- EHR content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        function searchPatients() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('patientsTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }

        // View patient
        function viewPatient(name, mobile, address, dob, gender, medicalHistory, patientId, email, bloodType, emergencyContact) {
            document.getElementById("ehrContent").innerHTML = `
                <h6>Patient ID: ${patientId}</h6>
                <p><b>Patient Name:</b> ${name}</p>
                <p><b>Mobile:</b> ${mobile}</p>
                <p><b>Address:</b> ${address}</p>
                <p><b>Date of Birth:</b> ${dob}</p>
                <p><b>Gender:</b> ${gender}</p>
                <p><b>Medical History:</b> ${medicalHistory}</p>
                <p><b>Email:</b> ${email}</p>
                <p><b>Blood Type:</b> ${bloodType}</p>
                <p><b>Emergency Contact:</b> ${emergencyContact}</p>
            `;
            
            // Show modal
            document.getElementById("ehrModal").style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById("ehrModal").style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById("ehrModal");
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
<?= $this->endSection() ?>
