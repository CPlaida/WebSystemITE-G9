<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Book Appointment<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
