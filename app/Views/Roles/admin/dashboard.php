<?php $this->extend('partials/header') ?>
<?php
// Safe on-view fallback for pharmacy paid total (keeps dashboard working even if controller doesn't pass it)
$pharmacyPaidTotal = 0.0;
$pharmacyPaidToday = 0.0;
// Laboratory stats safe computation
$labPending = 0;
$labCompletedToday = 0;
$labThisMonth = 0;
// Billing & patients/appointments defaults
$todayRevenue = 0.0; $paidThisMonth = 0.0; $outstanding = 0.0; $pendingBills = 0;
$appointmentsCount = 0; $patientsCount = 0; $activeCases = 0; $newPatientsToday = 0;
//users
$usersActiveTotal = 0;
$usersInactiveTotal = 0;
$usersSuspendedTotal = 0;
try {
    $db = \Config\Database::connect();
    // Pharmacy totals
    if ($db->tableExists('pharmacy_transactions')) {
        $fields = $db->getFieldData('pharmacy_transactions');
        $hasStatus = false;
        foreach ($fields as $f) { if (($f->name ?? '') === 'status') { $hasStatus = true; break; } }
        // All-time paid
        $builder = $db->table('pharmacy_transactions')->selectSum('total_amount', 'sum');
        if ($hasStatus) { $builder->where('status', 'completed'); }
        $row = $builder->get()->getRowArray();
        $pharmacyPaidTotal = (float)($row['sum'] ?? 0);
        // Today paid
        $today = date('Y-m-d');
        $todayB = $db->table('pharmacy_transactions')->selectSum('total_amount', 'sum');
        if ($hasStatus) { $todayB->where('status', 'completed'); }
        $todayB->where('DATE(date)', $today);
        $rowT = $todayB->get()->getRowArray();
        $pharmacyPaidToday = (float)($rowT['sum'] ?? 0);
    }
    // Laboratory (primary: lab_requests; fallback: test_results)
    $labPending = 0; $labCompletedToday = 0; $labThisMonth = 0;
    $today = date('Y-m-d');
    $todayStart = $today . ' 00:00:00';
    $tomorrowStart = date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00';
    $monthStart = date('Y-m-01');
    $nextMonthStart = date('Y-m-d', strtotime($monthStart . ' +1 month'));

    if ($db->tableExists('lab_requests')) {
        // Detect columns
        $fields = $db->getFieldData('lab_requests');
        $have = []; foreach ($fields as $f) { $have[strtolower($f->name ?? '')] = $f->name; }
        $statusCol = isset($have['status']) ? $have['status'] : (isset($have['lab_status']) ? $have['lab_status'] : 'status');
        // Completion timestamp preference
        $completeCol = isset($have['completed_at']) ? $have['completed_at'] : (isset($have['updated_at']) ? $have['updated_at'] : (isset($have['date']) ? $have['date'] : (isset($have['created_at']) ? $have['created_at'] : null)));
        // Creation date for monthly count
        $createdCol = isset($have['created_at']) ? $have['created_at'] : (isset($have['date']) ? $have['date'] : (isset($have['requested_at']) ? $have['requested_at'] : null));

        // Pending
        $qbP = $db->table('lab_requests')->select('COUNT(*) AS c')
                 ->groupStart()
                   ->where("LOWER(TRIM($statusCol)) =", 'pending', false)
                 ->groupEnd();
        $row = $qbP->get()->getRowArray();
        $labPending = (int)($row['c'] ?? 0);

        // Completed Today
        $qbC = $db->table('lab_requests')->select('COUNT(*) AS c')
                 ->groupStart()
                   ->where("LOWER(TRIM($statusCol)) =", 'completed', false)
                   ->orWhere("LOWER(TRIM($statusCol)) =", 'reviewed', false)
                 ->groupEnd();
        if ($completeCol) { $qbC->where("$completeCol >=", $todayStart)->where("$completeCol <", $tomorrowStart); }
        $row = $qbC->get()->getRowArray();
        $labCompletedToday = (int)($row['c'] ?? 0);

        // Tests this month
        if ($createdCol) {
            $qbM = $db->table('lab_requests')->select('COUNT(*) AS c')
                     ->where("$createdCol >=", $monthStart)
                     ->where("$createdCol <", $nextMonthStart);
            $row = $qbM->get()->getRowArray();
            $labThisMonth = (int)($row['c'] ?? 0);
        }
    }
    if ($db->tableExists('test_results')) {
        // Also compute from test_results and take max per metric
        $fields = $db->getFieldData('test_results');
        $have = []; foreach ($fields as $f) { $have[strtolower($f->name ?? '')] = $f->name; }
        $statusCol = isset($have['status']) ? $have['status'] : 'status';
        $dateCol = isset($have['result_date']) ? $have['result_date'] : (isset($have['test_date']) ? $have['test_date'] : (isset($have['updated_at']) ? $have['updated_at'] : (isset($have['created_at']) ? $have['created_at'] : null)));
        $monthCol = isset($have['created_at']) ? $have['created_at'] : ($dateCol ?: null);

        $trPending = 0; $trCompletedToday = 0; $trThisMonth = 0;
        // Pending
        $qbP = $db->table('test_results')->select('COUNT(*) AS c')
                 ->groupStart()
                   ->where("LOWER(TRIM($statusCol)) =", 'pending', false)
                 ->groupEnd();
        $row = $qbP->get()->getRowArray();
        $trPending = (int)($row['c'] ?? 0);

        // Completed Today
        $qbC = $db->table('test_results')->select('COUNT(*) AS c')
                 ->groupStart()
                   ->where("LOWER(TRIM($statusCol)) =", 'completed', false)
                   ->orWhere("LOWER(TRIM($statusCol)) =", 'reviewed', false)
                   ->orWhere("LOWER(TRIM($statusCol)) =", 'released', false)
                   ->orWhere("LOWER(TRIM($statusCol)) =", 'done', false)
                 ->groupEnd();
        if ($dateCol) { $qbC->where("$dateCol >=", $todayStart)->where("$dateCol <", $tomorrowStart); }
        $row = $qbC->get()->getRowArray();
        $trCompletedToday = (int)($row['c'] ?? 0);

        // Tests This Month
        if ($monthCol) {
            $qbM = $db->table('test_results')->select('COUNT(*) AS c')
                     ->where("$monthCol >=", $monthStart)
                     ->where("$monthCol <", $nextMonthStart);
            $row = $qbM->get()->getRowArray();
            $trThisMonth = (int)($row['c'] ?? 0);
        }
        // Take max across sources
        $labPending = max($labPending, $trPending);
        $labCompletedToday = max($labCompletedToday, $trCompletedToday);
        $labThisMonth = max($labThisMonth, $trThisMonth);
    }
    // Billing & Payments (match Billing Management) with dynamic column detection
    $todayRevenue = 0.0; $paidThisMonth = 0.0; $outstanding = 0.0; $pendingBills = 0;
    $billingTables = ['bills','billing'];
    foreach ($billingTables as $bt) {
        if ($db->tableExists($bt)) {
            $fields = $db->getFieldData($bt);
            $have = [];
            foreach ($fields as $f) { $have[strtolower($f->name ?? '')] = $f->name; }
            // Build amount expression only with existing columns
            $amtColsPref = ['final_amount','total_amount','amount','total','bill_amount'];
            $amtParts = [];
            foreach ($amtColsPref as $c) { if (isset($have[$c])) { $amtParts[] = $have[$c]; } }
            $amtExpr = count($amtParts) ? ('COALESCE(' . implode(',', $amtParts) . ')') : '0';
            // Build date expression for billing date
            $dateColsPref = ['bill_date','date','created_at','updated_at'];
            $dateParts = [];
            foreach ($dateColsPref as $c) { if (isset($have[$c])) { $dateParts[] = $have[$c]; } }
            $dateExpr = count($dateParts) ? ('COALESCE(' . implode(',', $dateParts) . ')') : null;
            // Status column
            $statusCol = isset($have['payment_status']) ? $have['payment_status'] : (isset($have['status']) ? $have['status'] : null);
            if ($statusCol === null) { $statusCol = 'NULL'; }

            // Today's revenue (sum of paid with today's date)
            if ($dateExpr) {
                $sqlToday = "SELECT COALESCE(SUM($amtExpr),0) AS s FROM $bt WHERE LOWER(TRIM(COALESCE($statusCol)))='paid' AND DATE($dateExpr)=CURDATE()";
                $row = $db->query($sqlToday)->getRowArray();
                $todayRevenue = (float)($row['s'] ?? 0);
            }
            // Paid this month
            if ($dateExpr) {
                $sqlMonth = "SELECT COALESCE(SUM($amtExpr),0) AS s FROM $bt WHERE LOWER(TRIM(COALESCE($statusCol)))='paid' AND DATE($dateExpr) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
                $row = $db->query($sqlMonth)->getRowArray();
                $paidThisMonth = (float)($row['s'] ?? 0);
            }
            // Outstanding amount (pending or unpaid)
            $sqlOut = "SELECT COALESCE(SUM($amtExpr),0) AS s FROM $bt WHERE LOWER(TRIM(COALESCE($statusCol))) IN ('pending','unpaid')";
            $row = $db->query($sqlOut)->getRowArray();
            $outstanding = (float)($row['s'] ?? 0);
            // Pending bills count
            $sqlCnt = "SELECT COUNT(*) AS c FROM $bt WHERE LOWER(TRIM(COALESCE($statusCol))) IN ('pending','unpaid')";
            $row = $db->query($sqlCnt)->getRowArray();
            $pendingBills = (int)($row['c'] ?? 0);
            break;
        }
    }

    // Patients and appointments
    if ($db->tableExists('patients')) {
        $row = $db->table('patients')->select('COUNT(*) AS c')->get()->getRowArray();
        $patientsCount = (int)($row['c'] ?? 0);
        // new patients today
        $fields = $db->getFieldData('patients'); $hasCreated=false; $createdCol='created_at';
        foreach ($fields as $f){ $n=strtolower($f->name ?? ''); if ($n==='created_at'||$n==='createdat'||$n==='date_created'){ $hasCreated=true; $createdCol=$f->name; break; } }
        if ($hasCreated) {
            $row = $db->table('patients')->select('COUNT(*) AS c')->where("DATE($createdCol)", date('Y-m-d'))->get()->getRowArray();
            $newPatientsToday = (int)($row['c'] ?? 0);
        }
        // active cases if patients has 'status' column
        $hasStatus=false; foreach($fields as $f){ if (strtolower($f->name ?? '')==='status'){ $hasStatus=true; $statusCol=$f->name; break; } }
        if ($hasStatus){
            $row = $db->table('patients')->select('COUNT(*) AS c')->groupStart()->where($statusCol,'active')->orWhere($statusCol,'admitted')->groupEnd()->get()->getRowArray();
            $activeCases = (int)($row['c'] ?? 0);
        }
    }
    if ($db->tableExists('appointments')) {
        // today's appointments
        $fields = $db->getFieldData('appointments'); $dateCol=null; $statusCol=null;
        foreach ($fields as $f){ $n=strtolower($f->name ?? ''); if (in_array($n,['date','appointment_date','scheduled_at'])) $dateCol=$f->name; if ($n==='status') $statusCol=$f->name; }
        if ($dateCol){
            // Count today's appointments using a date range to support DATE or DATETIME columns
            $start = date('Y-m-d');
            $end = date('Y-m-d', strtotime('+1 day'));
            $qbA = $db->table('appointments')->select('COUNT(*) AS c')
                      ->where("$dateCol >=", $start)
                      ->where("$dateCol <", $end);
            if ($statusCol) {
                // Exclude only cancelled/no_show; count all other statuses
                $qbA->whereNotIn($statusCol, ['cancelled','no_show']);
            }
            $row = $qbA->get()->getRowArray();
            $appointmentsCount = (int)($row['c'] ?? 0);
            if ($appointmentsCount === 0) {
                // Fallback 1: try equality on DATE column name if common
                $col = $dateCol ?: 'appointment_date';
                $qbB = $db->table('appointments')->select('COUNT(*) AS c')->where($col, date('Y-m-d'));
                if ($statusCol) { $qbB->whereNotIn($statusCol, ['cancelled','no_show']); }
                $rowB = $qbB->get()->getRowArray();
                $appointmentsCount = max($appointmentsCount, (int)($rowB['c'] ?? 0));
            }
            if ($appointmentsCount === 0) {
                // Fallback 2: raw SQL using CURDATE() (works on MySQL/MariaDB)
                $col = $dateCol ?: 'appointment_date';
                $sql = "SELECT COUNT(*) AS c FROM appointments WHERE $col = CURDATE()" . ($statusCol ? " AND $statusCol NOT IN ('cancelled','no_show')" : '');
                $rowC = $db->query($sql)->getRowArray();
                $appointmentsCount = max($appointmentsCount, (int)($rowC['c'] ?? 0));
            }
        }
    }

    // Users active total (safe detection)
    // Try common tables: users, user_accounts
    $userTables = ['users','user_accounts'];
    $userTableUsed = null; $hasIsActive = false; $hasStatus = false; $hasActive = false; $hasIsSuspended = false;
    foreach ($userTables as $t) {
        if ($db->tableExists($t)) {
            $fields = $db->getFieldData($t);
            $hasIsActive = false; $hasStatus = false; $hasActive = false; $hasIsSuspended = false;
            foreach ($fields as $f) {
                $n = strtolower($f->name ?? '');
                if ($n === 'is_active') $hasIsActive = true;
                if ($n === 'status') $hasStatus = true;
                if ($n === 'active') $hasActive = true;
                if ($n === 'is_suspended' || $n === 'suspended') $hasIsSuspended = true;
            }
            $qb = $db->table($t)->select('COUNT(*) AS c');
            if ($hasIsActive) {
                $qb->where('is_active', 1);
            } elseif ($hasStatus) {
                $qb->groupStart()
                      ->where('status', 1)
                      ->orWhere('status', '1')
                      ->orWhere('LOWER(status)', 'active')
                   ->groupEnd();
            } elseif ($hasActive) {
                $qb->where('active', 1);
            }
            $rowU = $qb->get()->getRowArray();
            $usersActiveTotal = (int)($rowU['c'] ?? 0);
            $userTableUsed = $t;
            break;
        }
    }
    // If a table was detected, compute inactive and suspended too
    if ($userTableUsed) {
        // Inactive
        $qbI = $db->table($userTableUsed)->select('COUNT(*) AS c');
        if ($hasIsActive) {
            $qbI->where('is_active', 0);
        } elseif ($hasStatus) {
            $qbI->groupStart()
                 ->where('status', 0)
                 ->orWhere('status', '0')
                 ->orWhere('LOWER(status)', 'inactive')
               ->groupEnd();
        } elseif ($hasActive) {
            $qbI->where('active', 0);
        }
        $rowI = $qbI->get()->getRowArray();
        $usersInactiveTotal = (int)($rowI['c'] ?? 0);

        // Suspended
        $qbS = $db->table($userTableUsed)->select('COUNT(*) AS c');
        if ($hasIsSuspended) {
            $qbS->groupStart()
                 ->where('is_suspended', 1)
                 ->orWhere('suspended', 1)
               ->groupEnd();
        } elseif ($hasStatus) {
            $qbS->groupStart()
                 ->where('LOWER(status)', 'suspended')
                 ->orWhere('LOWER(status)', 'blocked')
                 ->orWhere('LOWER(status)', 'disabled')
                 ->orWhere('status', '')
                 ->orWhere('status IS NULL', null, false)
               ->groupEnd();
        } else {
            // No clear suspended field, assume zero
            $usersSuspendedTotal = 0;
        }
        if (!isset($usersSuspendedTotal) || $usersSuspendedTotal === 0) {
            $rowS = $qbS->get()->getRowArray();
            $usersSuspendedTotal = (int)($rowS['c'] ?? 0);
        }
    }
} catch (\Throwable $e) { /* ignore */ }
?>


    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">

    <div class="composite-card billing-card dashboard-overview" style="margin-top:0;">
  <div class="composite-header">
    <div class="composite-title">Dashboard Overview</div>
  </div>
  <div class="admin-grid">
    <div class="kpi-card span-3">
      <div class="kpi-title">Today's Appointments</div>
      <div class="kpi-value"><?= number_format((int)($appointmentsCount ?? 0)) ?></div>
    </div>
    <div class="kpi-card span-3">
      <div class="kpi-title">Total Patients</div>
      <div class="kpi-value"><?= number_format((int)($patientsCount ?? 0)) ?></div>
    </div>
    <div class="kpi-card span-3">
      <div class="kpi-title">Active Cases</div>
      <div class="kpi-value"><?= number_format((int)($activeCases ?? 0)) ?></div>
    </div>
    <div class="kpi-card span-3">
      <div class="kpi-title">New Patients Today</div>
      <div class="kpi-value"><?= number_format((int)($newPatientsToday ?? 0)) ?></div>
    </div>

    <div class="panel-card span-4">
      <div class="panel-header">Billing & Payments</div>
      <div class="metric-grid">
        <div class="metric-item"><div class="metric-title">Today's Revenue</div><div class="metric-value">₱<?= number_format((float)($todayRevenue ?? 0),2) ?></div></div>
        <div class="metric-item"><div class="metric-title">Paid This Month</div><div class="metric-value">₱<?= number_format((float)($paidThisMonth ?? 0),2) ?></div></div>
        <div class="metric-item"><div class="metric-title">Outstanding</div><div class="metric-value">₱<?= number_format((float)($outstanding ?? 0),2) ?></div></div>
        <div class="metric-item"><div class="metric-title">Pending Bills</div><div class="metric-value"><?= number_format((int)($pendingBills ?? 0)) ?></div></div>
      </div>
    </div>

    <div class="panel-card span-4">
      <div class="panel-header">Pharmacy Sales</div>
      <div class="metric-grid">
        <div class="metric-item"><div class="metric-title">Total Paid</div><div class="metric-value">₱<?= number_format((float)($pharmacyPaidTotal ?? 0),2) ?></div></div>
        <div class="metric-item"><div class="metric-title">Paid Today</div><div class="metric-value">₱<?= number_format((float)($pharmacyPaidToday ?? 0),2) ?></div></div>
      </div>
    </div>

    <div class="panel-card span-4">
      <div class="panel-header">Laboratory</div>
      <div class="metric-grid">
        <div class="metric-item"><div class="metric-title">Pending</div><div class="metric-value"><?= number_format((int)($labPending ?? (($labStats['pending'] ?? null) !== null ? $labStats['pending'] : ($labTestsCount ?? 0))),0) ?></div></div>
        <div class="metric-item"><div class="metric-title">Completed Today</div><div class="metric-value"><?= number_format((int)($labCompletedToday ?? 0),0) ?></div></div>
        <div class="metric-item"><div class="metric-title">Tests This Month</div><div class="metric-value"><?= number_format((int)($labThisMonth ?? 0),0) ?></div></div>
      </div>
    </div>

    <div class="panel-card span-12 users-total">
      <div class="panel-header">Users Total</div>
      <div class="metric-grid">
        <div class="metric-item"><div class="metric-title">Active Users</div><div class="metric-value"><?= number_format((int)($usersActiveTotal ?? 0)) ?></div></div>
        <div class="metric-item"><div class="metric-title">Inactive Users</div><div class="metric-value"><?= number_format((int)($usersInactiveTotal ?? 0)) ?></div></div>
        <div class="metric-item"><div class="metric-title">Suspended Users</div><div class="metric-value"><?= number_format((int)($usersSuspendedTotal ?? 0)) ?></div></div>
      </div>
    </div>
  </div>
</div>