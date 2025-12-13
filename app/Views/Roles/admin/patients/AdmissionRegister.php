<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Admission<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="composite-card billing-card" style="margin-top:0;">
    <div class="composite-header">
      <h1 class="composite-title">Patient Admission</h1>
    </div>
    <div class="card-body">
        <form id="admissionForm" method="POST" action="<?= base_url('admin/patients/admission/store') ?>">
          <?= csrf_field() ?>

          <!-- Patient Lookup -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-user-plus"></i> Select Patient</h3>
            <div class="form-row">
              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Search Patient <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                  <input type="text" id="patientSearch" class="form-control" placeholder="Type name to search existing patients..." autocomplete="off">
                </div>
                <div id="patientResults" class="autocomplete-dropdown" style="display:none"></div>
                <input type="hidden" name="patient_id" id="patientId" required>
              </div>
            </div>

            <!-- Read-only patient summary -->
            <div id="patientSummary" class="form-row" style="display:none; margin-top: .75rem;">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" id="p_fullname" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="text" id="p_dob" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label class="form-label">Gender</label>
                <input type="text" id="p_gender" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label class="form-label">Contact</label>
                <input type="text" id="p_phone" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="text" id="p_email" class="form-control" readonly>
              </div>
              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Address</label>
                <input type="text" id="p_address" class="form-control" readonly>
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
                        $value = $doctor['doctor_id'] ?? '';
                        $label = $doctor['display_name'] ?? $doctor['username'] ?? 'Unknown Doctor';
                      ?>
                      <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
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
                <label class="form-label">Bed <span class="text-danger">*</span></label>
                <select name="bed_id" id="bedSelect" class="form-select" disabled required>
                  <option value="">Select Bed</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Admitting Diagnosis <span class="text-danger">*</span></label>
              <textarea name="admitting_diagnosis" class="form-control" rows="2" placeholder="Chief complaint / Admitting diagnosis" required></textarea>
            </div>
            <div class="form-group" style="margin-top: 0.75rem;">
              <label class="form-label">Reason for Admission</label>
              <textarea name="reason_admission" class="form-control" rows="2" placeholder="Additional details for admission (optional)"></textarea>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const form = document.getElementById('admissionForm');
  const search = document.getElementById('patientSearch');
  const results = document.getElementById('patientResults');
  const patientId = document.getElementById('patientId');
  const summary = document.getElementById('patientSummary');

  const roomsApiBase = '<?= base_url('api/rooms') ?>';
  
  // Store current patient info for room filtering
  let currentPatientId = null;
  let currentPatientDob = null;
  let currentPatientGender = null;

  // Calculate age in years and days from date of birth
  function calculateAge(dateOfBirth) {
    if (!dateOfBirth) return { years: null, days: null };
    try {
      const dob = new Date(dateOfBirth);
      const now = new Date();
      const diffTime = Math.abs(now - dob);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      const years = Math.floor(diffDays / 365.25);
      return { years, days: diffDays };
    } catch (e) {
      return { years: null, days: null };
    }
  }

  // Prefill date/time
  function initDateTime(){
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
  initDateTime();

  // Patient search
  let searchTimer = null;
  async function doSearch(term){
    if (!term || term.length < 2) { results.style.display='none'; results.innerHTML=''; return; }
    try{
      const res = await fetch(`<?= base_url('patients/search') ?>?term=${encodeURIComponent(term)}`);
      const data = await res.json();
      const rows = (data && Array.isArray(data.patients)) ? data.patients : [];
      if (rows.length === 0) { results.style.display='none'; results.innerHTML=''; return; }
      results.innerHTML = rows.map(r => `<div class="autocomplete-item" data-id="${r.id}">${r.name} <small class="text-muted">(${r.id})</small></div>`).join('');
      results.style.display='block';
    }catch(e){
      console.error(e);
    }
  }
  if (search) {
    search.addEventListener('input', function(){
      clearTimeout(searchTimer);
      const term = this.value.trim();
      searchTimer = setTimeout(() => doSearch(term), 250);
    });
  }

  // Pick a patient
  results?.addEventListener('click', async function(e){
    const item = e.target.closest('.autocomplete-item');
    if (!item) return;
    const id = item.getAttribute('data-id');
    patientId.value = id;
    results.style.display = 'none';
    search.value = item.textContent.trim();

    // Load details for summary
    try{
      const res = await fetch(`<?= base_url('patients/get') ?>/${encodeURIComponent(id)}`);
      const data = await res.json();
      const p = data && data.patient ? data.patient : null;
      if (!p) return;
      document.getElementById('p_fullname').value = [p.first_name, p.middle_name, p.last_name, p.name_extension].filter(Boolean).join(' ').replace(/\s+/g,' ').trim();
      document.getElementById('p_dob').value = p.date_of_birth || '';
      document.getElementById('p_gender').value = (p.gender||'').charAt(0).toUpperCase() + (p.gender||'').slice(1);
      document.getElementById('p_phone').value = p.phone || '';
      document.getElementById('p_email').value = p.email || '';
      const addr = p.address || [p.street, p.barangay, p.city, p.province].filter(Boolean).join(', ');
      document.getElementById('p_address').value = addr || '';
      summary.style.display = 'grid';

      // Store patient info for room filtering
      currentPatientId = id;
      currentPatientDob = p.date_of_birth || null;
      currentPatientGender = p.gender || null;
      
      console.log('Patient selected:', {
        id: currentPatientId,
        dob: currentPatientDob,
        gender: currentPatientGender
      });
      
      // Reset ward/room/bed selections when patient changes
      if (wardSelect) {
        wardSelect.value = '';
        roomSelect.innerHTML = '<option value="">Select Room</option>';
        roomSelect.disabled = true;
        bedSelect.innerHTML = '<option value="">Select Bed</option>';
        bedSelect.disabled = true;
      }
      
      // Reload wards with patient filter
      if (wardSelect) {
        loadWards();
      }

      // Check if patient is already admitted
      const checkRes = await fetch(`<?= base_url('admin/patients/admission/check') ?>?patient_id=${encodeURIComponent(id)}`);
      const checkData = await checkRes.json();
      if (checkData.success && checkData.is_admitted) {
        const admission = checkData.admission;
        const admissionDate = admission.admission_date ? new Date(admission.admission_date).toLocaleDateString() : 'N/A';
        const location = [admission.ward, admission.room].filter(Boolean).join(' / ') || 'N/A';
        await Swal.fire({
          icon: 'warning',
          title: 'Patient Already Admitted',
          html: `This patient is already admitted and has not been discharged.<br><br>
                 <strong>Admission Date:</strong> ${admissionDate}<br>
                 <strong>Location:</strong> ${location}<br><br>
                 Please discharge the patient first before creating a new admission.`,
          confirmButtonText: 'OK'
        });
        // Clear the selection
        patientId.value = '';
        search.value = '';
        summary.style.display = 'none';
        currentPatientId = null;
        currentPatientDob = null;
        currentPatientGender = null;
        // Reset ward/room/bed
        if (wardSelect) {
          wardSelect.value = '';
          wardSelect.dispatchEvent(new Event('change'));
        }
      }
    }catch(e){ console.error(e); }
  });

  // Submit
  form?.addEventListener('submit', async function(e){
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    try{
      if (!patientId.value) {
        await Swal.fire({ icon: 'warning', title: 'Select a patient', text: 'Please search and select a patient first.' });
        return;
      }
      
      // Check if a valid room is selected
      if (!roomSelect || !roomSelect.value || roomSelect.disabled) {
        await Swal.fire({ 
          icon: 'warning', 
          title: 'No Valid Room', 
          text: 'No available rooms for this patient\'s age and gender. Please select a different ward or patient.' 
        });
        return;
      }
      btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      const fd = new FormData(form);
      const csrf = document.querySelector('input[name="csrf_test_name"]');
      if (csrf) fd.append('csrf_test_name', csrf.value);
      const res = await fetch(form.action, { method:'POST', body: fd, headers: { 'X-Requested-With':'XMLHttpRequest' }});
      const result = await res.json();
      if (result.success) {
        await Swal.fire({ icon: 'success', title: 'Success', text: result.message || 'Admission saved.' });
        form.reset();
        summary.style.display='none';
        currentPatientId = null;
        currentPatientDob = null;
        currentPatientGender = null;
        // Reset ward/room/bed
        if (wardSelect) {
          wardSelect.value = '';
          wardSelect.dispatchEvent(new Event('change'));
        }
        initDateTime();
      } else {
        let html = result.message || 'Failed to save. Please review the form.';
        if (result.errors) {
          const list = Object.values(result.errors).map(e => `<li>${e}</li>`).join('');
          html = `<div class="text-start"><p>Please fix the following:</p><ul class="mb-0 ps-3">${list}</ul></div>`;
        }
        await Swal.fire({ icon: 'error', title: 'Error', html });
      }
    } catch(err){
      console.error(err);
      await Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
    } finally {
      btn.disabled = false; btn.innerHTML = original;
    }
  });

  // Ward / Room / Bed cascading selects
  const wardSelect = document.getElementById('wardSelect');
  const roomSelect = document.getElementById('roomSelect');
  const bedSelect  = document.getElementById('bedSelect');

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

    const categoryOrder = [
      'General Inpatient',
      'Critical Care Units',
      'Specialized Patient Rooms'
    ];

    let totalWards = 0;
    categoryOrder.forEach(categoryName => {
      const categoryWards = categories[categoryName];
      if (categoryWards && Array.isArray(categoryWards) && categoryWards.length > 0) {
        const optgroup = document.createElement('optgroup');
        optgroup.label = categoryName;
        categoryWards.forEach(ward => {
          const opt = document.createElement('option');
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
      // Build URL with patient_id if available
      let url = `${roomsApiBase}/wards`;
      if (currentPatientId) {
        url += `?patient_id=${encodeURIComponent(currentPatientId)}`;
      }
      
      const res = await fetch(url);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      if (data.categories && Object.keys(data.categories).length > 0) {
        // Check if any categories have wards
        const hasWards = Object.values(data.categories).some(cat => Array.isArray(cat) && cat.length > 0);
        if (hasWards) {
          renderCategorizedWards(wardSelect, 'Select Ward', data.categories);
        } else {
          wardSelect.innerHTML = '<option value="">No available wards for this patient\'s age and gender.</option>';
          wardSelect.disabled = true;
        }
      } else if (Array.isArray(data.all) && data.all.length > 0) {
        const wards = data.all.map(ward => ({ name: ward, value: ward }));
        renderSimpleOptions(wardSelect, 'Select Ward', wards, 'value', 'name');
      } else if (Array.isArray(data) && data.length > 0) {
        renderSimpleOptions(wardSelect, 'Select Ward', data, 'value', 'name');
      } else {
        wardSelect.innerHTML = '<option value="">No available wards for this patient\'s age and gender.</option>';
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
      // Build URL with patient_id if available
      let url = `${roomsApiBase}/rooms/${encodeURIComponent(wardName)}`;
      if (currentPatientId) {
        url += `?patient_id=${encodeURIComponent(currentPatientId)}`;
        console.log('Loading rooms for ward:', wardName, 'with patient_id:', currentPatientId);
      } else {
        console.log('Loading rooms for ward:', wardName, '(no patient selected)');
      }
      
      const res = await fetch(url);
      const rows = await res.json();
      console.log('Rooms response:', rows);
      
      if (!Array.isArray(rows) || rows.length === 0) {
        roomSelect.innerHTML = '<option value="">No available rooms for this patient\'s age and gender.</option>';
        roomSelect.disabled = true;
        // Disable form submission if no valid rooms
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
      } else {
        renderSimpleOptions(roomSelect, 'Select Room', rows, 'name', 'name');
        // Re-enable form submission if rooms are available
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && patientId.value) submitBtn.disabled = false;
      }
    } catch (e) {
      console.error('Error loading rooms:', e);
      roomSelect.innerHTML = '<option value="">Failed to load rooms</option>';
      roomSelect.disabled = true;
      // Disable form submission on error
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
    }
  };

  const loadBeds = async (wardName, roomName) => {
    if (!bedSelect) return;
    setSimpleLoading(bedSelect, 'Select Bed', 'Loading beds...');
    try {
      const res = await fetch(`${roomsApiBase}/beds/${encodeURIComponent(wardName)}/${encodeURIComponent(roomName)}`);
      const rows = await res.json();
      renderSimpleOptions(bedSelect, 'Select Bed', rows, 'id', 'name');
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
      if (ward) loadRooms(ward);
    });
    roomSelect.addEventListener('change', () => {
      const ward = wardSelect.value;
      const room = roomSelect.value;
      bedSelect.innerHTML = '<option value="">Select Bed</option>';
      bedSelect.disabled = !(ward && room);
      if (ward && room) loadBeds(ward, room);
      
      // Enable/disable submit button based on room selection
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = !(patientId.value && ward && room && !roomSelect.disabled);
      }
    });
    
    // Also check on bed selection
    if (bedSelect) {
      bedSelect.addEventListener('change', () => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          const hasPatient = !!patientId.value;
          const hasWard = !!wardSelect.value;
          const hasRoom = !!roomSelect.value && !roomSelect.disabled;
          const hasBed = !!bedSelect.value && !bedSelect.disabled;
          submitBtn.disabled = !(hasPatient && hasWard && hasRoom && hasBed);
        }
      });
    }
  }
})();
</script>
<?= $this->endSection() ?>
