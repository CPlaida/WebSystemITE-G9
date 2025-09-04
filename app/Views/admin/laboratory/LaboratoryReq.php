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
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --border-radius-sm: 4px;
            --border-radius: 6px;
            --border-radius-lg: 8px;
            --transition: all 0.2s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fb;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        
        .header {
            background: var(--primary-color);
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
            gap: 8px;
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
            padding: 0 2rem 2rem;
        }
        
        .form-section {
            background: var(--white);
            border-radius: var(--border-radius);
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
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
        
        .form-control, .form-select {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--white);
            color: var(--text-color);
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .input-group {
            display: flex;
            width: 100%;
            position: relative;
        }
        
        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0 1rem;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            color: #6c757d;
        }
        
        .input-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            font-size: 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                border-radius: 0;
                box-shadow: none;
            }
            
            .header {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .page-title {
                position: static;
                order: -1;
                width: 100%;
            }
            
            .back-btn {
                align-self: flex-start;
            }
            
            .form-container {
                padding: 0 1rem 1.5rem;
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
            <a href="<?= base_url('dashboard') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="page-title">Laboratory Request</h1>
        </div>
        
        <div class="form-container">
            <form id="labRequestForm">
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
                                       placeholder="Doctor's name">
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
                        <select class="form-select" id="testType" name="testType" required>
                            <option value="">Select a test type</option>
                            <option value="blood">Blood Test</option>
                            <option value="urine">Urine Analysis</option>
                            <option value="xray">X-Ray</option>
                            <option value="mri">MRI Scan</option>
                            <option value="ct">CT Scan</option>
                            <option value="ultrasound">Ultrasound</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="testNotes" class="form-label">Clinical Notes</label>
                        <textarea class="form-control" id="testNotes" name="testNotes" 
                                 placeholder="Enter any specific instructions or clinical notes"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label form-required">Priority</label>
                        <div class="form-grid">
                            <label class="radio-label">
                                <input type="radio" name="priority" value="routine" checked> Routine
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="priority" value="urgent"> Urgent
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="priority" value="stat"> STAT
                            </label>
                        </div>
                    </div>
                </div>
                
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
            // Add your form submission logic here
            alert('Form submitted successfully!');
        });
        
        // Add any additional JavaScript functionality here
    </script>
</body>
</html>
