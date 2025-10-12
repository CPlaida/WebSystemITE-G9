<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    :root {
        --primary-color: #4361ee;
        --primary-hover: #3a56d4;
        --light-gray: #f8f9fa;
        --border-color: #dee2e6;
        --text-color: #333;
        --white: #ffffff;
        --error-color: #dc3545;
        --success-color: #28a745;
    }
    
    .main-content {
        padding: 15px;
        margin-left: 120px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    .main-content.expanded { margin-left: 70px; }

    .page-header {
        margin: 0 0 15px 0;
        padding: 12px 15px;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .page-title {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
    }

    .card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        border: 1px solid #eee;
    }

    .card-body { 
        padding: 15px; 
    }

    .form-section {
        margin-bottom: 15px;
        padding: 15px;
        background: #fff;
        border-radius: 6px;
        border: 1px solid #eee;
    }

    .section-title {
        color: #4361ee;
        margin: 0 0 15px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
        font-size: 1.1rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group { 
        margin-bottom: 1rem; 
    }

    .form-label {
        display: block;
        margin-bottom: 0.3rem;
        font-size: 0.85rem;
        color: #555;
        font-weight: 500;
    }

    .form-control, .form-select, .input-group-text {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        outline: none;
    }

    .input-group {
        display: flex;
        align-items: center;
    }

    .input-group .form-control {
        position: relative;
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
        margin-bottom: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        font-weight: 400;
        line-height: 1.5;
        color: #555;
        text-align: center;
        white-space: nowrap;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-right: none;
        border-radius: 4px 0 0 4px;
    }

    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn i { 
        font-size: 0.9em;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        border: 1px solid var(--primary-hover);
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-outline-secondary {
        background: #fff;
        color: #333;
        border: 1px solid #ddd;
    }

    .btn-outline-secondary:hover {
        background: #f8f9fa;
    }

    .alert {
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .btn-close {
        float: right;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
    }

    .text-danger {
        color: var(--error-color);
    }

    @media (max-width: 992px) {
        .main-content {
            margin-left: 0;
            padding: 10px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="last_name" class="form-control" placeholder="Patient's Last Name" value="<?= old('last_name') ?>" required>
                            </div>
                        </div>
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
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-address-book"></i> Contact Information
                    </h3>
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

                <!-- Medical Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-notes-medical"></i> Medical Information
                    </h3>
                    <!-- Auto-set as Outpatient -->
                    <input type="hidden" name="patient_type" value="outpatient">
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
                                    <option value="Bombay (Oh)" <?= old('blood_type') == 'Bombay (Oh)' ? 'selected' : '' ?>>Bombay (Oh)</option>
                                    <option value="Hh (Bombay)" <?= old('blood_type') == 'Hh (Bombay)' ? 'selected' : '' ?>>Hh (Bombay)</option>
                                    <option value="Rh null (Golden Blood)" <?= old('blood_type') == 'Rh null (Golden Blood)' ? 'selected' : '' ?>>Rh null (Golden Blood)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medical History & Allergies</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-notes-medical text-muted"></i></span>
                            <textarea name="medical_history" class="form-control" rows="3" placeholder="List any known allergies, medical conditions, or relevant medical history"><?= old('medical_history') ?></textarea>
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
});
</script>
<?= $this->endSection() ?>
