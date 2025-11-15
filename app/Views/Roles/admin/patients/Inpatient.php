<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Inpatient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-content" id="mainContent">
  <div class="page-header">
    <h1 class="page-title">Inpatient Registration</h1>
  </div>

  <div class="card">
    <div class="card-body">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= session()->getFlashdata('success') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= session()->getFlashdata('error') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
      <?php endif; ?>

      <form id="inpatientForm" method="POST" action="<?= base_url('admin/patients/register') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="inpatient">

        <!-- Personal Information -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-user"></i> Personal Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?= old('first_name') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Middle Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" value="<?= old('middle_name') ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= old('last_name') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Name Extension</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-id-badge text-muted"></i></span>
                <input type="text" name="name_extension" class="form-control" placeholder="e.g., Jr., III" value="<?= old('name_extension') ?>">
              </div>
            </div>
          </div>
          <div class="form-row" style="margin-top: 0.75rem;">
            <div class="form-group">
              <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                <input type="date" name="date_of_birth" class="form-control" value="<?= old('date_of_birth') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Age</label>
              <input type="number" name="age" class="form-control" placeholder="Age" min="0" max="130" readonly>
            </div>
            <div class="form-group">
              <label class="form-label">Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Civil Status <span class="text-danger">*</span></label>
              <select name="civil_status" class="form-select" required>
                <option value="">Select Civil Status</option>
                <option value="single" <?= old('civil_status') == 'single' ? 'selected' : '' ?>>Single</option>
                <option value="married" <?= old('civil_status') == 'married' ? 'selected' : '' ?>>Married</option>
                <option value="widowed" <?= old('civil_status') == 'widowed' ? 'selected' : '' ?>>Widowed</option>
                <option value="separated" <?= old('civil_status') == 'separated' ? 'selected' : '' ?>>Separated</option>
                <option value="divorced" <?= old('civil_status') == 'divorced' ? 'selected' : '' ?>>Divorced</option>
              </select>
            </div>
          </div>
          <div class="form-row" style="margin-top: 0.75rem;">
            <div class="form-group" style="flex: 1 1 100%;">
              <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                <input type="text" name="place_of_birth" class="form-control" placeholder="City/Municipality, Province" value="<?= old('place_of_birth') ?>" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-address-book"></i> Contact Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Province <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                <input type="text" id="provinceSearch" class="form-control" placeholder="Search province..." style="max-width: 220px;">
                <select id="provinceSelect" class="form-select" required>
                  <option value="">Select Province</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-city text-muted"></i></span>
                <input type="text" id="citySearch" class="form-control" placeholder="Search city/municipality..." style="max-width: 220px;">
                <select id="citySelect" class="form-select" required disabled>
                  <option value="">Select City/Municipality</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Barangay <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-home text-muted"></i></span>
                <input type="text" id="barangaySearch" class="form-control" placeholder="Search barangay..." style="max-width: 220px;">
                <select id="barangaySelect" class="form-select" required disabled>
                  <option value="">Select Barangay</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">House No. / Street</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-road text-muted"></i></span>
                <input type="text" id="streetInput" class="form-control" placeholder="e.g., 23-A Mabini St." value="">
              </div>
            </div>
            <input type="hidden" name="address" id="addressHidden" value="<?= old('address') ?>">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">+63</span>
                <input type="tel" name="phone" class="form-control" placeholder="912 345 6789" value="<?= old('phone') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text">@</span>
                <input type="email" name="email" class="form-control" placeholder="patient@example.com" value="<?= old('email') ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Admission Details -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-hospital-user"></i> Admission Details</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Admission Date <span class="text-danger">*</span></label>
              <input type="date" name="admission_date" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Admission Time</label>
              <input type="time" name="admission_time" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-label">Admission Type <span class="text-danger">*</span></label>
              <select name="admission_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="emergency">Emergency</option>
                <option value="elective">Elective</option>
                <option value="transfer">Transfer</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Admitting Doctor <span class="text-danger">*</span></label>
              <select name="attending_physician" class="form-select">
                <option value="">Select Physician</option>
                <?php if (!empty($doctors)): ?>
                  <?php foreach ($doctors as $doctor): ?>
                    <?php
                      $value = $doctor['id'] ?? '';
                      $label = $doctor['display_name'] ?? $doctor['username'] ?? 'Unknown Doctor';
                      $selected = old('attending_physician') == $value ? 'selected' : '';
                    ?>
                    <option value="<?= esc($value) ?>" <?= $selected ?>><?= esc($label) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Ward</label>
              <select name="ward" id="wardSelect" class="form-select">
                <option value="">Select Ward</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Room</label>
              <select name="room" id="roomSelect" class="form-select" disabled>
                <option value="">Select Room</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Bed</label>
              <select name="bed" id="bedSelect" class="form-select" disabled>
                <option value="">Select Bed</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Admitting Diagnosis <span class="text-danger">*</span></label>
            <textarea name="admitting_diagnosis" class="form-control" rows="2" placeholder="Chief complaint / Admitting diagnosis" required><?= old('admitting_diagnosis') ?></textarea>
          </div>
          <div class="form-group" style="margin-top: 0.75rem;">
            <label class="form-label">Reason for Admission</label>
            <textarea name="reason_admission" class="form-control" rows="2" placeholder="Additional details for admission (optional)"><?= old('reason_admission') ?></textarea>
          </div>
        </div>

        <!-- Emergency Contact -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Contact Person <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user-friends text-muted"></i></span>
                <input type="text" name="emergency_contact_person" class="form-control" placeholder="Full name of emergency contact" value="<?= old('emergency_contact_person') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Relationship</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-heart text-muted"></i></span>
                <input type="text" name="emergency_contact_relationship" class="form-control" placeholder="e.g., Father, Spouse" value="<?= old('emergency_contact_relationship') ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">+63</span>
                <input type="tel" name="emergency_contact_phone" class="form-control" placeholder="912 345 6789" value="<?= old('emergency_contact_phone') ?>" minlength="10" maxlength="15" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Insurance -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-file-invoice"></i> Insurance</h3>
          <div id="insuranceContainer">
            <div class="form-row insurance-row">
              <div class="form-group">
                <label class="form-label">Insurance Provider</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-building text-muted"></i></span>
                  <select name="insurance_provider" class="form-select">
                    <option value="">Select Insurance Provider</option>
                    <?php
                      $insuranceOptions = [
                        'PhilHealth',
                        'Maxicare',
                        'Intellicare',
                        'Medicard',
                        'Kaiser',
                        'Others',
                      ];
                      $oldInsurance = old('insurance_provider');
                    ?>
                    <?php foreach ($insuranceOptions as $opt): ?>
                      <option value="<?= esc($opt) ?>" <?= $oldInsurance === $opt ? 'selected' : '' ?>>
                        <?= esc($opt) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Policy / Insurance Number</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-id-card text-muted"></i></span>
                  <input type="text" name="insurance_number" class="form-control" placeholder="Enter policy or insurance number" value="<?= old('insurance_number') ?>">
                </div>
              </div>
            </div>
          </div>
          <button type="button" id="addInsuranceBtn" class="btn btn-sm btn-outline-primary" style="margin-top: 0.5rem;">
            <i class="fas fa-plus"></i> Add Insurance
          </button>
        </div>

        <!-- Medical Information -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-notes-medical"></i> Medical Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Blood Type</label>
              <select name="blood_type" class="form-select">
                <option value="">Select Blood Type</option>
                                    <option value="A+" <?= old('blood_type') == 'A+' ? 'selected' : '' ?>>A+</option>
                                    <option value="A-" <?= old('blood_type') == 'A-' ? 'selected' : '' ?>>A-</option>
                                    <option value="B+" <?= old('blood_type') == 'B+' ? 'selected' : '' ?>>B+</option>
                                    <option value="B-" <?= old('blood_type') == 'B-' ? 'selected' : '' ?>>B-</option>
                                    <option value="AB+" <?= old('blood_type') == 'AB+' ? 'selected' : '' ?>>AB+</option>
                                    <option value="AB-" <?= old('blood_type') == 'AB-' ? 'selected' : '' ?>>AB-</option>
                                    <option value="O+" <?= old('blood_type') == 'O+' ? 'selected' : '' ?>>O+</option>
                                    <option value="O-" <?= old('blood_type') == 'O-' ? 'selected' : '' ?>>O-</option>
                                    <option value="A1+" <?= old('blood_type') == 'A1+' ? 'selected' : '' ?>>A1+</option>
                                    <option value="A1-" <?= old('blood_type') == 'A1-' ? 'selected' : '' ?>>A1-</option>
                                    <option value="A1B+" <?= old('blood_type') == 'A1B+' ? 'selected' : '' ?>>A1B+</option>
                                    <option value="A1B-" <?= old('blood_type') == 'A1B-' ? 'selected' : '' ?>>A1B-</option>
                                    <option value="A2+" <?= old('blood_type') == 'A2+' ? 'selected' : '' ?>>A2+</option>
                                    <option value="A2-" <?= old('blood_type') == 'A2-' ? 'selected' : '' ?>>A2-</option>
                                    <option value="A2B+" <?= old('blood_type') == 'A2B+' ? 'selected' : '' ?>>A2B+</option>
                                    <option value="A2B-" <?= old('blood_type') == 'A2B-' ? 'selected' : '' ?>>A2B-</option>
              </select>
            </div>
          </div>
          <div class="form-section" style="margin-top: 1rem;">
            <h4 class="section-title" style="font-size: 1rem;">
              <i class="fas fa-heartbeat"></i> Vitals
            </h4>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Blood Pressure</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-heartbeat text-muted"></i></span>
                  <input type="text" name="vitals_bp" class="form-control" placeholder="e.g., 120/80">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Heart Rate (bpm)</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-heart text-muted"></i></span>
                  <input type="number" name="vitals_hr" class="form-control" min="0" max="300" placeholder="e.g., 72">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Temperature (°C)</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-thermometer-half text-muted"></i></span>
                  <input type="number" step="0.1" name="vitals_temp" class="form-control" min="30" max="45" placeholder="e.g., 36.8">
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Medical History & Allergies</label>
            <textarea name="medical_history" class="form-control" rows="3" placeholder="List known allergies, past medical/surgical history, relevant notes."><?= old('medical_history') ?></textarea>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="form-section" style="text-align:right;">
          <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Admission</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('inpatientForm');
  if (!form) return;

  // Submit via AJAX (expects JSON per Admin\Patients::processRegister)
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    try {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      const fd = new FormData(form);
      // CI4 default token name used in existing register view
      const csrf = document.querySelector('input[name="csrf_test_name"]');
      if (csrf) fd.append('csrf_test_name', csrf.value);
      const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const result = await res.json();
      if (result.success) {
        await Swal.fire({ icon: 'success', title: 'Success', text: result.message || 'Inpatient registered successfully.' });
        form.reset();
        // Re-initialize derived fields after reset
        initDefaults();
      } else {
        let html = result.message || 'Failed to save. Please review the form.';
        if (result.errors) {
          const list = Object.values(result.errors).map(e => `<li>${e}</li>`).join('');
          html = `<div class="text-start"><p>Please fix the following:</p><ul class="mb-0 ps-3">${list}</ul></div>`;
        }
        await Swal.fire({ icon: 'error', title: 'Error', html });
      }
    } catch (err) {
      console.error(err);
      await Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
    } finally {
      btn.disabled = false;
      btn.innerHTML = original;
    }
  });

  // DOB max and age auto-calc
  const dob = form.querySelector('input[name="date_of_birth"]');
  const age = form.querySelector('input[name="age"]');
  function updateAge() {
    if (!dob || !age || !dob.value) { if (age) age.value=''; return; }
    const t = new Date();
    const d = new Date(dob.value + 'T00:00:00');
    let a = t.getFullYear() - d.getFullYear();
    const m = t.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < d.getDate())) a--;
    age.value = (a >= 0 && a <= 130) ? a : '';
  }
  if (dob) {
    const todayStr = new Date().toISOString().split('T')[0];
    dob.setAttribute('max', todayStr);
    dob.addEventListener('change', updateAge);
    dob.addEventListener('input', updateAge);
  }

  // Phone formatting (patient only)
  function formatPhone(input) {
    if (!input) return;
    let v = input.value.replace(/\D/g, '');
    if (v.length > 0) {
      const m = v.match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      v = !m[2] ? m[1] : m[1] + ' ' + m[2] + (m[3] ? ' ' + m[3] : '');
    }
    input.value = v;
  }
  const phone = form.querySelector('input[name="phone"]');
  if (phone) phone.addEventListener('input', () => formatPhone(phone));

  // Admission defaults and limits
  function initDefaults() {
    const ad = form.querySelector('input[name="admission_date"]');
    const at = form.querySelector('input[name="admission_time"]');
    if (ad) {
      const today = new Date();
      const ds = today.toISOString().split('T')[0];
      ad.setAttribute('max', ds);
      if (!ad.value) ad.value = ds;
    }
    if (at) {
      const now = new Date();
      const hh = String(now.getHours()).padStart(2,'0');
      const mm = String(now.getMinutes()).padStart(2,'0');
      if (!at.value) at.value = `${hh}:${mm}`;
    }
  }

  // Dynamic Insurance rows (add more)
  const insuranceContainer = document.getElementById('insuranceContainer');
  const addInsuranceBtn = document.getElementById('addInsuranceBtn');
  if (insuranceContainer && addInsuranceBtn) {
    const templateRow = insuranceContainer.querySelector('.insurance-row');

    const createRemovableRow = () => {
      const clone = templateRow.cloneNode(true);
      clone.querySelectorAll('select').forEach(sel => { sel.value = ''; });
      clone.querySelectorAll('input[type="text"]').forEach(inp => { inp.value = ''; });

      const actionsCol = document.createElement('div');
      actionsCol.className = 'form-group';
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-sm btn-outline-danger';
      removeBtn.textContent = 'Remove';
      removeBtn.addEventListener('click', () => {
        insuranceContainer.removeChild(clone);
      });
      actionsCol.appendChild(removeBtn);
      clone.appendChild(actionsCol);

      return clone;
    };

    addInsuranceBtn.addEventListener('click', () => {
      const newRow = createRemovableRow();
      insuranceContainer.appendChild(newRow);
    });
  }

  // Ward / Room / Bed cascading selects (available only)
  const wardSelect = document.getElementById('wardSelect');
  const roomSelect = document.getElementById('roomSelect');
  const bedSelect  = document.getElementById('bedSelect');
  const roomsApiBase = '<?= base_url('api/rooms') ?>';

  const setSimpleLoading = (select, placeholder, loadingText) => {
    if (!select) return;
    select.disabled = true;
    select.innerHTML = `<option value="">${loadingText}</option>`;
  };

  const renderSimpleOptions = (select, placeholder, items, valueKey, labelKey) => {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item[valueKey] ?? item[labelKey] ?? '';
      opt.textContent = item[labelKey] ?? '';
      select.appendChild(opt);
    });
    select.disabled = items.length === 0;
  };

  const loadWards = async () => {
    if (!wardSelect) return;
    setSimpleLoading(wardSelect, 'Select Ward', 'Loading wards...');
    try {
      const res = await fetch(`${roomsApiBase}/wards`);
      const rows = await res.json();
      renderSimpleOptions(wardSelect, 'Select Ward', rows, 'name', 'name');
    } catch (e) {
      wardSelect.innerHTML = '<option value="">Failed to load wards</option>';
      wardSelect.disabled = true;
    }
  };

  const loadRooms = async (wardName) => {
    if (!roomSelect) return;
    setSimpleLoading(roomSelect, 'Select Room', 'Loading rooms...');
    bedSelect.innerHTML = '<option value="">Select Bed</option>';
    bedSelect.disabled = true;
    try {
      const res = await fetch(`${roomsApiBase}/rooms/${encodeURIComponent(wardName)}`);
      const rows = await res.json();
      renderSimpleOptions(roomSelect, 'Select Room', rows, 'name', 'name');
    } catch (e) {
      roomSelect.innerHTML = '<option value="">Failed to load rooms</option>';
      roomSelect.disabled = true;
    }
  };

  const loadBeds = async (wardName, roomName) => {
    if (!bedSelect) return;
    setSimpleLoading(bedSelect, 'Select Bed', 'Loading beds...');
    try {
      const res = await fetch(`${roomsApiBase}/beds/${encodeURIComponent(wardName)}/${encodeURIComponent(roomName)}`);
      const rows = await res.json();
      renderSimpleOptions(bedSelect, 'Select Bed', rows, 'name', 'name');
    } catch (e) {
      bedSelect.innerHTML = '<option value="">Failed to load beds</option>';
      bedSelect.disabled = true;
    }
  };

  if (wardSelect && roomSelect && bedSelect) {
    loadWards();

    wardSelect.addEventListener('change', () => {
      const ward = wardSelect.value;
      roomSelect.innerHTML = '<option value="">Select Room</option>';
      roomSelect.disabled = !ward;
      bedSelect.innerHTML = '<option value="">Select Bed</option>';
      bedSelect.disabled = true;
      if (ward) {
        loadRooms(ward);
      }
    });

    roomSelect.addEventListener('change', () => {
      const ward = wardSelect.value;
      const room = roomSelect.value;
      bedSelect.innerHTML = '<option value="">Select Bed</option>';
      bedSelect.disabled = !(ward && room);
      if (ward && room) {
        loadBeds(ward, room);
      }
    });
  }

  initDefaults();
  updateAge();
  // Address cascading selects with searchable dropdowns
  const provinceSelect = document.getElementById('provinceSelect');
  const citySelect = document.getElementById('citySelect');
  const barangaySelect = document.getElementById('barangaySelect');
  const addressHidden = document.getElementById('addressHidden');
  const streetInput = document.getElementById('streetInput');
  const provinceSearch = document.getElementById('provinceSearch');
  const citySearch = document.getElementById('citySearch');
  const barangaySearch = document.getElementById('barangaySearch');
  const apiBase = '<?= base_url('api/locations') ?>';

  let provinceOptions = [];
  let cityOptions = [];
  let barangayOptions = [];

  function setLoading(select, loading) {
    if (!select) return;
    select.disabled = loading;
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = loading ? 'Loading...' : 'Select';
    select.innerHTML = '';
    select.appendChild(opt);
  }

  function composeAddress() {
    const provText = provinceSelect?.selectedOptions[0]?.text || '';
    const cityText = citySelect?.selectedOptions[0]?.text || '';
    const brgyText = barangaySelect?.selectedOptions[0]?.text || '';
    const street = streetInput?.value?.trim() || '';
    const parts = [street, brgyText, cityText, provText].filter(Boolean);
    if (addressHidden) addressHidden.value = parts.join(', ');
  }

  function renderOptions(select, placeholder, rows) {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    rows.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.code;
      opt.textContent = r.name;
      select.appendChild(opt);
    });
    select.disabled = rows.length === 0;
  }

  async function loadProvinces() {
    if (!provinceSelect) return;
    setLoading(provinceSelect, true);
    try {
      const res = await fetch(`${apiBase}/provinces`);
      const rows = await res.json();
      provinceOptions = rows;
      renderOptions(provinceSelect, 'Select Province', provinceOptions);
      provinceSelect.disabled = false;
    } catch (e) {
      provinceSelect.innerHTML = '<option value="">Failed to load provinces</option>';
      provinceSelect.disabled = true;
    }
  }

  async function loadCities(provCode) {
    if (!citySelect) return;
    setLoading(citySelect, true);
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    barangaySelect.disabled = true;
    try {
      const res = await fetch(`${apiBase}/cities/${encodeURIComponent(provCode)}`);
      const rows = await res.json();
      cityOptions = rows;
      renderOptions(citySelect, 'Select City/Municipality', cityOptions);
      citySelect.disabled = false;
    } catch (e) {
      citySelect.innerHTML = '<option value="">Failed to load cities</option>';
      citySelect.disabled = true;
    }
    composeAddress();
  }

  async function loadBarangays(cityCode) {
    if (!barangaySelect) return;
    setLoading(barangaySelect, true);
    try {
      const res = await fetch(`${apiBase}/barangays/${encodeURIComponent(cityCode)}`);
      const rows = await res.json();
      barangayOptions = rows;
      renderOptions(barangaySelect, 'Select Barangay', barangayOptions);
      barangaySelect.disabled = false;
    } catch (e) {
      barangaySelect.innerHTML = '<option value="">Failed to load barangays</option>';
      barangaySelect.disabled = true;
    }
    composeAddress();
  }

  if (provinceSelect && citySelect && barangaySelect && addressHidden) {
    loadProvinces();
    provinceSelect.addEventListener('change', () => {
      const v = provinceSelect.value;
      if (!v) {
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        citySelect.disabled = true;
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        composeAddress();
        return;
      }
      if (citySearch) citySearch.value = '';
      if (barangaySearch) barangaySearch.value = '';
      loadCities(v);
    });
    citySelect.addEventListener('change', () => {
      const v = citySelect.value;
      if (!v) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        composeAddress();
        return;
      }
      if (barangaySearch) barangaySearch.value = '';
      loadBarangays(v);
    });
    barangaySelect.addEventListener('change', composeAddress);
    if (streetInput) streetInput.addEventListener('input', composeAddress);

    if (provinceSearch) {
      provinceSearch.addEventListener('input', () => {
        const term = provinceSearch.value.trim().toLowerCase();
        const filtered = term
          ? provinceOptions.filter(r => r.name.toLowerCase().includes(term))
          : provinceOptions;
        renderOptions(provinceSelect, 'Select Province', filtered);
      });
    }

    if (citySearch) {
      citySearch.addEventListener('input', () => {
        const term = citySearch.value.trim().toLowerCase();
        const filtered = term
          ? cityOptions.filter(r => r.name.toLowerCase().includes(term))
          : cityOptions;
        renderOptions(citySelect, 'Select City/Municipality', filtered);
      });
    }

    if (barangaySearch) {
      barangaySearch.addEventListener('input', () => {
        const term = barangaySearch.value.trim().toLowerCase();
        const filtered = term
          ? barangayOptions.filter(r => r.name.toLowerCase().includes(term))
          : barangayOptions;
        renderOptions(barangaySelect, 'Select Barangay', filtered);
      });
    }
  }
});
</script>
<?= $this->endSection() ?>

