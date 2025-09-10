<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Laboratory Requests<?= $this->endSection() ?>

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

        .page-title {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: #4e73df;
        }

        .card-body {
            padding: 20px;
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5a5c69;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #6e707e;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            border-radius: 0.35rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-secondary {
            color: #fff;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #2f2d9c;
            border-color: #2c2a8f;
        }

        .text-right {
            text-align: right;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .main-content.expanded {
                margin-left: 0;
            }
        }
    </style>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Laboratory Request Form</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="labRequestForm" method="POST" action="<?= base_url('laboratory/request/submit') ?>">
                    <!-- Patient Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-injured"></i> Patient Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="patientId">Patient ID</label>
                                <input type="text" class="form-control" id="patientId" name="patient_id" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="patientName">Patient Name</label>
                                <input type="text" class="form-control" id="patientName" name="patient_name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="patientDob">Date of Birth</label>
                                <input type="date" class="form-control" id="patientDob" name="dob" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="gender">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contactNumber">Contact Number</label>
                                <input type="tel" class="form-control" id="contactNumber" name="contact_number" required>
                            </div>
                        </div>
                    </div>

                    <!-- Test Request Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-vial"></i> Test Request
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="testType">Test Type</label>
                                <select class="form-select" id="testType" name="test_type" required>
                                    <option value="">Select Test Type</option>
                                    <option value="blood">Blood Test</option>
                                    <option value="urine">Urine Test</option>
                                    <option value="xray">X-Ray</option>
                                    <option value="mri">MRI Scan</option>
                                    <option value="ct">CT Scan</option>
                                    <option value="ultrasound">Ultrasound</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="priority">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="clinicalNotes">Clinical Notes</label>
                            <textarea class="form-control" id="clinicalNotes" name="clinical_notes" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Physician Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-md"></i> Physician Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="physicianName">Physician Name</label>
                                <input type="text" class="form-control" id="physicianName" name="physician_name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="licenseNumber">License Number</label>
                                <input type="text" class="form-control" id="licenseNumber" name="license_number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="physicianNotes">Physician's Notes</label>
                            <textarea class="form-control" id="physicianNotes" name="physician_notes" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="reset" class="btn btn-secondary me-2">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebar && mainContent) {
                const toggleSidebar = () => {
                    if (sidebar.classList.contains('closed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                };

                // Initial check
                toggleSidebar();

                // Add event listener for sidebar toggle
                const toggleBtn = document.querySelector('.toggle-btn');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', toggleSidebar);
                }
            }

            // Form validation
            document.getElementById('labRequestForm').addEventListener('submit', function(e) {
                // Add your form validation logic here
                console.log('Form submitted');
                // e.preventDefault(); // Uncomment to prevent form submission for testing
            });
        });
    </script>
<?= $this->endSection() ?>
