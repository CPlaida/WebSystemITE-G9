<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Inpatient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="composite-card billing-card" style="margin-top:0;">
    <div class="composite-header">
      <h1 class="composite-title">Inpatient Registration</h1>
    </div>
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
                <select name="name_extension" class="form-select">
                  <option value="">Select Extension</option>
                  <?php
                    $nameExtensions = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
                    $oldExtension = old('name_extension');
                  ?>
                  <?php foreach ($nameExtensions as $ext): ?>
                    <option value="<?= esc($ext) ?>" <?= $oldExtension === $ext ? 'selected' : '' ?>>
                      <?= esc($ext) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
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
          <div class="form-row" style="margin-top: 0.75rem; display: block !important; grid-template-columns: 1fr !important;">
            <div class="form-group" style="width: 100% !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important;">
              <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
              <div class="input-group" style="width: 100% !important; max-width: 100% !important;">
                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                <input type="text" name="place_of_birth" class="form-control" placeholder="City/Municipality, Province" value="<?= old('place_of_birth') ?>" required style="padding: 0.875rem 1.25rem !important; font-size: 1.05rem !important; min-height: 52px !important; width: 100% !important; flex: 1 !important; max-width: 100% !important;">
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
              <div class="input-group autocomplete-wrapper">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" id="provinceSearch" class="form-control" placeholder="Type to search province..." autocomplete="off" required>
                <select id="provinceSelect" class="form-select" required style="display: none;">
                  <option value="">Select Province</option>
                </select>
                <div id="provinceDropdown" class="autocomplete-dropdown"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
              <div class="input-group autocomplete-wrapper">
                <span class="input-group-text"><i class="fas fa-city"></i></span>
                <input type="text" id="citySearch" class="form-control" placeholder="Type to search city/municipality..." autocomplete="off" disabled required>
                <select id="citySelect" class="form-select" required disabled style="display: none;">
                  <option value="">Select City/Municipality</option>
                </select>
                <div id="cityDropdown" class="autocomplete-dropdown"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Barangay <span class="text-danger">*</span></label>
              <div class="input-group autocomplete-wrapper">
                <span class="input-group-text"><i class="fas fa-home"></i></span>
                <input type="text" id="barangaySearch" class="form-control" placeholder="Type to search barangay..." autocomplete="off" disabled required>
                <select id="barangaySelect" class="form-select" required disabled style="display: none;">
                  <option value="">Select Barangay</option>
                </select>
                <div id="barangayDropdown" class="autocomplete-dropdown"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">House No. / Street</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-road"></i></span>
                <input type="text" id="streetInput" class="form-control" placeholder="e.g., 23-A Mabini St." value="" autocomplete="off">
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
              <select name="attending_doctor_id" class="form-select" required>
                <option value="">Select Physician</option>
                <?php if (!empty($doctors)): ?>
                  <?php foreach ($doctors as $doctor): ?>
                    <?php
                      $value = $doctor['doctor_id'] ?? $doctor['id'] ?? '';
                      $label = $doctor['display_name'] ?? $doctor['username'] ?? 'Unknown Doctor';
                      $selected = old('attending_doctor_id') == $value ? 'selected' : '';
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
    
    // Define insurance options to ensure they're always available
    const insuranceOptions = [
      { value: '', text: 'Select Insurance Provider' },
      { value: 'PhilHealth', text: 'PhilHealth' },
      { value: 'Maxicare', text: 'Maxicare' },
      { value: 'Intellicare', text: 'Intellicare' },
      { value: 'Medicard', text: 'Medicard' },
      { value: 'Kaiser', text: 'Kaiser' },
      { value: 'Others', text: 'Others' }
    ];

    const createRemovableRow = () => {
      const clone = templateRow.cloneNode(true);
      
      // Restore select options from hardcoded array
      const clonedSelect = clone.querySelector('select[name="insurance_provider"]');
      if (clonedSelect) {
        clonedSelect.innerHTML = '';
        insuranceOptions.forEach(opt => {
          const option = document.createElement('option');
          option.value = opt.value;
          option.textContent = opt.text;
          clonedSelect.appendChild(option);
        });
        clonedSelect.value = '';
        // Ensure select is visible and enabled
        clonedSelect.style.display = 'block';
        clonedSelect.disabled = false;
      }
      
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

  const renderCategorizedWards = (select, placeholder, categories) => {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    
    // Define category order
    const categoryOrder = [
      'General Inpatient',
      'Critical Care Units',
      'Specialized Patient Rooms'
    ];
    
    let totalWards = 0;
    
    // Render each category as an optgroup
    categoryOrder.forEach(categoryName => {
      const categoryWards = categories[categoryName];
      if (categoryWards && Array.isArray(categoryWards) && categoryWards.length > 0) {
        const optgroup = document.createElement('optgroup');
        optgroup.label = categoryName;
        
        categoryWards.forEach(ward => {
          const opt = document.createElement('option');
          // Use 'value' if available (for abbreviations like ICU), otherwise 'name'
          opt.value = ward.value || ward.name || ward;
          opt.textContent = ward.name || ward;
          optgroup.appendChild(opt);
          totalWards++;
        });
        
        select.appendChild(optgroup);
      }
    });
    
    select.disabled = totalWards === 0;
  };

  const loadWards = async () => {
    if (!wardSelect) return;
    setSimpleLoading(wardSelect, 'Select Ward', 'Loading wards...');
    try {
      const res = await fetch(`${roomsApiBase}/wards`);
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      const data = await res.json();
      
      // Handle the API response structure: { categories: {...}, all: [...] }
      if (data.categories && Object.keys(data.categories).length > 0) {
        // Render with categories using optgroups
        renderCategorizedWards(wardSelect, 'Select Ward', data.categories);
      } else if (Array.isArray(data.all) && data.all.length > 0) {
        // Fallback to 'all' array if categories don't exist
        const wards = data.all.map(ward => ({
          name: ward,
          value: ward
        }));
        renderSimpleOptions(wardSelect, 'Select Ward', wards, 'value', 'name');
      } else if (Array.isArray(data) && data.length > 0) {
        // Direct array response
        renderSimpleOptions(wardSelect, 'Select Ward', data, 'value', 'name');
      } else {
        wardSelect.innerHTML = '<option value="">No wards available</option>';
        wardSelect.disabled = true;
      }
    } catch (e) {
      console.error('Error loading wards:', e);
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
  
  function renderDropdown(dropdown, items, onSelect) {
    if (!dropdown) return;
    dropdown.innerHTML = '';
    if (items.length === 0) {
      dropdown.classList.remove('show');
      return;
    }
    items.forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'dropdown-item';
      div.textContent = item.name;
      div.dataset.value = item.code;
      div.dataset.name = item.name;
      div.addEventListener('mousedown', (e) => {
        e.preventDefault(); // Prevent blur event
        onSelect(item.code, item.name);
        dropdown.classList.remove('show');
      });
      dropdown.appendChild(div);
    });
    dropdown.classList.add('show');
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
        if (citySearch) {
          citySearch.disabled = true;
          citySearch.value = '';
        }
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        if (barangaySearch) {
          barangaySearch.disabled = true;
          barangaySearch.value = '';
        }
        composeAddress();
        return;
      }
      if (citySearch) {
        citySearch.disabled = false;
        citySearch.value = '';
      }
      if (barangaySearch) {
        barangaySearch.disabled = true;
        barangaySearch.value = '';
      }
      loadCities(v);
    });
    citySelect.addEventListener('change', () => {
      const v = citySelect.value;
      if (!v) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        if (barangaySearch) {
          barangaySearch.disabled = true;
          barangaySearch.value = '';
        }
        composeAddress();
        return;
      }
      if (barangaySearch) {
        barangaySearch.disabled = false;
        barangaySearch.value = '';
      }
      loadBarangays(v);
    });
    barangaySelect.addEventListener('change', composeAddress);
    if (streetInput) streetInput.addEventListener('input', composeAddress);

    // Auto-suggest functionality - show dropdown automatically while typing
    const provinceDropdown = document.getElementById('provinceDropdown');
    const cityDropdown = document.getElementById('cityDropdown');
    const barangayDropdown = document.getElementById('barangayDropdown');
    
    if (provinceSearch && provinceDropdown) {
      provinceSearch.addEventListener('input', (e) => {
        const term = e.target.value.trim().toLowerCase();
        const filtered = term
          ? provinceOptions.filter(r => r.name.toLowerCase().includes(term))
          : provinceOptions;
        renderOptions(provinceSelect, 'Select Province', filtered);
        renderDropdown(provinceDropdown, filtered.slice(0, 10), (code, name) => {
          provinceSelect.value = code;
          provinceSearch.value = name;
          provinceSelect.dispatchEvent(new Event('change'));
        });
      });
      
      provinceSearch.addEventListener('focus', () => {
        if (provinceOptions.length > 0) {
          const term = provinceSearch.value.trim().toLowerCase();
          const filtered = term
            ? provinceOptions.filter(r => r.name.toLowerCase().includes(term))
            : provinceOptions;
          renderDropdown(provinceDropdown, filtered.slice(0, 10), (code, name) => {
            provinceSelect.value = code;
            provinceSearch.value = name;
            provinceSelect.dispatchEvent(new Event('change'));
          });
        }
      });
      
      provinceSearch.addEventListener('blur', (e) => {
        // Don't close if clicking on dropdown
        setTimeout(() => {
          if (!provinceDropdown.contains(document.activeElement)) {
            provinceDropdown.classList.remove('show');
          }
        }, 200);
      });
      
      // Prevent dropdown from closing when clicking on it
      provinceDropdown.addEventListener('mousedown', (e) => {
        e.preventDefault();
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!provinceSearch.contains(e.target) && !provinceDropdown.contains(e.target)) {
          provinceDropdown.classList.remove('show');
        }
      });
    }

    if (citySearch && cityDropdown) {
      citySearch.addEventListener('input', (e) => {
        const term = e.target.value.trim().toLowerCase();
        const filtered = term
          ? cityOptions.filter(r => r.name.toLowerCase().includes(term))
          : cityOptions;
        renderOptions(citySelect, 'Select City/Municipality', filtered);
        renderDropdown(cityDropdown, filtered.slice(0, 10), (code, name) => {
          citySelect.value = code;
          citySearch.value = name;
          citySelect.dispatchEvent(new Event('change'));
        });
      });
      
      citySearch.addEventListener('focus', () => {
        if (cityOptions.length > 0) {
          const term = citySearch.value.trim().toLowerCase();
          const filtered = term
            ? cityOptions.filter(r => r.name.toLowerCase().includes(term))
            : cityOptions;
          renderDropdown(cityDropdown, filtered.slice(0, 10), (code, name) => {
            citySelect.value = code;
            citySearch.value = name;
            citySelect.dispatchEvent(new Event('change'));
          });
        }
      });
      
      citySearch.addEventListener('blur', (e) => {
        // Don't close if clicking on dropdown
        setTimeout(() => {
          if (!cityDropdown.contains(document.activeElement)) {
            cityDropdown.classList.remove('show');
          }
        }, 200);
      });
      
      // Prevent dropdown from closing when clicking on it
      cityDropdown.addEventListener('mousedown', (e) => {
        e.preventDefault();
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!citySearch.contains(e.target) && !cityDropdown.contains(e.target)) {
          cityDropdown.classList.remove('show');
        }
      });
    }

    if (barangaySearch && barangayDropdown) {
      barangaySearch.addEventListener('input', (e) => {
        const term = e.target.value.trim().toLowerCase();
        const filtered = term
          ? barangayOptions.filter(r => r.name.toLowerCase().includes(term))
          : barangayOptions;
        renderOptions(barangaySelect, 'Select Barangay', filtered);
        renderDropdown(barangayDropdown, filtered.slice(0, 10), (code, name) => {
          barangaySelect.value = code;
          barangaySearch.value = name;
          barangaySelect.dispatchEvent(new Event('change'));
        });
      });
      
      barangaySearch.addEventListener('focus', () => {
        if (barangayOptions.length > 0) {
          const term = barangaySearch.value.trim().toLowerCase();
          const filtered = term
            ? barangayOptions.filter(r => r.name.toLowerCase().includes(term))
            : barangayOptions;
          renderDropdown(barangayDropdown, filtered.slice(0, 10), (code, name) => {
            barangaySelect.value = code;
            barangaySearch.value = name;
            barangaySelect.dispatchEvent(new Event('change'));
          });
        }
      });
      
      barangaySearch.addEventListener('blur', (e) => {
        // Don't close if clicking on dropdown
        setTimeout(() => {
          if (!barangayDropdown.contains(document.activeElement)) {
            barangayDropdown.classList.remove('show');
          }
        }, 200);
      });
      
      // Prevent dropdown from closing when clicking on it
      barangayDropdown.addEventListener('mousedown', (e) => {
        e.preventDefault();
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!barangaySearch.contains(e.target) && !barangayDropdown.contains(e.target)) {
          barangayDropdown.classList.remove('show');
        }
      });
    }
  }
});
</script>
<?= $this->endSection() ?>

