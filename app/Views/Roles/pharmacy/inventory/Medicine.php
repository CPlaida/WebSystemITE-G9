<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Medicine Inventory<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
// Set the current menu item for highlighting
$currentMenu = 'pharmacy';
$currentSubmenu = 'inventory';
?>

<style>
/* Hide the dropdown arrow shown by some browsers for inputs with datalist */
#medicineForm input[list]::-webkit-calendar-picker-indicator { display: none !important; }
#medicineForm input[list] { appearance: none; -webkit-appearance: none; background-image: none; }
</style>
<div class="content-wrapper">
    <div class="content-header">
        <div class="header-left">
            <h1 class="page-title">Medicine Inventory</h1>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="toggleForm()">
                <i class="fas fa-plus"></i> Add New Medicine
            </button>
        </div>
    </div>

    <div class="content">
        <?php $errorFlash = session()->getFlashdata('error'); ?>
        <?php $errorMsg = session()->getFlashdata('error') ?? ''; ?>
        <!-- Error modal is shown later after DOMContentLoaded using ERROR_MSG -->
        <?php
        $expired_count = 0;
        $expiring_soon_count = 0; // no expiring state; 3-month rule moves them to Stock Out
        ?>
        <div class="card-container">
            <div class="card">
                <h3>Total Items</h3>
                <div class="value" id="totalItems"><?php if (isset($total)) { echo (int)$total; } ?></div>
            </div>
            <div class="card">
                <h3>Low Stock</h3>
                <div class="value" id="lowStock"><?php if (isset($low_stock)) { echo (int)$low_stock; } ?></div>
            </div>
            <div class="card">
                <h3>Out of Stock</h3>
                <div class="value" id="outOfStock"><?php if (isset($out_stock)) { echo (int)$out_stock; } ?></div>
            </div>
            <div class="card">
                <h3>Expiring Soon</h3>
                <div class="value" id="expiringSoon"><?= (int)($expiring_soon_count ?? 0) ?></div>
            </div>
        </div>

        <!-- Search Medicine -->
        <div class="medicine-search-wrapper">
            <div class="medicine-search-row">
                <i class="fas fa-search medicine-search-icon"></i>
                <input type="text" id="medicineSearch" class="medicine-search-field" placeholder="Search medicine by ID, barcode, name, brand, category...">
                <button type="button" id="clearSearch" class="medicine-search-clear">Clear</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicine Barcode</th>
                        <th>Medicine Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Unit Price</th>
                        <th>Retail Price</th>
                        <th>Manufactured Date</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="medicineTableBody">
                    <?php if (!empty($medicines)) : ?>
                        <?php foreach ($medicines as $m): ?>
                            <tr class="medicine-row-clickable" data-medicine-id="<?= esc($m['id']) ?>" style="cursor: pointer;" onclick="showMedicineDetails(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)">
                                <td><?= esc($m['barcode'] ?? $m['id']) ?></td>
                                <td><?= esc($m['name']) ?></td>
                                <td><?= esc($m['brand']) ?></td>
                                <td><?= esc($m['category']) ?></td>
                                <td class="<?= ((int)$m['stock'] === 0 ? 'out-of-stock' : 'in-stock') ?>"><?= (int)$m['stock'] ?></td>
                                <td>₱<?= number_format((float)($m['unit_price'] ?? $m['price'] ?? 0), 2) ?></td>
                                <td>₱<?= number_format((float)($m['retail_price'] ?? $m['price'] ?? 0), 2) ?></td>
                                <td><?= esc($m['manufactured_date'] ?? '-') ?></td>
                                <?php
                                    $expDate = $m['expiry_date'] ?? null;
                                    // No expiring badge; anything within 3 months is in Stock Out already
                                    $statusBadge = '';
                                ?>
                                <td data-exp="<?= esc($expDate) ?>">
                                    <?= esc($expDate) ?><?= $statusBadge ? $statusBadge : '' ?>
                                </td>
                                <td class="actions" onclick="event.stopPropagation();">
                                    <a class="medicine-btn-edit" href="<?= base_url('medicines/edit/' . $m['id']) ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Medicine Description Modal -->
<div class="modal medicine-details-modal" id="medicineDetailsModal" role="dialog" aria-modal="true" aria-labelledby="medicineDetailsTitle">
    <div class="modal-content medicine-details-modal-content">
        <div class="medicine-details-header">
            <h3 id="medicineDetailsTitle" class="medicine-details-title">About This Medicine</h3>
        </div>
        <div id="medicineDetailsContent" class="medicine-details-body">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="medicine-details-footer">
            <button type="button" onclick="closeMedicineDetails()" class="medicine-details-close-btn">Close</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal" id="errorModal" role="dialog" aria-modal="true" aria-labelledby="errorTitle" style="z-index: 2000;">
    <div class="modal-content" style="max-width: 420px; text-align: center; padding: 30px 24px;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #ffe5e8; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2" fill="none"/>
                <path d="M8 8l8 8M16 8l-8 8" stroke="#e74c3c" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <h3 id="errorTitle" style="margin: 0 0 10px; font-size: 22px; color: #2c3e50;">Error</h3>
        <div id="errorModalBody" style="color: #6b7280; font-size: 14px; margin-bottom: 18px;"></div>
        <button type="button" id="errorOkBtn" style="background: #e74c3c; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-weight: 600; cursor: pointer;">OK</button>
    </div>
</div>

<!-- Add/Edit Form Modal -->
    <?php $isEdit = isset($edit_medicine) && is_array($edit_medicine); ?>
    <div class="modal" id="medicineForm">
        <div class="modal-content medicine-modal-content">
            <div class="medicine-modal-header">
                <div class="medicine-modal-title-section">
                    <h2 class="medicine-modal-title">
                    <?= $isEdit ? 'Edit Medicine' : 'Add New Medicine(s)' ?>
                </h2>
                    <?php if (!$isEdit): ?>
                        <p class="medicine-modal-subtitle">Fill in the details below to add new medicine entries to the inventory</p>
                    <?php endif; ?>
                </div>
                <span class="close medicine-modal-close" onclick="closeForm()">&times;</span>
            </div>
            
            <form id="medicineFormElement" style="margin: 0;" method="post" action="<?= $isEdit ? base_url('medicines/update/' . $edit_medicine['id']) : base_url('medicines/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
            <!-- Autocomplete suggestion lists -->
            <datalist id="medicineNamesList"></datalist>
            <datalist id="brandList"></datalist>
            <datalist id="categoryList"></datalist>
            <div class="medicine-form-scrollable">
            <div id="medicineEntries">
                    <div class="medicine-entry">
                        <div class="medicine-form-row">
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Medicine Barcode</label>
                                <input type="text" name="<?= $isEdit ? 'barcode' : 'barcode[]' ?>" value="<?= $isEdit ? esc($edit_medicine['barcode'] ?? '') : '' ?>" class="form-control medicine-input" placeholder="Barcode" autocomplete="off">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Medicine Name <span class="required-asterisk">*</span></label>
                                <input type="text" name="<?= $isEdit ? 'name' : 'name[]' ?>" value="<?= $isEdit ? esc($edit_medicine['name']) : '' ?>" list="medicineNamesList" autocomplete="off" class="form-control medicine-input" required placeholder="Medicine Name">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Brand <span class="required-asterisk">*</span></label>
                                <select name="<?= $isEdit ? 'brand' : 'brand[]' ?>" class="form-control medicine-input" required>
                                    <option value="">Select Brand</option>
                                    <?php 
                                    $brands = ['Biogesic','Amoxil','Vitacare','Loratin','Pfizer','Unilab','RiteMed','GSK'];
                                    foreach ($brands as $b): 
                                    ?>
                                        <option value="<?= $b ?>" <?= $isEdit && isset($edit_medicine['brand']) && $edit_medicine['brand'] === $b ? 'selected' : '' ?>><?= $b ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Category <span class="required-asterisk">*</span></label>
                                <select name="<?= $isEdit ? 'category' : 'category[]' ?>" class="form-control medicine-input" required>
                                    <option value="">Select Category</option>
                                    <?php $categories = ['Pain Relief','Antibiotics','Vitamins','Antihistamines']; ?>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c ?>" <?= $isEdit && isset($edit_medicine['category']) && $edit_medicine['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Stock <span class="required-asterisk">*</span></label>
                                <input type="number" name="<?= $isEdit ? 'stock' : 'stock[]' ?>" value="<?= $isEdit ? (int)$edit_medicine['stock'] : '' ?>" min="0" class="form-control medicine-input" required placeholder="0">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Unit Price <span class="required-asterisk">*</span> (₱)</label>
                                <input type="number" name="<?= $isEdit ? 'unit_price' : 'unit_price[]' ?>" value="<?= $isEdit ? (float)($edit_medicine['unit_price'] ?? $edit_medicine['price'] ?? '') : '' ?>" min="0" step="0.01" class="form-control medicine-input" required placeholder="0.00">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Retail Price <span class="required-asterisk">*</span> (₱)</label>
                                <input type="number" name="<?= $isEdit ? 'retail_price' : 'retail_price[]' ?>" value="<?= $isEdit ? (float)($edit_medicine['retail_price'] ?? $edit_medicine['price'] ?? '') : '' ?>" min="0" step="0.01" class="form-control medicine-input" required placeholder="0.00">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Manufactured Date</label>
                                <input type="date" name="<?= $isEdit ? 'manufactured_date' : 'manufactured_date[]' ?>" value="<?= $isEdit ? esc($edit_medicine['manufactured_date'] ?? '') : '' ?>" class="form-control medicine-input" placeholder="Date">
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Expiry Date <span class="required-asterisk">*</span></label>
                                <input type="date" name="<?= $isEdit ? 'expiry_date' : 'expiry_date[]' ?>" value="<?= $isEdit ? esc($edit_medicine['expiry_date']) : '' ?>" class="form-control medicine-input" required>
                            </div>
                            <div class="medicine-field-group" style="grid-column: 1 / -1;">
                                <label class="medicine-field-label">Description</label>
                                <textarea name="<?= $isEdit ? 'description' : 'description[]' ?>" class="form-control medicine-input" rows="3" placeholder="Enter medicine description..."><?= $isEdit ? esc($edit_medicine['description'] ?? '') : '' ?></textarea>
                            </div>
                            <div class="medicine-field-group">
                                <label class="medicine-field-label">Image</label>
                                <?php if ($isEdit && !empty($edit_medicine['image'])): 
                                    $currentImagePath = FCPATH . 'uploads/medicines/' . $edit_medicine['image'];
                                    $currentImageUrl = base_url('uploads/medicines/' . esc($edit_medicine['image']));
                                    $hasCurrentImage = file_exists($currentImagePath);
                                ?>
                                    <div style="margin-bottom: 12px;" id="currentImageContainer">
                                        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                                            <?php if ($hasCurrentImage): ?>
                                                <img src="<?= $currentImageUrl ?>" alt="Current image" id="currentImagePreview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e0e0e0; flex-shrink: 0;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f5f5f5; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 10px; text-align: center; border: 1px solid #e0e0e0; flex-shrink: 0;">No Image</div>
                                            <?php endif; ?>
                                            <div style="flex: 1; min-width: 0;">
                                                <button type="button" onclick="removeCurrentImage()" style="padding: 4px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 500;">Remove</button>
                                            </div>
                    </div>
                                        <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                        </div>
                                    <div class="image-input-wrapper" style="width: 100%; display: none;">
                                        <input type="file" name="<?= $isEdit ? 'image' : 'image[]' ?>" accept="image/*" class="form-control medicine-input image-input" onchange="previewImage(this)">
                        </div>
                                <?php else: ?>
                                    <div class="image-input-wrapper" style="width: 100%;">
                                        <input type="file" name="<?= $isEdit ? 'image' : 'image[]' ?>" accept="image/*" class="form-control medicine-input image-input" onchange="previewImage(this)">
                        </div>
                                <?php endif; ?>
                                <div class="newImagePreviewContainer" style="display: none; margin-top: 8px; background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px;">
                                    <div style="text-align: center;">
                                        <img class="previewImg" src="" alt="Preview" style="width: 100%; max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e0e0e0; display: block; margin: 0 auto 8px;">
                                        <button type="button" onclick="clearImagePreview(this)" style="padding: 4px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 500;">Remove</button>
                        </div>
                        </div>
                        </div>
                            <div class="medicine-field-group medicine-remove-group">
                                <label class="medicine-field-label">&nbsp;</label>
                                <button type="button" onclick="removeMedicineEntry(this)" class="medicine-remove-btn" <?= $isEdit ? 'disabled' : '' ?> title="Remove this entry">
                                    <i class="fas fa-times"></i>
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="medicine-form-footer">
                <div class="medicine-form-footer-left">
                <?php if (!$isEdit): ?>
                    <button type="button" onclick="addMoreMedicine()" class="btn-medicine-add-more">
                        <i class="fas fa-plus"></i> Add More Medicine
                </button>
                <?php endif; ?>
                </div>
                <div class="medicine-form-footer-right">
                    <button type="button" onclick="closeForm()" class="btn-medicine-cancel">
                        Cancel
                    </button>
                    <button type="submit" class="btn-medicine-save">
                        <i class="fas fa-save"></i> <?= $isEdit ? 'Update Medicine' : 'Save All Medicines' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Page mode flags from PHP (edit vs add)
    const IS_EDIT = <?= isset($isEdit) && $isEdit ? 'true' : 'false' ?>;
    const OPEN_ADD = <?= isset($open_add_modal) && $open_add_modal ? 'true' : 'false' ?>;
    const ADD_URL = '<?= base_url('medicines?add=1') ?>';
    const LIST_URL = '<?= base_url('medicines') ?>';
    const ERROR_MSG = <?= json_encode(isset($errorFlash) ? (string)$errorFlash : '') ?>;

    // Toggle form visibility
    function toggleForm() {
        // If current page was rendered in edit mode, reload in add mode to reset DOM
        if (IS_EDIT) {
            window.location.href = ADD_URL;
            return;
        }
        const form = document.getElementById('medicineForm');
        form.style.display = 'flex';
        // Reset form when opening
        const formEl = document.getElementById('medicineFormElement');
        if (formEl) formEl.reset();
        // Reset to one entry
        const entries = document.getElementById('medicineEntries');
        if (entries) {
            const firstEntry = entries.querySelector('.medicine-entry');
            if (firstEntry) {
                entries.innerHTML = '';
                entries.appendChild(firstEntry);
            }
        }
        // Refresh suggestions each time modal opens
        if (typeof refreshAutocompleteLists === 'function') {
            refreshAutocompleteLists();
        }
    }

    function closeForm() {
        const form = document.getElementById('medicineForm');
        if (IS_EDIT) {
            // Leaving edit mode should clear the edit state
            window.location.href = LIST_URL;
            return;
        }
        form.style.display = 'none';
    }

    // Add more medicine entry
    function addMoreMedicine() {
        const entries = document.getElementById('medicineEntries');
        const newEntry = entries.querySelector('.medicine-entry').cloneNode(true);
        // Clear input values in the new entry
        newEntry.querySelectorAll('input').forEach(input => {
            input.value = '';
            // Clear image preview if it exists
            if (input.type === 'file') {
                const preview = newEntry.querySelector('.newImagePreviewContainer');
                const inputWrapper = newEntry.querySelector('.image-input-wrapper');
                if (preview) {
                    preview.style.display = 'none';
                }
                if (inputWrapper) {
                    inputWrapper.style.display = 'block';
                }
            }
        });
        newEntry.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        // Remove current image container if it exists in the new entry
        const currentImageContainer = newEntry.querySelector('#currentImageContainer');
        if (currentImageContainer) {
            currentImageContainer.remove();
        }
        const removeImageFlag = newEntry.querySelector('#removeImageFlag');
        if (removeImageFlag) {
            removeImageFlag.remove();
        }
        entries.appendChild(newEntry);
    }

    // Remove medicine entry
    function removeMedicineEntry(button) {
        const entries = document.getElementById('medicineEntries');
        if (entries.children.length > 1) {
            button.closest('.medicine-entry').remove();
        } else {
            // If it's the last entry, just clear the inputs
            const entry = button.closest('.medicine-entry');
            entry.querySelectorAll('input').forEach(input => input.value = '');
            entry.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        }
    }

    // Preview image when file is selected
    function previewImage(input) {
        if (!input || !input.files || !input.files[0]) {
            return;
        }
        
        // Find the closest medicine entry container
        const entry = input.closest('.medicine-entry');
        if (!entry) return;
        
        // Find preview container and input wrapper within this entry
        const preview = entry.querySelector('.newImagePreviewContainer');
        const previewImg = preview ? preview.querySelector('.previewImg') : null;
        const inputWrapper = entry.querySelector('.image-input-wrapper');
        const currentPreview = entry.querySelector('#currentImagePreview');
        const removeFlag = entry.querySelector('#removeImageFlag');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) {
                previewImg.src = e.target.result;
            }
            if (preview) {
                preview.style.display = 'block';
            }
            // Hide the file input when image is selected
            if (inputWrapper) {
                inputWrapper.style.display = 'none';
            }
            // Dim current image preview when new image is selected
            if (currentPreview) {
                currentPreview.style.opacity = '0.5';
            }
            // Reset remove flag when new image is selected
            if (removeFlag) {
                removeFlag.value = '0';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
    
    // Clear image preview
    function clearImagePreview(button) {
        const entry = button.closest('.medicine-entry');
        if (!entry) return;
        
        const preview = entry.querySelector('.newImagePreviewContainer');
        const input = entry.querySelector('.image-input');
        const inputWrapper = entry.querySelector('.image-input-wrapper');
        const currentContainer = entry.querySelector('#currentImageContainer');
        
        if (input) {
            input.value = '';
        }
        if (preview) {
            preview.style.display = 'none';
        }
        // Show the file input again when image is removed
        if (inputWrapper) {
            inputWrapper.style.display = 'block';
        }
        // Show current image container again if it exists
        if (currentContainer) {
            currentContainer.style.display = 'block';
        }
    }

    // Remove current image
    function removeCurrentImage() {
        const entry = document.querySelector('.medicine-entry');
        if (!entry) return;
        
        const container = entry.querySelector('#currentImageContainer');
        const removeFlag = entry.querySelector('#removeImageFlag');
        const input = entry.querySelector('.image-input');
        const inputWrapper = entry.querySelector('.image-input-wrapper');
        const preview = entry.querySelector('.newImagePreviewContainer');
        
        if (confirm('Are you sure you want to remove the current image?')) {
            if (container) {
                container.style.display = 'none';
            }
            if (removeFlag) {
                removeFlag.value = '1';
            }
            if (input) {
                input.value = '';
            }
            if (preview) {
                preview.style.display = 'none';
            }
            // Show the file input when current image is removed
            if (inputWrapper) {
                inputWrapper.style.display = 'block';
            }
        }
    }

    // Let the form submit normally to the backend. No client-side interception.
    
    // Stats are rendered from PHP variables on page load.
    
    // Medicine Description Modal Functions
    function showMedicineDetails(medicine) {
        const modal = document.getElementById('medicineDetailsModal');
        const content = document.getElementById('medicineDetailsContent');
        
        if (!medicine) return;
        
        const imageUrl = medicine.image ? '<?= base_url('uploads/medicines/') ?>' + medicine.image : '';
        const hasImage = medicine.image && imageUrl;
        const medicineName = medicine.name || 'Medicine';
        const description = medicine.description && medicine.description.trim() ? medicine.description : null;
        
        content.innerHTML = `
            <div class="medicine-details-container">
                ${hasImage ? `
                <div class="medicine-details-image-wrapper">
                    <img src="${imageUrl}" alt="${medicineName}" class="medicine-details-image">
                </div>
                ` : ''}
                <div class="medicine-details-name-wrapper">
                    <h4 class="medicine-details-name">${medicineName}</h4>
                </div>
                ${description ? `
                <div class="medicine-details-description">
                    <p class="medicine-details-description-text">${description}</p>
                </div>
                ` : `
                <div class="medicine-details-no-description">
                    <p class="medicine-details-no-description-text">No description available for this medicine.</p>
                </div>
                `}
            </div>
        `;
        
        modal.style.display = 'flex';
    }
    
    function closeMedicineDetails() {
        const modal = document.getElementById('medicineDetailsModal');
        modal.style.display = 'none';
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('medicineDetailsModal');
        if (event.target === modal) {
            closeMedicineDetails();
        }
    });
    
    // --- Autocomplete helpers ---
    const defaultMedicineNames = [];
    const defaultBrands = [];
    const defaultCategories = [];

    function uniqueSorted(arr) {
        return Array.from(new Set(arr.filter(Boolean))).sort((a,b)=>a.localeCompare(b));
    }

    function gatherFromTable() {
        const rows = document.querySelectorAll('#medicineTableBody tr');
        const names = [];
        const brands = [];
        const categories = [];
        rows.forEach(r => {
            const tds = r.querySelectorAll('td');
            if (tds[1]) names.push(tds[1].textContent.trim());
            if (tds[2]) brands.push(tds[2].textContent.trim());
            if (tds[3]) categories.push(tds[3].textContent.trim());
        });
        return { names: uniqueSorted(names), brands: uniqueSorted(brands), categories: uniqueSorted(categories) };
    }

    function renderDatalist(listId, values) {
        const dl = document.getElementById(listId);
        if (!dl) return;
        dl.innerHTML = values.map(v => `<option value="${v.replace(/"/g,'&quot;')}"></option>`).join('');
    }

    function refreshAutocompleteLists() {
        const fromTable = gatherFromTable();
        const names = uniqueSorted(fromTable.names || []);
        const brands = uniqueSorted(fromTable.brands || []);
        const categories = uniqueSorted(fromTable.categories || []);
        renderDatalist('medicineNamesList', names);
        renderDatalist('brandList', brands);
        renderDatalist('categoryList', categories);
    }

    function addToDatalist(listId, value){
        const v = (value || '').trim();
        if (!v) return;
        const dl = document.getElementById(listId);
        if (!dl) return;
        const exists = Array.from(dl.options).some(o => (o.value || '').toLowerCase() === v.toLowerCase());
        if (!exists){
            const opt = document.createElement('option');
            opt.value = v;
            dl.appendChild(opt);
        }
    }

    document.addEventListener('input', function(e){
        const t = e.target;
        if (!t) return;
        if (t.matches("input[list='medicineNamesList'], input[name='name[]'], input[name='name']")){
            addToDatalist('medicineNamesList', t.value);
        }
        if (t.matches("input[list='brandList'], input[name='brand[]'], input[name='brand']")){
            addToDatalist('brandList', t.value);
        }
        if (t.matches("input[list='categoryList'], input[name='category[]'], input[name='category']")){
            addToDatalist('categoryList', t.value);
        }
    });
    
    // Search functionality
    function filterMedicineTable() {
        const searchInput = document.getElementById('medicineSearch');
        const clearBtn = document.getElementById('clearSearch');
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // Show/hide clear button
        if (searchTerm.length > 0) {
            clearBtn.classList.add('show');
        } else {
            clearBtn.classList.remove('show');
        }
        
        const rows = document.querySelectorAll('#medicineTableBody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            const barcode = (tds[0]?.textContent || '').toLowerCase();
            const name = (tds[1]?.textContent || '').toLowerCase();
            const brand = (tds[2]?.textContent || '').toLowerCase();
            const category = (tds[3]?.textContent || '').toLowerCase();
            
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
                noResultsMsg.innerHTML = '<td colspan="10" class="no-results-row"><i class="fas fa-search no-results-icon"></i><p>No medicines found matching "' + searchTerm + '"</p></td>';
                document.getElementById('medicineTableBody').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Clear search
    document.getElementById('clearSearch')?.addEventListener('click', function() {
        document.getElementById('medicineSearch').value = '';
        this.classList.remove('show');
        filterMedicineTable();
    });

    // Initialize autocomplete options after table is populated
    document.addEventListener('DOMContentLoaded', function() {
        refreshAutocompleteLists();
        
        // Initialize search
        const searchInput = document.getElementById('medicineSearch');
        if (searchInput) {
            searchInput.addEventListener('input', filterMedicineTable);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    document.getElementById('clearSearch').classList.remove('show');
                    filterMedicineTable();
                }
            });
        }

        <?php if (isset($isEdit) && $isEdit): ?>
            const form = document.getElementById('medicineForm');
            form.style.display = 'flex';
        <?php endif; ?>
        <?php if (isset($open_add_modal) && $open_add_modal): ?>
            const formAdd = document.getElementById('medicineForm');
            formAdd.style.display = 'flex';
        <?php endif; ?>

        if (ERROR_MSG) {
            const modal = document.getElementById('errorModal');
            const body = document.getElementById('errorModalBody');
            const ok = document.getElementById('errorOkBtn');
            body.innerHTML = 'Please fix the following errors:<br><ul style="text-align:left;margin:8px 0 0 18px;"><li>' + ERROR_MSG + '</li></ul>';
            // Ensure modal is above any other overlay and visible
            modal.style.display = 'flex';
            modal.style.zIndex = 2000;
            ok.onclick = function() { modal.style.display = 'none'; };
        }
    });
</script>
<?= $this->endSection() ?>