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
            const q = $(this).val().trim();
            patientIdInput.val('');
            if (suggestTimer) clearTimeout(suggestTimer);
            if (q.length < 2) { suggestionsDiv.hide(); return; }
            suggestTimer = setTimeout(() => fetchSuggestions(q), 200);
        });

        $(document).on('click', '.suggestion-item', function(){
            const name = $(this).data('name');
            const id = $(this).data('id');
            patientInput.val(name);
            patientIdInput.val(id);
            suggestionsDiv.hide();
        });

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

        $('#labRequestForm').on('submit', function(e) {
            e.preventDefault();
            const formData = {
                patient_name: $('#patientName').val(),
                patient_id: $('#patientId').val(),
                test_type: $('#testType').val(),
                priority: $('#priority').val(),
                clinical_notes: $('#clinicalNotes').val(),
                test_date: $('#testDate').val()
            };
            if (!formData.patient_name || !formData.test_type) { alert('Please fill in all required fields'); return; }
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) { alert('Lab request submitted successfully!'); $('#labRequestForm')[0].reset(); }
                    else { alert('Error: ' + (response.message || 'Failed to submit request')); }
                },
                error: function() { $('#labRequestForm')[0].submit(); }
            });
        });
    });
    </script>
<?= $this->endSection() ?>
