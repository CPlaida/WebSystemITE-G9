<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Test Result Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
        <div class="composite-header test-result-header">
            <h1 class="composite-title">Test Result Details</h1>
            <div class="no-print">
                <span class="badge <?= strtolower($testResult['status']) === 'completed' ? 'badge-success' : (strtolower($testResult['status']) === 'in_progress' ? 'badge-primary' : 'badge-warning') ?> px-3 py-2 test-result-status-badge">
                    <i class="fas <?= strtolower($testResult['status']) === 'completed' ? 'fa-check-circle' : (strtolower($testResult['status']) === 'in_progress' ? 'fa-clock' : 'fa-hourglass-half') ?> me-1"></i>
                    <?= ucfirst($testResult['status']) ?>
                </span>
            </div>
        </div>

        <div class="card-body">
            <!-- Test Information Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Request ID</div>
                    <div class="info-value"><?= esc($testResult['test_id'] ?? 'N/A') ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Patient Name</div>
                    <div class="info-value"><?= esc($testResult['patient_name']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Test Types</div>
                    <div class="info-value">
                        <?php 
                        if (!empty($testResult['all_tests'])) {
                            $testTypeNames = array_map(function($test) {
                                return ucfirst($test['test_type']);
                            }, $testResult['all_tests']);
                            echo esc(implode(', ', $testTypeNames));
                        } else {
                            echo esc(ucfirst($testResult['test_type'] ?? 'N/A'));
                        }
                        ?>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        <span class="badge badge-secondary px-3 py-2" style="font-size:0.875rem; background-color: #6c757d; color: #fff;">
                            <i class="fas fa-info-circle me-1"></i>
                            <?= esc($testResult['priority_display']) ?>
                        </span>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-label">Test Date</div>
                    <div class="info-value">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?= esc($testResult['formatted_test_date']) ?>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-label">Test Time</div>
                    <div class="info-value">
                        <i class="fas fa-clock me-2"></i>
                        <?= esc($testResult['formatted_test_time']) ?>
                    </div>
                </div>
                <?php if (!empty($testResult['result_date'])): ?>
                <div class="info-card">
                    <div class="info-label">Result Date</div>
                    <div class="info-value">
                        <i class="fas fa-calendar-check me-2"></i>
                        <?= date('F j, Y', strtotime($testResult['result_date'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($testResult['technician_name'])): ?>
                <div class="info-card">
                    <div class="info-label">Technician</div>
                    <div class="info-value">
                        <i class="fas fa-user-md me-2"></i>
                        <?= esc($testResult['technician_name']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- All Test Types Section -->
            <?php if (!empty($testResult['all_tests'])): ?>
                <?php foreach ($testResult['all_tests'] as $index => $test): ?>
                <div class="section-card test-type-section" style="margin-bottom: 1.5rem;">
                    <div class="test-type-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e0e0e0;">
                        <h3 class="section-title" style="margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                            <input type="checkbox" 
                                   <?= (!empty($test['has_results']) || !empty($test['is_completed']) || strtolower($test['status']) === 'completed') ? 'checked' : '' ?> 
                                   disabled
                                   style="width: 18px; height: 18px; cursor: not-allowed;">
                            <i class="fas fa-vial"></i>
                            <?= esc(ucfirst($test['test_type'])) ?>
                        </h3>
                        <?php if (strtolower($test['status']) !== 'completed' && (empty($test['has_results']) && empty($test['is_completed']))): ?>
                        <span class="badge <?= strtolower($test['status']) === 'in_progress' ? 'badge-primary' : 'badge-warning' ?> px-3 py-2">
                            <i class="fas <?= strtolower($test['status']) === 'in_progress' ? 'fa-clock' : 'fa-hourglass-half' ?> me-1"></i>
                            <?= ucfirst($test['status']) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Clinical Notes for this test -->
                    <?php if (!empty($test['notes'])): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.95rem; color: #666; margin-bottom: 0.5rem;">
                            <i class="fas fa-stethoscope"></i> Clinical Notes
                        </h4>
                        <div class="notes-content" style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                            <?= nl2br(esc($test['notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Test Results Table for this test -->
                    <?php if (!empty($test['results']) && is_array($test['results'])): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.95rem; color: #666; margin-bottom: 0.5rem;">
                            <i class="fas fa-flask"></i> Test Results
                        </h4>
                        <div class="table-responsive">
                            <table class="data-table test-results-table">
                                <thead>
                                    <tr>
                                        <th>Test Parameter</th>
                                        <th>Result</th>
                                        <th>Reference Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($test['results'] as $parameter => $result): 
                                        $range = $test['normal_ranges'][$parameter] ?? 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($parameter) ?></td>
                                        <td><?= htmlspecialchars($result) ?></td>
                                        <td><?= htmlspecialchars($range) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- File Attachments for this test -->
                    <?php if (!empty($test['attachments']) || !empty($test['result_file_url'])): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.95rem; color: #666; margin-bottom: 0.5rem;">
                            <i class="fas fa-file-alt"></i> Result Files
                        </h4>
                        <?php if (!empty($test['attachments'])): ?>
                        <div class="attachment-list">
                            <?php foreach ($test['attachments'] as $file): ?>
                                <?php
                                    $fileIndex = isset($file['index']) ? (int)$file['index'] : 0;
                                    $downloadUrl = base_url('laboratory/testresult/download/' . $test['id']) . '?file=' . $fileIndex;
                                    $sizeKb = isset($file['size']) && $file['size'] ? round($file['size'] / 1024, 1) : null;
                                    $fileIcon = 'fa-file';
                                    if (isset($file['type'])) {
                                        if (strpos($file['type'], 'image') !== false) $fileIcon = 'fa-file-image';
                                        elseif (strpos($file['type'], 'pdf') !== false) $fileIcon = 'fa-file-pdf';
                                        elseif (strpos($file['type'], 'word') !== false) $fileIcon = 'fa-file-word';
                                    }
                                ?>
                                <div class="attachment-item">
                                    <div class="attachment-info">
                                        <div class="attachment-icon-container">
                                            <i class="fas <?= $fileIcon ?>"></i>
                                        </div>
                                        <div>
                                            <div class="attachment-name"><?= esc($file['label'] ?? 'Result File') ?></div>
                                            <?php if ($sizeKb): ?>
                                                <div class="attachment-size"><?= $sizeKb ?> KB</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="<?= esc($downloadUrl) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:0.5rem;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif (!empty($test['result_file_url'])): ?>
                        <div class="attachment-item">
                            <div class="attachment-info">
                                <div class="attachment-icon-container">
                                    <i class="fas fa-file-download"></i>
                                </div>
                                <div class="attachment-name"><?= esc($test['result_file_label'] ?? 'Result File') ?></div>
                            </div>
                            <a href="<?= esc($test['result_file_url']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:0.5rem;">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- No Results Message for this test -->
                    <?php if (empty($test['results']) || !is_array($test['results'])): ?>
                        <?php if (empty($test['attachments']) && empty($test['result_file_url'])): ?>
                        <div class="test-result-empty-state" style="text-align: center; padding: 2rem; color: #999;">
                            <i class="fas fa-flask" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <div>
                                No test results available yet for <?= esc(ucfirst($test['test_type'])) ?>. Results will appear here once the test is completed.
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback to single test display if all_tests is not set -->
                <!-- Clinical Notes -->
                <?php if (!empty($testResult['notes'])): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-stethoscope"></i>
                        Clinical Notes
                    </h3>
                    <div class="notes-content">
                        <?= nl2br(esc($testResult['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Test Results Table -->
                <?php if (!empty($testResult['results']) && is_array($testResult['results'])): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-flask"></i>
                        Test Results
                    </h3>
                    <div class="table-responsive">
                        <table class="data-table test-results-table">
                            <thead>
                                <tr>
                                    <th>Test Parameter</th>
                                    <th>Result</th>
                                    <th>Reference Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testResult['results'] as $parameter => $result): 
                                    $range = $testResult['normal_ranges'][$parameter] ?? 'N/A';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($parameter) ?></td>
                                    <td><?= htmlspecialchars($result) ?></td>
                                    <td><?= htmlspecialchars($range) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- File Attachments -->
                <?php if (!empty($testResult['attachments']) || !empty($testResult['result_file_url'])): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-file-alt"></i>
                        Result Files
                    </h3>
                    <?php if (!empty($testResult['attachments'])): ?>
                    <div class="attachment-list">
                        <?php foreach ($testResult['attachments'] as $file): ?>
                            <?php
                                $fileIndex = isset($file['index']) ? (int)$file['index'] : 0;
                                $downloadUrl = base_url('laboratory/testresult/download/' . $testResult['id']) . '?file=' . $fileIndex;
                                $sizeKb = isset($file['size']) && $file['size'] ? round($file['size'] / 1024, 1) : null;
                                $fileIcon = 'fa-file';
                                if (isset($file['type'])) {
                                    if (strpos($file['type'], 'image') !== false) $fileIcon = 'fa-file-image';
                                    elseif (strpos($file['type'], 'pdf') !== false) $fileIcon = 'fa-file-pdf';
                                    elseif (strpos($file['type'], 'word') !== false) $fileIcon = 'fa-file-word';
                                }
                            ?>
                            <div class="attachment-item">
                                <div class="attachment-info">
                                    <div class="attachment-icon-container">
                                        <i class="fas <?= $fileIcon ?>"></i>
                                    </div>
                                    <div>
                                        <div class="attachment-name"><?= esc($file['label'] ?? 'Result File') ?></div>
                                        <?php if ($sizeKb): ?>
                                            <div class="attachment-size"><?= $sizeKb ?> KB</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?= esc($downloadUrl) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:0.5rem;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif (!empty($testResult['result_file_url'])): ?>
                    <div class="attachment-item">
                        <div class="attachment-info">
                            <div class="attachment-icon-container">
                                <i class="fas fa-file-download"></i>
                            </div>
                            <div class="attachment-name"><?= esc($testResult['result_file_label'] ?? 'Result File') ?></div>
                        </div>
                        <a href="<?= esc($testResult['result_file_url']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:0.5rem;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Clinical Interpretation -->
                <?php
                    $interpretation = isset($testResult['interpretation']) && trim($testResult['interpretation']) !== ''
                        ? $testResult['interpretation']
                        : ($testResult['notes'] ?? '');
                ?>
                <?php if (!empty(trim($interpretation)) && !empty($testResult['results'])): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Clinical Interpretation
                    </h3>
                    <div class="interpretation-content">
                        <?= nl2br(htmlspecialchars($interpretation)) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- No Results Message -->
                <?php if (empty($testResult['results']) || !is_array($testResult['results'])): ?>
                    <?php if (empty($testResult['attachments']) && empty($testResult['result_file_url'])): ?>
                    <div class="section-card test-result-empty-state">
                        <i class="fas fa-flask"></i>
                        <div>
                            No test results available yet. Results will appear here once the test is completed.
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Mark Complete Button -->
        <?php if ($testResult['all_test_types_completed'] && strtolower($testResult['status'] ?? 'pending') !== 'completed' && in_array(session('role'), ['admin', 'labstaff'])): ?>
        <div class="card-footer no-print" style="background: #f8f9fa; border-top: 2px solid #28a745;">
            <div style="display: flex; justify-content: flex-end; align-items: center; padding: 1rem;">
                <form method="POST" action="<?= base_url('laboratory/testresult/mark-complete/' . $testResult['test_id']) ?>" style="display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this request as complete? This will lock the request and prevent further modifications.');">
                        <i class="fas fa-check-double"></i> Mark Request as Complete
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="card-footer no-print test-result-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="<?= base_url('laboratory/testresult') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
            <?php 
            $currentStatus = strtolower($testResult['status'] ?? 'pending');
            $isRequestCompleted = $currentStatus === 'completed';
            ?>
            <?php if ($isRequestCompleted): ?>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print All Results
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
