<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Laboratory Requests<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="container-fluid py-4">
        <div class="composite-card billing-card" style="margin-top:0;">
            <div class="composite-header">
                <h1 class="composite-title">Laboratory Request Form</h1>
            </div>
            <div class="card-body">
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
                                <input type="hidden" id="patientId" name="patient_id" value="">
                                <div id="patientSuggestions" class="suggestions-dropdown" style="display: none;"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="testDate">Test Date</label>
                                <input type="date" class="form-control" id="testDate" name="test_date" 
                                       value="<?= old('test_date') ?: date('Y-m-d') ?>" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-vial"></i> Test Request
                        </h3>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 0.5rem;">
                                    <label class="form-label" style="margin-bottom: 0;">Test Types <span class="text-danger">*</span></label>
                                    <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                                        <label class="form-label" for="priority" style="margin-bottom: 0.5rem;">Priority</label>
                                        <select class="form-select" id="priority" name="priority" required>
                                            <option value="normal" <?= old('priority') == 'normal' ? 'selected' : '' ?>>Normal</option>
                                            <option value="urgent" <?= old('priority') == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                            <option value="stat" <?= old('priority') == 'stat' ? 'selected' : '' ?>>Critical</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="test-types-checklist-container">
                                    <div class="test-types-checklist">
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="Blood Test">
                                                <span class="test-type-checkbox-text">Blood Test</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="Urine Test">
                                                <span class="test-type-checkbox-text">Urine Test</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="X-Ray">
                                                <span class="test-type-checkbox-text">X-Ray</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="MRI Scan">
                                                <span class="test-type-checkbox-text">MRI Scan</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="CT Scan">
                                                <span class="test-type-checkbox-text">CT Scan</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="Ultrasound">
                                                <span class="test-type-checkbox-text">Ultrasound</span>
                                            </label>
                                        </div>
                                        <div class="test-type-checkbox-item">
                                            <label class="test-type-checkbox-label">
                                                <input type="checkbox" class="test-type-checkbox" name="test_types_check[]" value="ECG">
                                                <span class="test-type-checkbox-text">ECG</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="selectedTestTypesCount" class="selected-test-types-count"></div>
                                    <input type="hidden" id="testTypesJson" name="test_types" value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="clinicalNotes">Clinical Notes</label>
                            <textarea class="form-control" id="clinicalNotes" name="clinical_notes" rows="2" 
                                      placeholder="Enter clinical notes or special instructions"><?= old('clinical_notes') ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions-container">
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
        const patientInput = $('#patientName');
        const patientIdInput = $('#patientId');
        const suggestionsDiv = $('#patientSuggestions');
        let suggestTimer = null;

        function renderSuggestions(items) {
            if (!Array.isArray(items) || items.length === 0) {
                suggestionsDiv.hide();
                return;
            }
            let html = '';
            items.forEach(p => {
                const safeName = (p.name || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const type = p.type ? ` <small style="color:#6c757d">(${p.type})</small>` : '';
                html += `<div class="suggestion-item" data-id="${p.id}" data-name="${safeName}">${safeName}${type}</div>`;
            });
            suggestionsDiv.html(html).show();
        }

        function fetchSuggestions(q) {
            $.getJSON('<?= base_url('laboratory/patient/suggest') ?>', { q }, function(resp){
                if (resp && resp.success) {
                    renderSuggestions(resp.results || []);
                } else {
                    suggestionsDiv.hide();
                }
            }).fail(function(){ suggestionsDiv.hide(); });
        }

        patientInput.on('input', function(){
            const currentValue = $(this).val();
            const q = currentValue.trim();
            const selectedName = (patientInput.data('selected-name') || '').toString();
            
            // If patient name is cleared/erased, re-enable the form
            if (q === '' || currentValue === '') {
                patientIdInput.val('');
                patientInput.data('selected-id', '');
                patientInput.data('selected-name', '');
                patientInput.removeClass('is-valid is-invalid');
                // Re-enable form when name is cleared
                enableForm();
                // Hide any error messages
                $('#patientErrorAlert').fadeOut(300, function() {
                    $(this).remove();
                });
                return;
            }
            
            // Clear patient ID when user types something different from the stored selection
            if (currentValue !== selectedName) {
                patientIdInput.val('');
                patientInput.data('selected-id', '');
                patientInput.removeClass('is-valid is-invalid');
                // Re-enable form when name is changed
                enableForm();
                // Hide any error messages
                $('#patientErrorAlert').fadeOut(300, function() {
                    $(this).remove();
                });
            } else if (patientIdInput.val()) {
                // Keep the valid state when the input still matches the stored selection
                patientInput.removeClass('is-invalid').addClass('is-valid');
            }
            
            if (suggestTimer) clearTimeout(suggestTimer);
            if (q.length < 2) { 
                suggestionsDiv.hide(); 
                return; 
            }
            suggestTimer = setTimeout(() => fetchSuggestions(q), 200);
        });

        $(document).on('click', '.suggestion-item', function(e){
            e.preventDefault();
            e.stopPropagation();
            const name = $(this).data('name');
            const id = $(this).data('id');
            
            // Validate data exists - handle id: 0 case
            if (!name || (id === null || id === undefined || id === '')) {
                console.error('Invalid patient data:', {name, id});
                return;
            }
            
            // Store selected info before updating the visible input to avoid input-event clearing
            patientInput.data('selected-name', name);
            patientInput.data('selected-id', id);
            
            // Set values
            patientInput.val(name);
            patientIdInput.val(id);
            
            // Add visual feedback
            patientInput.removeClass('is-invalid').addClass('is-valid');
            
            // Hide suggestions
            suggestionsDiv.hide();
            
            // Check for pending requests immediately after selection
            checkPendingRequest(id);
        });
        
        // Function to check if patient has pending requests
        function checkPendingRequest(patientId) {
            if (!patientId) {
                // Enable form if no patient selected
                enableForm();
                return;
            }
            
            $.getJSON('<?= base_url('laboratory/request/check-pending') ?>', { patient_id: patientId }, function(resp) {
                if (resp && resp.success) {
                    if (resp.has_pending) {
                        // Show error message at the top of the form
                        let errorAlert = $('#patientErrorAlert');
                        if (errorAlert.length === 0) {
                            errorAlert = $('<div id="patientErrorAlert" class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle"></i> <span>' + resp.message + '</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                            // Insert at the very top of the form, as the first element
                            $('#labRequestForm').prepend(errorAlert);
                        } else {
                            errorAlert.find('span').text(resp.message);
                            errorAlert.fadeIn();
                        }
                        
                        // Auto-hide after 5 seconds
                        setTimeout(function() {
                            $('#patientErrorAlert').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 5000);
                        
                        patientInput.removeClass('is-valid').addClass('is-invalid');
                        
                        // Disable form - prevent selecting test types and submitting
                        disableForm();
                    } else {
                        // No pending requests - clear any error and enable form
                        $('#patientErrorAlert').fadeOut(300, function() {
                            $(this).remove();
                        });
                        patientInput.removeClass('is-invalid').addClass('is-valid');
                        enableForm();
                    }
                }
            }).fail(function() {
                // Silently fail - don't block user if check fails
                console.warn('Failed to check pending requests');
                enableForm(); // Enable form if check fails
            });
        }
        
        // Function to disable form when patient has pending request
        function disableForm() {
            // Disable all test type checkboxes
            $('.test-type-checkbox').prop('disabled', true).css('opacity', '0.5');
            
            // Disable submit button
            $('#labRequestForm button[type="submit"]').prop('disabled', true).css('opacity', '0.5');
            
            // Disable other form inputs (but NOT patient name - allow it to be changed)
            $('#testDate, #priority, #clinicalNotes').prop('disabled', true).css('opacity', '0.5');
            
            // Disable test type container but keep patient name editable
            $('.test-types-checklist-container').css('pointer-events', 'none').css('opacity', '0.5');
        }
        
        // Function to enable form
        function enableForm() {
            // Enable all test type checkboxes
            $('.test-type-checkbox').prop('disabled', false).css('opacity', '1');
            
            // Enable submit button
            $('#labRequestForm button[type="submit"]').prop('disabled', false).css('opacity', '1');
            
            // Enable other form inputs
            $('#testDate, #priority, #clinicalNotes').prop('disabled', false).css('opacity', '1');
            
            // Enable test type container
            $('.test-types-checklist-container').css('pointer-events', 'auto').css('opacity', '1');
        }

        $(document).on('click', function(e){
            if (!$(e.target).closest('#patientName, #patientSuggestions').length) {
                suggestionsDiv.hide();
            }
        });

        patientInput.on('keydown', function(e){
            const items = $('.suggestion-item');
            const current = $('.suggestion-item.active');
            if (e.key === 'ArrowDown') { e.preventDefault(); (current.length? current.removeClass('active').next(): items.first()).addClass('active'); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); (current.length? current.removeClass('active').prev(): items.last()).addClass('active'); }
            else if (e.key === 'Enter') { if (current.length){ e.preventDefault(); current.click(); } }
            else if (e.key === 'Escape') { suggestionsDiv.hide(); }
        });

        // Validate test date to prevent past dates
        $('#testDate').on('change', function() {
            const selectedDate = $(this).val();
            const today = new Date().toISOString().split('T')[0];
            if (selectedDate && selectedDate < today) {
                alert('Test date cannot be in the past. Please select today or a future date.');
                $(this).val(today);
            }
        });

        // Multiple Test Types Management - Checkbox List
        function updateTestTypesJson() {
            const checkboxes = $('.test-type-checkbox:checked');
            const selectedTestTypes = [];
            checkboxes.each(function() {
                selectedTestTypes.push($(this).val());
            });
            
            const hiddenInput = $('#testTypesJson');
            hiddenInput.val(JSON.stringify(selectedTestTypes));
            
            // Update count display
            const count = selectedTestTypes.length;
            const countEl = $('#selectedTestTypesCount');
            if (count > 0) {
                countEl.html(`<div class="test-types-selected-info"><i class="fas fa-check-circle"></i> <strong>${count}</strong> test type${count !== 1 ? 's' : ''} selected</div>`);
            } else {
                countEl.html('<div class="test-types-selected-info text-muted"><i class="fas fa-info-circle"></i> Please select at least one test type</div>');
            }
        }
        
        // Handle checkbox changes
        $(document).on('change', '.test-type-checkbox', function() {
            updateTestTypesJson();
        });
        
        // Initialize display
        updateTestTypesJson();

        // Form submission validation
        $('#labRequestForm').on('submit', function(e) {
            const patientName = $('#patientName').val().trim();
            const patientId = $('#patientId').val().trim();
            const testDate = $('#testDate').val();
            const today = new Date().toISOString().split('T')[0];
            
            // Validate patient ID exists (must be selected from suggestions)
            if (!patientId || patientId === '') {
                e.preventDefault();
                patientInput.removeClass('is-valid').addClass('is-invalid');
                
                // Show error message
                let errorAlert = $('#patientErrorAlert');
                if (errorAlert.length === 0) {
                    errorAlert = $('<div id="patientErrorAlert" class="alert alert-danger" role="alert" style="margin-top: 10px;"><i class="fas fa-exclamation-circle"></i> Please select a valid patient from the suggestions dropdown.</div>');
                    patientInput.closest('.form-group').after(errorAlert);
                } else {
                    errorAlert.fadeIn();
                }
                
                $('#patientName').focus();
                
                // Show suggestions if user has typed something
                if (patientName.length >= 2) {
                    fetchSuggestions(patientName);
                }
                
                return false;
            }
            
            // Patient ID is valid
            patientInput.removeClass('is-invalid').addClass('is-valid');
            $('#patientErrorAlert').fadeOut();
            
            const selectedCount = $('.test-type-checkbox:checked').length;
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one test type');
                $('.test-type-checkbox').first().focus();
                return false;
            }
            
            // Validate that test date is not in the past
            if (testDate && testDate < today) {
                e.preventDefault();
                alert('Test date cannot be in the past. Please select today or a future date.');
                $('#testDate').focus();
                return false;
            }
            
            return true;
        });
    });
    </script>
<?= $this->endSection() ?>
