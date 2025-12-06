<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Add Test Result<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid py-4">
        <div class="composite-card billing-card" style="margin-top:0;">
            <div class="composite-header">
                <h1 class="composite-title">Add Test Result</h1>
            </div>
            <div class="card-body">
        <div class="card" style="box-shadow: none; border: none; margin: 0;">
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
                        <input type="file" class="form-control file-input-hidden" id="resultFiles" name="result_files[]" accept=".pdf,.csv,.txt,.xml,.json,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png" multiple>
                        <div id="fileDropzone" class="file-dropzone">
                            <div class="file-dropzone-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-dropzone-text">
                                <strong>Drag & drop analyzer files here</strong>
                                <span class="file-dropzone-subtext">or use the button below to browse and add files one at a time</span>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm file-add-btn" id="addFilesBtn">
                                <i class="fas fa-folder-open"></i> Add Files
                            </button>
                        </div>
                        <small class="file-upload-hint">You can drag/drop or click "Add Files" repeatedly—each file will be queued without using Ctrl-select.</small>
                        <div id="selectedFilesContainer" class="selected-files-container">
                            <div class="selected-files-header">
                                <i class="fas fa-folder-open"></i>
                                <span>Selected Files</span>
                                <span class="selected-files-count" id="selectedFilesCount">0</span>
                            </div>
                            <div id="selectedFilesList" class="selected-files-list"></div>
                        </div>
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
            const container = document.getElementById('selectedFilesContainer');
            const countEl = document.getElementById('selectedFilesCount');
            
            selectedFilesList.innerHTML = '';

            if (!selectedFiles.length) {
                if (container) container.style.display = 'none';
                if (countEl) countEl.textContent = '0';
                return;
            }

            if (container) container.style.display = 'block';
            if (countEl) countEl.textContent = selectedFiles.length;

            selectedFiles.forEach((file, index) => {
                const fileCard = document.createElement('div');
                fileCard.className = 'selected-file-card';
                
                const fileIcon = document.createElement('div');
                fileIcon.className = 'selected-file-icon-wrapper';
                const icon = document.createElement('i');
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    icon.className = 'fas fa-file-image';
                } else if (['pdf'].includes(ext)) {
                    icon.className = 'fas fa-file-pdf';
                } else if (['doc', 'docx'].includes(ext)) {
                    icon.className = 'fas fa-file-word';
                } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                    icon.className = 'fas fa-file-excel';
                } else {
                    icon.className = 'fas fa-file';
                }
                fileIcon.appendChild(icon);
                
                const fileInfo = document.createElement('div');
                fileInfo.className = 'selected-file-info';
                
                const fileName = document.createElement('div');
                fileName.className = 'selected-file-name';
                fileName.textContent = file.name;
                
                const fileMeta = document.createElement('div');
                fileMeta.className = 'selected-file-meta';
                const fileSize = (file.size / 1024).toFixed(2);
                fileMeta.innerHTML = `<span class="file-size">${fileSize} KB</span><span class="file-separator">•</span><span class="file-type">${ext.toUpperCase()}</span>`;
                
                fileInfo.appendChild(fileName);
                fileInfo.appendChild(fileMeta);
                
                const fileActions = document.createElement('div');
                fileActions.className = 'selected-file-actions';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'selected-file-remove';
                removeBtn.setAttribute('aria-label', 'Remove file');
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    syncNativeInput();
                    renderSelectedFiles();
                });
                
                fileActions.appendChild(removeBtn);
                
                fileCard.appendChild(fileIcon);
                fileCard.appendChild(fileInfo);
                fileCard.appendChild(fileActions);
                
                selectedFilesList.appendChild(fileCard);
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
