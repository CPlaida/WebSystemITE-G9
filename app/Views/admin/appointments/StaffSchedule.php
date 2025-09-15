<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Staff Schedule & Shift Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  /* Custom styles for the schedule table and tooltips */
  .schedule-table {
    border-collapse: collapse;
    width: 100%;
    background: white;
  }
  .schedule-table th,
  .schedule-table td {
    border: 1px solid #ccc;
    padding: 8px 12px;
    vertical-align: top;
    font-size: 0.9rem;
  }
  .shift {
    border-radius: 4px;
    margin-bottom: 6px;
    padding: 6px 10px;
    cursor: pointer;
    display: inline-block;
    width: 100%;
    position: relative;
  }
  .shift:hover {
    filter: brightness(95%);
  }
  /* Department colors - improved medical associations */
  .dept-emergency { background-color: #dc2626; color: white; } /* Strong red - urgent */
  .dept-cardiology { background-color: #ef4444; color: white; } /* Heart red */
  .dept-neurology { background-color: #3b82f6; color: white; } /* Brain blue */
  .dept-orthopedics { background-color: #059669; color: white; } /* Bone green */
  .dept-pediatrics { background-color: #f59e0b; color: white; } /* Child orange */
  .dept-general { background-color: #6b7280; color: white; } /* Neutral gray */
  
  .conflict-icon {
    position: absolute;
    top: 6px;
    right: 8px;
    font-weight: 700;
    color: #b45309; /* amber-700 */
  }
  .legend-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    margin-bottom: 4px;
  }
  .legend-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: inline-block;
  }
</style>
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <section aria-label="Staff Schedule Section">
      <div class="flex justify-between items-center mb-5 p-6">
        <h1 class="text-xl font-bold">Doctor Schedule</h1>
        <div class="flex gap-3 items-center">
          <button id="btnConflictSchedules" class="border border-gray-400 px-3 py-1 rounded hover:bg-gray-200 text-sm">Conflict schedules</button>
          <button id="btnAddShift" class="bg-gray-800 text-white rounded px-3 py-1 hover:bg-gray-900 text-sm">+ Add Shift</button>
          <div>
            <button type="button" data-view="day" class="btn-view-mode px-3 py-2 border border-gray-300 rounded-l hover:bg-blue-50 text-sm bg-blue-600 text-white font-medium" aria-pressed="true">📅 Day</button>
            <button type="button" data-view="week" class="btn-view-mode px-3 py-2 border-t border-b border-l border-gray-300 hover:bg-blue-50 text-sm bg-white text-gray-700">📊 Week</button>
            <button type="button" data-view="month" class="btn-view-mode px-3 py-2 border border-gray-300 rounded-r hover:bg-blue-50 text-sm bg-white text-gray-700">🗓️ Month</button>
          </div>
        </div>
      </div>

      <!-- Day View -->
      <div id="dayView" class="view-container">
        <div class="mb-4 text-center">
          <h2 class="text-lg font-semibold" id="dayViewTitle">Today's Schedule - <?= date('F j, Y') ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php 
          $todaySchedules = array_filter($schedules, function($schedule) {
            return date('Y-m-d', strtotime($schedule['shift_date'])) === date('Y-m-d');
          });
          ?>
          <?php if (!empty($todaySchedules)): ?>
            <?php foreach ($todaySchedules as $schedule): ?>
              <div class="shift dept-<?= strtolower($schedule['department']) ?> <?= $schedule['status'] === 'cancelled' ? 'opacity-50' : '' ?>" 
                   data-schedule-id="<?= $schedule['id'] ?>" 
                   tabindex="0" 
                   role="button">
                <div class="font-medium"><?= htmlspecialchars($schedule['doctor_name']) ?></div>
                <div class="text-xs opacity-75"><?= ucfirst($schedule['department']) ?> • <?= ucfirst($schedule['shift_type']) ?></div>
                <div class="text-xs mt-1"><?= date('g:i A', strtotime($schedule['start_time'])) ?> - <?= date('g:i A', strtotime($schedule['end_time'])) ?></div>
                <?php if ($schedule['status'] === 'cancelled'): ?>
                  <div class="text-xs text-red-600 font-medium">(Cancelled)</div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-span-full text-center py-8 text-gray-500">
              No schedules for today.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Week View -->
      <div id="weekView" class="view-container hidden">
        <div class="mb-4 text-center">
          <h2 class="text-lg font-semibold">Week View - <?= date('F j', strtotime('monday this week')) ?> - <?= date('F j, Y', strtotime('sunday this week')) ?></h2>
        </div>
        <div class="grid grid-cols-7 gap-2 text-sm">
          <?php 
          $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
          for ($i = 0; $i < 7; $i++): 
            $dayDate = date('Y-m-d', strtotime('monday this week +' . $i . ' days'));
            $daySchedules = array_filter($schedules, function($schedule) use ($dayDate) {
              return date('Y-m-d', strtotime($schedule['shift_date'])) === $dayDate;
            });
          ?>
            <div class="border rounded-lg p-3 min-h-[200px]">
              <div class="font-semibold text-center mb-2 text-gray-700">
                <?= $weekDays[$i] ?>
                <div class="text-xs text-gray-500"><?= date('M j', strtotime($dayDate)) ?></div>
              </div>
              <div class="space-y-1">
                <?php foreach ($daySchedules as $schedule): ?>
                  <div class="shift dept-<?= strtolower($schedule['department']) ?> text-xs p-1" 
                       data-schedule-id="<?= $schedule['id'] ?>">
                    <div class="font-medium"><?= htmlspecialchars($schedule['doctor_name']) ?></div>
                    <div class="opacity-75"><?= ucfirst($schedule['shift_type']) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Month View -->
      <div id="monthView" class="view-container hidden">
        <div class="mb-4 text-center">
          <h2 class="text-lg font-semibold">Month View - <?= date('F Y') ?></h2>
        </div>
        <div class="grid grid-cols-7 gap-1 text-sm">
          <!-- Calendar headers -->
          <?php $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; ?>
          <?php foreach ($weekDays as $day): ?>
            <div class="p-2 text-center font-semibold text-gray-600 bg-gray-100"><?= $day ?></div>
          <?php endforeach; ?>
          
          <!-- Calendar days -->
          <?php 
          $firstDay = date('Y-m-01');
          $lastDay = date('Y-m-t');
          $startDate = date('Y-m-d', strtotime('last sunday', strtotime($firstDay)));
          $endDate = date('Y-m-d', strtotime('next saturday', strtotime($lastDay)));
          
          $currentDate = $startDate;
          while ($currentDate <= $endDate):
            $isCurrentMonth = date('m', strtotime($currentDate)) === date('m');
            $daySchedules = array_filter($schedules, function($schedule) use ($currentDate) {
              return date('Y-m-d', strtotime($schedule['shift_date'])) === $currentDate;
            });
          ?>
            <div class="border min-h-[80px] p-1 <?= $isCurrentMonth ? 'bg-white' : 'bg-gray-50' ?>">
              <div class="text-xs <?= $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' ?> mb-1">
                <?= date('j', strtotime($currentDate)) ?>
              </div>
              <div class="space-y-1">
                <?php foreach (array_slice($daySchedules, 0, 2) as $schedule): ?>
                  <div class="dept-<?= strtolower($schedule['department']) ?> text-xs p-1 rounded" 
                       data-schedule-id="<?= $schedule['id'] ?>">
                    <?= htmlspecialchars(substr($schedule['doctor_name'], 0, 8)) ?>
                  </div>
                <?php endforeach; ?>
                <?php if (count($daySchedules) > 2): ?>
                  <div class="text-xs text-gray-500">+<?= count($daySchedules) - 2 ?> more</div>
                <?php endif; ?>
              </div>
            </div>
          <?php 
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
          endwhile; 
          ?>
        </div>
      </div>

      <!-- Legend Section - Simplified -->
      <section id="scheduleLegend" class="p-4 bg-gray-50 border-t" aria-label="Schedule legend">
        <div class="flex flex-wrap gap-6 text-sm">
          <div>
            <div class="font-semibold mb-2">Departments</div>
            <div class="flex flex-wrap gap-2">
              <span class="dept-emergency px-2 py-1 rounded text-xs font-medium">Emergency</span>
              <span class="dept-cardiology px-2 py-1 rounded text-xs font-medium">Cardiology</span>
              <span class="dept-neurology px-2 py-1 rounded text-xs font-medium">Neurology</span>
              <span class="dept-orthopedics px-2 py-1 rounded text-xs font-medium">Orthopedics</span>
              <span class="dept-pediatrics px-2 py-1 rounded text-xs font-medium">Pediatrics</span>
              <span class="dept-general px-2 py-1 rounded text-xs font-medium">General</span>
            </div>
          </div>
          <div>
            <div class="font-semibold mb-2">Alerts</div>
            <div class="text-amber-700 flex items-center gap-1">
              <span>⚠️</span>
              <span>Scheduling Conflict</span>
            </div>
          </div>
        </div>
      </section>
    </section>
  </div>
</div>

<!-- Modal: Conflict Schedule Detected -->
<div id="conflictModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="conflictModalTitle">
  <div class="bg-white max-w-md w-full rounded-lg shadow-lg p-6 relative">
    <header class="flex justify-between items-center mb-4">
      <h2 id="conflictModalTitle" class="text-lg font-bold">Conflicts Schedule Detected</h2>
      <button aria-label="Close conflict modal" id="closeConflictModal" class="text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
    </header>
    <nav class="inline-flex border border-gray-300 rounded overflow-hidden mb-4" role="tablist" aria-label="Conflict tab navigation">
      <button role="tab" aria-selected="true" aria-controls="conflictListPanel" id="conflictListTab" tabindex="0"
        class="px-4 py-2 bg-gray-100 font-semibold border-r border-gray-300 focus:outline-none focus:ring focus:ring-indigo-400 transition">Conflict List</button>
      <button role="tab" aria-selected="false" aria-controls="conflictDetailsPanel" id="conflictDetailsTab" tabindex="-1"
        class="px-4 py-2 font-semibold hover:bg-gray-100 focus:outline-none focus:ring focus:ring-indigo-400 transition">Conflict Details</button>
    </nav>
    <section id="conflictListPanel" role="tabpanel" tabindex="0" aria-labelledby="conflictListTab" class="space-y-3 max-h-56 overflow-y-auto">
      <article class="bg-gray-200 p-3 rounded shadow-sm" tabindex="0" role="button" aria-label="Conflict: Double Booking Emergency Nurse 1 on 2025-08-13" data-conflictid="1">
        <div class="inline-flex items-baseline gap-2 mb-1">
          <span class="bg-gray-300 rounded-full text-xs font-bold px-2 py-0.5">Double Booking</span>
          <time class="text-xs text-gray-600 ml-auto">2025-08-13</time>
        </div>
        <div>
          <p class="font-bold">Emergency</p>
          <p class="text-sm">6:00 am - 2:00 pm</p>
          <p class="text-sm font-semibold">Dr. Smith</p>
        </div>
      </article>
      <article class="bg-gray-200 p-3 rounded shadow-sm" tabindex="0" role="button" aria-label="Conflict: Double Booking General Nurse 1 on 2025-08-13" data-conflictid="2">
        <div class="inline-flex items-baseline gap-2 mb-1">
          <span class="bg-gray-300 rounded-full text-xs font-bold px-2 py-0.5">Double Booking</span>
          <time class="text-xs text-gray-600 ml-auto">2025-08-13</time>
        </div>
        <div>
          <p class="font-bold">General</p>
          <p class="text-sm">6:00 am - 2:00 pm</p>
          <p class="text-sm font-semibold">Dr. Smith</p>
        </div>
      </article>
    </section>
    <section id="conflictDetailsPanel" role="tabpanel" tabindex="0" aria-labelledby="conflictDetailsTab" class="hidden max-h-56 overflow-y-auto text-sm text-gray-800">
      <!-- Conflict details will be shown here upon tab switch -->
      <p class="italic text-gray-500">Select a conflict from the list to see details here.</p>
    </section>
    <footer class="mt-6 flex justify-between">
      <button id="btnCloseConflict" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 focus:outline-none focus:ring focus:ring-indigo-400" type="button">
        Close
      </button>
      <button id="btnResolveConflict" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-400" type="button">
        Resolve Conflict
      </button>
    </footer>
  </div>
</div>

<script>
  // Modal elements
  const conflictModal = document.getElementById('conflictModal');
  const btnConflictSchedules = document.getElementById('btnConflictSchedules');
  const btnCloseConflict = document.getElementById('btnCloseConflict');
  const closeConflictModalBtn = document.getElementById('closeConflictModal');
  const btnResolveConflict = document.getElementById('btnResolveConflict');
  
  // Tabs
  const conflictListTab = document.getElementById('conflictListTab');
  const conflictDetailsTab = document.getElementById('conflictDetailsTab');
  const conflictListPanel = document.getElementById('conflictListPanel');
  const conflictDetailsPanel = document.getElementById('conflictDetailsPanel');

  // Conflict articles in list
  const conflictArticles = conflictListPanel.querySelectorAll('article');

  // Schedule shifts clickable
  const shifts = document.querySelectorAll('.shift');

  btnConflictSchedules.addEventListener('click', () => {
    openConflictModal();
  });

  btnCloseConflict.addEventListener('click', closeConflictModal);
  closeConflictModalBtn.addEventListener('click', closeConflictModal);

  // Close modal on ESC key
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !conflictModal.classList.contains('hidden')) {
      closeConflictModal();
    }
  });

  function openConflictModal() {
    conflictModal.classList.remove('hidden');
    conflictListTab.focus();
  }
  function closeConflictModal() {
    conflictModal.classList.add('hidden');
  }

  // Tab switching logic
  conflictListTab.addEventListener('click', () => {
    conflictListTab.setAttribute('aria-selected', 'true');
    conflictListTab.tabIndex = 0;
    conflictDetailsTab.setAttribute('aria-selected', 'false');
    conflictDetailsTab.tabIndex = -1;
    conflictListPanel.classList.remove('hidden');
    conflictDetailsPanel.classList.add('hidden');
    conflictListPanel.focus();
  });
  conflictDetailsTab.addEventListener('click', () => {
    conflictDetailsTab.setAttribute('aria-selected', 'true');
    conflictDetailsTab.tabIndex = 0;
    conflictListTab.setAttribute('aria-selected', 'false');
    conflictListTab.tabIndex = -1;
    conflictDetailsPanel.classList.remove('hidden');
    conflictListPanel.classList.add('hidden');
    conflictDetailsPanel.focus();
  });

  // Populate conflict details on click on list items
  conflictArticles.forEach((article) => {
    article.addEventListener('click', () => {
      showConflictDetails(article);
      // Switch to Conflict Details tab
      conflictDetailsTab.click();
    });
    article.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        article.click();
      }
    });
  });

  function showConflictDetails(conflictItem) {
    const conflictData = {
      1: {
        title: 'Double Booking - Emergency Shift',
        date: '2025-08-13',
        time: '6:00 am - 2:00 pm',
        role: 'Dr. Smith',
        description: 'Dr. Smith is booked for two conflicting shifts in Emergency and General departments at the same time. Resolution required to avoid overlap.'
      },
      2: {
        title: 'Double Booking - General Shift',
        date: '2025-08-13',
        time: '6:00 am - 2:00 pm',
        role: 'Dr. Smith',
        description: 'Dr. Smith is booked for two conflicting shifts in General and Emergency departments at the same time. Resolution required to avoid overlap.'
      }
    };
    const id = conflictItem.getAttribute('data-conflictid');
    const conflict = conflictData[id];
    if (!conflict) {
      conflictDetailsPanel.innerHTML = '<p class="italic text-gray-500">No details available for this conflict.</p>';
      return;
    }
    conflictDetailsPanel.innerHTML = `
      <h3 class="font-semibold mb-2">${conflict.title}</h3>
      <p><strong>Date:</strong> ${conflict.date}</p>
      <p><strong>Time:</strong> ${conflict.time}</p>
      <p><strong>Role:</strong> ${conflict.role}</p>
      <p class="mt-3">${conflict.description}</p>
    `;
  }

  // Adding click handlers for shifts event
  shifts.forEach(shift => {
    shift.addEventListener('click', () => {
      alert('Shift Details:\n' + shift.textContent.trim().replace(/\n/g, ', '));
    });
    shift.addEventListener('keypress', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        shift.click();
      }
    });
  });

  // View mode toggle buttons
  const viewButtons = document.querySelectorAll('.btn-view-mode');
  const dayView = document.getElementById('dayView');
  const weekView = document.getElementById('weekView');
  const monthView = document.getElementById('monthView');
  
  viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const viewMode = btn.getAttribute('data-view');
      
      // Update button states
      viewButtons.forEach(b => {
        b.classList.remove('bg-blue-600', 'text-white');
        b.classList.add('bg-white', 'text-gray-700');
        b.setAttribute('aria-pressed', 'false');
      });
      btn.classList.remove('bg-white', 'text-gray-700');
      btn.classList.add('bg-blue-600', 'text-white');
      btn.setAttribute('aria-pressed', 'true');
      
      // Hide all views
      dayView.classList.add('hidden');
      weekView.classList.add('hidden');
      monthView.classList.add('hidden');
      
      // Show selected view
      switch(viewMode) {
        case 'day':
          dayView.classList.remove('hidden');
          break;
        case 'week':
          weekView.classList.remove('hidden');
          break;
        case 'month':
          monthView.classList.remove('hidden');
          break;
      }
    });
  });
</script>

<!-- Add Shift Modal -->
<div id="addShiftModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white max-w-md w-full rounded-lg shadow-xl p-6 relative">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Add New Shift</h3>
      <button id="closeAddShiftModal" class="text-gray-500 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="addShiftForm">
      <div class="mb-4">
        <label for="staffName" class="block text-sm font-medium text-gray-700 mb-1">Staff Member</label>
        <select id="doctorSelect" name="doctor_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <option value="">Select doctor</option>
          <!-- Debug info -->
          <?php if (isset($doctors)): ?>
            <!-- Doctors variable exists, count: <?= count($doctors) ?> -->
            <?php if (!empty($doctors)): ?>
              <?php foreach ($doctors as $doctor): ?>
                <option value="<?= $doctor['id'] ?>" data-name="<?= ucfirst(str_replace('dr.', '', $doctor['username'])) ?>">
                  <?= ucfirst(str_replace('dr.', 'Dr. ', $doctor['username'])) ?>
                </option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="">No doctors found in database</option>
            <?php endif; ?>
          <?php else: ?>
            <option value="">Doctors variable not set</option>
          <?php endif; ?>
        </select>
      </div>
      
      <div class="mb-4">
        <label for="shiftDate" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" id="shiftDate" name="shiftDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label for="shiftType" class="block text-sm font-medium text-gray-700 mb-1">Shift Type</label>
          <select id="shiftType" name="shiftType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="morning">Morning (6:00 AM - 2:00 PM)</option>
            <option value="afternoon">Afternoon (10:00 AM - 4:00 PM)</option>
            <option value="night">Night (4:00 PM - 10:00 PM)</option>
          </select>
        </div>
        
        <div>
          <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select id="department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="Emergency">🚑 Emergency Department</option>
            <option value="General">🏥 General Medicine</option>
            <option value="Cardiology">❤️ Cardiology</option>
            <option value="Neurology">🧠 Neurology</option>
            <option value="Orthopedics">🦴 Orthopedics</option>
            <option value="Pediatrics">👶 Pediatrics</option>
          </select>
        </div>
      </div>
      
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" id="cancelAddShift" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Add Shift
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Add Shift Modal Functionality
  document.addEventListener('DOMContentLoaded', function() {
    const addShiftModal = document.getElementById('addShiftModal');
    const btnAddShift = document.getElementById('btnAddShift');
    const closeAddShiftModal = document.getElementById('closeAddShiftModal');
    const cancelAddShift = document.getElementById('cancelAddShift');
    const addShiftForm = document.getElementById('addShiftForm');

    // Open modal
    btnAddShift.addEventListener('click', function() {
      addShiftModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
    });

    // Close modal
    function closeModal() {
      addShiftModal.classList.add('hidden');
      document.body.style.overflow = ''; // Re-enable scrolling
    }

    closeAddShiftModal.addEventListener('click', closeModal);
    cancelAddShift.addEventListener('click', closeModal);

    // Close modal when clicking outside
    addShiftModal.addEventListener('click', function(e) {
      if (e.target === addShiftModal) {
        closeModal();
      }
    });

    // Form submission
    addShiftForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form values
      const doctorId = document.getElementById('doctorSelect').value;
      const doctorName = document.getElementById('doctorSelect').options[document.getElementById('doctorSelect').selectedIndex].getAttribute('data-name');
      const shiftDate = document.getElementById('shiftDate').value;
      const shiftType = document.getElementById('shiftType').value;
      const department = document.getElementById('department').value;
      
      // Validate form
      if (!doctorId || !shiftDate || !shiftType || !department) {
        alert('Please fill in all required fields.');
        return;
      }
      
      // Send AJAX request to add schedule
      fetch('<?= base_url('/doctor/addSchedule') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          doctor_id: doctorId,
          doctor_name: doctorName,
          shift_date: shiftDate,
          shift_type: shiftType,
          department: department
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Schedule added successfully!');
          closeModal();
          addShiftForm.reset();
          // Reload the page to show the new schedule
          window.location.reload();
        } else {
          alert('Error: ' + (data.message || 'Failed to add schedule'));
          if (data.conflicts && data.conflicts.length > 0) {
            alert('Scheduling conflict detected. Please choose a different time.');
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the schedule.');
      });
    });

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('shiftDate').min = today;
  });
</script>
<?= $this->endSection() ?>
