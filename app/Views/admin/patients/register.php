<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Patient - HMS</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #2563eb;
      --primary-hover: #1d4ed8;
      --secondary-color: #64748b;
      --success-color: #10b981;
      --danger-color: #ef4444;
      --warning-color: #f59e0b;
      --info-color: #3b82f6;
      --light-color: #f8fafc;
      --dark-color: #1e293b;
      --border-color: #e2e8f0;
      --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: var(--light-color);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: var(--dark-color);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }

    .card {
      background: white;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .card-header {
      background-color: white;
      border-bottom: 1px solid var(--border-color);
      padding: 1.5rem;
    }

    .card-header h5 {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
    }

    .card-header p {
      color: var(--secondary-color);
      font-size: 0.875rem;
      margin: 0;
    }

    .card-body {
      padding: 2rem;
    }

    .form-row {
      display: grid;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .form-label {
      font-weight: 500;
      color: var(--dark-color);
      font-size: 0.875rem;
    }

    .required-field::after {
      content: " *";
      color: var(--danger-color);
    }

    .input-group {
      display: flex;
      align-items: stretch;
    }

    .input-group-text {
      background-color: var(--light-color);
      border: 1px solid var(--border-color);
      border-right: none;
      color: var(--secondary-color);
      padding: 0.75rem;
      display: flex;
      align-items: center;
      border-radius: 0.375rem 0 0 0.375rem;
    }

    .form-control, .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 0.375rem;
      font-size: 0.875rem;
      transition: all 0.2s ease;
      background-color: white;
    }

    .input-group .form-control,
    .input-group .form-select {
      border-radius: 0 0.375rem 0.375rem 0;
      border-left: none;
    }

    .form-control:focus, .form-select:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .section-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--primary-color);
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 1rem;
    }

    .row {
      display: grid;
      gap: 1rem;
    }

    .col-12 {
      grid-column: 1 / -1;
    }

    .col-md-6 {
      grid-column: span 1;
    }

    @media (min-width: 768px) {
      .row {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .col-12 {
        grid-column: 1 / -1;
      }
    }

    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    .btn-group {
      display: flex;
      gap: 1rem;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border-radius: 0.375rem;
      font-weight: 500;
      font-size: 0.875rem;
      border: 1px solid transparent;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
      border-color: var(--primary-hover);
    }

    .btn-outline-secondary {
      border-color: var(--border-color);
      color: var(--secondary-color);
      background-color: white;
    }

    .btn-outline-secondary:hover {
      background-color: var(--light-color);
      color: var(--dark-color);
    }

    @media (max-width: 768px) {
      .form-actions {
        flex-direction: column;
        align-items: stretch;
      }
      
      .btn-group {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Add Patient Details</h5>
        <p class="text-muted mb-0 small">Please fill in all required fields</p>
      </div>
      <div class="card-body p-4">
        <form id="patientForm">
          <!-- Personal Information -->
          <div class="col-12">
            <h6 class="section-title">Personal Information</h6>
            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">First Name</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Patient's First Name" required>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">Last Name</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Patient's Last Name" required>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">Date of Birth</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                    <input type="date" class="form-control" required>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">Gender</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-venus-mars text-muted"></i></span>
                    <select class="form-select" required>
                      <option value="">Select Gender</option>
                      <option>Male</option>
                      <option>Female</option>
                      <option>Other</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="col-12">
            <h6 class="section-title">Contact Information</h6>
            <div class="row g-3 mt-2">
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Complete Address</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="House No., Street, Barangay, City/Municipality">
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">Mobile Number</label>
                  <div class="input-group">
                    <span class="input-group-text">+63</span>
                    <input type="tel" class="form-control" placeholder="912 345 6789" required>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Email Address</label>
                  <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="email" class="form-control" placeholder="patient@example.com">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Medical Information -->
          <div class="col-12">
            <h6 class="section-title">Medical Information</h6>
            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label required-field">Patient Type</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-hospital-user text-muted"></i></span>
                    <select class="form-select" required>
                      <option value="">Select Type</option>
                      <option>Inpatient</option>
                      <option>Outpatient</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Patient Alignment</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-align-left text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="e.g., Left, Right, Center">
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Known Allergies</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-allergies text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="List any allergies (separate with commas)">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between gap-3 mt-5 pt-3 border-top">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary px-4">
              <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <div class="d-flex gap-3">
              <button type="reset" class="btn btn-outline-secondary px-4">
                <i class="fas fa-times me-1"></i> Cancel
              </button>
              <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Patient
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  // Form validation
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('patientForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    }
  });
  </script>
</body>
</html>
