<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1 class="page-title">Patient Registration</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">×</button>
                </div>
            <?php endif; ?>

            <form id="patientForm" method="POST" action="<?= base_url('admin/patients/register') ?>">
                <?= csrf_field() ?>
                
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Personal Information
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="first_name" class="form-control" placeholder="Patient's First Name" value="<?= old('first_name') ?>" required>
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
                                <input type="text" name="last_name" class="form-control" placeholder="Patient's Last Name" value="<?= old('last_name') ?>" required>
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
                                <input type="date" name="date_of_birth" class="form-control" value="<?= old('date_of_birth') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hourglass-half text-muted"></i></span>
                                <input type="number" name="age" class="form-control" placeholder="Age" min="0" max="130" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-venus-mars text-muted"></i></span>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Civil Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ring text-muted"></i></span>
                                <select name="civil_status" class="form-select">
                                    <option value="">Select Civil Status</option>
                                    <option value="single" <?= old('civil_status') == 'single' ? 'selected' : '' ?>>Single</option>
                                    <option value="married" <?= old('civil_status') == 'married' ? 'selected' : '' ?>>Married</option>
                                    <option value="widowed" <?= old('civil_status') == 'widowed' ? 'selected' : '' ?>>Widowed</option>
                                    <option value="separated" <?= old('civil_status') == 'separated' ? 'selected' : '' ?>>Separated</option>
                                    <option value="divorced" <?= old('civil_status') == 'divorced' ? 'selected' : '' ?>>Divorced</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top: 0.75rem;">
                        <div class="form-group" style="flex: 1 1 100%;">
                            <label class="form-label">Place of Birth</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" name="place_of_birth" class="form-control" placeholder="City/Municipality, Province" value="<?= old('place_of_birth') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-address-book"></i> Contact Information
                    </h3>
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

                <!-- Medical Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-notes-medical"></i> Medical Information
                    </h3>
                    <!-- Auto-set as Outpatient -->
                    <input type="hidden" name="type" value="outpatient">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Blood Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tint text-muted"></i></span>
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
                    <button type="button" id="addInsuranceBtn" class="btn btn-sm btn-outline-primary" style="margin-top: 0.5rem; margin-bottom: 0.75rem;">
                        <i class="fas fa-plus"></i> Add Insurance
                    </button>
                    <div class="form-group">
                        <label class="form-label">Medical History & Allergies</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-notes-medical text-muted"></i></span>
                            <textarea name="medical_history" class="form-control" rows="3" placeholder="List known allergies, past medical/surgical history, relevant notes."><?= old('medical_history') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact (minimal for outpatient) -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-phone-alt"></i> Emergency Contact
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-friends text-muted"></i></span>
                                <input type="text" name="emergency_contact_person" class="form-control" placeholder="Full name of emergency contact" value="<?= old('emergency_contact_person') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" name="emergency_contact_phone" class="form-control" placeholder="912 345 6789" value="<?= old('emergency_contact_phone') ?>" minlength="10" maxlength="15">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-section text-right">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('patientForm');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            try {
                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Registering...';
                
                const formData = new FormData(form);
                
                // Add CSRF token to form data
                formData.append('csrf_test_name', document.querySelector('input[name="csrf_test_name"]').value);
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Patient registered successfully!',
                        confirmButtonColor: '#4361ee',
                        confirmButtonText: 'OK'
                    });
                    
                    // Reset form after successful submission
                    form.reset();
                } else {
                    // Show error message
                    let errorMessage = result.message || 'Failed to register patient. Please check the form and try again.';
                    
                    if (result.errors) {
                        // Handle validation errors
                        const errorList = Object.values(result.errors).map(error => 
                            `<li>${error}</li>`
                        ).join('');
                        
                        errorMessage = `<div class="text-start">
                            <p>Please fix the following errors:</p>
                            <ul class="mb-0 ps-3">${errorList}</ul>
                        </div>`;
                    }
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: errorMessage,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request. Please try again.',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Re-enable button and restore original text
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
    
    // Date of Birth max (no future dates) and Age auto-calc
    const dobInput = document.querySelector('input[name="date_of_birth"]');
    const ageInput = document.querySelector('input[name="age"]');
    
    if (dobInput && ageInput) {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        // Enforce max date as today (tomorrow and future are disabled)
        dobInput.setAttribute('max', todayStr);

        const updateAge = () => {
            const val = dobInput.value;
            if (!val) { 
                ageInput.value = ''; 
                return; 
            }
            const dob = new Date(val + 'T00:00:00');
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            ageInput.value = (age >= 0 && age <= 130) ? age : '';
        };

        dobInput.addEventListener('change', updateAge);
        dobInput.addEventListener('input', updateAge);
        // Initialize if pre-filled
        updateAge();
    }

    // Address cascading selects (Province → City/Municipality → Barangay)
    const provinceSelect = document.getElementById('provinceSelect');
    const citySelect = document.getElementById('citySelect');
    const barangaySelect = document.getElementById('barangaySelect');
    const streetInput = document.getElementById('streetInput');
    const addressHidden = document.getElementById('addressHidden');
    const provinceSearch = document.getElementById('provinceSearch');
    const citySearch = document.getElementById('citySearch');
    const barangaySearch = document.getElementById('barangaySearch');
    const apiBase = '<?= base_url('api/locations') ?>';

    let provinceOptions = [];
    let cityOptions = [];
    let barangayOptions = [];

    const setLoading = (select, loading) => {
        if (!select) return;
        select.disabled = loading;
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = loading ? 'Loading...' : 'Select';
        select.innerHTML = '';
        select.appendChild(opt);
    };

    const composeAddress = () => {
        const provText = provinceSelect?.selectedOptions[0]?.text || '';
        const cityText = citySelect?.selectedOptions[0]?.text || '';
        const brgyText = barangaySelect?.selectedOptions[0]?.text || '';
        const street = streetInput?.value?.trim() || '';
        const parts = [street, brgyText, cityText, provText].filter(Boolean);
        addressHidden.value = parts.join(', ');
    };

    const renderOptions = (select, placeholder, rows) => {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>`;
        rows.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.code;
            opt.textContent = r.name;
            select.appendChild(opt);
        });
        select.disabled = rows.length === 0;
    };

    const loadProvinces = async () => {
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
    };

    const loadCities = async (provCode) => {
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
    };

    const loadBarangays = async (cityCode) => {
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
    };

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

    // Format phone number
    const phoneInput = document.querySelector('input[name="phone"]');
    
    const formatPhoneNumber = (input) => {
        // Remove all non-digit characters
        let phoneNumber = input.value.replace(/\D/g, '');
        
        // Format the phone number as XXX XXX XXXX
        if (phoneNumber.length > 0) {
            phoneNumber = phoneNumber.substring(0, 10); // Limit to 10 digits
            
            if (phoneNumber.length > 6) {
                phoneNumber = phoneNumber.replace(/(\d{3})(\d{3})(\d{1,4})/, '$1 $2 $3');
            } else if (phoneNumber.length > 3) {
                phoneNumber = phoneNumber.replace(/(\d{3})(\d{1,3})/, '$1 $2');
            }
        }
        
        input.value = phoneNumber;
    };
    
    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => formatPhoneNumber(e.target));
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
});
</script>
<?= $this->endSection() ?>
