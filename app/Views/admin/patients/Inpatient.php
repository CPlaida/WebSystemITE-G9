<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Inpatient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
 /* Simplified styles aligned with register.php */
 .main-content { padding: 15px; margin-left: 120px; background-color: #f8f9fa; min-height: 100vh; }
 .page-header { margin: 0 0 15px; padding: 12px 15px; background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
 .page-title { margin: 0; font-size: 1.3rem; font-weight: 600; color: #333; }
 .card { background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 15px; border: 1px solid #eee; }
 .card-body { padding: 15px; }
 .form-section { margin-bottom: 15px; padding: 15px; background: #fff; border-radius: 6px; border: 1px solid #eee; }
 .section-title { color: #4361ee; margin: 0 0 15px; padding-bottom: 8px; border-bottom: 1px solid #eee; font-size: 1.1rem; }
 .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap: 15px; margin-bottom: 10px; }
 .form-group { margin-bottom: 10px; }
 .form-label { display: block; margin-bottom: .3rem; font-size: .85rem; color: #555; font-weight: 500; }
 .input-group { display: flex; align-items: center; }
 .input-group-text { padding: .5rem .75rem; font-size: .9rem; background: #f8f9fa; border: 1px solid #ddd; border-right: none; border-radius: 4px 0 0 4px; color: #555; }
 .form-control, .form-select { padding: .5rem .75rem; font-size: .9rem; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
 .btn { padding: .5rem 1rem; font-size: .9rem; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; }
 .btn-primary { background: #4361ee; color: #fff; border: 1px solid #3a56d4; }
 .btn-outline-secondary { background: #fff; color: #333; border: 1px solid #ddd; }
 .text-danger { color: #dc3545; }
 @media (max-width: 992px) { .main-content { margin-left: 0; padding: 10px; } .form-row { grid-template-columns: 1fr; } }
</style>

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
              <label class="form-label">Complete Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                <input type="text" name="address" class="form-control" placeholder="House No., Street, Barangay, City/Municipality" value="<?= old('address') ?>">
              </div>
            </div>
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
              <input type="text" name="attending_physician" class="form-control" placeholder="Doctor's Name">
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

        <!-- Medical Information -->
        <div class="form-section">
          <h3 class="section-title"><i class="fas fa-notes-medical"></i> Medical Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Blood Type</label>
              <input type="text" name="blood_type" class="form-control" placeholder="e.g., A+, O-, AB+" value="<?= old('blood_type') ?>">
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
});
</script>
<?= $this->endSection() ?>

