<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Appointment - HMS</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background-color: #eef2f7;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      border-radius: 0.75rem;
      border: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    .card-header {
      background: linear-gradient(45deg, #0d6efd, #0dcaf0);
      color: white;
      border-radius: 0.75rem 0.75rem 0 0;
      padding: 1.25rem;
    }
    .card-header h5 {
      margin: 0;
      font-weight: 600;
    }
    .form-control, .form-select {
      border-radius: 0.5rem;
      padding: 0.6rem 0.8rem;
    }
    .form-label {
      font-weight: 500;
    }
    .required-field::after {
      content: " *";
      color: #dc3545;
    }
    textarea {
      resize: none;
    }
    .btn-primary {
      background: linear-gradient(45deg, #0d6efd, #0dcaf0);
      border: none;
      font-weight: 500;
      transition: 0.3s;
    }
    .btn-primary:hover {
      background: linear-gradient(45deg, #0b5ed7, #0aa2c0);
      transform: translateY(-2px);
    }
    .btn-outline-secondary {
      border-radius: 0.5rem;
    }
    .btn-outline-secondary:hover {
      background: #f8f9fa;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-calendar-plus me-2"></i> Book Appointment</h5>
        <p class="small mb-0">Please fill in all required fields</p>
      </div>
      <div class="card-body p-4">
        <form id="appointmentForm" class="needs-validation" novalidate>
          <div class="row g-4">
            <!-- Patient Name -->
            <div class="col-md-6">
              <label class="form-label required-field">Patient Name</label>
              <input type="text" class="form-control" placeholder="Enter patient name" required>
            </div>

            <!-- Doctor -->
            <div class="col-md-6">
              <label class="form-label required-field">Doctor</label>
              <select class="form-select" required>
                <option value="">Select Doctor</option>
                <option>Dr. John Smith</option>
                <option>Dr. Maria Santos</option>
                <option>Dr. Alex Cruz</option>
              </select>
            </div>

            <!-- Date -->
            <div class="col-md-6">
              <label class="form-label required-field">Date</label>
              <input type="date" class="form-control" required>
            </div>

            <!-- Time -->
            <div class="col-md-6">
              <label class="form-label required-field">Time</label>
              <select class="form-select" required>
                <option value="">Select Time</option>
                <option>09:00 AM</option>
                <option>10:00 AM</option>
                <option>11:00 AM</option>
                <option>01:00 PM</option>
                <option>02:00 PM</option>
                <option>03:00 PM</option>
              </select>
            </div>

            <!-- Appointment Type -->
            <div class="col-md-6">
              <label class="form-label required-field">Appointment Type</label>
              <select class="form-select" required>
                <option value="">Select Type</option>
                <option>Consultation</option>
                <option>Follow-up</option>
                <option>Lab Test</option>
                <option>Emergency</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea class="form-control" rows="3" placeholder="Additional Notes..."></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between mt-5 pt-4 border-top">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary px-4">
              <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <button type="submit" class="btn btn-primary px-4">
              <i class="fas fa-calendar-check me-2"></i> Book Appointment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>
</body>
</html>
