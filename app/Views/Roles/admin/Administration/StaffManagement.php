<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Staff Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container">
    <div class="header">
      <h1 class="page-title">Staff Management</h1>
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
        <div id="flashIcon" class="flash-icon <?= $isSuccess ? 'flash-success' : 'flash-error' ?>"><?= $isSuccess ? '✓' : '✕' ?></div>
        <div id="flashTitle" class="flash-title"><?= $isSuccess ? 'Success!' : 'Error' ?></div>
        <div id="flashMessage" class="flash-text"><?= esc($flashMsg) ?></div>
        <button type="button" class="btn btn-primary" onclick="closeFlashModal()">OK</button>
      </div>
    </div>

    <div class="card-container">
      <div class="card">
        <h3>Total Staff</h3>
        <div class="value"><?= esc($stats['total'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Active</h3>
        <div class="value"><?= esc($stats['active'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>On Leave</h3>
        <div class="value"><?= esc($stats['on_leave'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Linked Accounts</h3>
        <div class="value"><?= esc($stats['with_accounts'] ?? 0) ?></div>
      </div>
    </div>

    <div style="margin: 1.5rem 0;">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div style="flex: 1; min-width: 250px;">
          <div class="unified-search-wrapper">
            <div class="unified-search-row">
              <i class="fas fa-search unified-search-icon"></i>
              <input
                type="text"
                id="searchStaffInput"
                class="unified-search-field"
                placeholder="Search by name, department, or specialization..."
                onkeyup="filterStaffTable()"
              >
            </div>
          </div>
        </div>
        <div style="min-width: 200px;">
          <select id="departmentFilter" onchange="filterStaffTable()" class="staff-filter-select">
            <option value="">All Departments</option>
            <?php if (!empty($departments)): ?>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= esc($dept['id'] ?? '', 'attr') ?>"><?= esc($dept['name'] ?? '') ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div style="min-width: 180px;">
          <select id="roleFilter" onchange="filterStaffTable()" class="staff-filter-select">
            <option value="">All Roles</option>
            <?php if (!empty($roles)): ?>
              <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id'] ?? '', 'attr') ?>"><?= esc($role['name'] ?? '') ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div style="min-width: 160px;">
          <select id="statusFilter" onchange="filterStaffTable()" class="staff-filter-select">
            <option value="">All Status</option>
            <?php if (!empty($statuses)): ?>
              <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <button onclick="openStaffModal('add')" class="btn btn-primary" style="height: 42px; display: flex; align-items:center; gap: 0.5rem;">
          <i class="fas fa-user-plus"></i>
          Add Staff Profile
        </button>
      </div>
    </div>

    <div class="table-responsive user-table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Specialization</th>
            <th>Role</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Linked User</th>
            <th style="width:200px;">Actions</th>
          </tr>
        </thead>
        <tbody id="staffTable">
          <?php if (!empty($staff)): ?>
            <?php foreach ($staff as $member): ?>
              <tr
                data-department-id="<?= esc($member['department_id'] ?? '', 'attr') ?>"
                data-role-id="<?= esc($member['role_id'] ?? '', 'attr') ?>"
              >
                <td>
                  <strong><?= esc(trim(($member['last_name'] ?? '') . ', ' . ($member['first_name'] ?? ''))) ?></strong>
                </td>
                <td><?= esc($member['department_name'] ?? '-') ?></td>
                <td><?= esc($member['specialization_name'] ?? '-') ?></td>
                <td><?= esc($member['staff_role_name'] ?? '-') ?></td>
                <td>
                  <div><?= esc($member['phone'] ?? 'N/A') ?></div>
                  <small><?= esc($member['email'] ?? 'No email') ?></small>
                </td>
                <td>
                  <?php $staffStatus = strtolower($member['status'] ?? 'active'); ?>
                  <span class="status-badge <?= $staffStatus === 'active' ? 'status-active' : ($staffStatus === 'on_leave' ? 'status-warning' : 'status-inactive') ?>">
                    <?= esc(ucwords(str_replace('_', ' ', $staffStatus))) ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($member['user_username'])): ?>
                    <div><?= esc($member['user_username']) ?></div>
                    <small><?= esc($member['user_role_name'] ?? 'N/A') ?></small>
                  <?php else: ?>
                    <span class="status-badge status-inactive">Not linked</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="action-group">
                    <button type="button" class="btn-action btn-view" onclick="openStaffView(this)"
                      data-name="<?= esc(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')), 'attr') ?>"
                      data-role="<?= esc($member['staff_role_name'] ?? '-', 'attr') ?>"
                      data-department="<?= esc($member['department_name'] ?? '-', 'attr') ?>"
                      data-specialization="<?= esc($member['specialization_name'] ?? '-', 'attr') ?>"
                      data-phone="<?= esc($member['phone'] ?? 'N/A', 'attr') ?>"
                      data-email="<?= esc($member['email'] ?? 'N/A', 'attr') ?>"
                      data-status="<?= esc(ucwords(str_replace('_', ' ', $member['status'] ?? 'active')), 'attr') ?>"
                      data-hire_date="<?= esc($member['hire_date'] ?? 'N/A', 'attr') ?>"
                      data-address="<?= esc($member['address'] ?? 'Not provided', 'attr') ?>"
                    >View</button>
                    <button type="button" class="btn-action btn-edit" onclick="openStaffEdit(this)"
                      data-id="<?= (int)($member['id'] ?? 0) ?>"
                      data-first_name="<?= esc($member['first_name'] ?? '', 'attr') ?>"
                      data-middle_name="<?= esc($member['middle_name'] ?? '', 'attr') ?>"
                      data-last_name="<?= esc($member['last_name'] ?? '', 'attr') ?>"
                      data-gender="<?= esc($member['gender'] ?? '', 'attr') ?>"
                      data-date_of_birth="<?= esc($member['date_of_birth'] ?? '', 'attr') ?>"
                      data-phone="<?= esc($member['phone'] ?? '', 'attr') ?>"
                      data-staff_email="<?= esc($member['email'] ?? '', 'attr') ?>"
                      data-role_id="<?= esc($member['role_id'] ?? '', 'attr') ?>"
                      data-department_id="<?= esc($member['department_id'] ?? '', 'attr') ?>"
                      data-specialization_id="<?= esc($member['specialization_id'] ?? '', 'attr') ?>"
                      data-address="<?= esc($member['address'] ?? '', 'attr') ?>"
                      data-hire_date="<?= esc($member['hire_date'] ?? '', 'attr') ?>"
                      data-status="<?= esc($member['status'] ?? '', 'attr') ?>"
                      data-emergency_contact_name="<?= esc($member['emergency_contact_name'] ?? '', 'attr') ?>"
                      data-emergency_contact_phone="<?= esc($member['emergency_contact_phone'] ?? '', 'attr') ?>"
                    >Edit</button>
                    <?php if (empty($member['user_id'])): ?>
                      <a href="<?= base_url('admin/Administration/ManageUser?staff_id=' . ($member['id'] ?? 0)) ?>" class="btn-action btn-secondary">Create Account</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No staff profiles found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal" id="staffModal">
    <div class="modal-content" style="max-width:960px;">
      <form id="staffForm" method="post" action="<?= base_url('admin/staff/create') ?>" class="card" style="border:none;">
        <?= csrf_field() ?>
        <div class="card-body">
          <h3 id="staffModalTitle" class="page-title" style="margin-bottom:1.25rem;">Add Staff Profile</h3>


          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-user"></i> Personal Information</h3>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-id-badge text-muted"></i></span>
                  <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" required>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="middle_name">Middle Name</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-id-badge text-muted"></i></span>
                  <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Middle Name">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-id-badge text-muted"></i></span>
                  <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" required>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="gender">Gender</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-venus-mars text-muted"></i></span>
                  <select id="gender" name="gender" class="form-select">
                    <option value="">Prefer not to say</option>
                    <?php foreach ($genders as $value => $label): ?>
                      <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="date_of_birth">Date of Birth</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                  <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="phone">Contact Number</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                  <input type="text" id="phone" name="phone" class="form-control" placeholder="09XX XXX XXXX">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="staff_email">Staff Email</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                  <input type="email" id="staff_email" name="staff_email" class="form-control" placeholder="staff@example.com">
                </div>
              </div>
            </div>
          </div>


          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Contact & Address</h3>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="address_province">Province</label>
                <div class="input-group autocomplete-wrapper">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                  <input type="text" id="address_province" name="address_province" class="form-control autocomplete-input" placeholder="Type to search province..." autocomplete="off">
                  <input type="hidden" id="address_province_code">
                  <div class="autocomplete-dropdown" id="addressProvinceDropdown"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="address_city">City / Municipality</label>
                <div class="input-group autocomplete-wrapper">
                  <span class="input-group-text"><i class="fas fa-city text-muted"></i></span>
                  <input type="text" id="address_city" name="address_city" class="form-control autocomplete-input" placeholder="Select province first" autocomplete="off" disabled>
                  <input type="hidden" id="address_city_code">
                  <div class="autocomplete-dropdown" id="addressCityDropdown"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="address_barangay">Barangay</label>
                <div class="input-group autocomplete-wrapper">
                  <span class="input-group-text"><i class="fas fa-home text-muted"></i></span>
                  <input type="text" id="address_barangay" name="address_barangay" class="form-control autocomplete-input" placeholder="Select city/municipality" autocomplete="off" disabled>
                  <input type="hidden" id="address_barangay_code">
                  <div class="autocomplete-dropdown" id="addressBarangayDropdown"></div>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="address_street">House No. / Street</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-road text-muted"></i></span>
                  <input type="text" id="address_street" name="address_street" class="form-control" placeholder="e.g., 23-A Mabini St." autocomplete="off">
                </div>
              </div>
              <div class="form-group" style="flex:1;">
                <label class="form-label" for="address_preview">Full Address Preview</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-map-marked-alt text-muted"></i></span>
                  <input type="text" id="address_preview" class="form-control" placeholder="Full address will appear here" readonly>
                </div>
                <small class="text-muted">We’ll store the combined address automatically.</small>
                <input type="hidden" id="address" name="address">
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="emergency_contact_name">Contact Person</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-user-friends text-muted"></i></span>
                  <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" placeholder="Full name">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="emergency_contact_phone">Contact Number</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-phone-volume text-muted"></i></span>
                  <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" placeholder="09XX XXX XXXX">
                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-briefcase"></i> Assignment & Role</h3>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="role_id">Staff Role <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-user-tag text-muted"></i></span>
                  <select id="role_id" name="role_id" class="form-select" required onchange="handleRoleChange()">
                    <option value="">Select Role</option>
                    <?php if (!empty($roles)): ?>
                      <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role['id'] ?? '', 'attr') ?>" data-scope="<?= esc($role['scope'] ?? 'all', 'attr') ?>"><?= esc($role['name'] ?? '') ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="department_id">Department</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas a-building text-muted"></i></span>
                  <select id="department_id" name="department_id" class="form-select" onchange="handleDepartmentChange()">
                    <option value="">Select Department</option>
                    <?php if (!empty($departments)): ?>
                      <?php foreach ($departments as $dept): ?>
                        <option value="<?= esc($dept['id'] ?? '', 'attr') ?>" data-scope="<?= esc($dept['applicable_to'] ?? 'all', 'attr') ?>"><?= esc($dept['name'] ?? '') ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="specialization_id">Specialization</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-stethoscope text-muted"></i></span>
                  <select id="specialization_id" name="specialization_id" class="form-select">
                    <option value="">Select Specialization</option>
                    <?php if (!empty($specializations)): ?>
                      <?php foreach ($specializations as $spec): ?>
                        <option
                          value="<?= esc($spec['id'] ?? '', 'attr') ?>"
                          data-department="<?= esc($spec['department_id'] ?? '', 'attr') ?>"
                          data-scope="<?= esc($spec['applicable_to'] ?? 'doctor', 'attr') ?>"
                        ><?= esc($spec['name'] ?? '') ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
                <small class="text-muted">Filtered by selected department & role.</small>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="hire_date">Hire Date</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-calendar-day text-muted"></i></span>
                  <input type="date" id="hire_date" name="hire_date" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="status">Employment Status</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-toggle-on text-muted"></i></span>
                  <select id="status" name="status" class="form-select" required>
                    <?php if (!empty($statuses)): ?>
                      <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid #edf2f7; background:#f8fafc;">
          <button type="button" class="btn btn-outline-secondary" onclick="closeStaffModal()">Cancel</button>
          <button type="submit" id="staffFormSubmit" class="btn btn-primary">Save Staff</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="staffViewModal">
    <div class="modal-content" style="max-width:600px;">
      <h3>Staff Details</h3>
      <div class="detail-list">
        <p><strong>Name:</strong> <span id="viewName"></span></p>
        <p><strong>Role:</strong> <span id="viewRole"></span></p>
        <p><strong>Department:</strong> <span id="viewDepartment"></span></p>
        <p><strong>Specialization:</strong> <span id="viewSpecialization"></span></p>
        <p><strong>Contact:</strong> <span id="viewPhone"></span></p>
        <p><strong>Email:</strong> <span id="viewEmail"></span></p>
        <p><strong>Status:</strong> <span id="viewStatus"></span></p>
        <p><strong>Hire Date:</strong> <span id="viewHireDate"></span></p>
        <p><strong>Address:</strong> <span id="viewAddress"></span></p>
      </div>
      <div class="form-actions" style="margin-top:1rem;">
        <button type="button" class="btn btn-primary" onclick="closeStaffView()">Close</button>
      </div>
    </div>
  </div>

  <script>
    const staffModal = document.getElementById('staffModal');
    const staffForm = document.getElementById('staffForm');
    const staffViewModal = document.getElementById('staffViewModal');
    const addressProvinceInput = document.getElementById('address_province');
    const addressCityInput = document.getElementById('address_city');
    const addressBarangayInput = document.getElementById('address_barangay');
    const addressProvinceDropdown = document.getElementById('addressProvinceDropdown');
    const addressCityDropdown = document.getElementById('addressCityDropdown');
    const addressBarangayDropdown = document.getElementById('addressBarangayDropdown');
    const addressProvinceCodeInput = document.getElementById('address_province_code');
    const addressCityCodeInput = document.getElementById('address_city_code');
    const addressBarangayCodeInput = document.getElementById('address_barangay_code');
    const addressLocationApiBase = '<?= base_url('api/locations') ?>';

    let addressProvinceOptions = [];
    let addressCityOptions = [];
    let addressBarangayOptions = [];
    let selectedAddressProvince = null;
    let selectedAddressCity = null;
    let selectedAddressBarangay = null;

    function openStaffModal(mode) {
      staffForm.reset();
      staffForm.action = '<?= base_url('admin/staff/create') ?>';
      document.getElementById('staffModalTitle').textContent = 'Add Staff Profile';
      document.getElementById('staffFormSubmit').textContent = 'Create Staff';
      handleRoleChange();
      handleDepartmentChange();
      resetAddressFields();
      composeAddressFromParts();
      updateStaffSummary();
      staffModal.style.display = 'flex';
    }

    function openStaffEdit(button) {
      const dataset = button.dataset;
      staffForm.reset();
      staffForm.action = '<?= base_url('admin/staff/update') ?>' + '/' + dataset.id;
      document.getElementById('staffModalTitle').textContent = 'Edit Staff Profile';
      document.getElementById('staffFormSubmit').textContent = 'Update Staff';

      const fields = ['first_name','middle_name','last_name','gender','date_of_birth','phone','staff_email','role_id','department_id','specialization_id','address','hire_date','status','emergency_contact_name','emergency_contact_phone'];
      fields.forEach((field) => {
        const element = document.getElementById(field);
        if (!element) return;
        const value = dataset[field] ?? '';
        element.value = value;
      });

      document.getElementById('role_id').disabled = false;
      handleRoleChange();
      handleDepartmentChange();
      hydrateAddressFields(dataset.address || '');
      updateStaffSummary();
      staffModal.style.display = 'flex';
    }

    function closeStaffModal() {
      staffModal.style.display = 'none';
    }

    function openStaffView(button) {
      const dataset = button.dataset;
      document.getElementById('viewName').textContent = dataset.name || 'N/A';
      document.getElementById('viewRole').textContent = dataset.role || '-';
      document.getElementById('viewDepartment').textContent = dataset.department || '-';
      document.getElementById('viewSpecialization').textContent = dataset.specialization || '-';
      document.getElementById('viewPhone').textContent = dataset.phone || 'N/A';
      document.getElementById('viewEmail').textContent = dataset.email || 'N/A';
      document.getElementById('viewStatus').textContent = dataset.status || 'Active';
      document.getElementById('viewHireDate').textContent = dataset.hire_date || 'N/A';
      document.getElementById('viewAddress').textContent = dataset.address || 'Not provided';
      staffViewModal.style.display = 'flex';
    }

    function closeStaffView() {
      staffViewModal.style.display = 'none';
    }

    staffForm.addEventListener('submit', function() {
      setTimeout(() => closeStaffModal(), 0);
    });

    function filterStaffTable() {
      const searchValue = document.getElementById('searchStaffInput').value.toLowerCase();
      const deptValue = document.getElementById('departmentFilter').value;
      const roleValue = document.getElementById('roleFilter').value;
      const statusValue = document.getElementById('statusFilter').value.toLowerCase();
      const rows = document.querySelectorAll('#staffTable tr');

      rows.forEach((row) => {
        const cells = row.getElementsByTagName('td');
        if (!cells.length) return;

        const name = (cells[0].innerText || '').toLowerCase();
        const deptText = (cells[1].innerText || '').toLowerCase();
        const specText = (cells[2].innerText || '').toLowerCase();
        const statusText = (cells[5].innerText || '').trim().toLowerCase();

        const matchesSearch = searchValue === '' || name.includes(searchValue) || deptText.includes(searchValue) || specText.includes(searchValue);
        const matchesDept = deptValue === '' || row.getAttribute('data-department-id') === deptValue;
        const matchesRole = roleValue === '' || row.getAttribute('data-role-id') === roleValue;
        const matchesStatus = statusValue === '' || statusText === statusValue.replace('_', ' ');

        row.style.display = (matchesSearch && matchesDept && matchesRole && matchesStatus) ? '' : 'none';
      });
    }

    function handleRoleChange() {
      const roleSelect = document.getElementById('role_id');
      const selectedRole = roleSelect.options[roleSelect.selectedIndex];
      const scope = selectedRole ? selectedRole.dataset.scope : 'all';
      filterDepartmentOptions(scope || 'all');
      handleDepartmentChange();
      updateStaffSummary();
    }

    function filterDepartmentOptions(scope) {
      const deptSelect = document.getElementById('department_id');
      Array.from(deptSelect.options).forEach((option) => {
        if (!option.value) return;
        const optionScope = option.dataset.scope || 'all';
        const isVisible = scope === 'all' || optionScope === 'all' || optionScope === scope;
        option.hidden = !isVisible;
        if (!isVisible && option.selected) {
          option.selected = false;
        }
      });
    }

    function handleDepartmentChange() {
      const deptSelect = document.getElementById('department_id');
      const deptId = deptSelect.value;
      const roleSelect = document.getElementById('role_id');
      const roleScope = roleSelect.options[roleSelect.selectedIndex]?.dataset.scope || 'all';
      const specSelect = document.getElementById('specialization_id');

      Array.from(specSelect.options).forEach((option) => {
        if (!option.value) return;
        const optionDept = option.dataset.department || '';
        const optionScope = option.dataset.scope || 'doctor';
        const matchesDept = !optionDept || optionDept === deptId;
        const matchesScope = roleScope === 'all' || optionScope === roleScope || optionScope === 'all';
        const show = matchesDept && matchesScope;
        option.hidden = !show;
        if (!show && option.selected) {
          option.selected = false;
        }
      });
      updateStaffSummary();
    }

    function updateStaffSummary() {
      const roleSelect = document.getElementById('role_id');
      const deptSelect = document.getElementById('department_id');
      const statusSelect = document.getElementById('status');
      const roleText = roleSelect?.options[roleSelect.selectedIndex]?.text?.trim() || 'Not set';
      const deptText = deptSelect?.options[deptSelect.selectedIndex]?.text?.trim() || 'Not set';
      const statusText = statusSelect?.options[statusSelect.selectedIndex]?.text?.trim() || 'Active';
      const summaryRole = document.getElementById('summaryRole');
      const summaryDept = document.getElementById('summaryDepartment');
      const summaryStatus = document.getElementById('summaryStatus');
      if (summaryRole) summaryRole.textContent = roleText;
      if (summaryDept) summaryDept.textContent = deptText;
      if (summaryStatus) summaryStatus.textContent = statusText;
    }

    document.getElementById('status').addEventListener('change', updateStaffSummary);

    const addressFields = ['address_province','address_city','address_barangay','address_street'];
    addressFields.forEach((id) => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', composeAddressFromParts);
      }
    });

    function composeAddressFromParts() {
      const province = document.getElementById('address_province')?.value.trim();
      const city = document.getElementById('address_city')?.value.trim();
      const barangay = document.getElementById('address_barangay')?.value.trim();
      const street = document.getElementById('address_street')?.value.trim();
      const parts = [street, barangay, city, province].filter(Boolean);
      const combined = parts.join(', ');
      const preview = document.getElementById('address_preview');
      const hidden = document.getElementById('address');
      if (preview) preview.value = combined;
      if (hidden) hidden.value = combined;
    }

    function resetAddressFields() {
      ['address_province','address_city','address_barangay','address_street'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
      const preview = document.getElementById('address_preview');
      if (preview) preview.value = '';
      const hidden = document.getElementById('address');
      if (hidden) hidden.value = '';
      if (addressProvinceCodeInput) addressProvinceCodeInput.value = '';
      if (addressCityCodeInput) addressCityCodeInput.value = '';
      if (addressBarangayCodeInput) addressBarangayCodeInput.value = '';
      selectedAddressProvince = null;
      selectedAddressCity = null;
      selectedAddressBarangay = null;
      addressCityOptions = [];
      addressBarangayOptions = [];
      if (addressCityInput) {
        addressCityInput.disabled = true;
        addressCityInput.placeholder = 'Select province first';
      }
      if (addressBarangayInput) {
        addressBarangayInput.disabled = true;
        addressBarangayInput.placeholder = 'Select city/municipality';
      }
      hideAddressDropdown(addressProvinceDropdown);
      hideAddressDropdown(addressCityDropdown);
      hideAddressDropdown(addressBarangayDropdown);
    }

    function hydrateAddressFields(fullAddress) {
      resetAddressFields();
      if (!fullAddress) {
        composeAddressFromParts();
        return;
      }
      const parts = fullAddress.split(',').map((p) => p.trim()).filter(Boolean);
      let province = '', city = '', barangay = '', street = '';
      if (parts.length === 1) {
        street = parts[0];
      } else if (parts.length === 2) {
        street = parts[0];
        province = parts[1];
      } else if (parts.length === 3) {
        street = parts[0];
        city = parts[1];
        province = parts[2];
      } else if (parts.length >= 4) {
        province = parts.pop();
        city = parts.pop();
        barangay = parts.pop();
        street = parts.join(', ');
      }
      document.getElementById('address_province').value = province;
      document.getElementById('address_city').value = city;
      document.getElementById('address_barangay').value = barangay;
      document.getElementById('address_street').value = street;
      if (addressCityInput) addressCityInput.disabled = false;
      if (addressBarangayInput) addressBarangayInput.disabled = false;
      composeAddressFromParts();
    }

    function showAddressDropdown(dropdown, items, onSelect) {
      if (!dropdown) return;
      dropdown.innerHTML = '';
      if (!items.length) {
        dropdown.style.display = 'none';
        return;
      }
      items.forEach((item) => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.textContent = item.name;
        div.addEventListener('click', () => {
          onSelect(item);
          dropdown.style.display = 'none';
        });
        dropdown.appendChild(div);
      });
      dropdown.style.display = 'block';
    }

    function hideAddressDropdown(dropdown) {
      if (dropdown) dropdown.style.display = 'none';
    }

    function filterAddressOptions(options, term) {
      if (!term) return options;
      const lower = term.toLowerCase();
      return options.filter((opt) => opt.name.toLowerCase().includes(lower));
    }

    async function loadAddressProvinces() {
      try {
        const res = await fetch(`${addressLocationApiBase}/provinces`);
        addressProvinceOptions = await res.json();
      } catch (err) {
        console.error('Failed to load provinces', err);
        addressProvinceOptions = [];
      }
    }

    async function loadAddressCities(provinceCode) {
      if (!provinceCode) return;
      try {
        const res = await fetch(`${addressLocationApiBase}/cities/${encodeURIComponent(provinceCode)}`);
        addressCityOptions = await res.json();
      } catch (err) {
        console.error('Failed to load cities', err);
        addressCityOptions = [];
      }
    }

    async function loadAddressBarangays(cityCode) {
      if (!cityCode) return;
      try {
        const res = await fetch(`${addressLocationApiBase}/barangays/${encodeURIComponent(cityCode)}`);
        addressBarangayOptions = await res.json();
      } catch (err) {
        console.error('Failed to load barangays', err);
        addressBarangayOptions = [];
      }
    }

    function selectAddressProvince(item) {
      selectedAddressProvince = item;
      if (addressProvinceInput) addressProvinceInput.value = item.name;
      if (addressProvinceCodeInput) addressProvinceCodeInput.value = item.code;
      if (addressCityInput) {
        addressCityInput.value = '';
        addressCityInput.disabled = false;
        addressCityInput.placeholder = 'Type to search city/municipality...';
      }
      if (addressCityCodeInput) addressCityCodeInput.value = '';
      if (addressBarangayInput) {
        addressBarangayInput.value = '';
        addressBarangayInput.disabled = true;
        addressBarangayInput.placeholder = 'Select city/municipality';
      }
      if (addressBarangayCodeInput) addressBarangayCodeInput.value = '';
      selectedAddressCity = null;
      selectedAddressBarangay = null;
      addressCityOptions = [];
      addressBarangayOptions = [];
      hideAddressDropdown(addressCityDropdown);
      hideAddressDropdown(addressBarangayDropdown);
      composeAddressFromParts();
    }

    function selectAddressCity(item) {
      selectedAddressCity = item;
      if (addressCityInput) {
        addressCityInput.value = item.name;
        addressCityInput.disabled = false;
      }
      if (addressCityCodeInput) addressCityCodeInput.value = item.code;
      if (addressBarangayInput) {
        addressBarangayInput.value = '';
        addressBarangayInput.disabled = false;
        addressBarangayInput.placeholder = 'Type to search barangay...';
      }
      if (addressBarangayCodeInput) addressBarangayCodeInput.value = '';
      selectedAddressBarangay = null;
      addressBarangayOptions = [];
      hideAddressDropdown(addressBarangayDropdown);
      composeAddressFromParts();
    }

    function selectAddressBarangay(item) {
      selectedAddressBarangay = item;
      if (addressBarangayInput) addressBarangayInput.value = item.name;
      if (addressBarangayCodeInput) addressBarangayCodeInput.value = item.code;
      composeAddressFromParts();
    }

    function initAddressAutocomplete() {
      if (addressProvinceInput && addressProvinceDropdown) {
        loadAddressProvinces();
        addressProvinceInput.addEventListener('input', (e) => {
          const term = e.target.value.trim();
          const filtered = filterAddressOptions(addressProvinceOptions, term);
          showAddressDropdown(addressProvinceDropdown, filtered.slice(0, 10), selectAddressProvince);
        });
        addressProvinceInput.addEventListener('focus', () => {
          if (!addressProvinceOptions.length) {
            loadAddressProvinces().then(() => {
              const filtered = filterAddressOptions(addressProvinceOptions, addressProvinceInput.value.trim());
              showAddressDropdown(addressProvinceDropdown, filtered.slice(0, 10), selectAddressProvince);
            });
          } else {
            const filtered = filterAddressOptions(addressProvinceOptions, addressProvinceInput.value.trim());
            showAddressDropdown(addressProvinceDropdown, filtered.slice(0, 10), selectAddressProvince);
          }
        });
        document.addEventListener('click', (e) => {
          if (!addressProvinceInput) return;
          const wrapper = addressProvinceInput.closest('.autocomplete-wrapper');
          if (wrapper && !wrapper.contains(e.target)) {
            hideAddressDropdown(addressProvinceDropdown);
          }
        });
      }

      if (addressCityInput && addressCityDropdown) {
        addressCityInput.addEventListener('input', (e) => {
          if (!selectedAddressProvince) return;
          const ensureCities = addressCityOptions.length
            ? Promise.resolve()
            : loadAddressCities(selectedAddressProvince.code);
          ensureCities.then(() => {
            const term = e.target.value.trim();
            const filtered = filterAddressOptions(addressCityOptions, term);
            showAddressDropdown(addressCityDropdown, filtered.slice(0, 10), selectAddressCity);
          });
        });
        addressCityInput.addEventListener('focus', () => {
          if (!selectedAddressProvince) return;
          const ensureCities = addressCityOptions.length
            ? Promise.resolve()
            : loadAddressCities(selectedAddressProvince.code);
          ensureCities.then(() => {
            const filtered = filterAddressOptions(addressCityOptions, addressCityInput.value.trim());
            showAddressDropdown(addressCityDropdown, filtered.slice(0, 10), selectAddressCity);
          });
        });
        document.addEventListener('click', (e) => {
          if (!addressCityInput) return;
          const wrapper = addressCityInput.closest('.autocomplete-wrapper');
          if (wrapper && !wrapper.contains(e.target)) {
            hideAddressDropdown(addressCityDropdown);
          }
        });
      }

      if (addressBarangayInput && addressBarangayDropdown) {
        addressBarangayInput.addEventListener('input', (e) => {
          if (!selectedAddressCity) return;
          const ensureBarangays = addressBarangayOptions.length
            ? Promise.resolve()
            : loadAddressBarangays(selectedAddressCity.code);
          ensureBarangays.then(() => {
            const term = e.target.value.trim();
            const filtered = filterAddressOptions(addressBarangayOptions, term);
            showAddressDropdown(addressBarangayDropdown, filtered.slice(0, 10), selectAddressBarangay);
          });
        });
        addressBarangayInput.addEventListener('focus', () => {
          if (!selectedAddressCity) return;
          const ensureBarangays = addressBarangayOptions.length
            ? Promise.resolve()
            : loadAddressBarangays(selectedAddressCity.code);
          ensureBarangays.then(() => {
            const filtered = filterAddressOptions(addressBarangayOptions, addressBarangayInput.value.trim());
            showAddressDropdown(addressBarangayDropdown, filtered.slice(0, 10), selectAddressBarangay);
          });
        });
        document.addEventListener('click', (e) => {
          if (!addressBarangayInput) return;
          const wrapper = addressBarangayInput.closest('.autocomplete-wrapper');
          if (wrapper && !wrapper.contains(e.target)) {
            hideAddressDropdown(addressBarangayDropdown);
          }
        });
      }
    }

    initAddressAutocomplete();

    window.onclick = function(e) {
      if (e.target === staffModal) closeStaffModal();
      if (e.target === staffViewModal) closeStaffView();
      if (e.target === document.getElementById('flashBackdrop')) closeFlashModal();
      if (e.target === document.getElementById('flashModal')) closeFlashModal();
    };

    function closeFlashModal() {
      document.getElementById('flashBackdrop').style.display = 'none';
      document.getElementById('flashModal').style.display = 'none';
    }
  </script>
<?= $this->endSection() ?>
