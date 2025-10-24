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
      padding: 24px 24px 20px;
      margin: 1rem 0;
      width: 100%;
      max-width: none;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }

    h2 {
      font-size: 1.3rem;
      font-weight: bold;
      margin-bottom: 1.2rem;
      color: var(white);
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 20px 24px;
      align-items: start;
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
      margin-top: 20px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      padding-top: 16px;
      border-top: 1px solid #eee;
      gap: 12px;
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

    @media (max-width: 992px) {
      .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .container {
        padding: 20px 16px;
      }
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
        <button type="submit" class="btn btn-primary">Update Permission</button>
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
