<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Specialized Patient Rooms<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1 class="page-title">
            Specialized Patient Rooms
        </h1>
    </div>

    <div class="card">
        <div class="card-header organized-header">
            <!-- Search Bar Section -->
            <div class="search-section-inline">
                <div class="search-wrapper-inline">
                    <i class="fas fa-search search-icon-inline"></i>
                    <input type="text" id="specializedSearch" class="search-input-inline" placeholder="Search by room number, bed number, patient ID, patient name" autocomplete="off">
                    <button type="button" class="search-clear-btn-inline" id="clearSearch" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <label class="filter-label">
                    <i class="fas fa-filter me-1"></i>Filter by Room Type:
                </label>
                <select id="specializedFilter" class="form-select filter-select">
                    <?php foreach ($roomTypes as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($currentFilter === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body">

            <?php 
            $renderTable = function(array $rows, string $title = null) {
            ?>
                <?php if ($title): ?>
                    <div class="unit-category-header">
                        <h5 class="unit-category-title"><?= esc($title) ?></h5>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle specialized-table">
                        <thead class="table-light">
                            <tr>
                                <th>ROOM TYPE</th>
                                <th>ROOM NO.</th>
                                <th>BED NO.</th>
                                <th>PATIENT ID</th>
                                <th>PATIENT NAME</th>
                                <th>STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): 
                                $patient = $r['patient'] ?? null;
                                $pid = $patient['patient_id'] ?? ($patient['id'] ?? '-');
                                $pname = trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''));
                                if ($pname === '') { $pname = '-'; }
                                $status = $r['status'] ?? 'Available';
                                $badge = ($status === 'Occupied') ? 'bg-danger' : 'bg-success';
                                $badgeColor = ($status === 'Occupied') ? '#dc3545' : '#198754';
                            ?>
                            <tr>
                                <td><?= esc($r['room_type'] ?? '') ?></td>
                                <td><?= esc($r['room'] ?? '-') ?></td>
                                <td><?= esc($r['bed'] ?? '-') ?></td>
                                <td><?= esc($pid) ?></td>
                                <td><?= esc($pname) ?></td>
                                <td><span class="badge <?= $badge ?> text-white" style="background-color: <?= $badgeColor ?> !important; color: #ffffff !important;"><?= esc($status) ?></span></td>
                                <td>
                                    <?php if ($status === 'Occupied' && $patient): ?>
                                        <button type="button" class="btn btn-sm btn-primary view-patient-btn" 
                                                data-patient-id="<?= esc($patient['id'] ?? $patient['patient_id'] ?? '') ?>"
                                                data-patient-data='<?= json_encode($patient, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php };
            ?>

            <?php if ($currentFilter === 'all'): ?>
                <?php 
                // Combine all rooms into a single list without category headings
                $allRows = [];
                foreach (($allRoomsData ?? []) as $roomRows) {
                    foreach ($roomRows as $row) {
                        $allRows[] = $row;
                    }
                }
                $renderTable($allRows);
                ?>
            <?php else: ?>
                <?php $renderTable($rows, $roomTypes[$currentFilter] ?? ''); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Patient Info Modal -->
<div class="modal" id="patientModal" style="display: none;">
    <div class="modal-content patient-modal-content">
        <div class="modal-header patient-modal-header">
            <div class="modal-title-wrapper">
                <i class="fas fa-user-circle modal-title-icon"></i>
                <div>
                    <h5 class="modal-title-main">Patient Registration Information</h5>
                    <p class="modal-subtitle">Complete patient demographic and admission details</p>
                </div>
            </div>
            <button class="close-btn" onclick="closePatientModal()" aria-label="Close">×</button>
        </div>
        <div class="modal-body patient-modal-body" id="patientModalBody">
            <!-- Patient info will be loaded here -->
        </div>
        <div class="modal-footer patient-modal-footer">
            <button type="button" class="btn-close-modal" onclick="closePatientModal()">
                <i class="fas fa-times me-2"></i>Close
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View button functionality
    const viewButtons = document.querySelectorAll('.view-patient-btn');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const patientDataStr = this.getAttribute('data-patient-data');
            if (!patientDataStr) {
                alert('No patient information available');
                return;
            }
            
            try {
                const patient = JSON.parse(patientDataStr);
                viewPatient(patient);
            } catch (error) {
                console.error('Error parsing patient data:', error);
                alert('Error loading patient information');
            }
        });
    });
    
    // Filter dropdown functionality
    const sel = document.getElementById('specializedFilter');
    if (sel) {
        sel.addEventListener('change', function(){
            const v = this.value || 'all';
            window.location.href = '<?= base_url('admin/rooms/specialized') ?>?filter=' + encodeURIComponent(v);
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('specializedSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    
    if (searchInput && clearSearchBtn) {
        const tables = document.querySelectorAll('.specialized-table');
        
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            // Show/hide clear button
            if (searchTerm.length > 0) {
                clearSearchBtn.style.display = 'flex';
            } else {
                clearSearchBtn.style.display = 'none';
            }
            
            // Filter rows in all tables
            tables.forEach(table => {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            });
        }
        
        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                performSearch();
            }
        });
        
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            performSearch();
            searchInput.focus();
        });
    }
});

function viewPatient(patient) {
    if (!patient) {
        alert('No patient information available');
        return;
    }

    // Parse emergency contact JSON if not already parsed (fallback)
    if (patient.emergency_contact && !patient.emergency_contact_person && !patient.emergency_contact_phone) {
        try {
            const contactData = JSON.parse(patient.emergency_contact);
            if (contactData && typeof contactData === 'object') {
                patient.emergency_contact_person = contactData.person || null;
                patient.emergency_contact_relationship = contactData.relationship || null;
                patient.emergency_contact_phone = contactData.phone || null;
            }
        } catch (e) {
            // If JSON parsing fails, check if it's a plain phone number
            if (patient.emergency_contact && /^\+?\d/.test(patient.emergency_contact)) {
                patient.emergency_contact_phone = patient.emergency_contact;
            }
        }
    }

    const modalBody = document.getElementById('patientModalBody');
    const modal = document.getElementById('patientModal');
    
    const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        try {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    };

    const calculateAge = (dateStr) => {
        if (!dateStr) return '-';
        try {
            const today = new Date();
            const birthDate = new Date(dateStr);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age >= 0 ? age : '-';
        } catch (e) {
            return '-';
        }
    };

    const escapeHtml = (text) => {
        if (!text || text === 'null' || text === 'undefined') return '-';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    };

    const fullName = [patient.first_name, patient.middle_name, patient.last_name, patient.name_extension].filter(Boolean).join(' ').trim() || '-';

    modalBody.innerHTML = `
        <!-- Personal Information Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-id-card section-icon"></i>
                <h6 class="section-title">Personal Information</h6>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-field">
                        <label class="field-label">Patient ID</label>
                        <p class="field-value field-value-primary">${escapeHtml(patient.id || patient.patient_id || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Full Name</label>
                        <p class="field-value field-value-primary">${escapeHtml(fullName)}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Date of Birth</label>
                        <p class="field-value">${formatDate(patient.date_of_birth)}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Age</label>
                        <p class="field-value">${calculateAge(patient.date_of_birth)} ${calculateAge(patient.date_of_birth) !== '-' ? 'years old' : ''}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Gender</label>
                        <p class="field-value">${patient.gender ? escapeHtml(patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1)) : '-'}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Blood Type</label>
                        <p class="field-value">${escapeHtml(patient.blood_type || '-')}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-address-book section-icon"></i>
                <h6 class="section-title">Contact Information</h6>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-field">
                        <label class="field-label">Phone Number</label>
                        <p class="field-value">${escapeHtml(patient.phone || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Email Address</label>
                        <p class="field-value">${escapeHtml(patient.email || '-')}</p>
                    </div>
                    <div class="info-field info-field-full">
                        <label class="field-label">Address</label>
                        <p class="field-value">${escapeHtml(patient.address || '-')}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admission Details Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-hospital section-icon"></i>
                <h6 class="section-title">Admission Details</h6>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-field">
                        <label class="field-label">Ward</label>
                        <p class="field-value">${escapeHtml(patient.ward || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Room Number</label>
                        <p class="field-value">${escapeHtml(patient.room || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Bed Number</label>
                        <p class="field-value">${escapeHtml(patient.bed || '-')}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-user-friends section-icon"></i>
                <h6 class="section-title">Emergency Contact</h6>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-field">
                        <label class="field-label">Contact Person</label>
                        <p class="field-value">${escapeHtml(patient.emergency_contact_person || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Relationship</label>
                        <p class="field-value">${escapeHtml(patient.emergency_contact_relationship || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Contact Number</label>
                        <p class="field-value">${escapeHtml(patient.emergency_contact_phone || (patient.emergency_contact && !patient.emergency_contact.startsWith('{') ? patient.emergency_contact : '') || '-')}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-file-medical section-icon"></i>
                <h6 class="section-title">Medical Information</h6>
            </div>
            <div class="section-content">
                <div class="info-field-full">
                    <label class="field-label">Medical History</label>
                    <div class="medical-history-box">
                        <p class="field-value-medical">${escapeHtml(patient.medical_history || 'No medical history recorded.')}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insurance Information Section -->
        <div class="info-section">
            <div class="section-header">
                <i class="fas fa-shield-alt section-icon"></i>
                <h6 class="section-title">Insurance Information</h6>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-field">
                        <label class="field-label">Insurance Provider</label>
                        <p class="field-value">${escapeHtml(patient.insurance_provider || '-')}</p>
                    </div>
                    <div class="info-field">
                        <label class="field-label">Insurance Number</label>
                        <p class="field-value">${escapeHtml(patient.insurance_number || '-')}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    modal.style.display = 'flex';
}

function closePatientModal() {
    const modal = document.getElementById('patientModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('patientModal');
    if (event.target === modal) {
        closePatientModal();
    }
});
</script>
<?= $this->endSection() ?>
