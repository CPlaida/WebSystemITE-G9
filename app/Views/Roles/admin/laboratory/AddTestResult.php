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
                <form method="post" action="<?= site_url('laboratory/testresult/add') ?>" id="addResultForm">
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

                    <div class="test-results">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Test Parameters</h3>
                        <div id="parametersContainer">
                            <div class="parameter-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 0.75rem; margin-bottom: 0.75rem; align-items: end;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Parameter Name</label>
                                    <input type="text" class="form-control" name="parameter_name[]" placeholder="e.g., Hemoglobin" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Result</label>
                                    <input type="text" class="form-control" name="parameter_result[]" placeholder="e.g., 12.5" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Reference Range</label>
                                    <input type="text" class="form-control" name="parameter_range[]" placeholder="e.g., 12.0-17.5 g/dL">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-secondary" onclick="removeParameter(this)" style="margin-top: 0;">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" onclick="addParameter()" style="margin-bottom: 0.75rem;">
                            <i class="fas fa-plus"></i>Add Parameter
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="interpretation" class="form-label">Clinical Interpretation</label>
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
        function addParameter() {
            const container = document.getElementById('parametersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'parameter-row';
            newRow.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 0.75rem; margin-bottom: 0.75rem; align-items: end;';
            
            newRow.innerHTML = `
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" class="form-control" name="parameter_name[]" placeholder="e.g., White Blood Cells" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" class="form-control" name="parameter_result[]" placeholder="e.g., 7.2" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" class="form-control" name="parameter_range[]" placeholder="e.g., 4.5-11.0 x10³/µL">
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" onclick="removeParameter(this)">Remove</button>
                </div>
            `;
            
            container.appendChild(newRow);
        }

        function removeParameter(button) {
            const container = document.getElementById('parametersContainer');
            if (container.children.length > 1) {
                button.closest('.parameter-row').remove();
            } else {
                alert('At least one parameter is required.');
            }
        }

        // Form submission handling
        document.getElementById('addResultForm').addEventListener('submit', function(e) {
            const parameterNames = document.querySelectorAll('input[name="parameter_name[]"]');
            const parameterResults = document.querySelectorAll('input[name="parameter_result[]"]');
            const submitButton = this.querySelector('button[type="submit"]');
            
            let hasValidParameters = false;
            for (let i = 0; i < parameterNames.length; i++) {
                if (parameterNames[i].value.trim() && parameterResults[i].value.trim()) {
                    hasValidParameters = true;
                    break;
                }
            }
            
            if (!hasValidParameters) {
                e.preventDefault();
                alert('Please enter at least one parameter with name and result.');
                return false;
            }
            
            // Show loading state
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
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
