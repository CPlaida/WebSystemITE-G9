<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Appointment List') ?> - HMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .search-box {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .appointments-table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h2 {
            color: #495057;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            text-transform: uppercase;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Appointment Management</h1>
            <p>View and manage all appointments</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="search-box">
            <input type="text" class="search-input" placeholder="Search appointments by patient name, doctor, appointment ID...">
        </div>

        <div class="appointments-table">
            <div class="table-header">
                <h2>All Appointments</h2>
            </div>
            
            <?php if (!empty($appointments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>APPOINTMENT ID</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>PATIENT</th>
                            <th>DOCTOR</th>
                            <th>TYPE</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><strong><?= esc($appointment['appointment_id']) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                <td><strong><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></strong></td>
                                <td>
                                    <?php if (!empty($appointment['patient_first_name']) && !empty($appointment['patient_last_name'])): ?>
                                        <?= esc($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                                    <?php else: ?>
                                        Patient ID: <?= esc($appointment['patient_id']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($appointment['doctor_name'])): ?>
                                        Dr. <?= esc($appointment['doctor_name']) ?>
                                        <?php if (!empty($appointment['doctor_email'])): ?>
                                            <br><small style="color: #6c757d;"><?= esc($appointment['doctor_email']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Doctor ID: <?= esc($appointment['doctor_id']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $appointment['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] == 'scheduled'): ?>
                                        <button class="btn btn-success" onclick="updateStatus(<?= $appointment['id'] ?>, 'confirmed')" title="Confirm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($appointment['status'], ['scheduled', 'confirmed'])): ?>
                                        <button class="btn btn-danger" onclick="deleteAppointment(<?= $appointment['id'] ?>)" title="Delete">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-info" onclick="viewDetails(<?= $appointment['id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <h3>No Appointments Found</h3>
                    <p>There are no appointments scheduled at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            const tableRows = document.querySelectorAll('tbody tr');
            
            if (searchInput && tableRows.length > 0) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    tableRows.forEach(row => {
                        const rowText = row.textContent.toLowerCase();
                        if (rowText.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
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
                body: JSON.stringify({
                    status: status,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                })
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
                body: JSON.stringify({
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                })
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
                                <div><strong>Appointment ID:</strong> ${appointment.appointment_id}</div>
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
                            <button onclick="closeModal()" class="btn" style="background: #6c757d; color: white;">Close</button>
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
</body>
</html>