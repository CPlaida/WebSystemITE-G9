<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Today's Appointments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --success-color: #1cc88a;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fc;
      color: #5a5c69;
    }
    
    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      margin-bottom: 2rem;
    }
    
    .card-header {
      background-color: #f8f9fc;
      border-bottom: 1px solid #e3e6f0;
      padding: 1.25rem 1.5rem;
    }
    
    .card-title {
      color: #4e73df;
      font-weight: 600;
      margin: 0;
    }
    
    .btn-outline-secondary {
      color: #6e707e;
      border-color: #d1d3e2;
    }
    
    .btn-outline-secondary:hover {
      background-color: #eaecf4;
      color: #5a5c69;
    }
    
    .search-box {
      position: relative;
      margin-bottom: 1.5rem;
    }
    
    .search-box i {
      position: absolute;
      top: 50%;
      left: 1rem;
      transform: translateY(-50%);
      color: #b7b9cc;
    }
    
    .search-input {
      padding-left: 2.5rem;
      border-radius: 0.5rem;
      border: 1px solid #d1d3e2;
      height: calc(1.5em + 1rem + 2px);
    }
    
    .table {
      margin-bottom: 0;
      color: #5a5c69;
    }
    
    .table thead th {
      background-color: #f8f9fc;
      color: #4e73df;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.7rem;
      letter-spacing: 0.05em;
      border-bottom: 1px solid #e3e6f0;
      padding: 0.75rem 1.5rem;
    }
    
    .table tbody td {
      padding: 1rem 1.5rem;
      vertical-align: middle;
      border-color: #e3e6f0;
    }
    
    .status-badge {
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 600;
      border-radius: 0.25rem;
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
    
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
      </a>
      <h4 class="mb-0 text-gray-800">
        <i class="fas fa-calendar-day text-primary me-2"></i>Today's Appointments
      </h4>
      <div style="width: 200px;"></div> <!-- Spacer for alignment -->
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Appointment List</h5>
        <div class="search-box" style="width: 250px;">
          <i class="fas fa-search"></i>
          <input type="text" class="form-control search-input" placeholder="Search appointments...">
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover">
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
      </div>
      <div class="card-footer text-muted small">
        Showing 1 to 3 of 3 entries
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
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
