<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Appointment - HMS</title>
  <link rel="stylesheet" href="<?= base_url('css/font-awesome/css/all.min.css') ?>">
  <style>
    :root {
      --primary-color: #0d6efd;
      --secondary-color: #0dcaf0;
      --light-bg: #eef2f7;
      --white: #ffffff;
      --text-color: #212529;
      --border-color: #dee2e6;
      --error-color: #dc3545;
      --success-color: #198754;
      --shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      background-color: var(--light-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: var(--text-color);
      padding: 20px;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .card {
      background: var(--white);
      border-radius: 12px;
      border: none;
      box-shadow: var(--shadow);
      overflow: hidden;
      margin: 20px 0;
    }
    
    .card-header {
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      color: var(--white);
      padding: 20px;
    }
    
    .card-header h2 {
      margin: 0 0 5px 0;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .card-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 0.9rem;
    }
    
    .card-body {
      padding: 30px;
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: 8px;
    }
    
    .alert-success {
      color: #0f5132;
      background-color: #d1e7dd;
      border-color: #badbcc;
    }
    
    .alert-danger {
      color: #842029;
      background-color: #f8d7da;
      border-color: #f5c2c7;
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-color);
    }
    
    .required-field::after {
      content: " *";
      color: var(--error-color);
    }
    
    .form-control, .form-select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    textarea.form-control {
      min-height: 100px;
      resize: vertical;
    }
    
    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 30px;
      margin-top: 30px;
      border-top: 1px solid var(--border-color);
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s;
    }
    
    .btn-primary {
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    .btn-outline {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-color);
    }
    
    .btn-outline:hover {
      background-color: #f8f9fa;
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    .form-control:invalid, .form-select:invalid {
      border-color: var(--error-color);
    }
    
    .error-message {
      color: var(--error-color);
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
    }
    
    .was-validated .form-control:invalid ~ .error-message,
    .was-validated .form-select:invalid ~ .error-message {
      display: block;
    }
    
    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .form-actions {
        flex-direction: column;
        align-items: stretch;
      }
      
      .btn {
        width: 100%;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <h2><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
        <p>Please fill in all required fields</p>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
          </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('appointments/create') ?>" method="POST" id="appointmentForm" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          
          <div class="form-grid">
            <!-- Patient Selection -->
            <div class="form-group">
              <label class="form-label required-field" for="patient_name">Patient</label>
              <input type="text" name="patient_name" id="patient_name" class="form-control" 
                     placeholder="Enter patient name" required>
              <div class="error-message">Please enter patient name</div>
            </div>

            <!-- Doctor Selection -->
            <div class="form-group">
              <label class="form-label required-field" for="doctor_id">Doctor</label>
              <select name="doctor_id" id="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php if (isset($doctors) && !empty($doctors)): ?>
                  <?php foreach ($doctors as $doctor): ?>
                    <option value="<?= $doctor['id'] ?>">
                      Dr. <?= esc($doctor['username']) ?> - <?= esc($doctor['email']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <div class="error-message">Please select a doctor</div>
            </div>

            <!-- Date -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_date">Date</label>
              <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                     min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
              <div class="error-message">Please select a date</div>
            </div>

            <!-- Time -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_time">Time</label>
              <select name="appointment_time" id="appointment_time" class="form-select" required>
                <option value="">Select Time</option>
                <option value="08:00:00">08:00 AM</option>
                <option value="09:00:00">09:00 AM</option>
                <option value="10:00:00">10:00 AM</option>
                <option value="11:00:00">11:00 AM</option>
                <option value="13:00:00">01:00 PM</option>
                <option value="14:00:00">02:00 PM</option>
                <option value="15:00:00">03:00 PM</option>
                <option value="16:00:00">04:00 PM</option>
              </select>
              <div class="error-message">Please select a time</div>
            </div>

            <!-- Appointment Type -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_type">Appointment Type</label>
              <select name="appointment_type" id="appointment_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="consultation">Consultation</option>
                <option value="follow_up">Follow-up</option>
                <option value="emergency">Emergency</option>
                <option value="routine_checkup">Routine Checkup</option>
              </select>
              <div class="error-message">Please select appointment type</div>
            </div>

            <!-- Reason -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label" for="reason">Reason for Visit</label>
              <textarea name="reason" id="reason" class="form-control" rows="3" 
                        placeholder="Describe the reason for this appointment..."></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <a href="<?= base_url('appointments/list') ?>" class="btn btn-outline">
              <i class="fas fa-list"></i> View Appointments
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-calendar-check"></i> Book Appointment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('appointmentForm');
      
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        
        form.classList.add('was-validated');
      }, false);
      
      // Real-time validation
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        input.addEventListener('input', function() {
          if (this.checkValidity()) {
            this.classList.remove('invalid');
          } else {
            this.classList.add('invalid');
          }
        });
      });
    });
  </script>
</body>
</html>
