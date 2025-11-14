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
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= old('last_name') ?>" required>
              </div>
            </div>
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
                <select id="provinceSelect" class="form-select" required>
                  <option value="">Select Province</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-city text-muted"></i></span>
                <select id="citySelect" class="form-select" required disabled>
                  <option value="">Select City/Municipality</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Barangay <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-home text-muted"></i></span>
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
              <label class="form-label">Attending Physician</label>
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
              <input type="text" name="ward" class="form-control" placeholder="Ward">
            </div>
            <div class="form-group">
              <label class="form-label">Room</label>
              <input type="text" name="room" class="form-control" placeholder="Room">
            </div>
            <div class="form-group">
              <label class="form-label">Bed</label>
              <input type="text" name="bed" class="form-control" placeholder="Bed">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Reason for Admission</label>
            <textarea name="reason_admission" class="form-control" rows="2" placeholder="Chief complaint / Reason for admission"></textarea>
          </div>
        </div>

        <!-- Insurance -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-file-invoice"></i> Insurance</h3>
          <div class="form-row">
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
                                    <option value="Bombay (Oh)" <?= old('blood_type') == 'Bombay (Oh)' ? 'selected' : '' ?>>Bombay (Oh)</option>
                                    <option value="Hh (Bombay)" <?= old('blood_type') == 'Hh (Bombay)' ? 'selected' : '' ?>>Hh (Bombay)</option>
                                    <option value="Rh null (Golden Blood)" <?= old('blood_type') == 'Rh null (Golden Blood)' ? 'selected' : '' ?>>Rh null (Golden Blood)</option>
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

  initDefaults();
  updateAge();
  // Address cascading selects
  const provinceSelect = document.getElementById('provinceSelect');
  const citySelect = document.getElementById('citySelect');
  const barangaySelect = document.getElementById('barangaySelect');
  const addressHidden = document.getElementById('addressHidden');
  const apiBase = '<?= base_url('api/locations') ?>';

  function setLoading(select, loading) {
    if (!select) return;
    select.disabled = loading;
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = loading ? 'Loading...' : 'Select';
    select.innerHTML = '';
    select.appendChild(opt);
  }

  const streetInput = document.getElementById('streetInput');
  function composeAddress() {
    const provText = provinceSelect?.selectedOptions[0]?.text || '';
    const cityText = citySelect?.selectedOptions[0]?.text || '';
    const brgyText = barangaySelect?.selectedOptions[0]?.text || '';
    const street = streetInput?.value?.trim() || '';
    const parts = [street, brgyText, cityText, provText].filter(Boolean);
    if (addressHidden) addressHidden.value = parts.join(', ');
  }

  async function loadProvinces() {
    if (!provinceSelect) return;
    setLoading(provinceSelect, true);
    try {
      const res = await fetch(`${apiBase}/provinces`);
      const rows = await res.json();
      provinceSelect.innerHTML = '<option value="">Select Province</option>';
      rows.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.code;
        opt.textContent = r.name;
        provinceSelect.appendChild(opt);
      });
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
      citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
      rows.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.code;
        opt.textContent = r.name;
        citySelect.appendChild(opt);
      });
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
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      rows.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.code;
        opt.textContent = r.name;
        barangaySelect.appendChild(opt);
      });
      barangaySelect.disabled = false;
    } catch (e) {
      barangaySelect.innerHTML = '<option value=\"\">Failed to load barangays</option>';
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
      loadBarangays(v);
    });
    barangaySelect.addEventListener('change', composeAddress);
    if (streetInput) streetInput.addEventListener('input', composeAddress);
  }
});
</script>
<?= $this->endSection() ?>

