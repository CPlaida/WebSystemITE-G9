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
                    <div class="form-row" style="margin-top: 0.75rem; display: block !important; grid-template-columns: 1fr !important;">
                        <div class="form-group" style="width: 100% !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important;">
                            <label class="form-label">Place of Birth</label>
                            <div class="input-group" style="width: 100% !important; max-width: 100% !important;">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" name="place_of_birth" class="form-control" placeholder="City/Municipality, Province" value="<?= old('place_of_birth') ?>" style="padding: 0.875rem 1.25rem !important; font-size: 1.05rem !important; min-height: 52px !important; width: 100% !important; flex: 1 !important; max-width: 100% !important;">
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
                            <div class="input-group autocomplete-wrapper">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" id="provinceInput" name="province" class="form-control autocomplete-input" placeholder="Type to search province..." autocomplete="off" required>
                                <input type="hidden" id="provinceCode" name="province_code">
                                <div class="autocomplete-dropdown" id="provinceDropdown"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                            <div class="input-group autocomplete-wrapper">
                                <span class="input-group-text"><i class="fas fa-city text-muted"></i></span>
                                <input type="text" id="cityInput" name="city" class="form-control autocomplete-input" placeholder="Type to search city/municipality..." autocomplete="off" required disabled>
                                <input type="hidden" id="cityCode" name="city_code">
                                <div class="autocomplete-dropdown" id="cityDropdown"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Barangay <span class="text-danger">*</span></label>
                            <div class="input-group autocomplete-wrapper">
                                <span class="input-group-text"><i class="fas fa-home text-muted"></i></span>
                                <input type="text" id="barangayInput" name="barangay" class="form-control autocomplete-input" placeholder="Type to search barangay..." autocomplete="off" required disabled>
                                <input type="hidden" id="barangayCode" name="barangay_code">
                                <div class="autocomplete-dropdown" id="barangayDropdown"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">House No. / Street</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-road text-muted"></i></span>
                                <input type="text" id="streetInput" name="street" class="form-control" placeholder="e.g., 23-A Mabini St." value="<?= old('street') ?>">
                            </div>
                        </div>
                        <input type="hidden" name="address" id="addressHidden" value="<?= old('address') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" name="phone" class="form-control" placeholder="912 345 6789" value="<?= old('phone') ?>" required style="padding: 0.875rem 1.25rem !important; font-size: 1.05rem !important; min-height: 52px !important;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="email" name="email" class="form-control" placeholder="patient@example.com" value="<?= old('email') ?>" style="padding: 0.875rem 1.25rem !important; font-size: 1.05rem !important; min-height: 52px !important;">
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
                            <label class="form-label">Relationship</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heart text-muted"></i></span>
                                <input type="text" name="emergency_contact_relationship" class="form-control" placeholder="e.g., Father, Spouse" value="<?= old('emergency_contact_relationship') ?>">
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

                <!-- Insurance Section -->
                <div class="form-section">
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

    // Address autocomplete (Province → City/Municipality → Barangay)
    const provinceInput = document.getElementById('provinceInput');
    const cityInput = document.getElementById('cityInput');
    const barangayInput = document.getElementById('barangayInput');
    const streetInput = document.getElementById('streetInput');
    const addressHidden = document.getElementById('addressHidden');
    const provinceCode = document.getElementById('provinceCode');
    const cityCode = document.getElementById('cityCode');
    const barangayCode = document.getElementById('barangayCode');
    const provinceDropdown = document.getElementById('provinceDropdown');
    const cityDropdown = document.getElementById('cityDropdown');
    const barangayDropdown = document.getElementById('barangayDropdown');
    const apiBase = '<?= base_url('api/locations') ?>';

    let provinceOptions = [];
    let cityOptions = [];
    let barangayOptions = [];
    let selectedProvince = null;
    let selectedCity = null;
    let selectedBarangay = null;

    const composeAddress = () => {
        const provText = selectedProvince?.name || provinceInput?.value || '';
        const cityText = selectedCity?.name || cityInput?.value || '';
        const brgyText = selectedBarangay?.name || barangayInput?.value || '';
        const street = streetInput?.value?.trim() || '';
        const parts = [street, brgyText, cityText, provText].filter(Boolean);
        addressHidden.value = parts.join(', ');
    };

    const showDropdown = (dropdown, items, onSelect) => {
        if (!dropdown) return;
        dropdown.innerHTML = '';
        if (items.length === 0) {
            dropdown.style.display = 'none';
            return;
        }
        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'autocomplete-item';
            div.textContent = item.name;
            div.addEventListener('click', () => {
                onSelect(item);
                dropdown.style.display = 'none';
            });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    };

    const hideDropdown = (dropdown) => {
        if (dropdown) dropdown.style.display = 'none';
    };

    const filterOptions = (options, term) => {
        if (!term) return options;
        const lowerTerm = term.toLowerCase();
        return options.filter(opt => opt.name.toLowerCase().includes(lowerTerm));
    };

    // Load provinces on page load
    const loadProvinces = async () => {
        try {
            const res = await fetch(`${apiBase}/provinces`);
            provinceOptions = await res.json();
        } catch (e) {
            console.error('Failed to load provinces:', e);
            provinceOptions = [];
        }
    };

    // Province autocomplete
    if (provinceInput && provinceDropdown) {
        loadProvinces();
        
        provinceInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            const filtered = filterOptions(provinceOptions, term);
            showDropdown(provinceDropdown, filtered.slice(0, 10), (item) => {
                selectedProvince = item;
                provinceInput.value = item.name;
                provinceCode.value = item.code;
                cityInput.value = '';
                cityCode.value = '';
                cityInput.disabled = false;
                barangayInput.value = '';
                barangayCode.value = '';
                barangayInput.disabled = true;
                cityOptions = [];
                barangayOptions = [];
                selectedCity = null;
                selectedBarangay = null;
                hideDropdown(cityDropdown);
                hideDropdown(barangayDropdown);
                composeAddress();
            });
        });

        provinceInput.addEventListener('focus', () => {
            if (provinceInput.value.trim()) {
                const filtered = filterOptions(provinceOptions, provinceInput.value);
                showDropdown(provinceDropdown, filtered.slice(0, 10), (item) => {
                    selectedProvince = item;
                    provinceInput.value = item.name;
                    provinceCode.value = item.code;
                    cityInput.disabled = false;
                    cityInput.value = '';
                    cityCode.value = '';
                    barangayInput.value = '';
                    barangayCode.value = '';
                    barangayInput.disabled = true;
                    composeAddress();
                });
            }
        });

        document.addEventListener('click', (e) => {
            const wrapper = provinceInput.closest('.autocomplete-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                hideDropdown(provinceDropdown);
            }
        });
    }

    // City autocomplete
    if (cityInput && cityDropdown) {
        const loadCities = async (provCode) => {
            if (!provCode) return;
            try {
                const res = await fetch(`${apiBase}/cities/${encodeURIComponent(provCode)}`);
                cityOptions = await res.json();
            } catch (e) {
                console.error('Failed to load cities:', e);
                cityOptions = [];
            }
        };

        cityInput.addEventListener('input', (e) => {
            if (!selectedProvince) return;
            if (cityOptions.length === 0) {
                loadCities(selectedProvince.code).then(() => {
                    const term = e.target.value.trim();
                    const filtered = filterOptions(cityOptions, term);
                    showDropdown(cityDropdown, filtered.slice(0, 10), (item) => {
                        selectedCity = item;
                        cityInput.value = item.name;
                        cityCode.value = item.code;
                        barangayInput.value = '';
                        barangayCode.value = '';
                        barangayInput.disabled = false;
                        barangayOptions = [];
                        selectedBarangay = null;
                        hideDropdown(barangayDropdown);
                        composeAddress();
                    });
                });
            } else {
                const term = e.target.value.trim();
                const filtered = filterOptions(cityOptions, term);
                showDropdown(cityDropdown, filtered.slice(0, 10), (item) => {
                    selectedCity = item;
                    cityInput.value = item.name;
                    cityCode.value = item.code;
                    barangayInput.value = '';
                    barangayCode.value = '';
                    barangayInput.disabled = false;
                    barangayOptions = [];
                    selectedBarangay = null;
                    hideDropdown(barangayDropdown);
                    composeAddress();
                });
            }
        });

        cityInput.addEventListener('focus', async () => {
            if (!selectedProvince) return;
            if (cityOptions.length === 0) {
                await loadCities(selectedProvince.code);
            }
            if (cityInput.value.trim()) {
                const filtered = filterOptions(cityOptions, cityInput.value);
                showDropdown(cityDropdown, filtered.slice(0, 10), (item) => {
                    selectedCity = item;
                    cityInput.value = item.name;
                    cityCode.value = item.code;
                    barangayInput.disabled = false;
                    composeAddress();
                });
            }
        });

        document.addEventListener('click', (e) => {
            const wrapper = cityInput.closest('.autocomplete-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                hideDropdown(cityDropdown);
            }
        });
    }

    // Barangay autocomplete
    if (barangayInput && barangayDropdown) {
        const loadBarangays = async (cityCode) => {
            if (!cityCode) return;
            try {
                const res = await fetch(`${apiBase}/barangays/${encodeURIComponent(cityCode)}`);
                barangayOptions = await res.json();
            } catch (e) {
                console.error('Failed to load barangays:', e);
                barangayOptions = [];
            }
        };

        barangayInput.addEventListener('input', (e) => {
            if (!selectedCity) return;
            if (barangayOptions.length === 0) {
                loadBarangays(selectedCity.code).then(() => {
                    const term = e.target.value.trim();
                    const filtered = filterOptions(barangayOptions, term);
                    showDropdown(barangayDropdown, filtered.slice(0, 10), (item) => {
                        selectedBarangay = item;
                        barangayInput.value = item.name;
                        barangayCode.value = item.code;
                        composeAddress();
                    });
                });
            } else {
                const term = e.target.value.trim();
                const filtered = filterOptions(barangayOptions, term);
                showDropdown(barangayDropdown, filtered.slice(0, 10), (item) => {
                    selectedBarangay = item;
                    barangayInput.value = item.name;
                    barangayCode.value = item.code;
                    composeAddress();
                });
            }
        });

        barangayInput.addEventListener('focus', async () => {
            if (!selectedCity) return;
            if (barangayOptions.length === 0) {
                await loadBarangays(selectedCity.code);
            }
            if (barangayInput.value.trim()) {
                const filtered = filterOptions(barangayOptions, barangayInput.value);
                showDropdown(barangayDropdown, filtered.slice(0, 10), (item) => {
                    selectedBarangay = item;
                    barangayInput.value = item.name;
                    barangayCode.value = item.code;
                    composeAddress();
                });
            }
        });

        document.addEventListener('click', (e) => {
            const wrapper = barangayInput.closest('.autocomplete-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                hideDropdown(barangayDropdown);
            }
        });
    }

    if (streetInput) {
        streetInput.addEventListener('input', composeAddress);
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
});
</script>
<?= $this->endSection() ?>
