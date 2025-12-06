<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Book Appointment<?= $this->endSection() ?>

<?= $this->section('content') ?>
  

  <div class="container text-start">
    <div class="header text-start">
      <h1 class="page-title text-start">
        <i class=""></i> Book Appointment
      </h1>
    </div>
    
    <div class="card">
      <div class="card-header text-start">
        <h2 class="card-title text-start">Appointment Information</h2>
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
                    <option value="<?= esc(trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? '') . ' ' . ($p['name_extension'] ?? ''))) ?>"></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </datalist>
            </div>

            <!-- Date (Dynamic from schedules) -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_date">Date</label>
              <select name="appointment_date" id="appointment_date" class="form-select" required disabled>
                <option value="" selected>Loading available dates...</option>
              </select>
            </div>

            <!-- Doctor Selection (Dynamic by date) -->
            <div class="form-group">
              <label class="form-label required-field" for="doctor_id">Doctor</label>
              <select name="doctor_id" id="doctor_id" class="form-select" required disabled>
                <option value="" selected>Select a date first</option>
              </select>
            </div>

            <!-- Time (Dynamic by doctor + date) -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_time">Time</label>
              <select name="appointment_time" id="appointment_time" class="form-select" required disabled>
                <option value="" selected>Select a doctor first</option>
              </select>
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
      const dateSelect = document.getElementById('appointment_date');
      const doctorSelect = document.getElementById('doctor_id');
      const timeSelect = document.getElementById('appointment_time');

      // Helper: Set options for a select element
      const setOptions = (select, options, placeholder, disabled = false) => {
        select.innerHTML = '';
        const placeholderOpt = document.createElement('option');
        placeholderOpt.value = '';
        placeholderOpt.textContent = placeholder;
        placeholderOpt.selected = true;
        select.appendChild(placeholderOpt);
        options.forEach(opt => {
          const option = document.createElement('option');
          option.value = opt.value;
          option.textContent = opt.label;
          select.appendChild(option);
        });
        select.disabled = disabled;
      };

      // Helper: Clear dependent selects
      const clearDependent = (from) => {
        if (from === 'date') {
          setOptions(doctorSelect, [], 'Select a date first', true);
          setOptions(timeSelect, [], 'Select a doctor first', true);
        } else if (from === 'doctor') {
          setOptions(timeSelect, [], 'Select a doctor first', true);
        }
      };

      // Load available dates on page load
      const loadDates = async () => {
        setOptions(dateSelect, [], 'Loading available dates...', true);
        try {
          const res = await fetch('<?= base_url('appointments/available-dates') ?>');
          const data = await res.json();
          if (data.success && data.dates && data.dates.length) {
            const opts = data.dates.map(d => {
              const date = new Date(d + 'T00:00:00');
              return {
                value: d,
                label: date.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })
              };
            });
            setOptions(dateSelect, opts, 'Select Date');
            dateSelect.disabled = false;
          } else {
            setOptions(dateSelect, [], 'No available dates', true);
          }
        } catch (e) {
          setOptions(dateSelect, [], 'Failed to load dates', true);
          clearDependent('date');
        }
      };

      // Load doctors for selected date
      const loadDoctors = async (date) => {
        setOptions(doctorSelect, [], 'Loading doctors...', true);
        setOptions(timeSelect, [], 'Select a doctor first', true);
        try {
          const res = await fetch('<?= base_url('appointments/doctors-by-date') ?>?date=' + encodeURIComponent(date));
          const data = await res.json();
          if (data.success && data.doctors && data.doctors.length) {
            const opts = data.doctors.map(d => ({ value: d.doctor_id, label: 'Dr. ' + d.name }));
            setOptions(doctorSelect, opts, 'Select Doctor');
            doctorSelect.disabled = false;
          } else {
            setOptions(doctorSelect, [], 'No doctors available for this date', true);
          }
        } catch (e) {
          setOptions(doctorSelect, [], 'Failed to load doctors', true);
        }
      };

      // Load times for selected date + doctor
      const loadTimes = async (date, doctorId) => {
        setOptions(timeSelect, [], 'Loading times...', true);
        try {
          const res = await fetch('<?= base_url('appointments/times-by-doctor') ?>?date=' + encodeURIComponent(date) + '&doctor_id=' + encodeURIComponent(doctorId));
          const data = await res.json();
          if (data.success && data.times && data.times.length) {
            const opts = data.times.map(t => ({ value: t.value, label: t.label }));
            setOptions(timeSelect, opts, 'Select Time');
            timeSelect.disabled = false;
          } else {
            setOptions(timeSelect, [], 'No available times for this doctor', true);
          }
        } catch (e) {
          setOptions(timeSelect, [], 'Failed to load times', true);
        }
      };

      // Wire events
      dateSelect.addEventListener('change', function() {
        clearDependent('date');
        if (this.value) {
          loadDoctors(this.value);
        }
      });

      doctorSelect.addEventListener('change', function() {
        clearDependent('doctor');
        const date = dateSelect.value;
        if (date && this.value) {
          loadTimes(date, this.value);
        }
      });

      // Form validation
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

      // Initialize: Load dates
      loadDates();
    });
  </script>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
