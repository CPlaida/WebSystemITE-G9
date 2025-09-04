<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results | HMS</title>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 6px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --transition: all 0.2s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fb;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: var(--white);
            min-height: 100vh;
        }
        
        .header {
            background: var(--primary-color);
            color: var(--white);
            padding: 1.5rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--white);
        }
        
        .content-container {
            padding: 0 2rem 2rem;
        }
        
        .search-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-container {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .search-container {
                grid-template-columns: 1fr 200px;
            }
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .search-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 0 1.5rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .search-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
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
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid transparent;
            cursor: pointer;
        }
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
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
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .back-btn:hover {
            background: var(--light-gray);
            transform: translateX(-2px);
        }
        
        @media (max-width: 768px) {
            .content-container {
                padding: 0 1rem 1.5rem;
            }
            
            .header {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .search-container {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                overflow-x: auto;
                padding-bottom: 0.5rem;
                flex-wrap: nowrap;
            }
            
            .filter-section::-webkit-scrollbar {
                height: 4px;
            }
            
            .filter-section::-webkit-scrollbar-thumb {
                background: var(--border-color);
                border-radius: 2px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">Test Results</h1>
        </div>
        
        <div class="content-container">
            <div class="search-section">
                <h3 class="section-title">
                    <i class="fas fa-search"></i> Search & Filter
                </h3>
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by patient name, test type, or ID...">
                    <button class="search-btn" id="searchBtn">
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
                            <td>Juan Dela Cruz</td>
                            <td>CBC</td>
                            <td>2023-09-04</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td>Normal</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= base_url('laboratory/testresult/view/1') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?= base_url('laboratory/testresult/add/2') ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus"></i> Add Result
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr data-status="pending" data-date="2023-09-04">
                            <td>Maria Santos</td>
                            <td>Urinalysis</td>
                            <td>2023-09-04</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>—</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= base_url('laboratory/testresult/view/2') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?= base_url('laboratory/testresult/add/2') ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus"></i> Add Result
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr data-status="completed" data-date="2023-09-03">
                            <td>John Doe</td>
                            <td>Blood Chemistry</td>
                            <td>2023-09-03</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td>Abnormal</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= base_url('laboratory/testresult/view/3') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?= base_url('laboratory/testresult/add/2') ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus"></i> Add Result
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="footer">
                <a href="<?= base_url('dashboard') ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter buttons functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            const searchBtn = document.getElementById('searchBtn');
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
            // Handle filter button clicks
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    filterTable(filterValue);
                });
            });
            
            // Handle search button click
            searchBtn.addEventListener('click', performSearch);
            
            // Handle Enter key in search input
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            // Make table rows clickable
            tableRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't navigate if clicking on buttons or links
                    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
                        return;
                    }
                    const viewLink = this.querySelector('a.btn-outline-primary');
                    if (viewLink) {
                        window.location.href = viewLink.href;
                    }
                });
            });
            
            function performSearch() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                filterTable('search', searchTerm);
            }
            
            function filterTable(filterType, searchTerm = '') {
                const today = new Date().toISOString().split('T')[0];
                const currentDate = new Date();
                const firstDayOfWeek = new Date(currentDate);
                firstDayOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
                const lastDayOfWeek = new Date(firstDayOfWeek);
                lastDayOfWeek.setDate(firstDayOfWeek.getDate() + 6);
                
                tableRows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    const date = row.getAttribute('data-date');
                    let showRow = true;
                    
                    if (filterType === 'search' && searchTerm) {
                        const rowText = row.textContent.toLowerCase();
                        showRow = rowText.includes(searchTerm);
                    } else if (filterType === 'pending') {
                        showRow = status === 'pending';
                    } else if (filterType === 'completed') {
                        showRow = status === 'completed';
                    } else if (filterType === 'today') {
                        showRow = date === today;
                    } else if (filterType === 'week') {
                        const rowDate = new Date(date);
                        showRow = rowDate >= firstDayOfWeek && rowDate <= lastDayOfWeek;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }
        });
    </script>
</body>
</html>
