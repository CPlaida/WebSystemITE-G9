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
                    <option value="<?= esc(trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? '') . ' ' . ($p['name_extension'] ?? ''))) ?>"></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </datalist>
              <div class="error-message">Please select or enter patient name</div>
            </div>

            <!-- Date (Dynamic from schedules) -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_date">Date</label>
              <select name="appointment_date" id="appointment_date" class="form-select" required disabled>
                <option value="" selected>Loading available dates...</option>
              </select>
              <div class="error-message">Please select a date</div>
            </div>

            <!-- Doctor Selection (Dynamic by date) -->
            <div class="form-group">
              <label class="form-label required-field" for="doctor_id">Doctor</label>
              <select name="doctor_id" id="doctor_id" class="form-select" required disabled>
                <option value="" selected>Select a date first</option>
              </select>
              <div class="error-message">Please select a doctor</div>
            </div>

            <!-- Time (Dynamic by doctor + date) -->
            <div class="form-group">
              <label class="form-label required-field" for="appointment_time">Time</label>
              <select name="appointment_time" id="appointment_time" class="form-select" required disabled>
                <option value="" selected>Select a doctor first</option>
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
      const dateSelect = document.getElementById('appointment_date');
      const doctorSelect = document.getElementById('doctor_id');
      const timeSelect = document.getElementById('appointment_time');

      const setOptions = (selectEl, options, placeholder, disabled = false) => {
        selectEl.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = placeholder;
        ph.selected = true;
        ph.disabled = true;
        selectEl.appendChild(ph);
        (options || []).forEach(opt => {
          const o = document.createElement('option');
          o.value = opt.value;
          o.textContent = opt.label;
          selectEl.appendChild(o);
        });
        selectEl.disabled = disabled;
      };

      const formatDateLong = (isoDate) => {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-').map(Number);
        const dt = new Date(y, (m || 1) - 1, d || 1);
        const base = dt.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
        return base.replace(/^([A-Za-z]+) (\d+), (\d{4})$/, '$1, $2, $3');
      };

      const clearDependent = (level) => {
        if (level === 'date') {
          setOptions(doctorSelect, [], 'Select a date first', true);
          setOptions(timeSelect, [], 'Select a doctor first', true);
        } else if (level === 'doctor') {
          setOptions(timeSelect, [], 'Select a doctor first', true);
        }
      };

      // Load available dates
      const loadDates = async () => {
        try {
          const res = await fetch('<?= base_url('appointments/available-dates') ?>');
          const data = await res.json();
          if (data.success && data.dates && data.dates.length) {
            const opts = data.dates.map(d => ({ value: d, label: formatDateLong(d) }));
            setOptions(dateSelect, opts, 'Select Date');
            dateSelect.disabled = false;
          } else {
            setOptions(dateSelect, [], 'No available schedule', true);
            clearDependent('date');
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
            const opts = data.doctors.map(d => ({ value: d.doctor_id, label: 'Dr. ' + d.name + (d.email ? ' - ' + d.email : '') }));
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
        if (this.value && dateSelect.value) {
          loadTimes(dateSelect.value, this.value);
        }
      });

      // Client-side validation styling
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);

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

      // Initialize
      clearDependent('date');
      loadDates();
    });
  </script>
<?= $this->endSection() ?>
