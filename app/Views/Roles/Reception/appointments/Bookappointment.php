<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Book Appointment<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <style>
    :root {
      --primary-color: #2563eb;
      --primary-hover: #1d4ed8;
      --primary-light: #dbeafe;
      --primary-lighter: #eff6ff;
      --secondary-color: #e0f2fe;
      --light-bg: #f8fafc;
      --white: #ffffff;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --error-color: #dc2626;
      --success-color: #16a34a;
      --warning-color: #d97706;
      --info-color: #0284c7;
      --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
      --border-radius: 0.5rem;
    }
    
    body {
      background-color: var(--light-bg);
      color: var(--text-color);
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .page-title {
      color: var(--text-color);
      font-size: 1.5rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
    }
    
    .page-title i {
      color: var(--primary-color);
      font-size: 1.2em;
    }
    
    .card {
      background: var(--white);
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .card:hover {
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      background-color: var(--secondary-color);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 1.5rem;
    }
    
    .card-title {
      color: var(--primary-color);
      font-weight: 600;
      margin: 0;
      font-size: 1.1rem;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      color: var(--text-color);
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .form-control, .form-select {
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      padding: 0.5rem 0.75rem;
      font-size: 0.9rem;
      width: 100%;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px var(--primary-lighter);
      outline: none;
      transition: all 0.2s ease-in-out;
    }
    
    .btn {
      padding: 0.5rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
      background-color: var(--primary-hover);
      border-color: var(--primary-hover);
      transform: translateY(-1px);
    }
    
    .btn-outline {
      background-color: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-color);
    }
    
    .btn-outline:hover {
      background-color: var(--secondary-color);
      border-color: var(--border-color);
      color: var(--text-color);
    }
    
    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }
    
    .required-field::after {
      content: " *";
      color: var(--error-color);
    }
    
    .alert {
      padding: 1rem 1.5rem;
      border-radius: var(--border-radius);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .alert-success {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.25rem;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-group[style*="grid-column: 1 / -1"] {
      margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .form-actions {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
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
      box-shadow: 0 0 0 0.2rem rgba(41, 13, 13, 0.25);
    }
    
    textarea.form-control {
      min-height: 100px;
      resize: vertical;
    }
    
    .form-actions {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding-top: 30px;
      margin-top: 30px;
      border-top: 1px solid var(--border-color);
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
      background-color: var(--primary-color);
      color: white;
      border: none;
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary-color);
      color: var(--primary-color);
    }

    .btn-outline:hover {
      background-color: var(--primary-lighter);
      color: var(--primary-hover);
    }
    
    .btn-primary:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    /* Remove red outline on invalid fields */
    .form-control:invalid, 
    .form-select:invalid,
    .was-validated .form-control:invalid,
    .was-validated .form-select:invalid {
      border-color: var(--border-color);
      box-shadow: none;
    }
    
    /* Keep error message visible but without the red outline */
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
    
    /* Focus state overrides */
    .form-control:focus:invalid, 
    .form-select:focus:invalid {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px var(--primary-lighter);
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

  <div class="container">
    <div class="header">
      <h1 class="page-title">
        <i class=""></i> Book Appointment
      </h1>
    </div>
    
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Appointment Information</h2>
      </div>
      <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success" style="background-color: #d1e7dd; color: #0f5132; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #badbcc;">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
          </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c2c7;">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('appointments/create') ?>" method="POST" id="appointmentForm" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="form-grid">
            <!-- Patient Selection (Searchable Dropdown via datalist) -->
            <div class="form-group">
              <label class="form-label required-field" for="patient_name">Patient Name</label>
              <input type="text" list="patients_list" name="patient_name" id="patient_name" class="form-control"
                     placeholder="Search patient by name..." value="<?= old('patient_name') ?>" required>
              <datalist id="patients_list">
                <?php if (isset($patients) && !empty($patients)): ?>
                  <?php foreach ($patients as $p): ?>
                    <option value="<?= esc($p['first_name'] . ' ' . $p['last_name']) ?>"></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </datalist>
              <div class="error-message">Please select or enter patient name</div>
            </div>

            <!-- Doctor Selection -->
            <div class="form-group">
              <label class="form-label required-field" for="doctor_id">Doctor</label>
              <select name="doctor_id" id="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php if (isset($doctors) && !empty($doctors)): ?>
                  <?php foreach ($doctors as $doctor): ?>
                    <option value="<?= $doctor['id'] ?>" <?= old('doctor_id') == $doctor['id'] ? 'selected' : '' ?>>
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
                     min="<?= date('Y-m-d') ?>" value="<?= old('appointment_date', date('Y-m-d', strtotime('+1 day'))) ?>" required>
              <div class="error-message">Please select a date</div>
            </div>

            <!-- Time -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_time">Time</label>
              <select name="appointment_time" id="appointment_time" class="form-select" required>
                <option value="">Select Time</option>
                <option value="08:00:00" <?= old('appointment_time') == '08:00:00' ? 'selected' : '' ?>>08:00 AM</option>
                <option value="09:00:00" <?= old('appointment_time') == '09:00:00' ? 'selected' : '' ?>>09:00 AM</option>
                <option value="10:00:00" <?= old('appointment_time') == '10:00:00' ? 'selected' : '' ?>>10:00 AM</option>
                <option value="11:00:00" <?= old('appointment_time') == '11:00:00' ? 'selected' : '' ?>>11:00 AM</option>
                <option value="13:00:00" <?= old('appointment_time') == '13:00:00' ? 'selected' : '' ?>>01:00 PM</option>
                <option value="14:00:00" <?= old('appointment_time') == '14:00:00' ? 'selected' : '' ?>>02:00 PM</option>
                <option value="15:00:00" <?= old('appointment_time') == '15:00:00' ? 'selected' : '' ?>>03:00 PM</option>
                <option value="16:00:00" <?= old('appointment_time') == '16:00:00' ? 'selected' : '' ?>>04:00 PM</option>
                <option value="17:00:00" <?= old('appointment_time') == '17:00:00' ? 'selected' : '' ?>>05:00 PM</option>
              </select>
              <div class="error-message">Please select a time</div>
            </div>

            <!-- Appointment Type -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_type">Appointment Type</label>
              <select name="appointment_type" id="appointment_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="consultation" <?= old('appointment_type') == 'consultation' ? 'selected' : '' ?>>Consultation</option>
                <option value="follow_up" <?= old('appointment_type') == 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                <option value="emergency" <?= old('appointment_type') == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                <option value="routine_checkup" <?= old('appointment_type') == 'routine_checkup' ? 'selected' : '' ?>>Routine Checkup</option>
              </select>
              <div class="error-message">Please select appointment type</div>
            </div>

            <!-- Reason -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label" for="reason">Reason for Visit</label>
              <textarea name="reason" id="reason" class="form-control" rows="3" 
                        placeholder="Describe the reason for this appointment..."><?= old('reason') ?></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <a href="<?= base_url('appointments/list') ?>" class="btn btn-outline">
              <i class=""></i> View Appointments
            </a>
            <button type="submit" class="btn btn-primary">
              <i class=""></i> Book Appointment
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
<?= $this->endSection() ?>
