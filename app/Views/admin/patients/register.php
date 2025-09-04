<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient - HMS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 0.5rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem 1.5rem;
        }
        .form-control, .form-select {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            transition: all 0.2s;
            padding: 0.5rem 0.75rem;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #6c757d;
            padding: 0.5rem 0.75rem;
        }
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-outline-secondary {
            border-color: #dee2e6;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0d6efd;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Add Patient Details</h5>
                <p class="text-muted mb-0 small">Please fill in all required fields</p>
            </div>
            <div class="card-body p-4">
                <form id="patientForm" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <h6 class="section-title">Personal Information</h6>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label required-field">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="Patient's First Name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="Patient's Last Name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Date of Birth</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                                        <input type="date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Gender</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-venus-mars text-muted"></i></span>
                                        <select class="form-select" required>
                                            <option value="">Select Gender</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-12">
                            <h6 class="section-title">Contact Information</h6>
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <label class="form-label">Complete Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="House No., Street, Barangay, City/Municipality">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+63</span>
                                        <input type="tel" class="form-control" placeholder="912 345 6789" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="email" class="form-control" placeholder="patient@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="col-12">
                            <h6 class="section-title">Medical Information</h6>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label required-field">Patient Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-hospital-user text-muted"></i></span>
                                        <select class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option>Inpatient</option>
                                            <option>Outpatient</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Patient Alignment</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-align-left text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="e.g., Left, Right, Center">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Known Allergies</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-allergies text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="List any allergies (separate with commas)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between gap-3 mt-5 pt-3 border-top">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <div class="d-flex gap-3">
                            <button type="reset" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Patient
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('patientForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        }
    });
    </script>
</body>
</html>