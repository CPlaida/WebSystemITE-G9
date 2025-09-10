<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Patient Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #3f37c9;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-color: #dee2e6;
            --text-color: #333;
            --white: #ffffff;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --border-radius: 6px;
            --border-radius-lg: 12px;
            --transition: all 0.2s ease;
        }
        
        .main-content {
            padding: 20px;
            width: 100%;
            margin-left: 120px;
            transition: all 0.3s;
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .page-header {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            background: var(--white);
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-body {
            padding: 25px;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--dark-gray);
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-gray);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-text {
            padding: 10px 15px;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .input-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <label class="form-label required-field">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="first_name" class="form-control" placeholder="Patient's First Name" value="<?= old('first_name') ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="last_name" class="form-control" placeholder="Patient's Last Name" value="<?= old('last_name') ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                                    <input type="date" name="date_of_birth" class="form-control" value="<?= old('date_of_birth') ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Gender</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-venus-mars text-muted"></i></span>
                                    <select name="gender" class="form-control" required>
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
                                <label class="form-label required-field">Mobile Number</label>
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
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Patient Type</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hospital-user text-muted"></i></span>
                                    <select name="patient_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="inpatient" <?= old('patient_type') == 'inpatient' ? 'selected' : '' ?>>Inpatient</option>
                                        <option value="outpatient" <?= old('patient_type') == 'outpatient' ? 'selected' : '' ?>>Outpatient</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Blood Type</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tint text-muted"></i></span>
                                    <input type="text" name="blood_type" class="form-control" placeholder="e.g., A+, B-, O+, AB-" value="<?= old('blood_type') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Medical History & Allergies</label>
                            <textarea name="medical_history" class="form-control" rows="3" placeholder="List any known allergies, medical conditions, or relevant medical history"><?= old('medical_history') ?></textarea>
                        </div>
                    </div>
                        <div class="d-flex gap-3">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Patient
                            </button>
                        </div>
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
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Registering...';
                    
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
    });
    </script>
<?= $this->endSection() ?>
