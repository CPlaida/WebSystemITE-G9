<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Prescription Dispensing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="prescription-container">
    <!-- LEFT SIDE - Medicine Shelf -->
    <div class="prescription-main">
        <!-- Medicine Shelf -->
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
                
    <!-- RIGHT SIDE - Cart & Checkout -->
    <div class="prescription-sidebar">
        <!-- Total Amount -->
        <div class="card total-amount-card">
            <div class="card-body total-amount-body">
                <div class="total-amount-row">
                    <span class="total-amount-label">Total Amount:</span>
                    <span id="total" class="total-amount-value">₱0.00</span>
            </div>
        </div>
    </div>
    
        <!-- Cart -->
        <div class="card cart-card">
            <div class="card-header cart-header">
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
                
        <!-- Checkout Area -->
        <div class="card payment-card">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
   const API_BASE = '<?= site_url('api/pharmacy') ?>';

    let allMedicines = [];
    let filteredMedicines = [];
    let cart = [];

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
            // Skip medicines with 0 stock
            if (stock <= 0) {
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

    // Add to cart function
    function addToCart(medicineId) {
        const medicine = allMedicines.find(m => m.id === medicineId);
        if (!medicine) return;

        const stock = Number(medicine.stock || 0);
        // Only block if stock is 0 (out of stock)
        // Low stock (1-5) is allowed but will show a warning
        if (stock <= 0) {
            alert('This medicine is out of stock.');
            return;
        }
        
        // Show low stock warning (non-blocking) if stock is 5 or less
        if (stock <= 5) {
            console.log('Low stock warning: ' + medicine.name + ' has only ' + stock + ' units remaining.');
            // Optional: Show a non-blocking notification
            // You can add a toast notification here if desired
        }
        
        // Check if already in cart
        const existing = cart.find(item => item.medicationId === medicineId);
        if (existing) {
            // If already in cart, just focus the quantity input
            updateCart();
            setTimeout(() => {
                const $input = $(`.cart-qty-input[data-medicine-id="${medicineId}"]`);
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
            const $input = $(`.cart-qty-input[data-medicine-id="${medicineId}"]`);
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
    
    // Update cart display - Simple list format
    function updateCart() {
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
                <div class="cart-item-simple-row" data-index="${index}">
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

    // Update total amount
    function updateTotal() {
        const total = cart.reduce((sum, item) => sum + (item.price * (item.quantity || 0)), 0);
        
        // Update display
        $('#total').text('₱' + total.toFixed(2));
        
        if (document.getElementById('amount_received')) {
            setTimeout(updateChange, 0);
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
    
    // Remove item from cart
    $(document).on('click', '.cart-item-remove-btn', function() {
        const index = parseInt($(this).data('index'));
        const item = cart[index];
        if (!item) return;
        
        // Just remove from cart - no stock restoration needed since stock wasn't reserved
        cart.splice(index, 1);
        updateCart();
    });
    
    // Search medicines
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

    // Initialize
    loadMedicines();
});
</script>

<?= $this->endSection() ?>
