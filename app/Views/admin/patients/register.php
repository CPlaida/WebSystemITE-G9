<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Patient - HMS</title>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 900px;
      margin: auto;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      border: 1px solid #ddd;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .card-header {
      background: #f8f9fa;
      border-bottom: 1px solid #e0e0e0;
      padding: 15px 20px;
    }

    .card-header h5 {
      margin: 0;
      font-size: 18px;
      color: #0d6efd;
    }

    .card-header p {
      margin: 5px 0 0;
      font-size: 14px;
      color: #6c757d;
    }

    .card-body {
      padding: 20px;
    }

    .section-title {
      font-size: 16px;
      font-weight: bold;
      color: #0d6efd;
      margin-bottom: 10px;
      border-bottom: 1px solid #eee;
      padding-bottom: 5px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .required-field::after {
      content: " *";
      color: red;
    }

    input, select {
      width: 100%;
      border: 1px solid #ced4da;
      border-radius: 5px;
      padding: 10px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
    }

    input:focus, select:focus {
      border-color: #0d6efd;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
    }

    .col-6 {
      flex: 1 1 calc(50% - 15px);
    }

    .col-12 {
      flex: 1 1 100%;
    }

    .actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid #ddd;
      margin-top: 20px;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: 0.2s;
    }

    .btn-primary {
      background: #0d6efd;
      color: #fff;
    }

    .btn-outline {
      background: #fff;
      border: 1px solid #ccc;
      color: #6c757d;
    }

    .btn-outline:hover {
      background: #f1f1f1;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <h5>Add Patient Details</h5>
        <p>Please fill in all required fields</p>
      </div>
      <div class="card-body">
        <form id="patientForm" novalidate>
          <!-- Personal Information -->
          <div class="section-title">Personal Information</div>
          <div class="row">
            <div class="col-6 form-group">
              <label class="required-field">First Name</label>
              <input type="text" placeholder="Patient's First Name" required>
            </div>
            <div class="col-6 form-group">
              <label class="required-field">Last Name</label>
              <input type="text" placeholder="Patient's Last Name" required>
            </div>
            <div class="col-6 form-group">
              <label class="required-field">Date of Birth</label>
              <input type="date" required>
            </div>
            <div class="col-6 form-group">
              <label class="required-field">Gender</label>
              <select required>
                <option value="">Select Gender</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="section-title">Contact Information</div>
          <div class="row">
            <div class="col-12 form-group">
              <label>Complete Address</label>
              <input type="text" placeholder="House No., Street, Barangay, City/Municipality">
            </div>
            <div class="col-6 form-group">
              <label class="required-field">Mobile Number</label>
              <input type="tel" placeholder="912 345 6789" required>
            </div>
            <div class="col-6 form-group">
              <label>Email Address</label>
              <input type="email" placeholder="patient@example.com">
            </div>
          </div>

          <!-- Medical Information -->
          <div class="section-title">Medical Information</div>
          <div class="row">
            <div class="col-6 form-group">
              <label class="required-field">Patient Type</label>
              <select required>
                <option value="">Select Type</option>
                <option>Inpatient</option>
                <option>Outpatient</option>
              </select>
            </div>
            <div class="col-6 form-group">
              <label>Patient Alignment</label>
              <input type="text" placeholder="e.g., Left, Right, Center">
            </div>
            <div class="col-12 form-group">
              <label>Known Allergies</label>
              <input type="text" placeholder="List any allergies (separate with commas)">
            </div>
          </div>

          <!-- Form Actions -->
          <div class="actions">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline">Back to Dashboard</a>
            <div>
              <button type="reset" class="btn btn-outline">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Patient</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Simple validation
    document.getElementById('patientForm').addEventListener('submit', function(e){
      if(!this.checkValidity()){
        e.preventDefault();
        alert("Please fill in all required fields.");
      }
    });
  </script>
</body>
</html>
