<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Financial Reports<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="header">
        <h1 class="page-title">Financial Reports</h1>
        <a href="<?= base_url('reports') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="composite-card">
        <div class="composite-header">
            <div class="composite-title">Financial Report Options</div>
        </div>
        <div class="admin-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <div class="panel-card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?= base_url('reports/revenue') ?>'">
                <div style="display: flex; align-items: center; gap: 15px; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Revenue Report</div>
                        <div style="font-size: 13px; color: #6b7280;">View revenue by date range and payment method</div>
                    </div>
                </div>
            </div>

            <div class="panel-card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?= base_url('reports/outstanding-payments') ?>'">
                <div style="display: flex; align-items: center; gap: 15px; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Outstanding Payments</div>
                        <div style="font-size: 13px; color: #6b7280;">Track unpaid bills and overdue payments</div>
                    </div>
                </div>
            </div>

            <div class="panel-card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?= base_url('reports/profit-loss') ?>'">
                <div style="display: flex; align-items: center; gap: 15px; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Profit & Loss</div>
                        <div style="font-size: 13px; color: #6b7280;">View profit and loss statements</div>
                    </div>
                </div>
            </div>

            <div class="panel-card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?= base_url('reports/expenses') ?>'">
                <div style="display: flex; align-items: center; gap: 15px; padding: 20px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Expense Report</div>
                        <div style="font-size: 13px; color: #6b7280;">Track expenses by category</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.panel-card:hover {
    transform: translateY(-3px);
}
</style>
<?= $this->endSection() ?>

