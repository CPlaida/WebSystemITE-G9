<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Today's Appointments<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container-fluid py-4">
    <div class="composite-card billing-card" style="margin-top:0;">
      <div class="composite-header">
        <h1 class="composite-title">Today's Appointments</h1>
      </div>
      <div class="card-body">
        <style>
          .table-container { position: relative; overflow: visible; }
          .action-buttons { position: relative; z-index: 1000; pointer-events: auto; }
          .action-buttons .btn-action { pointer-events: auto; position: relative; z-index: 1001; }
        </style>
        <div class="card" style="box-shadow: none; border: none; margin: 0;">
          <div class="card-header" style="background: transparent; border: none; padding: 0 0 1rem 0;">
        <h2 class="card-title">Appointment List</h2>
        <div class="filter-container" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-start;">
          <div class="btn-group" role="group" aria-label="Date filters" style="display:flex; gap:8px;">
            <button type="button" id="btnToday" class="btn <?= (isset($currentFilter) && $currentFilter==='today') ? 'btn-primary' : 'btn-outline' ?>">Today</button>
            <div style="display:flex; gap:6px; align-items:center;">
              <button type="button" id="btnChooseDate" class="btn <?= (isset($currentFilter) && $currentFilter==='date') ? 'btn-primary' : 'btn-outline' ?>">Choose Date</button>
              <input type="date" id="filterDate" class="form-control" value="<?= esc($currentDate ?? date('Y-m-d')) ?>" style="min-width:170px; <?= (isset($currentFilter) && $currentFilter==='date') ? '' : 'display:none;' ?>">
            </div>
            <button type="button" id="btnAll" class="btn <?= (isset($currentFilter) && $currentFilter==='all') ? 'btn-primary' : 'btn-outline' ?>">All</button>
          </div>
        </div>
      </div>
      <div class="unified-search-wrapper">
          <div class="unified-search-row">
              <i class="fas fa-search unified-search-icon"></i>
              <input type="text" id="searchInput" class="unified-search-field" placeholder="Search appointments...">
          </div>
      </div>
      <div class="table-container" style="position: relative;">
        <table class="data-table">
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
          <tbody id="appointmentsTbody">
            <?php if (isset($appointments) && !empty($appointments)): ?>
              <?php foreach ($appointments as $appointment): ?>
                <tr>
                  <td><strong><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></strong></td>
                  <td><?= esc(trim(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_middle_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '') . ' ' . ($appointment['patient_name_extension'] ?? ''))) ?></td>
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
                    <div class="action-buttons" style="display: flex; gap: 8px; flex-wrap: wrap; position: relative; z-index: 10;">
                      <a href="<?= base_url('appointments/show/' . $appointment['id']) ?>" class="btn-action btn-view js-view" data-appointment-id="<?= $appointment['id'] ?>" title="View Details">
                        View
                      </a>
                      <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                        <button type="button" class="btn-action btn-complete js-complete" data-appointment-id="<?= $appointment['id'] ?>" title="Mark Complete">
                          Complete
                        </button>
                        <button type="button" class="btn-action btn-cancel js-cancel" data-appointment-id="<?= $appointment['id'] ?>" title="Cancel">
                          Cancel
                        </button>
                      <?php endif; ?>
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
    </div>
  </div>

  <script>
    // search removed per request

    function handleViewAppointment(appointmentId) {
      if (!appointmentId) return;
      viewDetails(appointmentId);
    }

    function handleCompleteAppointment(appointmentId) {
      if (!appointmentId) return;
      if (!confirm('Mark this appointment as completed?')) return;
      updateStatus(appointmentId, 'completed');
    }

    function handleCancelAppointment(appointmentId) {
      if (!appointmentId) return;
      if (!confirm('Are you sure you want to cancel this appointment?')) return;
      updateStatus(appointmentId, 'cancelled');
    }

    document.addEventListener('click', function(e) {
      const target = e.target.closest('.js-view, .js-complete, .js-cancel');
      if (!target) return;

      if (target.classList.contains('js-view')) {
        e.preventDefault();
        const id = target.dataset.appointmentId || target.closest('tr')?.dataset.appointmentId;
        handleViewAppointment(id);
      } else if (target.classList.contains('js-complete')) {
        e.preventDefault();
        const id = target.dataset.appointmentId || target.closest('tr')?.dataset.appointmentId;
        handleCompleteAppointment(id);
      } else if (target.classList.contains('js-cancel')) {
        e.preventDefault();
        const id = target.dataset.appointmentId || target.closest('tr')?.dataset.appointmentId;
        handleCancelAppointment(id);
      }
    });

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Wire filter buttons
      const btnToday = document.getElementById('btnToday');
      const btnAll = document.getElementById('btnAll');
      const btnChooseDate = document.getElementById('btnChooseDate');
      const dateInput = document.getElementById('filterDate');

      function goTo(url) { window.location.href = url; }

      if (btnToday) btnToday.addEventListener('click', function(){
        goTo('<?= base_url('appointments/list') ?>?filter=today');
      });
      if (btnAll) btnAll.addEventListener('click', function(){
        goTo('<?= base_url('appointments/list') ?>?filter=all');
      });
      if (btnChooseDate) btnChooseDate.addEventListener('click', function(){
        if (dateInput) {
          dateInput.style.display = '';
          dateInput.focus();
        }
      });
      if (dateInput) dateInput.addEventListener('change', function(){
        if (!this.value) return;
        goTo('<?= base_url('appointments/list') ?>?filter=date&date=' + encodeURIComponent(this.value));
      });
    });

    function updateStatus(appointmentId, status) {
      if (status === 'cancelled' && !confirm('Are you sure you want to cancel this appointment?')) {
        return;
      }

      fetch(`<?= base_url('appointments/update/') ?>${appointmentId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: new URLSearchParams({ status: status })
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

    function formatTime12(timeStr) {
      if (!timeStr) return '';
      const parts = timeStr.split(':');
      const h = parseInt(parts[0], 10);
      const m = parseInt(parts[1] || '0', 10);
      const h12 = (h % 12) || 12;
      const mm = String(m).padStart(2, '0');
      const ampm = h < 12 ? 'AM' : 'PM';
      return `${h12}:${mm} ${ampm}`;
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
          </div>
        </div>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>
