<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>User Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">User Management</h1>
    </div>
    <style>
      .user-table-scroll { max-height: 420px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; }
      .data-table thead th { position: sticky; top: 0; background: #ffffff; color: #495057; z-index: 2; border-bottom: 2px solid #dee2e6; }
    </style>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success" role="alert" style="margin-bottom:1rem;">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger" role="alert" style="margin-bottom:1rem;">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card"><h3>Total Users</h3><p id="totalUsers"><?= esc($stats['total'] ?? 0) ?></p></div>
      <div class="stat-card"><h3>Doctors</h3><p><?= esc($stats['doctors'] ?? 0) ?></p></div>
      <div class="stat-card"><h3>Nurses</h3><p><?= esc($stats['nurses'] ?? 0) ?></p></div>
      <div class="stat-card"><h3>Active Users</h3><p><?= esc($stats['active'] ?? 0) ?></p></div>
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
    <div class="table-responsive user-table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th style="width:150px;">Actions</th>
          </tr>
        </thead>
        <tbody id="userTable">
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?= esc($u['username'] ?? '') ?></td>
                <td><?= esc($u['email'] ?? '') ?></td>
                <td><?= esc($u['role_name'] ?? '-') ?></td>
                <td>
                  <?php $st = strtolower($u['status'] ?? 'active'); ?>
                  <span class="status-badge <?= $st === 'active' ? 'status-active' : 'status-inactive' ?>"><?= esc(ucfirst($st)) ?></span>
                </td>
                <td>
                  <form method="post" action="<?= base_url('admin/users/delete/' . ($u['id'] ?? 0)) ?>" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" style="padding:6px 10px;">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <!-- Modal -->
  <div class="modal" id="userModal">
    <div class="modal-content">
      <h3>Add New User</h3>
      <form id="userForm" method="post" action="<?= base_url('admin/users/create') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
          </div>
          <div>
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
          </div>
          <div>
            <label for="role_id">User Role</label>
            <select id="role_id" name="role_id" required>
              <option value="">Select Role</option>
              <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= (int)$r['id'] ?>"><?= esc($r['name']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
          </div>
          <div>
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
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

    // Form posts to server; keep modal close on submit to feel responsive
    document.getElementById("userForm").addEventListener("submit", function() {
      setTimeout(() => { closeModal(); }, 0);
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
