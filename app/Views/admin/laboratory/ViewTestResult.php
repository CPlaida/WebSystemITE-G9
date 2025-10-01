<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Result Details | HMS</title>
    <link rel="stylesheet" href="<?= base_url('css/font-awesome/css/all.min.css') ?>">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #3f37c9;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-color: #dee2e6;
            --text-color: #333;
            --white: #ffffff;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #dc3545;
            --border-radius: 6px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --transition: all 0.2s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8f9fc;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin: 0;
        }
        
        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            background-color: var(--light-gray);
            border-top: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4em 0.8em;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50px;
        }
        
        .badge-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: var(--dark-gray);
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem 1.5rem;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }
        
        .info-group {
            margin-bottom: 1.5rem;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .info-value {
            color: var(--text-color);
            font-size: 1rem;
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .data-table th {
            background-color: var(--light-gray);
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.25rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: var(--transition);
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-1px);
        }
        
        .no-print {
            display: block;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .card-header,
            .card-footer {
                padding: 1rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background-color: white !important;
                padding: 0;
            }
            
            .container {
                padding: 0;
                max-width: 100%;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid var(--border-color);
            }
            
            .card-header {
                background-color: transparent !important;
                border-bottom: 1px solid var(--border-color);
            }
            
            .btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Test Result Details</h1>
            <a href="<?= base_url('laboratory/testresult') ?>" class="btn btn-secondary no-print">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lab Request Information</h2>
                <span class="badge <?= $testResult['status_class'] ?>">
                    <?= ucfirst($testResult['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Request ID</div>
                            <div class="info-value"><?= htmlspecialchars($testResult['test_id'] ?? 'N/A') ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Patient Name</div>
                            <div class="info-value"><?= htmlspecialchars($testResult['patient_name']) ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Test Type</div>
                            <div class="info-value"><?= htmlspecialchars($testResult['test_type']) ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Priority</div>
                            <div class="info-value">
                                <span class="badge <?= strtolower($testResult['priority_display']) === 'urgent' ? 'badge-warning' : 'badge-success' ?>">
                                    <?= $testResult['priority_display'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Test Date</div>
                            <div class="info-value"><?= $testResult['formatted_test_date'] ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Test Time</div>
                            <div class="info-value"><?= $testResult['formatted_test_time'] ?></div>
                        </div>

                        <?php if (!empty($testResult['result_date'])): ?>
                        <div class="info-group">
                            <div class="info-label">Result Date</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($testResult['result_date'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($testResult['technician_name'])): ?>
                        <div class="info-group">
                            <div class="info-label">Technician</div>
                            <div class="info-value"><?= htmlspecialchars($testResult['technician_name']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($testResult['notes'])): ?>
                <div class="info-group">
                    <div class="section-title">Clinical Notes</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($testResult['notes'])) ?></div>
                </div>
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

                <?php if (!empty($testResult['interpretation'])): ?>
                <div class="info-group">
                    <div class="section-title">Clinical Interpretation</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($testResult['interpretation'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer no-print">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Result
                </button>
            </div>
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
            <div class="card-footer no-print">
                <a href="<?= base_url('laboratory/testresult/add/' . $testResult['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Test Results
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Any page-specific JavaScript can go here
        });
    </script>
</body>
</html>
