<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>User Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <div class="header">
      <h1 class="page-title">User Management</h1>
    </div>
    <?php 
      $flashSuccess = session()->getFlashdata('success') ?? ''; 
      $flashError = session()->getFlashdata('error') ?? ''; 
      $hasFlash = !empty($flashSuccess) || !empty($flashError);
      $isSuccess = !empty($flashSuccess);
      $flashMsg = $flashSuccess !== '' ? $flashSuccess : $flashError;
    ?>
    <div id="flashBackdrop" class="flash-backdrop" style="display: <?= $hasFlash ? 'block' : 'none' ?>;"></div>
    <div id="flashModal" class="flash-modal" role="dialog" aria-modal="true" aria-labelledby="flashTitle" style="display: <?= $hasFlash ? 'flex' : 'none' ?>;">
      <div class="flash-card">
        <div id="flashIcon" class="flash-icon <?= $isSuccess ? 'flash-success' : 'flash-error' ?>"><?php echo $isSuccess ? '✓' : '✕'; ?></div>
        <div id="flashTitle" class="flash-title"><?php echo $isSuccess ? 'Success!' : 'Error'; ?></div>
        <div id="flashMessage" class="flash-text"><?php echo esc($flashMsg); ?></div>
        <button type="button" class="btn btn-primary" onclick="closeFlashModal()">OK</button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="card-container">
      <div class="card">
        <h3>Total Users</h3>
        <div class="value" id="totalUsers"><?= esc($stats['total'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Doctors</h3>
        <div class="value"><?= esc($stats['doctors'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Nurses</h3>
        <div class="value"><?= esc($stats['nurses'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Active Users</h3>
        <div class="value"><?= esc($stats['active'] ?? 0) ?></div>
      </div>
    </div>

    <!-- Search and Filter Section -->
    <div style="margin: 1.5rem 0;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <!-- Search Input -->
        <div style="flex: 1; min-width: 250px;">
          <div class="unified-search-wrapper">
            <div class="unified-search-row">
              <i class="fas fa-search unified-search-icon"></i>
              <input 
                type="text" 
                id="searchInput" 
                class="unified-search-field"
                placeholder="Search by name, email, or department..."
                onkeyup="filterTable()"
              >
            </div>
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
          onclick="openModal('add')" 
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
                  <div class="action-group">
                    <button type="button" class="btn-action btn-edit"
                            data-id="<?= (int)($u['id'] ?? 0) ?>"
                            data-username="<?= esc($u['username'] ?? '', 'attr') ?>"
                            data-email="<?= esc($u['email'] ?? '', 'attr') ?>"
                            data-roleid="<?= (int)($u['role_id'] ?? 0) ?>"
                            data-status="<?= esc(strtolower($u['status'] ?? 'active'), 'attr') ?>"
                            onclick="openEdit(this)">Edit</button>
                    <form method="post" action="<?= base_url('admin/users/delete/' . ($u['id'] ?? 0)) ?>" onsubmit="return confirm('Delete this user?');">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn-action btn-delete">Delete</button>
                    </form>
                  </div>
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
      <h3 id="userModalTitle">Add New User</h3>
      <form id="userForm" method="post" action="<?= base_url('admin/users/create') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div style="grid-column: 1 / -1;">
            <label for="staffSearch">Link Staff Profile (optional)</label>
            <div style="display:flex; gap:0.5rem; align-items:center;">
              <input
                type="text"
                id="staffSearch"
                list="staffOptionsList"
                placeholder="Type staff name or employee # to search..."
                style="flex:1;"
                autocomplete="off"
              >
              <button type="button" class="btn btn-secondary btn-sm" onclick="clearStaffSelection()">Clear</button>
            </div>
            <datalist id="staffOptionsList">
              <?php if (!empty($staffOptions)): ?>
                <?php foreach ($staffOptions as $staff): ?>
                  <option value="<?= esc($staff['display_label'] ?? $staff['full_name'], 'attr') ?>"></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </datalist>
            <input type="hidden" id="staff_id" name="staff_id" value="">
            <small class="text-muted">Selecting a staff profile will auto-fill credentials and lock the corresponding role.</small>
          </div>

          <div id="staffLinkedInfo" class="alert-info" style="display:none; grid-column: 1 / -1; border:1px solid #bee3f8; background:#ebf8ff; padding:0.75rem; border-radius:6px;">
            <strong>Linked Staff:</strong> <span id="staffInfoName"></span><br>
            <small id="staffInfoMeta"></small>
          </div>

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
            <select id="role_id" name="role_id" required onchange="handleUserRoleChange()">
              <option value="">Select Role</option>
              <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= (int)$r['id'] ?>" data-scope="<?= esc(strtolower($r['name']), 'attr') ?>"><?= esc($r['name']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div id="doctorSpecializationField" style="display:none;">
            <label>Specialization</label>
            <input type="text" id="doctorSpecialization" readonly>
          </div>
          <div id="nurseDepartmentField" style="display:none;">
            <label>Department</label>
            <input type="text" id="nurseDepartment" readonly>
          </div>
          <div>
            <label for="password">Password</label>
            <div class="input-group">
              <input type="text" id="password" name="password" required style="flex:1;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="generatePassword()">Generate</button>
            </div>
            <small id="passwordHelp" style="color:#6c757d;"></small>
          </div>
          <div>
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-close" onclick="closeModal()">Cancel</button>
          <button type="submit" id="userFormSubmit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("userModal");
    const STAFF_OPTIONS = <?= json_encode($staffOptions ?? []) ?>;
    const PREFILL_STAFF = <?= json_encode($prefillStaff ?? null) ?>;

    function openModal(mode) {
      const form = document.getElementById('userForm');
      const title = document.getElementById('userModalTitle');
      const password = document.getElementById('password');
      const username = document.getElementById('username');
      const email = document.getElementById('email');
      const role = document.getElementById('role_id');
      const status = document.getElementById('status');
      const help = document.getElementById('passwordHelp');
      title.textContent = 'Add New User';
      form.action = '<?= base_url('admin/users/create') ?>';
      username.value = '';
      email.value = '';
      role.value = '';
      status.value = 'active';
      password.type = 'password';
      password.value = '';
      password.required = true;
      password.placeholder = '';
      help.textContent = '';
      document.getElementById('userFormSubmit').textContent = 'Create User';
      clearStaffSelection();
      modal.style.display = 'flex';
    }
    function openEdit(el) {
  const form = document.getElementById('userForm');
  const title = document.getElementById('userModalTitle');
  const password = document.getElementById('password');
  const username = document.getElementById('username');
  const email = document.getElementById('email');
  const role = document.getElementById('role_id');
  const status = document.getElementById('status');
  const help = document.getElementById('passwordHelp');

  const id = el.getAttribute('data-id');
  title.textContent = 'Edit User';
  form.action = '<?= base_url('admin/users/update') ?>' + '/' + id;
  username.value = el.getAttribute('data-username') || '';
  email.value = el.getAttribute('data-email') || '';
  role.value = el.getAttribute('data-roleid') || '';
  status.value = el.getAttribute('data-status') || 'active';

  // password field is visible text now
  password.type = 'text';
  password.value = '';
  password.required = false;
  password.placeholder = 'Leave blank to keep current password';
  help.textContent = 'Leave blank to keep current password';

  document.getElementById('userFormSubmit').textContent = 'Update User';
  clearStaffSelection();
  document.getElementById('userModal').style.display = 'flex';
}
    function closeModal() { modal.style.display = 'none'; }

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
        
        // Check status filter (Status column is the 4th column -> index 3)
        if (!matchesStatus && cells.length > 3) {
          const statusText = (cells[3].textContent || cells[3].innerText || '').trim().toLowerCase();
          // exact match only (avoid 'inactive' matching 'active')
          matchesStatus = statusFilter === '' ? true : (statusText === statusFilter);
        }
        
        // Show/hide row based on all filters
        row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
      }
    }

    function generatePassword(){
      const input = document.getElementById('password');
      const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
      let pwd = '';
      for(let i=0;i<12;i++){ pwd += chars[Math.floor(Math.random()*chars.length)]; }
      input.value = pwd;
      input.type = 'text';
    }
    const staffSearchInput = document.getElementById('staffSearch');
    const staffIdInput = document.getElementById('staff_id');
    staffSearchInput?.addEventListener('change', handleStaffSearch);

    function handleStaffSearch() {
      const value = staffSearchInput.value.trim();
      if (!value) {
        clearStaffSelection();
        return;
      }
      const match = STAFF_OPTIONS.find(option => option.display_label === value || option.full_name === value);
      if (match) {
        applyStaffSelection(match);
      }
    }

    function applyStaffSelection(staff) {
      staffIdInput.value = staff.id;
      document.getElementById('username').value = staff.username_suggestion || '';
      if (staff.staff_email) {
        document.getElementById('email').value = staff.staff_email;
      }
      const roleSelect = document.getElementById('role_id');
      if (staff.role_id) {
        roleSelect.value = staff.role_id;
        roleSelect.dataset.locked = '1';
        roleSelect.dataset.lockedValue = staff.role_id;
      }
      document.getElementById('staffLinkedInfo').style.display = 'block';
      document.getElementById('staffInfoName').textContent = staff.full_name;
      const meta = [];
      if (staff.department_name) meta.push('Department: ' + staff.department_name);
      if (staff.specialization_name) meta.push('Specialization: ' + staff.specialization_name);
      document.getElementById('staffInfoMeta').textContent = meta.join(' • ');
      if (staff.display_label) {
        staffSearchInput.value = staff.display_label;
      }
      updateRoleSpecificFields(roleSelect.value, staff);
    }

    function clearStaffSelection() {
      staffIdInput.value = '';
      if (staffSearchInput) {
        staffSearchInput.value = '';
      }
      const roleSelect = document.getElementById('role_id');
      delete roleSelect.dataset.locked;
      delete roleSelect.dataset.lockedValue;
      document.getElementById('staffLinkedInfo').style.display = 'none';
      updateRoleSpecificFields('', null);
    }

    function handleUserRoleChange() {
      const roleSelect = document.getElementById('role_id');
      if (roleSelect.dataset.locked === '1') {
        roleSelect.value = roleSelect.dataset.lockedValue || roleSelect.value;
        return;
      }
      updateRoleSpecificFields(roleSelect.value, null);
    }

    function updateRoleSpecificFields(roleId, staff) {
      const doctorField = document.getElementById('doctorSpecializationField');
      const nurseField = document.getElementById('nurseDepartmentField');
      doctorField.style.display = 'none';
      nurseField.style.display = 'none';
      if (!roleId) {
        return;
      }
      const roleName = getRoleNameById(roleId);
      if (roleName.includes('doctor')) {
        doctorField.style.display = 'block';
        document.getElementById('doctorSpecialization').value = staff?.specialization_name || 'Linked specialization will appear here.';
      } else if (roleName.includes('nurse')) {
        nurseField.style.display = 'block';
        document.getElementById('nurseDepartment').value = staff?.department_name || 'Linked department will appear here.';
      }
    }

    function getRoleNameById(roleId) {
      const option = document.querySelector(`#role_id option[value="${roleId}"]`);
      return (option?.textContent || '').toLowerCase();
    }

    document.addEventListener('DOMContentLoaded', () => {
      if (PREFILL_STAFF) {
        openModal('add');
        applyStaffSelection(PREFILL_STAFF);
      }
    });
    window.onclick = function(e) { 
      if (e.target == modal) closeModal();
      if (e.target == document.getElementById('flashBackdrop')) closeFlashModal();
      if (e.target == document.getElementById('flashModal')) closeFlashModal();
    }

    function closeFlashModal(){
      document.getElementById('flashBackdrop').style.display = 'none';
      document.getElementById('flashModal').style.display = 'none';
    }
  </script>
<?= $this->endSection() ?>
