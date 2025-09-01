<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Requests | HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            padding: 0;
        }
        
        .header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-1px);
        }
        
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }
        
        .form-container {
            padding: 0 2rem 2rem;
        }
        
        .form-section {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            padding: 0.6rem 0.9rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-submit:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        .form-required::after {
            content: ' *';
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .form-container {
                padding: 0 1rem 1.5rem;
            }
            
            .header {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="<?= base_url('dashboard') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="page-title">Laboratory Request</h1>
            <div style="width: 120px;"></div> <!-- For alignment -->
        </div>
        
        <div class="form-container">
            <form id="labRequestForm">
                <!-- Patient Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-injured me-2"></i>Patient Information
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="patientName" class="form-label form-required">Patient Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="patientName" name="patientName" required 
                                       placeholder="Enter patient's full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="doctor" class="form-label form-required">Requesting Doctor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-md"></i></span>
                                <input type="text" class="form-control" id="doctor" name="doctor" required
                                       placeholder="Doctor's name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test Details Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-flask me-2"></i>Test Details
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="testType" class="form-label form-required">Test Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-vial"></i></span>
                                <select class="form-select" id="testType" name="testType" required>
                                    <option value="" disabled selected>Select test type</option>
                                    <option value="Blood Test">Blood Test</option>
                                    <option value="Urine Test">Urine Test</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="MRI">MRI</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="Ultrasound">Ultrasound</option>
                                    <option value="ECG">ECG</option>
                                    <option value="EEG">EEG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label form-required">Priority Level</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="" disabled selected>Select priority</option>
                                    <option value="Routine">Routine (Within 24-48 hours)</option>
                                    <option value="Urgent">Urgent (Within 4-6 hours)</option>
                                    <option value="Emergency">Emergency (Immediate)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="notes" class="form-label">
                            <i class="fas fa-clipboard me-1"></i>Clinical Notes & Instructions
                        </label>
                        <textarea class="form-control" id="notes" name="notes" 
                                 placeholder="Enter any relevant clinical notes, symptoms, or special instructions..."></textarea>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="reset" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById("labRequestForm");
            
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                
                // Get form values
                const formData = {
                    patientName: document.getElementById("patientName").value,
                    testType: document.getElementById("testType").value,
                    priority: document.getElementById("priority").value,
                    doctor: document.getElementById("doctor").value,
                    notes: document.getElementById("notes").value
                };
                
                // Determine priority color
                let priorityColor = '#6c757d'; // Default gray for Routine
                if (formData.priority === 'Urgent') priorityColor = '#fd7e14';
                if (formData.priority === 'Emergency') priorityColor = '#dc3545';
                
                // Show success message with SweetAlert2
                Swal.fire({
                    title: 'Request Submitted!',
                    html: `
                        <div class="text-start">
                            <p><strong>Patient:</strong> ${formData.patientName}</p>
                            <p><strong>Test Type:</strong> ${formData.testType}</p>
                            <p><strong>Priority:</strong> 
                                <span class="badge" style="background: ${priorityColor};">
                                    ${formData.priority}
                                </span>
                            </p>
                            <p><strong>Requested By:</strong> Dr. ${formData.doctor}</p>
                            ${formData.notes ? `<p class="mt-2"><strong>Notes:</strong><br>${formData.notes}</p>` : ''}
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#4361ee',
                    confirmButtonText: 'Done',
                    customClass: {
                        confirmButton: 'btn btn-primary px-4'
                    }
                });
                
                // Reset form
                form.reset();
            });
            
            // Add input formatting
            const patientNameInput = document.getElementById('patientName');
            if (patientNameInput) {
                patientNameInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^a-zA-Z\s-]/g, '');
                });
            }
        });
    </script>
</body>
</html>
