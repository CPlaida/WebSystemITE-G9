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
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-pills"></i>
                    <span>Medicine Shelf</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <!-- Search Bar -->
                <div style="padding: 15px; border-bottom: 1px solid #e9ecef; background: #f8f9fa;">
                    <input type="text" id="medicineSearch" class="form-control" placeholder="Search medicine" style="width: 100%;">
                </div>
                
                <!-- Medicine Grid -->
                <div id="medicineGrid" class="medicine-grid">
                    <!-- Medicine cards will be loaded here via JavaScript -->
                    <div style="padding: 40px; text-align: center; color: #6c757d;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                        <p style="margin-top: 10px;">Loading medicines...</p>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT SIDE - Cart & Checkout -->
    <div class="prescription-sidebar" style="display: flex; flex-direction: column; height: calc(100vh - 120px); max-height: calc(100vh - 120px);">
        <!-- Total Amount -->
        <div class="card" style="margin-bottom: 15px; border: 1px solid #e0e0e0; flex-shrink: 0;">
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 16px; color: #2c3e50; font-weight: 700;">Total Amount:</span>
                    <span id="total" style="font-size: 24px; font-weight: 700; color: #007bff;">₱0.00</span>
                </div>
            </div>
        </div>

        <!-- Cart -->
        <div class="card" style="border: 1px solid #e0e0e0; flex: 1; display: flex; flex-direction: column; min-height: 0; overflow: hidden;">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 12px 16px; flex-shrink: 0;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #2c3e50;">
                    <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>Cart Items
                </h3>
            </div>
            <div class="card-body" style="padding: 0; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                <div id="cartItems" style="flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0;">
                    <div style="padding: 40px; text-align: center; color: #6c757d;">
                        <i class="fas fa-shopping-cart" style="font-size: 32px; opacity: 0.3;"></i>
                        <p style="margin-top: 10px; font-size: 14px;">Your cart is empty</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Area -->
        <div class="card" style="margin-top: 15px; border: 1px solid #e0e0e0; flex-shrink: 0;">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 12px 16px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #2c3e50;">Payment</h3>
            </div>
            <div class="card-body" style="padding: 16px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="amount_received" style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px; font-size: 14px;">Amount Received</label>
                    <div style="display: flex; align-items: stretch;">
                        <span style="background: #f3f4f6; border: 1px solid #d1d5db; border-right: 0; color: #374151; padding: 10px 12px; border-radius: 6px 0 0 6px; font-weight: 600; display: flex; align-items: center; font-size: 14px;">₱</span>
                        <input type="number" id="amount_received" class="form-control" min="0" step="0.01" placeholder="0.00" style="border-radius: 0 6px 6px 0; text-align: right; border-left: 0; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px;">
                    </div>
                    <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 8px;">
                        <span id="change_badge" style="background: #eafaf1; color: #0f7a43; border: 1px solid #b7f0cf; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px;">Change: ₱0.00</span>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-block" id="checkoutBtn" style="padding: 12px; font-size: 15px; font-weight: 600; border-radius: 6px; background: #007bff; border: none; color: white; cursor: pointer; transition: background 0.2s; width: 100%;" onmouseover="this.style.background='#0056b3'" onmouseout="this.style.background='#007bff'">
                    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> Process Checkout
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
                $('#medicineGrid').html('<div style="padding: 40px; text-align: center; color: #dc3545;">Error loading medicines. Please refresh the page.</div>');
            });
    }

    // Render medicine grid
    function renderMedicineGrid() {
        const $grid = $('#medicineGrid');
        $grid.empty();

        if (filteredMedicines.length === 0) {
            $grid.html('<div style="padding: 40px; text-align: center; color: #6c757d;">No medicines found</div>');
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
                        <div class="medicine-price" style="color: #dc3545; font-weight: 600; font-size: 14px; margin: 4px 0;">₱${Number(med.price || 0).toFixed(2)}</div>
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
                price: Number(medicine.price || 0),
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
                    <div class="medicine-price" style="color: #dc3545; font-weight: 600; font-size: 14px; margin: 4px 0;">₱${Number(med.price || 0).toFixed(2)}</div>
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
                <div style="padding: 40px; text-align: center; color: #6c757d;">
                    <i class="fas fa-shopping-cart" style="font-size: 32px; opacity: 0.3;"></i>
                    <p style="margin-top: 10px;">Your cart is empty</p>
                </div>
            `);
            updateTotal();
            return;
        }
        
        cart.forEach((item, index) => {
            const $item = $(`
                <div class="cart-item-row" style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                    <div class="cart-item-info" style="flex: 1;">
                        <div class="cart-item-name" style="font-weight: 600; color: #2c3e50; font-size: 14px; margin-bottom: 4px;">${item.name}</div>
                        <div class="cart-item-details" style="font-size: 13px; color: #6c757d;">
                            Quantity: <span class="cart-item-qty" style="font-weight: 600; color: #2c3e50;">${item.quantity}</span> × 
                            <span style="color: #2c3e50;">₱${item.price.toFixed(2)}</span> = 
                            <span style="font-weight: 600; color: #007bff;">₱${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="cart-item-controls" style="display: flex; gap: 6px; margin-left: 12px;">
                        <button type="button" class="btn-qty btn-decrease" data-index="${index}" style="width: 32px; height: 32px; border: 1px solid #d1d5db; background: #fff; color: #6c757d; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">-</button>
                        <button type="button" class="btn-remove" data-index="${index}" style="width: 32px; height: 32px; border: 1px solid #dc3545; background: #fff; color: #dc3545; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">×</button>
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
