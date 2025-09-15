<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Test Results<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 6px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --transition: all 0.2s ease;
        }
        
        .main-content {
            padding: 20px;
            width: 100%;
            margin-left: 120px;
            transition: all 0.3s;
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .page-header {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: #4e73df;
        }

        .card-body {
            padding: 20px;
        }

        .search-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.25rem;
        }

        .search-container {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #6e707e;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            border-radius: 0.35rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn i {
            margin-right: 5px;
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

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-outline-success {
            color: var(--success-color);
            border-color: var(--success-color);
            background: transparent;
        }

        .btn-outline-success:hover {
            background: var(--success-color);
            color: var(--white);
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .data-table thead th {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }
        
        .data-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            cursor: pointer;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .search-container {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                overflow-x: auto;
                padding-bottom: 0.5rem;
                flex-wrap: nowrap;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Test Results</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="search-section">
                    <h3 class="section-title">
                        <i class="fas fa-search"></i> Search & Filter
                    </h3>
                    <div class="search-container">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by patient name, test type, or ID...">
                        <button class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    
                    <div class="filter-section">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="pending">Pending</button>
                        <button class="filter-btn" data-filter="completed">Completed</button>
                        <button class="filter-btn" data-filter="today">Today</button>
                        <button class="filter-btn" data-filter="week">This Week</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Patient Name</th>
                                <th>Test Type</th>
                                <th>Test Date</th>
                                <th>Status</th>
                                <th>Result</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-status="completed" data-date="2023-09-04">
                                <td>#P-1001</td>
                                <td>Juan Dela Cruz</td>
                                <td>CBC</td>
                                <td>2023-09-04</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>Normal</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= base_url('laboratory/testresult/view/1') ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?= base_url('laboratory/testresult/add/1') ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-plus"></i> Add Result
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr data-status="pending" data-date="2023-09-04">
                                <td>#P-1002</td>
                                <td>Maria Santos</td>
                                <td>Urinalysis</td>
                                <td>2023-09-04</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>—</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= base_url('laboratory/testresult/view/2') ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?= base_url('laboratory/testresult/add/2') ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-plus"></i> Add Result
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
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

            // Filter buttons functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchBtn');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    const filter = button.getAttribute('data-filter');
                    filterTable(filter);
                });
            });

            function filterTable(filter) {
                const today = new Date();
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                
                tableRows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    const dateString = row.getAttribute('data-date');
                    const rowDate = new Date(dateString);
                    
                    let showRow = true;
                    
                    if (filter === 'all') {
                        showRow = true;
                    } else if (filter === 'pending' || filter === 'completed') {
                        showRow = status === filter;
                    } else if (filter === 'today') {
                        showRow = rowDate.toDateString() === today.toDateString();
                    } else if (filter === 'week') {
                        showRow = rowDate >= startOfWeek;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }

            // Search functionality
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
                
                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    const isVisible = rowText.includes(searchTerm) && row.style.display !== 'none';
                    row.style.display = isVisible ? '' : 'none';
                });
            }

            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        });
    </script>
<?= $this->endSection() ?>
