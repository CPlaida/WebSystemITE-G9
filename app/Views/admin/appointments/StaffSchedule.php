<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nurses Schedule & Shift Management</title>
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
    .emergency { background: #fbb6b6; } /* Red-300 */
    .general { background: #fef9c3; } /* Yellow-200 */
    .pediatrics { background: #bbf7d0; } /* Green-200 */
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
    .nurse-pediatrics {
      background-color: #86efac; /* green-300 */
    }
    .nurse-icu {
      background-color: #bae6fd; /* blue-200 */
    }
    .nurse-emergency {
      background-color: #fbb6b6; /* red-300 */
    }
    .nurse-general {
      background-color: #fef9c3; /* yellow-200 */
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <div class="container mx-auto px-4 py-8">
    <div class="mb-6">
      <a href="<?= base_url('dashboard') ?>" class="inline-flex items-center text-gray-700 hover:text-gray-900">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Dashboard
      </a>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
    <section aria-label="Staff Schedule Section">
      <div class="flex justify-between items-center mb-5">
        <h1 class="text-lg font-bold">Nurses Schedule</h1>
        <div class="flex gap-3 items-center">
          <button id="btnConflictSchedules" class="border border-gray-400 px-3 py-1 rounded hover:bg-gray-200 text-sm">Conflict schedules</button>
          <button id="btnViewAll" class="border border-gray-400 px-3 py-1 rounded hover:bg-gray-200 text-sm">View All</button>
          <button id="btnAddShift" class="bg-gray-800 text-white rounded px-3 py-1 hover:bg-gray-900 text-sm">+ Add Shift</button>
          <div>
            <button type="button" data-view="day" class="btn-view-mode px-2 py-1 border border-gray-300 rounded-l hover:bg-gray-200 text-sm bg-gray-200" aria-pressed="true">Day</button>
            <button type="button" data-view="week" class="btn-view-mode px-2 py-1 border-t border-b border-gray-300 hover:bg-gray-200 text-sm">Week</button>
            <button type="button" data-view="month" class="btn-view-mode px-2 py-1 border border-gray-300 rounded-r hover:bg-gray-200 text-sm">Month</button>
          </div>
        </div>
      </div>

      <table class="schedule-table" role="grid" aria-describedby="scheduleLegend">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th scope="col" style="width: 14%;">DATE</th>
            <th scope="col" style="width: 20%;">TIME</th>
            <th scope="col" style="width: 66%;">EVENT</th>
          </tr>
        </thead>
        <tbody id="scheduleBody">
          <tr>
            <td rowspan="4" class="bg-gray-300 font-semibold">Thu Aug 14</td>
            <td>6:00 am - 2:00 pm</td>
            <td>
              <div class="shift nurse-emergency" tabindex="0" role="button" aria-label="Morning Nurse 1 Emergency shift with conflict">
                <strong>Morning</strong><br />
                <strong>Nurse 1</strong><br />
                Emergency
                <span class="conflict-icon" title="Scheduling Conflict" aria-hidden="true">&#9888;</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>10:00 am - 4:00 pm</td>
            <td>
              <div class="shift nurse-general" tabindex="0" role="button" aria-label="Morning Nurse 1 General shift with conflict">
                <strong>Morning</strong><br />
                <strong>Nurse 1</strong><br />
                General
                <span class="conflict-icon" title="Scheduling Conflict" aria-hidden="true">&#9888;</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>10:00 am - 4:00 pm</td>
            <td>
              <div class="shift nurse-pediatrics" tabindex="0" role="button" aria-label="Afternoon Nurse 4 Pediatrics shift">
                <strong>Afternoon</strong><br />
                <strong>Nurse 4</strong><br />
                Pediatrics
              </div>
            </td>
          </tr>
          <tr>
            <td>4:00 pm - 10:00 pm</td>
            <td>
              <div class="shift nurse-emergency" tabindex="0" role="button" aria-label="Night Nurse 3 Emergency shift">
                <strong>Night</strong><br />
                <strong>Nurse 3</strong><br />
                Emergency
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Legend Section -->
      <section id="scheduleLegend" class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-700 select-none" aria-label="Schedule legend">
        <div>
          <div class="font-semibold mb-1">Nurse Departments</div>
          <div class="legend-label"><span class="legend-dot nurse-emergency"></span> Emergency</div>
          <div class="legend-label"><span class="legend-dot nurse-general"></span> General</div>
        </div>
        <div>
          <div class="font-semibold mb-1">Shift Types</div>
          <div class="legend-label"><span class="legend-dot" style="background-color: #86efac;"></span> Morning (6:00 AM - 2:00 PM)</div>
          <div class="legend-label"><span class="legend-dot" style="background-color: #fef08a;"></span> Afternoon (10:00 AM - 4:00 PM)</div>
          <div class="legend-label"><span class="legend-dot" style="background-color: #bae6fd;"></span> Night (4:00 PM - 10:00 PM)</div>
        </div>
        <div>
          <div class="font-semibold mb-1">Nurse Status</div>
          <div><span class="w-3 h-3 inline-block rounded-full bg-green-500 mr-2"></span> Available</div>
          <div><span class="w-3 h-3 inline-block rounded-full bg-yellow-500 mr-2"></span> On Leave</div>
          <div><span class="w-3 h-3 inline-block rounded-full bg-red-500 mr-2"></span> On Call</div>
        </div>
        <div>
          <div class="font-semibold mb-1">Status</div>
          <div><span class="conflict-icon" aria-hidden="true">&#9888;</span> Scheduling Conflict</div>
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
            <p class="text-sm font-semibold">Nurse 1</p>
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
            <p class="text-sm font-semibold">Nurse 1</p>
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
          role: 'Nurse 1',
          description: 'Nurse 1 is booked for two conflicting shifts in Emergency and General departments at the same time. Resolution required to avoid overlap.'
        },
        2: {
          title: 'Double Booking - General Shift',
          date: '2025-08-13',
          time: '6:00 am - 2:00 pm',
          role: 'Nurse 1',
          description: 'Nurse 1 is booked for two conflicting shifts in General and Emergency departments at the same time. Resolution required to avoid overlap.'
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
    viewButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        viewButtons.forEach(b => {
          b.classList.remove('bg-gray-200');
          b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('bg-gray-200');
        btn.setAttribute('aria-pressed', 'true');
        alert(`View mode changed to: ${btn.getAttribute('data-view')}`);
      });
    });
  </script>

  <!-- Add Shift Modal -->
  <div id="addShiftModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <div class="p-6">
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
            <select id="staffName" name="staffName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              <option value="">Select staff member</option>
              <option value="nurse1">Nurse 1 - Emergency</option>
              <option value="nurse2">Nurse 2 - General</option>
              <option value="nurse3">Nurse 3 - Emergency</option>
              <option value="nurse4">Nurse 4 - General</option>
              <option value="nurse5">Nurse 5 - Emergency</option>
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
              <label for="shiftRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select id="shiftRole" name="shiftRole" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="nurse_emergency">🚑 Emergency Department</option>
                <option value="nurse_general">🏥 General Ward</option>
                <option value="nurse_pediatrics">👶 Pediatrics</option>
                <option value="nurse_icu">💊 Intensive Care Unit (ICU)</option>
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
        const staffName = document.getElementById('staffName').value;
        const shiftDate = document.getElementById('shiftDate').value;
        const shiftType = document.getElementById('shiftType').value;
        const shiftRole = document.getElementById('shiftRole').value;
        
        // Here you would typically send this data to your server
        console.log('Adding shift:', { staffName, shiftDate, shiftType, shiftRole });
        
        // Show success message
        alert('Shift added successfully!');
        
        // Close modal and reset form
        closeModal();
        addShiftForm.reset();
        
        // In a real application, you would refresh the schedule or add the new shift to the DOM
      });

      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('shiftDate').min = today;
    });
  </script>
</body>
</html>
