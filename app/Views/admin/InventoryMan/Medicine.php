<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 20px;
    }

    .container {
      background: #fff;
      padding: 20px;
      border: 1px solid #ccc;
      box-shadow: 0px 2px 6px rgba(0,0,0,0.2);
    }

    h2 {
      margin-top: 0;
    }

    .cards {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }

    .card {
      flex: 1;
      padding: 20px;
      text-align: center;
      background: #fafafa;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .quick-action {
      margin-bottom: 15px;
    }

    .quick-action button {
      padding: 6px 12px;
      border: 1px solid #333;
      background: #eaeaea;
      cursor: pointer;
      border-radius: 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table, th, td {
      border: 1px solid #aaa;
    }

    th, td {
      padding: 10px;
      text-align: center;
    }

    th {
      background: #e0e0e0;
    }

    .form-popup {
      display: none;
      margin-top: 15px;
      background: #f9f9f9;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form-popup input {
      margin: 5px;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .form-popup button {
      padding: 6px 12px;
      margin-top: 10px;
      border: none;
      background: #5a5a5a;
      color: #fff;
      cursor: pointer;
      border-radius: 4px;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Inventory Management</h2>

  <!-- Cards -->
  <div class="cards">
    <div class="card">Total Items: <span id="totalItems">0</span></div>
    <div class="card">Low Stock Items: <span id="lowStock">0</span></div>
    <div class="card">Expiring Soon: <span id="expiringSoon">0</span></div>
    <div class="card">Inventory Value: ₱<span id="inventoryValue">0</span></div>
  </div>

  <!-- Quick Action -->
  <div class="quick-action">
    Quick Action: <button onclick="toggleForm()">+ Add Item</button>
  </div>

  <!-- Form -->
  <div id="form" class="form-popup">
    <input type="text" id="medicine" placeholder="Medicine" required>
    <input type="number" id="quantity" placeholder="Stock Quantity" required>
    <input type="text" id="category" placeholder="Category" required>
    <input type="number" id="price" placeholder="Unit Price" required>
    <input type="text" id="status" placeholder="Status" required>
    <input type="date" id="expiry" required>
    <br>
    <button onclick="addItem()">Save Item</button>
  </div>

  <!-- Table -->
  <table>
    <thead>
      <tr>
        <th>Medicine</th>
        <th>Stock Quantity</th>
        <th>Category</th>
        <th>Unit Price</th>
        <th>Status</th>
        <th>Expiry</th>
      </tr>
    </thead>
    <tbody id="inventoryTable">
      <!-- Items will appear here -->
    </tbody>
  </table>
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

    // Update stats
    updateStats();

    // Clear form
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
    soon.setDate(today.getDate() + 30); // expiring in 30 days

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
</script>

</body>
</html>
