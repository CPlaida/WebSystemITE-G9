<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory - St. Peter Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f6f9;
            color: #333;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 18px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        .page-title {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .btn-back {
            padding: 10px 18px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.15);
        }
        .card h3 {
            margin-top: 0;
            font-size: 1rem;
            color: #7f8c8d;
        }
        .card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }
        .action-buttons {
            margin: 20px 0;
        }
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .form-popup {
            margin: 20px 0;
            padding: 25px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        .form-popup h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #34495e;
            margin-bottom: 5px;
            display: inline-block;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            margin-top: 3px;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 4px rgba(52,152,219,0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f1f3f5;
            font-weight: 600;
            color: #2c3e50;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        tr:hover {
            background-color: #f1f3f5;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="header-left">
                <h1 class="page-title">Medicine Inventory</h1>
            </div>
            <a href="<?= base_url('/dashboard') ?>" class="btn-back" style="
                display: inline-flex;
                align-items: center;
                padding: 0.6rem 1.2rem;
                background: rgba(255, 255, 255, 0.9);
                color: #333;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s ease;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            "
            onmouseover="this.style.backgroundColor='rgba(248, 249, 250, 0.95)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';"
            onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.9)'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
            >
                <i class="fas fa-arrow-left" style="margin-right: 8px; font-size: 0.9rem;"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="content">
            <div class="card-container">
                <div class="card">
                    <h3>Total Items</h3>
                    <div class="value" id="totalItems">0</div>
                </div>
                <div class="card">
                    <h3>Low Stock Items</h3>
                    <div class="value" id="lowStock">0</div>
                </div>
                <div class="card">
                    <h3>Expiring Soon</h3>
                    <div class="value" id="expiringSoon">0</div>
                </div>
                <div class="card">
                    <h3>Inventory Value</h3>
                    <div class="value">₱<span id="inventoryValue">0</span></div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="toggleForm()">
                    <i class="fas fa-plus"></i> Add New Item
                </button>
            </div>

            <div class="search-container" style="margin: 15px 0;">
                <input type="text" id="searchInput" placeholder="Search medicines..." style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; width: 300px; margin-right: 10px;">
                <button onclick="searchMedicines()" style="padding: 8px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>

            <div id="form" class="form-popup" style="display: none;">
                <h3>Add New Medicine</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 15px;">
                    <div>
                        <label>Medicine Name</label>
                        <input type="text" id="medicine" class="form-control" placeholder="Enter medicine name" required>
                    </div>
                    <div>
                        <label>Quantity</label>
                        <input type="number" id="quantity" class="form-control" placeholder="Enter quantity" required>
                    </div>
                    <div>
                        <label>Category</label>
                        <input type="text" id="category" class="form-control" placeholder="Enter category" required>
                    </div>
                    <div>
                        <label>Unit Price (₱)</label>
                        <input type="number" step="0.01" id="price" class="form-control" placeholder="Enter price" required>
                    </div>
                    <div>
                        <label>Status</label>
                        <select id="status" class="form-control" required>
                            <option value="">Select status</option>
                            <option value="In Stock">In Stock</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>
                    </div>
                    <div>
                        <label>Expiry Date</label>
                        <input type="date" id="expiry" class="form-control" required>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <button class="btn btn-primary" onclick="addItem()">
                        <i class="fas fa-save"></i> Save Item
                    </button>
                    <button class="btn btn-secondary" onclick="toggleForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Stock Quantity</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTable">
                        <!-- Items will be dynamically added here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            let form = document.getElementById("form");
            form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
        }

        function addItem() {
            let medicine = document.getElementById("medicine").value;
            let quantity = parseInt(document.getElementById("quantity").value);
            let category = document.getElementById("category").value;
            let price = parseFloat(document.getElementById("price").value);
            let status = document.getElementById("status").value;
            let expiry = document.getElementById("expiry").value;

            if (!medicine || !quantity || !category || !price || !status || !expiry) {
                alert("Please fill all fields");
                return;
            }

            let table = document.getElementById("inventoryTable");
            let row = table.insertRow();

            row.insertCell(0).innerText = medicine;
            row.insertCell(1).innerText = quantity;
            row.insertCell(2).innerText = category;
            row.insertCell(3).innerText = "₱" + price.toFixed(2);
            row.insertCell(4).innerText = status;
            row.insertCell(5).innerText = expiry;

            updateStats();

            document.getElementById("medicine").value = "";
            document.getElementById("quantity").value = "";
            document.getElementById("category").value = "";
            document.getElementById("price").value = "";
            document.getElementById("status").value = "";
            document.getElementById("expiry").value = "";
            document.getElementById("form").style.display = "none";
        }

        function updateStats() {
            let table = document.getElementById("inventoryTable");
            let rows = table.rows;
            let totalItems = rows.length;
            let lowStock = 0;
            let expiringSoon = 0;
            let inventoryValue = 0;

            let today = new Date();
            let soon = new Date();
            soon.setDate(today.getDate() + 30);

            for (let i = 0; i < rows.length; i++) {
                let quantity = parseInt(rows[i].cells[1].innerText);
                let price = parseFloat(rows[i].cells[3].innerText.replace("₱",""));
                let expiryDate = new Date(rows[i].cells[5].innerText);

                inventoryValue += quantity * price;
                if (quantity < 10) lowStock++;
                if (expiryDate <= soon) expiringSoon++;
            }

            document.getElementById("totalItems").innerText = totalItems;
            document.getElementById("lowStock").innerText = lowStock;
            document.getElementById("expiringSoon").innerText = expiringSoon;
            document.getElementById("inventoryValue").innerText = inventoryValue.toFixed(2);
        }

        function searchMedicines() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#inventoryTable tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add event listener for Enter key
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                searchMedicines();
            }
        });
    </script>
</body>
</html>
