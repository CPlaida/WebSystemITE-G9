<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Today's Appointments</title>
  <link rel="stylesheet" href="<?= base_url('css/font-awesome/css/all.min.css') ?>">
  <style>
    :root {
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --success-color: #1cc88a;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --text-color: #5a5c69;
      --border-color: #e3e6f0;
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fc;
      color: var(--text-color);
      line-height: 1.6;
      padding: 20px;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      border: 1px solid #d1d3e2;
      border-radius: 4px;
      background: white;
      color: #6e707e;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn:hover {
      background-color: #eaecf4;
      color: #5a5c69;
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    .page-title {
      color: var(--text-color);
      font-size: 1.5rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .page-title i {
      color: var(--primary-color);
    }
    
    .card {
      background: white;
      border-radius: 8px;
      box-shadow: var(--card-shadow);
      margin-bottom: 30px;
      overflow: hidden;
    }
    
    .card-header {
      background-color: var(--secondary-color);
      border-bottom: 1px solid var(--border-color);
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .card-title {
      color: var(--primary-color);
      font-weight: 600;
      margin: 0;
      font-size: 1.25rem;
    }
    
    .search-box {
      position: relative;
      width: 250px;
    }
    
    .search-box i {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #b7b9cc;
    }
    
    .search-input {
      width: 100%;
      padding: 10px 15px 10px 35px;
      border: 1px solid #d1d3e2;
      border-radius: 4px;
      font-size: 14px;
      transition: border-color 0.3s;
    }
    
    .search-input:focus {
      outline: none;
      border-color: var(--primary-color);
    }
    
    .table-container {
      overflow-x: auto;
      width: 100%;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
    }
    
    th {
      background-color: var(--secondary-color);
      color: var(--primary-color);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.7rem;
      letter-spacing: 0.05em;
      text-align: left;
      padding: 12px 24px;
      border-bottom: 1px solid var(--border-color);
    }
    
    td {
      padding: 16px 24px;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-color);
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
      background-color: rgba(78, 115, 223, 0.05);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 10px;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 4px;
      text-align: center;
      white-space: nowrap;
    }
    
    .status-confirmed {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    
    .status-pending {
      background-color: #fff3cd;
      color: #664d03;
    }
    
    .card-footer {
      padding: 15px 24px;
      background-color: #f8f9fc;
      color: #858796;
      font-size: 0.875rem;
      border-top: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .search-box {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <a href="<?= base_url('dashboard') ?>" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
      <h1 class="page-title">
        <i class="fas fa-calendar-day"></i> Today's Appointments
      </h1>
      <div style="width: 200px;"></div> <!-- Spacer for alignment -->
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Appointment List</h2>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" class="search-input" placeholder="Search appointments...">
        </div>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Patient Name</th>
              <th>Doctor</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>09:00 AM</strong></td>
              <td>Juan Dela Cruz</td>
              <td>Dr. Maria Santos</td>
              <td>Consultation</td>
              <td><span class="status-badge status-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td><strong>10:00 AM</strong></td>
              <td>Ana Lopez</td>
              <td>Dr. John Smith</td>
              <td>Follow-up</td>
              <td><span class="status-badge status-pending">Pending</span></td>
            </tr>
            <tr>
              <td><strong>11:30 AM</strong></td>
              <td>Pedro Reyes</td>
              <td>Dr. Alex Cruz</td>
              <td>Lab Test</td>
              <td><span class="status-badge status-pending">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        Showing 1 to 3 of 3 entries
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.querySelector('.search-input');
      const tableRows = document.querySelectorAll('tbody tr');
      
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
          const rowText = row.textContent.toLowerCase();
          if (rowText.includes(searchTerm)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    });
  </script>
</body>
</html>
