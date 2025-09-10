<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Add Test Result<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
        
        .card {
            background: var(--white);
            border-radius: 0.35rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .card-header {
            padding: 1rem 1.25rem;
            background-color: #f8f9fc;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: #4e73df;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5a5c69;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #6e707e;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #6e707e;
            background-color: #fff;
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.35rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-secondary {
            color: #fff;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        
        .text-right {
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Add Test Result</h1>
        </div>

        <div class="card">
            <div class="card-header">
                Test Information
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

    <script>
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
