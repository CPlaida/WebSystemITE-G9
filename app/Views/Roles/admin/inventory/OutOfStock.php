<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Out of Stock Medicines<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
// Set the current menu item for highlighting
$currentMenu = 'pharmacy';
$currentSubmenu = 'inventory';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="header-left">
            <h1 class="page-title">Out of Stock Medicines</h1>
        </div>
        <div class="header-right">
            <a href="<?= base_url('medicines') ?>" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="content">
        <?php if (!empty($out_of_stock_medicines)): ?>
            <!-- Search Medicine -->
            <div class="medicine-search-wrapper">
                <div class="medicine-search-row">
                    <i class="fas fa-search medicine-search-icon"></i>
                    <input type="text" id="outOfStockSearch" class="medicine-search-field" placeholder="Search out of stock medicine by barcode, name, brand, category...">
                    <button type="button" id="clearOutOfStockSearch" class="medicine-search-clear">Clear</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Medicine Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Unit Price</th>
                            <th>Retail Price</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="outOfStockTableBody">
                        <?php foreach ($out_of_stock_medicines as $medicine): ?>
                            <tr>
                                <td data-col="barcode"><?= esc($medicine['barcode'] ?? $medicine['id']) ?></td>
                                <td data-col="name"><?= esc($medicine['name']) ?></td>
                                <td data-col="brand"><?= esc($medicine['brand'] ?? '-') ?></td>
                                <td data-col="category"><?= esc($medicine['category'] ?? '-') ?></td>
                                <td class="out-of-stock" style="color: #dc3545; font-weight: 600;">
                                    <?= (int)$medicine['stock'] ?>
                                    <?php
                                    // Show status badge
                                    $status = $medicine['status'] ?? null;
                                    if ($status === 'expired_soon') {
                                        echo '<span class="badge" style="margin-left: 8px; background-color: #ff9800; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75em; font-weight: 600;">Expiring Soon</span>';
                                    }
                                    ?>
                                </td>
                                <td data-col="unit_price">₱<?= number_format((float)($medicine['unit_price'] ?? $medicine['price'] ?? 0), 2) ?></td>
                                <td data-col="retail_price">₱<?= number_format((float)($medicine['retail_price'] ?? $medicine['price'] ?? 0), 2) ?></td>
                                <td data-exp="<?= esc($medicine['expiry_date'] ?? '-') ?>">
                                    <?= esc($medicine['expiry_date'] ?? '-') ?>
                                    <?php
                                    // Show warning if expiring soon
                                    if ($status === 'expired_soon' && !empty($medicine['expiry_date'])) {
                                        $expiry = new \DateTime($medicine['expiry_date']);
                                        $today = new \DateTime();
                                        $daysLeft = $today->diff($expiry)->days;
                                        echo '<span style="margin-left: 8px; color: #ff9800; font-size: 0.85em;">(' . $daysLeft . ' days left)</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('medicines/edit/' . $medicine['id']) ?>" class="btn" style="padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-block;">Restock</a>
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
                <h3 style="color: #6b7280; margin-bottom: 8px;">No Out of Stock Medicines</h3>
                <p style="color: #9ca3af; font-size: 14px;">All medicines in your inventory have stock available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Search functionality
    function filterOutOfStockTable() {
        const searchInput = document.getElementById('outOfStockSearch');
        const clearBtn = document.getElementById('clearOutOfStockSearch');
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // Show/hide clear button
        if (searchTerm.length > 0) {
            clearBtn.classList.add('show');
        } else {
            clearBtn.classList.remove('show');
        }
        
        const rows = document.querySelectorAll('#outOfStockTableBody tr');
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
                noResultsMsg.innerHTML = '<td colspan="9" class="no-results-row"><i class="fas fa-search no-results-icon"></i><p>No out of stock medicines found matching "' + searchTerm + '"</p></td>';
                document.getElementById('outOfStockTableBody').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Clear search
    document.getElementById('clearOutOfStockSearch')?.addEventListener('click', function() {
        document.getElementById('outOfStockSearch').value = '';
        this.classList.remove('show');
        filterOutOfStockTable();
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize search
        const searchInput = document.getElementById('outOfStockSearch');
        if (searchInput) {
            searchInput.addEventListener('input', filterOutOfStockTable);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    document.getElementById('clearOutOfStockSearch').classList.remove('show');
                    filterOutOfStockTable();
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

