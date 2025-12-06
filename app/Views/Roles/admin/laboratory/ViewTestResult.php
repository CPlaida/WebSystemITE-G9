<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Test Result Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid py-4">
        <div class="composite-card billing-card" style="margin-top:0;">
            <div class="composite-header">
                <h1 class="composite-title">Test Result Details</h1>
            </div>
            <div class="card-body">
        <div class="lab-receipt card card--detail" style="box-shadow: none; border: none; margin: 0;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px; background: transparent; border: none; padding: 0 0 1rem 0;">
                <h2 class="card-title" style="margin:0;">Test Result Details</h2>
                <div style="display:flex;align-items:center;gap:10px;" class="no-print">
                    <span class="status-pill <?= esc(strtolower($testResult['status'])) ?>">
                        <?= ucfirst($testResult['status']) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="kv-grid">
                    <div class="kv">
                        <div class="k">Request ID</div><div class="v"><?= esc($testResult['test_id'] ?? 'N/A') ?></div>
                        <div class="k">Patient Name</div><div class="v"><?= esc($testResult['patient_name']) ?></div>
                        <div class="k">Test Type</div><div class="v"><?= esc($testResult['test_type']) ?></div>
                        <div class="k">Priority</div>
                        <div class="v">
                            <span class="badge <?= strtolower($testResult['priority_display']) === 'urgent' ? 'badge-warning' : 'badge-success' ?>">
                                <?= esc($testResult['priority_display']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="kv">
                        <div class="k">Test Date</div><div class="v"><?= esc($testResult['formatted_test_date']) ?></div>
                        <div class="k">Test Time</div><div class="v"><?= esc($testResult['formatted_test_time']) ?></div>
                        <?php if (!empty($testResult['result_date'])): ?>
                        <div class="k">Result Date</div><div class="v"><?= date('F j, Y', strtotime($testResult['result_date'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($testResult['technician_name'])): ?>
                        <div class="k">Technician</div><div class="v"><?= esc($testResult['technician_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($testResult['notes'])): ?>
                <div class="section-title" style="margin-top:1.5rem;">Clinical Notes</div>
                <div class="info-value" style="white-space:pre-line;"><?= nl2br(esc($testResult['notes'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($testResult['results']) && is_array($testResult['results'])): ?>
        <div class="card card--detail">
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
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
                <?php if (!empty($testResult['attachments']) || !empty($testResult['result_file_url'])): ?>
                <div class="info-group" style="margin-top:1.5rem;">
                    <h2 class="card-title" style="margin-bottom:0.75rem;">Test Results</h2>
                    <?php if (!empty($testResult['attachments'])): ?>
                    <div class="attachment-list" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <?php foreach ($testResult['attachments'] as $file): ?>
                            <?php
                                $fileIndex = isset($file['index']) ? (int)$file['index'] : 0;
                                $downloadUrl = base_url('laboratory/testresult/download/' . $testResult['id']) . '?file=' . $fileIndex;
                                $sizeKb = isset($file['size']) && $file['size'] ? round($file['size'] / 1024, 1) : null;
                            ?>
                            <div class="attachment-item" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #e5e7eb;border-radius:6px;padding:0.6rem 0.9rem;">
                                <div>
                                    <strong><?= esc($file['label'] ?? 'Result File') ?></strong>
                                    <?php if ($sizeKb): ?>
                                        <small style="color:#6b7280;">(<?= $sizeKb ?> KB)</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= esc($downloadUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                                    <i class="fas fa-file-download"></i> Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif (!empty($testResult['result_file_url'])): ?>
                    <a href="<?= esc($testResult['result_file_url']) ?>" class="btn btn-outline-primary" target="_blank" rel="noopener">
                        <i class="fas fa-file-download"></i>
                        <?= esc($testResult['result_file_label'] ?? 'Download Result File') ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php
                    $interpretation = isset($testResult['interpretation']) && trim($testResult['interpretation']) !== ''
                        ? $testResult['interpretation']
                        : ($testResult['notes'] ?? '');
                ?>
                <?php if (!empty(trim($interpretation))): ?>
                <div class="info-group">
                    <div class="section-title">Clinical Interpretation</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($interpretation)) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if (strtolower($testResult['status']) === 'completed'): ?>
            <div class="card-footer no-print">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Result
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card card--detail">
            <div class="card-body">
                <div class="info-group">
                    <?php if (!empty($testResult['attachments'])): ?>
                    <div class="info-value" style="text-align: left;">
                        <h2 class="card-title" style="margin-bottom:0.5rem;">Test Results</h2>
                        <div class="attachment-list" style="display:flex;flex-direction:column;gap:0.5rem;">
                            <?php foreach ($testResult['attachments'] as $file): ?>
                                <?php
                                    $fileIndex = isset($file['index']) ? (int)$file['index'] : 0;
                                    $downloadUrl = base_url('laboratory/testresult/download/' . $testResult['id']) . '?file=' . $fileIndex;
                                ?>
                                <a href="<?= esc($downloadUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:0.35rem;justify-content:flex-start;">
                                    <i class="fas fa-file-download"></i> <?= esc($file['label'] ?? 'Result File') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php elseif (!empty($testResult['result_file_url'])): ?>
                    <div class="info-value" style="text-align: center;">
                        Test results are available in the uploaded file.<br>
                        <a href="<?= esc($testResult['result_file_url']) ?>" class="btn btn-link" target="_blank" rel="noopener">
                            <i class="fas fa-file-download"></i> <?= esc($testResult['result_file_label'] ?? 'Download Result File') ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="info-value" style="text-align: center; color: #6c757d; font-style: italic;">
                        No test results available yet. Results will appear here once the test is completed.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer no-print" style="display:flex;gap:10px;align-items:center;">
                <?php if (strtolower($testResult['status']) !== 'completed'): ?>
                <a href="<?= base_url('laboratory/testresult/add/' . $testResult['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Test Results
                </a>
                <?php endif; ?>
                <?php if (strtolower($testResult['status']) === 'completed'): ?>
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <style>
        .card--detail {
            width: 100%;
            max-width: 920px;
            margin: 1.25rem auto;
        }

        .card--detail:first-of-type {
            margin-top: 0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Any page-specific JavaScript can go here
        });
    </script>
<?= $this->endSection() ?>
