<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Requests | HMS</title>
    <link rel="stylesheet" href="<?= base_url('css/font-awesome/css/all.min.css') ?>">
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            color: var(--white);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
            z-index: 1;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
            text-align: center;
            flex: 1;
            position: absolute;
            left: 0;
            right: 0;
            pointer-events: none;
        }
        
        .form-container {
            padding: 2rem;
        }
        
        .form-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .form-required::after {
            content: ' *';
            color: var(--error-color);
        }
        
        .input-group {
            display: flex;
            width: 100%;
        }
        
        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.6rem 0.75rem;
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            flex: 1;
            padding: 0.6rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--white);
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
            }
            
            .header {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
            }
            
            .page-title {
                position: static;
                margin-top: 0.5rem;
            }
            
            .back-btn {
                align-self: flex-start;
            }
            
            .form-container {
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="<?= base_url('laboratory/dashboard') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="page-title">Laboratory Request</h1>
        </div>
        
        <div class="form-container">
            <form id="labRequestForm" method="POST" action="<?= base_url('laboratory/request/submit') ?>">
                <!-- Patient Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-injured"></i> Patient Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="patientName" class="form-label form-required">Patient Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="patientName" name="patientName" required
                                       placeholder="Enter patient's full name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="doctor" class="form-label form-required">Requesting Doctor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-md"></i></span>
                                <input type="text" class="form-control" id="doctor" name="doctor" required
                                       placeholder="Enter doctor's name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test Details Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-flask"></i> Test Details
                    </h3>
                    <div class="form-group">
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
                    
                    <div class="form-group">
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
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Clinical Notes & Instructions</label>
                        <textarea class="form-control" id="notes" name="notes" 
                                 placeholder="Enter any relevant clinical notes, symptoms, or special instructions..."></textarea>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Form validation and submission
        document.getElementById('labRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Laboratory request submitted successfully!');
            // Add your form submission logic here
        });
        
        // Add any additional JavaScript functionality here
    </script>
</body>
</html>
