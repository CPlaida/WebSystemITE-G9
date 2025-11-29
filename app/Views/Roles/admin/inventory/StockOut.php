<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Stock Out - Expired Medicines<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
// Set the current menu item for highlighting
$currentMenu = 'pharmacy';
$currentSubmenu = 'inventory';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="header-left">
            <h1 class="page-title">Stock Out - Expired Medicines</h1>
        </div>
        <div class="header-right">
            <a href="<?= base_url('medicines') ?>" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="content">
        <?php if (!empty($expired_medicines)): ?>
            <div class="card-container" style="margin-bottom: 20px;">
                <div class="card">
                    <h3>Total Expired Items</h3>
                    <div class="value" style="color: #dc3545;"><?= $total_expired ?></div>
                </div>
            </div>

            <!-- Search Medicine -->
            <div class="medicine-search-wrapper">
                <div class="medicine-search-row">
                    <i class="fas fa-search medicine-search-icon"></i>
                    <input type="text" id="stockOutSearch" class="medicine-search-field" placeholder="Search expired medicine by barcode, name, brand, category...">
                    <button type="button" id="clearStockOutSearch" class="medicine-search-clear">Clear</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody id="stockOutTableBody">
                        <?php foreach ($expired_medicines as $medicine): ?>
                            <tr>
                                <td data-col="barcode"><?= esc($medicine['barcode'] ?? $medicine['id']) ?></td>
                                <td data-col="name"><?= esc($medicine['name']) ?></td>
                                <td data-col="brand"><?= esc($medicine['brand'] ?? '-') ?></td>
                                <td data-col="category"><?= esc($medicine['category'] ?? '-') ?></td>
                                <td class="out-of-stock"><?= (int)$medicine['stock'] ?></td>
                                <td data-exp="<?= esc($medicine['expiry_date']) ?>" style="color: #dc3545; font-weight: 600;">
                                    <?= esc($medicine['expiry_date']) ?>
                                    <span style="margin-left:8px; padding:2px 8px; border-radius:9999px; background:#fdecea; color:#b91c1c; font-size:12px; font-weight:600;">Expired</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="color: #6b7280; margin-bottom: 8px;">No Expired Medicines</h3>
                <p style="color: #9ca3af; font-size: 14px;">All medicines in your inventory are still valid.</p>
                <a href="<?= base_url('medicines') ?>" class="btn btn-primary" style="margin-top: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Search functionality
    function filterStockOutTable() {
        const searchInput = document.getElementById('stockOutSearch');
        const clearBtn = document.getElementById('clearStockOutSearch');
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // Show/hide clear button
        if (searchTerm.length > 0) {
            clearBtn.classList.add('show');
        } else {
            clearBtn.classList.remove('show');
        }
        
        const rows = document.querySelectorAll('#stockOutTableBody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const barcode = (row.querySelector('td[data-col="barcode"]') || row.querySelector('td:first-child'))?.textContent.toLowerCase() || '';
            const name = (row.querySelector('td[data-col="name"]') || row.querySelector('td:nth-child(2)'))?.textContent.toLowerCase() || '';
            const brand = (row.querySelector('td[data-col="brand"]') || row.querySelector('td:nth-child(3)'))?.textContent.toLowerCase() || '';
            const category = (row.querySelector('td[data-col="category"]') || row.querySelector('td:nth-child(4)'))?.textContent.toLowerCase() || '';
            
            const matches = barcode.includes(searchTerm) ||
                           name.includes(searchTerm) || 
                           brand.includes(searchTerm) || 
                           category.includes(searchTerm);
            
            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "No results" message if no matches
        let noResultsMsg = document.getElementById('noResultsMessage');
        if (visibleCount === 0 && searchTerm.length > 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('tr');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.innerHTML = '<td colspan="6" class="no-results-row"><i class="fas fa-search no-results-icon"></i><p>No expired medicines found matching "' + searchTerm + '"</p></td>';
                document.getElementById('stockOutTableBody').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Clear search
    document.getElementById('clearStockOutSearch')?.addEventListener('click', function() {
        document.getElementById('stockOutSearch').value = '';
        this.classList.remove('show');
        filterStockOutTable();
    });

    // Initialize search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('stockOutSearch');
        if (searchInput) {
            searchInput.addEventListener('input', filterStockOutTable);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    document.getElementById('clearStockOutSearch').classList.remove('show');
                    filterStockOutTable();
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

