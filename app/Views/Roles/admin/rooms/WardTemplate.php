<?php $this->extend('partials/header') ?>

<?php
// Expecting: $wardName (string), $rows (array of ['room','bed','patient','status'])
$title = $wardName . ' – Room & Bed Management';
?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-content" id="mainContent">
  <div class="card">
    <div class="card-header">
      <h2 class="card-title mb-0"><?= esc($title) ?></h2>
      <div class="unified-search-wrapper">
        <div class="unified-search-row">
          <i class="fas fa-search unified-search-icon"></i>
          <input type="text" id="wardSearch" class="unified-search-field" placeholder="Search rooms/beds/patients...">
        </div>
      </div>
    </div>
    <div class="card-body">
      <p class="mb-3">This page shows rooms and beds in the <?= esc($wardName) ?>, including whether each bed is Available or Occupied.</p>

      <?php if (!empty($rows)): ?>
        <table class="data-table ward-table">
          <thead>
            <tr>
              <th>Room No.</th>
              <th>Bed No.</th>
              <th>Patient ID</th>
              <th>Patient Name</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <?php
                $patient = $row['patient'] ?? null;
                $status  = $row['status'] ?? 'Available';
                $badge   = $status === 'Occupied' ? 'badge-danger' : 'badge-success';
                $rawStatus = $row['raw_status'] ?? 'Available';
              ?>
              <tr>
                <td><?= esc($row['room'] ?? '') ?></td>
                <td><?= esc($row['bed'] ?? '') ?></td>
                <td><?= esc($patient['id'] ?? '') ?></td>
                <td><?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . ' ' . ($patient['name_extension'] ?? ''))) ?></td>
                <td><span class="badge <?= esc($badge) ?>"><?= esc($status) ?></span></td>
                <td>
                  <?php if ($status === 'Occupied'): ?>
                    <span class="text-muted">In use</span>
                  <?php else: ?>
                    <form action="<?= site_url('admin/rooms/beds/update-status') ?>" method="post" class="d-flex align-items-center" style="gap: 4px;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="bed_id" value="<?= esc($row['bed_id']) ?>">
                      <input type="hidden" name="ward" value="<?= esc($wardName) ?>">
                      <select name="status" class="form-control form-control-sm" style="max-width: 140px;">
                        <option value="Available" <?= $rawStatus === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Occupied" <?= $rawStatus === 'Occupied' ? 'selected' : '' ?>>Occupied</option>
                      </select>
                      <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted mb-0">No room layout configured for this ward.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?= base_url('js/ward-search.js') ?>"></script>
<?= $this->endSection() ?>
