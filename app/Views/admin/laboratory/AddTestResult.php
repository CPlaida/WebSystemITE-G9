<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test Result | HMS</title>
    <link rel="stylesheet" href="<?= base_url('css/font-awesome/css/all.min.css') ?>">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
            --text-color: #333;
            --white: #ffffff;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 1.75rem;
            color: var(--text-color);
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            opacity: 0.9;
        }
        
        .card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 20px;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 25px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .text-right {
            text-align: right;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Add Test Result</h1>
            <a href="<?= base_url('laboratory/testresult') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Test Information</h2>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('laboratory/testresult/add/' . $testId) ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Patient Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($testDetails['patient_name']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Test Type</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($testDetails['test_type']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Test Date</label>
                            <input type="text" class="form-control" value="<?= date('F j, Y', strtotime($testDetails['test_date'])) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?= $testDetails['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="test-results">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Test Results</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Result</th>
                                        <th>Reference Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Parameter 1</td>
                                        <td>
                                            <input type="text" class="form-control" name="parameter1" style="width: 100%;">
                                        </td>
                                        <td>Range 1</td>
                                    </tr>
                                    <tr>
                                        <td>Parameter 2</td>
                                        <td>
                                            <input type="text" class="form-control" name="parameter2" style="width: 100%;">
                                        </td>
                                        <td>Range 2</td>
                                    </tr>
                                    <!-- Add more parameters as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Result
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
