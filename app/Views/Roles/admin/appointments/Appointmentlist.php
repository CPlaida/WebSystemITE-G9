<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Today's Appointments<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <style>
    :root {
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --success-color: #1cc88a;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --text-color: #5a5c69;
      --border-color: #e3e6f0;
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .page-title {
      color: var(--text-color);
      font-size: 1.5rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .page-title i {
      color: var(--primary-color);
    }
    
    .card {
      background: white;
      border-radius: 8px;
      box-shadow: var(--card-shadow);
      margin-bottom: 30px;
      overflow: hidden;
    }
    
    .card-header {
      background-color: var(--secondary-color);
      border-bottom: 1px solid var(--border-color);
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .card-title {
      color: var(--primary-color);
      font-weight: 600;
      margin: 0;
      font-size: 1.25rem;
    }
    
    .search-container {
      display: flex;
      gap: 10px;
    }

    .search-input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    .search-button {
      background-color: #2c3e50;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 0 20px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .table-container {
      overflow-x: auto;
      width: 100%;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
    }
    
    th {
      background-color: var(--secondary-color);
      color: var(--primary-color);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.7rem;
      letter-spacing: 0.05em;
      text-align: left;
      padding: 12px 24px;
      border-bottom: 1px solid var(--border-color);
    }
    
    td {
      padding: 16px 24px;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-color);
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
      background-color: rgba(78, 115, 223, 0.05);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 10px;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: 4px;
      text-align: center;
      white-space: nowrap;
    }
    
    .status-confirmed {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    
    

    .status-scheduled {
      background-color: #cff4fc;
      color: #055160;
    }

    .status-completed {
      background-color: #d1e7dd;
      color: #0f5132;
    }

    .status-cancelled {
      background-color: #f8d7da;
      color: #721c24;
    }

    .status-no_show {
      background-color: #e2e3e5;
      color: #383d41;
    }

    .btn-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      font-weight: 500;
      transition: all 0.3s;
      white-space: nowrap;
      min-width: 60px;
    }

    .btn-info {
      background-color: #17a2b8;
      color: white;
    }

    .btn-info:hover {
      background-color: #138496;
    }

    .btn-success {
      background-color: #28a745;
      color: white;
    }

    .btn-success:hover {
      background-color: #218838;
    }

    .btn-warning {
      background-color: #ffc107;
      color: #212529;
    }

    .btn-warning:hover {
      background-color: #e0a800;
    }

    .btn-danger {
      background-color: #dc3545;
      color: white;
    }

    .btn-danger:hover {
      background-color: #c82333;
    }
    
    .card-footer {
      padding: 15px 24px;
      background-color: #f8f9fc;
      color: #858796;
      font-size: 0.875rem;
      border-top: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .search-container {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>

  <div class="container">
    <div class="header">
      <h1 class="page-title">
        Today's Appointments
      </h1>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Appointment List</h2>
        <div class="search-container">
          <input type="text" id="searchInput" class="search-input" placeholder="Search appointments...">
          <button id="searchButton" class="search-button">Search</button>
        </div>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Patient Name</th>
              <th>Doctor</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($appointments) && !empty($appointments)): ?>
              <?php foreach ($appointments as $appointment): ?>
                <tr>
                  <td><strong><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></strong></td>
                  <td><?= esc(trim($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name'])) ?></td>
                  <td>
                    <?php if ($appointment['doctor_name']): ?>
                      Dr. <?= esc($appointment['doctor_name']) ?><br>
                      <small style="color: #666;"><?= esc($appointment['doctor_email']) ?></small>
                    <?php else: ?>
                      <span style="color: #999;">Doctor not assigned</span>
                    <?php endif; ?>
                  </td>
                  <td><?= ucfirst(str_replace('_', ' ', esc($appointment['appointment_type']))) ?></td>
                  <td>
                    <span class="status-badge status-<?= $appointment['status'] ?>">
                      <?= ucfirst(str_replace('_', ' ', esc($appointment['status']))) ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons" style="display: flex; gap: 8px; flex-wrap: wrap;">
                      <button onclick="viewDetails(<?= $appointment['id'] ?>)" class="btn-action btn-info" title="View Details">
                        View
                      </button>
                      <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                        <button onclick="updateStatus(<?= $appointment['id'] ?>, 'completed')" class="btn-action btn-success" title="Mark Complete">
                          Complete
                        </button>
                        <button onclick="updateStatus(<?= $appointment['id'] ?>, 'cancelled')" class="btn-action btn-warning" title="Cancel">
                          Cancel
                        </button>
                      <?php endif; ?>
                      <button onclick="deleteAppointment(<?= $appointment['id'] ?>)" class="btn-action btn-danger" title="Delete">
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                  <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i><br>
                  No appointments found
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <?php if (isset($appointments)): ?>
          Showing <?= count($appointments) ?> appointment(s)
        <?php else: ?>
          No appointments to display
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    function filterAppointments() {
      const searchInput = document.getElementById('searchInput');
      const searchTerm = searchInput.value.toLowerCase();
      const tableRows = document.querySelectorAll('tbody tr');
      
      tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const searchButton = document.getElementById('searchButton');
      const searchInput = document.getElementById('searchInput');
      
      if (searchButton) {
        searchButton.addEventListener('click', filterAppointments);
      }
      
      if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
          if (e.key === 'Enter') {
            filterAppointments();
          }
        });
      }
    });

    function updateStatus(appointmentId, status) {
      if (status === 'cancelled' && !confirm('Are you sure you want to cancel this appointment?')) {
        return;
      }

      fetch(`<?= base_url('appointments/update/') ?>${appointmentId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({ status: status })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showMessage('Appointment status updated successfully', 'success');
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showMessage(data.message || 'Failed to update appointment status', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while updating the appointment', 'error');
      });
    }

    function deleteAppointment(appointmentId) {
      if (!confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
        return;
      }

      fetch(`<?= base_url('appointments/delete/') ?>${appointmentId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({})
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showMessage('Appointment deleted successfully', 'success');
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showMessage(data.message || 'Failed to delete appointment', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while deleting the appointment', 'error');
      });
    }

    function viewDetails(appointmentId) {
      fetch(`<?= base_url('appointments/show/') ?>${appointmentId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showAppointmentModal(data.appointment);
        } else {
          showMessage(data.message || 'Failed to load appointment details', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while loading appointment details', 'error');
      });
    }

    function showMessage(message, type) {
      const existingMessages = document.querySelectorAll('.alert-message');
      existingMessages.forEach(msg => msg.remove());

      const messageDiv = document.createElement('div');
      messageDiv.className = `alert-message alert-${type}`;
      messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        ${type === 'success' ? 'background-color: #28a745;' : 'background-color: #dc3545;'}
      `;
      messageDiv.textContent = message;

      document.body.appendChild(messageDiv);

      setTimeout(() => {
        messageDiv.remove();
      }, 3000);
    }

    function showAppointmentModal(appointment) {
      const modalHTML = `
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
          <div class="modal-content" style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
              <h3 style="margin: 0; color: #2c3e50;">Appointment Details</h3>
              <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
            </div>
            <div class="modal-body">
              <div style="display: grid; gap: 15px;">
                <div><strong>Appointment ID:</strong> ${appointment.id}</div>
                <div><strong>Patient:</strong> ${appointment.patient_name || 'N/A'}</div>
                <div><strong>Doctor:</strong> ${appointment.doctor_name || 'N/A'}</div>
                <div><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString()}</div>
                <div><strong>Time:</strong> ${appointment.appointment_time}</div>
                <div><strong>Type:</strong> ${appointment.appointment_type}</div>
                <div><strong>Status:</strong> <span class="status-badge status-${appointment.status}">${appointment.status}</span></div>
                ${appointment.reason ? `<div><strong>Reason:</strong> ${appointment.reason}</div>` : ''}
                ${appointment.notes ? `<div><strong>Notes:</strong> ${appointment.notes}</div>` : ''}
              </div>
            </div>
            <div class="modal-footer" style="margin-top: 20px; text-align: right; border-top: 1px solid #eee; padding-top: 15px;">
              <button onclick="closeModal()" style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    function closeModal() {
      const modal = document.querySelector('.modal-overlay');
      if (modal) {
        modal.remove();
      }
    }

    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('modal-overlay')) {
        closeModal();
      }
    });
  </script>
<?= $this->endSection() ?>
