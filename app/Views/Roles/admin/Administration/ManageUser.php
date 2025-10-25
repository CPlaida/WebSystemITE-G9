<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>User Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">User Management</h1>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card"><h3>Total Users</h3><p id="totalUsers">3</p></div>
      <div class="stat-card"><h3>Doctors</h3><p>156</p></div>
      <div class="stat-card"><h3>Nurses</h3><p>324</p></div>
      <div class="stat-card"><h3>Active Today</h3><p>189</p></div>
    </div>

    <!-- Search and Filter Section -->
    <div style="margin: 1.5rem 0;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <!-- Search Input -->
        <div style="flex: 1; min-width: 250px;">
          <div style="position: relative;">
            <input 
              type="text" 
              id="searchInput" 
              placeholder="Search by name, email, or department..." 
              style="
                width: 100%;
                padding: 0.6rem 1rem 0.6rem 2.5rem;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                font-size: 0.95rem;
                transition: all 0.2s ease;
              "
              onkeyup="filterTable()"
            >
            <i class="fas fa-search" style="
              position: absolute;
              left: 1rem;
              top: 50%;
              transform: translateY(-50%);
              color: #6c757d;
              font-size: 0.9rem;
            "></i>
          </div>
        </div>
        
        <!-- Role Filter -->
        <div style="min-width: 180px;">
          <select 
            id="roleFilter" 
            onchange="filterTable()"
            style="
              width: 100%;
              padding: 0.6rem 1rem;
              border: 1px solid #dee2e6;
              border-radius: 6px;
              font-size: 0.95rem;
              background-color: white;
              cursor: pointer;
            "
          >
            <option value="">All Roles</option>
            <option value="Doctor">Doctor</option>
            <option value="Nurse">Nurse</option>
            <option value="Receptionist">Receptionist</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
        
        <!-- Status Filter -->
        <div style="min-width: 150px;">
          <select 
            id="statusFilter" 
            onchange="filterTable()"
            style="
              width: 100%;
              padding: 0.6rem 1rem;
              border: 1px solid #dee2e6;
              border-radius: 6px;
              font-size: 0.95rem;
              background-color: white;
              cursor: pointer;
            "
          >
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        
        <!-- Add New User Button -->
        <button 
          onclick="openModal()" 
          style="
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background-color: #4361ee;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            height: 42px;
          "
          onmouseover="this.style.backgroundColor='#3a56d4'"
          onmouseout="this.style.backgroundColor='#4361ee'"
        >
          <i class="fas fa-plus" style="margin-right: 8px;"></i>
          Add New User
        </button>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Branch</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="userTable">
          <tr>
            <td>Dr. Maria Santos</td>
            <td>maria.s@example.com</td>
            <td>Doctor</td>
            <td>Cardiology</td>
            <td>Main</td>
            <td><span class="status-badge status-active">Active</span></td>
          </tr>
          <tr>
            <td>John Doe</td>
            <td>john.d@example.com</td>
            <td>Nurse</td>
            <td>Emergency</td>
            <td>Main</td>
            <td><span class="status-badge status-active">Active</span></td>
          </tr>
          <tr>
            <td>Sarah Johnson</td>
            <td>sarah.j@example.com</td>
            <td>Receptionist</td>
            <td>Front Desk</td>
            <td>Branch clinic 1</td>
            <td><span class="status-badge status-inactive">Suspended</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  <!-- Modal -->
  <div class="modal" id="userModal">
    <div class="modal-content">
      <h3>Add New User</h3>
      <form id="userForm">
        <div class="form-grid">
          <div>
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" required>
          </div>
          <div>
            <label for="email">Email Address</label>
            <input type="email" id="email" required>
          </div>
          <div>
            <label for="role">User Role</label>
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
            <label for="department">Department</label>
            <select id="department" required>
              <option value="">Select Department</option>
              <option>Administration</option>
              <option>Pediatrics</option>
              <option>Emergency medicine</option>
              <option>Internal medicine</option>
              <option>surgery</option>
              <option>Finance</option>
              <option>Laboratory</option>
              <option>Pharmacy</option>
              <option>IT Depatment</option>
            </select>
          </div>
          <div>
            <label for="branch">Branch</label>
            <select id="branch" required>
              <option>Main</option>
              <option>Branch Clinic 1</option>
              <option>Branch Clinic 2</option>
              <option>Upcoming branch</option>
            </select>
          </div>
          <div>
            <label for="status">Status</label>
            <select id="status" required>
              <option>Active</option>
              <option>Inactive</option>
              <option>Suspended</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-close" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("userModal");
    const totalUsers = document.getElementById("totalUsers");

    function openModal() { modal.style.display = "flex"; }
    function closeModal() { modal.style.display = "none"; }

    document.getElementById("userForm").addEventListener("submit", function(e) {
      e.preventDefault();

      const name = document.getElementById("fullName").value;
      const email = document.getElementById("email").value;
      const role = document.getElementById("role").value;
      const dept = document.getElementById("department").value;
      const branch = document.getElementById("branch").value;
      const status = document.getElementById("status").value;

      // add row
      const table = document.getElementById("userTable");
      const row = table.insertRow();
      row.innerHTML = `
        <td>${name}</td>
        <td>${email}</td>
        <td>${role}</td>
        <td>${dept}</td>
        <td>${branch}</td>
        <td><span class="status-badge ${status === 'Active' ? 'status-active' : 'status-inactive'}">${status}</span></td>
      `;

      // update stats
      totalUsers.textContent = parseInt(totalUsers.textContent) + 1;

      closeModal();
      document.getElementById("userForm").reset();
    });

    function filterTable() {
      const searchInput = document.getElementById('searchInput').value.toLowerCase();
      const roleFilter = document.getElementById('roleFilter').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
      const table = document.querySelector('.data-table tbody');
      const rows = table.getElementsByTagName('tr');

      for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let matchesSearch = searchInput === '';
        let matchesRole = roleFilter === '';
        let matchesStatus = statusFilter === '';
        
        // Check each cell for search term
        if (!matchesSearch) {
          for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
              const text = cell.textContent || cell.innerText;
              if (text.toLowerCase().includes(searchInput)) {
                matchesSearch = true;
                break;
              }
            }
          }
        }
        
        // Check role filter (assuming role is in the 3rd column, index 2)
        if (!matchesRole && cells.length > 2) {
          const role = cells[2].textContent || cells[2].innerText;
          matchesRole = role.toLowerCase() === roleFilter.toLowerCase();
        }
        
        // Check status filter (assuming status is in the last column)
        if (!matchesStatus && cells.length > 0) {
          const statusCell = cells[cells.length - 1];
          const status = statusCell.textContent || statusCell.innerText;
          matchesStatus = status.toLowerCase().includes(statusFilter.toLowerCase());
        }
        
        // Show/hide row based on all filters
        row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
      }
    }

    window.onclick = function(e) { if (e.target == modal) closeModal(); }
  </script>
<?= $this->endSection() ?>
