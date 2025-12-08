<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Staff Schedule & Shift Management<?= $this->endSection() ?>

<?php
// Helper function to get department color class
function getDepartmentColorClass($department) {
    if (empty($department)) return 'dept-general';
    
    $dept = strtolower(trim($department));
    
    // Map department names to color classes
    if (strpos($dept, 'emergency') !== false) {
        return 'dept-emergency';
    }
    if (strpos($dept, 'cardiology') !== false) {
        return 'dept-cardiology';
    }
    if (strpos($dept, 'neurology') !== false) {
        return 'dept-neurology';
    }
    if (strpos($dept, 'orthopedic') !== false) {
        return 'dept-orthopedics';
    }
    if (strpos($dept, 'pediatric') !== false) {
        return 'dept-pediatrics';
    }
    if (strpos($dept, 'general') !== false) {
        return 'dept-general';
    }
    
    // Default fallback
    return 'dept-general';
}
?>

<?= $this->section('content') ?>
<script src="https://cdn.tailwindcss.com"></script>
<div class="container-fluid py-4">
  <div class="composite-card billing-card" style="margin-top:0; background: #fff;">
    <div class="composite-header">
      <h1 class="composite-title"><?= (isset($isReadOnly) && $isReadOnly) || session('role') === 'doctor' ? 'My Schedule' : 'Doctor Schedule' ?></h1>
      <div class="flex gap-3 items-center">
        <?php if (!isset($isReadOnly) || !$isReadOnly): ?>
        <button id="btnAddShift" class="bg-gray-800 text-white rounded px-3 py-1 hover:bg-gray-900 text-sm">+ Add Shift</button>
        <?php endif; ?>
        <div>
          <button type="button" data-view="day" class="btn-view-mode px-2 py-1 border border-gray-300 rounded-l hover:bg-gray-200 text-sm bg-gray-200" aria-pressed="true">Day</button>
          <button type="button" data-view="week" class="btn-view-mode px-2 py-1 border-t border-b border-gray-300 hover:bg-gray-200 text-sm">Week</button>
          <button type="button" data-view="month" class="btn-view-mode px-2 py-1 border border-gray-300 rounded-r hover:bg-gray-200 text-sm">Month</button> 
        </div>
      </div>
    </div>
    <div class="card-body">
      <!-- Day View -->
      <div id="dayView" class="view-container">
        <?php $request = \Config\Services::request(); $currentDateYmd = $request->getGet('date') ?? date('Y-m-d'); ?>
        <div class="mb-4" data-current-date="<?= esc($currentDateYmd) ?>">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Schedule for <?= date('l, F j, Y', strtotime($currentDateYmd)) ?></h2>
            <div class="flex gap-2">
              <button id="btnDayToday" class="px-3 py-1 border rounded hover:bg-gray-100 text-sm">Today</button>
              <button id="btnDayPrev" class="px-2 py-1 border rounded hover:bg-gray-100" aria-label="Previous day">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button id="btnDayNext" class="px-2 py-1 border rounded hover:bg-gray-100" aria-label="Next day">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
          
          <div class="bg-white rounded-lg border overflow-hidden">
            <div class="grid grid-cols-12 border-b bg-gray-50">
              <div class="col-span-4 p-3 font-medium">TIME</div>
              <div class="col-span-8 p-3 font-medium">EVENT</div>
            </div>
            
            <div class="divide-y divide-gray-200">
            <?php
            // Only current day's schedules (from query parameter or today)
            $dateLabel = date('D M j', strtotime($currentDateYmd));
            $todaySchedules = array_filter($schedules, function($schedule) use ($currentDateYmd) {
              return isset($schedule['shift_date']) && date('Y-m-d', strtotime($schedule['shift_date'])) === $currentDateYmd;
            });
            ?>
            <?php if (!empty($todaySchedules)): ?>
              <?php $rowCount = count($todaySchedules); $index = 0; ?>
              <?php foreach ($todaySchedules as $schedule): ?>
                <div class="grid grid-cols-12 hover:bg-gray-50">
                  <div class="col-span-4 p-3 border-r border-gray-100">
                    <?= date('g:i a', strtotime($schedule['start_time'] ?? '00:00:00')) ?> - 
                    <?= date('g:i a', strtotime($schedule['end_time'] ?? '00:00:00')) ?>
                  </div>
                  <div class="col-span-8 p-3">
                    <div class="shift <?= getDepartmentColorClass($schedule['department'] ?? '') ?> text-xs p-2 rounded"
                         data-schedule-id="<?= $schedule['id'] ?>">
                      <div class="font-medium"><?= htmlspecialchars($schedule['doctor_name']) ?></div>
                      <div class="opacity-90 text-xs mt-0.5"><?= ucfirst($schedule['shift_type']) ?> • <?= htmlspecialchars($schedule['department'] ?? 'General') ?></div>
                    </div>
                  </div>
                </div>
                <?php $index++; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="grid grid-cols-12 hover:bg-gray-50">
                <div class="col-span-12 p-3 text-gray-400">No scheduled shifts</div>
              </div>
            <?php endif; ?>
            </div>
          </div>
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
                  <?php 
                  $startTime = isset($schedule['start_time']) ? date('g:i A', strtotime($schedule['start_time'])) : '';
                  $endTime = isset($schedule['end_time']) ? date('g:i A', strtotime($schedule['end_time'])) : '';
                  $timeDisplay = $startTime && $endTime ? $startTime . ' - ' . $endTime : ($startTime ? $startTime : '');
                  $doctorName = htmlspecialchars($schedule['doctor_name'] ?? '');
                  ?>
                  <div class="shift <?= getDepartmentColorClass($schedule['department'] ?? '') ?> text-xs p-1.5 rounded cursor-pointer hover:opacity-90 transition-opacity" 
                       data-schedule-id="<?= $schedule['id'] ?>"
                       title="<?= htmlspecialchars($doctorName . ' - ' . ($schedule['department'] ?? 'General') . ' (' . $timeDisplay . ')') ?>">
                    <div class="font-medium truncate"><?= $doctorName ?></div>
                    <?php if ($timeDisplay): ?>
                      <div class="text-[10px] opacity-90 mt-0.5 truncate"><?= $timeDisplay ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Month View -->
      <div id="monthView" class="view-container hidden relative">
        <!-- Loading Overlay -->
        <div id="monthViewLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50 hidden">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
            <p class="text-sm text-gray-600">Loading calendar...</p>
          </div>
        </div>
        
        <?php 
        $monthParam = $request->getGet('month');
        // Validate and sanitize month parameter
        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
          $monthNum = (int)substr($monthParam, 5, 2);
          if ($monthNum >= 1 && $monthNum <= 12) {
            $viewMonth = date('Y-m-01', strtotime($monthParam . '-01'));
          } else {
            $monthParam = date('Y-m');
            $viewMonth = date('Y-m-01');
          }
        } else {
          $monthParam = date('Y-m');
          $viewMonth = date('Y-m-01');
        }
        $prevMonth = date('Y-m', strtotime($viewMonth . ' -1 month'));
        $nextMonth = date('Y-m', strtotime($viewMonth . ' +1 month'));
        $currentMonth = date('Y-m');
        ?>
        <div class="mb-4">
          <div class="flex justify-center items-center gap-3 mb-4">
            <button type="button" id="btnMonthPrev" class="p-2 border rounded hover:bg-gray-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" data-month="<?= $prevMonth ?>" title="Previous Month">
              <i class="fas fa-chevron-left"></i>
            </button>
            <h2 class="text-lg font-semibold">Month View - <?= date('F Y', strtotime($viewMonth)) ?></h2>
            <button type="button" id="btnMonthNext" class="p-2 border rounded hover:bg-gray-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" data-month="<?= $nextMonth ?>" title="Next Month">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-7 gap-1 text-sm">
          <!-- Calendar headers -->
          <?php $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; ?>
          <?php foreach ($weekDays as $day): ?>
            <div class="p-2 text-center font-semibold text-gray-600 bg-gray-100"><?= $day ?></div>
          <?php endforeach; ?>
          
          <!-- Calendar days -->
          <?php 
          $firstDay = date('Y-m-01', strtotime($viewMonth));
          $lastDay = date('Y-m-t', strtotime($viewMonth));
          $startDate = date('Y-m-d', strtotime('last sunday', strtotime($firstDay)));
          $endDate = date('Y-m-d', strtotime('next saturday', strtotime($lastDay)));
          
          $currentDate = $startDate;
          $viewMonthNum = date('m', strtotime($viewMonth));
          $viewYear = date('Y', strtotime($viewMonth));
          $today = date('Y-m-d');
          
          while ($currentDate <= $endDate):
            $isCurrentMonth = date('m', strtotime($currentDate)) === $viewMonthNum;
            $isToday = $currentDate === $today;
            $daySchedules = array_filter($schedules, function($schedule) use ($currentDate) {
              return isset($schedule['shift_date']) && date('Y-m-d', strtotime($schedule['shift_date'])) === $currentDate;
            });
          ?>
            <div class="border min-h-[100px] p-1 <?= $isCurrentMonth ? 'bg-white' : 'bg-gray-50' ?> <?= $isToday ? 'ring-2 ring-blue-500' : '' ?> calendar-day-cell <?= count($daySchedules) > 0 ? 'cursor-pointer hover:bg-gray-50' : '' ?>" 
                 data-date="<?= $currentDate ?>" 
                 <?= count($daySchedules) > 0 ? 'onclick="showDateSchedules(\'' . $currentDate . '\')"' : '' ?>>
              <div class="text-xs <?= $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' ?> mb-1 <?= $isToday ? 'font-bold text-blue-600' : '' ?>">
                <?= date('j', strtotime($currentDate)) ?>
              </div>
              <div class="space-y-1">
                <?php foreach (array_slice($daySchedules, 0, 2) as $schedule): ?>
                  <?php 
                  $startTime = isset($schedule['start_time']) ? date('g:i A', strtotime($schedule['start_time'])) : '';
                  $endTime = isset($schedule['end_time']) ? date('g:i A', strtotime($schedule['end_time'])) : '';
                  $timeDisplay = $startTime && $endTime ? $startTime . ' - ' . $endTime : ($startTime ? $startTime : '');
                  $doctorName = htmlspecialchars($schedule['doctor_name'] ?? '');
                  ?>
                  <div class="shift <?= getDepartmentColorClass($schedule['department'] ?? '') ?> text-xs p-1.5 rounded cursor-pointer hover:opacity-90 transition-opacity" 
                       data-schedule-id="<?= $schedule['id'] ?>"
                       title="<?= htmlspecialchars($doctorName . ' - ' . ($schedule['department'] ?? 'General') . ' (' . $timeDisplay . ')') ?>"
                       onclick="event.stopPropagation();">
                    <div class="font-medium truncate"><?= $doctorName ?></div>
                  </div>
                <?php endforeach; ?>
                <?php if (count($daySchedules) > 2): ?>
                  <div class="text-xs text-gray-500 px-1 font-semibold">+<?= count($daySchedules) - 2 ?> more</div>
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
      <?php if (!empty($conflicts)): ?>
        <?php foreach ($conflicts as $idx => $c): ?>
          <?php
            $title = 'Double Booking - ' . htmlspecialchars($c['department'] ?? '');
            $date = date('Y-m-d', strtotime($c['shift_date'] ?? ''));
            // Display the primary shift time; note: conflict_start/conflict_end reflect the overlapping shift
            $timeLabel = date('g:i a', strtotime($c['start_time'] ?? '00:00:00')) . ' - ' . date('g:i a', strtotime($c['end_time'] ?? '00:00:00'));
            $role = htmlspecialchars($c['doctor_name'] ?? 'Doctor');
            $desc = $role . ' has overlapping shifts on ' . $date . ' between ' . $timeLabel . ' and another from ' . date('g:i a', strtotime($c['conflict_start'] ?? '00:00:00')) . ' - ' . date('g:i a', strtotime($c['conflict_end'] ?? '00:00:00')) . '.';
          ?>
          <article class="bg-gray-200 p-3 rounded shadow-sm" tabindex="0" role="button"
                   data-title="<?= $title ?>"
                   data-date="<?= $date ?>"
                   data-time="<?= $timeLabel ?>"
                   data-role="<?= $role ?>"
                   data-description="<?= htmlspecialchars($desc) ?>">
            <div class="inline-flex items-baseline gap-2 mb-1">
              <span class="bg-gray-300 rounded-full text-xs font-bold px-2 py-0.5">Double Booking</span>
              <time class="text-xs text-gray-600 ml-auto"><?= $date ?></time>
            </div>
            <div>
              <p class="font-bold"><?= htmlspecialchars($c['department'] ?? '') ?></p>
              <p class="text-sm"><?= $timeLabel ?></p>
              <p class="text-sm font-semibold"><?= $role ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-sm text-gray-500">No conflicts detected.</div>
      <?php endif; ?>
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

  // Attach listener only if the button exists to avoid runtime errors
  if (btnConflictSchedules) {
    btnConflictSchedules.addEventListener('click', () => {
      const count = parseInt(btnConflictSchedules.getAttribute('data-conflict-count') || '0', 10);
      if (count > 0) {
        openConflictModal();
      }
    });
  }

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
    const title = conflictItem.getAttribute('data-title') || 'Conflict';
    const date = conflictItem.getAttribute('data-date') || '';
    const time = conflictItem.getAttribute('data-time') || '';
    const role = conflictItem.getAttribute('data-role') || '';
    const description = conflictItem.getAttribute('data-description') || '';
    conflictDetailsPanel.innerHTML = `
      <h3 class="font-semibold mb-2">${title}</h3>
      <p><strong>Date:</strong> ${date}</p>
      <p><strong>Time:</strong> ${time}</p>
      <p><strong>Role:</strong> ${role}</p>
      <p class="mt-3">${description}</p>
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
        b.classList.remove('bg-blue-600', 'text-white', 'bg-gray-200');
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

  // Day View navigation (Today, Prev, Next)
  const dayMeta = document.querySelector('#dayView [data-current-date]');
  const btnDayToday = document.getElementById('btnDayToday');
  const btnDayPrev = document.getElementById('btnDayPrev');
  const btnDayNext = document.getElementById('btnDayNext');

  function navigateToDate(targetYmd) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', targetYmd);
    // Remove month parameter when switching to day view
    url.searchParams.delete('month');
    // Ensure we're in day view
    url.searchParams.set('view', 'day');
    window.location.href = url.toString();
  }
  function formatYMD(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
  }

  if (dayMeta) {
    const currentYmd = dayMeta.getAttribute('data-current-date');
    if (btnDayToday) {
      btnDayToday.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Navigate to today's date and switch to day view
        navigateToDate(formatYMD(new Date()));
      });
    }
    if (btnDayPrev) {
      btnDayPrev.addEventListener('click', () => {
        const d = new Date(currentYmd);
        d.setDate(d.getDate() - 1);
        navigateToDate(formatYMD(d));
      });
    }
    if (btnDayNext) {
      btnDayNext.addEventListener('click', () => {
        const d = new Date(currentYmd);
        d.setDate(d.getDate() + 1);
        navigateToDate(formatYMD(d));
      });
    }
  }

  // Month View navigation (Previous, Next, Today)
  function formatMonth(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    return `${y}-${m}`;
  }

  function getCurrentMonthFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const monthParam = urlParams.get('month');
    if (monthParam) {
      // Validate format YYYY-MM
      if (/^\d{4}-\d{2}$/.test(monthParam)) {
        // Validate month is between 01-12
        const month = parseInt(monthParam.split('-')[1], 10);
        if (month >= 1 && month <= 12) {
          return monthParam;
        }
      }
    }
    // If no valid month in URL, default to current month
    return formatMonth(new Date());
  }

  function getPreviousMonth(currentMonth) {
    const [year, month] = currentMonth.split('-').map(Number);
    let prevYear = year;
    let prevMonth = month - 1;
    
    if (prevMonth < 1) {
      prevMonth = 12;
      prevYear = year - 1;
    }
    
    return `${prevYear}-${String(prevMonth).padStart(2, '0')}`;
  }

  function getNextMonth(currentMonth) {
    const [year, month] = currentMonth.split('-').map(Number);
    let nextYear = year;
    let nextMonth = month + 1;
    
    if (nextMonth > 12) {
      nextMonth = 1;
      nextYear = year + 1;
    }
    
    return `${nextYear}-${String(nextMonth).padStart(2, '0')}`;
  }

  function navigateToMonth(monthYmd) {
    // Validate month format before navigating
    if (!/^\d{4}-\d{2}$/.test(monthYmd)) {
      console.error('Invalid month format:', monthYmd);
      return;
    }
    
    // Show loading state
    const loadingOverlay = document.getElementById('monthViewLoading');
    const btnMonthPrev = document.getElementById('btnMonthPrev');
    const btnMonthNext = document.getElementById('btnMonthNext');
    
    if (loadingOverlay) {
      loadingOverlay.classList.remove('hidden');
    }
    
    // Disable buttons to prevent multiple clicks
    if (btnMonthPrev) {
      btnMonthPrev.disabled = true;
      btnMonthPrev.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (btnMonthNext) {
      btnMonthNext.disabled = true;
      btnMonthNext.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    const url = new URL(window.location.href);
    url.searchParams.set('month', monthYmd);
    // Remove date parameter when switching to month view
    url.searchParams.delete('date');
    
    // Small delay to show loading state, then navigate
    setTimeout(() => {
      window.location.href = url.toString();
    }, 100);
  }

  // Set up event listeners when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    const btnMonthPrev = document.getElementById('btnMonthPrev');
    const btnMonthNext = document.getElementById('btnMonthNext');
    
    // Check URL parameters to determine which view to show
    const urlParams = new URLSearchParams(window.location.search);
    const monthParam = urlParams.get('month');
    const dateParam = urlParams.get('date');
    const viewParam = urlParams.get('view');
    
    const monthView = document.getElementById('monthView');
    const dayView = document.getElementById('dayView');
    const weekView = document.getElementById('weekView');
    
    // If date parameter exists, show day view
    if (dateParam && dayView && monthView && weekView) {
      dayView.classList.remove('hidden');
      weekView.classList.add('hidden');
      monthView.classList.add('hidden');
      
      // Update button states
      const dayViewBtn = document.querySelector('[data-view="day"]');
      if (dayViewBtn) {
        document.querySelectorAll('.btn-view-mode').forEach(b => {
          b.classList.remove('bg-blue-600', 'text-white');
          b.classList.add('bg-white', 'text-gray-700');
        });
        dayViewBtn.classList.remove('bg-white', 'text-gray-700');
        dayViewBtn.classList.add('bg-blue-600', 'text-white');
      }
    }
    // If month parameter exists in URL, show month view
    else if (monthParam && monthView && dayView && weekView) {
      dayView.classList.add('hidden');
      weekView.classList.add('hidden');
      monthView.classList.remove('hidden');
      
      // Update button states
      const monthViewBtn = document.querySelector('[data-view="month"]');
      if (monthViewBtn) {
        document.querySelectorAll('.btn-view-mode').forEach(b => {
          b.classList.remove('bg-blue-600', 'text-white');
          b.classList.add('bg-white', 'text-gray-700');
        });
        monthViewBtn.classList.remove('bg-white', 'text-gray-700');
        monthViewBtn.classList.add('bg-blue-600', 'text-white');
      }
    }
    // If view parameter is explicitly set
    else if (viewParam && dayView && monthView && weekView) {
      dayView.classList.add('hidden');
      weekView.classList.add('hidden');
      monthView.classList.add('hidden');
      
      const viewBtn = document.querySelector(`[data-view="${viewParam}"]`);
      if (viewBtn) {
        document.querySelectorAll('.btn-view-mode').forEach(b => {
          b.classList.remove('bg-blue-600', 'text-white');
          b.classList.add('bg-white', 'text-gray-700');
        });
        viewBtn.classList.remove('bg-white', 'text-gray-700');
        viewBtn.classList.add('bg-blue-600', 'text-white');
        
        if (viewParam === 'day') dayView.classList.remove('hidden');
        else if (viewParam === 'week') weekView.classList.remove('hidden');
        else if (viewParam === 'month') monthView.classList.remove('hidden');
      }
    }
    
    // Hide loading overlay when page is fully loaded
    window.addEventListener('load', function() {
      const loadingOverlay = document.getElementById('monthViewLoading');
      if (loadingOverlay) {
        loadingOverlay.classList.add('hidden');
      }
      
      // Re-enable buttons
      const btnMonthPrev = document.getElementById('btnMonthPrev');
      const btnMonthNext = document.getElementById('btnMonthNext');
      
      if (btnMonthPrev) {
        btnMonthPrev.disabled = false;
        btnMonthPrev.classList.remove('opacity-50', 'cursor-not-allowed');
      }
      if (btnMonthNext) {
        btnMonthNext.disabled = false;
        btnMonthNext.classList.remove('opacity-50', 'cursor-not-allowed');
      }
    });

    if (btnMonthPrev) {
      btnMonthPrev.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const currentMonth = getCurrentMonthFromURL();
        const prevMonth = getPreviousMonth(currentMonth);
        navigateToMonth(prevMonth);
      });
    }

    if (btnMonthNext) {
      btnMonthNext.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const currentMonth = getCurrentMonthFromURL();
        const nextMonth = getNextMonth(currentMonth);
        navigateToMonth(nextMonth);
      });
    }
  });

</script>

<!-- Add Shift Modal -->
<div id="addShiftModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
  <div class="bg-white max-w-lg w-full mx-4 rounded-xl shadow-2xl relative overflow-hidden">
    <!-- Modal Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 border-b border-blue-800">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add New Shift
        </h3>
        <button id="closeAddShiftModal" class="text-white hover:text-gray-200 transition-colors duration-200 rounded-full p-1 hover:bg-blue-800">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <form id="addShiftForm" class="p-6">
      <div id="addShiftError" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-r hidden">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-sm font-medium"></span>
        </div>
      </div>

      <!-- Doctor Selection -->
      <div class="mb-5 relative">
        <label for="doctorSelect" class="block text-sm font-semibold text-gray-700 mb-2">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Doctor
          </span>
        </label>
        <div class="relative">
          <select 
            id="doctorSelect" 
            name="doctor_id" 
            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-gray-700 font-medium" 
            required
          >
            <option value="">Select a doctor...</option>
            <?php if (isset($doctors) && !empty($doctors)): ?>
              <?php foreach ($doctors as $doctor): ?>
                <option 
                  value="<?= esc($doctor['doctor_id']) ?>" 
                  data-name="<?= esc(ucfirst(str_replace('dr.', 'Dr. ', $doctor['username']))) ?>"
                  data-specialization="<?= esc($doctor['specialization'] ?? '') ?>"
                  data-department="<?= esc($doctor['department'] ?? '') ?>"
                  data-department-slug="<?= esc($doctor['department_slug'] ?? '') ?>"
                >
                  <?= esc(ucfirst(str_replace('dr.', 'Dr. ', $doctor['username']))) ?>
                  <?php if (!empty($doctor['specialization'])): ?>
                    - <?= esc($doctor['specialization']) ?>
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <input type="hidden" id="doctorName" name="doctor_name" value="">
        </div>
      </div>

      <!-- Auto-filled Information Section -->
      <div class="mb-5 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Doctor Information</span>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Specialization</label>
            <input type="text" id="specializationDisplay" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-700 font-medium shadow-sm" readonly placeholder="—">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Department</label>
            <div id="departmentDisplay" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg min-h-[2.75rem] flex items-center text-sm font-semibold shadow-sm"></div>
            <input type="hidden" id="department" name="department" value="">
          </div>
        </div>
      </div>
      
      <!-- Schedule Range -->
      <div class="mb-5">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Schedule Range
          </span>
        </label>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="startDate" class="block text-xs font-medium text-gray-600 mb-1.5">Start Date</label>
            <input type="date" id="startDate" name="startDate" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-gray-700 font-medium" required>
          </div>
          <div>
            <label for="endDate" class="block text-xs font-medium text-gray-600 mb-1.5">End Date</label>
            <input type="date" id="endDate" name="endDate" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-gray-700 font-medium" required>
          </div>
        </div>
      </div>
      
      <!-- Shift Type -->
      <div class="mb-6">
        <label for="shiftType" class="block text-sm font-semibold text-gray-700 mb-2">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Shift Type
          </span>
        </label>
        <select id="shiftType" name="shiftType" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-gray-700 font-medium" required>
          <optgroup label="Morning Shifts">
            <option value="morning_06_12">Morning (06:00 AM – 12:00 PM)</option>
            <option value="morning_07_11">Morning (07:00 AM – 11:00 AM)</option>
            <option value="morning_08_12">Morning (08:00 AM – 12:00 PM)</option>
          </optgroup>
          <optgroup label="Afternoon Shifts">
            <option value="afternoon_12_18">Afternoon (12:00 PM – 06:00 PM)</option>
            <option value="afternoon_13_17">Afternoon (01:00 PM – 05:00 PM)</option>
            <option value="afternoon_14_18">Afternoon (02:00 PM – 06:00 PM)</option>
          </optgroup>
          <optgroup label="Evening Shifts">
            <option value="evening_16_22">Evening (04:00 PM – 10:00 PM)</option>
            <option value="evening_17_21">Evening (05:00 PM – 09:00 PM)</option>
          </optgroup>
          <optgroup label="Night Shifts">
            <option value="night_22_06">Night (10:00 PM – 06:00 AM)</option>
            <option value="night_23_07">Night (11:00 PM – 07:00 AM)</option>
          </optgroup>
          <optgroup label="Full Day Shifts">
            <option value="full_day_08_17">Full Day (08:00 AM – 05:00 PM)</option>
            <option value="full_day_09_18">Full Day (09:00 AM – 06:00 PM)</option>
          </optgroup>
          <optgroup label="Half-Day Shifts">
            <option value="half_day_08_12">Half Day (08:00 AM – 12:00 PM)</option>
            <option value="half_day_13_17">Half Day (01:00 PM – 05:00 PM)</option>
          </optgroup>
          <optgroup label="Split Shifts">
            <option value="split_08_12_14_18">Split Shift (08:00 AM – 12:00 PM and 02:00 PM – 06:00 PM)</option>
          </optgroup>
        </select>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
        <button type="button" id="cancelAddShift" class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
          Cancel
        </button>
        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md hover:shadow-lg transition-all duration-200">
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
    
    // Define doctor-related variables early to avoid scope issues
    const doctorSelect = document.getElementById('doctorSelect');
    const doctorName = document.getElementById('doctorName');
    const specializationDisplay = document.getElementById('specializationDisplay');
    const departmentDisplay = document.getElementById('departmentDisplay');
    const departmentHidden = document.getElementById('department');

    // Open modal
    btnAddShift.addEventListener('click', function() {
      addShiftModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
    });

    // Close modal
    function closeModal() {
      addShiftModal.classList.add('hidden');
      document.body.style.overflow = ''; // Re-enable scrolling
      // Reset doctor select
      if (doctorSelect) {
        doctorSelect.value = '';
      }
      if (doctorName) {
        doctorName.value = '';
      }
      if (specializationDisplay) {
        specializationDisplay.value = '';
      }
      if (departmentDisplay) {
        departmentDisplay.className = 'w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 min-h-[2.5rem] flex items-center text-sm font-medium';
        departmentDisplay.textContent = '';
      }
      if (departmentHidden) {
        departmentHidden.value = '';
      }
    }

    closeAddShiftModal.addEventListener('click', closeModal);
    cancelAddShift.addEventListener('click', closeModal);


    // Close modal when clicking outside
    addShiftModal.addEventListener('click', function(e) {
      if (e.target === addShiftModal) {
        closeModal();
      }
    });

    // Function to get department color class and text color (GLOBAL SCOPE)
    window.getDepartmentColorClass = function(departmentSlug, departmentName) {
      if (!departmentSlug && !departmentName) return { class: 'dept-general', textColor: 'text-white' };
      
      const slug = (departmentSlug || '').toLowerCase();
      const name = (departmentName || '').toLowerCase();
      
      // Map department slugs/names to color classes
      if (slug.includes('emergency') || name.includes('emergency')) {
        return { class: 'dept-emergency', textColor: 'text-white' };
      }
      if (slug.includes('cardiology') || name.includes('cardiology')) {
        return { class: 'dept-cardiology', textColor: 'text-red-900' };
      }
      if (slug.includes('neurology') || name.includes('neurology')) {
        return { class: 'dept-neurology', textColor: 'text-white' };
      }
      if (slug.includes('orthopedic') || name.includes('orthopedic')) {
        return { class: 'dept-orthopedics', textColor: 'text-white' };
      }
      if (slug.includes('pediatric') || name.includes('pediatric')) {
        return { class: 'dept-pediatrics', textColor: 'text-white' };
      }
      if (slug.includes('general') || name.includes('general')) {
        return { class: 'dept-general', textColor: 'text-white' };
      }
      
      // Default fallback
      return { class: 'dept-general', textColor: 'text-white' };
    };

    // Handle doctor select change
    if (doctorSelect) {
      doctorSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const doctorNameField = document.getElementById('doctorName');
        
        if (selectedOption.value) {
          // Set doctor name in hidden field
          if (doctorNameField) {
            doctorNameField.value = selectedOption.getAttribute('data-name') || '';
          }
          
          // Update specialization and department
          const specialization = selectedOption.getAttribute('data-specialization') || '';
          const department = selectedOption.getAttribute('data-department') || '';
          const departmentSlug = selectedOption.getAttribute('data-department-slug') || '';
          
          if (specializationDisplay) {
            specializationDisplay.value = specialization;
          }
          
          if (departmentHidden) {
            departmentHidden.value = department;
          }
          
          if (departmentDisplay) {
            if (department) {
              const colorInfo = getDepartmentColorClass(departmentSlug, department);
              departmentDisplay.className = `w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg min-h-[2.75rem] flex items-center text-sm font-semibold shadow-sm ${colorInfo.class} ${colorInfo.textColor}`;
              departmentDisplay.textContent = department;
            } else {
              departmentDisplay.className = 'w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg bg-white min-h-[2.75rem] flex items-center text-sm font-medium shadow-sm';
              departmentDisplay.textContent = '—';
            }
          }
        } else {
          // Reset if no doctor selected
          if (doctorName) doctorName.value = '';
          if (specializationDisplay) specializationDisplay.value = '';
          if (departmentDisplay) {
            departmentDisplay.className = 'w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 min-h-[2.5rem] flex items-center text-sm font-medium';
            departmentDisplay.textContent = '';
          }
          if (departmentHidden) departmentHidden.value = '';
        }
      });
    }

    // Form submission
    addShiftForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form values
      const doctorSelect = document.getElementById('doctorSelect');
      const doctorId = doctorSelect.value;
      let doctorName = document.getElementById('doctorName').value;
      
      // If doctor name is empty, get it from the selected option
      if (!doctorName && doctorId) {
        const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
        doctorName = selectedOption.getAttribute('data-name') || selectedOption.textContent.trim();
      }
      
      const startDate = document.getElementById('startDate').value;
      const endDate = document.getElementById('endDate').value;
      const shiftType = document.getElementById('shiftType').value;
      const department = document.getElementById('department').value;

      // Validate form with specific missing fields
      const missing = [];
      if (!doctorId) missing.push('Doctor');
      if (!doctorName) missing.push('Doctor Name');
      if (!startDate) missing.push('Start Date');
      if (!endDate) missing.push('End Date');
      if (!shiftType) missing.push('Shift Type');
      if (!department) missing.push('Department');
      if (missing.length) {
        showAddShiftError('Missing: ' + missing.join(', ') + '.');
        return;
      }

      // Validate that dates are not in the past
      hideAddShiftError();
      const now = new Date();
      const todayStr = new Date().toISOString().split('T')[0];
      const todayDate = new Date(todayStr);
      const startDateObj = new Date(startDate);
      const endDateObj = new Date(endDate);
      
      // Prevent past dates
      if (startDateObj < todayDate) {
        showAddShiftError('Start date cannot be in the past. Please select today or a future date.');
        return;
      }
      
      if (endDateObj < todayDate) {
        showAddShiftError('End date cannot be in the past. Please select today or a future date.');
        return;
      }

      // Validate date range - ensure logical order (start <= end)
      if (startDateObj > endDateObj) {
        showAddShiftError('Start date must be before or equal to end date.');
        return;
      }

      // Client-side guard: prevent past-time submission for today
      if (startDate === todayStr) {
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const currentTime = currentHour * 60 + currentMinute; // Convert to minutes for easier comparison
        
        // Extract start hour from shift type value (format: type_HH_MM or type_HH_MM_HH_MM)
        let shiftStartHour = 0;
        const parts = shiftType.split('_');
        if (parts.length >= 2 && !isNaN(parseInt(parts[parts.length - 2]))) {
          shiftStartHour = parseInt(parts[parts.length - 2]);
        } else if (parts.length >= 3 && !isNaN(parseInt(parts[1]))) {
          shiftStartHour = parseInt(parts[1]);
        }
        
        // For split shifts, check the first block start time
        if (shiftType.startsWith('split_')) {
          const splitParts = shiftType.split('_');
          if (splitParts.length >= 3) {
            shiftStartHour = parseInt(splitParts[1]);
          }
        }
        
        // Check if shift start time has passed
        if (shiftStartHour > 0 && currentTime >= (shiftStartHour * 60)) {
          showAddShiftError('Selected shift time has already passed today. Please choose a future shift.');
          return;
        }
      }
      
      // Prepare request data
      const requestData = {
        doctor_id: doctorId,
        doctor_name: doctorName,
        start_date: startDate,
        end_date: endDate,
        shift_type: shiftType,
        department: department
      };
      
      // Add CSRF token
      requestData['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
      
      console.log('Sending add schedule request:', requestData);
      
      // Send AJAX request to add schedule
      fetch('<?= base_url('/doctor/addSchedule') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(requestData)
      })
      .then(async response => {
        // Try to parse JSON response even if status is not ok
        let data;
        try {
          data = await response.json();
        } catch (e) {
          // If JSON parsing fails, throw with response status
          throw new Error(`Server error (${response.status}): ${response.statusText}`);
        }
        
        console.log('Add Schedule Response:', data);
        
        if (data.success) {
          const message = data.message || 'Doctor schedule successfully added for all valid dates.';
          alert(message);
          closeModal();
          addShiftForm.reset();
          if (specializationDisplay) specializationDisplay.value = '';
          if (departmentDisplay) {
            departmentDisplay.className = 'w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg bg-white min-h-[2.75rem] flex items-center text-sm font-medium shadow-sm';
            departmentDisplay.textContent = '—';
          }
          if (departmentHidden) departmentHidden.value = '';
          // Reload the page to show the new schedule
          window.location.reload();
        } else {
          // Show server error message
          if (data.conflicts && data.conflicts.length > 0) {
            showAddShiftError('This doctor already has a schedule during the selected date and shift.');
          } else if ((data.message || '').toLowerCase().includes('past date')) {
            showAddShiftError('Selected shift time has already passed today. Please choose a future shift.');
          } else {
            showAddShiftError(data.message || 'Failed to add schedule');
          }
        }
      })
      .catch(error => {
        console.error('Error adding schedule:', error);
        showAddShiftError('An error occurred while adding the schedule: ' + error.message);
      });
    });

    // Set minimum date to today (prevent past dates)
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const todayStr = today.toISOString().split('T')[0];
    const startDateEl = document.getElementById('startDate');
    const endDateEl = document.getElementById('endDate');
    const shiftTypeEl = document.getElementById('shiftType');
    
    // Set minimum to today for both fields
    startDateEl.min = todayStr;
    endDateEl.min = todayStr;
    
    // Set default values to today if empty
    if (!startDateEl.value) {
      startDateEl.value = todayStr;
    }
    if (!endDateEl.value) {
      endDateEl.value = todayStr;
    }

    // End date can be set to any future date (not constrained by start date)
    // But we still validate in form submission that start <= end
    startDateEl.addEventListener('change', function() {
      updateShiftTypeOptions();
    });

    endDateEl.addEventListener('change', function() {
      // Only validate that end date is not in the past (already handled by min attribute)
      // Don't constrain it based on start date - user can set any future date
      updateShiftTypeOptions();
    });

    // Disable past shift types for today's date based on current time
    function updateShiftTypeOptions() {
      if (!shiftTypeEl) return;
      const selectedDate = startDateEl.value;
      const now = new Date();
      const currentHour = now.getHours();
      const currentMinute = now.getMinutes();
      const currentTime = currentHour * 60 + currentMinute; // Convert to minutes
      const isToday = selectedDate === todayStr;
      const options = shiftTypeEl.querySelectorAll('option');

      options.forEach(opt => {
        if (!opt.value) return; // skip placeholder
        let shouldDisable = false;
        if (isToday) {
          // Extract start hour from shift type value
          const shiftType = opt.value;
          let shiftStartHour = 0;
          const parts = shiftType.split('_');
          
          // Handle split shifts (format: split_HH_MM_HH_MM)
          if (shiftType.startsWith('split_')) {
            if (parts.length >= 3) {
              shiftStartHour = parseInt(parts[1]);
            }
          } else if (parts.length >= 2) {
            // Try to get hour from second-to-last part (for formats like morning_06_12)
            const hourPart = parts[parts.length - 2];
            if (!isNaN(parseInt(hourPart)) && parseInt(hourPart) < 24) {
              shiftStartHour = parseInt(hourPart);
            } else if (parts.length >= 3 && !isNaN(parseInt(parts[1]))) {
              shiftStartHour = parseInt(parts[1]);
            }
          }
          
          // Disable if shift start time has passed
          if (shiftStartHour > 0 && currentTime >= (shiftStartHour * 60)) {
            shouldDisable = true;
          }
        } else {
          shouldDisable = false;
        }
        opt.disabled = shouldDisable;
        opt.style.color = shouldDisable ? '#999' : '';
        if (shouldDisable && opt.textContent.indexOf('(Past Time)') === -1) {
          opt.textContent += ' (Past Time)';
        }
        if (!shouldDisable) {
          opt.textContent = opt.textContent.replace(' (Past Time)', '');
        }
      });
    }

    // Inline error helpers
    function showAddShiftError(msg) {
      const el = document.getElementById('addShiftError');
      if (!el) return;
      const span = el.querySelector('span');
      if (span) span.textContent = msg;
      el.classList.remove('hidden');
    }
    function hideAddShiftError() {
      const el = document.getElementById('addShiftError');
      if (!el) return;
      const span = el.querySelector('span');
      if (span) span.textContent = '';
      el.classList.add('hidden');
    }

    // Initialize and bind
    updateShiftTypeOptions();
  });

  // Show schedules modal for a specific date
  window.showDateSchedules = async function(dateStr) {
    const modal = document.getElementById('dateSchedulesModal');
    const modalDate = document.getElementById('modalDate');
    const modalContent = document.getElementById('modalSchedulesContent');
    const modalLoading = document.getElementById('modalSchedulesLoading');
    
    if (!modal || !modalDate || !modalContent || !modalLoading) {
      alert('Modal elements not found. Please refresh the page.');
      return;
    }

    // Validate and format date (ensure YYYY-MM-DD format)
    if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
      alert('Invalid date format');
      return;
    }

    // Format date for display
    const date = new Date(dateStr + 'T00:00:00');
    if (isNaN(date.getTime())) {
      alert('Invalid date');
      return;
    }
    modalDate.textContent = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    // Show modal and loading state
    modal.classList.remove('hidden');
    modalContent.innerHTML = '';
    modalLoading.classList.remove('hidden');

    try {
      // Build URL with properly encoded date
      const url = '<?= base_url('doctor/schedules-by-date') ?>?date=' + encodeURIComponent(dateStr);
      
      const res = await fetch(url, {
        method: 'GET',
        headers: { 
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });
      
      // Only throw error for actual server errors (4xx, 5xx)
      if (res.status >= 400 && res.status < 500) {
        const errorData = await res.json().catch(() => ({ message: res.statusText }));
        throw new Error(errorData.message || `HTTP ${res.status}: ${res.statusText}`);
      }
      
      if (res.status >= 500) {
        throw new Error(`Server error: HTTP ${res.status}`);
      }
      
      const data = await res.json();
      modalLoading.classList.add('hidden');

      // Check if request was successful
      if (data && data.success === true) {
        // Check if schedules array exists and has items
        if (data.schedules && Array.isArray(data.schedules) && data.schedules.length > 0) {
          let html = '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
          data.schedules.forEach((schedule) => {
            const doctorName = schedule.doctor_name || 'Unknown';
            const department = schedule.department || 'General';
            const time = schedule.time || '';
            const statusDisplay = schedule.status_display || schedule.status || 'Available';
            const deptSlug = department.toLowerCase().replace(/\s+/g, '-');
            
            // Get department color with fallback
            let colorInfo;
            try {
              colorInfo = window.getDepartmentColorClass ? window.getDepartmentColorClass(deptSlug, department) : { class: 'dept-general', textColor: 'text-white' };
            } catch (e) {
              console.error('Error getting department color:', e);
              colorInfo = { class: 'dept-general', textColor: 'text-white' };
            }
            const deptClass = colorInfo && colorInfo.class ? colorInfo.class : 'dept-general';
            
            // Status badge color
            let statusColor = '#10b981'; // green for available/scheduled
            if (statusDisplay.toLowerCase() === 'completed') {
              statusColor = '#3b82f6'; // blue
            } else if (statusDisplay.toLowerCase().includes('leave') || statusDisplay.toLowerCase().includes('booked')) {
              statusColor = '#f59e0b'; // orange
            }
            
            html += `
              <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.5rem; transition: box-shadow 0.2s;">
                <div style="flex-shrink: 0;">
                  <span class="inline-block w-4 h-4 rounded-full ${deptClass}" style="width: 16px; height: 16px; border-radius: 50%; display: inline-block;"></span>
                </div>
                <div style="flex: 1; min-width: 0;">
                  <div style="font-weight: 600; color: #111827; font-size: 0.875rem; margin-bottom: 0.25rem;">${doctorName}</div>
                  <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">${department}</div>
                  <div style="font-size: 0.75rem; color: ${statusColor}; font-weight: 500;">${statusDisplay}</div>
                </div>
                <div style="font-size: 0.875rem; color: #374151; font-weight: 500; white-space: nowrap; text-align: right;">
                  ${time}
                </div>
              </div>
            `;
          });
          html += '</div>';
          modalContent.innerHTML = html;
        } else {
          // Empty schedules array - show "No schedules found"
          modalContent.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280; font-size: 0.875rem;">No schedules found for this date.</div>';
        }
      } else {
        // API returned success=false
        throw new Error(data.message || 'Failed to load schedules');
      }
    } catch (error) {
      modalLoading.classList.add('hidden');
      // Only show error for actual failures (network, server errors)
      modalContent.innerHTML = '<div style="text-align: center; padding: 2rem; color: #dc2626; font-size: 0.875rem;">Error loading schedules: ' + error.message + '</div>';
      console.error('Error loading schedules:', error);
    }
  }

  // Close modal
  function closeDateSchedulesModal() {
    const modal = document.getElementById('dateSchedulesModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  }

  // Close modal on outside click
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('dateSchedulesModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeDateSchedulesModal();
        }
      });
    }
  });
</script>

<!-- Date Schedules Modal -->
<div id="dateSchedulesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">
        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
        Schedules for <span id="modalDate"></span>
      </h3>
      <button onclick="closeDateSchedulesModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div class="p-4 overflow-y-auto flex-1">
      <div id="modalSchedulesLoading" class="text-center py-8">
        <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
        <p class="text-gray-600">Loading schedules...</p>
      </div>
      <div id="modalSchedulesContent"></div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
