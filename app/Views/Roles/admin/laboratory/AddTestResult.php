<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Add Test Result<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header">
            <h1 class="composite-title">Add Test Result</h1>
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

            <form id="addResultForm">
                <?= csrf_field() ?>
                <input type="hidden" name="test_id" id="test_id" value="<?= esc($testResult['id'] ?? '') ?>">

                <!-- Patient Information Card -->
                <div class="info-grid" style="margin-bottom: 2rem;">
                    <div class="info-card">
                        <div class="info-label">Request ID</div>
                        <div class="info-value"><?= esc($testResult['id'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Patient Name</div>
                        <div class="info-value"><?= esc($testResult['test_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Patient ID</div>
                        <div class="info-value"><?= esc($testResult['patient_id'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Test Date</div>
                        <div class="info-value">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?= !empty($testResult['test_date']) ? date('F j, Y', strtotime($testResult['test_date'])) : 'N/A' ?>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge <?= strtolower($testResult['status'] ?? 'pending') === 'completed' ? 'badge-success' : (strtolower($testResult['status'] ?? 'pending') === 'in_progress' ? 'badge-primary' : 'badge-warning') ?> px-3 py-2">
                                <i class="fas <?= strtolower($testResult['status'] ?? 'pending') === 'completed' ? 'fa-check-circle' : (strtolower($testResult['status'] ?? 'pending') === 'in_progress' ? 'fa-clock' : 'fa-hourglass-half') ?> me-1"></i>
                                <?= ucfirst($testResult['status'] ?? 'Pending') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Test Types & Results Card -->
                <div id="testTypesContainer">
                        <?php 
                        $testTypeItems = $testTypes ?? [];
                        $hasAnyEditable = false;
                        
                        foreach ($testTypeItems as $index => $item): 
                            $testType = is_array($item) ? ($item['name'] ?? '') : (string)$item;
                            $isCompleted = is_array($item) ? ($item['completed'] ?? false) : false;
                            if (empty($testType)) continue;
                            if (!$isCompleted) $hasAnyEditable = true;
                        ?>
                        <div class="section-card test-type-section <?= $isCompleted ? 'lab-test-completed' : '' ?>" data-test-type="<?= esc($testType) ?>" style="margin-bottom: 2rem; border-left: 4px solid <?= $isCompleted ? '#28a745' : '#007bff' ?>;">
                            <div class="test-type-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e0e0e0;">
                                <h3 class="section-title" style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #2c3e50;">
                                    <i class="fas fa-vial me-2"></i>
                                    <?= esc(strtoupper($testType)) ?>
                                    <?php if ($isCompleted): ?>
                                        <i class="fas fa-check-circle text-success ms-2" style="font-size: 0.9em;" title="Results finalized"></i>
                                    <?php endif; ?>
                                </h3>
                                <span class="badge <?= $isCompleted ? 'badge-success' : 'badge-warning' ?> px-3 py-2">
                                    <i class="fas <?= $isCompleted ? 'fa-check-circle' : 'fa-hourglass-half' ?> me-1"></i>
                                    <?= $isCompleted ? 'Completed' : 'Pending' ?>
                                </span>
                            </div>
                            
                            <?php if ($isCompleted): ?>
                            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 4px; color: #6c757d; font-size: 0.875rem;">
                                <i class="fas fa-info-circle me-2"></i>
                                Completed on <?= !empty($testResult['updated_at']) ? date('F j, Y, g:i A', strtotime($testResult['updated_at'])) : date('F j, Y') ?> by <?= esc(session()->get('username') ?? 'Staff') ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!$isCompleted): ?>
                            <div class="lab-test-upload-section">
                                <!-- Upload Result Files -->
                                <div style="margin-bottom: 1.5rem;">
                                    <h4 style="font-size: 0.95rem; color: #495057; margin-bottom: 0.75rem; font-weight: 600;">
                                        <i class="fas fa-file-upload me-2"></i> Upload Result Files <span class="text-danger">*</span>
                                    </h4>
                                    <div class="lab-file-dropzone" data-test-type="<?= esc($testType) ?>" style="border: 2px dashed #cbd5e0; border-radius: 8px; padding: 2rem; text-align: center; background: #f8f9fa; transition: all 0.3s ease; cursor: pointer;">
                                        <input type="file" 
                                               class="lab-file-input" 
                                               id="file_input_<?= str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($testType)) ?>" 
                                               data-test-type="<?= esc($testType) ?>"
                                               accept=".pdf,.csv,.txt,.xml,.json,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png" 
                                               multiple
                                               style="display: none;">
                                        <div class="lab-dropzone-content">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                                            <p style="font-size: 1rem; color: #2c3e50; font-weight: 500; margin-bottom: 0.5rem;">Click to upload files or drag and drop</p>
                                            <p style="font-size: 0.875rem; color: #6c757d; margin: 0;">PDF, CSV, TXT, XML, Excel, JSON, Word, Images (Max 10MB per file)</p>
                                        </div>
                                    </div>
                                    <div class="lab-uploaded-files" id="files_<?= str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($testType)) ?>" style="display: none; margin-top: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; padding: 0.875rem 1rem; background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%); border-radius: 6px; border: 1px solid #dee2e6;">
                                            <i class="fas fa-folder-open" style="color: #495057; font-size: 1.1rem;"></i>
                                            <span style="font-weight: 600; color: #2c3e50; font-size: 0.95rem;">Uploaded Files (<span class="file-count">0</span>)</span>
                                        </div>
                                        <div class="lab-files-list" style="background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);"></div>
                                    </div>
                                </div>

                                <!-- Notes Section -->
                                <div style="margin-bottom: 1.5rem;">
                                    <h4 style="font-size: 0.95rem; color: #495057; margin-bottom: 0.75rem; font-weight: 600;">
                                        <i class="fas fa-comment-alt me-2"></i> Notes / Comments <span style="font-weight: normal; color: #6c757d;">(Optional)</span>
                                    </h4>
                                    <textarea class="form-control" 
                                              id="notes_<?= str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($testType)) ?>" 
                                              rows="3" 
                                              placeholder="Enter any notes or comments about this test..."
                                              style="border: 1px solid #cbd5e0; border-radius: 6px; resize: vertical; padding: 0.75rem;"></textarea>
                                </div>

                                <!-- Save Button for this test type -->
                                <div class="lab-test-save-section">
                                    <button type="button" 
                                            class="btn btn-success btn-save-test-type" 
                                            data-test-id="<?= esc($testResult['id'] ?? '') ?>"
                                            data-test-type="<?= esc($testType) ?>"
                                            disabled
                                            style="min-width: 180px; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <i class="fas fa-save me-2"></i> Save Results
                                    </button>
                                </div>
                            </div>
                            <?php else: ?>
                            <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6; text-align: center; color: #6c757d;">
                                <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 0.75rem; color: #6c757d;"></i>
                                <p style="margin: 0; font-weight: 500;">This test type is completed and read-only. Results cannot be modified.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($testTypeItems)): ?>
                        <div class="section-card" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; color: #6c757d; margin-bottom: 1rem;"></i>
                            <p style="color: #6c757d; margin: 0; font-size: 1rem;">No test types found for this request.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                <div class="form-actions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
                    <a href="<?= base_url('laboratory/testresult') ?>" class="btn btn-secondary" style="min-width: 150px; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <i class="fas fa-arrow-left me-2"></i> Back to Results
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addResultForm');
    const testId = document.getElementById('test_id').value;
    const fileInputs = document.querySelectorAll('.lab-file-input');
    const dropzones = document.querySelectorAll('.lab-file-dropzone');
    const saveButtons = document.querySelectorAll('.btn-save-test-type');
    
    let testFiles = {}; // { testType: [File objects] }

    // Update save button state for a specific test type
    function updateSaveButton(testType) {
        const saveBtn = document.querySelector(`.btn-save-test-type[data-test-type="${testType}"]`);
        if (!saveBtn) return;
        
        const files = testFiles[testType] || [];
        saveBtn.disabled = files.length === 0;
    }

    // File upload handling
    fileInputs.forEach(input => {
        const testType = input.dataset.testType;
        
        input.addEventListener('change', function(e) {
            handleFiles(testType, Array.from(e.target.files));
            updateSaveButton(testType);
        });
    });

    // Drag and drop
    dropzones.forEach(dropzone => {
        const testType = dropzone.dataset.testType;
        const input = dropzone.querySelector('.lab-file-input');
        
        ['dragenter', 'dragover'].forEach(event => {
            dropzone.addEventListener(event, function(e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'dragend'].forEach(event => {
            dropzone.addEventListener(event, function() {
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            handleFiles(testType, Array.from(e.dataTransfer.files));
            updateSaveButton(testType);
        });

        dropzone.addEventListener('click', function() {
            input.click();
        });
    });

    function handleFiles(testType, files) {
        if (!testFiles[testType]) {
            testFiles[testType] = [];
        }
        
        const allowedExtensions = ['pdf','csv','txt','xml','json','xls','xlsx','doc','docx','jpg','jpeg','png'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        files.forEach(file => {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowedExtensions.includes(ext)) {
                alert(`File "${file.name}" has unsupported format. Allowed: PDF, CSV, TXT, XML, JSON, Excel, Word, Images.`);
                return;
            }
            if (file.size > maxSize) {
                alert(`File "${file.name}" exceeds 10MB limit.`);
                return;
            }
            
            // Check for duplicates
            const exists = testFiles[testType].some(f => f.name === file.name && f.size === file.size);
            if (!exists) {
                testFiles[testType].push(file);
            }
        });
        
        renderFiles(testType);
    }

    function renderFiles(testType) {
        const files = testFiles[testType] || [];
        // Use a safe ID that matches PHP's base64_encode
        const testTypeId = btoa(testType).replace(/[+/=]/g, function(match) {
            return {'+': '-', '/': '_', '=': ''}[match];
        });
        const container = document.getElementById('files_' + testTypeId);
        if (!container) {
            // Fallback: try direct base64
            const container2 = document.getElementById('files_' + btoa(testType));
            if (container2) {
                renderFilesList(container2, files);
                return;
            }
            return;
        }
        renderFilesList(container, files);
    }
    
    function renderFilesList(container, files) {
        
        const list = container.querySelector('.lab-files-list');
        const count = container.querySelector('.file-count');
        
        if (files.length === 0) {
            container.style.display = 'none';
            if (count) count.textContent = '0';
            return;
        }
        
        container.style.display = 'block';
        if (count) count.textContent = files.length;
        
        if (!list) return;
        list.innerHTML = '';
        
        files.forEach((file, index) => {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const size = (file.size / 1024).toFixed(2);
            let iconClass = 'fas fa-file';
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) iconClass = 'fas fa-file-image';
            else if (ext === 'pdf') iconClass = 'fas fa-file-pdf';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'fas fa-file-word';
            else if (['xls', 'xlsx', 'csv'].includes(ext)) iconClass = 'fas fa-file-excel';
            
            const fileItem = document.createElement('div');
            fileItem.className = 'lab-file-item';
            const testType = container.closest('.lab-test-type-card')?.dataset.testType || '';
            fileItem.innerHTML = `
                <div class="lab-file-info">
                    <i class="${iconClass} lab-file-icon"></i>
                    <div class="lab-file-details">
                        <div class="lab-file-name">${file.name}</div>
                        <div class="lab-file-meta">${size} KB</div>
                    </div>
                </div>
                <button type="button" class="lab-file-remove" onclick="removeFile('${testType}', ${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(fileItem);
        });
    }

    window.removeFile = function(testType, index) {
        if (testFiles[testType]) {
            testFiles[testType].splice(index, 1);
            renderFiles(testType);
            updateSaveButton(testType);
        }
    };

    // Save button click handlers
    saveButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const testType = this.dataset.testType;
            const files = testFiles[testType] || [];
            
            if (files.length === 0) {
                alert(`Please upload at least one file for "${testType}".`);
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const formData = new FormData();
            formData.append('test_id', testId);
            formData.append('test_type', testType);
            
            // Add CSRF token
            const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
            if (csrfInput) {
                formData.append('<?= csrf_token() ?>', csrfInput.value);
            }
            
            // Add files
            files.forEach(file => {
                formData.append(`result_files[${testType}][]`, file);
            });
            
            // Add notes
            const notesInput = document.getElementById(`notes_${btoa(testType).replace(/[+/=]/g, function(match) {
                return {'+': '-', '/': '_', '=': ''}[match];
            })}`);
            if (notesInput && notesInput.value.trim()) {
                formData.append('notes', notesInput.value.trim());
            }

            try {
                const response = await fetch('<?= site_url('laboratory/testresult/save-test-type') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data?.message || 'Server error while saving.');
                }

                // Success - reload page to show updated state
                alert(data.message || 'Test results saved successfully!');
                window.location.reload();
            } catch (err) {
                alert(err.message || 'Unexpected error occurred.');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
