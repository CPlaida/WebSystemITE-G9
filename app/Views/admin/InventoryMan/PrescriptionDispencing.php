<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Dispensing - St. Peter Hospital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f6f8fb;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .breadcrumb {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }

        .card {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .card-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.2em;
        }

        .card-body {
            padding: 20px;
        }

        .prescription-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
            padding: 9px 19px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-secondary:hover {
            background-color: #f8f9fa;
            color: #5a6268;
            border-color: #5a6268;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Prescription Dispensing</h1>
            <div class="breadcrumb">
                Prescription Dispensing
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Dispense Medication</h3>
            </div>
            <div class="card-body">
                <form class="prescription-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient">Patient Name</label>
                            <input type="text" id="patient" placeholder="Enter Patient Name" required>
                        </div>

                        <div class="form-group">
                            <label for="medication">Medication</label>
                            <input list="medications" id="medication" name="medication" 
                                   placeholder="Type or select medication" required>
                            <datalist id="medications">
                                <option value="Paracetamol">
                                <option value="Amoxicillin">
                                <option value="Ibuprofen">
                                <option value="Cetirizine">
                                <option value="Metformin">
                                <option value="Omeprazole">
                                <option value="Losartan">
                                <option value="Amlodipine">
                                <option value="Aspirin">
                                <option value="Atorvastatin">
                                <option value="Salbutamol">
                                <option value="Hydrochlorothiazide">
                                <option value="Clopidogrel">
                                <option value="Azithromycin">
                                <option value="Ciprofloxacin">
                                <option value="Doxycycline">
                                <option value="Prednisone">
                                <option value="Insulin">
                                <option value="Lisinopril">
                                <option value="Furosemide">
                                <option value="Warfarin">
                                <option value="Morphine">
                                <option value="Tramadol">
                                <option value="Acetaminophen + Codeine">
                                <option value="Vitamin B Complex">
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" placeholder="Enter Quantity" required>
                        </div>

                        <div class="form-group">
                            <label for="instructions">Instructions</label>
                            <input type="text" id="instructions" placeholder="Enter Instructions" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= base_url('admin/dashboard') ?>" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-pills"></i> Dispense Medication
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
