<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h2 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
        }

        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .box {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .box p {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #e0e0e0;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .status-pending {
            background-color: #f1c40f;
            color: #000;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-paid {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-overdue {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-button:hover {
            color: #2980b9;
        }

        .back-button svg {
            margin-right: 8px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-icon {
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .btn-view {
            background-color: #3498db;
        }

        .btn-edit {
            background-color: #f39c12;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .btn-icon:hover {
            opacity: 0.9;
        }

        /* Search Bar Styles */
        .search-container {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .search-button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }

        .search-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <main>
        <a href="<?= base_url('dashboard') ?>" class="back-button">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Dashboard
        </a>
        
        <div class="header">
            <h2>Billing Management</h2>
            <button id="add-bill">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add New Bill
            </button>
        </div>

        <div class="summary">
            <div class="box">
                <h3>Total Bills</h3>
                <p id="total-bills">0</p>
            </div>
            <div class="box">
                <h3>Total Amount</h3>
                <p id="total-amount">₱0.00</p>
            </div>
            <div class="box">
                <h3>Pending Bills</h3>
                <p id="pending-bills">0</p>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by patient name, bill number, or status...">
            <button class="search-button" id="searchButton">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                Search
            </button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Patient Name</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="billing-table-body">
                <!-- Sample Data -->
                <tr>
                    <td>B1001</td>
                    <td>Juan Dela Cruz</td>
                    <td>General Checkup</td>
                    <td>₱500.00</td>
                    <td>2023-08-26</td>
                    <td><span class="status-pending">Pending</span></td>
                    <td class="actions">
                        <button class="btn-icon btn-view">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn-icon btn-edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-icon btn-delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>B1002</td>
                    <td>Maria Santos</td>
                    <td>Dental Checkup</td>
                    <td>₱1,200.00</td>
                    <td>2023-08-25</td>
                    <td><span class="status-paid">Paid</span></td>
                    <td class="actions">
                        <button class="btn-icon btn-view">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn-icon btn-edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-icon btn-delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </main>

    <script>
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', filterBills);
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                filterBills();
            }
        });

        function filterBills() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#billing-table-body tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const billNumber = row.cells[0].textContent.toLowerCase();
                const patientName = row.cells[1].textContent.toLowerCase();
                const status = row.cells[5].querySelector('span').textContent.toLowerCase();
                
                if (billNumber.includes(searchTerm) || 
                    patientName.includes(searchTerm) || 
                    status.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update summary with visible rows
            updateSummary();
        }

        document.getElementById('add-bill').addEventListener('click', function() {
            const billDetails = {
                billNumber: 'B' + Math.floor(1000 + Math.random() * 9000),
                patientName: prompt("Enter Patient Name:"),
                service: prompt("Enter Service Provided:"),
                amount: prompt("Enter Amount:"),
                date: new Date().toLocaleDateString('en-CA'),
                status: "Pending"
            };

            if (!billDetails.patientName || !billDetails.service || !billDetails.amount) {
                alert("Please fill out all details.");
                return;
            }

            const tableBody = document.getElementById('billing-table-body');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${billDetails.billNumber}</td>
                <td>${billDetails.patientName}</td>
                <td>${billDetails.service}</td>
                <td>₱${parseFloat(billDetails.amount).toFixed(2)}</td>
                <td>${billDetails.date}</td>
                <td><span class="status-pending">${billDetails.status}</span></td>
                <td class="actions">
                    <button class="btn-icon btn-view" onclick="viewReceipt('${billDetails.billNumber}')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </button>
                    <button class="btn-icon btn-edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteBill(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            `;
            
            tableBody.prepend(newRow);
        });

        // Add delete functionality
        function deleteBill(button) {
            if (confirm('Are you sure you want to delete this bill?')) {
                const row = button.closest('tr');
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        }

        // Add event delegation for action buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                deleteBill(e.target.closest('.btn-delete'));
            } else if (e.target.closest('.btn-edit')) {
                editBill(e.target.closest('.btn-edit'));
            } else if (e.target.closest('.btn-view')) {
                const billNumber = e.target.closest('tr').cells[0].textContent;
                viewReceipt(billNumber);
            }
        });
        
        // Initialize summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
        });

        // View receipt function
        function viewReceipt(billNumber) {
            // Open receipt in a new tab with correct base URL
            window.open(`<?= base_url('billing/receipt') ?>/${billNumber}`, '_blank');
        }

        // Edit bill function
        function editBill(button) {
            const row = button.closest('tr');
            const cells = row.cells;
            const billNumber = cells[0].textContent;
            
            // Get current values
            const currentData = {
                patientName: cells[1].textContent,
                service: cells[2].textContent,
                amount: cells[3].textContent.replace('₱', ''),
                date: cells[4].textContent,
                status: cells[5].querySelector('span').textContent
            };

            // Create form for editing
            const form = document.createElement('form');
            form.innerHTML = `
                <div style="margin-bottom: 10px;">
                    <label>Patient Name:</label>
                    <input type="text" class="form-control" name="patientName" value="${currentData.patientName}" required>
                </div>
                <div style="margin-bottom: 10px;">
                    <label>Service:</label>
                    <input type="text" class="form-control" name="service" value="${currentData.service}" required>
                </div>
                <div style="margin-bottom: 10px;">
                    <label>Amount:</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="amount" value="${currentData.amount}" step="0.01" required>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Status:</label>
                    <select class="form-control" name="status" required>
                        <option value="Pending" ${currentData.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Paid" ${currentData.status === 'Paid' ? 'selected' : ''}>Paid</option>
                        <option value="Overdue" ${currentData.status === 'Overdue' ? 'selected' : ''}>Overdue</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="Swal.close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            `;

            // Show edit form in SweetAlert
            Swal.fire({
                title: `Edit Bill #${billNumber}`,
                html: form,
                showCancelButton: false,
                showConfirmButton: false,
                width: '500px'
            });

            // Handle form submission
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                
                try {
                    // In a real application, you would make an AJAX call to update the bill
                    // Example:
                    // const response = await fetch(`/billing/update/${billNumber}`, {
                    //     method: 'POST',
                    //     body: JSON.stringify(Object.fromEntries(formData)),
                    //     headers: {
                    //         'Content-Type': 'application/json'
                    //     }
                    // });
                    // const result = await response.json();
                    
                    // For demo purposes, we'll just update the UI
                    cells[1].textContent = formData.get('patientName');
                    cells[2].textContent = formData.get('service');
                    cells[3].textContent = '₱' + parseFloat(formData.get('amount')).toFixed(2);
                    
                    const statusSpan = cells[5].querySelector('span');
                    statusSpan.textContent = formData.get('status');
                    statusSpan.className = `status-${formData.get('status').toLowerCase()}`;
                    
                    Swal.fire('Success!', 'Bill updated successfully.', 'success');
                } catch (error) {
                    console.error('Error updating bill:', error);
                    Swal.fire('Error', 'Failed to update bill. Please try again.', 'error');
                }
            };
        }

        // Delete bill function
        async function deleteBill(button) {
            const row = button.closest('tr');
            const billNumber = row.cells[0].textContent;
            
            const result = await Swal.fire({
                title: 'Delete Bill',
                text: `Are you sure you want to delete Bill #${billNumber}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });
            
            if (result.isConfirmed) {
                try {
                    // In a real application, you would make an AJAX call to delete the bill
                    // Example:
                    // const response = await fetch(`/billing/delete/${billNumber}`, {
                    //     method: 'POST'
                    // });
                    // const result = await response.json();
                    
                    // For demo purposes, we'll just remove the row from the UI
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updateSummary();
                        Swal.fire('Deleted!', 'The bill has been deleted.', 'success');
                    }, 300);
                } catch (error) {
                    console.error('Error deleting bill:', error);
                    Swal.fire('Error', 'Failed to delete bill. Please try again.', 'error');
                }
            }
        }

        // Update summary cards
        function updateSummary() {
            const rows = document.querySelectorAll('#billing-table-body tr');
            const totalBills = rows.length;
            let totalAmount = 0;
            let pendingBills = 0;
            
            rows.forEach(row => {
                const amount = parseFloat(row.cells[3].textContent.replace('₱', '').replace(',', ''));
                totalAmount += amount;
                
                if (row.cells[5].querySelector('.status-pending')) {
                    pendingBills++;
                }
            });
            
            document.getElementById('total-bills').textContent = totalBills;
            document.getElementById('total-amount').textContent = '₱' + totalAmount.toFixed(2);
            document.getElementById('pending-bills').textContent = pendingBills;
        }
    </script>
</body>
</html>
