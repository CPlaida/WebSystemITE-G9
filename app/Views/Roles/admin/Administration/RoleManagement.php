<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Role Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --light: #f8f9fa;
      --dark: #343a40;
      --border-radius: 8px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
      background: #fff;
      padding: 20px;
      margin: 2rem auto;
      width: 90%;
      max-width: 800px;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }

    h2 {
      font-size: 1.3rem;
      font-weight: bold;
      margin-bottom: 1.2rem;
      color: var(--dark);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      font-size: 0.9rem;
      color: #333;
    }

    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background: #f8f8f8;
      font-size: 0.9rem;
    }

    .form-actions {
      margin-top: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .btn {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-outline {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      color: #333;
      text-decoration: none;
    }

    .btn-outline:hover {
      background: #e9ecef;
      border-color: #ced4da;
      transform: translateY(-1px);
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
      transform: translateY(-1px);
    }
  </style>

  <div class="container">
    <h2>Role Management</h2>
    <form id="roleForm">
      <div class="form-grid">
        <div>
          <label for="role">Select Role</label>
          <select id="role" required>
            <option value="">Select Role</option>
            <option>Doctor</option>
            <option>Nurse</option>
            <option>Receptionist</option>
            <option>Lab Staff</option>
            <option>Pharmacist</option>
            <option>Accountant</option>
            <option>IT Staff</option>
            <option>Hospital Administration</option>
          </select>
        </div>
        <div>
          <label for="module">Module Access</label>
          <select id="module" required>
            <option value="">Select Module</option>
            <option>Patient Records</option>
            <option>Billing</option>
            <option>Inventory</option>
            <option>Reports</option>
          </select>
        </div>
        <div>
          <label for="data">Data Access Level</label>
          <select id="data" required>
            <option value="">Select Access Level</option>
            <option>Full Access</option>
            <option>Read Only</option>
            <option>Limited Access</option>
            <option>No Access</option>
          </select>
        </div>
        <div>
          <label for="branch">Branch Access</label>
          <select id="branch" required>
            <option value="">Select Branch</option>
            <option>Main</option>
              <option>Branch Clinic 1</option>
              <option>Branch Clinic 2</option>
              <option>Upcoming branch</option>

          </select>
        </div>
      </div>
      <div class="form-actions">
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save" style="margin-right: 8px;"></i>
          Update Permission
        </button>
      </div>
    </form>
  </div>

  <script>
    document.getElementById("roleForm").addEventListener("submit", function(e) {
      e.preventDefault();
      alert("Permissions updated successfully!");
    });
  </script>
<?= $this->endSection() ?>
