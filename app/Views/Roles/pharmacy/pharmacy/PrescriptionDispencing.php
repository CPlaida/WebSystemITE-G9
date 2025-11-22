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
                <div class="medicine-search-container">
                    <input type="text" id="medicineSearch" class="form-control medicine-search-input" placeholder="Search medicine">
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
            
            const $card = $(`
                <div class="medicine-card" data-medicine-id="${med.id}">
                    <div class="medicine-card-image">
                        <img src="${imageUrl}" alt="${med.name}" onerror="this.onerror=null; this.src='${placeholderUrl}'" loading="lazy">
                    </div>
                    <div class="medicine-card-info">
                        <div class="medicine-name">${med.name}</div>
                        <div class="medicine-price">₱${Number(med.retail_price || med.price || 0).toFixed(2)}</div>
                        <div class="medicine-stock">Stock: ${stock}</div>
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
        if (stock <= 0) {
            alert('This medicine is out of stock.');
            return;
        }
        
        // Check if already in cart
        const existing = cart.find(item => item.medicationId === medicineId);
        if (existing) {
            // Check stock availability - use current stock, not original
            if (stock <= 0) {
                alert(`Insufficient stock. Available: ${stock}`);
                return;
            }
            existing.quantity += 1;
        } else {
            cart.push({
                id: Date.now(),
                medicationId: medicineId,
                name: medicine.name,
                quantity: 1,
                price: Number(medicine.retail_price || medicine.price || 0),
                stock: stock
            });
        }

        // Reserve stock on server
        fetch(API_BASE + '/stock/reserve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                medicine_id: medicineId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) {
                // Revert cart change if stock reservation failed
                if (existing) {
                    existing.quantity -= 1;
                } else {
                    cart.pop();
                }
                alert(resp.message || 'Failed to reserve stock');
                updateCart();
                return;
            }
            
            // Update local stock in medicine data
            if (medicine) {
                medicine.stock = resp.remaining_stock;
            }
            
            // Update stock display in the grid
            updateStockDisplay(medicineId, resp.remaining_stock);
            
            updateCart();
        })
        .catch(err => {
            // Revert cart change on error
            if (existing) {
                existing.quantity -= 1;
            } else {
                cart.pop();
            }
            alert('Error reserving stock: ' + err.message);
            updateCart();
        });
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
            $stockEl.text('Stock: ' + newStock);
            
            // Hide card if stock is 0
            if (newStock <= 0) {
                $card.fadeOut(300, function() {
                    $(this).remove();
                });
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
        
        const $card = $(`
            <div class="medicine-card" data-medicine-id="${med.id}">
                <div class="medicine-card-image">
                    <img src="${imageUrl}" alt="${med.name}" onerror="this.onerror=null; this.src='${placeholderUrl}'" loading="lazy">
                </div>
                <div class="medicine-card-info">
                    <div class="medicine-name">${med.name}</div>
                    <div class="medicine-price" style="color: #dc3545; font-weight: 600; font-size: 14px; margin: 4px 0;">₱${Number(med.retail_price || med.price || 0).toFixed(2)}</div>
                    <div class="medicine-stock">Stock: ${stock}</div>
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
    
    // Update cart display
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
            const $item = $(`
                <div class="cart-item-row">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-details">
                            Quantity: <span class="cart-item-qty">${item.quantity}</span> × 
                            <span class="cart-item-price">₱${item.price.toFixed(2)}</span> = 
                            <span class="cart-item-total">₱${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="cart-item-controls">
                        <button type="button" class="btn-qty btn-decrease" data-index="${index}">-</button>
                        <button type="button" class="btn-remove" data-index="${index}">×</button>
                    </div>
                </div>
            `);
            $cartItems.append($item);
        });
        
        updateTotal();
    }
        
    // Update total amount
    function updateTotal() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Update display
        $('#total').text('₱' + total.toFixed(2));
        
        if (document.getElementById('amount_received')) {
            setTimeout(updateChange, 0);
        }
    }

    // Cart item actions
    $(document).on('click', '.btn-decrease', function() {
        const index = $(this).data('index');
        const item = cart[index];
        if (!item) return;
        
        if (item.quantity > 1) {
            item.quantity--;
            // Restore 1 unit of stock
            restoreStockFromCart(item.medicationId, 1, index);
        } else {
            // Remove if quantity becomes 0
            restoreStockFromCart(item.medicationId, item.quantity, index, true);
        }
    });
    
    $(document).on('click', '.btn-remove', function() {
        const index = $(this).data('index');
        const item = cart[index];
        if (!item) return;
        
        // Restore all quantity back to stock
        restoreStockFromCart(item.medicationId, item.quantity, index, true);
    });
    
    // Restore stock when removing from cart
    function restoreStockFromCart(medicineId, quantity, cartIndex, removeFromCart = false) {
        fetch(API_BASE + '/stock/restore', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                medicine_id: medicineId,
                quantity: quantity
            })
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) {
                alert(resp.message || 'Failed to restore stock');
                return;
            }
            
            // Update local stock in medicine data
            const medicine = allMedicines.find(m => m.id === medicineId);
            if (medicine) {
                medicine.stock = resp.remaining_stock;
            }
            
            // Update stock display in the grid (will recreate card if stock > 0)
            updateStockDisplay(medicineId, resp.remaining_stock);
            
            // Remove from cart if needed
            if (removeFromCart) {
                cart.splice(cartIndex, 1);
            }
            
            updateCart();
        })
        .catch(err => {
            alert('Error restoring stock: ' + err.message);
        });
    }
    
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
        if (cart.length === 0) {
            alert('Your cart is empty');
            return;
        }
        
        const amountReceived = parseFloat($('#amount_received').val() || '0');
        const totalDue = getTotal();
        if (amountReceived < totalDue) {
            alert('Amount received is insufficient to cover the total.');
            return;
        }
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        const orderData = {
            patient_id: '',
            patient_name: '',
            items: cart.map(i => ({
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
            
            // Reset form
            cart = [];
            updateCart();
            $('#amount_received').val('');
            
            // Reload medicines to update stock and remove out-of-stock items
            // Note: Stock was already decreased when items were added to cart,
            // so this reload will show the updated stock
            loadMedicines();
        })
        .catch(err => {
            alert('Error: ' + err.message);
        });
    });

    // Initialize
    loadMedicines();
});
</script>

<?= $this->endSection() ?>
