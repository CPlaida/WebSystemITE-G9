<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Role Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
