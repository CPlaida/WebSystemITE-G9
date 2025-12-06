<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Male Ward – Room & Bed Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="composite-card billing-card" style="margin-top:0;">
    <div class="composite-header">
      <h1 class="composite-title">Male Ward – Room & Bed Management</h1>
    </div>
    <div class="card-body">
      <p class="mb-3">This is a placeholder page for managing rooms and beds in the Male Ward. The data below is sample only.</p>
      <div class="card" style="box-shadow: none; border: none; margin: 0;">
        <div class="card-body" style="padding: 0;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Room No.</th>
            <th>Bed No.</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>M-201</td>
            <td>Bed 1</td>
            <td><span class="badge badge-success">Available</span></td>
            <td>Cleaned and ready</td>
          </tr>
          <tr>
            <td>M-201</td>
            <td>Bed 2</td>
            <td><span class="badge badge-danger">Occupied</span></td>
            <td>Post-op patient</td>
          </tr>
          <tr>
            <td>M-202</td>
            <td>Bed 1</td>
            <td><span class="badge badge-warning">Reserved</span></td>
            <td>For ER transfer</td>
          </tr>
        </tbody>
      </table>
      <p class="text-muted mb-0"><small>Tip: Later, this table can be connected to real room/bed data.</small></p>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
