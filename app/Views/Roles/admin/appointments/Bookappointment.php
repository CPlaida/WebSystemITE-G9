<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Book Appointment<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
      <div class="composite-header">
        <h1 class="composite-title">Book Appointment</h1>
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

        <!-- Duplicate Appointment Error Message (shown at top) -->
        <div id="duplicateAppointmentError" class="alert alert-danger" style="display:none; background-color: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c2c7;">
          <i class="fas fa-exclamation-circle"></i> <span id="duplicateErrorMessage"></span>
        </div>

        <form action="<?= base_url('appointments/create') ?>" method="POST" id="appointmentForm" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="form-grid">
            <!-- Patient Selection (Searchable Dropdown with Autocomplete) -->
            <div class="form-group" style="position: relative;">
              <label class="form-label required-field" for="patient_name">Patient Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="patient_name" id="patient_name" class="form-control"
                       placeholder="Type name to search existing patients..." 
                       value="<?= old('patient_name') ?>" 
                       autocomplete="off" required>
                <input type="hidden" name="patient_id" id="patient_id" value="">
              </div>
              <div id="patientResults" class="autocomplete-dropdown" style="display:none;"></div>
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
      const patientSearch = document.getElementById('patient_name');
      const patientId = document.getElementById('patient_id');
      const patientResults = document.getElementById('patientResults');

      // Patient search autocomplete
      let searchTimer = null;
      async function doPatientSearch(term) {
        if (!term || term.length < 2) { 
          patientResults.style.display = 'none'; 
          patientResults.innerHTML = ''; 
          patientId.value = '';
          return; 
        }
        try {
          const res = await fetch(`<?= base_url('patients/search') ?>?term=${encodeURIComponent(term)}`);
          const data = await res.json();
          const rows = (data && Array.isArray(data.patients)) ? data.patients : [];
          if (rows.length === 0) { 
            patientResults.style.display = 'none'; 
            patientResults.innerHTML = ''; 
            return; 
          }
          patientResults.innerHTML = rows.map(r => 
            `<div class="autocomplete-item" data-id="${r.id}">${r.name} <small class="text-muted">(${r.id})</small></div>`
          ).join('');
          patientResults.style.display = 'block';
        } catch(e) {
          console.error('Patient search error:', e);
          patientResults.style.display = 'none';
        }
      }

      if (patientSearch) {
        patientSearch.addEventListener('input', function() {
          clearTimeout(searchTimer);
          const term = this.value.trim();
          patientId.value = ''; // Clear patient ID when typing
          
          // Clear duplicate error when patient is cleared
          if (!term) {
            duplicateErrorDiv.style.display = 'none';
            hasDuplicateAppointment = false;
            form.querySelector('button[type="submit"]').disabled = false;
            if (duplicateErrorTimeout) {
              clearTimeout(duplicateErrorTimeout);
              duplicateErrorTimeout = null;
            }
          }
          
          searchTimer = setTimeout(() => doPatientSearch(term), 250);
        });

        // Handle patient selection
        patientResults?.addEventListener('click', function(e) {
          const item = e.target.closest('.autocomplete-item');
          if (!item) return;
          const id = item.getAttribute('data-id');
          patientId.value = id;
          patientResults.style.display = 'none';
          patientSearch.value = item.textContent.trim().replace(/\s*\([^)]+\)\s*$/, ''); // Remove ID from display
          
          // Check for duplicate appointment when patient is selected
          // This will check for active appointments even if date is not selected
          checkDuplicateAppointment();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!patientSearch.contains(e.target) && !patientResults.contains(e.target)) {
            patientResults.style.display = 'none';
          }
        });
      }

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

      // Check for duplicate appointment
      const duplicateErrorDiv = document.getElementById('duplicateAppointmentError');
      const duplicateErrorMessage = document.getElementById('duplicateErrorMessage');
      let hasDuplicateAppointment = false;
      let duplicateErrorTimeout = null;

      const checkDuplicateAppointment = async () => {
        const patientIdValue = patientId.value;
        const selectedDate = dateSelect.value;
        
        // Clear any existing timeout
        if (duplicateErrorTimeout) {
          clearTimeout(duplicateErrorTimeout);
          duplicateErrorTimeout = null;
        }
        
        // Hide error if patient is not selected
        if (!patientIdValue) {
          duplicateErrorDiv.style.display = 'none';
          hasDuplicateAppointment = false;
          // Clear timeout if error is cleared
          if (duplicateErrorTimeout) {
            clearTimeout(duplicateErrorTimeout);
            duplicateErrorTimeout = null;
          }
          return;
        }

        try {
          // Build URL with patient_id (required) and date (optional)
          let url = '<?= base_url('appointments/check-patient') ?>?patient_id=' + encodeURIComponent(patientIdValue);
          if (selectedDate) {
            url += '&date=' + encodeURIComponent(selectedDate);
          }
          
          const res = await fetch(url);
          const data = await res.json();
          
          if (data.success && data.hasAppointment) {
            duplicateErrorMessage.textContent = data.message || 'This patient has a conflicting appointment.';
            duplicateErrorDiv.style.display = 'block';
            hasDuplicateAppointment = true;
            // Disable form submission
            form.querySelector('button[type="submit"]').disabled = true;
            
            // Auto-hide error message after 5 seconds
            duplicateErrorTimeout = setTimeout(() => {
              duplicateErrorDiv.style.display = 'none';
              duplicateErrorTimeout = null;
            }, 5000);
          } else {
            duplicateErrorDiv.style.display = 'none';
            hasDuplicateAppointment = false;
            // Enable form submission
            form.querySelector('button[type="submit"]').disabled = false;
            // Clear timeout if error is cleared before 5 seconds
            if (duplicateErrorTimeout) {
              clearTimeout(duplicateErrorTimeout);
              duplicateErrorTimeout = null;
            }
          }
        } catch (e) {
          console.error('Error checking duplicate appointment:', e);
          // On error, don't block submission but log it
          duplicateErrorDiv.style.display = 'none';
          hasDuplicateAppointment = false;
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
          // Check for duplicate appointment when date changes
          if (patientId.value) {
            checkDuplicateAppointment();
          }
        } else {
          // Clear duplicate error if date is cleared
          duplicateErrorDiv.style.display = 'none';
          hasDuplicateAppointment = false;
          // Clear timeout if error is cleared
          if (duplicateErrorTimeout) {
            clearTimeout(duplicateErrorTimeout);
            duplicateErrorTimeout = null;
          }
        }
      });

      doctorSelect.addEventListener('change', function() {
        clearDependent('doctor');
        if (this.value && dateSelect.value) {
          loadTimes(dateSelect.value, this.value);
        }
      });

      // Client-side validation styling and real-time validation
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add('was-validated');
          return;
        }
        
        // Prevent submission if patient already has an appointment on this date
        if (hasDuplicateAppointment) {
          event.preventDefault();
          event.stopPropagation();
          alert('This patient already has an appointment on this date. Please select a different date.');
          dateSelect.focus();
          form.classList.add('was-validated');
          return;
        }
        
        // Additional real-time validation: prevent booking past appointments
        const selectedDate = dateSelect.value;
        const selectedTime = timeSelect.value;
        
        if (selectedDate && selectedTime) {
          const now = new Date();
          const appointmentDateTime = new Date(selectedDate + ' ' + selectedTime);
          
          // Check if the selected appointment time is in the past
          if (appointmentDateTime <= now) {
            event.preventDefault();
            event.stopPropagation();
            alert('Cannot book appointments in the past. Please select a future date and time.');
            timeSelect.focus();
            form.classList.add('was-validated');
            return;
          }
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
      </div>
    </div>
  </div>
<?= $this->endSection() ?>
