<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Laboratory Requests<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --text-color: #333;
            --white: #ffffff;
        }
        
        .main-content {
            padding: 15px;
            margin-left: 120px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .main-content.expanded { margin-left: 70px; }

        .page-header {
            margin: 0 0 15px 0;
            padding: 12px 15px;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .page-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }

        .card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .card-body { padding: 15px; }

        .form-section {
            margin-bottom: 15px;
            padding: 15px;
            background: #fff;
            border-radius: 6px;
            border: 1px solid #eee;
        }

        .section-title {
            color: #4361ee;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            font-size: 1.1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .form-group { margin-bottom: 0.8rem; }

        .form-label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
            color: #555;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.4rem 0.6rem;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
            border-radius: 4px;
            margin-left: 5px;
        }

        .btn i { margin-right: 4px; }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .form-group { margin-bottom: 0.5rem}
            
        }
        .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        
    }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Laboratory Request Form</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="labRequestForm" method="POST" action="<?= base_url('laboratory/request/submit') ?>">
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-injured"></i> Patient Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="patientName">Patient Name</label>
                                <select class="form-control select2" id="patientName" name="patient_name" required>
                                    <option value="">Search patient...</option>
                                    <?php 
                                    // This should be replaced with actual data from your database
                                    $patients = [
                                        [
                                            'id' => 1,
                                            'first_name' => 'Juan',
                                            'last_name' => 'Dela Cruz',
                                            'dob' => '1990-05-15'
                                        ],
                                        [
                                            'id' => 2,
                                            'first_name' => 'Maria',
                                            'last_name' => 'Santos',
                                            'dob' => '1985-10-22'
                                        ],
                                        [
                                            'id' => 3,
                                            'first_name' => 'Pedro',
                                            'last_name' => 'Reyes',
                                            'dob' => '1980-03-10'
                                        ]
                                    ];
                                    
                                    foreach ($patients as $patient) {
                                        echo '<option value="' . $patient['id'] . '" data-dob="' . $patient['dob'] . '">' . 
                                             htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']) . 
                                             '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="patientDob">Date of Birth</label>
                                <input type="date" class="form-control" id="patientDob" name="dob" required>
                            </div>
                        </div>
                    </div>

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
                                    <option value="ECG">ECG</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="priority">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="routine">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="clinicalNotes">Clinical Notes</label>
                            <textarea class="form-control" id="clinicalNotes" name="clinical_notes" rows="2"></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 15px;">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebar && mainContent) {
                const toggleSidebar = () => {
                    mainContent.classList.toggle('expanded', sidebar.classList.contains('closed'));
                };
                toggleSidebar();
                document.querySelector('.toggle-btn')?.addEventListener('click', toggleSidebar);
            }

            document.getElementById('labRequestForm')?.addEventListener('submit', function(e) {
                console.log('Form submitted');
                // e.preventDefault(); // Uncomment to prevent form submission for testing
            });
        });
    </script>

    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Search patient...',
            width: '100%'
        });

        // Update DOB when patient is selected
        $('#patientName').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const dob = selectedOption.data('dob');
            if (dob) {
                $('#patientDob').val(dob);
            }
        });

        // For demo purposes - in production, you would use AJAX to load patients
        // Example AJAX implementation:
        /*
        $('.select2').select2({
            ajax: {
                url: '<?= base_url('api/patients/search') ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: formatPatient,
            templateSelection: formatPatientSelection
        });

        function formatPatient(patient) {
            if (patient.loading) return patient.text;
            return $(
                '<div>'+patient.last_name + ', ' + patient.first_name + 
                '<br><small class="text-muted">DOB: ' + patient.dob + '</small></div>'
            );
        }

        function formatPatientSelection(patient) {
            return patient.last_name + ', ' + patient.first_name;
        }
        */
    });
    </script>
<?= $this->endSection() ?>
