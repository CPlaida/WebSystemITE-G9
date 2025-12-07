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
                    <div class="info-label">Test Type</div>
                    <div class="info-value"><?= esc(ucfirst($testResult['test_type'])) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        <span class="badge <?= strtolower($testResult['priority_display']) === 'urgent' ? 'badge-danger' : (strtolower($testResult['priority_display']) === 'high' ? 'badge-warning' : 'badge-success') ?> px-3 py-2" style="font-size:0.875rem;">
                            <i class="fas <?= strtolower($testResult['priority_display']) === 'urgent' ? 'fa-exclamation-triangle' : 'fa-info-circle' ?> me-1"></i>
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
        </div>

        <!-- Action Buttons -->
        <div class="card-footer no-print test-result-footer">
            <?php if (strtolower($testResult['status']) !== 'completed'): ?>
            <a href="<?= base_url('laboratory/testresult/add/' . $testResult['id']) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Test Results
            </a>
            <?php endif; ?>
            <?php if (strtolower($testResult['status']) === 'completed'): ?>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Result
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
