<?php $this->extend('partials/header') ?>

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

        .form-group { 
            position: relative;
            margin-bottom: 0.8rem; 
        }

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

        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Laboratory Request Form</h1>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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
                                <input type="text" class="form-control" id="patientName" name="patient_name" 
                                       placeholder="Enter patient name or search..." 
                                       autocomplete="off" required>
                                <div id="patientSuggestions" class="suggestions-dropdown" style="display: none;"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="testDate">Test Date</label>
                                <input type="date" class="form-control" id="testDate" name="test_date" 
                                       value="<?= old('test_date') ?: date('Y-m-d') ?>" required>
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
                                    <option value="normal" <?= old('priority') == 'normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="urgent" <?= old('priority') == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                    <option value="stat" <?= old('priority') == 'stat' ? 'selected' : '' ?>>Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="clinicalNotes">Clinical Notes</label>
                            <textarea class="form-control" id="clinicalNotes" name="clinical_notes" rows="2" 
                                      placeholder="Enter clinical notes or special instructions"><?= old('clinical_notes') ?></textarea>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 15px;">
                        <button type="reset" class="btn btn-secondary">
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
        // Patient data for autocomplete
        const patients = [
            { id: 1, name: 'Juan Dela Cruz', dob: '1990-05-15' },
            { id: 2, name: 'Maria Santos', dob: '1985-10-22' },
            { id: 3, name: 'Pedro Reyes', dob: '1980-03-10' },
            { id: 4, name: 'Ana Garcia', dob: '1995-01-30' },
            { id: 5, name: 'Carlos Rodriguez', dob: '1988-07-12' }
        ];

        const patientInput = $('#patientName');
        const suggestionsDiv = $('#patientSuggestions');

        // Handle input typing
        patientInput.on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            
            if (query.length < 2) {
                suggestionsDiv.hide();
                return;
            }

            // Filter patients based on input
            const filteredPatients = patients.filter(patient => 
                patient.name.toLowerCase().includes(query)
            );

            if (filteredPatients.length > 0) {
                let suggestionsHtml = '';
                filteredPatients.forEach(patient => {
                    suggestionsHtml += `<div class="suggestion-item" data-id="${patient.id}" data-name="${patient.name}" data-dob="${patient.dob}">
                        ${patient.name}
                    </div>`;
                });
                suggestionsDiv.html(suggestionsHtml).show();
            } else {
                suggestionsDiv.hide();
            }
        });

        // Handle suggestion click
        $(document).on('click', '.suggestion-item', function() {
            const name = $(this).data('name');
            const dob = $(this).data('dob');
            
            patientInput.val(name);
            suggestionsDiv.hide();
            
            // Update DOB if field exists
            if ($('#patientDob').length) {
                $('#patientDob').val(dob);
            }
        });

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#patientName, #patientSuggestions').length) {
                suggestionsDiv.hide();
            }
        });

        // Handle keyboard navigation
        patientInput.on('keydown', function(e) {
            const suggestions = $('.suggestion-item');
            const current = $('.suggestion-item.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (current.length === 0) {
                    suggestions.first().addClass('active');
                } else {
                    current.removeClass('active').next().addClass('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (current.length === 0) {
                    suggestions.last().addClass('active');
                } else {
                    current.removeClass('active').prev().addClass('active');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (current.length > 0) {
                    current.click();
                }
            } else if (e.key === 'Escape') {
                suggestionsDiv.hide();
            }
        });

        // Form submission
        $('#labRequestForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                patient_name: $('#patientName').val(),
                test_type: $('#testType').val(),
                priority: $('#priority').val(),
                clinical_notes: $('#clinicalNotes').val(),
                test_date: $('#testDate').val()
            };

            // Basic validation
            if (!formData.patient_name || !formData.test_type) {
                alert('Please fill in all required fields');
                return;
            }

            // Submit form
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Lab request submitted successfully!');
                        $('#labRequestForm')[0].reset();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to submit request'));
                    }
                },
                error: function() {
                    // If AJAX fails, submit normally
                    $('#labRequestForm')[0].submit();
                }
            });
        });
    });
    </script>
<?= $this->endSection() ?>
