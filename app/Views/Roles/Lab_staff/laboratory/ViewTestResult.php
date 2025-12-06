<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Test Result Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid py-4">
        <div class="composite-card billing-card" style="margin-top:0;">
            <div class="composite-header">
                <h1 class="composite-title">Test Result Details</h1>
            </div>
            <div class="card-body">
        <div class="lab-receipt" style="box-shadow: none; border: none; margin: 0;">
            <div class="page-header" style="display:flex;align-items:center;justify-content:flex-end;gap:10px; background: transparent; border: none; padding: 0 0 1rem 0;">
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
  <div class="section-title">Clinical Notes</div>
  <div class="v"><?= nl2br(esc($testResult['notes'])) ?></div>
  <?php endif; ?>
    </div>
        </div>

        <?php if (!empty($testResult['results']) && is_array($testResult['results'])): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Test Results</h2>
            </div>
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
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Test Results</h2>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <div class="info-value" style="text-align: center; color: #6c757d; font-style: italic;">
                        No test results available yet. Results will appear here once the test is completed.
                    </div>
                </div>
            </div>
            <div class="card-footer no-print" style="display:flex;gap:10px;align-items:center;">
                <a href="<?= base_url('laboratory/testresult/add/' . $testResult['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Test Results
                </a>
                <?php if (strtolower($testResult['status']) === 'completed'): ?>
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Any page-specific JavaScript can go here
        });
    </script>
<?= $this->endSection() ?>
