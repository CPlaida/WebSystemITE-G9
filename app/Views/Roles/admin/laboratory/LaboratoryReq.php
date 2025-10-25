<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Laboratory Requests<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
