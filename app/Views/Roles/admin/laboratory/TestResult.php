<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Test Results<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid py-4">
        <div class="composite-card billing-card" style="margin-top:0;">
            <div class="composite-header">
                <h1 class="composite-title">Test Results</h1>
            </div>
            <div class="card-body">
        <div class="test-result-search-wrapper">
            <div class="test-result-search-row">
                <i class="fas fa-search test-result-search-icon"></i>
                <input type="text" class="test-result-search-field" id="searchInput" placeholder="Search patients...">
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="filter-section">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="completed">Completed</button>
                    <button class="filter-btn" data-filter="cancelled">Cancelled</button>
                    <button class="filter-btn" data-filter="today">Today</button>
                    <button class="filter-btn" data-filter="week">This Week</button>
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
                                    <th style="width: 200px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="testResultsTableBody">
                                <?php if (!empty($initialResults ?? [])): ?>
                                    <?php foreach (($initialResults ?? []) as $item): ?>
                                        <?php
                                            $identifier = $item['id'] ?? $item['test_id'] ?? '';
                                            $viewUrl = $identifier ? base_url('laboratory/testresult/view/' . $identifier) : base_url('laboratory/testresult/view');
                                            $addUrl = $identifier ? base_url('laboratory/testresult/add/' . $identifier) : base_url('laboratory/testresult/add');
                                            $status = $item['status'] ?? 'pending';
                                        ?>
                                        <?php 
                                        $statusLower = strtolower((string)$status);
                                        $statusClass = 'status-pending';
                                        if ($statusLower === 'completed') {
                                            $statusClass = 'status-completed';
                                        } elseif ($statusLower === 'cancelled') {
                                            $statusClass = 'status-cancelled';
                                        } elseif ($statusLower === 'in_progress') {
                                            $statusClass = 'status-in-progress';
                                        }
                                        $canAddResult = $statusLower !== 'completed' && $statusLower !== 'cancelled';
                                        ?>
                                        <tr data-status="<?= esc($status) ?>" data-date="<?= esc($item['test_date'] ?? '') ?>">
                                            <td><?= esc($item['test_id'] ?? $item['id'] ?? 'N/A') ?></td>
                                            <td><?= esc($item['patient_name'] ?? $item['test_name'] ?? 'N/A') ?></td>
                                            <td><?= esc($item['test_type'] ?? 'N/A') ?></td>
                                            <td><?= esc($item['test_date'] ?? 'N/A') ?></td>
                                            <td><span class="status-badge <?= $statusClass ?>"><?= esc($status) ?></span></td>
                                            <td style="text-align: center; padding: 0.5rem;">
                                                <div class="action-buttons" style="justify-content: center; gap: 4px;">
                                                    <a href="<?= $viewUrl ?>" class="btn btn-primary btn-sm" role="button">View</a>
                                                    <?php if ($canAddResult && in_array(session('role'), ['admin', 'labstaff'])): ?>
                                                        <a href="<?= $addUrl ?>" class="btn btn-primary btn-sm" role="button">Add Result</a>
                                                    <?php endif; ?>
                                                    <?php if ($canAddResult && in_array(session('role'), ['admin', 'labstaff'])): ?>
                                                        <form method="POST" action="<?= base_url('laboratory/testresult/cancel/' . ($item['id'] ?? $item['test_id'] ?? '')) ?>" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this laboratory request? This action cannot be undone.');">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-danger btn-sm" role="button" style="white-space: nowrap;">
                                                                <i class="fas fa-times-circle"></i> Cancel
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allTestResults = <?= isset($initialResults) ? json_encode($initialResults) : '[]' ?>; // Seed with preloaded data
        
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
                        if (activeFilter === 'pending' || activeFilter === 'completed' || activeFilter === 'cancelled') {
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
                            item.test_type || ''
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
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">
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
                    
                    const identifier = item.id || item.test_id || '';
                    const viewUrl = identifier
                        ? '<?= base_url('laboratory/testresult/view/') ?>' + identifier
                        : '<?= base_url('laboratory/testresult/view') ?>';
                    const addUrl = identifier
                        ? '<?= base_url('laboratory/testresult/add/') ?>' + identifier
                        : '<?= base_url('laboratory/testresult/add') ?>';
                    
                    const status = String(item.status || '').toLowerCase();
                    const isCompleted = status === 'completed';
                    const isCancelled = status === 'cancelled';
                    const canAddResult = !isCompleted && !isCancelled;
                    
                    // Determine status badge class
                    let statusClass = 'status-pending';
                    if (status === 'completed') {
                        statusClass = 'status-completed';
                    } else if (status === 'cancelled') {
                        statusClass = 'status-cancelled';
                    } else if (status === 'in_progress') {
                        statusClass = 'status-in-progress';
                    }
                    
                    const cancelForm = canAddResult ? `
                        <form method="POST" action="<?= base_url('laboratory/testresult/cancel/') ?>${identifier}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this laboratory request? This action cannot be undone.');">
                            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                            <button type="submit" class="btn btn-danger btn-sm" role="button" style="margin-left: 4px;">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        </form>
                    ` : '';
                    
                    row.innerHTML = `
                        <td>${item.test_id || 'N/A'}</td>
                        <td>${item.patient_name || 'N/A'}</td>
                        <td>${item.test_type || 'N/A'}</td>
                        <td>${item.test_date || 'N/A'}</td>
                        <td><span class="status-badge ${statusClass}">${item.status || 'pending'}</span></td>
                        <td style="text-align: center; padding: 0.5rem;">
                            <div class="action-buttons" style="justify-content: center; gap: 4px;">
                                <a href="${viewUrl}" class="btn btn-primary btn-sm" role="button">View</a>
                                ${canAddResult ? `<a href="${addUrl}" class="btn btn-primary btn-sm" role="button">Add Result</a>` : ''}
                                ${cancelForm}
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
            
            // If initial results exist, render immediately; else show loading
            const tableBody = document.getElementById('testResultsTableBody');
            if (Array.isArray(allTestResults) && allTestResults.length > 0) {
                renderTable(allTestResults);
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
                            Loading test results...
                        </td>
                    </tr>
                `;
            }
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Store data globally for filtering
                    allTestResults = Array.isArray(data) ? data : [];
                    
                    if (allTestResults.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">
                                    <i class="fas fa-flask" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    No test results found. Create your first lab request to get started.
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    // Refresh table with latest data
                    renderTable(allTestResults);
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #dc3545;">
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
