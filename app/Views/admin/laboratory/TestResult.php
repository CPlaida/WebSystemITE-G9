<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Test Results<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-hover: #2e59d9;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --border-color: #e3e6f0;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            --transition: all 0.3s ease-in-out;
        }
        
        body {
            background-color: #f8f9fc;
            color: #5a5c69;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
        }

        .main-content {
            padding: 1.5rem;
            margin-left: 6.5rem;
            transition: var(--transition);
            min-height: 100vh;
            padding-bottom: 2rem;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .page-header {
            background: #fff;
            border-radius: 0.35rem;
            box-shadow: var(--card-shadow);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 4px solid var(--primary-color);
        }

        .page-title {
            color: var(--dark);
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            line-height: 1.2;
        }

        .card {
            background: #fff;
            border: none;
            border-radius: 0.35rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .card-title {
            color: var(--dark);
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .search-section {
            background: #fff;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.25rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.575rem 0.75rem;
            font-size: 0.9rem;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            border-radius: 0.35rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: #fff;
        }

        .btn-outline-success {
            color: var(--success-color);
            border-color: var(--success-color);
            background: transparent;
        }

        .btn-outline-success:hover {
            background: var(--success-color);
            color: #fff;
        }

        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--secondary-color);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 900px;
            background: #fff;
        }
        
        .data-table thead th {
            background: var(--primary-color);
            color: #fff;
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        
        .data-table thead th:first-child {
            border-top-left-radius: 0.35rem;
        }
        
        .data-table thead th:last-child {
            border-top-right-radius: 0.35rem;
        }
        
        .data-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .data-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 0.35rem;
        }
        
        .data-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 0.35rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            transition: var(--transition);
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-top: 4rem;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .search-container {
                flex-direction: column;
            }
            
            .filter-section {
                overflow-x: auto;
                padding-bottom: 0.5rem;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
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
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Test ID</th>
                                    <th>Patient Name</th>
                                    <th>Test Type</th>
                                    <th>Test Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="testResultsTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allTestResults = []; // Store all loaded data for filtering
        
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
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchBtn');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    const filter = button.getAttribute('data-filter');
                    applyFilters();
                });
            });

            function applyFilters() {
                const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                let filteredData = [...allTestResults];
                
                // Apply status/date filters
                if (activeFilter !== 'all') {
                    const today = new Date();
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    
                    filteredData = filteredData.filter(item => {
                        if (activeFilter === 'pending' || activeFilter === 'completed') {
                            return (item.status || 'pending') === activeFilter;
                        } else if (activeFilter === 'today') {
                            const itemDate = new Date(item.test_date);
                            return itemDate.toDateString() === today.toDateString();
                        } else if (activeFilter === 'week') {
                            const itemDate = new Date(item.test_date);
                            return itemDate >= startOfWeek;
                        }
                        return true;
                    });
                }
                
                // Apply search filter
                if (searchTerm) {
                    filteredData = filteredData.filter(item => {
                        const searchableText = [
                            item.test_id || '',
                            item.patient_name || '',
                            item.test_type || '',
                            item.notes || ''
                        ].join(' ').toLowerCase();
                        
                        return searchableText.includes(searchTerm);
                    });
                }
                
                renderTable(filteredData);
            }

            function renderTable(data) {
                const tableBody = document.getElementById('testResultsTableBody');
                
                if (!data || data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #666;">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                No test results found matching your criteria.
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                tableBody.innerHTML = '';
                
                data.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-status', item.status || 'pending');
                    row.setAttribute('data-date', item.test_date || '');
                    
                    const viewUrl = '<?= base_url('laboratory/testresult/view/') ?>' + (item.id || '');
                    const addUrl = '<?= base_url('laboratory/testresult/add/') ?>' + (item.id || '');
                    
                    row.innerHTML = `
                        <td>${item.test_id || 'N/A'}</td>
                        <td>${item.patient_name || 'N/A'}</td>
                        <td>${item.test_type || 'N/A'}</td>
                        <td>${item.test_date || 'N/A'}</td>
                        <td><span class="status-badge ${item.status === 'pending' ? 'status-pending' : 'status-completed'}">${item.status || 'pending'}</span></td>
                        <td>${item.notes || '—'}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="${viewUrl}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="${addUrl}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-plus"></i> Add Result
                                </a>
                            </div>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
            }

            // Search functionality
            function performSearch() {
                applyFilters();
            }

            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    performSearch();
                } else {
                    // Real-time search as user types (with debounce)
                    clearTimeout(searchInput.searchTimeout);
                    searchInput.searchTimeout = setTimeout(performSearch, 300);
                }
            });

            // Load data from laboratory table
            const url = '<?= base_url('laboratory/testresult/data') ?>';
            
            console.log('Fetching data from:', url);
            
            // Show loading state
            const tableBody = document.getElementById('testResultsTableBody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
                        Loading test results...
                    </td>
                </tr>
            `;
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Store data globally for filtering
                    allTestResults = Array.isArray(data) ? data : [];
                    
                    if (allTestResults.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #666;">
                                    <i class="fas fa-flask" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    No test results found. Create your first lab request to get started.
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    // Render initial data
                    renderTable(allTestResults);
                    
                    console.log('Table populated with', allTestResults.length, 'rows');
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #dc3545;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                                Error loading test results: ${error.message}
                                <br><small>Please refresh the page or contact support if the problem persists.</small>
                            </td>
                        </tr>
                    `;
                });
        });
    </script>
<?= $this->endSection() ?>
