<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Prescription Dispensing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/main.css') ?>" />

<div class="prescription-container">
    <!-- LEFT SIDE - Medicine Shelf / Patient Prescriptions -->
    <div class="prescription-main">
        <!-- Patient Search with Filter Buttons - Always Visible -->
        <div class="patient-search-section">
            <div class="search-filter-row">
                <div class="filter-buttons-group">
                    <button type="button" id="filterInpatient" class="btn btn-primary filter-btn active" data-filter="inpatient">
                        <i class="fas fa-bed"></i> Inpatient/Admitted
                    </button>
                    <button type="button" id="filterOPD" class="btn btn-outline-primary filter-btn" data-filter="opd">
                        <i class="fas fa-user-injured"></i> OPD
                    </button>
                </div>
                <div class="unified-search-wrapper" style="flex: 1; position: relative;">
                    <div class="unified-search-row">
                        <i class="fas fa-search unified-search-icon"></i>
                        <input type="text" id="patientSearch" class="unified-search-field" placeholder="Search admitted patient..." autocomplete="off">
                    </div>
                    <div id="patientDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #dee2e6; border-radius: 5px; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 5px;">
                    </div>
                </div>
            </div>
            <div id="patientInfo" style="display: none; padding: 15px; background: #f8f9fa; border-radius: 5px; margin-top: 15px; margin-bottom: 15px;">
                <div id="patientDetails"></div>
            </div>
            <div id="prescriptionMedicines" class="prescription-medicines-list" style="margin-bottom: 15px; display: none;">
            </div>
        </div>
        
        <!-- Unified Medicine Shelf (same for both Inpatient and OPD) -->
        <div class="card">
            <div class="card-header">
                <div class="medicine-shelf-header">
                    <i class="fas fa-pills"></i>
                    <span>Medicine Shelf</span>
                </div>
            </div>
            <div class="card-body medicine-shelf-body">
                <!-- Search Bar -->
                <div class="unified-search-wrapper">
                    <div class="unified-search-row">
                        <i class="fas fa-search unified-search-icon"></i>
                        <input type="text" id="medicineSearch" class="unified-search-field" placeholder="Search medicine">
                    </div>
                </div>
                
                <!-- Medicine Grid -->
                <div id="medicineGrid" class="medicine-grid">
                    <!-- Medicine cards will be loaded here via JavaScript -->
                    <div class="loading-container">
                        <i class="fas fa-spinner fa-spin loading-spinner"></i>
                        <p class="loading-text">Loading medicines...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
                
    <!-- RIGHT SIDE - Cart & Checkout / Billing Button -->
    <div class="prescription-sidebar">
        <!-- Inpatient Billing Section -->
        <div id="inpatientBilling" class="filter-view">
            <div class="card billing-card-wrapper">
                <div class="card-header">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Record to Billing</h3>
                </div>
                <div class="card-body billing-card-body">
                    <!-- Total Amount at Top -->
                    <div class="card total-amount-card" style="margin-bottom: 15px; flex-shrink: 0;">
                        <div class="card-body total-amount-body">
                            <div class="total-amount-row">
                                <span class="total-amount-label">Total Amount:</span>
                                <span id="inpatientTotal" class="total-amount-value">₱0.00</span>
                            </div>
                        </div>
                    </div>
                    <!-- Scrollable Medicines List -->
                    <div id="inpatientMedicinesList" class="billing-medicines-list">
                        <p style="color: #6c757d; text-align: center;">No medicines selected</p>
                    </div>
                    <!-- Fixed Button at Bottom -->
                    <div class="billing-button-container">
                        <button class="btn btn-primary btn-block checkout-btn" id="recordToBillingBtn" disabled>
                            <i class="fas fa-check-circle checkout-btn-icon"></i> Record to Billing
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- OPD Checkout Section -->
        <div id="opdCheckout" class="filter-view" style="display: none;">
            <!-- Total Amount at Top -->
            <div class="card total-amount-card" style="margin-bottom: 15px; flex-shrink: 0;">
                <div class="card-body total-amount-body">
                    <div class="total-amount-row">
                        <span class="total-amount-label">Total Amount:</span>
                        <span id="total" class="total-amount-value">₱0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- Cart with Scrollable Items -->
            <div class="card cart-card" style="flex: 1; min-height: 0; display: flex; flex-direction: column; margin-bottom: 15px;">
                <div class="card-header cart-header" style="flex-shrink: 0;">
                    <h3>
                        <i class="fas fa-shopping-cart cart-header-icon"></i>Cart Items
                    </h3>
                </div>
                <div class="card-body cart-body">
                    <div id="cartItems" class="cart-items-container">
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart cart-empty-icon"></i>
                            <p class="cart-empty-text">Your cart is empty</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fixed Checkout Area at Bottom -->
            <div class="card payment-card" style="flex-shrink: 0;">
                <div class="card-header payment-header">
                    <h3>Payment</h3>
                </div>
                <div class="card-body payment-body">
                    <div class="form-group payment-form-group">
                        <label for="amount_received" class="payment-label">Amount Received</label>
                        <div class="amount-input-group">
                            <span class="amount-prefix">₱</span>
                            <input type="number" id="amount_received" class="form-control amount-input" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="change-badge">
                            <span id="change_badge">Change: ₱0.00</span>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary btn-block checkout-btn" id="checkoutBtn">
                        <i class="fas fa-check-circle checkout-btn-icon"></i> Process Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
   const API_BASE = '<?= site_url('api/pharmacy') ?>';
   const BILLING_API = '<?= site_url('billing') ?>';

    let allMedicines = [];
    let filteredMedicines = [];
    let cart = []; // Unified cart for both OPD and Inpatient
    let currentFilter = 'inpatient'; // 'inpatient' or 'opd'
    let selectedPatient = null;
    let patientPrescriptions = [];
    let selectedPrescriptionMedicines = [];

    // Filter switching
    $('.filter-btn').on('click', function() {
        const filter = $(this).data('filter');
        currentFilter = filter;
        
        // Update button states
        $('.filter-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        
        // Show/hide billing/payment sections (medicine shelf stays the same)
        if (filter === 'inpatient') {
            $('#inpatientBilling').show();
            $('#opdCheckout').hide();
            // Load medicines if not loaded
            if (allMedicines.length === 0) {
                loadMedicines();
            } else {
                filteredMedicines = allMedicines;
                renderMedicineGrid();
            }
            // Update cart display for inpatient view
            updateCart();
        } else {
            $('#inpatientBilling').hide();
            $('#opdCheckout').show();
            // Load medicines if not loaded
            if (allMedicines.length === 0) {
                loadMedicines();
            } else {
                filteredMedicines = allMedicines;
                renderMedicineGrid();
            }
            // Update cart display for OPD view
            updateCart();
        }
    });

    function debounce(fn, wait) {
        let t;
        return function(...a) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, a), wait);
        };
    }

    // Load all medicines
    function loadMedicines() {
        fetch(API_BASE + '/medications?term=')
            .then(r => r.json())
            .then(data => {
                allMedicines = data || [];
                filteredMedicines = allMedicines;
                // Render unified medicine grid
                renderMedicineGrid();
            })
            .catch(err => {
                console.error('Error loading medicines:', err);
                $('#medicineGrid').html('<div class="error-message">Error loading medicines. Please refresh the page.</div>');
            });
    }

    // Render medicine grid
    function renderMedicineGrid() {
        const $grid = $('#medicineGrid');
        $grid.empty();

        if (filteredMedicines.length === 0) {
            $grid.html('<div class="loading-container">No medicines found</div>');
            return;
        }

        filteredMedicines.forEach(med => {
            const stock = Number(med.stock || 0);
            const status = med.status || null;
            
            // Skip medicines with 0 stock or expired_soon status
            if (stock <= 0 || status === 'expired_soon') {
                return;
            }

            // Determine image URL
            const placeholderUrl = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Crect fill=\'%23e9ecef\' width=\'120\' height=\'120\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'14\'%3ENo Image%3C/text%3E%3C/svg%3E';
            
            let imageUrl = placeholderUrl;
            
            // Use image_url if it exists and is valid
            if (med.image_url && typeof med.image_url === 'string' && med.image_url.trim() !== '' && med.image_url !== 'null') {
                imageUrl = med.image_url;
            }
            // Fallback: construct from image field if image_url is not available
            else if (med.image && typeof med.image === 'string' && med.image.trim() !== '' && med.image !== 'null') {
                const baseUrl = '<?= rtrim(config("App")->baseURL, "/") ?>';
                imageUrl = baseUrl + '/uploads/medicines/' + med.image;
            }
            
            // Show low stock warning (1-5 units) but don't block
            let stockDisplay = 'Stock: ' + stock;
            if (stock > 0 && stock <= 5) {
                stockDisplay = 'Stock: ' + stock + '<br><span class="low-stock-warning">(Low Stock)</span>';
            }
            
            const $card = $(`
                <div class="medicine-card" data-medicine-id="${med.id}">
                    <div class="medicine-card-image">
                        <img src="${imageUrl}" alt="${med.name}" onerror="this.onerror=null; this.src='${placeholderUrl}'" loading="lazy">
                    </div>
                    <div class="medicine-card-info">
                        <div class="medicine-name">${med.name}</div>
                        <div class="medicine-price">₱${Number(med.retail_price || med.price || 0).toFixed(2)}</div>
                        <div class="medicine-stock">${stockDisplay}</div>
                    </div>
                </div>
            `);

            $card.on('click', function() {
                addToCart(med.id);
            });

            $grid.append($card);
        });
    }

    // Unified add to cart function for both OPD and Inpatient
    function addToCart(medicineId) {
        const medicine = allMedicines.find(m => m.id === medicineId);
        if (!medicine) return;

        const stock = Number(medicine.stock || 0);
        const status = medicine.status || null;
        
        // Block if expired_soon
        if (status === 'expired_soon') {
            alert('This medicine is expiring soon and cannot be added to cart.');
            return;
        }
        
        // Only block if stock is 0 (out of stock)
        // Low stock (1-5) is allowed but will show a warning
        if (stock <= 0) {
            alert('This medicine is out of stock.');
            return;
        }
        
        // Show low stock warning (non-blocking) if stock is 5 or less
        if (stock <= 5) {
            console.log('Low stock warning: ' + medicine.name + ' has only ' + stock + ' units remaining.');
        }
        
        // Check if already in cart
        const existing = cart.find(item => item.medicationId === medicineId);
        if (existing) {
            // If already in cart, just focus the quantity input
            updateCart();
            setTimeout(() => {
                const inputSelector = currentFilter === 'inpatient' ? '.cart-qty-input-inpatient' : '.cart-qty-input';
                const $input = $(inputSelector + `[data-medicine-id="${medicineId}"]`);
                if ($input.length) {
                    $input.focus();
                }
            }, 100);
            return;
        }
        
        // Add to cart with quantity 0 (empty field)
        cart.push({
            id: Date.now(),
            medicationId: medicineId,
            name: medicine.name,
            quantity: 0,
            price: Number(medicine.retail_price || medicine.price || 0),
            stock: stock
        });

        updateCart();
        
        // Focus the quantity input for the newly added item
        setTimeout(() => {
            const inputSelector = currentFilter === 'inpatient' ? '.cart-qty-input-inpatient' : '.cart-qty-input';
            const $input = $(inputSelector + `[data-medicine-id="${medicineId}"]`);
            if ($input.length) {
                $input.focus();
            }
        }, 100);
    }
    
    // Update stock display in medicine grid
    function updateStockDisplay(medicineId, newStock) {
        const $card = $(`.medicine-card[data-medicine-id="${medicineId}"]`);
        const med = allMedicines.find(m => m.id === medicineId);
        
        // Update in allMedicines array
        if (med) {
            med.stock = newStock;
        }
        
        if ($card.length) {
            // Card exists, update stock display
            const $stockEl = $card.find('.medicine-stock');
            
            // Show low stock warning (1-5 units) but don't block
            if (newStock > 0 && newStock <= 5) {
                $stockEl.html('Stock: ' + newStock + '<br><span class="low-stock-warning">(Low Stock)</span>');
            } else if (newStock > 5) {
                $stockEl.text('Stock: ' + newStock);
            } else {
                $stockEl.text('Stock: ' + newStock);
            }
            
            // After checkout, if stock = 0, remove card (medicine moved to Out of Stock)
            // This function is only called after checkout when reloading medicines
            if (newStock <= 0) {
                $card.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                // Ensure card is enabled if stock is available
                $card.removeClass('out-of-stock');
            }
        } else if (newStock > 0 && med) {
            // Card doesn't exist but stock is > 0, recreate it
            // Check if it should be visible (matches current filter)
            const shouldShow = filteredMedicines.some(m => m.id === medicineId);
            if (!shouldShow) {
                // Add to filteredMedicines if it matches the current search
                const searchTerm = $('#medicineSearch').val().toLowerCase().trim();
                if (searchTerm === '' || 
                    (med.name && med.name.toLowerCase().includes(searchTerm)) ||
                    (med.brand && med.brand.toLowerCase().includes(searchTerm))) {
                    filteredMedicines.push(med);
                } else {
                    return; // Don't show if it doesn't match search
                }
            }
            
            // Recreate the card
            recreateMedicineCard(med);
        }
    }
    
    // Recreate a medicine card in the grid
    function recreateMedicineCard(med) {
        const stock = Number(med.stock || 0);
        if (stock <= 0) return;
        
        // Check if card already exists
        const $existing = $(`.medicine-card[data-medicine-id="${med.id}"]`);
        if ($existing.length) return;
        
        // Determine image URL
        const placeholderUrl = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Crect fill=\'%23e9ecef\' width=\'120\' height=\'120\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-family=\'Arial\' font-size=\'14\'%3ENo Image%3C/text%3E%3C/svg%3E';
        
        let imageUrl = placeholderUrl;
        
        if (med.image_url && typeof med.image_url === 'string' && med.image_url.trim() !== '' && med.image_url !== 'null') {
            imageUrl = med.image_url;
        } else if (med.image && typeof med.image === 'string' && med.image.trim() !== '' && med.image !== 'null') {
            const baseUrl = '<?= rtrim(config("App")->baseURL, "/") ?>';
            imageUrl = baseUrl + '/uploads/medicines/' + med.image;
        }
        
        // Show low stock warning (1-5 units) but don't block
        let stockDisplay = 'Stock: ' + stock;
        if (stock > 0 && stock <= 5) {
            stockDisplay = 'Stock: ' + stock + '<br><span class="low-stock-warning">(Low Stock)</span>';
        }
        
        const $card = $(`
            <div class="medicine-card" data-medicine-id="${med.id}">
                <div class="medicine-card-image">
                    <img src="${imageUrl}" alt="${med.name}" onerror="this.onerror=null; this.src='${placeholderUrl}'" loading="lazy">
                </div>
                    <div class="medicine-card-info">
                        <div class="medicine-name">${med.name}</div>
                        <div class="medicine-price">₱${Number(med.retail_price || med.price || 0).toFixed(2)}</div>
                        <div class="medicine-stock">${stockDisplay}</div>
                    </div>
            </div>
        `);
        
        $card.on('click', function() {
            addToCart(med.id);
        });
        
        // Insert card in appropriate position (maintain grid order)
        const $grid = $('#medicineGrid');
        let inserted = false;
        $grid.find('.medicine-card').each(function() {
            const cardId = $(this).data('medicine-id');
            const cardMed = allMedicines.find(m => m.id === cardId);
            if (cardMed && med.name && cardMed.name && med.name.localeCompare(cardMed.name) < 0) {
                $card.insertBefore($(this));
                inserted = true;
                return false; // break
            }
        });
        
        if (!inserted) {
            $grid.append($card);
        }
        
        // Fade in animation
        $card.hide().fadeIn(300);
    }
    
    // Unified cart update function for both OPD and Inpatient
    function updateCart() {
        if (currentFilter === 'opd') {
            updateOPDCart();
        } else {
            updateInpatientCart();
        }
    }
    
    // Update OPD cart display
    function updateOPDCart() {
        const $cartItems = $('#cartItems');
        $cartItems.empty();
        
        if (cart.length === 0) {
            $cartItems.html(`
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart cart-empty-icon"></i>
                    <p class="cart-empty-text">Your cart is empty</p>
                </div>
            `);
            updateTotal();
            return;
        }
        
        cart.forEach((item, index) => {
            const medicine = allMedicines.find(m => m.id === item.medicationId);
            const availableStock = Number(medicine?.stock || 0);
            const maxQuantity = (item.quantity || 0) + availableStock;
            const displayValue = item.quantity > 0 ? item.quantity : '';
            
            const $item = $(`
                <div class="cart-item-simple-row" data-index="${index}" data-medicine-id="${item.medicationId}">
                    <div class="cart-item-simple-name">${item.name}</div>
                    <div class="cart-item-simple-price">₱${item.price.toFixed(2)}</div>
                    <div class="cart-item-simple-qty">
                        <input type="number" 
                               class="cart-qty-input" 
                               data-index="${index}" 
                               value="${displayValue}" 
                               placeholder="0"
                               min="1" 
                               max="${maxQuantity}"
                               data-medicine-id="${item.medicationId}">
                    </div>
                    <button type="button" class="cart-item-remove-btn" data-index="${index}" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            $cartItems.append($item);
        });
        
        updateTotal();
    }

    // Unified total update function for both OPD and Inpatient
    function updateTotal() {
        if (currentFilter === 'opd') {
            const total = cart.reduce((sum, item) => sum + (item.price * (item.quantity || 0)), 0);
            $('#total').text('₱' + total.toFixed(2));
            if (document.getElementById('amount_received')) {
                setTimeout(updateChange, 0);
            }
        } else {
            // Inpatient: Calculate from cart + selected prescription medicines
            const medicineMap = new Map();
            
            // Add selected prescription medicines
            if (selectedPrescriptionMedicines && selectedPrescriptionMedicines.length > 0) {
                selectedPrescriptionMedicines.forEach(med => {
                    if (med.selected) {
                        const key = med.id + '_' + med.price;
                        const inCart = cart.some(c => c.medicationId === med.id && c.price === med.price);
                        if (!inCart) {
                            medicineMap.set(key, {
                                quantity: med.quantity || 0,
                                price: med.price
                            });
                        }
                    }
                });
            }
            
            // Add cart medicines (override prescription if same)
            cart.forEach(item => {
                const key = item.medicationId + '_' + item.price;
                medicineMap.set(key, {
                    quantity: item.quantity || 0,
                    price: item.price
                });
            });
            
            // Calculate total
            let total = 0;
            medicineMap.forEach(item => {
                total += item.price * item.quantity;
            });
            
            $('#inpatientTotal').text('₱' + total.toFixed(2));
            $('#recordToBillingBtn').prop('disabled', total <= 0);
        }
    }

    // Handle quantity input changes
    $(document).on('input change blur', '.cart-qty-input', function() {
        const index = parseInt($(this).data('index'));
        const medicineId = $(this).data('medicine-id');
        const inputValue = $(this).val().trim();
        let newQuantity = inputValue === '' ? 0 : parseInt(inputValue) || 0;
        
        const item = cart[index];
        if (!item) return;
        
        const medicine = allMedicines.find(m => m.id === medicineId);
        const availableStock = Number(medicine?.stock || 0);
        const currentQuantity = item.quantity || 0;
        
        // If field is empty or 0, set quantity to 0 but don't remove item
        if (newQuantity <= 0) {
            item.quantity = 0;
            updateCart();
            return;
        }
        
        // Validate stock availability (check against current available stock)
        // Don't reserve stock - just validate that requested quantity doesn't exceed available
        if (newQuantity > availableStock) {
            alert('Not enough stock. Available: ' + availableStock);
            $(this).val(currentQuantity > 0 ? currentQuantity : '');
            return;
        }
        
        // Update quantity (stock will be decreased only on checkout)
        item.quantity = newQuantity;
        updateCart();
    });
    
    // Unified remove item handler for both OPD and Inpatient
    $(document).on('click', '.cart-item-remove-btn', function() {
        if (currentFilter === 'opd') {
            // OPD: Remove from cart
            const index = parseInt($(this).data('index'));
            cart.splice(index, 1);
            updateCart();
        } else {
            // Inpatient: Remove from cart or unselect prescription
            const $row = $(this).closest('.cart-item-simple-row');
            const medicineId = $row.data('medicine-id');
            const itemPrice = parseFloat($row.find('.cart-item-simple-price').text().replace('₱', '').trim()) || 0;
            
            const cartItem = cart.find(c => c.medicationId === medicineId && c.price === itemPrice);
            if (cartItem) {
                // Remove from cart
                cart.splice(cart.indexOf(cartItem), 1);
            } else {
                // Unselect prescription medicine
                const prescMed = selectedPrescriptionMedicines.find(m => m.id === medicineId && m.price === itemPrice);
                if (prescMed) {
                    prescMed.selected = false;
                }
            }
            updateCart();
        }
    });
    



    // Update inpatient cart display (combines prescription medicines with cart)
    function updateInpatientCart() {
        // Combine prescription medicines and cart medicines into a unified list
        // Priority: Cart items override prescription items (user is actively editing cart)
        const allSelectedMedicines = [];
        const medicineMap = new Map();
        
        // First, add selected prescription medicines (only if not in cart)
        if (selectedPrescriptionMedicines && selectedPrescriptionMedicines.length > 0) {
            selectedPrescriptionMedicines.forEach(med => {
                if (med.selected) {
                    const key = med.id + '_' + med.price;
                    // Only add if not already in cart
                    const inCart = cart.some(c => c.medicationId === med.id && c.price === med.price);
                    if (!inCart) {
                        medicineMap.set(key, {
                            id: med.id,
                            name: med.name,
                            quantity: med.quantity || 0,
                            price: med.price,
                            total: med.price * (med.quantity || 0),
                            source: 'prescription',
                            stock: med.stock || 999
                        });
                    }
                }
            });
        }
        
        // Then add cart medicines (these override prescription if same medicine/price)
        cart.forEach(item => {
            const key = item.medicationId + '_' + item.price;
            medicineMap.set(key, {
                id: item.medicationId,
                name: item.name,
                quantity: item.quantity || 0,
                price: item.price,
                total: item.price * (item.quantity || 0),
                source: 'cart',
                stock: item.stock
            });
        });
        
        // Convert map to array
        allSelectedMedicines.push(...medicineMap.values());
        
        // Update the display
        const $container = $('#inpatientMedicinesList');
        const $btn = $('#recordToBillingBtn');
        
        if (allSelectedMedicines.length === 0) {
            $container.html('<div class="cart-empty"><i class="fas fa-shopping-cart cart-empty-icon"></i><p class="cart-empty-text">No medicines selected</p></div>');
            $btn.prop('disabled', true);
            $('#inpatientTotal').text('₱0.00');
            return;
        }
        
        // Render similar to OPD cart items
        $container.empty();
        allSelectedMedicines.forEach((item, index) => {
            const medicine = allMedicines.find(m => m.id === item.id);
            const availableStock = Number(medicine?.stock || item.stock || 0);
            const maxQuantity = (item.quantity || 0) + availableStock;
            const displayValue = item.quantity > 0 ? item.quantity : '';
            
            const $item = $(`
                <div class="cart-item-simple-row" data-index="${index}" data-medicine-id="${item.id}">
                    <div class="cart-item-simple-name">${item.name}</div>
                    <div class="cart-item-simple-price">₱${item.price.toFixed(2)}</div>
                    <div class="cart-item-simple-qty">
                        <input type="number" 
                               class="cart-qty-input-inpatient" 
                               data-index="${index}" 
                               value="${displayValue}" 
                               placeholder="0"
                               min="1" 
                               max="${maxQuantity}"
                               data-medicine-id="${item.id}">
                    </div>
                    <button type="button" class="cart-item-remove-btn" data-index="${index}" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            $container.append($item);
        });
        
        // Calculate and update total (only count items with quantity > 0)
        const total = allSelectedMedicines.reduce((sum, item) => {
            const qty = item.quantity || 0;
            return sum + (item.price * qty);
        }, 0);
        $('#inpatientTotal').text('₱' + total.toFixed(2));
        $btn.prop('disabled', total <= 0);
    }

    // Unified quantity input handler for both OPD and Inpatient
    $(document).on('input change blur', '.cart-qty-input, .cart-qty-input-inpatient', function() {
        const index = parseInt($(this).data('index'));
        const medicineId = $(this).data('medicine-id');
        const inputValue = $(this).val().trim();
        let newQuantity = inputValue === '' ? 0 : parseInt(inputValue) || 0;
        
        if (currentFilter === 'opd') {
            // OPD: Update cart directly
            const item = cart[index];
            if (!item) return;
            
            const medicine = allMedicines.find(m => m.id === medicineId);
            const availableStock = Number(medicine?.stock || 0);
            const currentQuantity = item.quantity || 0;
            
            if (newQuantity <= 0) {
                item.quantity = 0;
                updateCart();
                return;
            }
            
            if (newQuantity > availableStock) {
                alert('Not enough stock. Available: ' + availableStock);
                $(this).val(currentQuantity > 0 ? currentQuantity : '');
                return;
            }
            
            item.quantity = newQuantity;
            updateCart();
        } else {
            // Inpatient: Update cart or prescription medicine
            const $row = $(this).closest('.cart-item-simple-row');
            const itemPrice = parseFloat($row.find('.cart-item-simple-price').text().replace('₱', '').trim()) || 0;
            
            const cartItem = cart.find(c => c.medicationId === medicineId && c.price === itemPrice);
            const isFromCart = !!cartItem;
            
            if (newQuantity <= 0) {
                if (isFromCart && cartItem) {
                    cartItem.quantity = 0;
                } else {
                    const prescMed = selectedPrescriptionMedicines.find(m => m.id === medicineId && m.price === itemPrice);
                    if (prescMed) {
                        prescMed.quantity = 0;
                        prescMed.total = 0;
                    }
                }
                updateCart();
                return;
            }
            
            const medicine = allMedicines.find(m => m.id === medicineId);
            const availableStock = Number(medicine?.stock || 0);
            
            if (newQuantity > availableStock) {
                alert('Not enough stock. Available: ' + availableStock);
                const currentQty = isFromCart 
                    ? (cartItem ? (cartItem.quantity || 0) : 0)
                    : (() => {
                        const prescMed = selectedPrescriptionMedicines.find(m => m.id === medicineId && m.price === itemPrice);
                        return prescMed ? (prescMed.quantity || 0) : 0;
                    })();
                $(this).val(currentQty > 0 ? currentQty : '');
                return;
            }
            
            if (isFromCart && cartItem) {
                cartItem.quantity = newQuantity;
            } else {
                const prescMed = selectedPrescriptionMedicines.find(m => m.id === medicineId && m.price === itemPrice);
                if (prescMed) {
                    prescMed.quantity = newQuantity;
                    prescMed.total = prescMed.price * newQuantity;
                }
            }
            
            updateCart();
        }
    });
    

    // Search medicines for OPD
    $('#medicineSearch').on('input', debounce(function() {
        const term = $(this).val().toLowerCase().trim();
        if (term === '') {
            filteredMedicines = allMedicines;
        } else {
            filteredMedicines = allMedicines.filter(m => 
                (m.name && m.name.toLowerCase().includes(term)) ||
                (m.brand && m.brand.toLowerCase().includes(term))
            );
        }
        renderMedicineGrid();
    }, 300));


    // Change calculator
    const formatMoney = v => '₱' + (Number(v) || 0).toFixed(2);
    function getTotal() {
        return parseFloat($('#total').text().replace('₱', '')) || 0;
    }
    function updateChange() {
        const paid = parseFloat($('#amount_received').val() || '0');
        const change = paid - getTotal();
        $('#change_badge').text('Change: ' + formatMoney(Math.max(change, 0)));
    }
    $(document).on('input', '#amount_received', updateChange);
    
    // Checkout
    $('#checkoutBtn').on('click', function() {
        // Filter out items with quantity 0 or empty
        const validCartItems = cart.filter(item => item.quantity > 0);
        
        if (validCartItems.length === 0) {
            alert('Your cart is empty or no items have quantity entered.');
            return;
        }
        
        const amountReceived = parseFloat($('#amount_received').val() || '0');
        const totalDue = getTotal();
        if (amountReceived < totalDue) {
            alert('Amount received is insufficient to cover the total.');
            return;
        }
        
        const total = validCartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        const orderData = {
            patient_id: '',
            patient_name: '',
            items: validCartItems.map(i => ({
                medicine_id: i.medicationId,
                medicine_name: i.name,
                quantity: i.quantity,
                price: Number(i.price)
            })),
            payment_method: 'cash',
            subtotal: total,
            tax: 0,
            total: total,
            amount_paid: amountReceived,
            change: Math.max(amountReceived - total, 0),
            date: '<?= date('Y-m-d') ?>'
        };

        // Disable checkout button to prevent double submission
        const $checkoutBtn = $('#checkoutBtn');
        $checkoutBtn.prop('disabled', true).text('Processing...');
        
        fetch(API_BASE + '/transaction/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) {
                let errorMsg = resp.message || 'Transaction failed';
                if (resp.errors) {
                    const errorList = Object.values(resp.errors).join(', ');
                    errorMsg += ': ' + errorList;
                }
                throw new Error(errorMsg);
            }
            alert(`Transaction created! #${resp.transaction_number}`);
            
            // Reset form - remove items that were checked out
            cart = cart.filter(item => !validCartItems.some(v => v.id === item.id));
            updateCart();
            $('#amount_received').val('');
            
            // Reload medicines to update stock after checkout
            // Medicines with stock = 0 will be removed from shelf and appear in Out of Stock
            loadMedicines();
        })
        .catch(err => {
            alert('Error: ' + err.message);
            // Re-enable checkout button on error
            $checkoutBtn.prop('disabled', false).html('<i class="fas fa-check-circle checkout-btn-icon"></i> Process Checkout');
        })
        .finally(() => {
            // Re-enable checkout button
            $checkoutBtn.prop('disabled', false).html('<i class="fas fa-check-circle checkout-btn-icon"></i> Process Checkout');
        });
    });

    // Patient search for inpatient
    let patientSearchTimeout;
    $('#patientSearch').on('input', function() {
        clearTimeout(patientSearchTimeout);
        const term = $(this).val().trim();
        
        if (term.length < 2) {
            $('#patientDropdown').hide();
            $('#patientInfo').hide();
            $('#prescriptionMedicines').hide();
            selectedPatient = null;
            patientPrescriptions = [];
            selectedPrescriptionMedicines = [];
            cart = [];
            updateCart();
            return;
        }
        
        patientSearchTimeout = setTimeout(() => {
            searchAdmittedPatients(term);
        }, 300);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#patientSearch, #patientDropdown').length) {
            $('#patientDropdown').hide();
        }
    });

    // Search admitted patients and show dropdown
    function searchAdmittedPatients(term) {
        fetch(API_BASE + '/admitted-patients?term=' + encodeURIComponent(term))
            .then(r => r.json())
            .then(data => {
                const $dropdown = $('#patientDropdown');
                $dropdown.empty();
                
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(patient => {
                        const patientName = `${patient.first_name} ${patient.last_name}`;
                        const $item = $(`
                            <div class="patient-dropdown-item" style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;" data-patient-id="${patient.id}">
                                <div style="font-weight: 600;">${patientName}</div>
                                <div style="font-size: 0.85em; color: #6c757d;">
                                    ID: ${patient.id} | Ward: ${patient.admission_ward || 'N/A'} | Room: ${patient.admission_room || 'N/A'}
                                </div>
                            </div>
                        `);
                        
                        $item.on('click', function() {
                            selectPatient(patient);
                            $('#patientSearch').val(patientName);
                            $dropdown.hide();
                        });
                        
                        $item.on('mouseenter', function() {
                            $(this).css('background-color', '#f8f9fa');
                        }).on('mouseleave', function() {
                            $(this).css('background-color', 'white');
                        });
                        
                        $dropdown.append($item);
                    });
                    $dropdown.show();
                } else {
                    $dropdown.html('<div style="padding: 10px 15px; color: #6c757d;">No patients found</div>');
                    $dropdown.show();
                }
            })
            .catch(err => {
                console.error('Error searching patients:', err);
                $('#patientDropdown').hide();
            });
    }

    // Select patient and load prescriptions
    function selectPatient(patient) {
        selectedPatient = patient;
        $('#patientInfo').show();
        $('#patientDetails').html(`
            <strong>${patient.first_name} ${patient.last_name}</strong><br>
            <small>Patient ID: ${patient.id} | Ward: ${patient.admission_ward || 'N/A'} | Room: ${patient.admission_room || 'N/A'}</small>
        `);
        
        // Load prescriptions for this patient
        loadPatientPrescriptions(patient.id);
    }

    // Load prescriptions for patient
    function loadPatientPrescriptions(patientId) {
        $('#prescriptionMedicines').show().html('<div class="loading-container"><i class="fas fa-spinner fa-spin loading-spinner"></i><p class="loading-text">Loading prescriptions...</p></div>');
        
        fetch(API_BASE + '/patient-prescriptions?patient_id=' + encodeURIComponent(patientId))
            .then(r => r.json())
            .then(data => {
                if (data.success && data.prescriptions) {
                    patientPrescriptions = data.prescriptions;
                    renderPrescriptionMedicines();
                } else {
                    $('#prescriptionMedicines').hide();
                    patientPrescriptions = [];
                    updateInpatientCart();
                }
            })
            .catch(err => {
                console.error('Error loading prescriptions:', err);
                $('#prescriptionMedicines').hide();
            });
    }

    // Render prescription medicines
    function renderPrescriptionMedicines() {
        const $container = $('#prescriptionMedicines');
        $container.empty();
        $container.show();
        
        if (patientPrescriptions.length === 0) {
            $container.hide();
            selectedPrescriptionMedicines = [];
            updateInpatientCart();
            return;
        }
        
        // Collect all medicines from all prescriptions
        const allMedicinesMap = {};
        patientPrescriptions.forEach(prescription => {
            if (prescription.items && prescription.items.length > 0) {
                prescription.items.forEach(item => {
                    const medId = item.medication_id || item.medicine_id;
                    const medName = item.medicine_name || item.name || 'Unknown';
                    const medPrice = parseFloat(item.price || item.unit_price || 0);
                    const medQuantity = parseInt(item.quantity || 0);
                    const medTotal = parseFloat(item.total || (medQuantity * medPrice) || 0);
                    
                    // Create unique key to handle same medicine from different prescriptions
                    const uniqueKey = medId + '_' + medPrice;
                    
                    if (!allMedicinesMap[uniqueKey]) {
                        allMedicinesMap[uniqueKey] = {
                            id: medId,
                            name: medName,
                            quantity: 0,
                            price: medPrice,
                            total: 0,
                            selected: false,
                            prescription_id: prescription.id
                        };
                    }
                    allMedicinesMap[uniqueKey].quantity += medQuantity;
                    allMedicinesMap[uniqueKey].total += medTotal;
                });
            }
        });
        
        const medicinesList = Object.values(allMedicinesMap);
        
        if (medicinesList.length === 0) {
            $container.hide();
            selectedPrescriptionMedicines = [];
            updateInpatientCart();
            return;
        }
        
        // Create a card for the medicines list
        const $medicinesCard = $('<div class="card"><div class="card-header"><h5 style="margin: 0;"><i class="fas fa-prescription-bottle-alt"></i> Prescribed Medicines</h5></div><div class="card-body"></div></div>');
        const $medicinesBody = $medicinesCard.find('.card-body');
        
        medicinesList.forEach(med => {
            const $medItem = $(`
                <div class="prescription-medicine-item" data-medicine-id="${med.id}" data-medicine-key="${med.id}_${med.price}">
                    <div style="display: flex; align-items: center; padding: 12px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px; transition: background-color 0.2s;">
                        <input type="checkbox" class="medicine-checkbox" data-medicine-id="${med.id}" data-medicine-key="${med.id}_${med.price}" style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; margin-bottom: 5px;">${med.name}</div>
                            <div style="font-size: 0.9em; color: #6c757d;">
                                Quantity: <strong>${med.quantity}</strong> | Unit Price: <strong>₱${med.price.toFixed(2)}</strong> | Total: <strong style="color: #28a745;">₱${med.total.toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Hover effect
            $medItem.find('.prescription-medicine-item > div').on('mouseenter', function() {
                $(this).css('background-color', '#f8f9fa');
            }).on('mouseleave', function() {
                if (!$medItem.find('.medicine-checkbox').is(':checked')) {
                    $(this).css('background-color', 'white');
                }
            });
            
            $medicinesBody.append($medItem);
        });
        
        $container.append($medicinesCard);
        
        // Handle checkbox changes - use event delegation on the container
        $medicinesBody.off('change', '.medicine-checkbox').on('change', '.medicine-checkbox', function() {
            const medKey = $(this).data('medicine-key');
            const medicine = medicinesList.find(m => (m.id + '_' + m.price) === medKey);
            if (medicine) {
                medicine.selected = $(this).is(':checked');
                const $item = $(this).closest('.prescription-medicine-item');
                if (medicine.selected) {
                    $item.find('> div').css('background-color', '#e7f3ff').css('border-color', '#007bff');
                } else {
                    $item.find('> div').css('background-color', 'white').css('border-color', '#dee2e6');
                }
                updateInpatientCart();
            }
        });
        
        // Also allow clicking on the entire item to toggle checkbox
        $medicinesBody.off('click', '.prescription-medicine-item').on('click', '.prescription-medicine-item', function(e) {
            // Don't trigger if clicking directly on the checkbox
            if ($(e.target).is('input[type="checkbox"]')) {
                return;
            }
            const $checkbox = $(this).find('.medicine-checkbox');
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        });
        
        // Store medicines list for later use
        selectedPrescriptionMedicines = medicinesList;
        updateInpatientCart();
    }

    // Update inpatient medicines list in billing section (now handled by updateInpatientCart)
    function updateInpatientMedicinesList() {
        updateInpatientCart();
    }

    // Record to billing
    $('#recordToBillingBtn').on('click', function() {
        if (!selectedPatient) {
            alert('Please select a patient first');
            return;
        }
        
        // Get current medicines from the cart display (read from DOM to get updated quantities)
        const medicines = [];
        $('#inpatientMedicinesList .cart-item-simple-row').each(function() {
            const $row = $(this);
            const medicineId = $row.data('medicine-id');
            const name = $row.find('.cart-item-simple-name').text().trim();
            const priceText = $row.find('.cart-item-simple-price').text().replace('₱', '').trim();
            const price = parseFloat(priceText) || 0;
            const quantity = parseInt($row.find('.cart-qty-input-inpatient').val()) || 0;
            
            if (quantity > 0 && name) {
                medicines.push({
                    id: medicineId,
                    name: name,
                    quantity: quantity,
                    price: price,
                    total: price * quantity
                });
            }
        });
        
        if (medicines.length === 0) {
            alert('Please select at least one medicine');
            return;
        }
        
        const total = medicines.reduce((sum, m) => sum + m.total, 0);
        const tax = total * 0.12;
        const finalTotal = total + tax;
        
        // Prepare billing data
        const billingData = {
            patient_id: selectedPatient.id,
            bill_date: '<?= date('Y-m-d') ?>',
            payment_status: 'pending',
            payment_method: 'cash',
            medication_cost: total,
            consultation_fee: 0,
            lab_tests_cost: 0,
            other_charges: 0,
            total_amount: total,
            tax: tax,
            final_amount: finalTotal,
            items: medicines.map(med => ({
                service: med.name,
                qty: med.quantity,
                price: med.price,
                amount: med.total,
                source_table: 'prescription_items',
                source_id: med.id
            }))
        };
        
        // Disable button
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Create billing record - use form data format
        const formData = new FormData();
        formData.append('patient_id', billingData.patient_id);
        formData.append('bill_date', billingData.bill_date);
        formData.append('payment_status', billingData.payment_status);
        formData.append('payment_method', billingData.payment_method);
        formData.append('medication_cost', billingData.medication_cost);
        formData.append('consultation_fee', billingData.consultation_fee);
        formData.append('lab_tests_cost', billingData.lab_tests_cost);
        formData.append('other_charges', billingData.other_charges);
        formData.append('total_amount', billingData.total_amount);
        formData.append('tax', billingData.tax);
        formData.append('final_amount', billingData.final_amount);
        
        billingData.items.forEach((item, idx) => {
            formData.append(`service[${idx}]`, item.service);
            formData.append(`qty[${idx}]`, item.qty);
            formData.append(`price[${idx}]`, item.price);
            formData.append(`amount[${idx}]`, item.amount);
            formData.append(`source_table[${idx}]`, item.source_table);
            formData.append(`source_id[${idx}]`, item.source_id);
        });
        
        fetch(BILLING_API + '/storeWithItems', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async r => {
            // Check if response is JSON or HTML
            const contentType = r.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return r.json();
            } else {
                // If redirect or HTML response, try to parse as JSON first
                try {
                    const text = await r.text();
                    const json = JSON.parse(text);
                    return json;
                } catch (e) {
                    // If not JSON, consider it success (redirect response)
                    return { status: 'success', id: null };
                }
            }
        })
        .then(resp => {
            if (resp.status === 'success' || resp.id || !resp.error) {
                // Also create pharmacy transaction
                const transactionData = {
                    patient_id: selectedPatient.id,
                    patient_name: selectedPatient.first_name + ' ' + selectedPatient.last_name,
                    date: billingData.bill_date,
                    payment_method: 'cash',
                    is_inpatient: true, // Flag to indicate this is for inpatient billing
                    items: medicines.map(med => ({
                        medicine_id: med.id,
                        medicine_name: med.name,
                        quantity: med.quantity,
                        price: med.price
                    })),
                    subtotal: total,
                    tax: tax,
                    total: finalTotal
                };
                
                return fetch(API_BASE + '/transaction/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(transactionData)
                }).then(async r => {
                    const contentType = r.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const json = await r.json();
                        if (!r.ok && !json.success) {
                            console.error('Transaction API error:', json);
                        }
                        return json;
                    } else {
                        const text = await r.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Transaction response parse error:', text);
                            return { success: false, message: 'Invalid response from server: ' + text.substring(0, 100) };
                        }
                    }
                });
            } else {
                throw new Error(resp.error || resp.message || 'Failed to create billing record');
            }
        })
        .then(transactionResp => {
            if (transactionResp && transactionResp.success) {
                alert('Successfully recorded to billing and transactions!');
                // Reset selection
                cart = [];
                selectedPrescriptionMedicines.forEach(m => m.selected = false);
                $('.medicine-checkbox').prop('checked', false);
                updateCart();
            } else if (transactionResp && !transactionResp.success) {
                // Billing was created but transaction failed - still show success for billing
                console.error('Transaction error:', transactionResp);
                let errorMsg = transactionResp.message || 'Unknown error';
                if (transactionResp.errors) {
                    errorMsg += ': ' + JSON.stringify(transactionResp.errors);
                }
                if (transactionResp.details) {
                    errorMsg += ': ' + JSON.stringify(transactionResp.details);
                }
                if (transactionResp.error_details) {
                    errorMsg += ' | DB Error: ' + JSON.stringify(transactionResp.error_details);
                }
                if (transactionResp.debug_info) {
                    console.log('Debug info:', transactionResp.debug_info);
                }
                console.error('Full transaction response:', transactionResp);
                alert('Billing recorded successfully, but transaction creation had an issue: ' + errorMsg + '\n\nCheck browser console (F12) for more details.');
                cart = [];
                selectedPrescriptionMedicines.forEach(m => m.selected = false);
                $('.medicine-checkbox').prop('checked', false);
                updateCart();
            } else {
                // No transaction response but billing succeeded
                alert('Billing recorded successfully!');
                cart = [];
                selectedPrescriptionMedicines.forEach(m => m.selected = false);
                $('.medicine-checkbox').prop('checked', false);
                updateCart();
            }
        })
        .catch(err => {
            console.error('Error details:', err);
            alert('Error: ' + (err.message || 'Failed to record billing. Please check the console for details.'));
        })
        .finally(() => {
            $btn.prop('disabled', false).html('<i class="fas fa-check-circle checkout-btn-icon"></i> Record to Billing');
        });
    });

    // Initialize - load medicines for both views
    loadMedicines();
});
</script>

<style>
.filter-btn {
    transition: all 0.3s ease;
}

.prescription-medicine-item {
    transition: background-color 0.2s ease;
}

.prescription-medicine-item:hover {
    background-color: #f8f9fa;
}

.transaction-item:hover {
    background-color: #f8f9fa;
}

.filter-view {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<?= $this->endSection() ?>
