<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Add Test Result<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Add Test Result</h1>
        </div>

        <div class="card">
            <div class="card-header">
                Test Information
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('laboratory/testresult/add') ?>" id="addResultForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="test_id" value="<?= esc($testResult['id']) ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Patient Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($testResult['test_name']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Test Type</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($testResult['test_type']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Test Date</label>
                            <input type="text" class="form-control" value="<?= date('F j, Y', strtotime($testResult['test_date'])) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" value="<?= ucfirst($testResult['status']) ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="resultFiles">Analyzer Output Files</label>
                        <style>
                            .file-dropzone {
                                border: 2px dashed #9ca3af;
                                border-radius: 10px;
                                padding: 1.25rem;
                                text-align: center;
                                background: #f9fafb;
                                transition: border-color 0.2s ease, background 0.2s ease;
                                cursor: pointer;
                            }
                            .file-dropzone.dragover {
                                border-color: #2563eb;
                                background: #eff6ff;
                            }
                            .file-dropzone button {
                                margin-top: 0.75rem;
                            }
                            .selected-files-list {
                                margin-top: 0.75rem;
                                padding-left: 1.2rem;
                                font-size: 0.9rem;
                                color: #111827;
                                list-style: decimal;
                            }
                            .selected-files-list li {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 0.75rem;
                            }
                            .selected-files-list button {
                                border: none;
                                background: transparent;
                                color: #dc2626;
                                cursor: pointer;
                            }
                        </style>
                        <input type="file" class="form-control" id="resultFiles" name="result_files[]" accept=".pdf,.csv,.txt,.xml,.json,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png" multiple style="display:none;">
                        <div id="fileDropzone" class="file-dropzone">
                            <strong>Drag & drop analyzer files here</strong>
                            <div style="color:#6b7280;font-size:0.9rem;margin-top:0.4rem;">
                                or use the button below to browse and add files one at a time.
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addFilesBtn">
                                <i class="fas fa-folder-open"></i> Add Files
                            </button>
                        </div>
                        <small class="form-text text-muted" style="display:block;margin-top:0.4rem;">You can drag/drop or click “Add Files” repeatedly—each file will be queued without using Ctrl-select.</small>
                        <ul id="selectedFilesList" class="selected-files-list"></ul>
                    </div>

                    <div class="form-group">
                        <label for="interpretation" class="form-label">Clinical Interpretation (optional)</label>
                        <textarea class="form-control" id="interpretation" name="interpretation" rows="3" placeholder="Enter clinical interpretation of results..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="<?= base_url('laboratory/testresult') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>Back to Results
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>Save Result & Mark Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const viewUrlBase = '<?= base_url('laboratory/testresult/view/') ?>';
        // Advanced multi-file picker helpers
        const fileInput = document.getElementById('resultFiles');
        const fileDropzone = document.getElementById('fileDropzone');
        const addFilesBtn = document.getElementById('addFilesBtn');
        const selectedFilesList = document.getElementById('selectedFilesList');
        let selectedFiles = [];

        const syncNativeInput = () => {
            if (!fileInput) return;
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        };

        const renderSelectedFiles = () => {
            if (!selectedFilesList) return;
            selectedFilesList.innerHTML = '';

            if (!selectedFiles.length) {
                const placeholder = document.createElement('li');
                placeholder.style.color = '#6b7280';
                placeholder.textContent = 'No files selected yet.';
                selectedFilesList.appendChild(placeholder);
                return;
            }

            selectedFiles.forEach((file, index) => {
                const li = document.createElement('li');
                const nameSpan = document.createElement('span');
                nameSpan.textContent = file.name;
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.setAttribute('aria-label', 'Remove file');
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    syncNativeInput();
                    renderSelectedFiles();
                });

                li.appendChild(nameSpan);
                li.appendChild(removeBtn);
                selectedFilesList.appendChild(li);
            });
        };

        const addFiles = (fileList) => {
            if (!fileList || !fileList.length) return;
            const allowedExtensions = ['pdf','csv','txt','xml','json','xls','xlsx','doc','docx','jpg','jpeg','png'];
            const disallowed = [];

            Array.from(fileList).forEach(file => {
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                if (!allowedExtensions.includes(ext)) {
                    disallowed.push(file.name);
                    return;
                }

                const exists = selectedFiles.some(existing => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified);
                if (!exists) {
                    selectedFiles.push(file);
                }
            });

            if (disallowed.length) {
                alert('These files have unsupported formats and were skipped:\n- ' + disallowed.join('\n- '));
            }

            syncNativeInput();
            renderSelectedFiles();
        };

        if (addFilesBtn && fileInput) {
            addFilesBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', (e) => {
                addFiles(e.target.files);
                fileInput.value = '';
            });
        }

        if (fileDropzone) {
            ['dragenter', 'dragover'].forEach(evt => fileDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                fileDropzone.classList.add('dragover');
            }));

            ['dragleave', 'dragend'].forEach(evt => fileDropzone.addEventListener(evt, () => fileDropzone.classList.remove('dragover')));

            fileDropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                fileDropzone.classList.remove('dragover');
                addFiles(e.dataTransfer.files);
            });
        }

        renderSelectedFiles();

        // Form submission handling (AJAX to include custom file queue)
        document.getElementById('addResultForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitButton = this.querySelector('button[type="submit"]');

            if (!selectedFiles.length) {
                alert('Please upload at least one analyzer output file before saving.');
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }

            const formData = new FormData(this);
            formData.delete('result_files[]');
            selectedFiles.forEach(file => formData.append('result_files[]', file));

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                });

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                const data = isJson ? await response.json() : null;

                if (!response.ok) {
                    throw new Error(data?.message || 'Server error while saving the test result.');
                }

                if (data?.success) {
                    const testId = formData.get('test_id');
                    window.location.href = viewUrlBase + encodeURIComponent(testId);
                    return;
                }

                throw new Error(data?.message || 'Failed to save the test result.');
            } catch (err) {
                alert(err.message || 'Unexpected error occurred while saving.');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save"></i>Save Result & Mark Completed';
                }
            }
        });

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
        });
    </script>
<?= $this->endSection() ?>
